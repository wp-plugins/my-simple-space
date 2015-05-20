=== My Simple Space ===
Contributors: mannweb
Tags: disk space, database size
Requires at least: 3.5.0
Tested up to: 4.3
Stable tag: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Diskspace Disk Space, Database and Memory Usage in the dashboard.

== Description ==

Display the total size space usage as well:

*   wp-content total size
*   wp-content/plugins size
*   wp-content/themes size
*   wp-content/uploads size
*   database size
*   Total available memory / used memory
*   PHP Version and OS (32/64 bit)

== Installation ==

Simply downloadThis section describes how to install the plugin and get it working.

e.g.

1. Upload `my-simple-space` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How is diskspace calculated? =

The plugin cycles through the provided path to calculate the total space used for that particular path. The wp-content size includes the plugins, themes and upload folders, but also other folders under wp-content.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0.2 =
* Removed hard coded paths and replaced with dynamic paths.

= 1.0.1 =
* Rewrote database calculation to make use of $wpdb, rather than mysql calls, which broke in some instances.

= 1.0.0 =
* Initial Release

== Upgrade Notice ==

= 1.0.1 =
Repair database table size routine to make use of $wpdb, as it was not working on some sites.
