<?php
/** 
*
* New WP-United widgets
*
* @package WP-United
* @version $Id: v0.8.4RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/

/**
 */

// this file will also be called in wp admin panel, when phpBB is not loaded. 
if ( !defined('ABSPATH') && !defined('IN_PHPBB') ) {
	exit;
}

class WPU_Login_User_info extends WP_Widget {
	global $phpbbForum;
	function WPU_Login_User_Info() {
		$widget_ops = array('classname' => 'wpulogininfo', 'description' => 'Displays the logged-in user\'s details, such as username, avatar, and number of posts since last visit, together with varius meta links. If the user is logged out, displays a phpBB login form.' );
		$this->WP_Widget('wpu-login-info', $phpbbForum->lang['wpu_loginbox_desc'], $widget_ops);
	}
	
	function widget($args, $instance) {
		// prints the widget
	}
 
	function update($new_instance, $old_instance) {
		//save the widget
	}
 
	function form($instance) {
		//widgetform in backend
	}
	
		
}

/**
 * Wrapper function for initialising widgets
 */
function wpu_widgets_init() {

register_widget('WPU_Login_User_info');

}