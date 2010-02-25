<?php

/** 
*
* WP-United -- Integration class (the bit that talks to WordPress!)
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/


if ( !defined('IN_PHPBB') ) exit;

/**
 * This class provides access to WordPress
 * It should be accessed as a singleton, and made global
 * It calculates and caches code pathways to invoke WordPress, which can then be evaluated in the global scope
 * 
 * It also handles modifying WordPress as necessary in order to integrate it.
 * @package WP-United Core Integration
 */
Class WPU_Integration {
 
	
	// The instructions we build in order to execute WordPress
	var $wpRun;
	
	/**
	 * This is a list of  vars phpBB also uses. We'll unset them when the class is instantiated, and restore them later. 
	 */
	var $varsToUnsetAndRestore = array('table_prefix', 'userdata', 'search', 'error', 'author');
	
	/**
	 * More vars that phpBB or MODS could use, that MUST be unset before WP runs
	 */
	var $varsToUnset = array('m', 'p', 'posts', 'w', 'cat', 'withcomments', 'withoutcomments', 's', 'search',
		'exact', 'sentence', 'debug', 'calendar', 'page', 'paged', 'more', 'tb', 'pb', 
		'author', 'order', 'orderby', 'year', 'monthnum', 'day', 'hour', 'minute', 'second',
		'name', 'category_name', 'feed', 'author_name', 'static', 'pagename', 'page_id', 'error',
		'comments_popup', 'attachment', 'attachment_id', 'subpost', 'subpost_id', 'preview', 'robots', 'entry');
		
	
	/** 
	 * A list of vars that we *know* WordPress will want in the global scope. 
	 * These are ONLY needed on pages where WP is called from within a function -- which is only on message_die pages. On such pages, most of these won't be needed anyway
	 */
	var $globalVarNames = array(
		// basic
		'wpdb', 
		'wp_db_version', 
		'wp_did_header', 
		'userdata', 
		'user_ID', 
		'current_user',
		'error',
		'errors',
		'post',
		'posts',
		'post_cache',
		'table_prefix', 
		'IN_WORDPRESS',
		'wp_version',
		'wp_taxonomies',
		'wp_object_cache',
		'options',
		'entry',
		'id',
		'withcomments',
		'wp_embed',
		
		// widgets
		'registered_sidebars', 
		'registered_widgets', 
		'registered_widget_controls', 
		'registered_widget_styles', 
		'register_widget_defaults', 
		'wp_registered_sidebars', 
		'wp_registered_widgets', 
		'wp_registered_widget_controls', 
		'wp_registered_widget_updates',
		'_wp_deprecated_widgets_callbacks',
		
		// comments
		'comment', 
		'comments', 
		'comment_type', 
		// k2
		'allowedtags', 
		'allowedposttags', 
		'k2sbm_k2_path', 
		'k2sbm_theme_path',
		'wp_version',
  		'wp_taxonomies', 
  		// inove
  		'inove_nosidebar',
  		// plugins
  		// awpcp
  		/*'awpcp_db_version',
  		'isclassifiedpage',
  		'hasregionsmodule',
  		'hascaticonsmodule',
  		'message',
  		'user_identity',
  		'imagesurl',
  		'haspoweredbyremovalmodule',
  		'clearform',
  		'hascaticonsmodule',*/

		// you could add your own here
	);
	
	/**
	 * these are set as references to objects in the global scope
	 */
	var $globalRefs = array( 
		'wp', 
		'wp_object_cache',
		'wp_rewrite', 
		'wp_the_query', 
		'wp_query',
		'wp_locale',
		'wp_widget_factory'
	);
	
	// We'll put phpBB's current variable state "on ice" in here.
	var $sleepingVars = array();
	var $varsToSave;
	
	var $wpu_settings;
	var $phpbb_root;
	var $phpEx;
	var $phpbb_usr_data;
	var $phpbb_db_name;
	
	var $wpVersion;
	
	var $wpu_ver;
	
	// prevents the main WordPress script from being included more than once
	var $wpLoaded;
	

	// Compatibility mode?
	var $wpu_compat;
	
	var $debugBuffer;
	var $debugBufferFull;
	
	/*
	 * This class MUST be called as a singleton using this method
	 * @param array $varsToSave An array of variable names that should be saved in their current state
	 */
	function getInstance ($varsToSave = FALSE ) {
		static $instance;
		if (!isset($instance)) {
			$instance = new WPU_Integration($varsToSave);
        } 
        	return $instance;
    	}

	/**
	 * Class constructor.
	 * Takes a snapshot of phpBB variables at this point.
	 * When we exit WordPress, these can be restored.
	 */
	function WPU_Integration($varsToSave) {

		//these are constants that ain't gonna change - we're going to need them in our class
		$this->wpRun = '';
		$this->phpbb_usr_data = $GLOBALS['userdata']; 
		$this->phpEx = $GLOBALS['phpEx'];
		$this->phpbb_db_name = $GLOBALS['dbname'];
		$this->phpbb_root = $GLOBALS['phpbb_root_path'];
		$this->wpu_settings = $GLOBALS['wpSettings'];
		// store all vars set by phpBB, ready for retrieval after we exit WP
		/**
		 * @todo disable passing in of varsToSave -- may be better to 100% manage
		 */
		if ($varsToSave === FALSE ) {
			$varsToSave = $this->varsToUnsetAndRestore;
		}
		$this->varsToSave = $varsToSave;
		$this->wpLoaded = FALSE;
		$this->debugBuffer = '';
		$this->debugBufferFull = FALSE;
		$this->wpVersion = 0;
		
		$this->wpu_ver = $GLOBALS['wpuVersion'];
		
		// Load plugin fixer -- must be loaded regardless of settings, as core cache may contain plugin fixes
		require($this->phpbb_root . 'wp-united/plugin-fixer.' . $this->phpEx);
		
		// Several library functions are required, and might not have been included if this is called directly from a phpBB function
		// (e.g. during setup)
		require_once($this->phpbb_root . 'wp-united/functions-general.' . $this->phpEx);
	}
	
	/**
	 * Test connection to WordPress
	 */
	function can_connect_to_wp() {
		$test = str_replace('http://', '', $this->wpu_settings['wpPath']); // urls sometimes return true on php 5.. this makes sure they don't.
		if ( !file_exists( $test . 'wp-settings.php') ) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	/**
	 * Tests if the core cache is ready
	 */
	function core_cache_ready() {
		global $wpuCache;
	
		if ( !$wpuCache->core_cache_enabled() ){
			return false;
		}

		if ($wpuCache->use_core_cache($this->wpVersion, $this->wpu_compat)) {
			return true;
		}
		return false;
	} 
		

	
	/**
	 * Prepares code for execution by adding it to the internal code store
	 */
	function prepare($wpCode) {
		$this->wpRun .= $wpCode . "\n";
	}
	
	
	/**
	 * Returns the code to be executed.
	 * eval() can be called directly on the returned string
	 */	
	function exec() {
		
		$code = $this->wpRun; 
		$this->wpRun = '';
		return $code;
	}
	
	/**
	 * Loads up the WordPress install, to just before the point that the template would be shown
	 */
	function enter_wp_integration() {

		global $wpuCache;
		//Tell phpBB that we're in WordPress. This controls the branching of the duplicate functions get_userdata and make_clickable
		$GLOBALS['IN_WORDPRESS'] = 1;
		
		/**
		 * @since v0.8.0
		 */
		foreach($this->varsToUnsetAndRestore as $value) {
			$this->sleepingVars[$value] = $GLOBALS[$value];
		}
		
		
		// This is not strictly necessary, but it cleans up the vars we know are important to WP, or unsets variables we want to explicity get rid of.
		$toUnset=array_merge( $this->varsToUnsetAndRestore, $this->varsToUnset);
		foreach ( $toUnset as $varNames) {
			if(isset($GLOBALS[$varNames])) {
				unset($GLOBALS[$varNames]);
			}
		} 
		


		//Determine if WordPress will be running in the global scope -- in rare ocasions, such as in message_die, it won't be. 
		// This is fine - even preferable, but many third-party plugins are not prepared for this and we must hold their hands
		$this->wpu_compat = ( isset($GLOBALS['amIGlobal']) ) ? TRUE : FALSE;
		
		//Override site cookie path if set in options.php
		if ( (defined('WP_ROOT_COOKIE')) && (WP_ROOT_COOKIE) ) {
			define  ('SITECOOKIEPATH', '/');
			define  ('COOKIEPATH', '/');
			define  ('ADMIN_COOKIE_PATH', '/');
		}		


		if (!$this->wpu_compat) {
			$this->prepare('foreach ($wpUtdInt->globalVarNames as $globalVarName) global $$globalVarName;');
			$this->prepare('$beforeVars = array_keys(get_defined_vars());');
		}
		
		
		// do nothing if WP is already loaded
		if ($this->wpLoaded ) {
			$this->prepare('$wpUtdInt->switch_db(\'TO_W\');');
			return FALSE;
		}
		
		$this->wpLoaded = true;
		
		//Which version of WordPress are we about to load?
		global $wp_version;
		require($this->wpu_settings['wpPath'] . 'wp-includes/version.php');
		$this->wpVersion = $wp_version;
		
		// A few WordPress functions that we have changed - all in a separate file for easy updating.
		require($this->phpbb_root . 'wp-united/wp-functions.' . $this->phpEx);
		
		// Load widgets
		require($this->phpbb_root . 'wp-united/widgets.' . $this->phpEx);
		
		
		
		global $user;
		// Added realpath to account for symlinks -- 
		//otherwise it is inconsistent with __FILE__ in WP, which causes plugin inconsistencies.
		$realAbsPath = realpath($this->wpu_settings['wpPath']);
		$realAbsPath = ($realAbsPath[strlen($realAbsPath)-1] == "/" ) ? $realAbsPath : $realAbsPath . "/";
		define('ABSPATH',$realAbsPath);

		if (!$this->core_cache_ready()) { 
			
			// Now wp-config can be moved one level up, so we try that as well:
			$wpConfigLoc = (!file_exists($this->wpu_settings['wpPath'] . 'wp-config.php')) ? $this->wpu_settings['wpPath'] . '../wp-config.php' : $this->wpu_settings['wpPath'] . 'wp-config.php';

			$cConf = file_get_contents($wpConfigLoc);
			$cSet = file_get_contents($this->wpu_settings['wpPath'] . 'wp-settings.php');
			//Handle the make clickable conflict
			if (file_exists($this->wpu_settings['wpPath'] . 'wp-includes/formatting.php')) {
				$fName='formatting.php';  //WP >= 2.1
			} elseif (file_exists($this->wpu_settings['wpPath'] . 'wp-includes/functions-formatting.php')) {
				$fName='functions-formatting.php';  //WP< 2.1
			} else {
				trigger_error($user->lang['Function_Duplicate']);
			}
			$cFor = file_get_contents($this->wpu_settings['wpPath'] . "wp-includes/$fName");
			$cFor = '?'.'>'.trim(str_replace('function make_clickable', 'function wp_make_clickable', $cFor)).'<'.'?php ';
			$cSet = str_replace('require (ABSPATH . WPINC . ' . "'/$fName","$cFor // ",$cSet);	
			unset ($cFor);
			
			// Fix plugins
			if(!empty($this->wpu_settings['pluginFixes'])) {
				$strCompat = ($this->wpu_compat) ? "true" : "false";
				// MU Plugins
				$cSet = str_replace('if ( is_dir( WPMU_PLUGIN_DIR', 'global $wpuMuPluginFixer; $wpuMuPluginFixer = new WPU_WP_Plugins(WPMU_PLUGIN_DIR,  \'muplugins\', \'' . $this->wpu_ver . '\', \'' .  $this->wpVersion . '\', ' . $strCompat . ');if ( is_dir( WPMU_PLUGIN_DIR', $cSet);
				$cSet = str_replace('include_once( WPMU_PLUGIN_DIR . \'/\' . $plugin );', ' include_once($wpuMuPluginFixer->fix(WPMU_PLUGIN_DIR  . \'/\' . $plugin, true));', $cSet);
				
				//WP Plugins
				$cSet = preg_replace('/(get_option\(\s?\'active_plugins\'\s?\)\s?\)?;)/', '$1global $wpuPluginFixer; $wpuPluginFixer = new WPU_WP_Plugins(WP_PLUGIN_DIR, \'plugins\', \'' . $this->wpu_ver . '\', \'' .  $this->wpVersion . '\', ' . $strCompat . ');', $cSet);
				$cSet = str_replace('include_once(WP_PLUGIN_DIR . \'/\' . $plugin);', ' include_once($wpuPluginFixer->fix(WP_PLUGIN_DIR  . \'/\' . $plugin, true));', $cSet);
				
				// Theme functions
				$cSet = str_replace('// Load functions for active theme.', 'global $wpuStyleFixer; $wpuStyleFixer = new WPU_WP_Plugins(STYLESHEETPATH, \'styles\', \'' . $this->wpu_ver . '\', \'' .  $this->wpVersion . '\', ' . $strCompat . ');' . "\n" .  'global $wpuThemeFixer; $wpuThemeFixer = new WPU_WP_Plugins(TEMPLATEPATH, \'themes\', \'' . $this->wpu_ver . '\', \'' .  $this->wpVersion . '\', ' . $strCompat . ');', $cSet);
				$cSet = str_replace('include(STYLESHEETPATH . \'/functions.php\');', ' include_once($wpuStyleFixer->fix(STYLESHEETPATH . \'/functions.php\', true));', $cSet);
				$cSet = str_replace('include(TEMPLATEPATH . \'/functions.php\');', ' include_once($wpuThemeFixer->fix(TEMPLATEPATH . \'/functions.php\', true));', $cSet);
				
				// Predeclare globals for all 
				if (!$this->wpu_compat) {
					$cSet = str_replace('do_action(\'muplugins_loaded\');', 'eval($wpuMuPluginFixer->get_globalString()); unset($wpuMuPluginFixer); do_action(\'muplugins_loaded\');', $cSet);
					$cSet = str_replace('do_action(\'plugins_loaded\');', 'eval($wpuPluginFixer->get_globalString()); unset($wpuPluginFixer); do_action(\'plugins_loaded\');', $cSet);
					$cSet = str_replace('include_once($wpuThemeFixer->fix(TEMPLATEPATH . \'/functions.php\', true));', 'include_once($wpuThemeFixer->fix(TEMPLATEPATH . \'/functions.php\', true));' . "\n\n" . 'eval($wpuStyleFixer->get_globalString()); unset($wpuStyleFixer);' . "\n" . 'eval($wpuThemeFixer->get_globalString()); unset($wpuThemeFixer);', $cSet);
				}
			}
			
		
			//here we handle references to objects that need to be available in the global scope when we're not.
			if (!$this->wpu_compat) {
				foreach ( $this->globalRefs as $gloRef ) {
					$cSet = str_replace('$'. $gloRef . ' ', '$GLOBALS[\'' . $gloRef . '\'] ',$cSet);
					$cSet = str_replace('$'. $gloRef . '->', '$GLOBALS[\'' . $gloRef . '\']->',$cSet);
					$cSet = str_replace('=& $'. $gloRef . ';', '=& $GLOBALS[\'' . $gloRef . '\'];',$cSet);
				}
			}

			
			$cSet = '?'.'>'.trim($cSet).'<'.'?php ';
			$cConf = str_replace('require_once',$cSet . ' // ',$cConf);
			$this->prepare($content = '?'.'>'.trim($cConf).'<'.'?php ');
			unset ($cConf, $cSet);

			if ( $wpuCache->core_cache_enabled()) {
				$wpuCache->save_to_core_cache($content, $this->wpVersion, $this->wpu_compat);
			}
		} else {
			$this->prepare('require_once(\'' . $wpuCache->coreCacheLoc . '\');');
		}
		
		if(defined('WPU_BOARD_DISABLED')) {
			$this->prepare('wp_die(\'' . WPU_BOARD_DISABLED . '\', \'' . $GLOBALS['user']->lang['BOARD_DISABLED'] . '\');');
		}
		
		if ( defined('WPU_PERFORM_ACTIONS') ) {
			$this->prepare($GLOBALS['wpu_add_actions']);
		}
		
		if ( !$this->wpu_compat ) {
			$this->prepare('$newVars = array_diff(array_keys(get_defined_vars()), $beforeVars);');
			$this->prepare('foreach($newVars as $newVar) { if ($newVar != \'beforeVars\') $GLOBALS[$newVar] =& $$newVar;}');
		}
		
		
		return TRUE;

		
		
	}
	/**
	 * Prepares the code path wrapper for integrating logins
	 */
	function integrate_login() {
		if ( !empty($this->wpu_settings['integrateLogin']) ) {
			$this->prepare('$wpUtdInt->do_integrate_login();');
		}
	}
	
	/**
	 * If we want to integrate login, this gets WordPress up to speed with the current user and their details. 
	 * If the user doesn't exist, we create them.
	 */
	function do_integrate_login() {
		if ( empty($this->wpu_settings['integrateLogin']) ) {
			return false;
		}
		
		global $phpbbForum;
		
		if(!isset($phpbbForum)) {
			return false;
		}

		$loggedInUser = '';
		$newWpUser = '';
		$phpbbRawUser = $phpbbForum->get_username();
		$phpbbUserName = sanitize_user($phpbbRawUser, true);
		$integratedID = ( array_key_exists('user_wpuint_id', $phpbbForum->get_userdata()) ) ? $phpbbForum->get_userdata('user_wpuint_id') : 0 ; 
		$wpUserData = '';
		$newUserID = '';
		
		$user_level = $this->get_userlevel($phpbbForum->get_userdata());

		// Integrate only if logged in, and user level is mapped to integrate
		if (  (!$phpbbForum->user_logged_in()) || ($user_level === false) || !( ($phpbbForum->get_userdata('user_type') == USER_NORMAL) || ($phpbbForum->get_userdata('user_type') == USER_FOUNDER) ) )  {
			//log them out of WP, and look needlessly suspicious
			$this->lDebug('Not logged in'); 
			$this->do_wp_logout();
			wp_set_current_user(0, 0);
		} else {
			$wpUserdata = get_userdata($integratedID);
			if ( empty($wpUserdata) ) {
				$integratedID = 0;
				$this->update_int_ID($phpbbForum->get_userdata('user_id'), $integratedID);
			} else {
				$this->lDebug('Logged into phpBB, username=' .  $phpbbRawUser . '(' . $phpbbUserName . ',' . $integratedID . ')');
			}
			//SECTION TO CREATE INTEGRATED ACCOUNT
			if ( empty($integratedID) ) {
				// The user hasn't integrated an account yet. If they're logged in for some reason, assume it is suspicious and log them out
				$this->do_wp_logout();
				wp_set_current_user(0, 0);
				$loggedInUser = '';
				
				$this->lDebug('No WP Account Detected, Creating.');
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
				
				$this->lDebug('Found a suitable WP username: '.$signUpName);
				
				//Now we have a unique signup name.. let's create the user.
				$newWpUser->user_login = $signUpName;
				$newWpUser->user_pass = $phpbbForum->get_userdata('user_password');
				$newWpUser->user_email = $phpbbForum->get_userdata('user_email');
				$newUserID = wp_insert_user(get_object_vars($newWpUser));
				$integratedID = $newUserID;
				$this->update_int_ID($phpbbForum->get_userdata('user_id'), $integratedID);
				$this->lDebug('Created a user with ID = ' . $integratedID);
				$newUserData = get_userdata($newUserID);

				//Set usermeta options and check details consistency
				$wpUpdateData =	$this->check_details_consistency($newUserData, $phpbbForum->get_userdata(), true);
				if ( $wpUpdateData ) {
					wp_update_user($wpUpdateData);
				}	
			}
										
			$loggedInUser = wp_get_current_user();
			
			
			// Check that they're not already logged into the wrong account (weird stuff happens)
			if ($loggedInUser->ID !== $integratedID && ($loggedInUser->ID)) {
				$this->lDebug('You are logged into the wrong account! (WP ID = ' . $loggedInUser->ID . ', integrated ID = ' . $integratedID . '). Logging out!');
				$this->do_wp_logout();
				wp_set_current_user(0, 0);
				$loggedInUser = wp_get_current_user();
				
			}
			
			
			//SECTION TO LOG USER IN
			if ( empty($loggedInUser->ID) ) {
				global $error;
				//user isn't logged in
				$wpUser = get_userdata($integratedID);
				$wpUserName = $wpUser->user_login;
				$this->lDebug('WP account detected, logging into account (ID=' . $integratedID . ',Username=' . $wpUserName . ')');
				//see if user can log into WP (need to double-hash password)  
				// This authentication is really unneccessary at this point.... but we need them to have a strong password in a WP cookie for Admin panel access
				

				if($this->wpSignIn($wpUserName,  $phpbbForum->get_userdata('user_password'))) {					
					$loggedInUser = wp_set_current_user($wpUser->ID);
					$this->lDebug('Logged in successfully. Cookie set. Current user=' . $GLOBALS['current_user']->ID);
				} else {
					$this->lDebug('Could not authenticate. (' . $error .') Synchronising password.');
					// they couldn't log in... so let's just change their password
					$wpUpdateData =	$this->check_details_consistency($wpUser,  $phpbbForum->get_userdata()); 
					if ( $wpUpdateData ) {
						wp_update_user($wpUpdateData);
					}
					//It must work now....
					$wpUser = get_userdata($integratedID);
					$wpUserName = $wpUser->user_login;

					if($this->wpSignIn($wpUserName,  $phpbbForum->get_userdata['user_password'])) {
						$loggedInUser = wp_set_current_user($wpUser->ID);
						$this->lDebug('Logged in successfully. Cookie set. Current user=' . $GLOBALS['current_user']->ID);
					} else {
						$this->exit_wp_integration();
						$this->lDebug('Failed, aborting (' . $error .')', 1);
						trigger_error('WordPress Integration Error: WP-United has encountered an unknown integration error. We tried twice to log you in and it didn\'t work. Sorry! Please inform an administrator of this message');
					}
				}
			}
							
			if ( !empty($loggedInUser->ID) ) {
				$this->lDebug('Checking Profile Consistency');
				$userdata = $this->check_userlevels($loggedInUser->ID, $user_level);
				$userdata = get_userdata($userdata->ID);
				wp_set_current_user($userdata->ID);
				$wpUpdateData =	$this->check_details_consistency($userdata, $phpbbForum->get_userdata());					
				if ( $wpUpdateData ) {
					$this->lDebug('Synchronising Profiles');
					$loggedInID = wp_update_user($wpUpdateData);
					$loggedInUser = wp_set_current_user($loggedInID);
					$loggedInUser = get_userdata($loggedInUser->ID);
				}
			} else {
				//The login integration has failed. Log them out of WP just in case, and raise a stink.
				$this->do_wp_logout();
				$this->exit_wp_integration();
				$this->lDebug('Failed, aborting2', 1);
				trigger_error('Integration Error with your account! Please contact an administrator.');
			}
			
			if ( !($loggedInUser->ID == $integratedID) ) {
				//ID mismatch. something is heavily borked.
				trigger_error('WordPress Integration Error: Integration Mismatch Error with your account! Please contact an administrator.');
			}
			
		}
		if (!$this->debugBufferFull) $this->lDebug('',1);
		
	} //end of the integration	
	

	/**
	 * Log users into WordPresss -- It's a private function, designed to be called from
	 * do_integrate_login(). It handles the various methods of logging into WP, maintaining backwards compatibility
	 */
	function wpSignIn($wpUsr, $pass) { 

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
	 * @todo No longer need to fill variable like this -- we can just return
	 * Grabs the raw WordPress page and hands it back in $toVarName for processing.
	 * We have to prepare everything here, because whe the code is returned it must be ALL executed in
	 * the global scope
	 * Even the if statements must be prepared, as the result is cached.
	 * @param string $varName the name of the variable in the global scope to fill with the page contents.
	 */
	function get_wp_page($toVarName) {
		require_once($this->phpbb_root . 'wp-united/template-tags.' . $this->phpEx);
		$this->prepare('ob_start();');
		$this->prepare('if ( $GLOBALS[\'latest\']) {define("WP_USE_THEMES", false);} else {define("WP_USE_THEMES", true);}');
		$this->prepare('global $wp_did_header; $wp_did_header = true;');
		$this->prepare('wp();');
		$this->prepare('if (!$latest ):');
			$this->prepare('if (!defined(\'WPU_REVERSE_INTEGRATION\')):');
				$this->prepare('global $wpuNoHead, $wpSettings;');
				$this->prepare('eval($wpUtdInt->fix_template_loader());');
			$this->prepare('endif;');
		$this->prepare('else:');
			$this->prepare('include($phpbb_root_path . \'wp-united/latest-posts.\' . $phpEx);');
		$this->prepare('endif;');
		$this->prepare('$' . $toVarName . ' = ob_get_contents();');
		$this->prepare('ob_end_clean();');
	}
	
	/**
	 * Fixes and returns template-loader
	* @todo this can use the plugin fixer tools to enter and fix themes too
	* @todo cache
	* The plugin fixer should extend a base general compatibility class in order to do so.
	* This must be executed just-in-time as WPINC is not yet set
	*/
	function fix_template_loader() {
		$wpuTemplate = file_get_contents(ABSPATH . WPINC . '/template-loader.php');
		$finds = array(
			'return;',
			'do_feed',
			'do_action(\'do_robots\');',
			'if ( is_trackback() ) {',
			'} else if ( is_author()',
			'} else {'
		);
		$repls = array(
			'',
			'$wpuNoHead = true; do_feed',
			'$wpuNoHead = true; do_action(\'do_robots\');',
			'if (is_trackback()) {$wpuNoHead=true;',
			'}else if(is_author()&& !empty($wpSettings[\'usersOwnBlogs\']) && $wp_template=get_author_template()){include($wp_template);} else if ( is_author()',
			'} else { $wpuNoHead = true;'
		);
		$wpuTemplate = str_replace($finds, $repls, $wpuTemplate);
		return "\n" . '?' . '>' . $wpuTemplate . '<' . '?php' . "\n";
	}

	/**
	 * Used by usercp_register.php - changes the WP username.
	 * @param string $oldName The old WordPress username
	 * @param string $newName The new WordPress username
	 */
	function wp_change_username($oldName, $newName) {
			// set the global vars we need
			foreach ($this->globalVarsStore as $varName => $varValue) {
				if ( !array_key_exists($varName, $this->globalRefs) ) {
					if ( !($varName == 'oldName') && !($varName == 'newName') ) {
						global $$varName;
						$$varName = $varValue;
					}
				}
			}	
		
			//load relevant user data
			$oldName = sanitize_user($oldName, true);
			$newName = sanitize_user($newName, true);
			$wpUserdata = get_userdatabylogin($oldName);
			if ( !empty($wpUserdata) ) {
				$wpID = $wpUserdata->ID;
				$query = "UPDATE $wpdb->users SET user_login='$newName' WHERE ID = '$wpID'";

				$wpdb->query( $query );
				wp_cache_delete($wpUserdata->ID, 'users');
				wp_cache_delete($wpUserdata->user_login, 'userlogins');
				wp_cache_delete($newName, 'userlogins');
				
				// not much else to do after this - so no need to check for new vars in this fn.
		}
	}
	
	/**
	 * Code wrapper for logging out of WordPress]
	 */
	function wp_logout() {
		$this->prepare('$wpUtdInt->do_wp_logout();');
	}

	/** 
	 * 
	 * Logs the current user out of WordPress
	 */
	function do_wp_logout() {
		if($this->wpVersion >= 2.5) {
			wp_logout();
			unset($_COOKIE[AUTH_COOKIE]);
			unset($_COOKIE[SECURE_AUTH_COOKIE]);
			unset($_COOKIE[LOGGED_IN_COOKIE]);
		} else {
			wp_clearcookie();
		}
		do_action('wp_logout');
		wp_set_current_user(0, 0);
		nocache_headers();
		unset($_COOKIE[USER_COOKIE]);
		unset($_COOKIE[PASS_COOKIE]);
	
	}
	/**
	 * Interated login debug -- adds to or displays the debugging information
	 * @param string $add_to_debug The string to add
	 * @param int $end_debug_now Make non-zero to close the debug block and make it ready for display
	 */
	function lDebug($add_to_debug, $end_debug_now = 0) {
		if ( defined('WPU_DEBUG') && (WPU_DEBUG == TRUE) ) {
			if ( empty($this->debugBuffer) ) {
				$this->debugBuffer = '<!-- wpu-debug --><div style="border: 1px solid #8f1fff; background-color: #cc99ff; padding: 3px; margin: 6px; color: #ffff99;"><strong>DEBUG</strong><br />WP Version = ' . $GLOBALS['wp_version'] . '<br />';
			}
			if ( !empty($add_to_debug) ) {
				$this->debugBuffer .= $add_to_debug . '<br />';
			}
			if ($end_debug_now) {
				global $lDebug;
				$lDebug = $this->debugBuffer . '</div><!-- /wpu-debug -->';
				$this->debugBufferFull = TRUE;				
			}

		}	
	
	}
	
	/**
	 * Exits this class, and cleans up, restoring phpBB variable state
	 */
	function exit_wp_integration() {
		global $phpbbForum;
		
		// check, in case user has deactivated wpu-plugin
		if(isset($phpbbForum)) {
			$phpbbForum->enter();
		}

		// We previously here mopped up all the WP vars that had been created... but it is a waste of CPU and usually unnecessary

		//reinstate all the phpBB variables that we've put "on ice", let them overwrite any variables that were claimed by WP.
		foreach ($this->sleepingVars as $varName => $varVal) {
				if ( ($varName != 'wpuNoHead') && ($varName != 'wpuCache') ) {
					global $$varName;
					$$varName = $varVal;
				}
		}
		
		// WordPress removes $_COOKIE from $_REQUEST, which is the source of much wailing and gnashing of teeth
		$_REQUEST = array_merge($_COOKIE, $_REQUEST);
		
		$GLOBALS['IN_WORDPRESS'] = 0; //just in case
		$this->wpRun = '';
	}
	
	/**
	 * switch DB must be called *every time* whenever we want to switch between the WordPress and phpBB DB
	 * We can't just acces $db and $wpdb without doing this first.
	 * @param string $direction Set to 'TO_P' to switch to phpBB, or 'TO_W' to switch to WordPress.
	 */
	function switch_db ($direction = 'TO_P') {
		if ( ($this->wpLoaded) && (!$this->phpbb_db_name != DB_NAME) ) {
			switch ( $direction ) {
				case 'TO_P':			
					global $db, $dbms;
					if(!empty($db->db_connect_id)) {
						if($dbms=='mysqli') {
							@mysqli_select_db($this->phpbb_db_name, $db->db_connect_id);
						} else if($dbms=='mysql') {
							@mysql_select_db($this->phpbb_db_name, $db->db_connect_id);
						}
					}
					break;
				case 'TO_W':
				default;
					global $wpdb;
					if(!empty($wpdb->dbh)) {
						@mysql_select_db(DB_NAME, $wpdb->dbh);
					} 				
					break;
			}
		}
	}
		
	/**
	 * Updates the Integration ID stored in phpBB profile
	 */
	function update_int_ID($pID, $intID) {
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
	 * Gets the logged-in user's level so we can arbitrate permissions
	 */
	function get_userlevel($phpbb_userdata) {

		global $db, $phpbbForum;
		
		$user_level = FALSE;
		
		if ( (!$phpbbForum->user_logged_in()) || !( ($phpbbForum->get_userdata('user_type') == USER_NORMAL) || ($phpbbForum->get_userdata('user_type') == USER_FOUNDER) ) ) {
			return FALSE;
		}
		
		global $auth, $user;
		$auth->acl($user->data);
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
		$this->lDebug($debug);
		$this->lDebug('User level set to: ' . $user_level);

		return $user_level;
	}
	
	
	/**
	 * Arbitrates the user permissions between phpBB and WordPress. Called internally by integrate_login
	 * @param int $ID WordPress ID
	 * @param the required WordPress role
	 */
	function check_userlevels ($ID, $usrLevel) {
		global $userdata;
		$user = new WP_User($ID);
		$user->set_role($usrLevel);
		$userdata = get_userdata($ID);
		wp_set_current_user($ID);
		return $userdata;
	}
	
	/**
	 * Arbitrates the user details - e-mail, password, website, aim, yim, between phpBB & WordPress -- called internally by integrate_login
	 * Basically, just overwrites WP values with the current phpBB values.
	 * We try to update these whenever they are changed, but that's not always the case, so for now we also do this on each access.
	 *	@param mixed $wpData WordPress user data
	 * @param mixed $pData phpBB user data
	 * @return mixed array of fields to update
	 */
	function check_details_consistency($wpData, $pData, $newUser = false) {	

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
	
	
}
?>