<?php
/** 
*
* WP-United [English]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.1 2009/05/18 John Wells (Jhong) Exp $
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

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(

	//Moved out from install.xml
	'BLOG' 					=>	'WordPress Blog',
	'VISIT_BLOG'			=>	'Visit User\'s Blog',
	'ACP_WP_UNITED' 		=> 	'WP-United',
	'ACP_WPU_MAINPAGE'		=>	'WP-United Administration',
	'ACP_WPU_CATMAIN'		=> 	'WP-United Admin',
	'ACP_WPU_CATSETUP'		=>	'Set Up WP-United',
	'ACP_WPU_CATMANAGE'		=>	'Manage User Integration',
	'ACP_WPU_CATSUPPORT'	=>	'Support WP-United',
	'ACP_WPU_CATOTHER'		=>	'Other',
	'ACP_WPU_MAINTITLE'		=>	'Main Page',
	'ACP_WPU_DETAILED'		=>	'All Settings On A Page',
	'ACP_WPU_WIZARD'		=> 	'Setup Wizard',
	'ACP_WPU_USERMAP'		=> 	'User Integration Mapping Tool',
	'ACP_WPU_PERMISSIONS'	=> 	'Administer permissions',		
	'ACP_WPU_DONATE'		=> 	'Donate to WP-United',
	'ACP_WPU_UNINSTALL'		=> 	'Uninstall WP-United',
	'ACP_WPU_RESET'			=> 	'Reset WP-United',
	'ACP_WPU_DEBUG'			=>	'Debug Info to Post',	
	'WP_UNINSTALLED' 		=> 	'Uninstalled WP-United',
	'WP_INSTALLED' 			=> 	'Installed WP-United',

	'WP_DBErr_Gen' 			=>	'Could not access WordPress integration configuration in the database. Please ensure that you have installed WP-United properly.',
	'WP_No_Login_Details' 	=>	'Error: A WordPress account could not be generated for you. Please contact an administrator.',
	'WP_DTD' 				=>	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	'Function_Duplicate' 	=>	'A duplicate function name has been detected. This is probably due to a heavily modded board. Please visit www.wp-united.com to report the error.',
	'WP_Not_Installed_Yet' 	=>	'WP-United has not yet been set up properly on your site. Please run the Setup Wizard located in the phpBB Admin Control Panel.',
	'WPU_Credit' 			=>	'Integration by %sWP-United%s',
	'get_blog' 				=>	'Create Your Blog',
	'add_to_blog' 			=>	'Add to Your Blog',
	'go_wp_admin'			=>	'Go to Admin Panel',
	'blog_intro_get' 		=>	"%sClick Here%s to get started with your blog today!",
	'blog_intro_add' 		=>	"%sClick Here%s to add to your blog.",
	'blog_intro_loginreg_ownblogs' 	=>	"%sRegister%s or %sLogin%s to get started with your blog.",
	'blog_intro_loginreg' 	=>	"%sRegister%s or %sLogin%s to participate",
	'Latest_Blog_Posts' 	=>	'Latest Blog Posts',
	'Article_By' 			=>	'by',
	'WP_Category' 			=>	'Category',
	'WP_Posted_On' 			=>	'Posted on',
	'default_blogname' 		=>	'My Blog',
	'default_blogdesc' 		=>	'I need to provide a blog tagline...',
	'Log_me_in' 			=>	'Remember me',
	'submit'				=>	'Submit',
	'Search_new'			=> 'Posts since last visit',
	'blog_title_prefix'	=>	'[BLOG]: ',
	'blog_post_intro'		=>	'This is a [b]blog post[/b]. To read the original post, please %1sclick here &raquo;%2s',
	'read_more'				=> '%1sRead this blog post &raquo;%2s',
	'blog_post_cats'		=>	'Posted under: ',
	'blog_post_tags'		=>	'Tags: ',
	'write_post'			=>	'Write Post',
	'admin_site'			=>	'Admin Site',
	'admin_forum'			=>	'Admin Forum',
	'newest_user'			=>	'Newest User: ',
	'registered_users'		=>	'Registered Users: ',
	'forum_posts'			=>	'Forum Posts: ',
	'forum_topics'			=>	'Forum Topics: ',
	'wpu_dash_copy'	=> 'phpbb integration &copy; 2006-2010 %1sWP-United%2s',
	'wpu_welcome'		=>	'Welcome to WP-United.',
	'wpu_write_blog_pre250'	=>	'Click %1sWrite%2s to add to your blog',
	'wpu_write_blog'	=>	'Click %1sPosts%2sAdd New%3s to add to your blog',
	'wpu_blog_intro_appearance' => 'You can set how it looks under the %1sYour Blog%2s tab.',
	'wpu_blog_panel_heading' => 'Your Blog',
	'wpu_blog_settings' => 'Your Blog Settings',
	'wpu_blog_theme'	=> 'Set Blog Theme',
	'wpu_blog_your_theme' => 'Set Your Blog Theme',
	'wpu_blog_details'	=> 'Your Blog Details',
	'wpu_blog_about' => 'About Your Blog',
	'wpu_blog_about_title' => 'The Title of Your Blog:',
	'wpu_blog_about_tagline' => 'Blog Tagline:',
	'wpu_theme_broken' => 'The theme you selected for your blog is broken.  Reverting to the default theme.',
	'wpu_theme_activated' => 'New theme activated. %1sView your blog &raquo;%2s',
	'wpu_more_themes_head' => 'Want More Themes?',
	'wpu_more_themes_get' => 'If you have found another WordPress theme that you would like to use, please inform an administrator.',
	'wpu_user_media_dir_error' => 'Unable to create directory %s. This is probably because its parent directory is not writable. Please tell an administrator.',
	'wpu_access_error' => 'You should not be here',
	'wpu_no_access' => 'No access',
	'wpu_xpost_box_title' => 'Cross-post to Forums?',
	'wpu_already_xposted' => 'Already cross-posted (Topic ID = %s)',
	'wpu_forcexpost_box_title' => 'Forum Posting',
	'wpu_forcexpost_details' => 'This post will be cross-posted to the forum: \'%s\'',
	'wpu_user_edit_use_phpbb' => '<strong>NOTE:</strong> Most of this user\'s  information can be administered using the forum. %1sClick here to view/edit%2s.',
	'wpu_profile_edit_use_phpbb' => '<strong>NOTE:</strong> Most of your  information can be edited in your forum profile. %1sClick here to view/edit%2s.',
	'wpu_userpanel_use_phpbb' => '<strong>NOTE:</strong> Integrated users\'  information can and should be edited using the forum using the links below.',
	'wpu_more_smilies' => 'More smilies',
	'wpu_less_smilies' => 'Less smilies',
	'wpu_blog_intro' => '%1s, by %2s',
	'wpu_total_entries' => 'Total Entries: ',
	'wpu_last_entry' => 'Last Entry: %1s, posted on %2s',
	'wpu_rss_feed' => 'RSS Feed: ',
	'wpu_rss_subscribe' => 'Subscribe',
	'wpu_no_user_blogs' => 'There are no user blogs to show',
	'wpu_latest_blogposts_format' => '%1s, in %2s',
	'wpu_forum_stats_posts' => 'Forum Posts: %s',
	'wpu_forum_stats_threads' => 'Forum Threads: %s',
	'wpu_forum_stats_users' => 'Registered Users: %s',
	'wpu_forum_stats_newest_user' => 'Newest User: %s',
	'wpu_nothing' => 'Nothing found.',
	'wpu_phpbb_post_summary' => '%1s, posted by %2s at %3s',
	'wpu_phpbb_topic_summary' => '%1s, posted by %2s in %3s',
	'wpu_write_post' => 'Write a Post',
	'wpu_loginbox_desc' => 'WP-United Login/User Info',
	'wpu_forumtopics_desc' => 'WP-United Latest phpBB Topics',
	'wpu_stats_desc' => 'WP-United Forum Statistics',
	'wpu_forumposts_desc' => 'WP-United Latest phpBB Posts',
	'wpu_online_desc' => 'WP-United Users Online',
	'wpu_bloglist_desc' => 'WP-United Recently Updated Blogs List',
	'wpu_blogposts_desc' => 'WP-United Recent Posts in Blogs',
	'wpu_loginbox_loggedin' => 'You are logged in as:',
	'wpu_loginbox_loggedout' => 'You are not logged in.',
	'wpu_loginbox_panel_loggedin' => 'Heading to show when reader is logged in:',
	'wpu_loginbox_panel_loggedout' =>'Heading to show when reader is not logged in:',
	'wpu_loginbox_panel_rank' => 'Show rank title & image?',
	'wpu_loginbox_panel_newposts' => 'Show new posts?',
	'wpu_loginbox_panel_write' => 'Show Write Post link?',
	'wpu_loginbox_panel_admin' => 'Show Admin link?',
	'wpu_loginbox_panel_loginform' => 'Show phpBB login form if logged out?',
	'wpu_bloglist_panel_title' => 'Recently Updated Blogs',
	'wpu_panel_heading' => 'Heading:',
	'wpu_panel_max_entries' => 'Maximum Entries:',
	'wpu_blogposts_panel_title' => 'Recent Posts in Blogs',
	'wpu_forumtopics_panel_title' => 'Recent Forum Topics',
	'wpu_forumposts_panel_title' => 'Recent Forum Posts',
	'wpu_forumposts_panel_date' => 'Date format:',
	'wpu_stats_panel_title' => 'Forum Stats',
	'wpu_online_panel_title' => 'Users Online',
	'wpu_online_panel_breakdown' => 'Show a breakdown of user types?',
	'wpu_online_panel_record' => 'Show record number of users?',
	'wpu_online_panel_legend' => 'Show legend?',
	'edit_phpbb_details' => 'Edit forum details'
	

));

?>
