<?php
/** 
*
* New WP-United widgets
*
* @package WP-United
* @version $Id: v0.9.0RC3 2012/12/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2012 wp-united.com
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
class WPU_Login_User_info_Widget extends WP_Widget {
	
	public function __construct() {
		$widget_ops = array('classname' => 'wp-united-loginuser-info', 'description' => __('Displays the logged-in user\'s details, such as username, avatar, and number of posts since last visit, together with varius meta links. If the user is logged out, displays a phpBB login form.', 'wp-united') );
		$this->WP_Widget('wp-united-loginuser-info', __('WP-United Login / User Info Box', 'wp-united'), $widget_ops);
	}
	
	public function widget($args, $instance) {
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
 
	public function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;

		$instance['title-logged-in'] = strip_tags(stripslashes($new_instance['title-logged-in']));
		$instance['title-logged-out'] = strip_tags(stripslashes($new_instance['title-logged-out']));
		
		$instance['login-form'] = (strip_tags(stripslashes($new_instance['login-form'])) == 'login-form')? 1 : 0;
		$instance['rank'] = (strip_tags(stripslashes($new_instance['rank'])) == 'rank')? 1 : 0;
		$instance['new'] = (strip_tags(stripslashes($new_instance['new'])) == 'new')? 1 : 0;
		$instance['write'] = (strip_tags(stripslashes($new_instance['write'])) == 'write')? 1 : 0;
		$instance['admin'] = (strip_tags(stripslashes($new_instance['admin'])) == 'admin')? 1 : 0;

		return $instance;
	}
 
	public function form($instance) {
		//widget form
		
		$instance = wp_parse_args( (array) $instance, array( 
			'title-logged-in'=> __('You are logged in as:', 'wp-united'),
			'title-logged-out'=> __('You are not logged in.', 'wp-united'),
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
		
		<p><label for="<?php echo $this->get_field_id('title-logged-in'); ?>"><?php _e('You are logged in as:', 'wp-united'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title-logged-in'); ?>" name="<?php echo $this->get_field_name('title-logged-in'); ?>" type="text" value="<?php echo esc_attr($titleLoggedIn); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('title-logged-out'); ?>"><?php _e('You are not logged in.', 'wp-united'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title-logged-out'); ?>" name="<?php echo $this->get_field_name('title-logged-out'); ?>" type="text" value="<?php echo esc_attr($titleLoggedOut); ?>" /></label></p>
		<p><input id="<?php echo $this->get_field_id('rank'); ?>" name="<?php echo $this->get_field_name('rank'); ?>" type="checkbox" value="rank" <?php echo $rank ?> /> <label for="<?php echo $this->get_field_id('rank'); ?>"><?php _e('Show rank title &amp; image?', 'wp-united'); ?></label></p>
		<p><input id="<?php echo $this->get_field_id('new'); ?>" name="<?php echo $this->get_field_name('new'); ?>" type="checkbox" value="new"  <?php echo $new ?> /> <label for="<?php echo $this->get_field_id('new'); ?>"><?php _e('Show new posts?', 'wp-united');?></label></p>
		<p><input id="<?php echo $this->get_field_id('write'); ?>" name="<?php echo $this->get_field_name('write'); ?>" type="checkbox" value="write" <?php echo $write ?> /> <label for="<?php echo $this->get_field_id('write'); ?>"><?php _e('Show write post link?', 'wp-united'); ?></label></p>
		<p><input id="<?php echo $this->get_field_id('admin'); ?>" name="<?php echo $this->get_field_name('admin'); ?>" type="checkbox" value="admin" <?php echo $admin ?> /> <label for="<?php echo $this->get_field_id('admin'); ?>"><?php _e('Show Admin link?', 'wp-united'); ?></label></p>
		<p><input id="<?php echo $this->get_field_id('login-form'); ?>" name="<?php echo $this->get_field_name('login-form'); ?>" type="checkbox" value="login-form" <?php echo $loginForm ?> /> <label for="<?php echo $this->get_field_id('login-form'); ?>"><?php _e('Show phpBB login form if logged out?', 'wp-united'); ?></label></p>
		
		<?php
	}	
}


/**
 * Latest phpBB topics widget
 * Returns a lsit of recent topics, in the format XXXXX posted by YYYYYY in ZZZZZZZ.
 */
class WPU_Latest_Phpbb_Topics_Widget extends WP_Widget {
	
	public function __construct() {
		$widget_ops = array('classname' => 'wp-united-latest-topics', 'description' => __('Shows the latest topics posted in the phpBB forum.', 'wp-united') );
		$this->WP_Widget('wp-united-latest-topics', __('WP-United Latest phpBB Topics', 'wp-united'), $widget_ops);
	}
	
	public function widget($args, $instance) {
		// prints the widget
		
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
 
	public function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['max'] = (int) strip_tags(stripslashes($new_instance['max']));

		return $instance;
	}
 
	public function form($instance) {
		//widget form
		
		$instance = wp_parse_args((array) $instance, array( 
			'title' => __('Recent Forum Topics', 'wp-united'),
			'max' => 25
		));
		
		$title = strip_tags($instance['title']);
		$max = strip_tags($instance['max']);

		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title: ', 'wp-united'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('max'); ?>"><?php _e('Maximum Entries:', 'wp-united') ?> <input class="widefat" id="<?php echo $this->get_field_id('max'); ?>" maxlength="3" name="<?php echo $this->get_field_name('max'); ?>" type="text" value="<?php echo esc_attr($max); ?>" /></label></p>
		<?php
	}	
}

/**
 * Latest phpBB posts widget
 * Returns a lsit of recent posts
 */
class WPU_Latest_Phpbb_Posts_Widget extends WP_Widget {
	
	public function __construct() {
		global $phpbbForum;
		$widget_ops = array('classname' => 'wp-united-latest-posts', 'description' => __('Shows the latest posts posted in the phpBB forum.', 'wp-united') );
		$this->WP_Widget('wp-united-latest-posts', __('WP-United Latest Forum Posts', 'wp-united'), $widget_ops);
	}
	
	function widget($args, $instance) {
		// prints the widget
		
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
 
	public function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['max'] = (int) strip_tags(stripslashes($new_instance['max']));

		return $instance;
	}
 
	public function form($instance) {
		//widget form
		
		$instance = wp_parse_args((array) $instance, array( 
			'title' => __('Recent Forum Posts', 'wp-united'),
			'max' => 25
		));
		
		$title = strip_tags($instance['title']);
		$max = strip_tags($instance['max']);

		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php  _e('Title: ', 'wp-united'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('max'); ?>"><?php _e('Maximum Entries: ', 'wp-united') ?> <input class="widefat" id="<?php echo $this->get_field_id('max'); ?>" maxlength="3" name="<?php echo $this->get_field_name('max'); ?>" type="text" value="<?php echo esc_attr($max); ?>" /></label></p>
		<?php
	}	
}


class WPU_Forum_Stats_Widget extends WP_Widget {
	public function __construct() {
		$widget_ops = array('classname' => 'wp-united-forum-stats', 'description' => __('Show key forum statistics and information.', 'wp-united') );
		$this->WP_Widget('wp-united-forum-stats', __('WP-United Forum Statistics', 'wp-united'), $widget_ops);
	}
	
	public function widget($args, $instance) {
		
		extract($args, EXTR_SKIP);
		
		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		
		if ( !function_exists('wpu_phpbb_stats') ) return false;
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="wpuforumstats">';
		wpu_phpbb_stats();
		echo '</ul>' . $after_widget;
		
		
	}
	
	public function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		
		return $instance;
	}
	
	public function form($instance) {
		//widget form
		
		$instance = wp_parse_args((array) $instance, array( 
			'title' => __('Forum Stats', 'wp-united')
		));
		
		$title = strip_tags($instance['title']);

		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php  _e('Title: ', 'wp-united'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<?php		
	}
	
	
	
	
}

class WPU_Forum_Users_Online_Widget extends WP_Widget {
	public function __construct() {
		$widget_ops = array('classname' => 'wp-united-users-online', 'description' => __('Show information about users that are currently online, in the usual phpBB format.', 'wp-united') );
		$this->WP_Widget('wp-united-users-online', __('WP-United Users Online', 'wp-united'), $widget_ops);
	}
	
	public function widget($args, $instance) {
		
		extract($args, EXTR_SKIP);
		
		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		$showBreakdown = $instance['showBreakdown'];
		$showRecord = $instance['showRecord'];
		$showLegend = $instance['showLegend'];
		

		if ( !function_exists('wpu_useronlinelist') ) return false;
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<div class="wpuusersonline textwidget">';
		wpu_useronlinelist("before= &after=<br />&showBreakdown={$showBreakdown}&showRecord={$showRecord}&showLegend={$showLegend}");
		echo '</div>' . $after_widget;

	}
	
	public function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		
		$instance['showBreakdown'] 	= (strip_tags(stripslashes($new_instance['showBreakdown'])) == 	'brk')? 1 : 0;
		$instance['showRecord'] 	= (strip_tags(stripslashes($new_instance['showRecord'])) 	== 	'rec')? 1 : 0;
		$instance['showLegend'] 	= (strip_tags(stripslashes($new_instance['showLegend'])) 	== 	'leg')? 1 : 0;
		
		return $instance;
	}
	
	public function form($instance) {
		//widget form
		
		$instance = wp_parse_args( (array) $instance, array( 
			'title' 			=> __('Users Online', 'wp-united'),
			'showBreakdown'		=> 1, 
			'showRecord'		=> 1, 
			'showLegend' 		=> 1
		));
		
		$title = strip_tags($instance['title']);
		 
		$showBreakdown	= (!empty($instance['showBreakdown'])) 	? 'checked="checked"' : '';
		$showRecord 	= (!empty($instance['showRecord'])) 	? 'checked="checked"' : '';
		$showLegend 	= (!empty($instance['showLegend'])) 	? 'checked="checked"' : '';

		?>
		
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php  _e('Title: ', 'wp-united'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><input id="<?php echo $this->get_field_id('showBreakdown'); ?>" name="<?php echo $this->get_field_name('showBreakdown'); ?>" type="checkbox" value="brk"  <?php echo $showBreakdown ?> /> <label for="<?php echo $this->get_field_id('showBreakdown'); ?>"><?php _e('Show a breakdown of user types?', 'wp-united'); ?></label></p>
		<p><input id="<?php echo $this->get_field_id('showRecord'); ?>" name="<?php echo $this->get_field_name('showRecord'); ?>" type="checkbox" value="rec" <?php echo $showRecord ?> /> <label for="<?php echo $this->get_field_id('showRecord'); ?>"><?php _e('Show record number of users?', 'wp-united'); ?></label></p>
		<p><input id="<?php echo $this->get_field_id('showLegend'); ?>" name="<?php echo $this->get_field_name('showLegend'); ?>" type="checkbox" value="leg" <?php echo $showLegend ?> /> <label for="<?php echo $this->get_field_id('showLegend'); ?>"><?php _e('Show legend?', 'wp-united'); ?></label></p>
		
		<?php
	}
	
	
	
	
}


/**
 * Wrapper function for initialising widgets
 */
function wpu_widgets_init() {
	register_widget('WPU_Login_User_info_Widget');
	register_widget('WPU_Latest_Phpbb_Topics_Widget');
	register_widget('WPU_Latest_Phpbb_Posts_Widget');
	register_widget('WPU_Forum_Stats_Widget');
	register_widget('WPU_Forum_Users_Online_Widget');
}
