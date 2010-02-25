<?php
/** 
*
* WP-United WordPress Widgets
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
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
 * Wrapper function for initialising widgets
 */
function wpu_widgets_init() {

	// Check for the required plugin functions. 
	if ( !function_exists('register_sidebar_widget') )
		return;
		
	/**
	 * Returns a nice block containing info about the phpBB user that is currently logged in *to phpBB*
	 */
	function widget_wpu_login_user_info($args) {
		global $phpbbForum;
		/**
		 *  these is_admin() switches should not be necessary, but users are reporting wp-admin errors,
		 * which can be traced back to widgets. Our widgets are not intended to be run from wp-admin.
		 * Therefore we need to do is_admin() checking for each widget.
		 */
		 if(!is_admin()) { 
			extract($args);
			$options = get_option('widget_wpu_login_user_info');
			$titleLoggedIn = $options['title_logged_in'];
			$titleLoggedOut = $options['title_logged_out'];
			$loginForm = $options['login_form'];
			$rankBlock = $options['rank'];
			$newPosts = $options['new'];
			$write = $options['write'];
			$admin = $options['admin'];
		
			//generate the widget output
			// wpu_template_funcs.php MUST be available!
			if ( !function_exists('wpu_login_user_info') ) return;
			
			$title =  ($phpbbForum->user_logged_in()) ? $titleLoggedIn : $titleLoggedOut;
			$loggedIn = (!empty($user_ID)) ? '1' : '0';
			echo $before_widget . $before_title . $title . $after_title;
			echo '<ul class="wpulogininfo">';
			wpu_login_user_info("showLoginForm={$loginForm}&showRankBlock={$rankBlock}&showNewPosts={$newPosts}&showWriteLink={$write}&showAdminLinks={$admin}");
			echo '</ul>';
			echo $after_widget;
		}
	}

	/**
	 * Widget control pane
	 */
	function widget_wpu_login_user_info_control() {
		global $phpbbForum;
		$options = get_option('widget_wpu_login_user_info');
		
		if ( !is_array($options) ) {
			$options = array('title_logged_in'=> $phpbbForum->lang['wpu_loginbox_loggedin'], 'title_logged_out'=>$phpbbForum->lang['wpu_loginbox_loggedout'], 'rank'=>1, 'new'=>1, 'login_form'=>1);
		}
		// handle form submission
		if ( $_POST['widget_wpu_login_user_info'] ) {
			$options['title_logged_in'] = strip_tags(stripslashes($_POST['wpu-user-info-titIn']));
			$options['title_logged_out'] = strip_tags(stripslashes($_POST['wpu-user-info-titOut']));
			$options['rank'] = strip_tags(stripslashes($_POST['wpu-user-info-rank']));
			$options['new'] = strip_tags(stripslashes($_POST['wpu-user-info-new']));
			$options['write'] = strip_tags(stripslashes($_POST['wpu-user-info-write']));
			$options['admin'] = strip_tags(stripslashes($_POST['wpu-user-info-admin']));
			$options['login_form'] = strip_tags(stripslashes($_POST['wpu-user-info-form']));
			$options['login_form'] = ($options['login_form'] == 'auto')? 1 : 0;
			$options['rank'] = ($options['rank'] == 'rank')? 1 : 0;
			$options['new'] = ($options['new'] == 'new')? 1 : 0;
			$options['write'] = ($options['write'] == 'write')? 1 : 0;
			$options['admin'] = ($options['admin'] == 'admin')? 1 : 0;
			update_option('widget_wpu_login_user_info', $options);
		}

		// set form values
		$titleLoggedIn = htmlspecialchars($options['title_logged_in'], ENT_QUOTES);
		$titleLoggedOut = htmlspecialchars($options['title_logged_out'], ENT_QUOTES);
		$loginForm = (int) $options['login_form'];
		$rank= (int) $options['rank'];
		$new= (int) $options['new'];
		$write= (int) $options['write'];
		$admin= (int) $options['admin'];
		$cbValue = ($loginForm == 1) ? 'checked="checked"' : '';
		$cbRankValue = ($rank == 1) ? 'checked="checked"' : '';
		$cbNewValue = ($new == 1) ? 'checked="checked"' : '';
		$cbWriteValue = ($write == 1) ? 'checked="checked"' : '';
		$cbAdminValue = ($admin == 1) ? 'checked="checked"' : '';
		
		// Show form
		echo '<p style="text-align:right;"><label for="wpu-user-info-titIn">' . $phpbbForum->lang['wpu_loginbox_panel_loggedin'] . ' <input style="width: 200px;" id="wpu-user-info-titIn" name="wpu-user-info-titIn" type="text" value="'.$titleLoggedIn.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-titOut">' .$phpbbForum->lang['wpu_loginbox_panel_loggedout']  . ' <input style="width: 200px;" id="wpu-user-info-titOut" name="wpu-user-info-titOut" type="text" value="'.$titleLoggedOut.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-rank">' . $phpbbForum->lang['wpu_loginbox_panel_rank'] . ' <input  id="wpu-user-info-rank" name="wpu-user-info-rank" type="checkbox" value="rank" ' . $cbRankValue . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-new">' . $phpbbForum->lang['wpu_loginbox_panel_newposts'] . ' <input  id="wpu-user-info-new" name="wpu-user-info-new" type="checkbox" value="new" ' . $cbNewValue . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-write">' . $phpbbForum->lang['wpu_loginbox_panel_write'] . ' <input  id="wpu-user-info-write" name="wpu-user-info-write" type="checkbox" value="write" ' . $cbWriteValue . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-admin">' .$phpbbForum->lang['wpu_loginbox_panel_admin'] . ' <input  id="wpu-user-info-admin" name="wpu-user-info-admin" type="checkbox" value="admin" ' . $cbAdminValue . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-form">' . $phpbbForum->lang['wpu_loginbox_panel_loginform'] . ' <input  id="wpu-user-info-form" name="wpu-user-info-form" type="checkbox" value="auto" ' . $cbValue . ' /></label></p>';

		echo '<input type="hidden" id="widget_wpu_login_user_info" name="widget_wpu_login_user_info" value="1" />';
	}
	
	
	
	
	
	/**
	 * List of latest blogs widget
	 * Returns a lsit of blogs in order of most recently updated
	 */
	function widget_wpulatestblogs($args) {
		if(!is_admin()) {		
			extract($args);

			$options = get_option('widget_wpulatestblogs');
			$title = $options['title'];
			$maxEntries = $options['max'];
		
		
			//generate the widget output
			if ( !function_exists('wpu_latest_blogs') ) return false;
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul>';
			wpu_latest_blogs('limit='.$maxEntries);
			echo '</ul>' . $after_widget;
		}
	}
	
	/**
	 * Widget control pane
	 */
	function widget_wpulatestblogs_control() {
		global $phpbbForum;
		$options = get_option('widget_wpulatestblogs');
		
		if ( !is_array($options) ) {
			$options = array('title'=> $phpbbForum->lang['wpu_bloglist_panel_title'], 'max'=>20);
		}
		// handle form submission
		if ( $_POST['widget_wpu_lb'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['wpu-lb-title']));
			$options['max'] = (int) strip_tags(stripslashes($_POST['wpu-lb-max']));
			update_option('widget_wpulatestblogs', $options);
		}

		// set form values
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$max = htmlspecialchars($options['max'], ENT_QUOTES);
		
		// Show form
		echo '<p style="text-align:right;"><label for="wpu-lb-title">' . $phpbbForum->lang['wpu_panel_heading'] . ' <input style="width: 200px;" id="wpu-lb-title" name="wpu-lb-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-lb-max">' . $phpbbForum->lang['wpu_panel_max_entries'] . ' <input style="width: 50px;" id="wpu-lb-max" name="wpu-lb-max" type="text" value="'.$max.'" /></label></p>';
		
		echo '<input type="hidden" id="widget_wpu_lb" name="widget_wpu_lb" value="1" />';
	}	
	



	/**
	 * List of latest blog posts widget
	 * Returns a lsit of recent posts, in the format XXXXX in YYYYYY. both XXXXX and YYYYYY are links.
	*/
	function widget_wpulatestblogposts($args) {
		if(!is_admin()) {		
			extract($args);

			$options = get_option('widget_wpulatestblogposts');
			$title = $options['title'];
			$maxEntries = $options['max'];
		
		
			//generate the widget output
			// wpu_template_funcs.php MUST be available!
			if ( !function_exists('wpu_latest_blogposts') ) return false;
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul class="wpulatestblogposts">';
			wpu_latest_blogposts('limit='.$maxEntries);
			echo '</ul>' . $after_widget;
		}
	}
	
	/**
	 * The widget control pane
	 */
	function widget_wpulatestblogposts_control() {
		global $phpbbForum;
		$options = get_option('widget_wpulatestblogposts');
		
		if ( !is_array($options) ) {
			$options = array('title'=> $phpbbForum->lang['wpu_blogposts_panel_title'], 'max'=>20);
		}
		// handle form submission
		if ( $_POST['widget_wpu_lbp'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['wpu-lbp-title']));
			$options['max'] = (int) strip_tags(stripslashes($_POST['wpu-lbp-max']));
			update_option('widget_wpulatestblogposts', $options);
		}

		// set form values
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$max = htmlspecialchars($options['max'], ENT_QUOTES);
		
		// Show form
		echo '<p style="text-align:right;"><label for="wpu-lbp-title">' . $phpbbForum->lang['wpu_panel_heading'] . ' <input style="width: 200px;" id="wpu-lbp-title" name="wpu-lbp-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-lbp-max">' . $phpbbForum->lang['wpu_panel_max_entries'] . ' <input style="width: 50px;" id="wpu-lbp-max" name="wpu-lbp-max" type="text" value="'.$max.'" /></label></p>';
		
		echo '<input type="hidden" id="widget_wpu_lbp" name="widget_wpu_lbp" value="1" />';
	}	
	


	/**
	 * Latest phpBB topics widget
	 * Returns a lsit of recent topics, in the format XXXXX posted by YYYYYY in ZZZZZZZ.
	 */
	function widget_wpulatestphpbbtopics($args) {
		if(!is_admin()) {		
			extract($args);

			$options = get_option('widget_wpulatestphpbbtopics');
			$title = $options['title'];
			$maxEntries = $options['max'];
		
		
			//generate the widget output
			// wpu_template_funcs.php MUST be available!
			if ( !function_exists('wpu_latest_phpbb_topics') ) return false;
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul class="wpulatesttopics">';
			wpu_latest_phpbb_topics('limit='.$maxEntries);
			echo '</ul>' . $after_widget;
		}
	}
	

	/** 
	 * The widget control pane
	 */
	function widget_wpulatestphpbbtopics_control() {
		global $phpbbForum;
		$options = get_option('widget_wpulatestphpbbtopics');
		
		if ( !is_array($options) ) {
			$options = array('title'=> $phpbbForum->lang['wpu_forumtopics_panel_title'], 'max'=>20);
		}
		// handle form submission
		if ( $_POST['widget_wpu_rpt'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['wpu-rpt-title']));
			$options['max'] = (int) strip_tags(stripslashes($_POST['wpu-rpt-max']));
			update_option('widget_wpulatestphpbbtopics', $options);
		}

		// set form values
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$max = htmlspecialchars($options['max'], ENT_QUOTES);
		
		// Show form
		echo '<p style="text-align:right;"><label for="wpu-rpt-title">' . $phpbbForum->lang['wpu_panel_heading']  . ' <input style="width: 200px;" id="wpu-rpt-title" name="wpu-rpt-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-rpt-max">' . $phpbbForum->lang['wpu_panel_max_entries'] . ' <input style="width: 50px;" id="wpu-rpt-max" name="wpu-rpt-max" type="text" value="'.$max.'" /></label></p>';
		
		echo '<input type="hidden" id="widget_wpu_rpt" name="widget_wpu_rpt" value="1" />';
	}	
	

	
	/**
	 * phpBB forum statistics widget
	 * Returns a lsit of forum statistics
	 */
	function widget_wpustats($args) {
		if(!is_admin()) {		
			extract($args);

			$options = get_option('widget_wpustats');
			$title = $options['title'];
		
		
			//generate the widget output
			// wpu_template_funcs.php MUST be available!
			if ( !function_exists('wpu_phpbb_stats') ) return false;
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul class="wpuforumstats">';
			wpu_phpbb_stats();
			echo '</ul>' . $after_widget;
		}
	}
	
	/**
	 * The widget control pane
	 */
	function widget_wpustats_control() {
		global $phpbbForum;
		$options = get_option('widget_wpustats');
		
		if ( !is_array($options) ) {
			$options = array('title'=>$phpbbForum->lang['wpu_stats_panel_title']);
		}
		// handle form submission
		if ( $_POST['widget_wpu_stats'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['wpu-stats-title']));
			update_option('widget_wpustats', $options);
		}

		// set form values
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		
		// Show form
		echo '<p style="text-align:right;"><label for="wpu-stats-title">' . $phpbbForum->lang['wpu_panel_heading']  . '</label> <input style="width: 200px;" id="wpu-stats-title" name="wpu-stats-title" type="text" value="'.$title.'" /></p>';
		echo '<input type="hidden" id="widget_wpu_stats" name="widget_wpu_stats" value="1" />';
	}	
	
	/**
	 * Users online widget
	 * Returns a lsit of forum statistics
	 */
	function widget_wpuusersonline($args) {
		if(!is_admin()) {		
			extract($args);

			$options = get_option('widget_wpuusersonline');
			$title = $options['title'];
			$showBreakdown = (int)$options['showBreakdown'];
			$showRecord = (int)$options['showRecord'];
			$showLegend = (int)$options['showLegend'];
		
			//generate the widget output
			// template-tags.php MUST be available!
			if ( !function_exists('wpu_useronlinelist') ) return false;
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul class="wpuusersonline">';
			wpu_useronlinelist("showBreakdown={$showBreakdown}&showRecord={$showRecord}&showLegend={$showLegend}");
			echo '</ul>' . $after_widget;
		}
	}
	
	/**
	 * The widget control pane
	 */
	function widget_wpuusersonline_control() {
		global $phpbbForum;
		$options = get_option('widget_wpuusersonline');
		
		if ( !is_array($options) ) {
			$options = array('title'=>$phpbbForum->lang['wpu_online_panel_title'], 'showBreakdown' => 1, 'showRecord' => 1, 'showLegend' => 1);
		}
		// handle form submission
		if ( $_POST['widget_wpu_usersonline'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['wpu-usersonline-title']));
			$options['showBreakdown'] = strip_tags(stripslashes($_POST['wpu-usersonline-breakdown']));
			$options['showRecord'] = strip_tags(stripslashes($_POST['wpu-usersonline-record']));
			$options['showLegend'] = strip_tags(stripslashes($_POST['wpu-usersonline-legend']));
			$options['showBreakdown'] = ($options['showBreakdown'] == 'brk')? 1 : 0;
			$options['showRecord'] = ($options['showRecord'] == 'rec')? 1 : 0;
			$options['showLegend'] = ($options['showLegend'] == 'leg')? 1 : 0;
			update_option('widget_wpuusersonline', $options);
		}

		// set form values
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$inShowBreakdown = ($options['showBreakdown'] == 1) ? 'checked="checked"' : '';
		$inShowRecord = ($options['showRecord'] == 1) ? 'checked="checked"' : '';
		$inShowLegend = ($options['showLegend'] == 1) ? 'checked="checked"' : '';
		
		// Show form
		echo '<p style="text-align:right;"><label for="wpu-stats-title">' . $phpbbForum->lang['wpu_panel_heading']  . '</label> <input style="width: 200px;" id="wpu-usersonline-title" name="wpu-usersonline-title" type="text" value="'.$title.'" /></p>';
		echo '<p style="text-align:right;"><label for="wpu-usersonline-breakdown">' . $phpbbForum->lang['wpu_online_panel_breakdown'] . ' <input  id="wpu-usersonline-breakdown" name="wpu-usersonline-breakdown" type="checkbox" value="brk" ' . $inShowBreakdown . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-usersonline-record">' . $phpbbForum->lang['wpu_online_panel_record'] . ' <input  id="wpu-usersonline-record" name="wpu-usersonline-record" type="checkbox" value="rec" ' . $inShowRecord . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-usersonline-legend">' . $phpbbForum->lang['wpu_online_panel_legend'] . ' <input  id="wpu-usersonline-legend" name="wpu-usersonline-legend" type="checkbox" value="leg" ' . $inShowLegend . ' /></label></p>';
		echo '<input type="hidden" id="widget_wpu_usersonline" name="widget_wpu_usersonline" value="1" />';
	}	
	
		
	/**
	 * phpBB latest posts
	 * @author Japgalaxy 
	 * Fixed by John Wells for v0.8
	 */
	function widget_wpulatestphpbbposts($args) {
		if(!is_admin()) {
			extract($args);
			
			$options = get_option('widget_wpulatestphpbbposts');
			$title = $options['title'];
			$maxEntries = $options['max'];
			
			if ( !function_exists('wpu_latest_phpbb_posts') ) return false;
			
			echo $before_widget;
			echo $before_title .$title. $after_title;
			echo '<ul class="wpulatestposts">';
			wpu_latest_phpbb_posts("limit={$maxEntries}");
			echo '</ul>';
			echo $after_widget;
		}
	}

	/**
	 * The widget control pane
	 */
	function widget_wpulatestphpbbposts_control() {
		global $phpbbForum;
		$options = get_option('widget_wpulatestphpbbposts');
		
		if ( !is_array($options) ) {
			$options = array('title'=>$phpbbForum->lang['wpu_forumposts_panel_title'], 'limit'=>20);
		}
		// handle form submission
		if ( $_POST['widget_wpu_lpp'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['wpu-lpp-title']));
			$options['max'] = (int) strip_tags(stripslashes($_POST['wpu-lpp-limit']));
			update_option('widget_wpulatestphpbbposts', $options);
		}

		// set form values
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$title = (empty($title)) ? $phpbbForum->lang['wpu_forumposts_panel_title'] : $title;
		$max =(int) htmlspecialchars($options['max'], ENT_QUOTES);
		$max = ($max) ? (string)$max : '20';
		$dateformat = empty($options['dateformat']) ? 'Y-m-j' : $options['dateformat'];
		$dateformat = htmlspecialchars($dateformat);

		// Show form
		echo '<p style="text-align:right;"><label for="wpu-lpp-title">' . $phpbbForum->lang['wpu_panel_heading'] . '</label> <input style="width: 200px;" id="wpu-lpp-title" name="wpu-lpp-title" type="text" value="'.$title.'" /></p>';
		echo '<p style="text-align:right;"><label for="wpu-lpp-limit">' . $phpbbForum->lang['wpu_panel_max_entries'] . '</label> <input style="width: 50px;" id="wpu-lpp-limit" name="wpu-lpp-limit" type="text" value="'.$max.'" /></p>';

		echo '<input type="hidden" id="widget_wpu_lpp" name="widget_wpu_lpp" value="1" />';
	}	



	
	/**
	 * The widgets are all registered here
	 */
	
	global $wpSettings, $phpbbForum;
	
	register_sidebar_widget(array($phpbbForum->lang['wpu_loginbox_desc'], 'widgets'), 'widget_wpu_login_user_info');
	register_sidebar_widget(array($phpbbForum->lang['wpu_forumtopics_desc'], 'widgets'), 'widget_wpulatestphpbbtopics');
	register_sidebar_widget(array($phpbbForum->lang['wpu_stats_desc'], 'widgets'), 'widget_wpustats');
	register_sidebar_widget(array($phpbbForum->lang['wpu_forumposts_desc'], 'widgets'), 'widget_wpulatestphpbbposts');
	register_sidebar_widget(array($phpbbForum->lang['wpu_online_desc'], 'widgets'), 'widget_wpuusersonline');
	if(!empty($wpSettings['usersOwnBlogs'])) {
		register_sidebar_widget(array($phpbbForum->lang['wpu_bloglist_desc'], 'widgets'), 'widget_wpulatestblogs');
		register_sidebar_widget(array($phpbbForum->lang['wpu_blogposts_desc'], 'widgets'), 'widget_wpulatestblogposts');	
	}
	/**
	 * Register all control panes
	 */
	register_widget_control(array($phpbbForum->lang['wpu_loginbox_desc'], 'widgets'), 'widget_wpu_login_user_info_control', 500, 180);
	register_widget_control(array($phpbbForum->lang['wpu_forumtopics_desc'], 'widgets'), 'widget_wpulatestphpbbtopics_control', 300, 100);
	register_widget_control(array($phpbbForum->lang['wpu_stats_desc'], 'widgets'), 'widget_wpustats_control', 300, 100);
	register_widget_control(array($phpbbForum->lang['wpu_forumposts_desc'], 'widgets'), 'widget_wpulatestphpbbposts_control', 300, 100);
	register_widget_control(array($phpbbForum->lang['wpu_online_desc'], 'widgets'), 'widget_wpuusersonline_control', 300, 100);
	if(!empty($wpSettings['usersOwnBlogs'])) {	
		register_widget_control(array($phpbbForum->lang['wpu_bloglist_desc'], 'widgets'), 'widget_wpulatestblogs_control', 300, 100);
		register_widget_control(array($phpbbForum->lang['wpu_blogposts_desc'], 'widgets'), 'widget_wpulatestblogposts_control', 300, 100);
	}
}


?>