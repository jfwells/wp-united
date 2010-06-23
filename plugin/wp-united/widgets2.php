<?php
/** 
*
* New WP-United widgets
*
* @package WP-United
* @version $Id: v0.9.0RC3 2010/06/22 John Wells (Jhong) Exp $
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

/**
 * Returns a nice block containing info about the phpBB user that is currently logged in *to phpBB*
 */
class WPU_Login_User_info extends WP_Widget {
	
	function WPU_Login_User_Info() {
		global $phpbbForum;
		$widget_ops = array('classname' => 'wp-united-loginuser-info', 'description' => 'Displays the logged-in user\'s details, such as username, avatar, and number of posts since last visit, together with varius meta links. If the user is logged out, displays a phpBB login form.' );
		$this->WP_Widget('wp-united-loginuser-info', $phpbbForum->lang['wpu_loginbox_desc'], $widget_ops);
	}
	
	function widget($args, $instance) {
		// prints the widget
		global $phpbbForum;
		
		extract($args, EXTR_SKIP);
		
		$titleLoggedIn = empty($instance['title-logged-in']) ? '&nbsp;' : apply_filters('widget_title', $instance['title-logged-in']);
		$titleLoggedOut = empty($instance['title-logged-out']) ? '&nbsp;' : apply_filters('widget_title', $instance['title-logged-out']);

		$loginForm = $instance['login-form'];
		$rankBlock = $instance['rank'];
		$newPosts = $instance['new'];
		$write = $instance['write'];
		$admin = $instance['admin'];

		if ( !function_exists('wpu_login_user_info') ) return;
		
		$title =  ($phpbbForum->user_logged_in()) ? $titleLoggedIn : $titleLoggedOut;

		echo $before_widget . $before_title . $title . $after_title;
		echo '<ul class="wpulogininfo">';
		wpu_login_user_info("showLoginForm={$loginForm}&showRankBlock={$rankBlock}&showNewPosts={$newPosts}&showWriteLink={$write}&showAdminLinks={$admin}");
		echo '</ul>';
		echo $after_widget;
	}
 
	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		$input = array(_);
		$instance['title-logged-in'] = strip_tags(stripslashes($new_instance['title-logged-in']));
		$instance['title-logged-out'] = strip_tags(stripslashes($new_instance['title-logged-out']));
		
		$instance['login-form'] = (strip_tags(stripslashes($new_instance['login-form'])) == 'login-form')? 1 : 0;
		$instance['rank'] = (strip_tags(stripslashes($new_instance['rank'])) == 'rank')? 1 : 0;
		$instance['new'] = (strip_tags(stripslashes($new_instance['new'])) == 'new')? 1 : 0;
		$instance['write'] = (strip_tags(stripslashes($new_instance['write'])) == 'write')? 1 : 0;
		$instance['admin'] = (strip_tags(stripslashes($new_instance['admin'])) == 'admin')? 1 : 0;

		return $instance;
	}
 
	function form($instance) {
		//widget form
		global $phpbbForum;
		
		$instance = wp_parse_args( (array) $instance, array( 
			'title-logged-in'=> $phpbbForum->lang['wpu_loginbox_loggedin'],
			'title-logged-out'=>$phpbbForum->lang['wpu_loginbox_loggedout'],
			'rank'=>1, 
			'new'=>1, 
			'write' => 0,
			'admin' => 0,
			'login-form'=>1
		));
		
		$titleLoggedIn = strip_tags($instance['title-logged-in']);
		$titleLoggedOut = strip_tags($instance['title-logged-out']);
		 
		$rank= (!empty($instance['rank'])) ? 'checked="checked"' : '';
		$new = (!empty($instance['new'])) ? 'checked="checked"' : '';
		$write = (!empty($instance['write'])) ? 'checked="checked"' : '';
		$admin = (!empty($instance['admin'])) ? 'checked="checked"' : '';
		$loginForm = (!empty($instance['login-form'])) ? 'checked="checked"' : '';
		?>
		
		<p><label for="<?php echo $this->get_field_id('title-logged-in'); ?>"><?php echo $phpbbForum->lang['wpu_loginbox_panel_loggedin'] ?> <input class="widefat" id="<?php echo $this->get_field_id('title-logged-in'); ?>" name="<?php echo $this->get_field_name('title-logged-in'); ?>" type="text" value="<?php echo attribute_escape($titleLoggedIn); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('title-logged-out'); ?>"><?php echo $phpbbForum->lang['wpu_loginbox_panel_loggedout'] ?> <input class="widefat" id="<?php echo $this->get_field_id('title-logged-out'); ?>" name="<?php echo $this->get_field_name('title-logged-out'); ?>" type="text" value="<?php echo attribute_escape($titleLoggedOut); ?>" /></label></p>
		<p><input id="<?php echo $this->get_field_id('rank'); ?>" name="<?php echo $this->get_field_name('rank'); ?>" type="checkbox" value="rank" <?php echo $rank ?> /> <label for="<?php echo $this->get_field_id('rank'); ?>"><?php echo $phpbbForum->lang['wpu_loginbox_panel_rank'] ?></label></p>
		<p><input id="<?php echo $this->get_field_id('new'); ?>" name="<?php echo $this->get_field_name('new'); ?>" type="checkbox" value="new"  <?php echo $new ?> /> <label for="<?php echo $this->get_field_id('new'); ?>"><?php echo $phpbbForum->lang['wpu_loginbox_panel_newposts'] ?></label></p>
		<p><input id="<?php echo $this->get_field_id('write'); ?>" name="<?php echo $this->get_field_name('write'); ?>" type="checkbox" value="write" <?php echo $write ?> /> <label for="<?php echo $this->get_field_id('write'); ?>"><?php echo $phpbbForum->lang['wpu_loginbox_panel_write'] ?></label></p>
		<p><input id="<?php echo $this->get_field_id('admin'); ?>" name="<?php echo $this->get_field_name('admin'); ?>" type="checkbox" value="admin" <?php echo $admin ?> /> <label for="<?php echo $this->get_field_id('admin'); ?>"><?php echo $phpbbForum->lang['wpu_loginbox_panel_admin'] ?></label></p>
		<p><input id="<?php echo $this->get_field_id('login-form'); ?>" name="<?php echo $this->get_field_name('login-form'); ?>" type="checkbox" value="login-form" <?php echo $loginForm ?> /> <label for="<?php echo $this->get_field_id('login-form'); ?>"><?php echo $phpbbForum->lang['wpu_loginbox_panel_loginform'] ?></label></p>
		
		<?php
	}	
}


/**
 * Latest phpBB topics widget
 * Returns a lsit of recent topics, in the format XXXXX posted by YYYYYY in ZZZZZZZ.
 */
class WPU_Latest_Phpbb_Topics extends WP_Widget {
	
	function WPU_Latest_Phpbb_Topics() {
		global $phpbbForum;
		$widget_ops = array('classname' => 'wp-united-latest-topics', 'description' => 'Shows the latest topics posted in the phpBB forum.' );
		$this->WP_Widget('wp-united-latest-topics', $phpbbForum->lang['wpu_forumtopics_desc'], $widget_ops);
	}
	
	function widget($args, $instance) {
		// prints the widget
		global $phpbbForum;
		
		extract($args, EXTR_SKIP);
		
		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		$maxEntries = empty($instance['max']) ? 25 : $instance['title'];

		if ( !function_exists('wpu_latest_phpbb_topics') ) return false;
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="wpulatesttopics">';
		wpu_latest_phpbb_topics('limit='.$maxEntries);
		echo '</ul>' . $after_widget;

	}
 
	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['max'] = (int) strip_tags(stripslashes($new_instance['max']));

		return $instance;
	}
 
	function form($instance) {
		//widget form
		global $phpbbForum;
		
		$instance = wp_parse_args((array) $instance, array( 
			'title' => $phpbbForum->lang['wpu_forumtopics_panel_title'],
			'max' => 25
		));
		
		$title = strip_tags($instance['title']);
		$max = strip_tags($instance['max']);

		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo $phpbbForum->lang['wpu_panel_heading'] ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('max'); ?>"><?php echo $phpbbForum->lang['wpu_panel_max_entries'] ?> <input class="widefat" id="<?php echo $this->get_field_id('max'); ?>" maxlength="3" name="<?php echo $this->get_field_name('max'); ?>" type="text" value="<?php echo attribute_escape($max); ?>" /></label></p>
		<?php
	}	
}

/**
 * Latest phpBB posts widget
 * Returns a lsit of recent posts
 */
class WPU_Latest_Phpbb_Posts extends WP_Widget {
	
	function WPU_Latest_Phpbb_Posts() {
		global $phpbbForum;
		$widget_ops = array('classname' => 'wp-united-latest-posts', 'description' => 'Shows the latest posts posted in the phpBB forum.' );
		$this->WP_Widget('wp-united-latest-posts', $phpbbForum->lang['wpu_forumposts_desc'], $widget_ops);
	}
	
	function widget($args, $instance) {
		// prints the widget
		global $phpbbForum;
		
		extract($args, EXTR_SKIP);
		
		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		$maxEntries = empty($instance['max']) ? 25 : (int)$instance['max'];

		if ( !function_exists('wpu_latest_phpbb_posts') ) return false;
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="wpulatestposts">';
		wpu_latest_phpbb_posts('limit='.$maxEntries); 
		echo '</ul>' . $after_widget;

	}
 
	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['max'] = (int) strip_tags(stripslashes($new_instance['max']));

		return $instance;
	}
 
	function form($instance) {
		//widget form
		global $phpbbForum;
		
		$instance = wp_parse_args((array) $instance, array( 
			'title' => $phpbbForum->lang['wpu_forumposts_panel_title'],
			'max' => 25
		));
		
		$title = strip_tags($instance['title']);
		$max = strip_tags($instance['max']);

		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo $phpbbForum->lang['wpu_panel_heading'] ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('max'); ?>"><?php echo $phpbbForum->lang['wpu_panel_max_entries'] ?> <input class="widefat" id="<?php echo $this->get_field_id('max'); ?>" maxlength="3" name="<?php echo $this->get_field_name('max'); ?>" type="text" value="<?php echo attribute_escape($max); ?>" /></label></p>
		<?php
	}	
}


/**
 * Wrapper function for initialising widgets
 */
function wpu_widgets_init() {
	register_widget('WPU_Login_User_info');
	register_widget('WPU_Latest_Phpbb_Topics');
	register_widget('WPU_Latest_Phpbb_Posts');
}