<?php
/** 
*
* WP-United WordPress Widgets
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.7.1[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* @author John Wells
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

/**
 * Wrapper function for initialising widgets
 */
function wpu_widgets_init() {

	// Check for the required plugin functions. 
	if ( !function_exists('register_sidebar_widget') )
		return;

		
		
	// Aaaaand... the widgets begin.....	
		
		
	/**
	 * Returns a nice block containing info about the phpBB user that is currently logged in *to phpBB*
	 */
	function widget_wpu_login_user_info($args) {
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
			$position = 'sidebar';
		
			//generate the widget output
			// wpu_template_funcs.php MUST be available!
			if ( !function_exists('wpu_login_user_info') ) return;
			echo $before_widget;
			wpu_login_user_info($titleLoggedIn, $titleLoggedOut, $loginForm, $rankBlock, $newPosts, $write, $admin, $position, $before_title, $after_title);
			echo $after_widget;
		}
	}

	/**
	 * Widget control pane
	 */
	function widget_wpu_login_user_info_control() {
	
		$options = get_option('widget_wpu_login_user_info');
		
		if ( !is_array($options) ) {
			$options = array('title_logged_in'=>__('You are logged in as:'), 'title_logged_out'=>__('You are not logged in.'), 'rank'=>1, 'new'=>1, 'login_form'=>1);
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
		echo '<p style="text-align:right;"><label for="wpu-user-info-titIn">' . __('Heading to show when reader is logged in:') . ' <input style="width: 200px;" id="wpu-user-info-titIn" name="wpu-user-info-titIn" type="text" value="'.$titleLoggedIn.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-titOut">' . __('Heading to show when reader is not logged in:') . ' <input style="width: 200px;" id="wpu-user-info-titOut" name="wpu-user-info-titOut" type="text" value="'.$titleLoggedOut.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-rank">' . __('Show rank title & image?') . ' <input  id="wpu-user-info-rank" name="wpu-user-info-rank" type="checkbox" value="rank" ' . $cbRankValue . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-new">' . __('Show new posts?') . ' <input  id="wpu-user-info-new" name="wpu-user-info-new" type="checkbox" value="new" ' . $cbNewValue . ' /></label></p>';
		//
		echo '<p style="text-align:right;"><label for="wpu-user-info-write">' . __('Show Write Post link?') . ' <input  id="wpu-user-info-write" name="wpu-user-info-write" type="checkbox" value="write" ' . $cbWriteValue . ' /></label></p>';
		echo '<p style="text-align:right;"><label for="wpu-user-info-admin">' . __('Show Admin link?') . ' <input  id="wpu-user-info-admin" name="wpu-user-info-admin" type="checkbox" value="admin" ' . $cbAdminValue . ' /></label></p>';
		//
		echo '<p style="text-align:right;"><label for="wpu-user-info-form">' . __('Show phpBB login form if logged out?') . ' <input  id="wpu-user-info-form" name="wpu-user-info-form" type="checkbox" value="auto" ' . $cbValue . ' /></label></p>';

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
			// wpu_template_funcs.php MUST be available!
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
			echo '<ul>';
			wpu_latest_blogposts('limit='.$maxEntries);
			echo '</ul>' . $after_widget;
		}
	}
	
	/**
	 * The widget control pane
	 */
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
			echo '<ul>';
			wpu_latest_phpbb_topics('limit='.$maxEntries);
			echo '</ul>' . $after_widget;
		}
	}
	
	/** 
	 * The widget control pane
	 */
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
			echo '<ul>';
			wpu_phpbb_stats();
			echo '</ul>' . $after_widget;
		}
	}
	
	/**
	 * The widget control pane
	 */
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
		echo '<p style="text-align:right;"><label for="wpu-stats-title">' . __('Heading:') . '</label> <input style="width: 200px;" id="wpu-stats-title" name="wpu-stats-title" type="text" value="'.$title.'" /></p>';
	
		
		echo '<input type="hidden" id="widget_wpu_stats" name="widget_wpu_stats" value="1" />';
	}	
	
		

	/**
	 * phpBB latest posts
	 * @author Japgalaxy
	 */
	function widget_wpulatestphpbbposts($args) {
		if(!is_admin()) {
			extract($args);
			
			$options = get_option('widget_wpulatestphpbbposts');
			$title = $options['title'];
		
			$before = $options['before'];
			$after = $options['after'];
			$gtm = $options['gtm'];
			$seo = $options['seo'];
			$limit = $options['limit'];
		
			if ( !function_exists('wpu_latest_phpbb_posts') ) return;
			echo $before_widget;
			echo '<h2>'.$title.'</h2>';
			if ( ($before=="<li>") && ($after=="</li>") ) {
				$prev = "<ul>";
				$next = "</ul>";
			}
		
			echo $prev;
			wpu_latest_phpbb_posts($before, $after, $gtm, $limit, $seo);
			echo $next;
			echo $after_widget;
		}
	}


	/**
	 * The widget control pane
	 */
	function widget_wpulatestphpbbposts_control() {
	
		$options = get_option('widget_wpulatestphpbbposts');
		
		if ( !is_array($options) ) {
			$options = array('title'=>__('Recent Forum Posts'), 'limit'=>20, 'gtm'=>"Y-m-j", 'before'=>"<li>", 'after'=>"</li>", 'seo'=>"No");
		}
		// handle form submission
		if ( $_POST['widget_wpu_lpp'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['wpu-lpp-title']));
			$options['limit'] = (int) strip_tags(stripslashes($_POST['wpu-lpp-limit']));
			$options['before'] = $_POST['wpu-lpp-before'];
			$options['after'] = $_POST['wpu-lpp-after'];
			$options['gtm'] = $_POST['wpu-lpp-gtm'];
			$options['seo'] = $_POST['wpu-lpp-seo'];
			update_option('widget_wpulatestphpbbposts', $options);
		}

		// set form values
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$max = htmlspecialchars($options['max'], ENT_QUOTES);
		$before = $options['before'];
		$after = $options['after'];
		$gtm = $options['gtm'];
		$seo = $options['seo'];
		$limit = $options['limit'];

		// Show form
		echo '<p style="text-align:right;"><label for="wpu-lpp-title">' . __('Heading:') . '</label> <input style="width: 200px;" id="wpu-lpp-title" name="wpu-lpp-title" type="text" value="'.$title.'" /></p>';
		echo '<p style="text-align:right;"><label for="wpu-lpp-limit">' . __('Maximum Entries:') . '</label> <input style="width: 50px;" id="wpu-lpp-limit" name="wpu-lpp-limit" type="text" value="'.$limit.'" /></p>';
		echo '<p style="text-align:right;"><label for="wpu-lpp-before">' . __('Before:') . '</label> <input style="width: 60px;" id="wpu-lpp-before" name="wpu-lpp-before" type="text" value="'.$before.'" /></p>';
		echo '<p style="text-align:right;"><label for="wpu-lpp-after">' . __('After:') . '</label> <input style="width: 60px;" id="wpu-lpp-after" name="wpu-lpp-after" type="text" value="'.$after.'" /></p>';
		echo '<p style="text-align:right;"><label for="wpu-lpp-gtm">' . __('GTM Data format:') . '</label> <input style="width: 90px;" id="wpu-lpp-gtm" name="wpu-lpp-gtm" type="text" value="'.$gtm.'" /></p>';
		echo '<p style="text-align:right;"><label for="wpu-lpp-seo">' . __('phpBB SEO installed?:') . '</label> <input style="width: 90px;" id="wpu-lpp-seo" name="wpu-lpp-seo" type="text" value="'.$seo.'" /></p>';

		echo '<input type="hidden" id="widget_wpu_lpp" name="widget_wpu_lpp" value="1" />';
	}	

	
	/**
	 * The widgets are all registered here
	 */
	register_sidebar_widget(array('WP-United Login/User Info', 'widgets'), 'widget_wpu_login_user_info');
	register_sidebar_widget(array('WP-United Recently Updated Blogs List', 'widgets'), 'widget_wpulatestblogs');
	register_sidebar_widget(array('WP-United Recent Posts in Blogs', 'widgets'), 'widget_wpulatestblogposts');
	register_sidebar_widget(array('WP-United Latest phpBB Topics', 'widgets'), 'widget_wpulatestphpbbtopics');
	register_sidebar_widget(array('WP-United Forum Statistics', 'widgets'), 'widget_wpustats');
	register_sidebar_widget(array('WP-United Latest phpBB Posts', 'widgets'), 'widget_wpulatestphpbbposts');

	/**
	 * Register all control panes
	 */
	register_widget_control(array('WP-United Login/User Info', 'widgets'), 'widget_wpu_login_user_info_control', 500, 180);
	register_widget_control(array('WP-United Recently Updated Blogs List', 'widgets'), 'widget_wpulatestblogs_control', 300, 100);
	register_widget_control(array('WP-United Recent Posts in Blogs', 'widgets'), 'widget_wpulatestblogposts_control', 300, 100);
	register_widget_control(array('WP-United Latest phpBB Topics', 'widgets'), 'widget_wpulatestphpbbtopics_control', 300, 100);
	register_widget_control(array('WP-United Forum Statistics', 'widgets'), 'widget_wpustats_control', 300, 100);
	register_widget_control(array('WP-United Latest phpBB Posts', 'widgets'), 'widget_wpulatestphpbbposts_control', 300, 100);
}


?>
