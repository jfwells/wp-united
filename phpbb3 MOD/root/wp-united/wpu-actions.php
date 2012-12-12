<?php

/** 
*
* WP-United Mod Edits
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* This is the file for accessing WordPress from inside phpBB pages. Most of this stuff is in flux, meaning that users had to constantly re-mod their phpBB.
* Moving them off into this file is intended to alleviate that. 
*/

/**
 */
if ( !defined('IN_PHPBB') ) {
	exit;
}

/**
 * A set of procedural actions called as "code edits" in phpBB. 
 * by abstracting all the edits into this file, only two-line additions need to be made to phpBB
 * core files
 */
class WPU_Actions {
	/**
	 * logs out of WordPress when the phpBB logout is called
	 */
	function do_logout() {
		return;
	}
	/**
	 * Updates the WordPress user profile when the phpBB profile is updated
	 */
	function profile_update($mode, $phpbb_id, $integration_id, $data) {
		return;

	}
	

	
	
	
	
	
	/**
	 * adds blog links to users' profiles.
	 */
	function generate_profile_link($bloglink_id, &$template) {
		global $wpUnited, $phpbb_root_path, $phpEx;
		
		if ($wpUnited->get_setting('buttonsProfile')) {
			if ( !empty($bloglink_id) ) {
				$blog_uri = append_sid($wpUnited->get_wp_home_url() . "?author=" . $bloglink_id);
				$blog_img = '';   //TODO: SET FOR SUBSILVER!!
				$template->assign_vars(array(
					'BLOG_IMG' 		=> $blog_img,
					'U_BLOG_LINK'		=> $blog_uri,
				));
			} else {
				$blog_img = '';
			}
		}
	}
	/**
	 * creates blog links for users' posts
	 * @todo set blog images for subSilver template
	 */
	function generate_viewtopic_link($bloglink_id, &$cache) { 
		global $wpUnited, $phpbb_root_path, $phpEx;
		if  ( isset($wpUnited) && $wpUnited->is_enabled() ) { 
			if ($wpUnited->get_setting('buttonsPost')) {
				if ((!isset($user_cache[$poster_id])) && !empty($bloglink_id)) {
					if ($poster_id == ANONYMOUS) {
						$cache['blog_img'] = '';
						$cache['blog_link'] = '';
					} else {
						$cache['blog_img'] = '';   //TODO: SET FOR SUBSILVER!!
						$cache['blog_link'] = append_sid($wpUnited->get_wp_home_url() . "?author=" . $bloglink_id);			
					}
				}
			}
		}	
	}
	 /**
	 * adds blog links to users' posts.
	 */
	function show_viewtopic_link($cache, &$postrow) {
		if (isset($cache['blog_link'])) {
			$postrow['BLOG_IMG'] = $cache['blog_img'];
			$postrow['U_BLOG_LINK'] = $cache['blog_link'];
		}		
	
	}
	 /**
	 * CSS Magic actions in style.php.
	 */	
	function css_magic($cssIn) {
		
		global $phpbb_root_path, $phpEx, $wpuCache, $wpUnited;
		define('WPU_STYLE_FIXER', true);
		require($phpbb_root_path . 'includes/hooks/hook_wp-united.' . $phpEx);

				
		if(!isset($wpUnited) || !$wpUnited->is_enabled()) {
			return $cssIn; 
		}
		
		require_once($wpUnited->get_plugin_path() . 'functions-css-magic.php');

		require_once($wpUnited->get_plugin_path() . 'cache.php');
		$wpuCache = WPU_Cache::getInstance();

		if(!isset($_GET['usecssm'])) {
			return $cssIn;
		}
		$pos = (request_var('pos', 'outer') == 'inner') ? 'inner' : 'outer';
		$cacheLocation = '';
		
		$cssIdentifier = request_var('cloc', 0);
		$cssIdentifier = $wpUnited->get_style_key($cssIdentifier);
		
		$useTV = -1;
		if(isset($_GET['tv']) && $pos == 'inner') { 
			$useTV = request_var('tv', -1);
		}
		
		
		/**
		 * First check cache
		 */
		$css = '';
		if($useTV > -1) {
			// template voodoo-modified CSS already cached?
			if($cacheLocation = $wpuCache->get_css_magic($cssIdentifier, $pos, $useTV)) {
				$css = @file_get_contents($cacheLocation);
			}
		} else {
			// Try loading CSS-magic-only CSS from cache
			if($cacheLocation = $wpuCache->get_css_magic($cssIdentifier, $pos, -1)) {
				$css = @file_get_contents($cacheLocation);
			}
		}		
		
		if(!empty($css)) {
			return $css;
		}
		
		// Apply or load css magic
		include($wpUnited->get_plugin_path() . 'css-magic.php');
		$cssMagic = CSS_Magic::getInstance();
		if(!$cssMagic->parseString($cssIn)) {
			return $cssIn;
		}
		
		// if pos= outer, we just need to cache the CSS so that Template Voodoo can get at it
		
		if($pos=='inner') { 
			// Apply Template Voodoo
			if($useTV > -1) {
				if(!apply_template_voodoo($cssMagic, $useTV)) {
					// set useTV to -1 so that cache name reflects that we weren't able to apply TemplateVoodoo
					$useTV = -1;
				}
			}	
			// Apply CSS Magic
			$cssMagic->makeSpecificByIdThenClass('wpucssmagic', false);
		}
		
		$css = $cssMagic->getCSS();
		$cssMagic->clear();
		
		//cache fixed CSS
		$wpuCache->save_css_magic($css, $cssIdentifier, $pos, $useTV);
		
		return $css;
	}
	
	
	/**
	 * Simple call to cache purge. We include it here so that phpBB core edits are static
	 */
	function purge_cache() {
		global $wpUnited, $phpEx;
		require_once($wpUnited->get_plugin_path() . 'cache.php');
		$wpuCache = WPU_Cache::getInstance();
		$wpuCache->purge();
	}
			
	
}

global $wpu_actions;
$wpu_actions = new WPU_Actions;
?>
