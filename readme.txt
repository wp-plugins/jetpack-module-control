=== Jetpack Module Control ===
Contributors: RavanH
Tags: Jetpack, jet pack, module, modules, manual control, blacklist, blacklist modules
Stable tag: 0.3
Requires at least: 4.1
Tested up to: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Your Jetpack, Restricted.

== Description ==

[Jetpack](http://jetpack.me/) adds powerful features but sometimes we don't want them all. This plugin will allow you to blacklist / remove individual Jetpack modules. It can also prevent the auto-activation of Jetpack modules.

This plugin was created to run both on single site and multisite installations. On multisite, it can only be network activated.

== Installation ==

1. Install Jetpack Module Control either via the WordPress.org plugin directory, or by uploading the files to your server.
2. After activating the plugin, go to either Settings > General (on single site) or Network Admin > Settings (on multisite) to find the new Jetpack Module Control section.
3. Select any module you wish to remove and save settings.
4. If you are on a single site isntallation and you wish to prevent other admins from reactivating any blacklisted modules, add define('JETPACK_MC_LOCKDOWN', true); to your wp-config.php to lock down settings.
5. That's it.

== Screenshots ==

1. Blacklist selection.

== Changelog ==

= 0.3 =
Date: April 27rd, 2015

* Added dashicons

= 0.2 =
Date: April 26rd, 2015

* Added network options

= 0.1 =
Date: April 25rd, 2015

* Initial release
