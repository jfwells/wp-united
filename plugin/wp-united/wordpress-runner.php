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
// is seriously screwed. @TODO: WE DON'T REALLY NEED THIS
if(!$wpUnited->is_enabled()) {
	trigger_error($user->lang['WP_Not_Installed_Yet']);
}

//Initialise the cache
require_once($wpUnited->get_plugin_path() . 'cache.php'); //@TODO: INIT THIS IN WP-UNITED CLASS
$wpuCache = WPU_Cache::getInstance();


// When PermaLinks are turned on, a trailing slash is added to the blog.php. Some templates also have trailing slashes hard-coded.
// This results in a single slash in PATH_INFO, which screws up WP_Query.
if ( isset($_SERVER['PATH_INFO']) ) {
	$_SERVER['PATH_INFO'] = ( $_SERVER['PATH_INFO'] == "/" ) ? '' : $_SERVER['PATH_INFO'];
}

// some WP pages don't want a phpBB header & footer (e.g. feeds). TODO: improve this check and move from wpUtdInt into a filter.
$wpuNoHead = false;

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
$wpContentVar = ($wpUnited->should_do_action('template-p-in-w')) ? 'outerContent' : 'innerContent';
$phpBBContentVar = ($wpUnited->should_do_action('template-p-in-w')) ? 'innerContent' : 'outerContent';
$connectSuccess = false;

if ( !$wpuCache->use_template_cache()) { 

	require_once($wpUnited->get_plugin_path() . 'core-patcher.php');
	$wpUtdInt = WPU_Core_Patcher::getInstance();

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

?>
