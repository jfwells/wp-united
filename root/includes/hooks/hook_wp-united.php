<?php

/** 
*
* WP-United Hooks
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

define('WPU_HOOK_ACTIVE', TRUE);

// If the user has deleted the wp-united directory, do nothing
if(file_exists($phpbb_root_path . 'wp-united/')) {
	
	$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 

	if(!isset($wpSettings['enabled'])) {
		return;
	}
	if($wpSettings['enabled'] != 'enabled') {
		return;
	}
	
	
	if(!defined('WPU_STYLE_FIXER')) {
		
		if(!defined('ADMIN_START') && (defined('WPU_BLOG_PAGE') || ($wpSettings['showHdrFtr'] == 'REV'))) {
			set_error_handler('wpu_msg_handler'); 
		}

		if(isset($wpSettings['wpPluginPath'])) {
			if(file_exists($wpSettings['wpPluginPath'])) {
				
				require_once($wpSettings['wpPluginPath'] . 'functions-general.' . $phpEx);

				
				wpu_set_buffering_init_level();

				if(!defined('ADMIN_START') && (!defined('WPU_PHPBB_IS_EMBEDDED')) ) {  
					if (!((defined('WPU_DISABLE')) && WPU_DISABLE)) {  
						$phpbb_hook->register('phpbb_user_session_handler', 'wpu_init');
						$phpbb_hook->register(array('template', 'display'), 'wpu_execute', 'last');
						$phpbb_hook->register('exit_handler', 'wpu_continue');

					
						/**
						 * New add for global scope 
						 */
						if (($wpSettings['showHdrFtr'] == 'REV') && !defined('WPU_BLOG_PAGE')) {
							define('WPU_REVERSE_INTEGRATION', true); 
							//ob_start(); // to capture errors
							require_once($wpSettings['wpPluginPath'] . 'wordpress-runner.' .$phpEx);
						}

					}
					
				} 
			}
		}

		/**
		 * Since WordPress suppresses timezone warnings in php 5.3 with the below, we do it in phpBB
		 * too, for wordpress users who might think it's an error in WP-United.
		 * @todo: In future phpBB releases (> 3.0.6), see if the devs hav added this to phpBB, and remove if so
		 */
		if ( function_exists('date_default_timezone_set') && !defined('WPU_BLOG_PAGE') && !defined('WPU_PHPBB_IS_EMBEDDED') ) {
			date_default_timezone_set('UTC');
		}
	}

}

/**
 * Initialise WP-United variables and template strings
 */
function wpu_init(&$hook) {
	global $wpSettings, $phpbb_root_path, $phpEx, $template, $user, $config;

		
	// Add lang strings if this isn't blog.php
	if( !defined('WPU_BLOG_PAGE')  && !defined('WPU_PHPBB_IS_EMBEDDED') ) {
		$user->add_lang('mods/wp-united');
	}	
	
	// Since we will buffer the page, we need to start doing so after the gzip handler is set
	// to prevent phpBB from setting the handler twice, we unset the option.
	if(!defined('ADMIN_START') ) { //&& (defined('WPU_BLOG_PAGE') || ($wpSettings['showHdrFtr'] == 'REV'))
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
	if (($wpSettings['showHdrFtr'] == 'REV') && !defined('WPU_BLOG_PAGE')) {
		ob_start();
	}
	if (($wpSettings['showHdrFtr'] == 'FWD') && defined('WPU_BLOG_PAGE') ) {
		define('WPU_FWD_INTEGRATION', true);
		ob_start();
		register_shutdown_function('wpu_wp_shutdown');
	}
}

function wpu_wp_shutdown() {
	global $wpSettings, $innerContent, $phpbb_root_path, $phpEx, $phpbbForum;
	if (defined('WPU_FWD_INTEGRATION') ) {
		$innerContent = ob_get_contents();
		ob_end_clean();  
		$phpbbForum->enter();
		include ($wpSettings['wpPluginPath'] . 'integrator.' . $phpEx);
	}
}

/**
 * Capture the outputted page, and prevent phpBB from exiting
 * @todo: use better check to ensure hook is called on template->display and just drop for everything else
 */
function wpu_execute(&$hook, $handle) { 
	global $wpuBuffered, $wpuRunning, $wpSettings, $template, $innerContent, $phpbb_root_path, $phpEx, $db, $cache;
	// We only want this action to fire once, and only on a real $template->display('body') event
	if ( (!$wpuRunning)  && (isset($template->filename[$handle])) ) {
		
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
				'U_BLOG'	 =>	append_sid($wpSettings['wpUri'], false, false, $GLOBALS['user']->session_id),
				'S_BLOG'	=>	TRUE,
			)); 
		}
		

		if (defined('WPU_REVERSE_INTEGRATION') ) {
			$template->display($handle);
			$innerContent = ob_get_contents();
			ob_end_clean(); 

			if(in_array($template->filename[$handle], (array)$GLOBALS['WPU_NOT_INTEGRATED_TPLS'])) {
				//Don't reverse-integrate pages we know don't want header/foote
				echo $innerContent;
			} else {
				//insert phpBB into a wordpress page
				include ($wpSettings['wpPluginPath'] .'integrator.' . $phpEx); 
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
	$level = ((int)($config['wpu_gzip_compress'] && @extension_loaded('zlib') && !headers_sent())) + 1;
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
	global $wpuRunning, $wpuBuffered;
	if (defined('PHPBB_EXIT_DISABLED') && !defined('WPU_FINISHED')) {
		return "";
	} else if ( $wpuBuffered && (!$wpuRunning) && defined('WPU_REVERSE_INTEGRATION') ) {
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
 * Gets the configuration settings from the db, and returns them in $wpSettings.
 * Sets initial values to sensible deafaults if they haven't been set yet.
 */
function get_integration_settings() {
	global $config, $db, $phpbb_root_path, $phpEx;
	
	$defaults = array(
		'status' => 0,
		'wpUri' => '' ,
		'wpPath' => '', 
		'integrateLogin' => 0, 
		'showHdrFtr' => 'NONE',
		'wpSimpleHdr' => 1,
		'dtdSwitch' => 0,
		//'installLevel' => 0,
		'usersOwnBlogs' => 0,
		//'buttonsProfile' => 0,
		//'buttonsPost' => 0,
		//'allowStyleSwitch' => 0,
		//'useBlogHome' => 0,
		//'blogListHead' => $user->lang['WPWiz_BlogListHead_Default'],
		//'blogIntro' => $user->lang['WPWiz_blogIntro_Default'],
		'blogsPerPage' => 6,
		'blUseCSS' => 1,
		'phpbbCensor' => 1,
		//'wpuVersion' => $user->lang['WPU_Not_Installed'],
		'wpPageName' => 'page.php',
		'phpbbPadding' =>  '6-12-6-12',
		'mustLogin' => 0,
		//'upgradeRun' => 0,
		'xposting' => 0,
		'phpbbSmilies' => 0,
		'xpostautolink' => 0,
		'xpostforce' => -1,
		'xposttype' => 'EXCERPT',	
		'cssMagic' => 1,
		'templateVoodoo' => 1,
		//'pluginFixes' => 0,
		'useForumPage' => 1
	);
	
	$wpSettings = array();

	$fullKey = '';
	$key = 1;
	while(isset( $config["wpu_settings_{$key}"])) {
		$fullKey .= $config["wpu_settings_{$key}"];
		$key++;
	}
	if(!empty($fullKey)) {
		$wpSettings =  (array)unserialize(base64_decode($fullKey));
	} else {
		$wpSettings= array();
	}

	$wpSettings = array_merge($defaults, $wpSettings);
	
		/**
	 * Handle style keys for CSS Magic
	 * We load them here so that we can auto-remove them if CSS Magic is disabled
	 */
	if(sizeof($wpSettings)) {
		$key = 1;
		if(!empty($wpSettings['cssMagic'])) {
			$fullKey = '';
			while(isset( $config["wpu_style_keys_{$key}"])) {
				$fullKey .= $config["wpu_style_keys_{$key}"];
				$key++;
			}
			if(!empty($fullKey)) {
				$wpSettings['styleKeys'] = unserialize(base64_decode($fullKey));
			} else {
				$wpSettings['styleKeys'] = array();
			}
		} else {
			// Clear out the config keys
			if(isset($config['wpu_style_keys_1'])) {
				$sql = 'DELETE FROM ' . CONFIG_TABLE . ' 
					WHERE config_name LIKE \'wpu_style_keys_%\'';
				$db->sql_query($sql);
			}
			$wpSettings['styleKeys'] = array();
		}
	}
	
	
	/**
	 * Load the version number
	 */
	if(isset($wpSettings['wpPluginPath'])) {
		if(file_exists($wpSettings['wpPluginPath'])) {
			require_once ($wpSettings['wpPluginPath'] . 'version.' . $phpEx);
			require_once ($wpSettings['wpPluginPath'] . 'options.' . $phpEx);
		}
	}
	
	return $wpSettings;	
	
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
	global $db, $config, $wpSettings;
	
	if(isset($config['wpu_style_keys_1'])) {
		$sql = 'DELETE FROM ' . CONFIG_TABLE . ' 
			WHERE config_name LIKE \'wpu_style_keys_%\'';
		$db->sql_query($sql);
	}
	$wpSettings['styleKeys'] = array();
}

/**
 * Write config settings to the database
 * Writes any configuration settings that are passed to the integration settings table.
 * We want changes to take place as a single transaction to avoid collisions, so we 
 * access DB directly rather than using set_config
*/
function set_integration_settings($dataIn) {
		global $cache, $db;
		$fullSettings = (base64_encode(serialize($dataIn)));
		$currPtr=1;
		$chunkStart = 0;
		$sql = array();
		wpu_clear_main_settings();
		while($chunkStart < strlen($fullSettings)) {
			$sql[] = array(
				'config_name' 	=> 	"wpu_settings_{$currPtr}",
				'config_value' 	=>	substr($fullSettings, $chunkStart, 255)
			);
			$chunkStart = $chunkStart + 255;
			$currPtr++;
		}
		
		$db->sql_multi_insert(CONFIG_TABLE, $sql);
		$cache->destroy('config');
		
}


?>