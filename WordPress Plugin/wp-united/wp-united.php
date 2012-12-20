<?php

/*
Plugin Name: WP-United
Plugin URI: http://www.wp-united.com
Description: WP-United connects to your phpBB forum and integrates user sign-on, behaviour and theming. Once your forum is up and running, you should not disable this plugin.
Author: John Wells
Author URI: http://www.wp-united.com
Version: 0.9.1.3
Last Updated: 20 December 2012
* 
*/


if ( !defined('ABSPATH') ) {
	exit;
}

// The WP-United class may be called as a base class from the phpBB side, or a fully fledged plugin class from here. 
// This file could be invoked from either side to instantiate the object.
// The WP-United class then decorates itself with a cross-package settings object.
if( !class_exists( 'WP_United_Plugin' ) ) {
	require_once(plugin_dir_path(__FILE__) . 'base-classes.php');
	require_once(plugin_dir_path(__FILE__) . 'plugin-main.php');
	global $wpUnited;
	$wpUnited = new WP_United_Plugin();	
}
$wpUnited->wp_init();


function wpu_deactivate() {
	delete_option('wpu_enabled');
}

/**
 * Removes all WP-United settings.
 * As the plugin is deactivated at this point, we can't reliably uninstall from phpBB (yet)
 */
function wpu_uninstall() {
	
	if(!defined('WP_UNINSTALL_PLUGIN')) {
		return;
	}
	
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
	
	return 'DEACTIVATED!!!';

}

register_deactivation_hook('wp-united/wp-united.php', 'wpu_deactivate');
register_uninstall_hook('wp-united/wp-united.php', 'wpu_uninstall');

// That is all. WP-United is a very large plugin. To understand the code, start in:
// plugin-main.php: The main plugin class that contains all the hooks and filters which are loaded as needed
// <phpbb>/includes/hooks/hook_wp-united.php: The phpBB hook file that loads WP-United from the phpBB side if needed


?>
