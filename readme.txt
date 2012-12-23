=== WP-United ===
Contributors: Jhong
Donate link: http://www.wp-united.com/
Tags: phpbb, phpBB3, forum, social, integrate, bridge, integration, widgets, template, sign-on, user integration, database
Requires at least: 3.4.0
Tested up to: 3.5.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP-United integrates phpBB and WordPress to make a social site. Enable any of these modules: sign-on, theming, widgets, cross-posting and behaviour.

== Description ==

Bridge phpBB and WordPress!

WP-United glues together WordPress sites and phpBB forums. Get the full power of WordPress publishing and plugins, with the familiar and established community format of phpBB.

WP-United is fully modular and individual features can be turned on and off. These features include:

* **User integration**: including single sign-on, synchronised profiles and avatars, and user management. Works with external registration modules such as social login plugins. Users in phpBB get accounts in WordPress and vice versa. Completely controllable and customisable by setting permissions for who can integrate and at what level, using a unique drag-and-drop interface.
* **Template integration**: Have your phpBB forum appear inside your WordPress site. Or vice-versa; integrate your WordPress blog appear inside your phpBB header and footer. Includes a "one-click" mode that analyses your theme's CSS and modifies it on the fly to avoid CSS and HTML conflicts. This is no iFrame solution, it is a complete and automatic template integration.
* **Behaviour integration**: Use phpBB smilies and word censor features in WordPress 
* **Cross-posting**: Post something in WordPress and have it cross-posted to your forum -- Automatically or manually, you choose! Once an item is cross-posted, comments in phpBB and WordPress can be set to sync up under the blog item too!

WP-United also includes eight widgets for you to drop into your WordPress page. Each widget is configurable and displays a wealth of forum information to increase enagagement on your site:

* Latest forum posts
* Latest forum topics
* users online list
* Forum statistics
* An integrated login/meta/avatar/profile block
* Birthday list
* Quick poll (select from active phpBB polls in an ajax widget)
* Forum top bar (with breadcrumbs that work in WordPress)

Visit [wp-united.com](http://www.wp-united.com) for more information and to view example integrations in the gallery.

The download includes English, with German and Simplified Chinese translations.

== Installation ==

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
8. The WP-United QuickPoll widget

== Changelog ==

= v09.1.4 RELEASE CANDIDATE 3 =

* BUGFIX: WordPress users created in user mapper set to administrators. if you have created WordPress users using the user mapper previously, please check to ensure they are not administrators.
* BUGFIX: WordPress register date now showing correctly in user mapper and minor mapper display fixes
* BUGFIX: improper context switching in user mapper causing error with W3 Total Cache
* BUGFIX: phpBB normal ranks not showing for users in user mapper
* BUGFIX: minor avatar notice
* BUGFIX: Incorrect "forgot password" link in user login block widget
* NEW: integrated links in WP menu bar (admin-bar)
* Code cleanup and improved code documentation. Developers/hackers look at wp-united.php to get started.

= v0.9.1.3 RELEASE CANDIDATE 3 =
* This quick release fixes a missing file, a bug in the user mapper due to the large changes in v0.9.1.0, and the install.xml file. Please update.

= v0.9.1.0 RELEASE CANDIDATE 3 =

* NEW: Quick poll widget! Can have multiple polls per page, can submit via AJAX, BBCode & smilies etc. work. Can use prosilver or subsilver2 forum styles.
* NEW: Forum top bar widget, complete with phpBB-style breadcrumbs in WordPress!
* NEW: Forum birthdays widget!
* NEW: phpBB MOD installation is now more closely checked, and we also ensure the phpBB MOD and WordPress plugin versions match.
* NEW: The cross-post prefix, [BLOG], can now be changed in the settings panel.
* NEW: Smilies now obey phpBB's max smilies per post setting
* NEW: Get Help screen now shows active plugins, theme and memory settings to help in error reporting
* NEW: WP-United Extras: Drop-in plugins for easy additions to WP-United. The first 'extra' is the Quick poll widget! In future versions there will be a UI added to download additional extras.
* BUGFIX / NEW: Allow password portability for passwords with htmlentities or leading/trailing spaces
* BUGFIX / NEW: WordPress initial init is deferred when in phpBB until after phpBB auth has completed, this solves a number of login oddities with plugins and with admin bar not showing on phpBB-in-WordPress pages.
* BUGFIX: RTL layout not preserved when using template integration
* BUGFIX: Warnings on reply posting pages due to phpBB request variables interfering with WP_Query when template is integrated.
* BUGFIX: WordPress adding slashes to phpBB post and get variables
* BUGFIX: A number of minor bugs and error notices in widgets
* BUGFIX: Template cache not working when WordPress version has a dash in it (e.g. RC releases)
* BUGFIX: phpBB header/footer added to WP ajax on WordPress-in-phpBB pages
* BUGFIX: WP logout link in user profile/loginblock widget not showing phpBB status if user is not integrated
* BUGFIX: login/out link in "useful links" widget reversing login and logout actions
* BUGFIX: Better error handling if the plugin gets disabled due to errors

= v0.9.0.3 RELEASE CANDIDATE 3 =

* NEW: Quick user search box added to user mapper
* NEW: Synchronize profiles user mapper button and bulk action
* UPDATED: User mapper allows processing up to 250 users at once
* UPDATED: Don't repaginate user mapper after processing actions
* UPDATED: More permissive in returned messages from server when connecting and enabling (fixes errors on servers where files have leading or trailing garbage).
* BUGFIX: User mapper dying on entities in usernames
* BUGFIX: User mapper not displaying user names in alphabetical order when phpBB was on left
* BUGFIX: Load version when WP-United is disabled so things like "Get Help" still work.
* BUGFIX: Rank images had incorrect URLs

= v0.9.0.2 RELEASE CANDIDATE 3 =

* NEW: New widget: Useful forum links
* BUGFIX: Explicitly disable WP admin bar on simple header & footer phpBB-in-WordPress pages
* BUGFIX: Link to post not displaying in cross-post
* BUGFIX: Cross-post box not honouring excerpt/fullpost/askme choice


= v0.9.0.0/1 RELEASE CANDIDATE 3 =

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

For previous changes, please view the full package at wp-united.com.

== Upgrade Notice ==

= 0.9.0.2 =
This version fixes a few minor bugs reported just after release of v0.9. You can update automatically or by simply copying over the WordPress plugin; You do not need to update the phpBB side.

= 0.9.0.3 =
This version improves the user mapper and addresses a few minor reported bugs. You can update automatically or by simply copying over the WordPress plugin; You do not need to update the phpBB side.

= 0.9.1.0 =
This version fixes a number of bugs with template integration and user integration. You should update as soon as possible. You will need to upgrade the phpBB portion in addition to the WordPress plugin by following the instructions in the contrib/.../upgrade.xml file.

= 0.9.1.2 =
This version adds a missing file from v0.9.1.0, please update to avoid errors

= 0.9.1.3 =
This version fixes a bug in the user mapper, please update.

= 0.9.1.4 =
This version fixes a few important bugs in the user mapper, please update ASAP.