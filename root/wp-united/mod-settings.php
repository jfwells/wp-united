<?php
/** 
*
* WP-United Mod Settings
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/01/14 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
 * A few functions to write and retrieve the integration package settings from the DB
 * This is a bit messy & unneccesarily complex, a result of our previous simultaneous support for phpBB2 & phpBB3.
 * The phpBB2 version used its own integration table, hence the schema.
 * 
 * In phpBB3, we just pull them from the config table.
 * Why don't we access them directly?  We may do in the future, but for now, this affords us more flexibility, and saves rewriting the rest of the mod.
 * @todo remove schema for v1.0
 */



if ( !defined('IN_PHPBB') ) {
	exit;
}

/**
 * Returns a map of the structure of our database against the variables we use in WP-United.
 * LEFT SIDE = VARIABLE NAMES
 * RIGHT SIDE = DB FIELD NAMES
 */

function get_db_schema() {

	$dbSchema = array( 
		'blogsUri' => 'blogsEntry',
		'wpUri' => 'fullUri' ,
		'wpPath' => 'fullPath', 
		'integrateLogin' => 'wpLogin', 
		'showHdrFtr' => 'showInside',
		'wpSimpleHdr' => 'wpHdrSimple',
		'dtdSwitch' => 'dtdChange',
		'installLevel' => 'installStage',
		'usersOwnBlogs' => 'ownBlogs',
		'buttonsProfile' => 'profileBtn',
		'buttonsPost' => 'postBtn',
		'allowStyleSwitch' => 'styleSwitch',
		'useBlogHome' => 'blogHomePage',
		'blogListHead' => 'blogHomeTitle',
		'blogIntro' => 'blogIntro',
		'blogsPerPage' => 'numBlogsPerPage',
		'blUseCSS' => 'wpublStyles',
		'charEncoding' => 'encoding',
		'phpbbCensor' => 'censorPosts',
		'wpuVersion' => 'wpuVer',
		'wpPageName' => 'wpPage',
		'phpbbPadding' => 'pPadding',
		'mustLogin' => 'mustLogin',
		'upgradeRun' => 'ugRun',
		'xposting' => 'xposting',
		'phpbbSmilies' => 'phpbbSmilies',
		'xpostautolink' => 'xpostautolink',
		'xpostforce' => 'xpostforce',
		'xposttype' => 'xposttype',		
		'cssMagic' => 'cssmagic',
		'templateVoodoo' => 'tempvoodoo',
		'pluginFixes' => 'pluginfixes',
		'useForumPage' => 'useforumpage'
	);
	
	return $dbSchema;
}

/**
 * Set default values for WPU settings
 */
function set_default($setting_key) {
	global $phpEx, $config, $phpbb_root_path, $phpEx, $user;

	$server = add_http(add_trailing_slash($config['server_name']));
	$scriptPath = add_trailing_slash($config['script_path']);
	$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
	$defaultBlogUri = $server . $scriptPath . "blog." . $phpEx;
	
	$defaults = array(
		'blogsUri' => $defaultBlogUri,
		'wpUri' => '' ,
		'wpPath' => '', 
		'integrateLogin' => 0, 
		'showHdrFtr' => 'FWD',
		'wpSimpleHdr' => 1,
		'dtdSwitch' => 0,
		'installLevel' => 0,
		'usersOwnBlogs' => 0,
		'buttonsProfile' => 0,
		'buttonsPost' => 0,
		'allowStyleSwitch' => 0,
		'useBlogHome' => 0,
		'blogListHead' => $user->lang['WPWiz_BlogListHead_Default'],
		'blogIntro' => $user->lang['WPWiz_blogIntro_Default'],
		'blogsPerPage' => 6,
		'blUseCSS' => 1,
		'phpbbCensor' => 1,
		'wpuVersion' => $user->lang['WPU_Not_Installed'],
		'wpPageName' => 'page.php',
		'phpbbPadding' =>  '6-12-6-12',
		'mustLogin' => 0,
		'upgradeRun' => 0,
		'xposting' => 0,
		'phpbbSmilies' => 0,
		'xpostautolink' => 0,
		'xpostforce' => -1,
		'xposttype' => 'EXCERPT',	
		'cssMagic' => 1,
		'templateVoodoo' => 1,
		'pluginFixes' => 0,
		'useForumPage' => 1
		
	);
	
	return $defaults[$setting_key];

}


/**
 * Get configuration setings from database
 * Gets the configuration settings from the db, and returns them in $wpSettings.
 * Sets initial values to sensible deafaults if they haven't been set yet.
 */
function get_integration_settings($setAdminDefaults = FALSE) {
	global $config, $db, $phpbb_root_path, $phpEx;
	
	$configFields = get_db_schema();
	$wpSettings = array();
	foreach($configFields as $varName => $fieldName) {
		if(isset($config["wpu_{$fieldName}"])) {
			if ($config["wpu_{$fieldName}"] !== FALSE) {
				$wpSettings[$varName] = $config["wpu_{$fieldName}"];
			} else {
				$wpSettings[$varName] ='';
			}
		} elseif ($setAdminDefaults) {
			$wpSettings[$varName] = set_default($varName);
		}
	}
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
	require_once($phpbb_root_path . 'wp-united/version.' . $phpEx);
	
	return $wpSettings;	
	
}
/**
 * Clear integration settings
 * Completely removes all traces of WP-united settings
 */
function clear_integration_settings() {
	global $db, $config;
	
	$config_fields = get_db_schema();
	$key_names = array();
	foreach ($config_fields as $config_field) {
		$key_names[] = 'wpu_' . $config_field;
	}
	
	$sql = 'DELETE FROM ' . CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $key_names);
	$db->sql_query($sql);
	
	if(isset($config['wpu_style_keys_1'])) {
	$sql = 'DELETE FROM ' . CONFIG_TABLE . ' 
		WHERE config_name LIKE \'wpu_style_keys_%\'';
	$db->sql_query($sql);
}

}

/**
 * Write config settings to the database
 * Writes any configuration settings that are passed to the integration settings table.
 * phpBB2 code path removed for v0.8
*/
function set_integration_settings($dataIn) {
	global $db;
	
	// Map DB schema to our data keys
	$fullFieldSet = get_db_schema();
	
	//Clean data, and convert it to our DB schema
		foreach ($fullFieldSet as $varName => $fieldName ) {
			if ( array_key_exists($varName, $dataIn) ) {
				$data[$fieldName] =	$dataIn[$varName];
				set_config('wpu_'.$fieldName, $dataIn[$varName]);
			}
		}
	
	return true;
}

/**
 * Clean for db reinsertion
 * @todo check magic quotes etc
 */
function clean_for_db_reinsert($value) {
//$value = str_replace("'", "''", $value);
$value = addslashes($value);
$value = str_replace("\'", "''", $value);
	return $value;

}


?>