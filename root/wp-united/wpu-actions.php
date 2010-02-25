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
		global $wpSettings, $phpbb_root_path, $phpEx, $wpUtdInt, $wpuCache;
		require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
		require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);		
		$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 
		if ( !empty($wpSettings['integrateLogin']) && ($wpSettings['installLevel'] == 10) ) {
			require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);
			$wpuCache = WPU_Cache::getInstance();
			require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
			$wpUtdInt = WPU_Integration::getInstance(get_defined_vars());
				if ($wpUtdInt->can_connect_to_wp()) { 
					$wpUtdInt->enter_wp_integration();
					$wpUtdInt->wp_logout();
					eval($wpUtdInt->exec()); 
					$wpUtdInt->exit_wp_integration();
					$wpUtdInt = null; unset ($wpUtdInt);
				}
			}
	}
	/**
	 * Updates the WordPress user profile when the phpBB profile is updated
	 */
	function profile_update($mode, $phpbb_id, $integration_id, $data) {
		global $wpSettings, $phpbb_root_path, $phpEx, $wpUtdInt, $db, $user, $wpuCache;

		if ( !empty($wpSettings['integrateLogin']) && ($wpSettings['installLevel'] == 10) ) {	
			
			// check that integration ID has been provided
			if (empty($integration_id)) {
				$sql = 	"SELECT *
					FROM " . USERS_TABLE . " 
					WHERE user_id = $phpbb_id";
				if (!$result = $db->sql_query($sql)) {
					trigger_error($user->lang['L_MAP_COULDNT_INT'] . '<br />' .  $user->lang['L_DB_ERROR']);
				}
				$user_data = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				$integration_id = $user_data['user_wpuint_id'];
			}
			
			// only bother integrating data if user is already integrated
			if (!empty($integration_id)) {
			
				$GLOBALS['wpu_newDetails'] = ''; 
				switch($mode) {
					case 'reg_details':
						$GLOBALS['wpu_newDetails'] = array(
							'user_id' 		=>  	$phpbb_id,
							'username' 		=>  	(isset($data['username'])) ? $data['username'] : '',
							'user_email' 		=> 	(isset($data['user_email'])) ? $data['user_email'] : '',
							'user_password' 	=> 	(isset($data['user_password'])) ? $data['user_password'] : ''
						);
					break;
					case 'profile_info':
						$GLOBALS['wpu_newDetails'] = array(
							'user_id' 		=> 	$phpbb_id,
							'user_aim'		=> 	(isset($data['user_aim'])) ? $data['user_aim'] : '',
							'user_yim'		=> 	(isset($data['user_yim'])) ? $data['user_yim'] : '',
							'user_jabber'		=> 	(isset($data['user_jabber'])) ? $data['user_jabber'] : '',
							'user_website'		=> 	(isset($data['user_website'])) ? $data['user_website'] : ''
						);
					break;
					case 'avatar':
						$GLOBALS['wpu_newDetails'] = array(
							'user_id' 			=> 	$phpbb_id,							
							'user_avatar' 			=> 	(isset($data['user_avatar'])) ? $data['user_avatar'] : '',
							'user_avatar_type'		=> 	(isset($data['user_avatar_type'])) ? $data['user_avatar_type'] : '',
							'user_avatar_width'		=> 	(isset($data['user_avatar_width'])) ? $data['user_avatar_width'] : '',
							'user_avatar_height'		=> 	(isset($data['user_avatar_height'])) ? $data['user_avatar_height'] : ''
						);
					break;
					case 'all':
					default;
						$GLOBALS['wpu_newDetails'] = array(
							'user_id' 		=>  	$phpbb_id,
							'username' 		=>  	(isset($data['username'])) ? $data['username'] : '',
							'user_email' 		=> 	(isset($data['user_email'])) ? $data['user_email'] : '',
							'user_password' 	=> 	(isset($data['user_password'])) ? $data['user_password'] : '',
							'user_aim'		=> 	(isset($data['user_aim'])) ? $data['user_aim'] : '',
							'user_yim'		=> 	(isset($data['user_yim'])) ? $data['user_yim'] : '',
							'user_jabber'		=> 	(isset($data['user_jabber'])) ? $data['user_jabber'] : '',
							'user_website'		=> 	(isset($data['user_website'])) ? $data['user_website'] : '',							
							'user_avatar' 			=> 	(isset($data['user_avatar'])) ? $data['user_avatar'] : '',
							'user_avatar_type'		=> 	(isset($data['user_avatar_type'])) ? $data['user_avatar_type'] : '',
							'user_avatar_width'		=> 	(isset($data['user_avatar_width'])) ? $data['user_avatar_width'] : '',
							'user_avatar_height'		=> 	(isset($data['user_avatar_height'])) ? $data['user_avatar_height'] : ''							
						);
					
					break;
				}
				if (!empty($GLOBALS['wpu_newDetails'])) {
					$GLOBALS['wpu_add_actions'] = '
						$wpUsrData = get_userdata(' . $integration_id . ');
						$wpUpdateData =	$wpUtdInt->check_details_consistency($wpUsrData, $GLOBALS[\'wpu_newDetails\']);
						if ( $wpUpdateData ) {
							wp_update_user($wpUpdateData);
						}
					';	
					define('WPU_PERFORM_ACTIONS', TRUE);
					if ( $wpSettings['showHdrFtr'] != 'REV' ) { // if reverse integration, we'll do it later
						require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
						require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);
						$wpuCache = WPU_Cache::getInstance();
						
						$wpUtdInt = WPU_Integration::getInstance(get_defined_vars());
						if ($wpUtdInt->can_connect_to_wp()) {
							//enter the integration
							$wpUtdInt->enter_wp_integration();
							/// No user integration here as we can't log in with the new credentials yet
							eval($wpUtdInt->exec());  
							$wpUtdInt->exit_wp_integration();
							$wpUtdInt = null; unset($wpUtdInt);
							
						}
					}
				}
			}			
		}
	}
	/**
	 * adds blog links to users' profiles.
	 */
	function generate_profile_link($bloglink_id, &$template) {
		global $wpSettings, $phpbb_root_path, $phpEx;
		require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
		require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);		
		$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 
		if  ( $wpSettings != FALSE ) {
			if (!empty($wpSettings['buttonsProfile'])) {
				if ( !empty($bloglink_id) ) {
					$blog_uri = append_sid($wpSettings['blogsUri'] . "?author=" . $bloglink_id);
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
		
	}
	/**
	 * creates blog links for users' posts
	 * @todo set blog images for subSilver template
	 */
	function generate_viewtopic_link($bloglink_id, &$cache) { 
		global $wpSettings, $phpbb_root_path, $phpEx;
		require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
		require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);		
		$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 
		if  ( $wpSettings['installLevel'] == 10 ) { 
			if (!empty($wpSettings['buttonsPost'])) {
				if ((!isset($user_cache[$poster_id])) && !empty($bloglink_id)) {
					if ($poster_id == ANONYMOUS) {
						$cache['blog_img'] = '';
						$cache['blog_link'] = '';
					} else {
						$cache['blog_img'] = '';   //TODO: SET FOR SUBSILVER!!
						$cache['blog_link'] = append_sid($wpSettings['blogsUri'] . "?author=" . $bloglink_id);			
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
		
		global $phpbb_root_path, $phpEx, $wpuCache, $wpSettings;
		
		require($phpbb_root_path . 'wp-united/functions-css-magic.' . $phpEx);
		
		require($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
		$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 

		require($phpbb_root_path . 'wp-united/version.' . $phpEx);
		require($phpbb_root_path . 'wp-united/cache.' . $phpEx);
		$wpuCache = WPU_Cache::getInstance();

		if(!isset($_GET['usecssm'])) {
			return $cssIn;
		}
		$pos = (request_var('pos', 'outer') == 'inner') ? 'inner' : 'outer';
		$cacheLocation = '';
		
		$cssIdentifier = request_var('cloc', 0);
		$cssIdentifier = $wpSettings['styleKeys'][$cssIdentifier];
		
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
		include($phpbb_root_path . 'wp-united/css-magic.' . $phpEx);
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
			
	
}

global $wpu_actions;
$wpu_actions = new WPU_Actions;
?>
