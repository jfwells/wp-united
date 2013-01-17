WP-UNITED: phpBB - WordPress Integration v0.9.1 RC3
-----------------------------------------------
Last updated: 29/12/2012, by John Wells

Check http://www.wp-united.com for latest versions, and for faster support

Thank you for downloading WP-United! This file introduces how to get up and running with your integrated phpBB & WordPress install.

Sorry for the delay in releasing v0.9!

The method for installing and using WP-United v0.9 has changed. Please disregard any instructions for v0.8.5 or earlier.

BEFORE YOU BEGIN
------------------------

1. Make sure you have already installed phpBB and WordPress, and have verified that they are working OK. I recommend the latest versions - at this time of writing, that means phpBB3 3.0.11 & WordPress 3.5.x.

2. Check that your phpBB and wordpress settings are correct -- please pay attention to your phpBB "script path" and cookie settings in your phpBB ACP "Server Settings" and ensure they are correct.

3. If these are not new installations, back up your phpBB & WordPress files and databases!

4. Install the two portions of WP-United. WP-United consists of two parts: a WordPress plugin and a phpBB "MOD".

4a) Install the wordpress plugin by uploading the plugin/wp-united directory to your WordPress plugins directory. and clicking "Activate" next to WP-United in the plugins menu.

4b) You have two options to install the phpBB MOD -- manually, by altering code in your phpBB files (not recommended), or automatically, using AutoMod (recommended). Details are below.

5. Follow the instructions in the WordPress WP-United panel to connect to your phpBB board, and activate the WP-United elements you need

INSTALLING THE phpBB MOD AUTOMATICALLY (recommended)
-------------------------------------

1. Download and install AutoMod from https://www.phpbb.com/mods/automod/

2. Upload the phpbb/ folder and all files to your forum's "store" folder.

3. Follow the instructions in AutoMod


INSTALLING THE phpBB MOD MAUNALLY (NOT RECOMMENDED)
--------------

An install.xml file has been included. To read this properly, unpack the archive to your hard drive, and open the install.xml file from its unpacked location in your browser (it needs the accompanying .xsl file to display properly).

The  file will tell you which files to upload to your forum and where they should go, and will give instructions on which files to modify and how. 

install.xml includes instructions for the prosilver template. If you are using the subSilver2 template, or other templates, you must also apply the mod to those. Instructions for subSilver2 are included in the templates folder. For other templates, use the prosilver or subSilver2 directions as necessary.

Some additional translations may have been provided in the /languages folder -- open up the .xml file for your language to install them.

If you need further help on what the directives mean, take a look at the phpBB forums -- http://www.phpbb.com/mods/installing/ . Make sure you back up your files before making any changes!



UPGRADES
-------------

Instructions on converting from previous versions of WP-United are included in the contrib folder.

When copying files over, delete any old files that WP-United copied for the previous version first. (v0.9 has a lot fewer files!)

Remember, whenever upgrading WordPress or WP-United, to check the WP-United Setup / Status page.

If you were using WP-United template tags in your template in versions prior to v0.9, note that a number of them have changed. Any template tags pertaining to per-user blogs are now gone. In addition, the template tags for avatars no longer accept a "default" parameter.


CONTINGENCIES
-------------

If you encounter errors that make your forums inaccessible, WP-United can be manually disabled temporarily, so that you can get into your phpBB ACP or WordPress Dashboard.

The easy way is simply to delete or rename the WP-United plugin folder in WordPress, and delete the phpbb/includes/hook/hook_wp-united.php file.



UNINSTALLING (Noooooo!)
-----------------------------------
If you want to uninstall WP-United, be sure to click the "disable" button in the WP-United - >Setup/Status panel before deactivating the WordPress plugin. Otherwise, phpBB won't know that WP-United has been disabled.



SUPPORT WP-UNITED   (*Please!*)
-------------------------------------------

Want to help improve the software? Or just want to put a smile on the developer's face? There are lots of ways you could help:
- Bug swatting -- Report any bugs you find on the forums. Anything, from typos to server crashes... let us have it!
- More Exposure -- Link back to www.wp-united.com, or post about WP-United on your blog. Want an interview? I'm available!
- Cash -- of the cold and hard variety. Every little bit helps. I don't have much...
- Translation -- translate this file and the language strings used in the mod. I'll put your name in lights (well, pixels) if you do.
- Rate this plugin on wordpress.org
- Cash --  Oh wait... 


**Thanks** To everyone who has donated or helped out so far.

HAPPY BLOGGING!
-John


CHANGE LOG
----------------

WP-United :: Public Releases

**** phpBB3 releases: ****

= v0.9.2.0 RELEASE CANDIDATE 3 =

* NEW: Re-write of cross-posting and cross-posted comments. Cross-posted comments now appear mixed with WordPress comments, and can be viewed, filtered and managed from WordPress as well as phpBB. Cross-posted topics now also fully support custom ordering, threading and guest posting, and are stored and recalled more efficiently.
* NEW: Cross-posted comments (posted by guests) that are pending moderator approval in phpBB now show up in WordPress, with the appropriate "pending approval" message.
* NEW: Guest-cross-posted comments now store e-mail and website, just like native comments.
* NEW: New cross-posting comment permission in phpBB allows guests to cross-post comments without having to open your forum up to guests.
* NEW: Cross-posts by unauthenticated users can now be passed through WordPress filters (e.g. Akismet).
* NEW: The initial connection screen now falls back to manual path entry if your server has restrictions on scannng the document root
* NEW: You can choose to enter the phpBB path and document root manually, in case your phpBB root is under a different document root.
* BUGFIX: Users getting logged out of phpBB on full-page phpBB-in-WordPress
* WORKAROUND: Incorrect user integration flow when Ultimate TinyMCE (or similar plugins that set current user too early) are active.
* BUGFIX: Regression in avatars; default avatars getting syncd to phpBB rather than true Gravatars.
* BUGFIX: categories/tags & stats not showing up for some users if the same database user is used for phpBB & WordPress in some circumstances.
* BUGFIX: Initial connect screen was complaining about lack of phpBB MOD before even trying to connect
* NEW: More errors can now be passed through on the initial connect & settings screens: No more guessing what it is keeping you from installing.
* NEW / BUGFIX: The forum page title keeps getting reset back to "Forum" and the page creation date keeps updating.
* BUGFIX: Top navbar not correctly showing post name
* BUGFIX: Numerous issues with caching of template-integrated stylesheets leading to very full caches and some styling errors.
* BUGFIX: Userdata cache not cleared on first integrated login to phpBB
* BUGFIX: $table_prefix was getting unset on phpBB-in-WordPress pages, upsetting some mods
* BUGFIX: Improve handling of double-byte characters when escaping cross-posted topic titles and user names in user mapper.
* BUGFIX: Clash with the phpBB classifieds MOD
* BUGFIX: Synced avatars losing CSS styling
* ENHANCEMENT: Try multiple ways to initialise admin javascript, so it works even when other plugins with script errors halt JavaScript loading
* NEW: Lots more login integration debugging, so you can see what is causing login integration problems. Now works on WP pages and in admin too.
* NEW: Some more core rewriting and cleanup; the context switcher is now separated into its own parent class; the main plugin is now divided into auto-loading modules.


= v0.9.1.6 RELEASE CANDIDATE 3 =

* BUGFIX: Regression, profile update in WordPress was not triggering profile update in phpBB
* BUGFIX: Unread PMs not displaying in user login/profile widget or top navigation widget
* UPDATED: Removed output buffering intercept, should now work with gzip-enabled themes 
* UPDATED: Now works properly with W3 Total Cache and some other plugins that buffer output
* BUGFIX: Broken template tag for profile link (only affected legacy users with manually added wp-united template tags in their templates)

v0.9.1.5 RELEASE CANDIDATE 3

* NEW/BUGFIX: The full page option now only allows page templates to be chosen, and works with child themes and subfolders
* BUGFIX: Error when updating avatars & profiles or logging out on full page reverse integration
* BUGFIX: suppressing unnecessarily triggered errors on initial connect and settings changes
* BUGFIX: Avatar marker added incorrectly to custom-set WordPress avatars
* BUGFIX: theme preview not working when WordPress-in-phpBB template integration is on
* BUGFIX: Difficult to change padding value in theme integration advanced settings, and the "reset to default" link didn't work
* BUGFIX: Autologin warning on login block widget in full page reverse integration
* CHANGE/BUGFIX: username & e-mail validation more reliable
* UPDATED: Specify index.php for forum link in admin bar in case index.php is not default served page

v0.9.1.4 RELEASE CANDIDATE 3

* BUGFIX: WordPress users created in user mapper set to administrators. if you have created WordPress users using the user mapper previously, please check to ensure they are not administrators.
* BUGFIX: WordPress register date now showing correctly in user mapper and minor mapper display fixes
* BUGFIX: improper context switching in user mapper causing error with W3 Total Cache
* BUGFIX: phpBB normal ranks not showing for users in user mapper
* BUGFIX: minor avatar notice
* BUGFIX: Incorrect "forgot password" link in user login block widget
* NEW: integrated links in WP menu bar (admin-bar)
* Code cleanup and improved code documentation. Developers/hackers look at wp-united.php to get started.

v0.9.1.0/2/3 RELEASE CANDIDATE 3

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

v0.9.0.3 RELEASE CANDIDATE 3

* NEW: Quick user search box added to user mapper
* NEW: Synchronize profiles user mapper button and bulk action
* UPDATED: User mapper allows processing up to 250 users at once
* UPDATED: Don't repaginate user mapper after processing actions
* UPDATED: More permissive in returned messages from server when connecting and enabling (fixes errors on servers where files have leading or trailing garbage).
* BUGFIX: User mapper dying on entities in usernames
* BUGFIX: User mapper not displaying user names in alphabetical order when phpBB was on left
* BUGFIX: Load version when WP-United is disabled so things like "Get Help" still work.
* BUGFIX: Rank images had incorrect URLs

v0.9.0.2 RELEASE CANDIDATE 3

- NEW: New widget: Useful forum links
- BUGFIX: Explicitly disable WP admin bar on simple header & footer phpBB-in-WordPress pages
- BUGFIX: Link to post not displaying in cross-post
- BUGFIX: Cross-post box not honouring excerpt/fullpost/askme choice

v0.9.0/1 RELEASE CANDIDATE 3
- WP-United is, for the most part, completely rewritten, to improve flexibility and compatibility
- The vast majority of WP-United now sits under WordPress rather than phpBB. Find plugin, click install, done... that's what the aim is.
- Modular -- now only hooks and files that are needed are loaded
- Brand new, modern, admin panel
--- A completely re-imagined settings panel, with modern UI and with most options significantly simplified.
--- No more Wizards!
--- Panel comunicates with phpBB asynchronously -- no more blank pages!
--- New interactive user mapper that can integate, break integrations, create, delete and edit users in a few clicks
--- New draggable, connectable permissions mapper to hide the arcane phpBB permissions UI
--- Less options, more sensible defaults, all in one place
- Completely re-written user integration
--- User integration is now bi-directional. Log in or register in WordPress or phpBB, and seamlessly access the site
--- Roles are now set at user creation time, not every visit -- much more flexible
--- Bi-directional profile sync. Update your profile anywhere and it works
--- Auto-synced avatars. Get your Gravatar in phpBB -- without having to anything
--- Designed to work with external auth providers. e.g. click the Facebook button in the oneall plugin, and you get both a phpBB and WordPress account
- Widgets all ported to new WordPress API
- Widgets now colour phpBB usernames according to default group colours
- Numerous bugs addressed
- User blogs has been removed. It will be added back, working with WP-MS, in the next release.
- Translations all moved to WordPress


v0.8.5 RELEASE CANDIDATE 2
- Fixed plugin fixer not covering all global variables
- Fixed plugin fixer not working with plugins tha use PHP short tags
- Suppress errors during path detection, etc, for people with open_basedir restrictions
- Fixed: If forum and blog are both in root directory, add explicit "index.php" to the forum link
- Added more classes to the login / userinfo widget to facilitate styling.
- New option: cross-post excerpts or full posts. Three choices: Excerpt / full post / ask each time
- Removed inline JavaScript for smilies. All WP-United JS moved to wp-united/js/wpu-min.js
- Fixed tags/categories not showing up properly on cross-posted posts
- show "cross-post" in past tense in force xpost box if already xposted.
- added two new options, SHOW_BLOG_LINK, and WPU_INTEG_DEFAULT_STYLE -- full info in options.php
- Fixed problem links in reverse integration, e.g. on phpBB FAQ page
- fixed uncategorized checked when selecting cross-posting
- changed <br /> and <p> BBCode translations to \n rather than [br]
- Stopped allowing submission of blank cross-posted comments
- Fixed smiley path in cross-posted comments sometimes not correct
- User mapper is now case insensitive when looking for suggested phpBB username matches
- Added new option, WPU_SHOW_TAGCATS, to suppress display of categories & tags in cross-posts
- Fixed avatar and other details not synced when deleted by user in UCP
- Added option in options.php to define templates on which the WordPress header/footer should not appear, and pre-filled it with some shoutboxes.
- Installer auto-purges cache again when finished to ensure WP-United tab appears

v0.8.4.1 RELEASE CANDIDATE 2

- NEW: wpu-install.php removed, replaced by auto-installer
- Fixed comments closed for global announcements when they shouldn't be.
- Fixed unreliable display of comment date/time
- Added better error catching for people who don't install the mod properly (e.g. if they activate the plugin without installing/setting up, or run it without the plugin)
- Fixed various unfriendly error messages
- Fixed timezone of cross-posts
- Improved BBCode & magic URL checking, stopped double-pass of make clickable
- Fixed errors with user blogs list, and improved login_userinfo
- Fixed username incorrect when editing cross-posted posts
- Fixed integration error message after updating password or username under some circumstances
- ... and several other minor bug fixes

v0.8.3 RELEASE CANDIDATE 2
- Slight cleanup of wp-integration-class.php 
- Cleaned up notices
- Fixed plugin fixes sometimes move fixed include path onto comment line
- Fixed comments should not try to load if $post is blank
- Fixed default WP avatar broken
- Fixed avatar syncing for integrated users
- Fixed RSS feeds not showing correctly for WordPress-in-phpBB
- Fixed comment  links on non-cross-posted comments
- Prevented phpBB from trying to do gzip compression twice.
- Suppressed PHP timezone warnings under PHP 5.3 by setting default timezone in phpBB, since WP does it anyway
- Editing cross-posts that have become sticky or announcements now preserves their status

v0.8.2 RELEASE CANDIDATE 2
- New Swedish translation (Thanks Oz)
- Fixed broken template integration when viewing custom fields, attachments, etc.
- Fixed missing sid on some  integrated logout links, fixed login link on comments page
- Fixed board does not display correctly in some cases when Gzip compresssion is on
- Improved reliability of insertion of smilies in WordPress
- Fixed error on RSS feed page when feeds disabled
- Fix for some styles and BBCodes with height attribute under CSS Magic
- Added default options to login/user info template tag
- Changed behaviour when board disabled: blog.php always calls wp_die when disabled, as there is no other reliale way to integrate templates or pull info
- Fixed CSS Magic not resolving image URLs in stylesheets under some circumstances
- Improved comments_open and comment_registration behaviour for cross-posted comments
- Redirect back to WP, if possible, after cross-posting comments
- Cross-posted comment title and Edit links now point to forum
- Fixed bugs due to missing $template when register_globals is on
- Fixed htmlspecialchars_decode error on comment posting
- Fixed time zone of cross-posts
- Improved plugin fixes -- they now enter plugin subdirectories under more circumstances
- Fixed default avatar options in built-in avatars. 
- Added error handler arbitrator to suppress notices in WordPress. 
- Improved Debug Info To Post display
- CSS Magic Style Keys are now purged with the cache
- Many other minor bug fixes

v0.8.1 RELEASE CANDIDATE 2
- Fixed error preventing integrated users from commenting on WordPress posts
- Fixed errors with forum posts and users online widgets
- cross-posted posts that later become global announcements can now be edited/commented on
- Fixed broken redirects and login links that occur in some circumstances
- fixed gzip handler in reverse forum integration
- Added WordPress user ID to cross-posted comment metadata so that avatar tags, etc. can work
- fixed error messages not being suppressed in installer backend
- Fixed ACP errors on user mapping, uninstall and donate pages
- Fixed template cache not blocked, preventing template showing on profile update, on simple reverse integration
- Fixed missing $template object on theme change in WP dashboard
- Many other minor bug fixes 

v0.8.0 RELEASE CANDIDATE 2
-- This is the biggest, most changed release yet, with new features, and the majority of the code 
completely rewritten.
-- Complete rewrite of main integration code for extensibility and clarity, all code refactored
-- NEW: CSS Magic and Template Voodoo automatically correct style conflicts in template integration
-- NEW: Automatic plugin workarounds fix problem plugins and themes
-- NEW: Cache class handles caching for various calculations and template modifications
-- NEW: WP-United Connection installed in background over AJAX panel to catch errors
-- NEW: phpBB hook system used to reduce code edits and improve extensibility
-- NEW: WP-United cache purged whenever phpBB cache is
-- NEW: Improved cross-posting, with ability to force all posts to be cross-posted to a particular forum
-- NEW: Cross-posted comments from cross-posted phpBB topic and WordPress comment form
-- NEW: Cross-posting scheduled and old posts works, and shows the correct time and user info
-- Big cleanup of widgets to remove unneeded markup and options
-- NEW: Users online widget
-- NEW: WP-United now supports forum & blog installation on different subdomains
-- FIXED: template integration does not honour right-to-left layouts
-- NEW forum page automatically created in WordPress to provide easy link to forum
-- FIXED: full page reverse template integration works with new forum page to display better
-- NEW: option in options.php to completely disable WP-United to prevent lock-outs
-- FIXED: user mapping not creating phpBB users from WordPress users properly
-- FIXED: login inaccessible when board is disabled
-- FIXED: Editing posts using quick Edit now updates the cross-posted post too
-- Profile fields and user-editing in dashboard now works properly (as it was before it was removed in v0.7.1)
-- FIXED: Login debug didn't always show up (when requested)
-- Removed unnecessary ACP options from Setup Wizard
-- NEW plugin implementation, phpBB is always available
-- NEW phpBB abstraction for use whenever in WP ($phpbbForum)
-- Massive cleanup of ACP to remove phpBB2 compatibility
-- NEW: wpu-install.php uses phpBB template system and enforces access control
-- NEW: Fixed with WP 2.9
-- FIXED: install.xml broken finds & broken subSilver finds
-- NEW: All language strings in WordPress can now be translated in phpBB in one place
-- NEW: wpuAbs abstraction class removed
-- FIXED: Missing quick links in WordPress dashboard
-- And much, much more...


v0.7.1 (STILL RELEASE CANDIDATE 1!)
-- NEW: Auto-linking of cross-posted posts' comments to the forum.
-- FIXED: (regression) List of forums with cross-posting permissions not correct in wp-admin
-- FIXED: posts not cross-posting if post is first saved as a draft
-- FIXED: Couple of mistakes in install.xml causing errors in AutoMod
-- FIXED: Regression in session invocation was causing autologin to stop workig with some setups
-- FIXED: Some plugins caused widgets to be executed in wp-admin, where wp-united widgets aren't designed to run, causing errors.


v0.7.0 RELEASE CANDIDATE 1
-- NEW: Updated to work reliably with WordPress 2.7 & 2.8
-- UPDATED: Login integration updated to work properly with new phpBB & WordPress password schemes. Third-party apps can still access WordPress
-- UPDATED: Cache mechanism upgraded -- all methods of integration are now cached properly, and the cache is automatically updated when Wp-United or WordPress is upgraded. This fixes 99% of reported eval'd code errors
-- NEW: phpBB avatars automatically used in WordPress templates (thanks to Japgalaxy)
-- NEW: phpBB smilies can be used in WordPress posts and comments (Japgalaxy again)
-- NEW: phpBB header is automatically removed on phpBB-in-wordpress pages, with the phpBB searchbox automatically spliced into the WordPress header
-- NEW: Improved user mapping tool now allows selection of items per page, and includes several useful quick links for bulk administration
-- NEW: Italian translation (thanks to Japgalaxy)
-- NEW: Install wizard tries to auto-copy wpu-plugin.php, and tells the user exactly what to do if it can't
-- NEW: WP-United ACP area now checks for wp-united cache writability, and checks that wpu-install has been deleted
-- UPDATED: ProPress theme updated for WP 2.7+
-- UPDATED: Improved one-click debugging information for posting to forums in support requests.
-- UPDATED: Install files and Setup Wizard contain more useful install instructions
-- FIXED: Users were not being logged out of WordPress until they visited the blog again
-- FIXED: Various cookie problems caused users to be logged out of phpBB when in wp-admin
-- FIXED: Install Wizard sometimes crashed when users are given own blogs
-- FIXED: WordPress admin panels for users own blogs improved
-- FiXED: When users are integrated, access to wp-login.php is disabled
-- FIXED: Various third-party provided widgets were not compatible with all setups
-- FIXED: Only show cross-posting box when user has ability to cross-post
-- FIXED: Non-simple reverse integration option now behaves a little better. 'Simple' is still recommended.
-- MOD repackaged in accordance with MODX 1.2.1 format guidelines
-- Any many other minor fixes and clean-ups


v0.6.1 RELEASE CANDIDATE 1
This was an unofficial release by the community. For changelog, please see here:
http://www.nitemarecafe.com/2009/02/20/wp-united-061-rc-1-released/

v0.6.0 RELEASE CANDIDATE 1

-- Updated to work with phpBB3 RC8 & WordPress 2.3.1
-- NEW: Cross-posting of blog posts to forums, complete with auto html-to-BBCode conversion. Cross posting drop-down controlled by phpBB3 per-forum permissions.
-- Fixed new user link problem in forum stats widget link
-- Fixed error in German install file
-- Fixed problem with user profile links not getting created
-- Fixed 'auto redirects when phpBB in wordpress not working'
-- Fixed phpBB2/3 abstracting errors and user class problems
-- Fixed encoding problem in wpu-plugin.php
-- ...and many other fixes and improvements.


v0.5.5
-- Fixed remaining login & register links -- they should also now redirect to the correct place.
-- Fixed 'can't convert to string' PHP errors due to query vars being pre-set.
-- Fixed bug affecting avatar upload from ACP
-- Fixed a couple of PHP notices that appear under Debug Mode when WP-United isn't yet set up.
-- Added German & Spanish translations (thanks psm & Raistlin!).
-- Added 'Quick debug info' page to the ACP to facilitate support requests/bug reports.

v0.5.1
-- Fixed: Some login & register links didn’t point to the right place when logins were itnegrated. This is now fixed (but redirection back to the page you were on will come later).
-- Fixed: function.php warnings appeared at the top of the page for some templates.
-- Fixed: Incorrect path to /includes/acp in the install MOD file.

v0.5.0 (FIRST BETA / PUBLIC RELEASE)
-- Feature parity with phpBB2 version, but with the following additions:
-- Improved DTD switching
-- CSS order can be set regardless of integration direction
-- Automatic fix for templates like Ocadia & Almost Spring
-- Included "compatibility" CSS
-- Uninstaller & resetter
-- Better user mapper
-- phpBB3 native permissions system used
-- A ton of bug fixes


**** phpBB2 releases: ****

v0.9.5 (LAST BETA -- MAJOR RELEASE)
-- WordPress 2.2 compatibility!
-- A new permissions system -- allowing you to assign WordPress roles to phpBB groups. This effectively allows you to pick and choose which users get integrated.
-- A new debug system, so you can see what is going on when logins are integrated.
-- Work on the WordPress admin panel -- put the WordPress "users" tab back -- but modified/redirected so it makes sense with wp-united 
-- WordPress core code modifications that were made on the fly are now cached to reduce resources (especially memory) usage.
-- WP-United Connection installer works better with aliases/SymLinks as paths.
-- WordPress cookie path can be overridden  to '/' in options.php if you have weird cookie issues, say, due to mod_rewrite.
-- Added debug information to Wizard Step 5 when it fails to enable easier/quicker support of common install problems.
-- When WP posts are edited by a mod or admin, they no longer get attributed to that person on the Blos Home page
-- Error fixed in wp-functions.php that could result in unhandled error.
-- Fixed incorrect link to most recent member's profile in forum stats func / widget.
-- Fixed a few bugs with the ranks template tags.
-- Fixed error in registration link in {GET-STARTED} autotext.
-- The only major feature missing at this stage is per-user categories and blogroll.


v0.9.2


 BETA (MAJOR RELEASE)

- rewrite of integration class to put WordPress in the global scope (this will improve plugin compatibility and speed. integration falls back to 'compatibility mode' when WordPress cannot be called in the global scope)
- syncing of main integration files with phpBB3 version
- NEW: cache system for phpbb inside the WordPress header/footer. Increases performance.
- NEW: You can set the integrated blogs as 'private' -- users that are not logged in will be redirected to the login page.
- NEW: Redirection on login -- if a user clicks on a login/out link in the blog, they are returned to the blog homepage, not the forum index.
- NEW: New template tags for phpBB username, phpBB rank title and images, forum stats, forum posts since your last visit, recent forum topics, 'most recently updated blogs' and 'most recent posts in blogs'.
- NEW: Widgets!  Widgets for most of the above, including a user info block with login form!
- NEW: A more robust mapping tool that can also create phpBB users.
- removed some of the lesser-used setup options by choosing sensible defaults, and moving them to an options.php file, to simplify the installation process.
- FIXED: duplicate </body> & </head> tags in reverse integration
- FIXED: improper output when php short tags are not available
- FIXED: errors on updating profile when phpBB is inside WordPress


v0.9.0 BETA (MAJOR RELEASE)
-- NEW: template integration: You can choose to have phpBB appear inside a WordPress header/footer, or inside a full WordPress page, with sidebar etc.
-- NEW: You can set the order of CSS files (i.e. phpBB first, or WordPress first -- easy way to correct conflicting styles, the latter overrides the previous)
-- FIXED: Problems with K2 & widgets. Future versions of WP-United will be even more compatible with add-ons.
-- NEW: user integration scheme rewritten to be more flexible, more robust and faster
-- NEW: migration tool to convert existing installs to the new sceheme is working
-- NEW: mapping between phpBB & WordPress users can be manually adjusted
-- NEW: "author" users can only see their own uploads.
-- NEW: Each blog user gets their own upload folder (i.e. uploads/Jhong or uploads/Jhong/yyyy/mm)
-- NEW: Better/clearer template tags, with documentation
-- FIXED: problem with phpBB word censor when database is not shared
-- FIXED: Blog buttons in profiles didn't show up for users using the upgrade mod files
-- NEW: RSS author feeds appear replace the normal RSS feeds when users can have their own blogs
-- Less changes are needed to phpBB files
-- Better handling of 'make clickable' -- phpBB's Make Clickable function is called when in phpBB, and WordPress' Make Clickable is called when in WordPress! (still no changes to any WP files required).
-- Better error handling/messages (still need to localise)
-- Added easier method to switch between WP & phpBB databases makes it easier for devs to code add-ons ( e.g. $wpuInt->switch_db('TO_P') gets you back to phpBB).
-- FIXED: Blank page in Setup Wizard if blogs home page turned on
-- FIXED: Multiple blogs home pages can be created by accident
-- Better prompts added to Setup Wizard and options page for optional items

v0.8.9.1b BETA
-- ADDRESSED: phpBB tries to create invalid usernames in WordPress, resulting in duplicated entries in the users table.

v0.8.9.1 BETA
-- FIXED: Message doesn't display after profile update
-- FIXED: Blog name doesn't always show up in admin panel
-- FIXED: Blogs home page list shows unexpected number of blogs
-- FIXED: Login / register links pointed to the wrong place when the page template was not integrated
-- FIXED: Per-user templates/styles don't show up properly in single post view or when using Permalinks on PHP ver < 5


v0.8.9.0 BETA (MAJOR RELEASE)
-- All RSS feeds now really work
-- Author details now checked and updated when they are changed in phpBB, not every time blog.php is visited
-- FIXED: Fatal error in footer of Wizard Step 5
-- FIXED: path/uri tests in Setup Wizard fail without warning when cURL is unavaulable (thanks Paul999!)
-- WP-United Connection still installs when case differences exist in pathnames on Windows servers
-- Check added to prevent path detection from working when a URL is provided
-- NEW: Templates automatically re-jigged to suit per-user blogs
-- User blog homepages now use the home template (previously was Author Archive)
-- Prev/Next posts links only lead to posts by the same author when users can have own blogs
-- Link in template header automatically re-pointed to the right place
-- NEW: A fancy list showing details of each users' blogs can be automatically installed as a homepage
-- NEW: Built-in method provided to put latest WordPress posts on an external page. Example add-on included for Smartor's ezPortal.
-- NEW: Tags provided so you can put avatars in your WordPress templates: wpu_get_avatar_commenter, wpu_avatar_poster & wpu_avatar_user
-- NEW: WordPress posts (including excerpts & titles) can be optionally passed through the phpBB word censor
-- FIXED: WordPress database error on new post when certain options are set
-- NEW: Version numbering moved to database to allow for automatic version checking/updates
-- NEW: "View Site" link in wp-admin points to the user's blog when users can have own blogs.
-- NEW: Flexible access point to integrated WordPress -- rename blog.php and put it anywhere, with no fuss
-- NEW: if non-integrated WordPress is accessed, it redirects to the integrated entry point

v0.8.8.7 BETA
-- FIXED: WP-United Connection now installs properly under IIS
-- Better and more reliable handling of embedded RSS feeds in an integrated page
-- Better auto-setting of page basename
-- FIXED: trailing slash after blog.php no longer causes a 404 error when permalinks are turned on
-- improved detection of WordPress styles and scripts that need to be included in an integrated page
-- superfluous full-stop removed from mod file (thanks Georgie!)

v0.8.8.6 BETA
-- NEW: Login/logout links hard-coded into templates are now fixed when logins are integrated
-- _All_ RSS feeds now work, even when WordPress is inside the phpBB header/footer

v0.8.8.5 BETA
-- Now REALLY works with WP2.1:
	  -- FIXED: "Page not found" errors under PHP4 fixed!
	  -- FIXED: Errors when changing WP-United blog settings (errors may also have occurred on IIS in WP 2.0.x)
-- FIXED: trailing slashes in addresses cause 404 error.

v0.8.8.3 BETA
-- Improved (again) setting of Blog URI (now includes http:)
-- FIXED: Non-fatal error when installing WP-United Connection on a new WordPress install
-- FIXED: object error in WordPress 2.1 -- This mod is now compatible with WordPress 2.1!

v0.8.8.2 BETA
-- Parse error really fixed now ;-)
-- FIXED: Fatal error when the WP-United Connection can't be installed

v0.8.8.1 BETA
-- Parse error in PHP4 fixed
-- Better auto-setting of Blog URI (now the fully qualified path is set)
-- traling slash after blog.php added by templates results in 404 error - trailing slash removed

v0.8.8.0 BETA
-- MAJOR RELEASE -- Several major new features, and hopefully no new bugs:
-- NOTE: This mod is now rather large in size. We recommend installation with EasyMod to prevent installation errors.
-- Users can set up their own blogs - with user defined titles, descriptions and themes
-- user theme switcher to set per-user themes
-- New automagically-installed "WP-United Connection" adds a nubmer of new WordPress admin panel features
-- New Admin panel prompts. Admin panel features no longer needed (e.g., profile, which is handled by phpBB), are removed.
-- Blog buttons in posts and in profile that link directly to users' blog posts.
-- WordPress login/register links are automatically altered to point to the phpBB equivalents
-- Two new Setup Wizard pages to fine-tune options
-- Sample template to help with per-user blog integration
-- FIXED: "Duplicate function declaration detected..." message addressed
-- FIXED: phpBB variables made global while WordPress is running are unset when WordPress hands back to phpBB
-- Option to set site-wide cookie included (remove commented out line in wp-integration-class), to fix problems with Apache rewriting preventing admin panel access.
-- still no changes required to any WordPress files! No need to even install a plugin...
(-- Tested with WP PermaLinks. Works a treat - but you will need to manually create a .htaccess file in your blog.php folder)
	
v0.8.7.2 BETA
-- FIXED: Setup Wizard failed when MySQL5 was runnning in 'strict' mode.

v0.8.7.1 BETA
-- FIXED: Some of the New Setup Wizard izard options were missing from the settings page.
-- FIXED: In the Wizard, if JavaScript is disabled, when running the setup tests, some options typed in text boxes were not remembered when the page refreshed.
-- FIXED: Incorrect version number in Admin Panel
-- ADDED: Now, no changes are needed to ANY WordPress core files!

v0.8.7.0 BETA
-- MAJOR RELEASE -- Includes a major code refactoring, and a name change. Note that wp-integration-class is still considered 'ALPHA' and will be subject to performance improvements.
-- NEW: Now, no changes are required to your WordPress install!
-- NEW: Improved administration interface, including a wizard and tools to automatically detect WordPress settings
-- NEW: The ability to turn off the phpBB header & footer
-- FIXED: For integrated logins, did not check if that user exists in WordPress when a new user registers (not a problem for the majority of integrations). 
-- REMOVED: Automatic font-resizing for integrated blogs. Not necessary any longer, was making the text size too small
-- TODO: Users are not yet deleted from WP when deleted from phpBB.

v0.8.5.8 BETA
-- FIXED: Log-out didn't work on some database servers. 
-- FIXED: When users change their usernames in phpBB, their WordPress usernames are now updated.
-- NEW:   WordPress roles can be mapped to phpBB user levels. Using this, login integration can also be turned off for certain user levels.
-- NEW:   DTD switching. You can choose for the WordPress page to have an XHTML DTD, without affecting the standard HTML4 DTD for other phpBB pages. 

	v0.8.5.5 BETA
-- FIXED: This mod file was a bit screwed up. Now works again in EM 0.3.
-- ADDED: when users log out of phpBB, they are logged out of WP. When users change username in phpBB, their username is automatically updated in WP
-- FIXED: blank and/or duplicate users showing up in WP sers table

v0.8.5.0 ALPHA
-- FIXED: When new WP users were created, the page had to be refreshed before they could log in.

v0.8.5.0	ALPHA
-- NEW: Integrated login.

v0.8.2.1	ALPHA
-- FIXED: All phpBB variables are now restored to their original state after WordPress has finished executing.

v0.8.1.1 ALPHA 
-- FIXED: Several file / copy lines missing in MOD file.
	
v0.8.1 ALPHA
-- NEW: auto path discovery altered to also work on Windows servers
-- FIXED: phpBB's $userdata was being overwritten by WordPress but problems only manifested on one machine.
