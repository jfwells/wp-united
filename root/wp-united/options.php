<?php
/** 
*
* WP-United Extra Options
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
//	YOU MAY SET THE ADVANCED OPTIONS IN THIS FILE... PLEASE READ THROUGH THE COMMENTS THAT PRECEDE EACH OPTION BEFORE CHANGING ANY.
//	MORE OPTIONS WILL BE HERE IN FUTURE RELEASES.

// This seciton is for security. Do not modify this part:
if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

//The options you can set begin below:

//
//	USE TEMPLATE CACHE?
//
//	The template cache is only used when you use the 'phpBB inside WordPress' template integraqtion in 'simple' mode. 
//	It SIGNIFICANTLY improves page generation time, as WordPress no longer needs to be invoked on phpBB pages just to get a header and footer.
//	However, if you have dynamic elements in your header or footer, then you will want to keep this option off.
//	To turn it on, change FALSE to TRUE.

define('WPU_CACHE_ENABLED', TRUE);



//
//	USE WORDPRESS CORE CACHE?
//
// When invoking WordPress, WP-United reads the WordPress core code and makes some minor changes to ensure compatibility.
// With this option turned on, this changed core code is cached to reduce processor and memory load (generation time isn't affected much).
// There should be no reason to turn this off, since this core code should never change, and does not need to be prepared each time. 
// If you are receiving unknown PHP errors and think this might be the cause, you can turn it off to aid in debugging.

define('WPU_CORE_CACHE_ENABLED', TRUE);



//
//	COMPRESS HTML OUTPUT FURTHER?
//
//	This section does a bit of minor extra HTML compression by stripping white space, at the expense of a little processing time.
// 	Doesn't yield much (about 5% reduction at the very most) if gzip is on. If gzip is off, it makes a significant difference. 
//	However, it CAN break some page output, especially uncompressed JavaScript in the page head. 
//	It's off by default, but you could turn it on by changing FALSE to TRUE.
//	Note that this will make your page source a little harder to read -- andis therefore not recommended if you're still building and debugging your site.

define('WPU_MAX_COMPRESS', FALSE);


//
//	ENABLE INTEGRATION DEBUG MODE?
//
//	Enabling the below option displays debug information that could be useful in tracking down problems with integrated logins
//	It should be left off on a production site!
//
define('WPU_DEBUG', FALSE);

//
// OVERRIDE WORDPRESS SITE COOKIE PATH?
//
// This sets the WordPress cookie path to '/'.
// Could be useful if your WordPress base install is in a path that is rewritten by Apache mod_rewrite, but most users will be fine if they leave this off.

define('WP_ROOT_COOKIE', FALSE);


// SHOW PAGE STATISTICS?
//
// Turn this option on to see the WP-United execution time and memory footprint.
// WP-United execution time is the time spent by WP-United doing integration, and includes
// WordPress run time, but not necessarily PHP run time.
//
// This is a good way to gauge how various options affect server load.
// It should be left OFF on production servers.
//
define('WPU_SHOW_STATS', TRUE);


//
//  DO NOT MAKE ANY CHANGES PAST THIS POINT!
//
//

// The "Remove header" option in the ACP kills too many templates. Rather than having to keep going back there
// while developing, we can just force it off here
define('DISABLE_HEADER_FIX', FALSE);




?>
