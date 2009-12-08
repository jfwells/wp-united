<?php
require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);	
if(!defined('ADMIN_START')) {
	$phpbb_hook->register('phpbb_user_session_handler', 'wpu_init');
	$phpbb_hook->register(array('template', 'display'), 'wpu_execute', 'last');
	$phpbb_hook->register('exit_handler', 'wpu_continue');
}

//initialise WP-United variables and template strings
function wpu_init(&$hook) {
	global $template, $phpEx, $phpbb_root_path;
	require_once($phpbb_root_path . 'wp-united/wpu-actions.' . $phpEx);
	$GLOBALS['wpu_actions']->do_head($template);
}



// prevent default exit from firing, so we can get do an ob callback

function wpu_execute(&$hook, $handle) {
	global $wpuRunning, $template, $innerContent, $phpbb_root_path, $phpEx;
	if(!$wpuRunning) {
		$wpuRunning = true;
		$hook->remove_hook(array('template', 'display'));
		$template->display($handle);
		if (defined('WPU_REVERSE_INTEGRATION')) { 
			$innerContent = ob_get_contents();
			ob_end_clean(); 
			//insert phpBB into a wordpress page
			include ($phpbb_root_path . 'wp-united/integrator.' . $phpEx);
			
		} elseif (defined('PHPBB_EXIT_DISABLED')) {
			return "";
		}
	}
}

function wpu_continue(&$hook) {

	if (defined('PHPBB_EXIT_DISABLED') && !defined('WPU_FINISHED')) {
		return "";
	}
}

?>
