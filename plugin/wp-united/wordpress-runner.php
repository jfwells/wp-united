<?php

/** 
*
* WP-United Main Integration File -- where the magic happens
*
* @package WP-United
* @version $Id: v0.9.0RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*/


// More required files
require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);

$amIGlobal = true;

//Initialise the cache
$wpuCache = WPU_Cache::getInstance();

// Our Mod Settings should have been loaded by now. If not, either WP-United hasn't been set up, or something
// is seriously screwed.
if  ( $wpSettings == FALSE ) {
	trigger_error($user->lang['WP_DBErr_Gen']);
} elseif ( ($wpSettings['installLevel'] < 10) || ($wpSettings['wpUri'] == '') || ($wpSettings['wpPath'] == '') ) {
	trigger_error($user->lang['WP_Not_Installed_Yet']);
}


// redirect to login if not logged in and blogs are private
if ( (empty($user->data['is_registered'])) && ($wpSettings['mustLogin'])  && (!defined('WPU_REVERSE_INTEGRATION')) ) {
 redirect(append_sid('ucp.'.$phpEx.'?mode=login&amp;redirect=' . urlencode($_SERVER["REQUEST_URI"])));	
}


// When PermaLinks are turned on, a trailing slash is added to the blog.php. Some templates also have trailing slashes hard-coded.
// This results in a single slash in PATH_INFO, which screws up WP_Query.
if ( isset($_SERVER['PATH_INFO']) ) {
	$_SERVER['PATH_INFO'] = ( $_SERVER['PATH_INFO'] == "/" ) ? '' : $_SERVER['PATH_INFO'];
}

// If we have been called only to provide the latest posts via cURL, we won't want to do any integration
$wpuNoHead = false;
$latest = false;
if ( isset($HTTP_GET_VARS['latest']) ) {
	$latest = true; // run in latest posts mode, for showing latest posts on portal page, etc.
	$wpuNoHead = true;
}
// number of posts to show on portal page in latest posts mode
if ( isset($HTTP_GET_VARS['numposts']) ) {
	$postsToShow = (int) $HTTP_GET_VARS['numposts'];
	$postsToShow = ($postsToShow > 10) ? 10 : $postsToShow;
	$postsToShow = ($postsToShow < 1) ? 3 : $postsToShow;
}




/**
 * Initialise cache
 */
$useCache = false;
if ( defined('WPU_REVERSE_INTEGRATION') ) { 
	// If we're only using a simple WP header & footer, we don't bother with integrated login, and we can cache the wordpress parts of the page
	if ( !empty($wpSettings['wpSimpleHdr']) ) {
		// we also don't use the cache if WPU_PERFORM_ACTIONS is set, as we need to perform pending actions in WordPress anyway.
		if ( $wpuCache->template_cache_enabled() && !defined('WPU_PERFORM_ACTIONS') ) { 
			$useCache = true; 
			$wpuCache->use_template_cache();
		}
	}
}


/**
 * Run WordPress
 *  If this is phpBB-in-wordpress, we just need to get WordPress header & footer, and store them in $outerContent
 *  if a valid WordPress template cache is available, we just do that and don't need to run WordPress at all.
 * If this is WordPress-in-phpBB, now we call WordPress too, but store it in $innerContent
 */

$wpContentVar = (defined('WPU_REVERSE_INTEGRATION')) ? 'outerContent' : 'innerContent';
$phpBBContentVar = (defined('WPU_REVERSE_INTEGRATION')) ? 'innerContent' : 'outerContent';
$connectSuccess = false;


if ( !$wpuCache->use_template_cache()  && !defined('WPU_FWD_INTEGRATION')) { 
	require_once($wpSettings['wpPluginPath'] . 'wp-integration-class.' . $phpEx);
	$wpUtdInt = WPU_Integration::getInstance();

	//We really want WordPress to run in the global scope. So, our integration class really just prepares
	// a whole set of code to run, and passes it back to us for us to eval.
	if ($wpUtdInt->can_connect_to_wp()) {
		// This generates the code for all the preparatory steps -- cleans up the scope, and 
		// analyses and modifies WordPress core files as appropriate
		$wpUtdInt->enter_wp_integration();

		eval($wpUtdInt->exec()); 

		if(!isset($phpbbForum)) {
			wp_die($user->lang['WP_Not_Installed_Yet'] . ' (Error type: plugin missing)');
		}
		
		$connectSuccess = true;
	}
	

	// clean up, go back to normal :-)
	if ( !$wpuCache->use_template_cache() ) {
		$phpbbForum->enter();
	}




}




?>