<?php

/** 
*
* WP-United Mod Edits
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.7.1[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
//
// This is the file for accessing WordPress from inside phpBB pages. Most of this stuff is in flux, meaning that users had to constantly re-mod their phpBB.
// Moving them off into this file is intended to alleviate that. Currently phpBB3-only
//


if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

class WPU_Actions {
	function do_head(&$template) {
		global $wpSettings, $phpbb_root_path, $phpEx;
		require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
		require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);		
		$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 
		if  ($wpSettings['installLevel'] == 10) {
			$template->assign_vars(array(
				'U_BLOG'	 =>	append_sid($GLOBALS['wpSettings']['blogsUri']),
				'S_BLOG'	=>	TRUE,
			));  
			//Do a reverse integration?
			if ($wpSettings['showHdrFtr'] == 'REV') {
				if (empty($gen_simple_header)) {
					define('WPU_REVERSE_INTEGRATION', true);
					ob_start();
				}
			}
		} 	
	}

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
	function profile_update($mode, $phpbb_id, $integration_id, $data) {
		global $wpSettings, $phpbb_root_path, $phpEx, $wpUtdInt, $db;
		require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
		require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);		
		$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 
		if ( !empty($wpSettings['integrateLogin']) && ($wpSettings['installLevel'] == 10) ) {	
			
			// check that integration ID has been provided
			if (empty($integration_id)) {
				$sql = 	"SELECT *
					FROM " . USERS_TABLE . " 
					WHERE user_id = $phpbb_id";
				if (!$result = $db->sql_query($sql)) {
					$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('L_MAP_COULDNT_INT'), $wpuAbs->lang('L_DB_ERROR'), __LINE__, __FILE__, $sql);
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
						$wpUtdInt = WPU_Integration::getInstance(get_defined_vars());
						if ($wpUtdInt->can_connect_to_wp()) {
							//enter the integration
							$wpUtdInt->enter_wp_integration();
							$wpUtdInt->integrate_login();
							eval($wpUtdInt->exec());  
							$wpUtdInt->exit_wp_integration();
							$wpUtdInt = null; unset($wpUtdInt);
						}	
					}
				}
			}			
		}
	}
	
	function generate_profile_link($bloglink_id, &$template) {
		global $wpSettings, $wpuAbs, $phpbb_root_path, $phpEx;
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
	
	function generate_viewtopic_link($bloglink_id, &$cache) { 
		global $wpSettings, $wpuAbs, $phpbb_root_path, $phpEx;
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
	
	function show_viewtopic_link($cache, &$postrow) {
		if (isset($cache['blog_link'])) {
			$postrow['BLOG_IMG'] = $cache['blog_img'];
			$postrow['U_BLOG_LINK'] = $cache['blog_link'];
		}		
	
	}
	
	function css_magic($css) {
		
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'wp-united/options.' . $phpEx); // temp -- this is called from style.php
		$pos = "outer";	
		
		
		
		if(isset($_GET['usecssm'])) {
			if(isset($_GET['pos'])) {
				$pos = ($_GET['pos'] == 'inner') ? 'inner' : 'outer';
			}
			$cacheLocation = '';
			if(isset($_GET['cloc'])) {
				$cacheLocation = urlencode(request_var('cloc', ''));
			}
			
			
			$useTV = '';
			if(isset($_GET['tv']) && $pos == 'inner') { 
				$useTV = $_GET['tv'];
				//prevent path traversal
				$useTV = str_replace(array('/', '\\', '..', ';', ':'), '', $useTV);
			}			
			
			if(request_var("usecssm", 0) && ($pos == 'inner')) {
				$cssCache = '';
				// first check caches (TODO: port to cache class):
				if(!empty($useTV)) {
					// template voodoo-modified CSS already cached?
					if(file_exists($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$useTV}.cssmtv")) {
						$cssCache = @file_get_contents($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$useTV}.cssmtv");
					}
				} else {
					// No template voodoo needed -- check for plain cache
					if(file_exists($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm")) {
						$cssCache = @file_get_contents($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm");
					}
				}			
			
				if(!empty($cssCache)) {
					$css = $cssCache;
				} else {
				
					include($phpbb_root_path . 'wp-united/wpu-css-magic.' . $phpEx);
					$cssMagic = CSS_Magic::getInstance();
					if($cssMagic->parseString($css)) {
					
						// Apply Template Voodoo
						if(!empty($useTV)) {
					
							$tvCacheLoc = $phpbb_root_path . "wp-united/cache/" . $useTV;
						
							if(file_exists($tvCacheLoc)) { 
								$templateVoodoo = @file_get_contents($tvCacheLoc);
								$templateVoodoo = @unserialize($templateVoodoo);

								if(isset($templateVoodoo['classes']) && isset($templateVoodoo['ids'])) {
							
									$classDupes = $templateVoodoo['classes'];
									$idDupes = $templateVoodoo['ids'];
									$finds = array();
									$repl = array();
									foreach($classDupes as $classDupe) {
										$finds[] = $classDupe;
										$repl[] = ".wpu" . substr($classDupe, 1);
									}
									foreach($idDupes as $idDupe) {
										$finds[] = $idDupe;
										$repl[] = "#wpu" . substr($idDupe, 1);
									}	

									$cssMagic->modifyKeys($finds, $repl);
								}
							}
				
						}					
					

						
						$cssMagic->makeSpecificByIdThenClass('wpucssmagic', false);
						$css = $cssMagic->getCSS();
						$cssMagic->clear();
					}
				
					// cache result here
					$fnTemp = $phpbb_root_path . "wp-united/cache/" . 'temp_' . floor(rand(0, 9999)) . 'cssmcache';
					
					$lastPart = (!empty($useTV)) ? "{$useTV}.cssmtv" : "{$pos}.cssm";
		
					$fnDest = $phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$lastPart}";
					
					$hTempFile = @fopen($fnTemp, 'w+');

					@fwrite($hTempFile, $css);
					@fclose($hTempFile);
					@copy($fnTemp, $fnDest);
					@unlink($fnTemp);				
				
				
				
				}


				$reset = '';
				if($pos == 'inner') {
					$reset = @file_get_contents($phpbb_root_path . "wp-united/theme/reset.css");
				}			

				return $reset . $css;
			}
		}
		return $css;
	}
	
}

global $wpu_actions;
$wpu_actions = new WPU_Actions;
?>
