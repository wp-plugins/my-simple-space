<?php

/**
 * Plugin Name: My Simple Space
 * Version: 1.0.7
 * Plugin URI: http://mannwd.com/wordpress/my-simple-features/
 * Description: Show the diskspace and memory usage of your site.
 * Author: Michael Mann
 * Author URI: http://mannwd.com
 * License: GPL v2

 * Copyright (C) 2015, Michael Mann - support@mannwd.com

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation version 2.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.

**/

class SimpleSpace {

     // Setup the environment for the plugin
     public function bootstrap() {
    }

	// @TODO Add class constructor description.
	function __construct() {

		// Constants
		if ( ! defined( 'MY_SIMPLE_SPACE_PLUGIN_URL' ) )
			define( 'MY_SIMPLE_SPACE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Hook into the 'wp_dashboard_setup' action to register our other functions
		add_action( 'wp_dashboard_setup', array( $this, 'my_simple_space_widget' ) );
		add_filter( 'admin_footer_text', array( $this, 'my_simple_space_footer' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'my_simple_space_admin_css' ) );

	}

	// Dashboard Widget
	function my_simple_space_widget() {
	add_meta_box( 'simple_space_widget', '<span class="dashicons dashicons-chart-pie"></span> My Simple Space', 'my_simple_space', 'dashboard', 'side', 'high' );
	}

	public function my_simple_space_admin_css() {
		wp_register_style( 'simple_space_admin', MY_SIMPLE_SPACE_PLUGIN_URL . 'space.css', false, '1.0.3' );
        wp_enqueue_style( 'simple_space_admin' );
	}

	function my_simple_space_footer($memory) {

		$memory = my_simple_get_memory();
		$memory_limit = $memory['memory_limit'];
		$memory_usage = $memory['memory_usage'];

		echo '<p id="footer-left" class="alignleft"><span id="footer-thankyou">Thank you for creating with <a href="https://wordpress.org/">WordPress</a>.</p><p class="alignleft">&nbsp;|&nbsp;<span id="my-simple-memory"><span style="font-weight:bold;">Total Memory:</span> ' . $memory_limit . '&nbsp;&nbsp; <span style="font-weight:bold;">Used:</span> ' . format_size($memory_usage) . '</span></p>';

	}

}

global $simplespace;
$simplespace = new SimpleSpace();
$simplespace->bootstrap();

function my_simple_get_memory() {

	$memory['memory_limit'] = ini_get( 'memory_limit' );
	$memory['memory_usage'] = function_exists( 'memory_get_usage' ) ? round( memory_get_usage(), 2 ) : 0;

	return $memory;

}

// Create the function to output the contents of our Dashboard Widget
function my_simple_space() {

	global $wpdb;
	$dbname = $wpdb->dbname;

	// Setup Home Path for Later Usage
	if ( get_home_path() === "/" )
		$homepath = ABSPATH;
	else
		$homepath = get_home_path();

	$phpversion = PHP_VERSION;

	$memory = my_simple_get_memory();
	$memory_limit = $memory['memory_limit'];
	$memory_usage = $memory['memory_usage'];

	$subfolder = strrpos( get_site_url(), '/', 8 ); // Starts after http:// or https:// to find the last slash

	// Determines if site is using a subfolder, such as /wp
	if ( isset( $subfolder ) && $subfolder != "" ) {

		$remove = substr( get_site_url(), strrpos( get_site_url(), '/' ), strlen( get_site_url() ) );
		$home = str_replace ( $remove, '', $homepath ); // Strips out subfolder to avoid duplicate folder in path

	} else {

		$home = $homepath;

	}

	echo $home;

	// Upload Directory
	$uploads = wp_upload_dir();

	// Get Memory
	if ( !empty( $memory_usage ) && !empty( $memory_limit ) ) {
		$memory_percent = round ( $memory_usage / $memory_limit * 100, 0 );
	}

	// Get Database Size
	$result = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
	$rows = count( $result );

	$dbsize = 0;

	if ( $wpdb->num_rows > 0 ) {
		foreach ( $result as $row ) {
			$dbsize += $row["Data_length"] + $row["Index_length"];
		}
	}

	// PHP version, memory, database size and entire site usage (may include not WP items)
	$topitems = array (
		'PHP Version' => $phpversion . ' '. ( PHP_INT_SIZE * 8 ) . __(' Bit OS', 'simple_space'),
		'Memory' => 'Total: ' . $memory_limit . ' Used: ' . format_size( $memory_usage ),
		'Database' => format_size( $dbsize ),
		'Entire Site' => format_size( foldersize( $homepath ) )
	);

	foreach ($topitems as $name => $value) {
		echo '<p class="halfspace"><span class="spacedark">' . $name . '</span>: ' . $value . '</p>';
	}

	echo '<div class="halfspace">
<p><span class="spacedark">Contents</span></p>';

	$content = parse_url( content_url() );
	$content = $home . $content['path'];
	$plugins = str_replace( plugin_basename( __FILE__ ), '', __FILE__ );

	// WP Content and selected subfolders
	$contents = array(
		"wp-content" => $content,
		"&nbsp;&nbsp;&nbsp;plugins" => $plugins,
		"&nbsp;&nbsp;&nbsp;themes" => get_theme_root(),
		"&nbsp;&nbsp;&nbsp;uploads" => $uploads['basedir'],
	);

	foreach ($contents as $name => $value) {

		echo '<span class="spacedark">' . $name . '</span>: ' . format_size( foldersize( $value ) ) . '<br />';

	}

	echo '</div>';

	$wpadmin = parse_url ( get_admin_url() );
	$wpadmin = $home . ltrim( $wpadmin['path'], '/' );
	$wpincludes = parse_url ( includes_url() );
	$wpincludes = $home . ltrim( $wpincludes['path'], '/' );

	echo '<div class="halfspace">
<p><b>Other WP Folders</b></p>';

	// wp-admin and wp-includes folders
	$folders = array(
		"wp-admin" => $wpadmin,
		"wp-includes" => $wpincludes
	);

	foreach ($folders as $name => $value) {
		echo '<span class="spacedark">' . $name . '</span>: ' . format_size( foldersize( $value ) ) . '<br />';
	}

	echo '</div>';

}

// Get Folder size function
function foldersize( $path ) {
    $total_size = 0;
    $files = scandir( $path );
    $cleanPath = rtrim( $path, '/' ). '/';

    foreach( $files as $t)  {
        if ( $t<>"." && $t<>".." ) {
            $currentFile = $cleanPath . $t;
            if ( is_dir( $currentFile ) ) {
                $size = foldersize( $currentFile );
                $total_size += $size;
            }
            else {
                $size = filesize( $currentFile );
                $total_size += $size;
            }
        }   
    }

    return $total_size;
}

// Formatting the size function
function format_size( $size ) {
    global $units;

    $units = explode( ' ', 'B KB MB GB TB PB' );

    $mod = 1024;

    for ( $i = 0; $size > $mod; $i++ ) {
        $size /= $mod;
    }

    $endIndex = strpos( $size, "." ) + 3;

    return substr( $size, 0, $endIndex ) .' '. $units[$i];
}

?>
