=== Plugin Name ===
Contributors: jhong
Donate link: http://www.wp-united.com/
Tags: phpbb, phpBB3, forum, social, integration, widgets, template, sign-on, user integration, database
Requires at least: 3.4.0
Tested up to: 3.5.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP-United integrates phpBB and WordPress to make a social site. Enable any of these modules: sign-on, theming, widgets, cross-posting and behaviour.

== Description ==

WP-United glues together WordPress sites and phpBB forums. Get the full power of WordPress publishing and plugins, with the familiar and established community format of phpBB.

WP-United is fully modular and individual features can be turned on and off. These features include:

* **User integration**: including single sign-on, synchronised profiles and avatars, and user management. Works with external registration modules such as social login plugins. Users in phpBB get accounts in WordPress and vice versa. Completely controllable and customisable by setting permissions for who can integrate and at what level, using a unique drag-and-drop interface.
* **Template integration**: Have your phpBB forum appear inside your WordPress site. Or vice-versa; integrate your WordPress blog appear inside your phpBB header and footer. Includes a "one-click" mode that analyses your theme's CSS and modifies it on the fly to avoid CSS and HTML conflicts. This is no iFrame solution, it is a complete and automatic template integration.
* **Behaviour integration**: Use phpBB smilies and word censor features in WordPress 
* **Cross-posting**: Post something in WordPress and have it cross-posted to your forum -- Automatically or manually, you choose! Once an item is cross-posted, comments in phpBB and WordPress can be set to sync up under the blog item too!


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Back up the current state of your site, just to be on the safe side.
1. Upload the `wp-united` directory and all its contents to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Install phpBB on your server somewhere -- anywhere is OK, even on a different subdomain -- and ensure it is set up and working OK. Pay particular attention to your phPBB "server settings" -- they need to be correct for WP-United to work.
1. Visit the WP-United section in the administration area to connect your WordPress site to your forum.
1. As part of the connection process, WP-United will prompt you to download and install a phpBB modification package. This will need to be installed into your phpBB3. We recommend you use [Automod](https://www.phpbb.com/mods/automod/) for this.

== Frequently Asked Questions ==

= Does this work with WordPress multisite? =
It works but is not recommended for use, especially with single sign-on currently, as it does not behave as expected. In future versions WP-United will allow per-user blogs with WordPress multisite, so stay tuned.

= I enabled the plugin but nothing is happening! =

Follow the instructions to connect to your phpBB forum in the WP-United section of the admin panel.

= I can't connect, I am getting errors. =

Ensure you have installed the phpBB modification package. You can download it from (WP-United.com)[http://www.wp-united.com/releases/wp-united-latest-phpbb]

= I am getting blank pages, I have no idea what is wrong! =

There are several ways to debug problems. We suggest you try the following, in order:

* Ensure you have installed the phpBB modification package correctly. (Don't guess, use [Automod](https://www.phpbb.com/mods/automod/) !)
* Turn on phpBB debugging in phpBB's `config.php` by uncommenting the two DEBUG lines.
* Turn on WordPress debugging in WordPress' `wp-config.php` by adding `define('WP_DEBUG', TRUE);` somewhere.
* Disable error suppression in phpBB mods by opening phpBB's `common.php`, and removing the `@` from `@include($phpbb_root_path . 'includes/hooks/' ...`
* Look in your php error log.

== Screenshots ==

1. The WP-United connection screen
2. The WP-United settings panel
3. The WP-United user mapper
4. The WP-United permissions mapper
5. A phpBB forum in a WordPress page
6. A WordPress page in a phpBB forum
7. Some WP-United widgets

== Changelog ==

= v0.9.0 RELEASE CANDIDATE 3 =

* WP-United is, for the most part, completely rewritten, to improve flexibility and compatibility
* The vast majority of WP-United now sits under WordPress rather than phpBB. Find plugin, click install, done... that's what the aim is.
* Modular -- hooks and files are only loaded if those options are selected
* Brand new, modern, admin panel:
	* A completely re-imagined settings panel, with modern UI and with most options significantly simplified
	* No more Wizards!
	* Panel communicates with phpBB asynchronously -- no more blank pages!
	* New interactive user mapper that can integate, break integrations, create, delete and edit users in a few clicks
	* New draggable, connectable permissions mapper to hide the arcane phpBB permissions UI
	* Less options, more sensible defaults, all in one place
* Completely re-written user integration
	* User integration is now bi-directional. Log in or register in WordPress or phpBB, and seamlessly access the site
	* Roles are now set at user creation time, not every visit -- much more flexible
	* Bi-directional profile sync. Update your profile anywhere and it works
	* Auto-synced avatars. Get your Gravatar in phpBB -- without having to anything
	* Designed to work with external auth providers. e.g. click the Facebook button in the oneall plugin, and you get both a phpBB and WordPress account
* Widgets all ported to new WordPress API. 
* Widgets now colour phpBB usernames according to default group colour
* Numerous bugs addressed
* User blogs has been removed. It will be added back, working with WP-MS, in the next release.
* Translations all moved to WordPress


= v0.8.5 RELEASE CANDIDATE 2 =

* Fixed plugin fixer not covering all global variables
* Fixed plugin fixer not working with plugins tha use PHP short tags
* Suppress errors during path detection, etc, for people with open_basedir restrictions
* Fixed: If forum and blog are both in root directory, add explicit "index.php" to the forum link
* Added more classes to the login / userinfo widget to facilitate styling.
* New option: cross-post excerpts or full posts. Three choices: Excerpt / full post / ask each time
* Removed inline JavaScript for smilies. All WP-United JS moved to wp-united/js/wpu-min.js
* Fixed tags/categories not showing up properly on cross-posted posts
* Show "cross-post" in past tense in force xpost box if already xposted.
* Added two new options, SHOW_BLOG_LINK, and WPU_INTEG_DEFAULT_STYLE -- full info in options.php
* Fixed problem links in reverse integration, e.g. on phpBB FAQ page
* Fixed uncategorized checked when selecting cross-posting
* changed some BBCode translations
* Stopped allowing submission of blank cross-posted comments
* Fixed smiley path in cross-posted comments sometimes not correct
* User mapper is now case insensitive when looking for suggested phpBB username matches
* Added new option, WPU_SHOW_TAGCATS, to suppress display of categories & tags in cross-posts
* Fixed avatar and other details not synced when deleted by user in UCP
* Added option in options.php to define templates on which the WordPress header/footer should not appear, and pre-filled it with some shoutboxes.
* Installer auto-purges cache again when finished to ensure WP-United tab appears

= v0.8.4.1 RELEASE CANDIDATE 2 = 

* NEW: wpu-install.php removed, replaced by auto-installer
* Fixed comments closed for global announcements when they shouldn't be.
* Fixed unreliable display of comment date/time
* Added better error catching for people who don't install the mod properly (e.g. if they activate the plugin without installing/setting up, or run it without the plugin)
* Fixed various unfriendly error messages
* Fixed timezone of cross-posts
* Improved BBCode & magic URL checking, stopped double-pass of make clickable
* Fixed errors with user blogs list, and improved login_userinfo
* Fixed username incorrect when editing cross-posted posts
* Fixed integration error message after updating password or username under some circumstances
* ... and several other minor bug fixes

For previous changes, please view the included `CHANGELOG` file.