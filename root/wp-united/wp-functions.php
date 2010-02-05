<?php
/** 
*
* WP-United Modified Core Functions
*
* @package WP-United
* @version $Id: v0.8.4RC2 2010/01/14 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/

if (!defined('IN_PHPBB')) {
	exit;
}

/**
 * Overridden from pluggable.php
 * @param int $user_id WordPress user ID
 * Originally overridden in WP 2.1, updated for WP 2.2 and 2.7
 */
if ( !function_exists('wp_get_userdata') ) :
function wp_get_userdata( $user_id ) {
	global $wpdb, $wp_version;  //added wp_version
	
	if ( function_exists('absint') ) { // new WordPress
		$user_id = absint($user_id);
	} else { // old WordPress
		$user_id = (int) $user_id;
	}
	if ( $user_id == 0 )
		return false;

	$user = wp_cache_get($user_id, 'users');
	
	if ( $user )
		return $user;
	if ( ((float) $wp_version) >= 2.3 ) { // newer versions do more sql escaping
		if ( !$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE ID = %d LIMIT 1", $user_id)) )
			return false;
	} else {
		if ( !$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID = '$user_id' LIMIT 1") )
			return false;	
	}

	

	if ( ((float) $wp_version) >= 2.5 ) { // function simplified for newer WP
		_fill_user($user);
	} else { // old branches
	
		$wpdb->hide_errors();
		$metavalues = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = '$user_id'");
		$wpdb->show_errors();
		
		if ($metavalues) {
			foreach ( $metavalues as $meta ) {
				if ( ((float) $wp_version) < 2.2 ) {
					@ $value = unserialize($meta->meta_value);
					if ($value === FALSE)
						$value = $meta->meta_value;
				} else { //WP 2.2+ branch
					$value = maybe_unserialize($meta->meta_value);
				}
				$user->{$meta->meta_key} = $value;

				// We need to set user_level from meta, not row
				if ( $wpdb->prefix . 'user_level' == $meta->meta_key )
					$user->user_level = $meta->meta_value;
			} // end foreach
		} //end if

		// For backwards compat.
		if ( isset($user->first_name) )
			$user->user_firstname = $user->first_name;
		if ( isset($user->last_name) )
			$user->user_lastname = $user->last_name;
		if ( isset($user->description) )
			$user->user_description = $user->description;
		
		wp_cache_add($user_id, $user, 'users');
		if ( ((float) $wp_version) < 2.2 ) {
			wp_cache_add($user->user_login, $user, 'userlogins');
		} else { //WP 2.2 version
			wp_cache_add($user->user_login, $user_id, 'userlogins');
		}
	} 
	
	return $user;
}
endif;

/**
 * Arbitrates between the two equally-named functions, get_userdata
 * Called specifically from within WP-United when we know we want to get phpBB user data
 */
function get_phpbb_userdata($uid) {
	global $IN_WORDPRESS;
	$IN_WORDPRESS = 0;
	$data = get_userdata($uid);
	$IN_WORDPRESS = 1;
	return  $data;
}


/**
 * Here we handle the make_clickable collision. 
 * We branch depending on whether we're in WP at the time.
 * @param string $text The text to make clickable
 * @param string $server_url server url
 * @param string $class The clickable link's classname, defaults to 'postlink'
 */
if (!function_exists('make_clickable')) {
	function make_clickable($text, $server_url = false, $class = 'postlink') { //$server_url is for phpBB3 only $class is for later phpBB3 only
		global $IN_WORDPRESS, $usePhpBBComments;

		if ($IN_WORDPRESS && !$usePhpBBComments) {
			return wp_make_clickable($text); //WP version
		} else { //phpBB version
			if ($server_url === false) {
				$server_url = generate_board_url();
			}
			static $magic_url_match;
			static $magic_url_replace;
			static $static_class;
			if (!is_array($magic_url_match)) {
				$magic_url_match = $magic_url_replace = array();
				if (function_exists('make_clickable_callback')) { //latest phpBB3s
					$magic_url_match[] = '#(^|[\n\t (>.])(' . preg_quote($server_url, '#') . ')/(' . get_preg_expression('relative_url_inline') . ')#ie';
					$magic_url_replace[] = "make_clickable_callback(MAGIC_URL_LOCAL, '\$1', '\$2', '\$3', '$local_class')";
					$magic_url_match[] = '#(^|[\n\t (>.])(' . get_preg_expression('url_inline') . ')#ie';
					$magic_url_replace[] = "make_clickable_callback(MAGIC_URL_FULL, '\$1', '\$2', '', '$class')";
					$magic_url_match[] = '#(^|[\n\t (>])(' . get_preg_expression('www_url_inline') . ')#ie';
					$magic_url_replace[] = "make_clickable_callback(MAGIC_URL_WWW, '\$1', '\$2', '', '$class')";
					$magic_url_match[] = '/(^|[\n\t (>])(' . get_preg_expression('email') . ')/ie';
					$magic_url_replace[] = "make_clickable_callback(MAGIC_URL_EMAIL, '\$1', '\$2', '', '')";	
				} else { // phpBB3 v1.0 
					$magic_url_match[] = '#(^|[\n\t (])(' . preg_quote($server_url, '#') . ')/(' . get_preg_expression('relative_url_inline') . ')#ie';
					$magic_url_replace[] = "'\$1<!-- l --><a href=\"\$2/' . preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}/', '\\\\1', '\$3') . '\">' . preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}/', '\\\\1', '\$3') . '</a><!-- l -->'";
					$magic_url_match[] = '#(^|[\n\t (])(' . get_preg_expression('url_inline') . ')#ie';
					$magic_url_replace[] = "'\$1<!-- m --><a href=\"\$2\">' . ((strlen('\$2') > 55) ? substr(str_replace('&amp;', '&', '\$2'), 0, 39) . ' ... ' . substr(str_replace('&amp;', '&', '\$2'), -10) : '\$2') . '</a><!-- m -->'";
					$magic_url_match[] = '#(^|[\n\t (])(' . get_preg_expression('www_url_inline') . ')#ie';
					$magic_url_replace[] = "'\$1<!-- w --><a href=\"http://\$2\">' . ((strlen('\$2') > 55) ? substr(str_replace('&amp;', '&', '\$2'), 0, 39) . ' ... ' . substr(str_replace('&amp;', '&', '\$2'), -10) : '\$2') . '</a><!-- w -->'";
					$magic_url_match[] = '/(^|[\n\t )])(' . get_preg_expression('email') . ')/ie';
					$magic_url_replace[] = "'\$1<!-- e --><a href=\"mailto:\$2\">' . ((strlen('\$2') > 55) ? substr('\$2', 0, 39) . ' ... ' . substr('\$2', -10) : '\$2') . '</a><!-- e -->'";
				}
			}
			return preg_replace($magic_url_match, $magic_url_replace, $text);			

		}
	}
}


/**
 * Overridden from WordPress' registration.php
 * We don't actually need to modify this -- but because we need our own versions of
 * wp_insert_user, and wp_update_user, and need to kill validate_username, we have to.
 * See WordPress documentation for usage
 * @todo registration.php overriding is causing many problems with plugins, as they try to require() it too.
 */ 
function username_exists( $username ) {
	global $wp_version; //wpu added
	if ( ((float) $wp_version) < 2.5 ) { // only needed for older WPs
		global $wpdb;
		$username = sanitize_user( $username );
	} 
	if ( $user = get_userdatabylogin($username) ) {
		return $user->ID;
	} else {
		return null;
	}
}

/**
 * Overridden from WordPress' registration.php
 * We don't actually need to modify this -- but because we need our own versions of
 * wp_insert_user, and wp_update_user, and need to kill validate_username, we have to.
 * See WordPress documentation for usage
 * @todo registration.php overriding is causing many problems with plugins, as they try to require() it too.
 */ 
function email_exists( $email ) {
	global $wp_version;
	if ( ((float) $wp_version) < 2.5 ) { // only needed for older WPs
		global $wpdb;
		$email = addslashes( $email );
		return $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE user_email = '$email'");
	} else { // NEW version
		if ( $user = get_user_by_email($email) )
			return $user->ID;

		return false;	
	}
}

/**
 * Overridden from WordPress' registration.php
 * This collides with phpBB's same-named function, so we rename it
 * We currently don't arbitrate between the two functions -- we just call wp_validate_username manually
 * and everything else gets sent through phpBB
 * @todo redirect to this when necessary
 * @todo an alpha plugin version is in wpu-plugin.php, but this whole setup requires more thought and testing
 */
function wp_validate_username( $username ) {
	$sanitized = sanitize_user( $username, true );
	$valid = ( $sanitized == $username );
	return apply_filters( 'validate_username', $valid, $username );
}
/**
 * Overridden from WordPress' registration.php to make password hashes compatible
 * partially filtered in wpu-plugin.php
 */
function wp_insert_user($userdata) {
	global $wpdb, $wp_version;  //added wp_version;

	if ((float)$wp_version >= 2.3) { //minor branch
		extract($userdata, EXTR_SKIP);
	} else {
		extract($userdata);
	}

	// Are we updating or creating?
	if ( !empty($ID) ) {
		$ID = (int) $ID;
		$update = true;
		$old_user_data = get_userdata($ID); //new in v2.5-2.7
	} else {
		$update = false;
		// Password is not hashed when creating new user.
		//$user_pass = wp_hash_password($user_pass); [WP-UNITED CHANGED]
	}

	$user_login = sanitize_user($user_login, true);
	$user_login = apply_filters('pre_user_login', $user_login);
	
	if ( empty($user_nicename) )
		$user_nicename = sanitize_title( $user_login ); 
	$user_nicename = apply_filters('pre_user_nicename', $user_nicename);

	if ( empty($user_url) )
		$user_url = '';
	$user_url = apply_filters('pre_user_url', $user_url);

	if ( empty($user_email) )
		$user_email = '';
	$user_email = apply_filters('pre_user_email', $user_email);

	if ( empty($display_name) )
		$display_name = $user_login;
	$display_name = apply_filters('pre_user_display_name', $display_name);

	if ( empty($nickname) )
		$nickname = $user_login;
	$nickname = apply_filters('pre_user_nickname', $nickname);

	if ( empty($first_name) )
		$first_name = '';
	$first_name = apply_filters('pre_user_first_name', $first_name);

	if ( empty($last_name) )
		$last_name = '';
	$last_name = apply_filters('pre_user_last_name', $last_name);

	if ( empty($description) )
		$description = '';
	$description = apply_filters('pre_user_description', $description);

	if ( empty($rich_editing) )
		$rich_editing = 'true';
		
	if ((float)$wp_version >= 2.5) { //new additions
		if ( empty($comment_shortcuts) )
			$comment_shortcuts = 'false';

		if ( empty($admin_color) )
			$admin_color = 'fresh';
		$admin_color = preg_replace('|[^a-z0-9 _.\-@]|i', '', $admin_color);

		if ( empty($use_ssl) )
			$use_ssl = 0;

		if ( empty($jabber) )
			$jabber = '';

		if ( empty($aim) )
			$aim = '';

		if ( empty($yim) )
			$yim = '';	
	
	}

	
	if ( empty($user_registered) )
		$user_registered = gmdate('Y-m-d H:i:s');
		
	if ((float)$wp_version >= 2.8) { // New in WP 2.8!
		$user_nicename_check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1" , $user_nicename, $user_login));

		if ($user_nicename_check) {
			$suffix = 2;
			while ($user_nicename_check) {
				$alt_user_nicename = $user_nicename . "-$suffix";
				$user_nicename_check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1" , $alt_user_nicename, $user_login));
				$suffix++;
			}
			$user_nicename = $alt_user_nicename;
		}
	}
		
		

	if ((float)$wp_version >= 2.5) { //new additions

		$data = compact( 'user_pass', 'user_email', 'user_url', 'user_nicename', 'display_name', 'user_registered' );
		$data = stripslashes_deep( $data );

		if ( $update ) {
			$wpdb->update( $wpdb->users, $data, compact( 'ID' ) );
			$user_id = (int) $ID;
		} else {
			$wpdb->insert( $wpdb->users, $data + compact( 'user_login' ) );
			$user_id = (int) $wpdb->insert_id;
		}
	} else {

		if ( $update ) {
			$query = "UPDATE $wpdb->users SET user_pass='$user_pass', user_email='$user_email', user_url='$user_url', user_nicename = '$user_nicename', display_name = '$display_name' WHERE ID = '$ID'";
			$query = apply_filters('update_user_query', $query);
			$wpdb->query( $query );
			$user_id = (int) $ID;  //int added in wp v2.2
		} else {
			$query = "INSERT INTO $wpdb->users
			(user_login, user_pass, user_email, user_url, user_registered, user_nicename, display_name)
			VALUES
			('$user_login', '$user_pass', '$user_email', '$user_url', '$user_registered', '$user_nicename', '$display_name')";
			$query = apply_filters('create_user_query', $query);
			$wpdb->query( $query );
			$user_id = (int) $wpdb->insert_id; //int added in wp v2.2
		}
	}

	update_usermeta( $user_id, 'first_name', $first_name);
	update_usermeta( $user_id, 'last_name', $last_name);
	update_usermeta( $user_id, 'nickname', $nickname );
	update_usermeta( $user_id, 'description', $description );
	update_usermeta( $user_id, 'jabber', $jabber );
	update_usermeta( $user_id, 'aim', $aim );
	update_usermeta( $user_id, 'yim', $yim );
	update_usermeta( $user_id, 'rich_editing', $rich_editing);
	
	if ((float)$wp_version >= 2.5) { //new additions
		update_usermeta( $user_id, 'comment_shortcuts', $comment_shortcuts);
		update_usermeta( $user_id, 'admin_color', $admin_color);
		update_usermeta( $user_id, 'use_ssl', $use_ssl);	
	}

	if ( $update && isset($role) ) {
		$user = new WP_User($user_id);
		$user->set_role($role);
	}

	if ( !$update ) {
		$user = new WP_User($user_id);
		$user->set_role(get_option('default_role'));
	}

	wp_cache_delete($user_id, 'users');
	wp_cache_delete($user_login, 'userlogins');

	if ( $update )
		do_action('profile_update', $user_id);
	else
		do_action('user_register', $user_id);

	return $user_id;
}

/**
 * Overridden from WordPress' registration.php to make password hashes compatible
 * 
 */
function wp_update_user($userdata) {
	global $wpdb, $wp_version;

	$ID = (int) $userdata['ID'];

	// First, get all of the original fields
	$user = get_userdata($ID);

	// Escape data pulled from DB.
	$user = add_magic_quotes(get_object_vars($user));

	// If password is changing, hash it now.
	if ( ! empty($userdata['user_pass']) ) {
		$plaintext_pass = $userdata['user_pass'];
		// NOTE BY JOHN WELLS -- IN WINTERMUTE VERSION BELOW IS UNCOMMENTED, BUT PHPBB WILL NOT BE PROVIDING
		// A PLAINTEXT PASSWORD HERE, SO THIS WILL NOT WORK || TODO: 20: TO CHECK HOW TO RECONCILE PASSWORDS, IF AT ALL
		//$userdata['user_pass'] = wp_hash_password($userdata['user_pass']); //[WP-UNITED CHANGED]
	}
	
	// Merge old and new fields with new fields overwriting old ones.
	$userdata = array_merge($user, $userdata);
	$user_id = wp_insert_user($userdata);

	
	// Update the cookies if the password changed.
	$current_user = wp_get_current_user();
	if ( $current_user->id == $ID ) {
		if ( isset($plaintext_pass) ) {
			if ((float)$wp_version >= 2.5) { //new additions
				wp_clear_auth_cookie();
				wp_set_auth_cookie($ID);			
			} else { //old WP
				wp_clearcookie();
				wp_setcookie($userdata['user_login'], $userdata['user_pass'], true, '', '', false);  // wp_setcookie($userdata['user_login'], $plaintext_pass); [WP-UNITED CHANGED]
			}
		}
	}
	return $user_id;
}

/**
 * Overridden from WordPress' registration.php, no change
 * 
 */
function wp_create_user($username, $password, $email = '') {
	global $wpdb;

	$user_login = $wpdb->escape($username);
	$user_email = $wpdb->escape($email);
	$user_pass = $password;

	$userdata = compact('user_login', 'user_email', 'user_pass');
	return wp_insert_user($userdata);
}

/**
 * Overridden from WordPress' registration.php -- but moved to deprecated.php in later wordpress
 * So we only override in WP < 2.5
 * we need to use $this->wpVersion here as this check takes place before WP invocation, so WP-United sets the variable, not WP.
 * INSIDE the functions we use $wp_version instead, a global variable set by WP.
 */
if($this->wpVersion < 2.5 && !function_exists('create_user')) {
	function create_user($username, $password, $email) {
		return wp_create_user($username, $password, $email);
	}
}

/**
 * We need to override WordPress password hash checking, as the password we have to log into wordpress is already hashed.
 * prior to WP 2.5, we could double-hash and check that way, but no longer :-(
 * @todo Half of this is going to take place in wpu-plugin. Eventually we will move it all there.
 */
function wp_check_password($password, $hash, $user_id = '') {
	global $wp_hasher;
	
	// Here phpBB has already handled authentication, so the inbound password is hashed and we just need to check it against the database.
	// IMPORTANT -- This should not be defined anywhere other than integration-class.php, otherwise it allows an attacker
	// who has gained access to the DB to log into wordpress without having to crack passwords.
	if(defined('PASSWORD_ALREADY_HASHED') && PASSWORD_ALREADY_HASHED) {
		// We can convert hashes from phpBB-type to WordPress-type
		if(substr($password, 0, 3) == '$H$') {
			$password = substr_replace($password, '$P$', 0, 3);
			$check = ($password == $hash);
			return apply_filters('check_password', $check, $password, $hash, $user_id);
		}
	} else { 
		// This is not an incoming phpBB/WP-United request, so this file will not be called.
		// Handle the request in wpu-plugin.php, in a filter.
	}


	// If the hash is still md5...
	if ( strlen($hash) <= 32 ) {
		$check = ( $hash == md5($password) );
		if ( $check && $user_id ) {
			// Rehash using new hash.
			wp_set_password($password, $user_id);
			$hash = wp_hash_password($password);
		}

		return apply_filters('check_password', $check, $password, $hash, $user_id);
	}

	// If the stored hash is longer than an MD5, presume the
	// new style phpass portable hash.
	if ( empty($wp_hasher) ) {
		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		// By default, use the portable hash from phpass
		$wp_hasher = new PasswordHash(8, TRUE);
	}

	$check = $wp_hasher->CheckPassword($password, $hash);


	return apply_filters('check_password', $check, $password, $hash, $user_id);
}


?>