<?php
/** 
*
* WP-United Extra Options
*
* @package WP-United
* @version $Id: v0.8.0 2009/12/21 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/


/**
 * This seciton is for security. Do not modify this part:
 * @ignore
 */
if ( !defined('IN_PHPBB') ) {
	exit;
}

//The options you can set begin below:

/**
 * (TEMPORARILY) DISABLE WP-UNITED?
 * This is useful if you have locked yourself out of your forum -- for example, if you have deleted 
 * your WordPress wpu-plugin, but have template integration on, you might not be able to see
 * your forum.
 * 
 * Temporarily change this to TRUE to completely disable the integration so that you can log in and
 * get to the ACP. 
 * 
 * To permanently remove WP-United, use the uninstall option in the ACP, not this.
 */
define('WPU_DISABLE', FALSE);


/**
 * USE TEMPLATE CACHE?
 * The template cache is only used when you use the 'phpBB inside WordPress' template integraqtion in 'simple' mode. 
 * It SIGNIFICANTLY improves page generation time, as WordPress no longer needs to be invoked on phpBB pages just to get a header and footer.
 * However, if you have dynamic elements in your header or footer, then you will want to keep this option off.
 * To turn it on, change FALSE to TRUE.
 */
define('WPU_CACHE_ENABLED', TRUE);



/**
 * USE WORDPRESS CORE CACHE?
 * When invoking WordPress, WP-United reads the WordPress core code and makes some minor changes to ensure compatibility.
 * With this option turned on, this changed core code is cached to reduce processor and memory load (generation time isn't affected much).
 * There should be no reason to turn this off, since this core code should never change, and does not need to be prepared each time. 
 * If you are receiving unknown PHP errors and think this might be the cause, you can turn it off to aid in debugging.
 */
define('WPU_CORE_CACHE_ENABLED', TRUE);


/**
 * COMPRESS HTML OUTPUT FURTHER?
 * This section does a bit of minor extra HTML compression by stripping white space, at the expense of a little processing time.
 * Doesn't yield much (about 5% reduction at the very most) if gzip is on. If gzip is off, it makes a significant difference. 
 * However, it CAN break some page output, especially uncompressed JavaScript in the page head. 
 * It's off by default, but you could turn it on by changing FALSE to TRUE.
 * Note that this will make your page source a little harder to read -- andis therefore not recommended if you're still building and debugging your site.
 */
define('WPU_MAX_COMPRESS', FALSE);


/**
 * ENABLE LOGIN INTEGRATION DEBUG MODE?
 * Enabling the below option displays debug information that could be useful in tracking down problems with integrated logins
 * It should be left off on a production site!
 */
define('WPU_DEBUG', TRUE);

/**
 * OVERRIDE WORDPRESS SITE COOKIE PATH?
 * This sets the WordPress cookie path to '/'.
 * Could be useful if your WordPress base install is in a path that is rewritten by Apache mod_rewrite, but most users will be fine if they leave this off.
 */
define('WP_ROOT_COOKIE', FALSE);


/**
 * SHOW PAGE STATISTICS?
 * Turn this option on to see the WP-United execution time and memory footprint.
 * WP-United execution time is the time spent by WP-United doing integration, and includes
 * WordPress run time, but not necessarily PHP run time.
 * This is a good way to gauge how various options affect server load.
 * It should be left OFF on production servers.
 */
define('WPU_SHOW_STATS', TRUE);

/**
 *  phpBB CSS?
 * These options control whether phpBB CSS is displayed, and whether it comes before
 * or after WordPress CSS.
 * Unless you specifically want to disable all phpBB styles, or change the order, leave these at their
 * default settings
 * If you change these, you my need to purge the cache for them to take effect properly.
 */
define('DISABLE_PHPBB_CSS', FALSE);
define('PHPBB_CSS_FIRST', TRUE);


?>
