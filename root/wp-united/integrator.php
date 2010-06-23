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
*/
global $useCache, $connectSuccess, $wpSettings, $wpuCache, $forum_page_ID, $phpbbForum;

global $wpSettings, $user, $userdata, $wpuNoHead, $wpUtdInt, $phpbbForum, $template, $module, $latest, $wpu_page_title, $wp_version, $lDebug;
global $innerHeadInfo, $innerContent;
global $wpContentVar, $lDebug, $outerContent, $phpbb_root_path, $phpEx, $wpuCache, $config, $auth;
	
if($connectSuccess) {
	
	$phpbbForum->leave();
	
	// get the page
	ob_start();
		if ( $GLOBALS['latest']) {
			define("WP_USE_THEMES", false);
		} else {
			define("WP_USE_THEMES", true);
		};
		global $wp_did_header; $wp_did_header = true;
		
		wp();
		if (!$latest ) {
			if (!defined('WPU_REVERSE_INTEGRATION')) {
				global $wpuNoHead, $wpSetngs;
				eval($wpUtdInt->fix_template_loader());
			} else {
				include($phpbb_root_path . 'wp-united/latest-posts.' . $phpEx);
			}
		}
		$wpContentVar = ob_get_contents();
		ob_end_clean();
	
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