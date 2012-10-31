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


$amIGlobal = true;

// Our Mod Settings should have been loaded by now. If not, either WP-United hasn't been set up, or something
// is seriously screwed.
if  ( empty($wpSettings) ) {
	trigger_error($user->lang['WP_DBErr_Gen']);
} elseif ($wpSettings['wpPath'] == '') {
	trigger_error($user->lang['WP_Not_Installed_Yet']);
}

//Initialise the cache
require_once($wpSettings['wpPluginPath'] . 'cache.' . $phpEx);
$wpuCache = WPU_Cache::getInstance();

// redirect to login if not logged in and blogs are private
if ( (empty($user->data['is_registered'])) && ($wpSettings['mustLogin'])  && ($wpuIntegrationMode != 'template-p-in-w') ) {
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
 *  Run WordPress
 *  If this is phpBB-in-wordpress, we just need to get WordPress header & footer, and store them in $outerContent
 *  if a valid WordPress template cache is available, we just do that and don't need to run WordPress at all.
 *  If this is WordPress-in-phpBB, now we call WordPress too, but store it in $innerContent
 */
$wpContentVar = ($wpuIntegrationMode == 'template-p-in-w') ? 'outerContent' : 'innerContent';
$phpBBContentVar = ($wpuIntegrationMode == 'template-p-in-w') ? 'innerContent' : 'outerContent';
$connectSuccess = false;

if ( !$wpuCache->use_template_cache()  && !defined('WPU_FWD_INTEGRATION')) { 
//if ( !defined('WPU_FWD_INTEGRATION')) { 
	require_once($wpSettings['wpPluginPath'] . 'wp-integration-class.' . $phpEx);
	$wpUtdInt = WPU_Integration::getInstance();

	//We really want WordPress to run in the global scope. So, our integration class really just prepares
	// a whole set of code to run, and passes it back to us for us to eval.
	if ($wpUtdInt->can_connect_to_wp()) {
		// This generates the code for all the preparatory steps -- cleans up the scope, and 
		// analyses and modifies WordPress core files as appropriate
		$wpUtdInt->enter_wp_integration();

		eval($wpUtdInt->exec()); 


		$connectSuccess = true;
	}
	
}

// Load phpBB abstraction class if needed
if(!is_object($phpbbForum)) {
	require_once($wpSettings['wpPluginPath'] .  'phpbb.' . $phpEx);
	$phpbbForum = new WPU_Phpbb();
	$phpbbForum->foreground();
}

?>
