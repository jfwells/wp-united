<?php
/** 
*
* WP-United Main Integration File -- where the magic happens
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*

This is the third incarnation of the main integration file (was wp-united.php, 
then wordpress-entry-point.php). It has been rewritten/refactored from scratch to clarify the code flow
for various different types of integration.

This file can be invoked in a few ways:
(a) via blog.php. This means we should show WordPress, and do user integration, etc. if required. If needed, we also
need to prepare the phpBB header and footer

(b) as a callback from hook_wp-united.php. This means the user has turned on "reverse integration" -- the phpBB page
has already been prepared, and phpBB has exited. All the page contents are stored in $innerContent. We need to run WordPress,
or pull stuff from our WordPress header/footer cache as necessary
if(empty $wpSettings)

(c) via blog.php using cURL from latest-posts.php or similar. This is used for grabbing latest blog data for, e.g. integration
into a separate portal page block. This functionality is not advertised (it is a throwback to phpBB2), but it still works.

(d) via an xmlHttpRequest. This is similar to (c), but it just requires us to login an integrated user silently, and return status via XML.
This is not implemented yet, but will be added in a future release. The request will need to come in via another file, which cleanses the request.

This is largely procedural code, by necessity.

*/


/**
 * Initialisation
 */
// Prevent direct accesses
if ( !defined('IN_PHPBB') ) exit;

// Set a define to inform people that we are WP-United -- WP plugin authors, etc. might find this useful.
// It is also another way to test that things are running OK from the WordPress side.
define('WP_UNITED_ENTRY', 1);

// There are several variables we need to have around in the global scope. We only need to
// do this if we are being called from a function, but for convenience, we just do it anyway
global $wpSettings, $user, $userdata, $wpuNoHead, $wpUtdInt, $template, $module, $latest, $wpu_page_title, $wp_version, $lDebug, $wpuPluginFixer, $phpbbForum;
global $innerHeadInfo, $innerContent;
global $db, $config, $user, $auth, $config, $template;
$lDebug = '';

// Start measuring script execution time
global $wpuScriptTime;
if(isset($GLOBALS['starttime'])) {
	$wpuScriptTime = $GLOBALS['starttime'];
} else {
	$wpuScriptTime = explode(' ', microtime());
	$wpuScriptTime = $starttime[1] + $starttime[0];
}


// More required files
require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);
require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);


//Initialise the cache
global $wpuCache;
$wpuCache = WPU_Cache::getInstance();


// This is another way for WP-United or WordPress elements to test if they are running in the global scope.
// They can test for $GLOBALS['amIGlobal']
$amIGlobal = true;

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

if ( !$wpuCache->use_template_cache() ) {
	require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
	$wpUtdInt = WPU_Integration::getInstance();

	//We really want WordPress to run in the global scope. So, our integration class really just prepares
	// a whole set of code to run, and passes it back to us for us to eval.
	if ($wpUtdInt->can_connect_to_wp()) {
		// This generates the code for all the preparatory steps -- cleans up the scope, and 
		// analyses and modifies WordPress core files as appropriate
		$wpUtdInt->enter_wp_integration();
				
		
		// This generates the code for integrating logins, synchronising user profiles, and managing WordPress permissions.
		// integrate_login handles whether logins should be integrated or not, so we can just call it without checking.
		if (!$latest && !defined('WPU_BOARD_DISABLED')) {
			$wpUtdInt->integrate_login();
		} 
		
		// We pass the name of the variable to populate as a string to be added to the invoked code.
		$wpUtdInt->get_wp_page($wpContentVar);

		// finally do the integration, execute all the prepared code.	
		eval($wpUtdInt->exec()); 

		if(!isset($phpbbForum)) {
			wp_die($user->lang['WP_Not_Installed_Yet'] . ' (Error type: plugin missing)');
		}
		
		$connectSuccess = true;
		
	} else {
		trigger_error($user->lang['WP_Not_Installed_Yet']);
	}
}


if ( $useCache || $connectSuccess ) { 

	/**
	 * Generate the WP header/footer for phpBB-in-WordPress
	 */
	if ( defined('WPU_REVERSE_INTEGRATION') ) {

		//prevent WP 404 error
		if ( !$wpuCache->use_template_cache() ) {
			query_posts('showposts=1');
		}

		if ( !empty($wpSettings['wpSimpleHdr']) ) {
			//
			//	Simple header and footer
			//
			if ( !$wpuCache->use_template_cache() ) {
				//
				// Need to rebuld the cache
				//
				
				// some theme/plugin options and workarounds for reverse integration
				// inove -- no sidebar on simple page
				$GLOBALS['inove_nosidebar'] = true;
		
				ob_start();
				if(!empty($wpSettings['useForumPage'])) {
					// set the page query so that the forum page is selected if in header
					$forum_page_ID = get_option('wpu_set_forum');
					if(!empty($forum_page_ID)) {
						query_posts("showposts=1&page_id={$forum_page_ID}");
					}
				}
				get_header();
				$outerContent = ob_get_contents();
				ob_end_clean();
		
	
				$outerContent .= "<!--[**INNER_CONTENT**]-->";
				if ( $wpuCache->template_cache_enabled() ) {
					$outerContent .= "<!--cached-->";
				}				
				
				ob_start();
				get_footer();
				$outerContent .= ob_get_contents();
				ob_end_clean();
				
				if ( $wpuCache->template_cache_enabled() ) {
					$wpuCache->save_to_template_cache($wp_version, $outerContent);
				}
				
			} else {
				//
				// Just pull the header and footer from the cache
				//
				$outerContent = $wpuCache->get_from_template_cache();

			}
		} else {
			//
			//	Full WP page
			//
			define('PHPBB_CONTENT_ONLY', TRUE);
			global $phpbbForum;
			ob_start();
			
			if(!empty($wpSettings['useForumPage'])) {
				// set the page query so that the forum page is selected if in header
				$forum_page_ID = get_option('wpu_set_forum');
				if(!empty($forum_page_ID)) {
					query_posts("showposts=1&page_id={$forum_page_ID}"); 
				}
			}
	
			$wpTemplateFile = TEMPLATEPATH . '/' . strip_tags($wpSettings['wpPageName']);
			if ( !file_exists($wpTemplateFile) ) {
				$wpTemplateFile = TEMPLATEPATH . "/page.php";
				// Fall back to index.php for Classic template
				if(!file_exists($wpTemplateFile)) {
					$wpTemplateFile = TEMPLATEPATH . "/index.php";
				}
			}

			include($wpTemplateFile);
	
			$outerContent = ob_get_contents();
			ob_end_clean();

		}
		
		/*
		if ( !$wpuCache->use_template_cache() ) {
			$phpbbForum->leave();
			wp_reset_query();
			$phpbbForum->enter();
		} */
		
		if (!DISABLE_PHPBB_CSS && PHPBB_CSS_FIRST) {
			$outerContent = str_replace('</title>', '</title>' . "\n\n" . '<!--[**HEAD_MARKER**]-->', $outerContent);
		}

	}


	// clean up, go back to normal :-)
	if ( !$wpuCache->use_template_cache() ) {
		$wpUtdInt->exit_wp_integration();
		$wpUtdInt = null; unset ($wpUtdInt);
	}

}

require($phpbb_root_path . 'wp-united/template-integrator.' . $phpEx);

/**
 * Work-around for plugins that force exit.
 * Some plugins include an exit() command after outputting content.
 *
 *  In the Integration Class, we can try to detect these, and insert a wpu_complete()
 * prior to the exit(). 
 * 
 * This function tries to complete the remaining tasks as best possile so that
 * WordPress still appears inside the phpBB header/footer in these circumstances.
 */
function wpu_complete() {
	global $wpSettings, $user, $userdata, $wpuNoHead, $wpUtdInt, $phpbbForum, $template, $module, $latest, $wpu_page_title, $wp_version, $lDebug;
	global $innerHeadInfo, $innerContent;
	global $wpContentVar, $lDebug, $outerContent, $phpbb_root_path, $phpEx, $wpuCache, $config, $auth;
	
	$$wpContentVar = ob_get_contents();
	ob_end_clean();
	// clean up, go back to normal :-)
	if ( !$wpuCache->use_template_cache() ) {
		$wpUtdInt->exit_wp_integration();
		$wpUtdInt = null; unset ($wpUtdInt);
	}

	require($phpbb_root_path . 'wp-united/template-integrator.' . $phpEx);
}
	?>