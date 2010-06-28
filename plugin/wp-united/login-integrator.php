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
if ( !defined('ABSPATH') && !defined('IN_PHPBB') ) {
	exit;
}

/**
 * Gets the integration ID for the current user
 * @return int phpBB User ID, or zero
 */
function wpu_get_integration_id() {
	global $phpbbForum;
	if( array_key_exists('user_wpuint_id', $phpbbForum->get_userdata()) ) {
		return $phpbbForum->get_userdata('user_wpuint_id');
	} 
	return 0;
}


/**
 * Gets the logged-in user's effective WP-United permissions
 * @return mixed WordPress user level, or false if no permissions
 */
function wpu_get_user_level() {
	global $phpbbForum, $lDebug, $auth;

	$phpbbForum->enter_if_out();
	
	$userLevel = false;
	
	// if checking for the current user, do a sanity check
	if ( (!$phpbbForum->user_logged_in()) || !in_array($phpbbForum->get_userdata('user_type'), array(USER_NORMAL, USER_FOUNDER)) ) {
		return false;
	}
	
	$wpuPermissions = array(
		'u_wpu_subscriber' 		=>	'subscriber',
		'u_wpu_contributor' 		=>	'contributor',
		'u_wpu_author' 				=>	'author',
		'm_wpu_editor' 				=>	'editor',
		'a_wpu_administrator' 	=>	'administrator'
	);
	
	// Higher permissions override lower ones, so we work from the bottom up to find the users'
	// actual level
	$debug = 'Checking permissions: ';
	foreach($wpuPermissions as $perm => $desc) {
		if( $auth->acl_get($perm) ) {
			$userLevel = $desc;
			$debug .= '[' . $desc . ']';
		}
	}
	
	$lDebug->add($debug);
	$lDebug->add('User level set to: ' . $userLevel);
	
	$phpbbForum->leave_if_just_entered();
	return $userLevel;

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
function wpu_update_int_id($pID, $intID) {
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
 * @param bool $newuser set to true to populate profile fields
 * @return mixed array of fields to update
 */
function wpu_make_profiles_consistent($wpData, $pData, $newUser = false) {	

	if( empty($wpData->ID) ) {
		return false;
	}

	$wpMeta = get_usermeta($wpData->ID);
		
	// initialise wp-united meta fields to prevent PHP notices later on
	$wpuSpecialFields = array('wpu_avatar_width', 'wpu_avatar_height', 'wpu_avatar_type', 'wpu_avatar');
	foreach($wpuSpecialFields as $key) {
		if(!isset($wpMeta[$key])) {
			$wpMeta[$key] = '';
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
		$update['ID'] = $wpData->ID;
		define('PASSWORD_ALREADY_HASHED', TRUE);
		if($loggedInID = wp_update_user($wpUpdateData)) {
			return $update;
		}
	}
	
	return 0;
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

/**
 * Sets the user role before they get logged in
 * This writes the data to the DB
 * @param int $id WordPress user ID
 * @param string $userLevel WordPress role
 */
function wpu_set_role($id, $userLevel) {
	$user = new WP_User($id);
	if($user->roles != array($userLevel)) { echo "changing role";
		$user->set_role($userLevel);
	}
}

?>