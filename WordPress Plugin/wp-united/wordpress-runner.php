<?php

/** 
*
* WP-United Main Integration File
*
* @package WP-United
* @version $Id: v0.9.0RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*/



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


/**
 *  Run WordPress
 *  If this is phpBB-in-wordpress, we just need to get WordPress header & footer, and store them as "outer content"
 *  if a valid WordPress template cache is available, we just do that and don't need to run WordPress at all.
 */

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


		$wpUnited->set_ran_patched_wordpress();
	}
	
}



function wpu_initialise_wp() {
	global $phpbbForum, $wpUtdInt, $wpUnited;
	
	$phpbbForum->background();

	// get the page
	ob_start();
	
	// items usually set by wordpress template loads:
	define("WP_USE_THEMES", true);
	global $wp_did_header; $wp_did_header = true;

	wp();
	if (!$wpUnited->should_do_action('template-p-in-w')) {
		$wpUtdInt->load_template();
	}
	
	$content = ob_get_contents();
	ob_end_clean();
	
	return $content;
}







// WordPress ran inside phpBB, or we pulled a header/footer from the cache
// this was either to integrate templates, or to perform actions.
function wpu_get_wordpress() {
	global $wpUnited, $wpuCache, $phpbbForum, $wpUtdInt;

	// Initialise the loaded WP
	if($wpUnited->ran_patched_wordpress()) { // Wordpress ran inside phpBB
		$wpUnited->set_wp_content(wpu_initialise_wp());
	}	
	



	/**
	 * Generate the WP header/footer for phpBB-in-WordPress
	 */
	if ($wpUnited->should_do_action('template-p-in-w')) {

		//prevent WP 404 error
		if ( !$wpuCache->use_template_cache() ) {
			query_posts('showposts=1');
		}

		if ($wpUnited->get_setting('wpSimpleHdr')) {
			//
			//	Simple header and footer
			//
			if ( !$wpuCache->use_template_cache() ) {
				//
				// Need to rebuld the cache
				//
				
				// some theme/plugin options and workarounds for reverse integration
				// inove -- no sidebar on simple page
				global $inove_sidebar; $inove_sidebar = true;

		
				ob_start();
				if($wpUnited->get_setting('useForumPage')) {
					// set the page query so that the forum page is selected if in header
					$forum_page_ID = get_option('wpu_set_forum');
					if(!empty($forum_page_ID)) {
						query_posts("showposts=1&page_id={$forum_page_ID}");
					}
				}
				get_header();
				$wpUnited->set_outer_content(ob_get_contents());
				ob_end_clean();
		
	
				$wpUnited->set_outer_content($wpUnited->get_outer_content() . '<!--[**INNER_CONTENT**]-->');
				if ( $wpuCache->template_cache_enabled() ) {
					$wpUnited->set_outer_content($wpUnited->get_outer_content() . '<!--cached-->');
				}				
				
				ob_start();
				get_footer();
				$wpUnited->set_outer_content($wpUnited->get_outer_content() . ob_get_contents());
				ob_end_clean();
				
				if ( $wpuCache->template_cache_enabled() ) {
					$wpuCache->save_to_template_cache($wp_version, $wpUnited->get_outer_content());
				}
				
			} else { 
				//
				// Just pull the header and footer from the cache
				//
				$wpUnited->set_outer_content($wpuCache->get_from_template_cache());

			}
		} else {
			//
			//	Full WP page
			//
			define('PHPBB_CONTENT_ONLY', TRUE);

			ob_start();
			
			if($wpUnited->get_setting('useForumPage')) {
				// set the page query so that the forum page is selected if in header
				$forum_page_ID = get_option('wpu_set_forum');
				if(!empty($forum_page_ID)) {
					query_posts("showposts=1&page_id={$forum_page_ID}"); 
				}
			}
	
			$wpTemplateFile = TEMPLATEPATH . '/' . strip_tags($wpUnited->get_setting('wpPageName'));
			if ( !@file_exists($wpTemplateFile) ) {
				$wpTemplateFile = TEMPLATEPATH . "/page.php";
				// Fall back to index.php for Classic template
				if(!@file_exists($wpTemplateFile)) {
					$wpTemplateFile = TEMPLATEPATH . "/index.php";
				}
			}

			include($wpTemplateFile);
	
			$wpUnited->set_outer_content(ob_get_contents());
			ob_end_clean();

		}
		
		
		if (!DISABLE_PHPBB_CSS && PHPBB_CSS_FIRST) { 
			$wpUnited->set_outer_content(str_replace('</title>', '</title>' . "\n\n" . '<!--[**HEAD_MARKER**]-->', $wpUnited->get_outer_content()));
		}


		// clean up, go back to normal :-)
		if ( !$wpuCache->use_template_cache() ) {
			$wpUtdInt->exit_wp_integration();
			$wpUtdInt = null; unset ($wpUtdInt);
		}

	}
}

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
	global $wpUnited, $wpUtdInt, $wpuCache;
	
	$wpUnited->set_wp_content(ob_get_contents());
	ob_end_clean();
	// clean up, go back to normal :-)
	if ( !$wpuCache->use_template_cache() ) {
		$wpUtdInt->exit_wp_integration();
		$wpUtdInt = null; unset ($wpUtdInt);
	}

	require($wpUnited->get_plugin_path() .'template-integrator.php');
}



?>
