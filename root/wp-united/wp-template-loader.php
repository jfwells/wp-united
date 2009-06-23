<?php
/** 
*
* WP-United WordPress template loader replacement
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.7.1[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
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
//
// Loads up the WP template. loose copy of WP's template-loader function, modified to work with functions exported to phpBB.
//
//

if ( !defined('IN_PHPBB') )exit;


global $wpuNoHead, $wpSettings, $wp_version;

if ( ((float) $wp_version) >= 2.1 ) {

	//WP 2.1 && 2.2 BRANCH
	if ( defined('WP_USE_THEMES') && constant('WP_USE_THEMES') ) {
		do_action('template_redirect');
		if ( is_robots() ) {
			$wpuNoHead = true;
			do_action('do_robots');
		} else if ( is_feed() ) {
			$wpuNoHead = true;
			do_feed();
		} else if ( is_trackback() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-trackback.php');
		} else if ( is_404() && $wp_template = get_404_template() ) {
			include($wp_template);
		} else if ( is_search() && $wp_template = get_search_template() ) {
			include($wp_template);
		} else if ( is_home() && $wp_template = get_home_template() ) {
			include($wp_template); 
		} else if ( is_attachment() && $wp_template = get_attachment_template() ) {
			include($wp_template);
		} else if ( is_single() && $wp_template = get_single_template() ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include($wp_template);
		} else if ( is_page() && $wp_template = get_page_template() ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include($wp_template);
		} else if ( is_category() && $wp_template = get_category_template()) {
			include($wp_template);
		} else if ( is_author() && (!empty($wpSettings['usersOwnBlogs'])) && ($wp_template = get_home_template()) ) {
			include($wp_template);
		} else if ( is_author() && $wp_template = get_author_template() ) {	
			include($wp_template);		
		} else if ( is_date() && $wp_template = get_date_template() ) {
			include($wp_template);
		} else if ( is_archive() && $wp_template = get_archive_template() ) {
			include($wp_template);
		} else if ( is_comments_popup() && $wp_template = get_comments_popup_template() ) {
			include($wp_template);
		} else if ( is_paged() && $wp_template = get_paged_template() ) {
			include($wp_template);
		} else if ( file_exists(TEMPLATEPATH . "/index.php") ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include(TEMPLATEPATH . "/index.php");
		}
	} else {
		// Process feeds and trackbacks even if not using themes.
		if ( is_robots() ) {
			$wpuNoHead = true;
			do_action('do_robots');
		} else if ( is_feed() ) {
			$wpuNoHead = true;
			do_feed();
		} else if ( is_trackback() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-trackback.php');
		}
	}

	
} else {

	// WP 2.0x BRANCH!
	if ( defined('WP_USE_THEMES') && constant('WP_USE_THEMES') ) {
		do_action('template_redirect');
		if ( is_feed() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-feed.php');
		} else if ( is_trackback() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-trackback.php');
		} else if ( is_404() && $wp_template = get_404_template() ) {
			include($wp_template);
		} else if ( is_search() && $wp_template = get_search_template() ) {
			include($wp_template);
		} else if ( is_home() && $wp_template = get_home_template() ) {
			include($wp_template);
		} else if ( is_attachment() && $wp_template = get_attachment_template() ) {
			include($wp_template);
		} else if ( is_single() && $wp_template = get_single_template() ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include($wp_template);
		} else if ( is_page() && $wp_template = get_page_template() ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include($wp_template);
		} else if ( is_category() && $wp_template = get_category_template()) {
			include($wp_template);
		} else if ( is_author() && (!empty($wpSettings['usersOwnBlogs'])) && ($wp_template = get_home_template()) ) {
			include($wp_template);
		} else if ( is_author() && $wp_template = get_author_template() ) {	
			include($wp_template);
		} else if ( is_date() && $wp_template = get_date_template() ) {
			include($wp_template);
		} else if ( is_archive() && $wp_template = get_archive_template() ) {
			include($wp_template);
		} else if ( is_comments_popup() && $wp_template = get_comments_popup_template() ) {
			include($wp_template);
		} else if ( is_paged() && $wp_template = get_paged_template() ) {
			include($wp_template);
		} else if ( file_exists(TEMPLATEPATH . "/index.php") ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include(TEMPLATEPATH . "/index.php");
		}
	
	} else {
		// Process feeds and trackbacks even if not using themes.
		if ( is_feed() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-feed.php');
		} else if ( is_trackback() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-trackback.php');
		}
	}
}


 
?>
