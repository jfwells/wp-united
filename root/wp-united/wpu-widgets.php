<?php
/** 
*
* WP-United WordPress Widgets
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v0.6.0[phpBB3] 2007/07/15 John Wells (Jhong) Exp $
* @copyright (c) 2006, 2007 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
//
// WordPress template functions rolled into widgets
//

// this file will also be called in wp admin panel, when phpBB is not loaded. 
if ( !defined('ABSPATH') && !defined('IN_PHPBB') ) {
	die('Hacking attempt!');
}

function wpu_widgets_init() {

	// Check for the required plugin functions. 
	if ( !function_exists('register_sidebar_widget') )
		return;

		
		
	// Aaaaand... the widgets begin.....	
		
		
	/*******************************************************************************************
	// 
	//	READER INFO BLOCK
	//	-----------------------------
	//
	//	Returns a nice block containing info about the phpBB user that is currently logged in *to phpBB*
	/*******************************************************************************************/

	function widget_readerdetails($args) {
		
		extract($args);
		$options = get_option('widget_readerdetails');
		$titleLoggedIn = $options['title_logged_in'];
		$titleLoggedOut = $options['title_logged_out'];
		$loginForm = $options['login_form'];
		$rankBlock = $options['rank'];
		$newPosts = $options['new'];
		
		//generate the widget output
		// wpu_template_funcs.php MUST be available!
		echo $before_widget . '<div id="loginbox">';
		$wpu_usr = get_wpu_phpbb_username(); 
		if ( 'Guest' != $wpu_usr ) { 
			echo $before_title . $titleLoggedIn . $after_title;
			echo '<p><strong>' . $wpu_usr . '</strong></p>';
			if ( $rankBlock ) {
				wpu_phpbb_rankblock();
			}
			echo '<img src="' . get_avatar_reader() . '" alt="' . __(avatar) . '" />'; 
			if ( $newPosts ) {
				echo '<p>'; wpu_newposts_link(); echo '</p> ';
			}
			echo '<p>'; wp_loginout(); echo '</p> ';
		} else {
			echo $before_title . $titleLoggedOut . $after_title;
			if ( $loginForm ) {
				global $scriptPath, $phpEx, $wpuAbs, $phpbb_sid, $wpSettings;
				$login_link = ($wpuAbs->ver == 'PHPBB2') ? 'login.'.$phpEx.'?redirect=wp-united-blog&amp;sid='. $phpbb_sid : 'ucp.'.$phpEx.'?mode=login&amp;sid=' . $phpbb_sid . '&amp;redirect=' . attribute_escape($_SERVER["REQUEST_URI"]);
				echo '<form method="post" action="' . add_trailing_slash($scriptPath) . $login_link . '">';
				echo '<p><label for="phpbb_username">' . $wpuAbs->lang('Username') . ': <input style="width: 90px;" type="text" name="username" id="phpbb_username"/></label></p>';
				echo '<p><label for="phpbb_password">' . $wpuAbs->lang('Password') . ': <input style="width: 90px;" type="password" name="password" id="phpbb_password" maxlength="32" /></label></p>';
				if ( $wpuAbs->config('allow_autologin') ) {
					echo '<p><label for="phpbb_autologin">' . $wpuAbs->lang('Log_me_in') . ': <input type="checkbox" id="phpbb_autologin" name="autologin" /></label></p>';
				}
					echo '<input type="submit" name="login" value="' . $wpuAbs->lang('submit') . '" />';
				echo '</form>';
			} else {
				echo '<p>'; wp_loginout(); echo '</p> ';
			}
		}			
		echo '</div>' . $after_widget;
	}

	//The widget control pane:	
	function widget_readerdetails_control() {
	
		$options = get_option('widget_readerdetails');
		
		if ( !is_array($options) ) {
			$options = array('title_logged_in'=>__('You are logged in as:'), 'title_logged_out'=>__('You are not logged in.'), 'rank'=>1, 'new'=>1, 'login_form'=>1);
		}
		// handle form submission
		if ( $_POST['widget_readerdetails'] ) {
			$options['title_logged_in'] = strip_tags(stripslashes($_POST['wpu-readerinfo-titIn']));
			$options['title_logged_out'] = strip_tags(stripslashes($_POST['wpu-readerinfo-titOut']));
			$options['rank'] = strip_tags(stripslashes($_POST['wpu-readerinfo-rank']));
			$options['new'] = strip_tags(stripslashes($_POST['wpu-readerinfo-new']));
			$options['login_form'] = strip_tags(stripslashes($_POST['wpu-readerinfo-form']));
			$options['login_form'] = ($options['login_form'] == 'auto')? 1 : 0;
			$options['rank'] = ($options['rank'] == 'rank')? 1 : 0;
			$options['new'] = ($options['new'] == 'new')? 1 : 0;
			update_option('widget_readerdetails', $options);
		}

		// set form values
		$titleLoggedIn = htmlspecialchars($options['title_logged_in'], ENT_QUOTES);
		$titleLoggedOut = htmlspecialchars($options['title_logged_out'], ENT_QUOTES);
		$loginForm = (int) $options['login_form'];
		$rank= (int) $options['rank'];
		$new= (int) $options['new'];
		$cbValue = ($loginForm == 1) ? 'checked="checked"' : '';
		$cbRankValue = ($rank == 1) ? 'checked="checked"' : '';
		$cbNewValue = ($new == 1) ? 'checked="checked"' : '';
		
		// Show form
		echo '<p style="text-align:right;"><label for="wpu-readerinfo-titIn">' . __('Heading to show when reader is logged in:') . ' <input style="width: 200px;" id="wpu-readerinfo-titIn" name="wpu-readerinfo-titIn" type="text" value="'.$titleLoggedIn.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-readerinfo-titOut">' . __('Heading to show when reader is not logged in:') . ' <input style="width: 200px;" id="wpu-readerinfo-titOut" name="wpu-readerinfo-titOut" type="text" value="'.$titleLoggedOut.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-readerinfo-rank">' . __('Show rank title & image?') . ' <input  id="wpu-readerinfo-rank" name="wpu-readerinfo-rank" type="checkbox" value="rank" ' . $cbRankValue . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-readerinfo-new">' . __('Show new posts?') . ' <input  id="wpu-readerinfo-new" name="wpu-readerinfo-new" type="checkbox" value="new" ' . $cbNewValue . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-readerinfo-form">' . __('Show phpBB login form if logged out?') . ' <input  id="wpu-readerinfo-form" name="wpu-readerinfo-form" type="checkbox" value="auto" ' . $cbValue . ' /></label></p>';

		echo '<input type="hidden" id="widget_readerdetails" name="widget_readerdetails" value="1" />';
	}
	
	
	
	
	
	/*******************************************************************************************
	// 
	//	LIST OF LATEST BLOGS
	//	-----------------------------
	//
	//	Returns a lsit of blogs in order of most recently updated
	/*******************************************************************************************/
	
	function widget_wpulatestblogs($args) {
		
		extract($args);

		$options = get_option('widget_wpulatestblogs');
		$title = $options['title'];
		$maxEntries = $options['max'];
		
		
		//generate the widget output
		// wpu_template_funcs.php MUST be available!
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul>';
		wpu_latest_blogs('limit='.$maxEntries);
		echo '</ul>' . $after_widget;
	}
	
	//The widget control pane:	
	function widget_wpulatestblogs_control() {
	
		$options = get_option('widget_wpulatestblogs');
		
		if ( !is_array($options) ) {
			$options = array('title'=>__('Recently Updated Blogs'), 'max'=>20);
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
		echo '<p style="text-align:right;"><label for="wpu-lb-title">' . __('Heading:') . ' <input style="width: 200px;" id="wpu-lb-title" name="wpu-lb-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-lb-max">' . __('Maximum Entries:') . ' <input style="width: 50px;" id="wpu-lb-max" name="wpu-lb-max" type="text" value="'.$max.'" /></label></p>';
		
		echo '<input type="hidden" id="widget_wpu_lb" name="widget_wpu_lb" value="1" />';
	}	
	



	/*******************************************************************************************
	// 
	//	LIST OF LATEST POSTS IN BLOGS
	//	-----------------------------
	//
	//	Returns a lsit of recent posts, in the format XXXXX in YYYYYY. both XXXXX and YYYYYY are links.
	/*******************************************************************************************/
	
	function widget_wpulatestblogposts($args) {
		
		extract($args);

		$options = get_option('widget_wpulatestblogposts');
		$title = $options['title'];
		$maxEntries = $options['max'];
		
		
		//generate the widget output
		// wpu_template_funcs.php MUST be available!
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul>';
		wpu_latest_blogposts('limit='.$maxEntries);
		echo '</ul>' . $after_widget;
	}
	
	//The widget control pane:	
	function widget_wpulatestblogposts_control() {
	
		$options = get_option('widget_wpulatestblogposts');
		
		if ( !is_array($options) ) {
			$options = array('title'=>__('Recent Posts in Blogs'), 'max'=>20);
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
		echo '<p style="text-align:right;"><label for="wpu-lbp-title">' . __('Heading:') . ' <input style="width: 200px;" id="wpu-lbp-title" name="wpu-lbp-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-lbp-max">' . __('Maximum Entries:') . ' <input style="width: 50px;" id="wpu-lbp-max" name="wpu-lbp-max" type="text" value="'.$max.'" /></label></p>';
		
		echo '<input type="hidden" id="widget_wpu_lbp" name="widget_wpu_lbp" value="1" />';
	}	
	


	/*******************************************************************************************
	// 
	//	LATEST PHPBB TOPICS
	//	-----------------------------
	//
	//	Returns a lsit of recent topics, in the format XXXXX posted by YYYYYY in ZZZZZZZ.
	/*******************************************************************************************/
	
	function widget_wpulatestphpbbtopics($args) {
		
		extract($args);

		$options = get_option('widget_wpulatestphpbbtopics');
		$title = $options['title'];
		$maxEntries = $options['max'];
		
		
		//generate the widget output
		// wpu_template_funcs.php MUST be available!
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul>';
		wpu_latest_phpbb_topics('limit='.$maxEntries);
		echo '</ul>' . $after_widget;
	}
	
	//The widget control pane:	
	function widget_wpulatestphpbbtopics_control() {
	
		$options = get_option('widget_wpulatestphpbbtopics');
		
		if ( !is_array($options) ) {
			$options = array('title'=>__('Recent Forum Topics'), 'max'=>20);
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
		echo '<p style="text-align:right;"><label for="wpu-rpt-title">' . __('Heading:') . ' <input style="width: 200px;" id="wpu-rpt-title" name="wpu-rpt-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-rpt-max">' . __('Maximum Entries:') . ' <input style="width: 50px;" id="wpu-rpt-max" name="wpu-rpt-max" type="text" value="'.$max.'" /></label></p>';
		
		echo '<input type="hidden" id="widget_wpu_rpt" name="widget_wpu_rpt" value="1" />';
	}	
	
	
	
	
	/*******************************************************************************************
	// 
	//	PHPBB FORUM STATISTICS
	//	--------------------------------------
	//
	//	Returns a lsit of forum statistics
	/*******************************************************************************************/
	
	function widget_wpustats($args) {
		
		extract($args);

		$options = get_option('widget_wpustats');
		$title = $options['title'];
		
		
		//generate the widget output
		// wpu_template_funcs.php MUST be available!
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul>';
		wpu_phpbb_stats();
		echo '</ul>' . $after_widget;
	}
	
	//The widget control pane:	
	function widget_wpustats_control() {
	
		$options = get_option('widget_wpustats');
		
		if ( !is_array($options) ) {
			$options = array('title'=>__('Forum Stats'));
		}
		// handle form submission
		if ( $_POST['widget_wpu_stats'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['wpu-stats-title']));
			update_option('widget_wpustats', $options);
		}

		// set form values
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		
		// Show form
		echo '<p style="text-align:right;"><label for="wpu-stats-title">' . __('Heading:') . ' <input style="width: 200px;" id="wpu-stats-title" name="wpu-stats-title" type="text" value="'.$title.'" /></label></p>';
	
		
		echo '<input type="hidden" id="widget_wpu_stats" name="widget_wpu_stats" value="1" />';
	}	
	
		

	/************************************************************************************************************************************/
	
	//register our widgets
	register_sidebar_widget(array('WP-United Reader Info Block', 'widgets'), 'widget_readerdetails');
	register_sidebar_widget(array('WP-United Recently Updated Blogs List', 'widgets'), 'widget_wpulatestblogs');
	register_sidebar_widget(array('WP-United Recent Posts in Blogs', 'widgets'), 'widget_wpulatestblogposts');
	register_sidebar_widget(array('WP-United Latest phpBB Topics', 'widgets'), 'widget_wpulatestphpbbtopics');
	register_sidebar_widget(array('WP-United Forum Statistics', 'widgets'), 'widget_wpustats');

	// register our widget control panes, specifying size of pane
	register_widget_control(array('WP-United Reader Info Block', 'widgets'), 'widget_readerdetails_control', 500, 180);
	register_widget_control(array('WP-United Recently Updated Blogs List', 'widgets'), 'widget_wpulatestblogs_control', 300, 100);
	register_widget_control(array('WP-United Recent Posts in Blogs', 'widgets'), 'widget_wpulatestblogposts_control', 300, 100);
	register_widget_control(array('WP-United Latest phpBB Topics', 'widgets'), 'widget_wpulatestphpbbtopics_control', 300, 100);
	register_widget_control(array('WP-United Forum Statistics', 'widgets'), 'widget_wpustats_control', 300, 100);
}


?>
