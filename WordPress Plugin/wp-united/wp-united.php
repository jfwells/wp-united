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
	
	return "DEACTIVATED!!!";
	
	
}

register_deactivation_hook('wp-united/wp-united.php', 'wpu_deactivate');
register_uninstall_hook('wp-united/wp-united.php', 'wpu_uninstall');

?>
