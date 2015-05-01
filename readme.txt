=== Jetpack Module Control ===
Contributors: RavanH
Tags: Jetpack, jet pack, module, modules, manual control, blacklist, blacklist modules, slim jetpack, jetpack light
Stable tag: 0.3
Requires at least: 4.1
Tested up to: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Your Jetpack, Restricted.

== Description ==

[Jetpack](https://wordpress.org/plugins/jetpack/) adds powerful features... but sometimes we don't want them all. This plugin will allow you to blacklist / remove individual Jetpack modules. It can also prevent the auto-activation of Jetpack modules.

This plugin can run on single and multisite installations. 

In most use cases, a carefully considered combination of Jetpack modules can be a really good thing. But not always is much consideration being done beforehand. Or site admins just don't know all the implications... Some examples: Does your web server use a server side caching making it incompatible with the Mobile Theme module? Do you already have a lightbox plugin running, making the Carousel module is redundant? Do you not want anyone monitoring uptime with the Monitor module just to call you every time the site was not accessible for a second? Do you offer your own backup formula and do not care much for VaultPress's competition? Any one of Jetpack's modules can bring overlapping or even conflicting functionality. In such cases, being able to remove them is a good thing.
 
On multisite, it can only be network activated and controls Jetpack on all sites. Jetpack itself can, but does not need to be, network activated.

For single site installations, this plugin would only serve purpose if the site has multiple administrators and the primary admin wants to prevent other admins from switching on certain modules. The primary admin needs access to the wp-config.php to be able to lock down the plugins settings for complete security.


== Installation ==

1. Install Jetpack Module Control either via the WordPress.org plugin directory, or by uploading the files to your server.
2. After activating the plugin, go to either Settings > General (on single site) or Network Admin > Settings (on multisite) to find the new Jetpack Module Control section.
3. Select any module you wish to remove and save settings.
4. If you are on a single site isntallation and you wish to prevent other admins from reactivating any blacklisted modules, add define('JETPACK_MC_LOCKDOWN', true); to your wp-config.php to lock down settings.
5. That's it.

== Screenshots ==

1. Options section with Blacklist.

== Changelog ==

= 0.4 =
Date: 

* Settings action link on Network plugins page

= 0.3 =
Date: April 27rd, 2015

* Added dashicons

= 0.2 =
Date: April 26rd, 2015

* Added network options

= 0.1 =
Date: April 25rd, 2015

* Initial release
