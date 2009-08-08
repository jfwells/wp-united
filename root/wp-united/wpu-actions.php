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
		global $wpSettings, $phpbb_root_path, $phpEx, $wpUtdInt;
		require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
		require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);		
		$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 
		if ( !empty($wpSettings['integrateLogin']) && ($wpSettings['installLevel'] == 10) ) {
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
			// First check cache (TODO: port to cache class)
			if(file_exists($phpbb_root_path . "wp-united/cache/$cacheLocation-{$pos}.cssm")) {
				$css = @file_get_contents($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm");
			} else {
				if(request_var("usecssm", 0) && ($pos == 'inner')) {
					include($phpbb_root_path . 'wp-united/wpu-css-magic.' . $phpEx);
					$cssMagic = CSS_Magic::getInstance();
					if($cssMagic->parseString($css)) {
					/*	if(defined('USE_TEMPLATE_VOODOO') && USE_TEMPLATE_VOODOO) {
							if(isset($_GET['tv'])) {
								$tvFile = (string) request_var('tv', '');
								$tvFile = urldecode($tvFile);
								$tvFile = $phpbb_root_path . "wp-united/cache/tvoodoo-" . $tvFile . ".tv";
								if(file_exists($tvFile)) {
									$tvFc = file_get_contents($tvFile);
									$tvFc = unserialize($tvFc);
									$tvIds = $tvFc[0];
									$tvClasses = $tvFc[1];
									$cssMagic->renameIds("wpu", $tvIds);
									$cssMagic->renameClasses("wpu", $tvClasses);
					
								}
							}
						}*/
						$cssMagic->makeSpecificByIdThenClass('wpucssmagic', false);
						$css = $cssMagic->getCSS();
						$cssMagic->clear();
					}
				}

				// cache result here
				$fnTemp = $phpbb_root_path . "wp-united/cache/" . 'temp_' . floor(rand(0, 9999)) . 'cssmcache';
				$fnDest = $phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm";
				$hTempFile = @fopen($fnTemp, 'w+');

				@fwrite($hTempFile, $css);
				@fclose($hTempFile);
				@copy($fnTemp, $fnDest);
				@unlink($fnTemp);


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


//phpBB2 version -- for refactoring into the above (TODO)!
if ($GLOBALS['wpuAbs']->ver == 'PHPBB2') {
	switch ($wpuAction) {
		case 'PROFILE UPDATE':
			require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
			$wpSettings = get_integration_settings();
			if ( ($wpSettings == FALSE)	|| ($wpSettings['wpPath'] == "") ) {
				message_die(GENERAL_ERROR, $lang['WP_DBErr_Gen'], __LINE__, __FILE__, $sql);
			}
			if ( !empty($wpSettings['integrateLogin']) ) {	
				$wpID = $userdata['user_wpuint_id'];
				if (!empty($wpID)) {
					$pass = $avImg = $avType = '';
					if ( !empty($avatar_sql) ) {
						$avDetails = explode(',', $avatar_sql);
						$avImg = explode('\'', $avDetails[1]);
						$avType = explode('=', $avDetails[2]);
						$avImg = $avImg[1];
						$avType = (int)trim($avType[1]);
					}
					if ( !empty($passwd_sql) ) {
						$pass = explode('\'', $passwd_sql);
						$pass=$pass[1];
					}
					$GLOBALS['wpu_newDetails'] = array(
						'username' => $username,
						'user_email' => $email,
						'user_password' => $pass,
						'user_website' => $website,
						'user_aim' => $aim,
						'user_yim' => $yim,
						'user_avatar_type' => $avType,
						'user_allow_avatar' => $userdata['user_allow_avatar'],
						'user_avatar' => $avImg
					);
					$GLOBALS['wpu_add_actions'] = '
						$wpUsrData = get_userdata($wpID);
						$wpUpdateData =	$wpUtdInt->check_details_consistency($wpUsrData, $GLOBALS[\'newDetails\']);
						if ( $wpUpdateData ) {
							wp_update_user($wpUpdateData);
						}
					';
					define('WPU_PERFORM_ACTIONS', TRUE);
					if ( $wpSettings['showHdrFtr'] != 'REV' ) {
						//enter the integration
						require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
						require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);
						$wpUtdInt = WPU_Integration::getInstance(get_defined_vars());
						if ( !$wpUtdInt->can_connect_to_wp() ) {
							message_die(GENERAL_ERROR, $lang['WP_Not_Installed_Yet'],'','','');
						}			
						$wpUtdInt->enter_wp_integration();
						eval($wpUtdInt->exec());  
						$wpUtdInt->exit_wp_integration();
						$wpUtdInt = null; unset($wpUtdInt);	
					}
				}
			}
		break;
		case 'GENERATE PROFILE LINK':
			global $wpSettings, $wpuAbs, $phpbb_root_path, $phpEx;
			require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);
			if  ( $wpSettings != FALSE ) {
				if (!empty($wpSettings['buttonsProfile'])) {
					$bloglink_id = ($wpuAbs->ver == 'PHPBB2') ? $profiledata['user_wpublog_id'] : $member['user_wpublog_id'];
					if ( !empty($bloglink_id) ) {
						$blog_uri = append_sid($wpSettings['blogsUri'] . "?author=" . $bloglink_id);
						$blog_img = '';   //TODO: SET FOR SUBSILVER!!
						if ($wpuAbs->ver == 'PHPBB3') {
							$template->assign_vars(array(
								'BLOG_IMG' 			=> $blog_img,
								'U_BLOG_LINK'		=> $blog_uri,
							));
						}
					} else {
						$blog_img = "";
					}
				}
			}
		break;
		case 'GENERATE VIEWTOPIC LINK':
			global $wpSettings, $wpuAbs, $phpbb_root_path, $phpEx;
			require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);	
			if  ( $wpSettings != FALSE ) {
				if (!empty($wpSettings['buttonsPost'])) {
					if ($wpuAbs->ver == 'PHPBB3') {			
						if ((!isset($user_cache[$poster_id])) && !empty($row['user_wpublog_id'])) {
							if ($poster_id == ANONYMOUS) {
								$user_cache[$poster_id]['blog_img'] = '';
								$user_cache[$poster_id]['blog_link'] = '';
							} else {
								$user_cache[$poster_id]['blog_img'] = '';   //TODO: SET FOR SUBSILVER!!
								$user_cache[$poster_id]['blog_link'] = append_sid($wpSettings['blogsUri'] . "?author=" . $row['user_wpublog_id']);			
							}
						}
					}
				}
			}
		break;
		case 'SHOW VIEWTOPIC LINK':
			if (($wpuAbs->ver == 'PHPBB3') && (isset($user_cache[$poster_id]['blog_link']))) {
				$postrow['BLOG_IMG'] = $user_cache[$poster_id]['blog_img'];
				$postrow['U_BLOG_LINK'] = $user_cache[$poster_id]['blog_link'];
			}
		break;
	}
}

?>
