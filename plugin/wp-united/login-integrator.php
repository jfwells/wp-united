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
if ( !defined('ABSPATH') ) {
	exit;
}

/**
 * The main login integration routine
 */
function wpu_integrate_login() {
	global $wpuPath, $lDebug, $phpbbForum;
	
	require_once($wpuPath. 'debugger.php');
	$lDebug = new WPU_Debug();

	if( !$phpbbForum->user_logged_in() ) {
		return  wpu_int_phpbb_logged_out();
	}
	
	return  wpu_int_phpbb_logged_in();
	
	return $user;
	
}

/**
 * What to do when the user is logged out of phpBB
 * in WP-United prior to v0.9.0, we would forcibly log them out
 * However this is left open as a prelude to bi-directional user integration
 */
function wpu_int_phpbb_logged_out() { 
	global $wpSettings, $lDebug, $phpbbForum, $wpuPath, $current_user, $user;
			
	// Check if user is logged into WP
	get_currentuserinfo();
	if ( ! $wpUser = $current_user ) {
		return false;
	}
	
	//if($wpSettings['integsource'] == 'wp') {
	
		$wpIntID = wpu_get_integrated_phpbbuser($wpUser->ID);

		if($wpIntID) {
			// the user has an integrated phpBB account, log them into it
			$fStateChanged = $phpbbForum->foreground();
			$user->session_create($wpIntID);
			$phpbbForum->restore_state($fStateChanged);
			
			wpu_make_profiles_consistent($wpUser, $phpbbForum->get_userdata(), false);
			
			return $wpUser->ID;
			
		} else { 
			// DISABLED FOR NOW
			// The user's account is NOT integrated!
			// What to do?
			// Need also WordPress->phpbb mapping to decide how to create a user
			/* if( ! $signUpName =  wpu_find_next_avail_name($wpUser->user_login, 'PHPBB') ) {
				return false;
			} */
			//$phpbbForum->add_user(xxxx) // see phpbb function user_add()
			// CREATE USER
			// MAP DETAILS
			// LOG IN
			/// [ or -- do all from phpbb ]
		}
	//}
	
	// this clears all WP-related cookies
	// wp_clear_auth_cookie();
	// return false; // old
	return $user;
}

function wpu_int_phpbb_logged_in() { 
	global $wpSettings, $lDebug, $phpbbForum, $wpuPath, $current_user;
	
	
	//if($wpSettings['integsource'] != 'phpbb') {
	//	return get_currentuserinfo();
	//}
	
	// Should this user integrate? If not, we can just let WordPress do it's thing
	if( !$userLevel = wpu_get_user_level() ) {
		return get_currentuserinfo();
	}

	// This user is logged in to phpBB and needs to be integrated. Do they already have a WP account?
	if($integratedID = wpu_get_integration_id() ) {
	
		// they already have a WP account, log them in to it and ensure they have the correct details
		if(!$wpUser = get_userdata($integratedID)) {
			return false;
		}
		
		// must set this here to prevent recursion
		wp_set_current_user($wpUser->ID);

		wpu_set_role($wpUser->ID, $userLevel);
		wpu_make_profiles_consistent($wpUser, $phpbbForum->get_userdata(), false);
		wp_set_auth_cookie($wpUser->ID);
		return $wpUser->ID;
		
	} else {
		// to prevent against recursion in strange error scenarios
		if(defined('WPU_CREATED_WP_USER')) {
			return WPU_CREATED_WP_USER;
		}
		// they don't have an account yet, create one
		require_once( ABSPATH . WPINC . '/registration.php');
		$signUpName = $phpbbForum->get_username();
		
		if(! $signUpName = wpu_find_next_avail_name($signUpName, 'wp') ) {
			return false;
		}

		$newWpUser = array(
			'user_login'	 	=> 	$signUpName,
			'user_pass'		=>	$phpbbForum->get_userdata('user_password'),
			'user_email'	=>	$phpbbForum->get_userdata('user_email')
		); 
		
		// remove WP-United hook so we don't get stuck in a loop on new user creation
		if(!remove_action('user_register', 'wpu_check_new_user_after', 10, 1)) {
			return false;
		}
		
		$newUserID = wp_insert_user($newWpUser);
		
		// reinstate the hook
		add_action('user_register', 'wpu_check_new_user_after', 10, 1); 
		
		if($newUserID) { 
			
		   if(!is_a($newUserID, 'WP_Error')) {
				$wpUser = get_userdata($newUserID);
				// must set this here to prevent recursion
				wp_set_current_user($wpUser->ID);
				wpu_set_role($wpUser->ID, $userLevel);		
				wpu_update_int_id($phpbbForum->get_userdata('user_id'), $wpUser->ID);
				wpu_make_profiles_consistent($wpUser, $phpbbForum->get_userdata(), true);
				wp_set_auth_cookie($wpUser->ID);
				define('WPU_CREATED_WP_USER', $wpUser->ID); 
				//do_action('auth_cookie_valid', $cookie_elements, $wpUser->ID);
				return $wpUser->ID; 
			}
		}
	}
	return false;		
	
}


/**
 * Logs the user out of phpBB if they log out of WordPress
 */
 function wpu_wp_logout() {
	 global $phpbbForum;
	 
	 $phpbbForum->logout();
	 
}



/**
 * Finds the next available username in WordPress or phpBB
 * @param string $name the desired username
 * @param $package the application to search in
 */
function wpu_find_next_avail_name($name, $package = 'wp') {
	
	global $phpbbForum, $phpbb_root_path, $phpEx;
	
	$i = 0;
	$foundFreeName = $result = false;
	
	
	//start with the plain username, if unavailable then append a number onto the login name until we find one that is available
	if($package == 'wp') {
		$name = $newName = sanitize_user($name, true);
		while ( !$foundFreeName ) {
			if ( !username_exists($newName) ) {
				$foundFreeName = true;
			} else {
				// This username already exists.
				$i++; 
				$newName = $name . $i;
			}
		}
		return $newName;
	} else {
			// search in phpBB
			$fStateChanged = $phpbbForum->foreground();
			require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			$newName = $name;
			while ( !$foundFreeName ) {
				$result = phpbb_validate_username($newName);
				if($result === false) {
					$foundFreeName = true;
				} else if($result != 'USERNAME_TAKEN') {
					$phpbbForum->restore_state($fStateChanged);
					return false;
				} else {
					$i++;
					$newName = $name . $i;
				}
			}
			$phpbbForum->restore_state($fStateChanged);
		
		return $newName;
	}
}

/**
 * Creates a new integrated user in phpBB to match a given WordPress user
 * @param int $userID the WordPress userID
 * @return int < 1 on failure; >=1 phpbb User ID on success
 */
function wpu_create_phpbb_user($userID) {
	global $phpbbForum;
	
	$wpUsr = get_userdata($userID);
	
	$fStateChanged = $phpbbForum->foreground();
	
	$password = $wpUsr->user_pass;
	if(substr($password, 0, 3) == '$P$') {
		$password = substr_replace($password, '$H$', 0, 3);
	}

	// validates and finds a unique username
	if(! $signUpName = wpu_find_next_avail_name($wpUsr->user_login, 'phpbb') ) {
		return -1;
	}

	$pUserID = 0;
				
	$userToAdd = array(
		'username' => $signUpName,
		'user_password' => $password,
		'user_email' => $wpUsr->user_email,
		'user_type' => USER_NORMAL,
		'group_id' => 2  //add to registered users group		
	);
	

				
	if ($pUserID = user_add($userToAdd)) {

		wpu_update_int_id($pUserID, $wpUsr->ID);
		/**
		 * @TODO: make consistent from phpBB -> WordPress
		 */
		//wpu_make_profiles_consistent($wpUsr, $wpuNewDetails, true);
	}

	$phpbbForum->restore_state($fStateChanged);

	
	return $pUserID;
}



/**
 * Determines if a non-integrated WP user can integrate into a new phpBB account.
 * @return array permission details in the user's language
 */
 
function wpu_assess_newuser_perms() {
	global $config;

	// if 0, they aren't added to the group -- else they are in group until they have this number of posts
	$newMemberGroup = ($config['new_member_post_limit'] != 0);
		
	$groups = ($newMemberGroup) ? array('REGISTERED', 'NEWLY_REGISTERED') : array('REGISTERED');
	
	return wpu_assess_perms($groups);
}

/**
 * Takes a list of group names to check, and returns an array of permissions due to role and direct permissions
 * (phpBB likes to over-complicate things, so need to check both :-/ )
 * @param array $groupList list of group names (language keys) to check
 * @return array permission details in the user's language
 */

function wpu_assess_perms($groupList = '') {
	global $phpbbForum, $config, $db, $user;
	
	$fStateChanged = $phpbbForum->foreground();
	
	if( ($groupList == '') || $groupList == array() ) {
		$where = '';
	} else {
		$groupList = (array)$groupList;
		if(sizeof($groupList) > 1) {
			$where = 'AND ' . $db->sql_in_set('group_name', $groupList);
		} else {
			$where = "AND group_name = '" . $db->sql_escape($groupList[0]) . "";
		}
	}
	
	
	$user->add_lang('acp/permissions');
		
	$perms = wpu_permissions_list();
	
	$sqlArr = array(
		'SELECT' 			=> 	'g.group_name, ao.auth_option, ag.auth_setting AS groupsetting, ar.role_name, ar.role_id, ar.role_type, ad.auth_setting AS rolesetting',
		
		'FROM' 			=> 	array(
			GROUPS_TABLE 			=> 	'g',
			ACL_GROUPS_TABLE 	=> 	'ag'
		),
	
		'LEFT_JOIN' 	=> 	array(
			array(
				'FROM'		=>	array(ACL_ROLES_TABLE => 'ar'),
				'ON'				=> 	'ag.auth_role_id = ar.role_id'
			),
			array(
				'FROM'		=>	array(ACL_ROLES_DATA_TABLE => 'ad'),
				'ON'				=> 	'ar.role_id = ad.role_id'
			),
			array(
				'FROM'		=>	array(ACL_OPTIONS_TABLE => 'ao'),
				'ON'				=>	'ag.auth_option_id = ao.auth_option_id 
													OR ad.auth_option_id = ao.auth_option_id'
			),		
		),
		'WHERE' 			=> 	'ag.group_id = g.group_id
											AND ' . $db->sql_in_set('ao.auth_option', array_keys($perms)) . 
												$where,
									
		'ORDER_BY'	=>	'g.group_type DESC, g.group_name ASC',
	);
	
		
	$sql = $db->sql_build_query('SELECT',$sqlArr);
	$result = $db->sql_query($sql);
	
	$calculatedPerms = array();
	
	while ($row = $db->sql_fetchrow($result)) {
		$group = (isset($user->lang['G_' . $row['group_name']])) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];
		$stg = (!empty($row['role_name'])) ? $row['rolesetting'] : $row['groupsetting']; 
		switch($stg) {
			case ACL_YES:
				$setting = $user->lang['ACL_YES'];
				break;
			case ACL_NEVER:
				$setting = $user->lang['ACL_NEVER'];
				break;
			case ACL_NO:
			default;
				$setting = $user->lang['ACL_NO'];
		}
		
		$roleType = '';
		if(!empty($row['role_name'])) {
			$role = (isset($user->lang[$row['role_name']])) ? $user->lang[$row['role_name']] : $row['role_name'];
			switch($row['role_type']) {
				case 'm_':
					$roleType = 'mod';
					break;
				case 'a_':
					$roleType = 'admin';
					break;
				case 'f_':
				case 'u_':
				default;
					// we only want global roles, so for f_, fall back to user
					$roleType = 'user';
					break;
			}
		} else {
			$role = '';
		}
		if(!isset($calculatedPerms[$group])) {
			$calculatedPerms[$group] = array();
		}
		$calculatedPerms[$group][] = array(
			'rolename' 		=> 	$role,
			'perm'				=>	$row['auth_option'],
			'settingText'	=>	$setting,
			'setting'			=>	$stg,
			'roleid'				=>	$row['role_id'],
			'roletype'		=>	$roleType
		);
	}
	
	$db->sql_freeresult($result);
	
	$phpbbForum->restore_state($fStateChanged);

	return (array)$calculatedPerms;
	
}

/**
 * Gets the integration ID for the current phpBB user, or for a provided phpBB user ID
 * @param in $userID phpBB user ID (optional)
 * @return int WordPress User ID, or zero
 */
function wpu_get_integration_id($userID = 0) {
	global $phpbbForum, $db;
	
	$userID = (int)$userID;
	
	if($userID == 0) {
		if( array_key_exists('user_wpuint_id', $phpbbForum->get_userdata()) ) {
			return $phpbbForum->get_userdata('user_wpuint_id');
		} 
	} else {
		$fStateChanged = $phpbbForum->foreground();
		
		$sql = 'SELECT user_wpuint_id FROM ' . USERS_TABLE . ' 
					WHERE user_id = ' . $userID;
		
		if(!$result = $db->sql_query($sql)) {
			$wUserID = 0;
		} else {
			$wUserID = $db->sql_fetchfield('user_wpuint_id');
		}
		$db->sql_freeresult();
				
		$phpbbForum->restore_state($fStateChanged);
		
		return $wUserID;
		
	}
	return 0;
}

/**
 * Gets the integration ID for the current WordPress user, or for a provided WordPress user ID
 * @param in $userID WordPress user ID (optional)
 * @return int phpBB User ID, or zero
 */
function wpu_get_integrated_phpbbuser($userID = 0) {
	global $current_user, $phpbbForum, $db;

	
		
	$userID = (int)$userID;
	
	if($userID == 0) {
	
		$current_user =  wp_get_current_user();
		$userID = $current_user->ID;
		
		if($userID == 0) {
			return 0;
		}
		
	}
	
	$fStateChanged = $phpbbForum->foreground();
		
	$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' 
				WHERE user_wpuint_id = ' . $userID;
		
	if(!$result = $db->sql_query_limit($sql, 1)) {
		$pUserID = 0;
	} else {
		$pUserID = $db->sql_fetchfield('user_id');
	}
	$db->sql_freeresult();
			
	$phpbbForum->restore_state($fStateChanged);
	
	return $pUserID;

}


/**
 * Gets the logged-in user's effective WP-United permissions
 * @return mixed WordPress user level, or false if no permissions
 */
function wpu_get_user_level() {
	global $phpbbForum, $lDebug, $auth;

	$fStateChanged = $phpbbForum->foreground();
	
	$userLevel = false;
	
	// if checking for the current user, do a sanity check
	if ( (!$phpbbForum->user_logged_in()) || !in_array($phpbbForum->get_userdata('user_type'), array(USER_NORMAL, USER_FOUNDER)) ) {
		return false;
	}
	
	$wpuPermissions = wpu_permissions_list();
	
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
	
	$phpbbForum->restore_state($fStateChanged);
	return $userLevel;

}

/** 
 * returns an array of WP-United permissions
 * Dead simple -- but called in several places
 * @TODO: Add custom wordpress roles?
 */

function wpu_permissions_list() {
	return array(
		'u_wpu_subscriber' 		=>	'subscriber',
		'u_wpu_contributor' 		=>	'contributor',
		'u_wpu_author' 				=>	'author',
		'm_wpu_editor' 				=>	'editor',
		'a_wpu_administrator' 	=>	'administrator'
	);
}


/**
 * Logs out from WP
 * Not currently used
 */
function wpu_wp_logout_legacy() {
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
	$fStateChanged = $phpbbForum->foreground();
	
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
	$phpbbForum->restore_state($fStateChanged);
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

	$wpMeta = get_user_meta($wpData->ID);
		
	// initialise wp-united meta fields to prevent PHP notices later on
	$wpuSpecialFields = array('wpu_avatar_width', 'wpu_avatar_height', 'wpu_avatar_type', 'wpu_avatar');
	foreach($wpuSpecialFields as $key) {
		if(!isset($wpMeta[$key])) {
			$wpMeta[$key] = '';
		}
	}
	
	// we use this so we can direct to their phpBB profile without faffing around
	if ($pData['user_id'] != $wpData->phpbb_userid) {
		update_user_meta( $wpData->ID, 'phpbb_userid', $pData['user_id']);
	}
	
	$doWpUpdate = false;
	// We only update the user's nicename, etc. on the first run -- they can change it thereafter themselves
	if($newUser) {
		if ( (!($pData['username'] == $wpData->user_nicename)) &&  (!empty($pData['username'])) ) {
			update_user_meta( $wpData->ID, 'phpbb_userLogin', $pData['username']);
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
		$update['user_pass'] =$pData['user_password']; 
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
			update_user_meta( $wpData->ID, 'wpu_avatar_type', $pData['user_avatar_type']);
		}
	}
	if ( ($pData['user_avatar'] != $wpMeta['wpu_avatar']) && (isset($pData['user_avatar'])) ) {
		if ( !empty($wpData->ID) ) {
			update_user_meta( $wpData->ID, 'wpu_avatar', $pData['user_avatar']);
		}
	}

	if ( (!($pData['user_avatar_width'] == $wpMeta['wpu_avatar_width'])) && (isset($pData['user_avatar_width'])) ) {
		if ( !empty($wpData->ID) ) {
			update_user_meta( $wpData->ID, 'wpu_avatar_width', $pData['user_avatar_width']);
		}
	}	
	if ( (!($pData['user_avatar_height'] == $wpMeta['wpu_avatar_height'])) && (isset($pData['user_avatar_height'])) ) {
		if ( !empty($wpData->ID) ) {
			update_user_meta( $wpData->ID, 'wpu_avatar_height', $pData['user_avatar_height']);
		}
	}								
	if ( $doWpUpdate ) {
		/**
		 * We re-implement most of wp_update_user here so that we can override the password hashing
		 * Before we just plugged wp_hash_password, but some naughty plugins (like Janrain) prevent us from
		 * being able to do that
		 */
		
		$update['ID'] = $wpData->ID;
		require_once( ABSPATH . WPINC . '/registration.php');
		$exstUser = get_userdata($update['ID']);
		$exstUser = add_magic_quotes(get_object_vars($exstUser));
		
		$userdata = array_merge($exstUser, $update);
		$user_id = wp_insert_user($userdata);
		
		$current_user = wp_get_current_user();
		if ( $current_user->id == $ID ) {
			if ( isset($update['user_pass']) ) {
				wp_clear_auth_cookie();
				wp_set_auth_cookie($ID);
			}
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
 * Sets the user role before they get logged in
 * This writes the data to the DB
 * Only updates if the role is not already correct
 * @param int $id WordPress user ID
 * @param string $userLevel WordPress role
 */
function wpu_set_role($id, $userLevel) {
	$user = new WP_User($id);
	if($user->roles != array($userLevel)) { 
		$user->set_role($userLevel);
	}
}

?>
