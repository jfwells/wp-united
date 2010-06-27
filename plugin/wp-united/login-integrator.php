<?php

/** 
*
* WP-United Login Integrator
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/

/**
 */
if ( !defined('IN_PHPBB') ) {
	exit;
}


function wpu_integrate_logins() {
	global $wpSettings, $phpbbForum, $latest, $phpbb_root_path, $phpEx;
	
	if ( empty($wpSettings['integrateLogin']) ) {
		return false;
	}
	
	if ($latest || defined('WPU_BOARD_DISABLED')) {
		return false;
	}
		
	if(!isset($phpbbForum)) {
		return false;
	}

	//if(defined('WPU_DEBUG')) {
		require_once($wpSettings['wpPluginPath'] . 'debugger.' . $phpEx);
		global $lDebug;
		$lDebug = new WPU_Debug();
	//}

	$loggedInUser = '';
	$newWpUser = '';
	$phpbbRawUser = $phpbbForum->get_username();
	$phpbbUserName = sanitize_user($phpbbRawUser, true);
	$integratedID = ( array_key_exists('user_wpuint_id', $phpbbForum->get_userdata()) ) ? $phpbbForum->get_userdata('user_wpuint_id') : 0 ; 
	$wpUserData = '';
	$newUserID = '';
		
	$user_level = wpu_get_userlevel();

	// Integrate only if logged in, and user level is mapped to integrate
	if (  (!$phpbbForum->user_logged_in()) || ($user_level === false) || !( ($phpbbForum->get_userdata('user_type') == USER_NORMAL) || ($phpbbForum->get_userdata('user_type') == USER_FOUNDER) ) )  {
		//log them out of WP, and look needlessly suspicious
		$lDebug->add('Not logged in'); 
		wpu_wp_logout();
		wp_set_current_user(0, 0);
	} else {
		$wpUserdata = get_userdata($integratedID);
		if ( empty($wpUserdata) ) {
			$integratedID = 0;
			wpu_update_int_ID($phpbbForum->get_userdata('user_id'), $integratedID);
		} else {
			$lDebug->add('Logged into phpBB, username=' .  $phpbbRawUser . '(' . $phpbbUserName . ',' . $integratedID . ')');
		}
		//SECTION TO CREATE INTEGRATED ACCOUNT
		if ( empty($integratedID) ) {
			require_once( ABSPATH . WPINC . '/registration.php');
			// The user hasn't integrated an account yet. If they're logged in for some reason, assume it is suspicious and log them out
			wpu_wp_logout();
			wp_set_current_user(0, 0);
			$loggedInUser = '';
			
			$lDebug->add('No WP Account Detected, Creating.');
			// No user reaching this point has an integrated account. So let's create one.
			$tryThisName = $phpbbUserName;
			
			//start with the plain username, if unavailable then append a number onto the login name until we find one that is available
			$i = 0; $foundFreeName = FALSE;
			while ( !$foundFreeName ) {
				if ( !username_exists($tryThisName) ) {
					$foundFreeName = TRUE;
				} else {
					// A username already exists. But it could belong to a different person 
					$whoIsIt = get_userdatabylogin($signUpName);
					// print_r($whoIsIt);
					if ( $whoIsIt->phpbb_userLogin == $phpbbRawUser ) {
						//uh-oh, we have a problem. Why has this integration come undone?
						// This will pop up for users integrated prior to phBB2 WPU v0.8.9.2 who haven't been converted w/ the update script... bah! too much validation...
						trigger_error('Error! Your Integration has become decoupled! Please contact an administrator and inform them of this error message.');
					}
					$i++; 
					$tryThisName = $phpbbUserName . $i;
				}
			}
			$signUpName = $tryThisName;
			
			$lDebug->add('Found a suitable WP username: '.$signUpName);
			
			//Now we have a unique signup name.. let's create the user.
			$newWpUser->user_login = $signUpName;
			$newWpUser->user_pass = $phpbbForum->get_userdata('user_password');
			$newWpUser->user_email = $phpbbForum->get_userdata('user_email');
			
			define('PASSWORD_ALREADY_HASHED', TRUE);
			$newUserID = wp_insert_user(get_object_vars($newWpUser));
			
			$integratedID = $newUserID;
			wpu_update_int_ID($phpbbForum->get_userdata('user_id'), $integratedID);
			$lDebug->add('Created a user with ID = ' . $integratedID);
			$newUserData = get_userdata($newUserID);

			//Set usermeta options and check details consistency
			$wpUpdateData =	wpu_check_details_consistency($newUserData, $phpbbForum->get_userdata(), true);
			if ( $wpUpdateData ) {
				wp_update_user($wpUpdateData);
			}	
		}
									
		$loggedInUser = wp_get_current_user();
		
		
		// Check that they're not already logged into the wrong account (weird stuff happens)
		if ($loggedInUser->ID !== $integratedID && ($loggedInUser->ID)) {
			$lDebug->add('You are logged into the wrong account! (WP ID = ' . $loggedInUser->ID . ', integrated ID = ' . $integratedID . '). Logging out!');
			wpu_wp_logout();
			wp_set_current_user(0, 0);
			$loggedInUser = wp_get_current_user();
			
		}
		
		
		//SECTION TO LOG USER IN
		if ( empty($loggedInUser->ID) ) {
			global $error;
			//user isn't logged in
			$wpUser = get_userdata($integratedID);
			$wpUserName = $wpUser->user_login;
			$lDebug->add('WP account detected, logging into account (ID=' . $integratedID . ',Username=' . $wpUserName . ')');
			//see if user can log into WP (need to double-hash password)  
			// This authentication is really unneccessary at this point.... but we need them to have a strong password in a WP cookie for Admin panel access
			

			if(wpu_sign_in($wpUserName,  $phpbbForum->get_userdata('user_password'))) {					
				$loggedInUser = wp_set_current_user($wpUser->ID);
				$lDebug->add('Logged in successfully. Cookie set. Current user=' . $GLOBALS['current_user']->ID);
			} else {
				$lDebug->add('Could not authenticate. (' . $error .') Synchronising password.');
				// they couldn't log in... so let's just change their password
				$wpUpdateData =	wpu_check_details_consistency($wpUser,  $phpbbForum->get_userdata()); 
				if ( $wpUpdateData ) {
					define('PASSWORD_ALREADY_HASHED', TRUE);
					require_once( ABSPATH . WPINC . '/registration.php');
					wp_update_user($wpUpdateData);
				}
				//It must work now....
				$wpUser = get_userdata($integratedID);
				$wpUserName = $wpUser->user_login;

				if(wpu_sign_in($wpUserName,  $phpbbForum->get_userdata['user_password'])) {
					$loggedInUser = wp_set_current_user($wpUser->ID);
					$lDebug->add('Logged in successfully. Cookie set. Current user=' . $GLOBALS['current_user']->ID);
				} else {
					$phpbbForum->enter();
					$lDebug->add('Failed, aborting (' . $error .')', 1);
					trigger_error('WordPress Integration Error: WP-United has encountered an unknown integration error. We tried twice to log you in and it didn\'t work. Sorry! Please inform an administrator of this message');
				}
			}
		}
						
		if ( !empty($loggedInUser->ID) ) {
			$lDebug->add('Checking Profile Consistency');
			$userdata = wpu_check_userlevels($loggedInUser->ID, $user_level);
			$userdata = get_userdata($userdata->ID);
			wp_set_current_user($userdata->ID);
			$wpUpdateData =	wpu_check_details_consistency($userdata, $phpbbForum->get_userdata());					
			if ( $wpUpdateData ) {
				require_once( ABSPATH . WPINC . '/registration.php');
				$lDebug->add('Synchronising Profiles');
				define('PASSWORD_ALREADY_HASHED', TRUE);
				$loggedInID = wp_update_user($wpUpdateData);
				$loggedInUser = wp_set_current_user($loggedInID);
				$loggedInUser = get_userdata($loggedInUser->ID);
			}
		} else {
			//The login integration has failed. Log them out of WP just in case, and raise a stink.
			wpu_wp_logout();
			$phpbbForum->enter();
			$lDebug->add('Failed, aborting2', 1);
			trigger_error('Integration Error with your account! Please contact an administrator.');
		}
		
		if ( !($loggedInUser->ID == $integratedID) ) {
			//ID mismatch. something is heavily borked.
			trigger_error('WordPress Integration Error: Integration Mismatch Error with your account! Please contact an administrator.');
		}
		
	}

	
}

/**
 * Gets the logged-in user's level so we can arbitrate permissions
 */
function wpu_get_userlevel($phpbb_userdata = false) {

	global $db, $phpbbForum, $user, $lDebug;

	$phpbbForum->enter_if_out();
	
	$user_level = false;
	if($phpbb_userdata == false) {
		if ( (!$phpbbForum->user_logged_in()) || !( ($phpbbForum->get_userdata('user_type') == USER_NORMAL) || ($phpbbForum->get_userdata('user_type') == USER_FOUNDER) ) ) {
			return false;
		}
		global $auth;
		$auth->acl($user->data);
	} else {
		$auth = new auth();
		$auth->acl($phpbb_userdata);
	}

	$debug = 'Checking permissions: ';
	if ( $auth->acl_get('u_wpu_subscriber') ) {
		$user_level = 'subscriber'; 
		$debug .= '[' . $user_level . ']';
	}
	if ( $auth->acl_get('u_wpu_contributor') ) {
		$user_level = 'contributor'; 
		$debug .= '[' . $user_level . ']';
	}
	if ( $auth->acl_get('u_wpu_author') ) {
		$user_level = 'author'; 
		$debug .= '[' . $user_level . ']';
	}
	if ( $auth->acl_get('m_wpu_editor') ) {
		$user_level = 'editor'; 
		$debug .= '[' . $user_level . ']';
	}
	if ( $auth->acl_get('a_wpu_administrator') ) {
		$user_level = 'administrator'; 
		$debug .= '[' . $user_level . ']';
	}			
	$lDebug->add($debug);
	$lDebug->add('User level set to: ' . $user_level);
	
	$phpbbForum->leave_if_just_entered();
	return $user_level;
}

/**
 * Logs out from WP
 */
function wpu_wp_logout() {
	wp_logout();
	unset($_COOKIE[AUTH_COOKIE]);
	unset($_COOKIE[SECURE_AUTH_COOKIE]);
	unset($_COOKIE[LOGGED_IN_COOKIE]);
	// prior to WP2.5 we did wp_clearcookie(); rather than the above
	
	do_action('wp_logout');
	wp_set_current_user(0, 0);
	nocache_headers();
	unset($_COOKIE[USER_COOKIE]);
	unset($_COOKIE[PASS_COOKIE]);
	
}

/**
 * Updates the Integration ID stored in phpBB profile
 */
function wpu_update_int_ID($pID, $intID) {
	global $db, $cache, $phpbbForum;

	//Do we need to update the integration ID?
	if ( empty($intID) ) {
		return false;
	} 
	//Switch back to the phpBB DB:
	$phpbbForum->enter();
	
	$updated = FALSE;
	if ( !empty($pID) ) { 
		$sql = 'UPDATE ' . USERS_TABLE . " 
			SET user_wpuint_id = $intID 
			WHERE user_id = '$pID'";
		if(!$result = $db->sql_query($sql)) {
			trigger_error('WP-United could not update your integration ID in phpBB, due to a database access error. Please contact an administrator and inform them of this error.');
		} else {
			$updated = TRUE;
		}
	}
	//Switch back to the WP DB:
	$phpbbForum->leave();
	if ( !$updated ) {
		trigger_error('Could not update integration data: WP-United could not update your integration ID in phpBB, due to an unknown error. Please contact an administrator and inform them of this error.');
	}
}	
	
	
/**
 * Arbitrates the user details - e-mail, password, website, aim, yim, between phpBB & WordPress -- called internally by integrate_login
 * Basically, just overwrites WP values with the current phpBB values.
 * We try to update these whenever they are changed, but that's not always the case, so for now we also do this on each access.
 *	@param mixed $wpData WordPress user data
 * @param mixed $pData phpBB user data
 * @return mixed array of fields to update
 */
function wpu_check_details_consistency($wpData, $pData, $newUser = false) {	

	if ( !empty($wpData->ID) ) {
		$wpMeta = get_usermeta($wpData->ID);
		
		// initialise wp-united meta fields to prevent PHP notices later on
		$wpuSpecialFields = array('wpu_avatar_width', 'wpu_avatar_height', 'wpu_avatar_type', 'wpu_avatar');
		foreach($wpuSpecialFields as $key) {
			if(!isset($wpMeta[$key])) {
				$wpMeta[$key] = '';
			}
		}
	}
	
	// we use this so we can direct to their phpBB profile without faffing around
	if ($pData['user_id'] != $wpData->phpbb_userid) {
		update_usermeta( $wpData->ID, 'phpbb_userid', $pData['user_id']);
	}
	
	$doWpUpdate = false;
	// We only update the user's nicename, etc. on the first run -- they can change it thereafter themselves
	if($newUser) {
		if ( (!($pData['username'] == $wpData->user_nicename)) &&  (!empty($pData['username'])) ) {
			update_usermeta( $wpData->ID, 'phpbb_userLogin', $pData['username']);
			$update['user_nicename'] = $pData['username'];
			$update['nickname'] = $pData['username'];
			$update['display_name'] = $pData['username'];
			$doWpUpdate = true;
		}
	}
	
	// When items are not set, the WP User object variables are non-existent.
	// So we cast the object to an array with all fields set
	$fields = array('user_email', 'user_pass', 'user_url', 'aim', 'yim', 'jabber');
	foreach($fields as $field) {
		if(isset($wpData->$field)) {
			$wpDataArr[$field] = $wpData->$field;
		} else {
			$wpDataArr[$field] = '';
		}
	}
	
	if ( (!($pData['user_email'] == $wpData->user_email)) && (isset($pData['user_email'])) ) {
		$update['user_email'] = $pData['user_email'];
		$doWpUpdate = true;
	} 
	
	// Store our password in a WordPress compatible format
	if(substr($pData['user_password'], 0, 3) == '$H$') {
		$pData['user_password'] = substr_replace($pData['user_password'], '$P$', 0, 3);
	}
	
	if ( ($pData['user_password'] != $wpDataArr['user_pass']) && (!empty($pData['user_password'])) && (isset($pData['user_password'])) ) {
		$update['user_pass'] = $pData['user_password']; 
		$doWpUpdate = true;
	}
	if ( (!($pData['user_website'] == $wpDataArr['user_url'])) && (isset($pData['user_website'])) ) {
		$update['user_url'] = $pData['user_website'];
		$doWpUpdate = true;
	}
	if ( ($pData['user_aim'] != $wpDataArr['aim']) && (isset($pData['user_aim'])) ) {
		$update['aim'] = $pData['user_aim'];
		$doWpUpdate = true;
	}
	if ( ($pData['user_yim'] != $wpDataArr['yim']) && (isset($pData['user_yim'])) ) {
		$update['yim'] = $pData['user_yim'];
		$doWpUpdate = true;
	}
	if ( ($pData['user_jabber'] != $wpDataArr['jabber']) && (isset($pData['user_jabber'])) ) {
		$update['jabber'] = $pData['user_jabber'];
		$doWpUpdate = true;
	}
	if ( ($pData['user_avatar_type'] != $wpMeta['wpu_avatar_type']) && (isset($pData['user_avatar_type'])) ) {
		if ( !empty($wpData->ID) ) {
			update_usermeta( $wpData->ID, 'wpu_avatar_type', $pData['user_avatar_type']);
		}
	}
	if ( ($pData['user_avatar'] != $wpMeta['wpu_avatar']) && (isset($pData['user_avatar'])) ) {
		if ( !empty($wpData->ID) ) {
			update_usermeta( $wpData->ID, 'wpu_avatar', $pData['user_avatar']);
		}
	}

	if ( (!($pData['user_avatar_width'] == $wpMeta['wpu_avatar_width'])) && (isset($pData['user_avatar_width'])) ) {
		if ( !empty($wpData->ID) ) {
			update_usermeta( $wpData->ID, 'wpu_avatar_width', $pData['user_avatar_width']);
		}
	}	
	if ( (!($pData['user_avatar_height'] == $wpMeta['wpu_avatar_height'])) && (isset($pData['user_avatar_height'])) ) {
		if ( !empty($wpData->ID) ) {
			update_usermeta( $wpData->ID, 'wpu_avatar_height', $pData['user_avatar_height']);
		}
	}								
	if ( $doWpUpdate ) {
		if ( !empty($wpData->ID) ) {
			$update['ID'] = $wpData->ID;
		}
		return $update;
	} else {
		return false;
	}	
}

/**
 * Log users into WordPresss -- It's a private function, designed to be called from
 * do_integrate_login(). It handles the various methods of logging into WP, maintaining backwards compatibility
 */
function wpu_sign_in($wpUsr, $pass) { 

	/* This overrides authentication in wp_check_password() [wp-functions.php]
	 * This is OK to set here, as phpBB has already dealt with integration.
	 * DO NOT define this anywhere else, ever!
	 */
	define('PASSWORD_ALREADY_HASHED', TRUE);	
	
	global $error;
	if ( function_exists('wp_signon') ) {
		$result = wp_signon(array('user_login' => $wpUsr, 'user_password' => $pass, 'remember' => false));
		if ( !is_wp_error($result) ) {
			return true;
		} 
		$error = $result->get_error_message();
	} else { 
		if ( wp_login($wpUsr, md5($pass), true) ) {
			wp_setcookie($wpUsr, md5($pass), true, '', '', false);
			do_action('wp_login', $wpUsr);
			return true;
		}
	}
	return false;
}

/**
 * Arbitrates the user permissions between phpBB and WordPress. Called internally by integrate_login
 * @param int $ID WordPress ID
 * @param the required WordPress role
 */
function wpu_check_userlevels ($ID, $usrLevel) {
	global $userdata;
	$user = new WP_User($ID);
	$user->set_role($usrLevel);
	$userdata = get_userdata($ID);
	wp_set_current_user($ID);
	return $userdata;
}





?>