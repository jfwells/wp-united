<?php

/*
Plugin Name: WP-United
Plugin URI: http://www.wp-united.com
Description: WP-United connects to your phpBB forum and integrates user sign-on, behaviour and theming. Once your forum is up and running, you should not disable this plugin.
Author: John Wells
Author URI: http://www.wp-united.com
Version: v0.9.0 RC3 
Last Updated: 9 November 2012
* 
*/


// this file will also be called in wp admin panel, when phpBB is not loaded. So we don't check IN_PHPBB.
// ABSPATH should *always* be set though!
if ( !defined('ABSPATH') ) {
	exit;
}

if( !class_exists( 'WP_United_Plugin' ) ) {
	require_once(plugin_dir_path(__FILE__) . 'base-classes.php');
	require_once(plugin_dir_path(__FILE__) . 'plugin-main.php');
	global $wpUnited;
	$wpUnited = new WP_United_Plugin();	
}
$wpUnited->wp_init();


function wpu_deactivate() {
	// No actions currently defined
	wpu_uninstall();  /** TEMP FOR RESETTING WHILE TESTING **/ // TODO: MUST DISABLE THIS BEFORE RELEASE!!!!!!!!!!!
}

/**
 * Removes all WP-United settings.
 * As the plugin is deactivated at this point, we can't reliably uninstall from phpBB (yet)
 */
function wpu_uninstall() {
	
	$forum_page_ID = get_option('wpu_set_forum');
	if ( !empty($forum_page_ID) ) {
		@wp_delete_post($forum_page_ID);
	}
	
	$options = array(
		'wpu_set_forum',
		'wpu-settings',
		'wpu-last-run',
		'wpu-enabled',
		'widget_wp-united-loginuser-info',
		'widget_wp-united-latest-topics',
		'widget_wp-united-latest-posts'
	);
	
	foreach($options as $option) {
		delete_option($option);
	}
	
	// TODO: ALSO SET ANY USER OPTIONS, EG INTEG ID & AVATAR
	
	/*
	if(isset($wpUnited->get_setting('phpbb_path'))) {
		
		global $db;
		
		$phpbb_root_path = $wpUnited->get_setting('phpbb_path');
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
	
		define('IN_PHPBB', true);
		define('WPU_UNINSTALLING', true);
		
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		
		$commonLoc = $phpbb_root_path . 'common.' . $phpEx;
		
		if(file_exists($commonLoc)) {
			include($phpbb_root_path . 'common.' . $phpEx);
			
			$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
						  DROP user_wpuint_id';
			$db->sql_query($sql);
			
			$sql = 'ALTER TABLE ' . USERS_TABLE . '
						DROP user_wpublog_id';
			$db->sql_query($sql);
					
			$sql = 'ALTER TABLE ' . POSTS_TABLE . ' 
						DROP post_wpu_xpost';
			$db->sql_query($sql);
			
		}
	} */
	
	
}

register_deactivation_hook('wp-united/wp-united.php', 'wpu_deactivate');
register_uninstall_hook('wp-united/wp-united.php', 'wpu_uninstall');

?>
