WP-UNITED WordPress Integration Mod v0.9.5 RC1 for phpBB3
-----------------------------------------------
Last updated: 01 Deceber 2007, by John Wells

Check http://www.wp-united.com for latest versions, and for faster support

Thank you for downloading WP-United! This file introduces how to get up and running with your integrated phpBB & WordPress install.


BEFORE YOU BEGIN
------------------------

Make sure you have already installed phpBB and WordPress, and have verified that they are working OK. I recommend the latest versions - at this time of writing, that means phpBB3 RC2 & WordPress 2.2.1.

If these are not new installations, back up your phpBB & WordPress files and databases! WP-United is still in Beta - it could have bugs!

You have two options to install this mod. -- manually, by altering code in your phpBB files, or automatically, using EasyMod.



INSTALLING 
--------------

An install.xml file has been included. To read this properly, unpack the archive to your hard drive, and access the install.xml file from its unpacked location (it needs the accompanying .xsl file to display properly).

The  file will tell you which files to upload to your forum and where they should go, and will give instructions on which files to modify and how. 

Once you have completed those edite, look in the /templates folder, and apply the changes necessary for your installed themes. Some additional translations of user-facing strings have been provided in the /languages folder -- open up the .xml file for your language to install them.

If you need further help on what the directives mean, take a look at the phpBB forums -- http://www.phpbb.com/community/viewtopic.php?f=16&t=61611 . Make sure you back up your files before making any changes!

Once you have installed the mod, run the wpu-install.php file that you uploaded, and then delete it from your webspace. 

You should then be able to run the WP-United Setup Wizard, located under the WP-United tab in the phpBB Admin Control Panel.



UPGRADES
-------------

if possible, I will try to provide upgrade instructions for newer versions of WP-United. However, it is still in Beta and this cannot be guaranteed. Keep a copy of the install file for your records in case you need to refer to it in the future.



UNINSTALLING  (Noooo)
-----------------------------

We can't understand why you'd want to do this... but just in case, here goes: Uninstalling is easy. Just click the uninstall link in the Admin Control Panel. 


SUPPORT WP-UNITED   (*Please!*)
-------------------------------------------

Want to help improve the software? Or just want to put a smile on the developer's face? There are lots of ways you could help:
- Bug swatting -- Report any bugs you find on the forums. Anything, from typos to server crashes... let us have it!
- More Exposure -- Link back to www.wp-united.com, or post about WP-United on your blog. Want an interview? I'm available!
- Cash -- of the cold and hard variety. Every little bit helps. I don't have much...
- Translation -- translate this file and the language strings used in the mod. I'll put your name in lights (well, pixels) if you do.
- Cash --  Oh wait... 


**Thanks** To everyone who has donated so far.

HAPPY BLOGGING!
-John


CHANGE LOG
----------------

WP-United :: Public Releases

**** phpBB3 releases: ****

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
