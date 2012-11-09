<?php
/** 
*
* WP-United Hooks
*
* @package WP-United
* @version $Id: v0.9RC3 2012/11/01 John Wells (Jhong) Exp $
* @copyright (c) 2006-2012 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/


/**
 */
if ( !defined('IN_PHPBB') ) { 
	exit;
}

define('WPU_HOOK_ACTIVE', TRUE);

/**
 * Only activate this hook if WP-United is set up and working correctly, and if it is needed:
 */

// If the user has deleted the wp-united directory, do nothing
if(!file_exists($phpbb_root_path . 'wp-united/')) {
	return;
}

wpu_get_integration_settings();

// We don't need anything if this is a stylesheet call to css magic (style-fixer.php)
if(defined('WPU_STYLE_FIXER')) {
	return;
}

if(!isset($wpUnited) || !is_object($wpUnited) || !$wpUnited->is_enabled()) {
	return;
}


// constants have just been loaded
if (defined('WPU_DISABLE') && WPU_DISABLE) {  
	return;
}	

		


// If we don't need to run WP, we don't need to do anything else here...
if(!$wpUnited->should_run_wordpress()) {
	return;
}

wpu_timer_start();
wpu_set_buffering_init_level();

// register our hooks.
$phpbb_hook->register('phpbb_user_session_handler', 'wpu_init');
$phpbb_hook->register(array('template', 'display'), 'wpu_execute', 'last');
$phpbb_hook->register('exit_handler', 'wpu_continue');


/**
 * INVOKE THE WP ENVIRONMENT NOW. This ***must*** be run in the global scope, for compatibility.
*/

require_once($wpUnited->pluginPath . 'wordpress-runner.php'); 

/**
 * Since WordPress uses PHP timezone handling in PHP 5.3+, we need to do in phpBB too to suppress warnings
 * @TODO: In future phpBB releases (> 3.0.11), see if the devs hav added this to phpBB, and remove if so
 */
if ( function_exists('date_default_timezone_set') && !defined('WPU_BLOG_PAGE') && !defined('WPU_PHPBB_IS_EMBEDDED') ) {
	date_default_timezone_set('UTC');
}	



/**
 * Initialise WP-United variables and template strings
 */
function wpu_init(&$hook) { 
	global $wpUnited, $phpbb_root_path, $template, $user, $config, $phpbbForum, $wpuCache;
	
	if($wpUnited->should_do_action('logout')) {
		$phpbbForum->background();
		wp_logout();
		$phpbbForum->foreground();
	}
	
		
		
	// Add lang strings if this isn't blog.php
	if( !defined('WPU_BLOG_PAGE')  && !defined('WPU_PHPBB_IS_EMBEDDED') ) {
		$user->add_lang('mods/wp-united');
	}	
	
	// Since we will buffer the page, we need to start doing so after the gzip handler is set
	// to prevent phpBB from setting the handler twice, we unset the option.
	if(!defined('ADMIN_START') ) { //&& (defined('WPU_BLOG_PAGE') || ($wpUnited->get_setting('showHdrFtr') == 'REV'))
		if ($config['gzip_compress']) {
			if (@extension_loaded('zlib') && !headers_sent()) {
				ob_start('ob_gzhandler');
				$config['wpu_gzip_compress'] = 1;
				$config['gzip_compress'] = 0;
			}
		}	
	}	

	/** 
	 * Do a template integration?
	 * @TODO: Clean up, remove defines
	 */
	if (($wpUnited->get_setting('showHdrFtr') == 'REV') && !defined('WPU_BLOG_PAGE')) { 
		ob_start();
	}
	if (($wpUnited->get_setting('showHdrFtr') == 'FWD') && defined('WPU_BLOG_PAGE') ) {
		define('WPU_FWD_INTEGRATION', true); // TODO: clean this somehow
		

		//Initialise the cache -- although we won't be using it, we may need some functionality
		require_once($wpUnited->pluginPath . 'cache.php');
		$wpuCache = WPU_Cache::getInstance();
		

		ob_start();
		register_shutdown_function('wpu_wp_shutdown');
	}
}

function wpu_wp_shutdown() { 
	global $innerContent, $wpContentVar, $phpbb_root_path, $phpbbForum, $wpuCache, $wpUnited;
	if (defined('WPU_FWD_INTEGRATION') ) {
		
		$innerContent = ob_get_contents();
		ob_end_clean(); 
		$phpbbForum->foreground();
		
		$wpContentVar = 'innerContent';
		include ($wpUnited->pluginPath . 'integrator.php');
	}
}

/**
 * Capture the outputted page, and prevent phpBB from exiting
 * @todo: use better check to ensure hook is called on template->display and just drop for everything else
 */
function wpu_execute(&$hook, $handle) { 
	global $wpUnited, $wpuBuffered, $wpuRunning, $template, $innerContent, $phpbb_root_path, $phpEx, $db, $cache;
	
	// We only want this action to fire once, and only on a real $template->display('body') event
	if ( (!$wpuRunning)  && (isset($template->filename[$handle])) ) {
	
		// perform profile sync if required
		if($wpUnited->should_do_action('profile')) {
			global $phpbbForum, $user;
			
			$idToFetch = ($wpUnited->actions_for_another()) ? $wpUnited->actions_for_another() : $user->data['user_id'];
			
			// have to reload data from scratch otherwise cached $user is used
			$newUserData = $phpbbForum->fetch_userdata_for($idToFetch);

			$phpbbForum->background(); 
			$wpUserData = get_userdata($newUserData['user_wpuint_id']);
			wpu_sync_profiles($wpUserData, $newUserData, 'phpbb-update');
			$phpbbForum->foreground();
		}
	

		if($handle != 'body') {
			return;
		}
		

		/**
		 * An additional check to ensure we don't act on a $template->assign_display('body') event --
		 * if a mod is doing weird things with $template instead of creating their own $template object
		 */
		if(defined('WPU_FWD_INTEGRATION')) {
			if($wpuBuffered = wpu_am_i_buffered()) {
				return;
			}
		}
		
		$wpuRunning = true;
		//$hook->remove_hook(array('template', 'display'));
		if(defined('SHOW_BLOG_LINK') && SHOW_BLOG_LINK) {
			$template->assign_vars(array(
				'U_BLOG'	 =>	append_sid($wpUnited->wpHomeUrl, false, false, $GLOBALS['user']->session_id),
				'S_BLOG'	=>	TRUE,
			)); 
		}


		if($wpUnited->should_do_action('template-p-in-w')) { 
			$template->display($handle);
			$innerContent = ob_get_contents();
			ob_end_clean(); 
			if(in_array($template->filename[$handle], (array)$GLOBALS['WPU_NOT_INTEGRATED_TPLS'])) {
				//Don't reverse-integrate pages we know don't want header/footers
				echo $innerContent;
			} else { 
				//insert phpBB into a wordpress page
				include ($wpUnited->pluginPath .'integrator.php'); 
			}
			
		
		} elseif (defined('PHPBB_EXIT_DISABLED')) {
			/**
			 * page_footer was called, but we don't want to close the DB connection & cache yet
			 */
			$template->display($handle);
			$GLOBALS['bckDB'] = $db;
			$GLOBALS['bckCache'] = $cache;
			$db = ''; $cache = '';
			
			return "";
		} 
	}
}

/**
 * This is the last line of defence against mods which might be calling $template->assign_display('body')
 *
 * We err on the side of caution -- only intervening if we are undoubtedly buffered. As a result,
 * This may on occasion return false negative
 * 
 */
function wpu_am_i_buffered() {
	global $config, $wpuBufferLevel;
	// + 1 to account for reverse integration buffer
	
	$level = (isset($config['wpu_gzip_compress']) && $config['wpu_gzip_compress'] && @extension_loaded('zlib') && !headers_sent()) + 1;
	if(ob_get_level() > ($wpuBufferLevel + $level)) {
		return true;
	}
	return false;
}

/**
 * Find base level of buffering -- e.g. if php.ini buffering is taking place
 * this ensures wpu_am_i_buffered detection is correct
 */
function wpu_set_buffering_init_level() {
	global $wpuBufferLevel;
	$wpuBufferLevel = ob_get_level();
}

/**
 * Prevent phpBB from exiting
 */
function wpu_continue(&$hook) {
	global $wpuRunning, $wpuBuffered, $wpUnited;
	
	if (defined('PHPBB_EXIT_DISABLED') && !defined('WPU_FINISHED')) {
		return '';
	} else if ( $wpuBuffered && (!$wpuRunning) && $wpUnited->should_do_action('template-p-in-w') ) {
		/** if someone else was buffering the page and are now asking to exit,
		 * wpu_execute won't have run yet
		 */
		$buff = false;
		// flush the buffer until we get to our reverse integrated layer
		while(wpu_am_i_buffered()) {
			ob_end_flush();
			$buff = true;
		}
		if($buff) {
			wpu_execute($hook, 'body');
		}
	}
}




/**
 * Get configuration setings from database
 * Configurations are stored as a serialized WP-United object that was initialised and send by WordPress, 
 * hashed together with the path back to WordPress (so we can reload it).
 * Sets initial values to sensible deafaults if they haven't been set yet.
 */
function wpu_get_integration_settings() {
	global $config, $db;

	$wpuString = '';
	$key = 1;
	while(isset( $config["wpu_settings_{$key}"])) {
		$wpuString .= $config["wpu_settings_{$key}"];
		$key++;
	}

	// convert config value to a serialised string
	if(empty($wpuString)) {
		return false;
	}
	$wpuString =  gzuncompress(base64_decode($wpuString));
	
	// if $wpUnited doesn't already exist, create it by unserialising the stored object
	global $wpUnited;
	if(!is_object($wpUnited)) { 
		$retrieved = unserialize($wpuString);
		if( is_array($retrieved) && (sizeof($retrieved) == 2) ) { 
			list($classLoc, $classDetails) = $retrieved;
			if(file_exists($classLoc)) { 
				require_once($classLoc . 'basics.php');
				// Convert it from a saved WP_United_Plugin class to our base WP_United_Basics class. 
				// Yes this is brittle/ugly but cleanest alternative for now and still beats duplicating
				// TODO: Make WP_United_Plugin expose no additional public interfaces.
				$classDetails = str_replace('WP_United_Plugin', 'WP_United_Basics', $classDetails);
				$wpUnited = unserialize($classDetails);
			}
		}
	}
	if(!is_object($wpUnited)) {
		return false;
	}
		
	/**
	 * Handle style keys for CSS Magic
	 * We load them here so that we can auto-remove them if CSS Magic is disabled
	 */
	$key = 1;
	if($wpUnited->get_setting('cssMagic')) {
		$fullKey = '';
		while(isset( $config["wpu_style_keys_{$key}"])) {
			$fullKey .= $config["wpu_style_keys_{$key}"];
			$key++;
		}
		if(!empty($fullKey)) {
			$wpUnited->init_style_keys(unserialize(base64_decode($fullKey)));
		} else {
			$wpUnited->init_style_keys();
		}
	} else {
		// Clear out the config keys
		if(isset($config['wpu_style_keys_1'])) {
			$sql = 'DELETE FROM ' . CONFIG_TABLE . ' 
				WHERE config_name LIKE \'wpu_style_keys_%\'';
			$db->sql_query($sql);
		}
		$wpUnited->init_style_keys();
	}
	
}


/**
 * Clear integration settings
 * Completely removes all traces of WP-united settings
 */
function clear_integration_settings() {
	global $db, $cache;
	
	wpu_clear_main_settings();
	wpu_clear_style_keys();
	
	$cache->destroy('config');
}

/** 
 * removes main settings from database
 * @access private
 */
function wpu_clear_main_settings() {
	global $db;
	
	$sql = 'DELETE FROM ' . CONFIG_TABLE . '
			WHERE config_name LIKE \'wpu_settings_%\'';
	$db->sql_query($sql);
}

/** 
 * removes main settings from database
 * @access private
 */
function wpu_clear_style_keys() {
	global $db, $config, $wpUnited;
	
	if(isset($config['wpu_style_keys_1'])) {
		$sql = 'DELETE FROM ' . CONFIG_TABLE . ' 
			WHERE config_name LIKE \'wpu_style_keys_%\'';
		$db->sql_query($sql);
	}
	$wpUnited->init_style_keys();
}

/**
 * Write config settings to the database
 * Writes any configuration settings that are passed to the integration settings table.
 * We want changes to take place as a single transaction to avoid collisions, so we 
 * access DB directly rather than using set_config
*/
function set_integration_settings($dataIn) {
		global $cache, $db;
		
		
		
		$currPtr=1;
		$chunkStart = 0;
		$sql = array();
		wpu_clear_main_settings();
		while($chunkStart < strlen($dataIn)) {
			$sql[] = array(
				'config_name' 	=> 	"wpu_settings_{$currPtr}",
				'config_value' 	=>	substr($dataIn, $chunkStart, 255)
			);
			$chunkStart = $chunkStart + 255;
			$currPtr++;
		}
		
		$db->sql_multi_insert(CONFIG_TABLE, $sql);
		$cache->destroy('config');
}

		
/**
 * Start the script timer
*/
function wpu_timer_start() {
	global $wpuScriptTime;
	$wpuScriptTime = explode(' ', microtime());
	$wpuScriptTime = $wpuScriptTime[0] + $wpuScriptTime[1];
}
?>
