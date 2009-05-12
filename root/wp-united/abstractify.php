<?php

/** 
*
* WP-United phpBB2 / phpBB3 abstraction
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v0.6.0[phpBB3] 2007/12/01 John Wells (Jhong) Exp $
* @copyright (c) 2006, 2007 wp-united.com
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

/***************************************************************************
		PHPBB2 / PHPBB3 'OLYMPUS' ABSTRACTIFY
		------------------------------------------------------------
		
	A vain attempt to make life easier by abstracting the functions that change between phpBB 2 & phpBB3.
	
	At least, we can include the common ones. Utterly subject to change.
	
	The aim is to make syncing the phpBB2 & phpBB3 distributions of WP-United much easier. Once development levels off, the versions may be diverged.
	
	phpBB3 mode is set in the external version.php file.
	
	Language zealots: Yes, 'abstractify' isn't a word.. Neither are 'abstractificate' or 'abstracterize'. 'Abstract', other than being ambiguous, just isn't as funny.
	
******************************************************************************/

if ( !defined('IN_PHPBB') ) {
	die("Hacking attempt");
	exit;
}

Class Abstractify {
	
	var $ver;

	//
	//	GET INSTANCE
	//	----------------------
	//	Makes class a Singleton.
	//	
	function getInstance ($version = 'PHPBB2' ) {
		static $instance;
		if (!isset($instance)) {
			$instance = new Abstractify($version);
        }
        return $instance;
    }

	function Abstractify($version) {
	// 
	// 	CLASS CONSTRUCTOR
	//	------------------------------
	//	
		$version = ($version == 'PHPBB3') ? 'PHPBB3' : 'PHPBB2';
		$this->ver = $version;
		if ('PHPBB3'== $this->ver) {
			define('GENERAL_ERROR', 100);
			define('CRITICAL_ERROR' , -100);
			if ( !$GLOBALS['user']->data ) {
				$GLOBALS['user']->session_begin();
				$GLOBALS['auth']->acl($GLOBALS['user']->data);
				$GLOBALS['user']->setup('mods/wp-united');
			} else {
				$GLOBALS['user']->add_lang('mods/wp-united');
			}
		} else {
			if ( !$GLOBALS['userdata'] ) {
				$GLOBALS['userdata'] = session_pagestart($GLOBALS['user_ip'], PAGE_BLOG); 
				init_userprefs($userdata); 
			}
			global $lang, $phpEx, $board_config, $phpbb_root_path;
			include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_wp-united.' . $phpEx);
		}
	}

	//retrieves board configuration
	function config($config_key) {
		if ('PHPBB2'== $this->ver) {
			if ( isset($GLOBALS['board_config'][$config_key]) ) {
				return $GLOBALS['board_config'][$config_key];
			}
		} else {
			if ( isset($GLOBALS['config'][$config_key]) ) {
				return $GLOBALS['config'][$config_key];
			}
		}
		return FALSE;
	}
	
	//Parses the template body
	function show_body($template_name) {
		if ('PHPBB2'== $this->ver) {
			$GLOBALS['template']->set_filenames(array(
				'body' => "$template_name.tpl") 
			); 
			$GLOBALS['template']->pparse('body'); 
		} else {
			$GLOBALS['template']->set_filenames(array( 
				'body' => "$template_name.html") 
			); 
			page_footer(); //displays the page!
		}
	}
	
	
	//Returns a language string. (getting really lazy now!)
	function lang($lang_key) {
		if ('PHPBB2'== $this->ver) {
			global $lang;
			if ( isset($lang[$lang_key]) ) {
				return $lang[$lang_key];
			}
		} else {
			if ( isset($GLOBALS['user']->lang[$lang_key]) ) {
				return $GLOBALS['user']->lang[$lang_key];
			}
		}
		return $lang_key;
	}
	
	//Assigns a single template switch. The phpBB3 way is more efficient and requires less template modification, so we prefer to use that if we can.
	function add_template_switch($switch_name, $switch_value) {
		//assume $template is already global
		if ('PHPBB2'== $this->ver) {
			$GLOBALS['template']->assign_block_vars('switch_' . strtolower($switch_name), array($switch_name => $switch_value));
		} else {
			$GLOBALS['template']->assign_vars(array($switch_name => $switch_value));
		}
	}
	
	//Returns full userdata array. Since we're sometimes unsure whether array keys are equal, we also abstract each key we need
	function userdata($key = '') {
		if ('PHPBB2'== $this->ver) {
			if ( $GLOBALS['IN_WORDPRESS'] == 1 ) {
				if ( !empty($key) ) {
					return $GLOBALS['wpUtdInt']->phpbb_usr_data[$key];
				}
				return $GLOBALS['wpUtdInt']->phpbb_usr_data;
			} else {
				if ( !empty($key) ) {
					return $GLOBALS['userdata'][$key];
				}
				return $GLOBALS['userdata'];
			}
		} else {
			if ( !empty($key) ) {
				return $GLOBALS['user']->data[$key];
			}
			return $GLOBALS['user']->data;
		}
	}
	
	
	// Returns whether the current user is logged in
	function user_logged_in() {  
		if ('PHPBB2'== $this->ver) {
			if ( $GLOBALS['IN_WORDPRESS'] == 1 ) {
				return ( empty($GLOBALS['wpUtdInt']->phpbb_usr_data['session_logged_in']) ) ? FALSE : TRUE;
			} else {
				return ( empty($GLOBALS['userdata']['session_logged_in']) ) ? FALSE : TRUE;
			}
		} else {
			return ( empty($GLOBALS['user']->data['is_registered']) ) ? FALSE : TRUE;
		}
	}
	
	// Returns username of currently logged in user
	function phpbb_username() {
		if ('PHPBB2'== $this->ver) {
			if ( $GLOBALS['IN_WORDPRESS'] == 1 ) {
				return $GLOBALS['wpUtdInt']->phpbb_usr_data['username'];	
			} else {
				return $GLOBALS['userdata']['username'];
			}
		} else {
			return $GLOBALS['user']->data['username'];
		}	
	}
	// Returns user id of currently logged in user
	function phpbb_user_id() {
		if ('PHPBB2'== $this->ver) {
			if ( $GLOBALS['IN_WORDPRESS'] == 1 ) {
				return $GLOBALS['wpUtdInt']->phpbb_usr_data['user_id'];
			} else {
				return $GLOBALS['userdata']['user_id'];
			}
		} else {
			return $GLOBALS['user']->data['user_id'];
		}	
	}
	// Returns password of currently logged in user
	function phpbb_passwd() {
		if ('PHPBB2'== $this->ver) {
			if ( $GLOBALS['IN_WORDPRESS'] == 1 ) {
				return $GLOBALS['wpUtdInt']->phpbb_usr_data['user_password'];
			} else {
				return $GLOBALS['userdata']['user_password'];			
			}
		} else {
			return $GLOBALS['user']->data['user_password'];
		}	
	}
	// Returns password of currently logged in user
	function phpbb_email() {
		if ('PHPBB2'== $this->ver) {
			if ( $GLOBALS['IN_WORDPRESS'] == 1 ) {
				return $GLOBALS['wpUtdInt']->phpbb_usr_data['user_email'];
			} else {
				return $GLOBALS['userdata']['user_email'];
			}
		} else {
			return $GLOBALS['user']->data['user_email'];
		}	
	}
	// Returns session_id of currently logged in user
	function phpbb_sid() {
		if ('PHPBB2'== $this->ver) {
			if ( $GLOBALS['IN_WORDPRESS'] == 1 ) {
				return $GLOBALS['wpUtdInt']->phpbb_usr_data['session_id'];
			} else {
				return $GLOBALS['userdata']['session_id'];
			}
		} else {
			return $GLOBALS['user']->data['session_id'];
		}	
	}	
	// Returns whether user is currently active -- normal or founder user
	function user_normal() {
		if ('PHPBB2'== $this->ver) {
			if ( $GLOBALS['IN_WORDPRESS'] == 1 ) {
				return $GLOBALS['wpUtdInt']->phpbb_usr_data['user_active'];
			} else {
				return $GLOBALS['userdata']['user_active'];
			}
		} else {
			if ( ($GLOBALS['user']->data['user_type'] == USER_NORMAL) || ($GLOBALS['user']->data['user_type'] == USER_FOUNDER) ) {
				return TRUE;
			} 
			return FALSE;
		}
	}
	
	//Returns rank info for currently logged in, or specified, user.
	function get_user_rank_info($userID = '') {
		global $db;
		if (!$userID ) {
			if( $this->user_logged_in() ) {
				$usrData = $this->userdata();
			} 
		} else {
			$sql = 'SELECT user_rank, user_posts 
						FROM ' . USERS_TABLE .
						' WHERE user_wpuint_id = ' . $userID;
				if(!($result = $db->sql_query($sql))) {
					$this->err_msg(GENERAL_ERROR, 'Could not query phpbb database', '', __LINE__, __FILE__, $sql);
				}
				$usrData = $db->sql_fetchrow($result);
		}
		if( $usrData ) {
			if ('PHPBB3'== $this->ver) {
				global $phpbb_root_path, $phpEx;
				if (!function_exists('get_user_rank')) {
					require_once($phpbb_root_path . 'includes/functions_display.php');
				}
				$rank = array();
				$rank['text'] = $rank['image_tag'] = $rank['image']  = '';
				get_user_rank($usrData['user_rank'], $usrData['user_posts'], $rank['text'], $rank['image_tag'], $rank['image']);
				return $rank;
			} else {
				$rankInfo = $this->_get_ranks_phpbb2();
				if ( $usrData['user_rank'] ) {
					for($j = 0; $j < count($rankInfo); $j++) {
						if ( $usrData['user_rank'] == $rankInfo[$j]['rank_id'] && $rankInfo[$j]['rank_special'] ) {
							$rank['text'] = $rankInfo[$j]['rank_title'];
							$rank['image'] = $rankInfo[$j]['rank_image'];
							return $rank;
						}
					}
				} else {
					for($j = 0; $j < count($rankInfo); $j++) {
						if ( $usrData['user_posts'] >= $rankInfo[$j]['rank_min'] && !$rankInfo[$j]['rank_special'] ) {
							$rank['text']= $rankInfo[$j]['rank_title'];
							$rank['image'] = $rankInfo[$j]['rank_image'];
							return $rank;
						}
					}
				}
			}
		}
	}
	// ranks helper func for phpBB2
	function _get_ranks_phpbb2() {
		//retrieve and cache rank array
		global $wpu_ranksArray, $wpuAbs, $db;
		if ( empty($this->ranks) ) {
			$GLOBALS['wpUtdInt']->switch_db('TO_P');
			$sql = "SELECT *
				FROM " . RANKS_TABLE . "
				ORDER BY rank_special, rank_min";
			if ( !($result = $db->sql_query($sql)) ) {
				$wpuAbs->err_msg(GENERAL_ERROR, "Could not obtain ranks information.", '', __LINE__, __FILE__, $sql);
			}
			while ( $row = $db->sql_fetchrow($result) ) {
				$this->ranks[] = $row;
			}
			$db->sql_freeresult($result);
			$GLOBALS['wpUtdInt']->switch_db('TO_W');
		}
		return $this->ranks;
	}
	
	// Censor words
	function censor($passage) {
		$GLOBALS['wpUtdInt']->switch_db('TO_P');
		if ('PHPBB2'== $this->ver) {
			// define censored word matches
			$orig_word = array();
			$replacement_word = array();
			obtain_word_list($orig_word, $replacement_word);
			// censor text and title
			if (count($orig_word)) {
				$passage = preg_replace($orig_word, $replacement_word, $passage);
			}
		} else {
			$passage = censor_text($passage);
		}
		$GLOBALS['wpUtdInt']->switch_db('TO_W');
		return $passage;
	}
	
	// Get board stats
	function stats($stat) {
		if ('PHPBB2'== $this->ver) {
			switch ($stat) {
				case 'num_topics':
					return get_db_stat('topiccount');
				case 'num_users':
					return get_db_stat('usercount');
				case 'newest_username':
					$result = get_db_stat('newestuser');
					return $result['username'];
				case 'newest_user_id':
					$result = get_db_stat('newestuser');
					return $result['user_id'];
				case 'num_posts':
				default;
					return get_db_stat('postcount');
			}
		} else {
			return $GLOBALS['config'][$stat];
		}
	
	}
	
	// Lifts latest phpBB topics from the DB. (this is the phpBB2 version) 
	// $forum_list limits to a specific forum (comma delimited list). $limit sets the number of posts fetched. 
	function get_recent_topics($forum_list = '', $limit = 50) {
		global $db, $auth;
		$GLOBALS['wpUtdInt']->switch_db('TO_P');
		
		if ($this->ver == 'PHPBB2') {
			$forum_sql = ($forum_list == '')? '' : 't.forum_id IN (' . $forum_list  . ') AND ';
		
			$sql = 'SELECT t.topic_id, t.topic_time, t.topic_title, u.username, u.user_id,
						t.topic_replies, t.forum_id, t.topic_poster, t.topic_status, f.forum_name
				FROM
				  ' . TOPICS_TABLE . ' AS t, ' . USERS_TABLE . ' AS u, ' . FORUMS_TABLE . ' AS f 
				WHERE ' . $forum_sql . ' 
					t.topic_poster = u.user_id 
						AND f.auth_read = 0 
							AND t.forum_id = f.forum_id 
								AND t.topic_status <> 2
				ORDER BY t.topic_time DESC 
				LIMIT 0,' . $limit;

			if(!($result = $db->sql_query($sql))) {
				$this->err_msg(GENERAL_ERROR, 'Could not query phpbb database', '', __LINE__, __FILE__, $sql);
			}
		} else { //PHPBB3 version
			$forum_list = (empty($forum_list)) ? array() :  explode(',', $forum_list); //forums to explicitly check
			$forums_check = array_unique(array_keys($auth->acl_getf('f_read', true))); //forums authorised to read posts in
			if (sizeof($forum_list)) {
				$forums_check = array_intersect($forums_check, $forum_list);
			}
			if (!sizeof($forums_check)) {
				return FALSE;
			}
			$sql = 'SELECT t.topic_id, t.topic_time, t.topic_title, u.username, u.user_id,
					t.topic_replies, t.forum_id, t.topic_poster, t.topic_status, f.forum_name
				FROM ' . TOPICS_TABLE . ' AS t, ' . USERS_TABLE . ' AS u, ' . FORUMS_TABLE . ' AS f 
				WHERE ' . $db->sql_in_set('f.forum_id', $forums_check)  . ' 
					AND t.topic_poster = u.user_id 
						AND t.forum_id = f.forum_id 
							AND t.topic_status <> 2 
				ORDER BY t.topic_time DESC';
				
			if(!($result = $db->sql_query_limit($sql, $limit, 0))) {
				$this->err_msg(GENERAL_ERROR, 'Could not query phpbb database', '', __LINE__, __FILE__, $sql);
			}		
		}
		// The rest is the same for both versions:
		$posts = array();
		$i = 0;
		while ($row = $db->sql_fetchrow($result)) {
			$posts[$i] = array(
				'topic_id' 		=> $row['topic_id'],
				'topic_replies' => $row['topic_replies'],
				'topic_title' 	=> wpu_censor($row['topic_title']),
				'user_id' 		=> $row['user_id'],
				'username' 		=> $row['username'],
				'forum_id' 		=> $row['forum_id'],
				'forum_name' 	=> $row['forum_name']
			);
			$i++;
		}
		$db->sql_freeresult($result);
		$GLOBALS['wpUtdInt']->switch_db('TO_W');
		return $posts;
	}	
	
	// Insert a new phpBB user
	function insert_user($username, $user_password, $user_email, $integration_id = '', $group_id = '') {
		if ('PHPBB2'== $this->ver) {
			global $db, $phpbb_root_path, $phpEx;
			include_once($phpbb_root_path . 'includes/functions_validate.' . $phpEx);
			include_once($phpbb_root_path . 'includes/functions_post.' . $phpEx);
			include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
			$wpu_user = new wpu_user($username, $user_password, $user_email);

			if (!empty($integration_id)) {
				$wpu_user->integrate($integration_id);
			}

			if ($group_id != '') {
				$wpu_user->add_to_group($group_id);
			}

			$result = $wpu_user->validate_user();
			if ($result) { 
				$result = $wpu_user->insert_user();
			} 
			return $result;
		} else {
			//phpBB3
			global $db, $phpbb_root_path, $phpEx;
			require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			if ( validate_username($username) === FALSE ) {
				return user_add(array(
					'username' => $username,
					'user_password' => $user_password,
					'user_email' => $user_email,
					'user_type' => USER_NORMAL,
					'group_id' => 2  //add to registered users group		
				));
			}
		}
	}

	
	
	//Displays a dying general error message
	function err_msg($errType, $msg = '', $title = '', $line = '', $file = '', $sql = '') {
		global $images, $wpUtdInt, $phpbb_root_path;
		//Exit the WordPress environment
		if ( isset($wpUtdInt) ) {
			if ( $wpUtdInt->wpLoaded ) {
				$wpUtdInt->exit_wp_integration();
			}
		}
		if ( $errType != CRITICAL_ERROR ) {
			$msg = '<img src="' . $phpbb_root_path . 'wp-united/images/wp-united-logo.gif" style="float: left;" /><br />' . $msg;
		}
		if ('PHPBB2'== $this->ver) {
			message_die($errType, $msg, $title, $line, $file, $sql);
		} else {
			//if ( $errType == CRITICAL_ERROR ) {
				trigger_error($msg);
			//} else {
			
			//}
		}
	}
	
}






/***************************************************************************
 * Original Filename:          functions_mod_user.php
 * Description:       A library consisting of a user class and some wrapper
 *                    functions which can be used by MOD authors to handle
 *                    user registration
 * Author:            Graham Eames (phpbb@grahameames.co.uk)
 * Last Modified:     30-Sep-2006
 * File Version:      1.3
 *
 * Acknowlegments:    A few pieces of code in this come from usercp_register.php
 *                    Much of the rest is adapted from [Graham's] convertors
 *	Lightly modified for WP-United
 ***************************************************************************/

class wpu_user {
	// These are the 3 critical values for any user
	var $username;
	var $user_password;
	var $user_email;

	var $user_id;

	// The remaining userdata fields are stored in an array
	var $user_fields;

	// This stores details of any usergroups that the user should be in
	var $groups;

	// The constructor for this class
	//
	// The password must be in MD5 format, but we'll handle escaping any special
	// characters in any field within the function
	function wpu_user($name, $password, $email) {
		global $board_config;

		$this->username = $this->sql_escape($name);
		$this->user_password = $this->sql_escape($password);
		$this->user_email = $this->sql_escape($email);

		$this->user_id = '';

		// Now we need to set the remaining fields to some default values
		// If you wish to integrate with another MOD, you should add any initilization
		// it requires after this
		$this->user_fields['user_regdate'] = time();
		$this->user_fields['user_from'] = '';
		$this->user_fields['user_occ'] = '';
		$this->user_fields['user_interests'] = '';
		$this->user_fields['user_website'] = '';
		$this->user_fields['user_icq'] = '';
		$this->user_fields['user_aim'] = '';
		$this->user_fields['user_yim'] = '';
		$this->user_fields['user_msnm'] = '';
		$this->user_fields['user_sig'] = '';
		$this->user_fields['user_sig_bbcode_uid'] = ( $board_config['allow_bbcode'] ) ? make_bbcode_uid() : '';
		$this->user_fields['user_avatar'] = '';
		$this->user_fields['user_avatar_type'] = USER_AVATAR_NONE;
		$this->user_fields['user_viewemail'] = 1;
		$this->user_fields['user_attachsig'] = 1;
		$this->user_fields['user_allowsmile'] = $board_config['allow_smilies'];
		$this->user_fields['user_allowhtml'] = $board_config['allow_html'];
		$this->user_fields['user_allowbbcode'] = $board_config['allow_bbcode'];
		$this->user_fields['user_allow_viewonline'] = 1;
		$this->user_fields['user_notify'] = 0;
		$this->user_fields['user_notify_pm'] = 1;
		$this->user_fields['user_popup_pm'] = 1;
		$this->user_fields['user_timezone'] = $board_config['board_timezone'];
		$this->user_fields['user_dateformat'] = $board_config['default_dateformat'];
		$this->user_fields['user_lang'] = $board_config['default_lang'];
		$this->user_fields['user_style'] = $board_config['default_style'];
		$this->user_fields['user_level'] = USER;
		$this->user_fields['user_posts'] = 0;
		$this->user_fields['user_wpuint_id'] = '';
	}

	// This function escapes any special characters in a string to allow for safe
	// use in the SQL query. It is used in the constructor and should be used on 
	// any data passed to set_field()
	function sql_escape($data) {
		return str_replace("\'", "''", addslashes($data));
	}

	// This function is used to set any of the user fields if you do not want to
	// use the default values. Any field listed in the array in this function
	// will have special characters escaped
	function set_field($field_name, $data) {
		// It's not the most efficient, but we escape everything just to be safe
		$this->user_fields[$field_name] = $this->sql_escape($data);
	}

	// This function allows you to set a specific user_id for this user
	// You should only call this if you know that the user_id you are specifying
	// is not already in use.
	// This is provided mainly for convertor use and not for normal use
	function set_user_id($id) {
		$this->user_id = intval($id);
	}

	// This function returns the user_id of the user.
	// It is only really useful after the call to insert_user()
	function get_user_id() {
		return $this->user_id;
	}

	// This function is used to set any usergroups the user should be added to
	// upon registration.
	// It can be called as many times as required
	function add_to_group($group_id) {
		$this->groups[] = $group_id;
	}
	
	//Integrate to WordPress account (WP-United)
	function integrate($wpID) {
		$this->user_fields['user_wpuint_id'] = (int)$wpID;
	}
	
	
	// This function validates the userdata to ensure that the user can be inserted
	// into the database. It checks for duplicate usernames, disallowed usernames,
	// invalid email addresses and disallowed email addresses
	//
	// Returns true if the user can be inserted, false otherwise
	function validate_user() {
		$name_check = validate_username(stripslashes(str_replace("''", "\'", $this->username)));
		if ($name_check['error']) { 
			return false;
		}

		$email_check = validate_email(stripslashes(str_replace("''", "\'", $this->user_email)));
		if ($email_check['error']) { 
			return false;
		}
		return true;
	}

	// This is the function which actually inserts the user into the database
	//
	// NB. This function does not validate the user allowing you to register names
	// and email addresses which might otherwise be disallowed, if you want to
	// validate the data you should call validate_user() first
	//
	// Returns true on success, false otherwise
	function insert_user() {
		global $db;

		// Get the user_id if one has not already been set
		if ($this->user_id == '') {
			$sql = "SELECT MAX(user_id) AS total
				FROM " . USERS_TABLE;
			if ( !($result = $db->sql_query($sql)) ) {
				message_die(GENERAL_ERROR, 'Could not obtain next user_id information', '', __LINE__, __FILE__, $sql);
			}

			if ( !($row = $db->sql_fetchrow($result)) ) {
				message_die(GENERAL_ERROR, 'Could not obtain next user_id information', '', __LINE__, __FILE__, $sql);
			}
			$this->user_id = $row['total'] + 1;
		}

		// Build the main SQL query
		$sql = "INSERT INTO " . USERS_TABLE . "	(user_id, username, user_regdate, user_password, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_sig_bbcode_uid, user_avatar, user_avatar_type, user_viewemail, user_aim, user_yim, user_msnm, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_viewonline, user_notify, user_notify_pm, user_popup_pm, user_timezone, user_dateformat, user_lang, user_style, user_level, user_allow_pm, user_active, user_actkey, user_posts, user_wpuint_id) ";
		$sql .= "VALUES (" . $this->user_id . ", '" . $this->username . "', '" . $this->user_fields['user_regdate'] . "', '" . $this->user_password . "', '" . $this->user_email . "', '" . $this->user_fields['user_icq'] . "', '" . $this->user_fields['user_website'] . "', '" . $this->user_fields['user_occ'] . "', '" . $this->user_fields['user_from'] . "', '" . $this->user_fields['user_interests'] . "', '" . $this->user_fields['user_sig'] . "', '" . $this->user_fields['user_sig_bbcode_uid'] . "', '" . $this->user_fields['user_avatar'] . "', '" . $this->user_fields['user_avatar_type'] . "', " . $this->user_fields['user_viewemail'] . ", '" . str_replace(' ', '+', $this->user_fields['user_aim']) . "', '" . $this->user_fields['user_yim'] . "', '" . $this->user_fields['user_msnm'] . "', " . $this->user_fields['user_attachsig'] . ", " . $this->user_fields['user_allowsmile'] . ", " . $this->user_fields['user_allowhtml'] . ", " . $this->user_fields['user_allowbbcode'] . ", " . $this->user_fields['user_allow_viewonline'] . ", " . $this->user_fields['user_notify'] . ", " . $this->user_fields['user_notify_pm'] . ", " . $this->user_fields['user_popup_pm'] . ", " . $this->user_fields['user_timezone'] . ", '" . $this->user_fields['user_dateformat'] . "', '" . $this->user_fields['user_lang'] . "', " . $this->user_fields['user_style'] . ", " . $this->user_fields['user_level'] . ", 1, 1, '', '" . $this->user_fields['user_posts']  . "', " .  $this->user_fields['user_wpuint_id'] . ")";

		// Insert the user
		if ( !($result = $db->sql_query($sql, BEGIN_TRANSACTION)) ) {
			$error = true;
		}

		// Insert the personal group
		$sql = "INSERT INTO " . GROUPS_TABLE . " (group_name, group_description, group_single_user, group_moderator)
			VALUES ('', 'Personal User', 1, 0)";
		if ( !($result = $db->sql_query($sql)) ) {
			$error = true;
		}

		$group_id = $db->sql_nextid();

		// Insert the user_group entry
		$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending)
			VALUES (" . $this->user_id . ", $group_id, 0)";
		if( !($result = $db->sql_query($sql, END_TRANSACTION)) ) {
			$error = true;
		}

		// Add the user to any applicable groups
		for ($i=0; $i<count($this->groups); $i++) {
			$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending)
				VALUES (" . $this->user_id . ", " . $this->groups[$i] . ", 0)";
			if( !($result = $db->sql_query($sql)) ) {
				$error = true;
			}
		}
		return ($error == true) ? false : true;
	}
}

















global $wpuAbs, $phpbb_root_path, $phpEx, $user, $userdata, $auth, $user_ip;
include ($phpbb_root_path . 'wp-united/version.' . $phpEx);
$wpuAbs = Abstractify::getInstance($phpbb_version);
?>
