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
function wpu_widgets_init_old() {

	// Check for the required plugin functions. 
	if ( !function_exists('register_sidebar_widget') )
		return;
		

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
	 * The widgets are all registered here
	 */
	
	global $wpSettings, $phpbbForum;
	//print_r($phpbbForum->lang);

	register_sidebar_widget(array($phpbbForum->lang['wpu_stats_desc'], 'widgets'), 'widget_wpustats');
	register_sidebar_widget(array($phpbbForum->lang['wpu_online_desc'], 'widgets'), 'widget_wpuusersonline');
	if(!empty($wpSettings['usersOwnBlogs'])) {
		register_sidebar_widget(array($phpbbForum->lang['wpu_bloglist_desc'], 'widgets'), 'widget_wpulatestblogs');
		register_sidebar_widget(array($phpbbForum->lang['wpu_blogposts_desc'], 'widgets'), 'widget_wpulatestblogposts');	
	}
	/**
	 * Register all control panes
	 */
	register_widget_control(array($phpbbForum->lang['wpu_stats_desc'], 'widgets'), 'widget_wpustats_control', 300, 100);
	register_widget_control(array($phpbbForum->lang['wpu_online_desc'], 'widgets'), 'widget_wpuusersonline_control', 300, 100);
	if(!empty($wpSettings['usersOwnBlogs'])) {	
		register_widget_control(array($phpbbForum->lang['wpu_bloglist_desc'], 'widgets'), 'widget_wpulatestblogs_control', 300, 100);
		register_widget_control(array($phpbbForum->lang['wpu_blogposts_desc'], 'widgets'), 'widget_wpulatestblogposts_control', 300, 100);
	}
}


?>
