<?php

/** 
*
* WP-United Hooks
*
* @package WP-United
* @version $Id: wp-united.php,v 0.8.0 2009/12/20 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
 */
if ( !defined('IN_PHPBB') ) {
	exit;
}

require_once ($phpbb_root_path . 'wp-united/version.' . $phpEx);
require_once ($phpbb_root_path . 'wp-united/options.' . $phpEx);
require_once($phpbb_root_path . 'wp-united/functions-general.' . $phpEx);

require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);		
$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 

if(!defined('ADMIN_START') && (!defined('WPU_PHPBB_IS_EMBEDDED'))) {  
	if (!((defined('WPU_DISABLE')) && WPU_DISABLE)) {  
		$phpbb_hook->register('phpbb_user_session_handler', 'wpu_init');
		$phpbb_hook->register(array('template', 'display'), 'wpu_execute', 'last');
		$phpbb_hook->register('exit_handler', 'wpu_continue');
	}
}

/**
 * Initialise WP-United variables and template strings
 */
function wpu_init(&$hook) {
	global $wpSettings, $phpbb_root_path, $phpEx, $template;

	if  ($wpSettings['installLevel'] == 10) {
		$template->assign_vars(array(
			'U_BLOG'	 =>	append_sid($GLOBALS['wpSettings']['blogsUri']),
			'S_BLOG'	=>	TRUE,
		));  
		//Do a reverse integration?
		if (($wpSettings['showHdrFtr'] == 'REV') && !defined('WPU_BLOG_PAGE')) {
			if (empty($gen_simple_header)) {
				define('WPU_REVERSE_INTEGRATION', true);
				ob_start();
			}
		}
	} 	
}



/**
 * Capture the outputted page, and prevent phpBB for exiting
 */
function wpu_execute(&$hook, $handle) {
	global $wpuRunning, $wpSettings, $template, $innerContent, $phpbb_root_path, $phpEx, $db, $cache;
	// We only want this action to fire once
	if ( (!$wpuRunning) &&  ($wpSettings['installLevel'] == 10) ) {
		$wpuRunning = true;
		//$hook->remove_hook(array('template', 'display'));
		if (defined('WPU_REVERSE_INTEGRATION')) {
			$template->display($handle);
			$innerContent = ob_get_contents();
			ob_end_clean(); 
			//insert phpBB into a wordpress page
			include ($phpbb_root_path . 'wp-united/integrator.' . $phpEx);
		} elseif (defined('PHPBB_EXIT_DISABLED')) {
			/**
			 * page_footer was called, but we don't want to close the DB connection & cache yet
			 */
			$template->display($handle);
			$GLOBALS['bckDB'] = $db;
			$GLOBALS['bckCache'] = $cache;
			$db = ''; $cache = '';
			
			return "";
		} // else display as normal
	}
}

/**
 * Prevent phpBB from exiting
 */
function wpu_continue(&$hook) {
	if (defined('PHPBB_EXIT_DISABLED') && !defined('WPU_FINISHED')) {
		return "";
	}
}

?>