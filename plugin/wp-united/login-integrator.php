<?php

/** 
*
* WP-United Login Integrator
*
* @package WP-United
* @version $Id: v0.8.5RC2 2012/11/04 John Wells (Jhong) Exp $
* @copyright (c) 2006-2012 wp-united.com
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
	global $wpUnited, $lDebug, $phpbbForum;

	// sometimes this gets called early, e.g. for admin ajax calls.
	if(!$phpbbForum->is_phpbb_loaded()) {
		return;
	}

	require_once($wpUnited->get_plugin_path() . 'debugger.php'); 

	$lDebug = new WPU_Debug();
				// TODO: CHECK WE ARE NOT **IN** PHPBB!!!
	if( !$phpbbForum->user_logged_in() ) { 
		return  wpu_int_phpbb_logged_out(); 
	}

	return  wpu_int_phpbb_logged_in();
	
	
}

/**
 * What to do when the user is logged out of phpBB
 * in WP-United prior to v0.9.0, we would forcibly log them out
 * However this is left open as a prelude to bi-directional user integration
 */
function wpu_int_phpbb_logged_out() { 
	global $lDebug, $phpbbForum, $wpUnited, $user;

	// Check if user is logged into WP
	global $current_user; get_currentuserinfo();  $wpUser = $current_user;
	if(!$wpUser->ID) {
		return false;
	}

		$createdUser = false;
	
		$phpbbId = wpu_get_integrated_phpbbuser($wpUser->ID);
		
		
		if(!$phpbbId) { // The user has no account in phpBB, so we create one:
			
			if(sizeof(wpu_assess_newuser_perms())) {
			
				// We just create standard users here for now, no setting of roles
				$phpbbId = wpu_create_phpbb_user($wpUser->ID);
						
				if($phpbbId == 0) {
					wp_die('Could not add user to phpBB');
				} else if($phpbbId == -1) {
					wp_die('A suitable username could not be found in phpBB');
				}
				$createdUser = true;
			}
		} 

		// the user now has an integrated phpBB account, log them into it
		$fStateChanged = $phpbbForum->foreground();
		$user->session_create($phpbbId);
		$phpbbForum->restore_state($fStateChanged);
		
		if($createdUser) {
			wpu_sync_profiles($wpUsr, $phpbbForum->get_userdata(), 'sync');
		} 

		return $wpUser->ID;
			

	
	// this clears all WP-related cookies
	// wp_clear_auth_cookie();
	// return false; // old
	return $user;
}

function wpu_int_phpbb_logged_in() { 
	global $wpUnited, $lDebug, $phpbbForum, $wpUnited, $current_user;
	
	
	// Should this user integrate? If not, we can just let WordPress do it's thing
	if( !$userLevel = wpu_get_user_level() ) {
		global $current_user; get_currentuserinfo();  return $current_user;
	}


	// This user is logged in to phpBB and needs to be integrated. Do they already have an integrated WP account?
	if($integratedID = wpu_get_integration_id() ) {
	
		// they already have a WP account, log them in to it and ensure they have the correct details
		if(!$wpUser = get_userdata($integratedID)) {
			return false;
		}
		
		// must set this here to prevent recursion
		wp_set_current_user($wpUser->ID);

		wpu_set_role($wpUser->ID, $userLevel);   // TODO: Stop killing main WP admin role
		
		wp_set_auth_cookie($wpUser->ID);
		return $wpUser->ID;
		
	} else {  
		

		static $createdUser;

		// to prevent against recursion in strange error scenarios
		if(isset($createdUser) && $createdUser > 0) {
			return $createdUser;
		}
		// they don't have an account yet, create one
		$signUpName = $phpbbForum->get_username();
		
		$newUserID = wpu_create_wp_user($signUpName, $phpbbForum->get_userdata('user_password'), $phpbbForum->get_userdata('user_email'));
		
		if($newUserID) { 
			
		   if(!is_a($newUserID, 'WP_Error')) {
				$wpUser = get_userdata($newUserID);
				// must set this here to prevent recursion
				wp_set_current_user($wpUser->ID);
				wpu_set_role($wpUser->ID, $userLevel);		
				wpu_update_int_id($phpbbForum->get_userdata('user_id'), $wpUser->ID);
				wpu_sync_profiles($wpUser, $phpbbForum->get_userdata(), 'sync');
				wp_set_auth_cookie($wpUser->ID);
				
				$createdUser = $wpUser->ID;
				
				//do_action('auth_cookie_valid', $cookie_elements, $wpUser->ID);
				return $wpUser->ID; 
			}
		}
	}
	return false;		
	
}

/**
 * Simple function to add a new user while preventing firing of the WPU user register hook
 */
function wpu_create_wp_user($signUpName, $password, $email) {
	global $wpUnited;
	

	if(! $foundName = wpu_find_next_avail_name($signUpName, 'wp') ) {

			return false;
	}

	$newWpUser = array(
		'user_login'	=> 	$foundName,
		'user_pass'		=>	$password,
		'user_email'	=>	$email
	); 

	// remove WP-United hook so we don't get stuck in a loop on new user creation
	if(!remove_action('user_register', array($wpUnited, 'process_new_wp_reg'), 10, 1)) {
		return false;
	}

	$newUserID = wp_insert_user($newWpUser);
	
	// reinstate the hook
	add_action('user_register', array($wpUnited, 'process_new_wp_reg'), 10, 1); 
	
	return $newUserID;
	
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
* Function 'wpu_fix_blank_username()' - Generates a username in WP when the sanitized username is blank,
* as phpbb is more liberal in user naming
* Originally by Wintermute
* If the sanitized userLogin is blank, create a random
* username inside WP. The userLogin begins with WPU followed
* by a random number (1-10) of digits between 0 & 9
* Also, check to make sure the userLogin is unique
* @since WP-United 0.7.1
*/
function wpu_fix_blank_username($userLogin) {
	if (empty($userLogin)){
		$foundFreeName = FALSE;
		while ( !$foundFreeName ) {
			$userLogin = 'WPU';
			srand(time());
			for ($i=0; $i < (rand()%9)+1; $i++)
				$userLogin .= (rand()%9);
			if ( !username_exists($userLogin) )
				$foundFreeName = TRUE;
		}
	}
	return $userLogin;
}


/**
 * Validates a new or prospective WordPress user in phpBB
 * @param string $username username
 * @param string $email e-mail
 * @param WP_Error $errors WordPress error object
 * @return bool|WP_Error false (on success) or modified WP_Error object (on failure)
 */
function wpu_validate_new_user($username, $email, $errors) {
	global $phpbbForum;
	$foundErrors = 0;
	
	
	if(function_exists('phpbb_validate_username')) {
		$fStateChanged = $phpbbForum->foreground();
		$result = phpbb_validate_username($username, false);
		$emailResult = validate_email($email);
		$phpbbForum->restore_state($fStateChanged);

		if($result !== false) {
			switch($result) {
				case 'INVALID_CHARS':
					$errors->add('phpbb_invalid_chars', __('The username contains invalid characters'));
					$foundErrors++;
					break;
				case 'USERNAME_TAKEN':
					$errors->add('phpbb_username_taken', __('The username is already taken'));
					$foundErrors++;
					break;
				case 'USERNAME_DISALLOWED':
					default;
					$errors->add('phpbb_username_disallowed', __('The username you chose is not allowed'));
					$foundErrors++;
					break;
			}
		}
		
		if($emailResult !== false) {
			switch($emailResult) {
				case 'DOMAIN_NO_MX_RECORD':
					$errors->add('phpbb_invalid_email_mx', __('The email address does not appear to exist (No MX record)'));
					$foundErrors++;
					break;
				case 'EMAIL_BANNED':
					$errors->add('phpbb_email_banned', __('The e-mail address is banned'));
					$foundErrors++;
					break;
				case 'EMAIL_TAKEN':
					$errors->add('phpbb_email_taken', __('The e-mail address is already taken'));
					break;
				case 'EMAIL_INVALID':
					default;
					$errors->add('phpbb_invalid_email', __('The email address is invalid'));
					$foundErrors++;
					break;									
			}
		}

	}
	return ($foundErrors) ? $errors : false;
	
}



/**
 * Creates a new integrated user in phpBB to match a given WordPress user
 * @param int $userID the WordPress userID
 * @return int < 1 on failure; >=1 phpbb User ID on success
 */
function wpu_create_phpbb_user($userID) {
	global $phpbbForum;

	if(!$userID) {
		return -1;
	}

	$wpUsr = get_userdata($userID);
	
	$fStateChanged = $phpbbForum->foreground();
	
	$password = wpu_convert_password_format($wpUsr->user_pass, 'to-phpbb');

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
		update_user_meta( $wpUsr->ID, 'phpbb_userid', $pUserID);
		

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

	static $perms = false;
	
	if(is_array($perms)) {
		return $perms;
	}
	
	
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
	
	$permCacheKey = '';
	
	if( ($groupList == '') || $groupList == array() ) {
		$where = '';
		$permCacheKey = '[BLANK]';
	} else {
		$groupList = (array)$groupList;
		if(sizeof($groupList) > 1) {
			$where = 'AND ' . $db->sql_in_set('group_name', $groupList);
		} else {
			$where = "AND group_name = '" . $db->sql_escape($groupList[0]) . "";
		}
		$permCacheKey = implode('|', $groupList);
	}
	
	
	static $cachedPerms = array();
	
	if (isset($cachedPerms[$permCacheKey])) {
			return $cachedPerms[$permCacheKey];
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
	
	$cachedPerms[$permCacheKey] = (array)$calculatedPerms;

	return $cachedPerms[$permCacheKey];
	
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
		'u_wpu_subscriber' 			=>	'subscriber',
		'u_wpu_contributor' 		=>	'contributor',
		'u_wpu_author' 				=>	'author',
		'm_wpu_editor' 				=>	'editor',
		'a_wpu_administrator' 		=>	'administrator'
	);
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
			trigger_error(__('WP-United could not update your integration ID in phpBB, due to a database access error. Please contact an administrator and inform them of this error.'));
		} else {
			$updated = TRUE;
		}
	}
	//Switch back to the WP DB:
	$phpbbForum->restore_state($fStateChanged);
	if ( !$updated ) {
		trigger_error(__('Could not update integration data: WP-United could not update your integration ID in phpBB, due to an unknown error. Please contact an administrator and inform them of this error.'));
	}
}	

/**
 * Bi-direcitonal profile synchroniser.
 * 
 * @param mixed $wpData WordPress user object or array of data
 * @param mixed $pData phpBB user data
 * @param string $action = sync | phpbb-update | wp-update
 * @return bool true if something was updated
*/
function wpu_sync_profiles($wpData, $pData, $action = 'sync') {
	global $wpUnited, $phpbbForum, $wpdb;

	if(is_object($wpData)) { 
		$wpData = (array)get_object_vars($wpData->data); 
	} 

	$wpMeta = get_user_meta($wpData['ID']);

	if( !isset($wpData['ID']) || empty($wpData['ID']) || empty($pData['user_id']) ) {
		return false;
	}

	/**
	 * 
	 *	First, update normal profile fields
	 *
	 */
	
	// Our profile fields to synchronise:
	$fields = array(
		array('wp'	=>	'user_nicename','phpbb'	=> 'username', 		'type'	=>	'main', 'dir' => 'wp-only'),
		array('wp'	=>	'nickname',		'phpbb'	=> 'username', 		'type'	=>	'main', 'dir' => 'wp-only'),
		array('wp'	=>	'display_name',	'phpbb'	=> 'username', 		'type'	=>	'main', 'dir' => 'wp-only'),
		array('wp'	=>	'user_email',	'phpbb'	=> 'user_email', 	'type'	=>	'main', 'dir' => 'bidi'),
		array('wp'	=>	'user_url',		'phpbb' => 'user_website', 	'type'	=>	'main', 'dir' => 'bidi'),
		array('wp'	=>	'phpbb_userid',	'phpbb' => 'user_id',		'type'	=>	'meta', 'dir' => 'wp-only'),
		array('wp'	=>	'aim',			'phpbb' => 'user_aim', 		'type'	=>	'meta', 'dir' => 'bidi'),
		array('wp'	=>	'yim',			'phpbb' => 'user_yim', 		'type'	=>	'meta', 'dir' => 'bidi'),
		array('wp'	=>	'jabber',		'phpbb' => 'user_jabber', 	'type'	=>	'meta', 'dir' => 'bidi')
	);	
	
	$updates = array('wp' => array(),	'phpbb' => array());
	

	foreach($fields as $field) {
		
		$type = $field['type'];
		$wpField = $field['wp'];
		$pField = $field['phpbb'];
		$dir = $field['dir'];
		
		// initialise items in both data arrays so we can compare them
		$pFieldData = (isset($pData[$pField])) ? $pData[$pField] : '';
		if($type == 'main') {
			$wpFieldData = (isset($wpData[$wpField])) ? $wpData[$wpField] : '';
		} else {
			$wpFieldData = (isset($wpMeta[$wpField])) ? $wpMeta[$wpField][0] : '';
		}
		
		switch($action) {
			case 'wp-update': // WP profile has been updated, so send to phpBB
				if((!empty($wpFieldData)) && ($dir != 'wp-only')) {
					$updates['phpbb'][$pField] = $wpFieldData;
				}
				break;
				
			case 'phpbb-update': // phpBB profile has been updated, so send to WP
				if((!empty($pFieldData)) && ($dir != 'phpbb-only')) {
					$updates['wp'][$wpField] = $pFieldData;
				}
				break;
				
			case 'sync': // initial sync of profiles, so fill in whatever we can on both sides
			default;
				if((!empty($wpFieldData)) && (empty($pFieldData)) &&  ($dir != 'wp-only')) {
					$updates['phpbb'][$pField] = $wpFieldData;
				}
				if((!empty($pFieldData)) && (empty($wpFieldData)) && ($dir != 'phpbb-only')) {
					$updates['wp'][$wpField] = $pFieldData;
				}
				break;
		}
	}
	
	/**
	 * 
	 *	Next, sync avatars
	 *   TODO: check if wpuput and if avatar_type == AVATAR_REMOTE. If so then can sync p -> w too!
	 */
				
	// sync avatar WP -> phpBB
	if((action != 'phpbb-update') &&  ($wpUnited->get_setting('avatarsync'))){
		// is the phpBB avatar empty already, or was it already put by WP-United?
		if(empty($pData['user_avatar']) || (stripos($pData['user_avatar'], 'wpuput=1') !== false)) { 
			
			$avatarSize = 90;
			
			// we send an avatar. First we need to get the WP one -- remove our filter hook
			if(remove_action('get_avatar', array($wpUnited, 'get_avatar'), 10, 5)) {
				
				// Gravatars are predicated on user e-mail. If we send ID instead, get_avatar could just return a default as the user might not
				// have cached data yet. E-mail is also faster as it doesn't need to be converted.
				$avatar = get_avatar($wpData['user_email'], $avatarSize);
				if(!empty($avatar)) {
					if(stripos($avatar, includes_url('images/blank.gif')) === false) {
						$avatarDetails = $phpbbForum->convert_avatar_to_phpbb($avatar, $pData['user_id'], $avatarSize, $avatarSize);
						$updates['phpbb'] = array_merge($updates['phpbb'], $avatarDetails);
					}
				}
				// reinstate our action hook:
				add_action('get_avatar', array($wpUnited, 'get_avatar'), 10, 5);
			}
		}
	}	
		
	 /**
	 * 
	 *	Compare and update passwords
	 *
	 */	
	if(($action == 'phpbb-update') || ($action == 'sync')) { // updating phpBB profile or syncing
		
		// convert password to WP format for comparison, as that will be the destination if it is different
		$pData['user_password'] = wpu_convert_password_format($pData['user_password'], 'to-wp');
		// wp_update_user double-hashes the password so we handle it separately, now
		if($pData['user_password'] != $wpData['user_pass']) {
			$wpdb->update($wpdb->users, array('user_pass' => stripslashes($pData['user_password'])) , array('ID' => (int)$wpData['ID']), '%s', '%d');
			wp_cache_delete($wpData['ID'], 'users');
			wp_cache_delete($wpData['ID'], 'userlogins');
		}
		
	} else if($action == 'wp-update') {	// updating WP profile 
		
		// convert password to phpBB format for comparison, as that will be the destination if it is different
		$wpData['user_pass'] = wpu_convert_password_format($wpData['user_pass'], 'to-phpbb');

		// for phpBB we can update along with everything else
		if($pData['user_password'] != $wpData['user_pass']) {
			$updates['phpbb']['user_password'] = $wpData['user_password'];
		}
		
	}


	/**
	 * 
	 *	Commit changes
	 *
	 */
	$updated = false;
	
	// Update phpBB items
	if(sizeof($updates['phpbb'])) { 
		$phpbbForum->update_userdata($pData['user_id'], $updates['phpbb']);
		$updated = true;
	}
	
	// update WP items
	if(sizeof($updates['wp'])) {
		$updates['wp']['ID'] = $wpData['ID'];
		
		// prevent our hook from firing
		remove_action('profile_update', array($wpUnited, 'profile_update'), 10, 2);
		$userID = wp_update_user($updates['wp']);
		add_action('profile_update', array($wpUnited, 'profile_update'), 10, 2);
		$updated = true; 
	}

	return $updated;

}	

/**
 * phpBB and WordPress passwords are compatible. phPBB marks the hash with a $H$, while WordPress uses $P$
 * So we just need to convert between them.
 */

function wpu_convert_password_format($password, $direction = 'to-phpbb') {

	switch($direction) {
	
		case 'to-phpbb':
			$from = '$P$';
			$to = '$H$';
			break;
			
		case 'to-wp':
			$from = '$H$';
			$to = '$P$';
			break;
			
		default;
			return $password;
	}
	
	if(substr($password, 0, 3) == $from) { 
		$password = substr_replace($password, $to, 0, 3); 
	}
	return $password;

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
