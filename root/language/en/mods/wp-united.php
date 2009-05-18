<?php
/** 
*
* WP-United [English]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.6.5 2009/05/18 John Wells (Jhong) Exp $
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
'Blog' 					=>	'WordPress Blog',
'WP_DBErr_Gen' 			=>	'Could not access WordPress integration configuration table in the database. Please ensure that you ran the SQL query included when installing the integration mod, and then configured the mod via the admin control panel.',
'WP_No_Login_Details' 	=>	'Error: A WordPress account could not be generated for you. Please contact an administrator.',
'WP_DTD' 				=>	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
'Function_Duplicate' 	=>	'A duplicate function name has been detected. This is probably due to a heavily modded board. Please visit www.wp-united.com to report the error.',
'WP_Not_Installed_Yet' 	=>	'The WP-United WordPress Integration Mod has not yet been set up properly on your site. Please run the Setup Wizard located in the phpBB Admin Control Panel.',
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
));

?>
