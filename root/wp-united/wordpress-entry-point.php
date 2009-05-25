<?php 
/** 
*
* WP-United Main Integration File
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.6.5[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
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
// The main integration file. 
//

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}
define('WP_UNITED_ENTRY', 1);

require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);

if ( !defined('WPU_BLOG_PAGE') ) {
	//We're accessing not via blog.php -- need to call abstractify
	global $wpuAbs;
	require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);

}
require_once($phpbb_root_path . 'wp-united/wpu-helper-funcs.' . $phpEx);



//if ( defined('WPU_REVERSE_INTEGRATION') && ( 'PHPBB3' == $wpuAbs->ver) ) { //whatever - we always need these in the global scope
	global $wpSettings, $user, $userdata, $wpuNoHead, $retWpInc, $wpUtdInt, $scriptPath, $template, $latest;
//}
// integration class will test for this in the global scope.
$amIGlobal = TRUE;

//Check that Mod Settings have been loaded
if  ( $wpSettings == FALSE ) {
	$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_DBErr_Gen'), 'WordPress Integration Error','','',''); 
} elseif ( ($wpSettings['installLevel'] < 10) || ($wpSettings['wpUri'] == '') || ($wpSettings['wpPath'] == '') ) {
	$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_Not_Installed_Yet'), 'WordPress Integration Error','','','');
}

// set some vars for our plugin to use. 
global $phpbb_logged_in, $phpbb_username, $phpbb_sid, $login_link, $logout_link;
$phpbb_logged_in = $wpuAbs->user_logged_in();
$phpbb_username = $wpuAbs->phpbb_username();
$phpbb_sid = $wpuAbs->phpbb_sid();


// redirect to login if not logged in and blogs are private
if ( (!$wpuAbs->user_logged_in()) && ($wpSettings['must_login'])  && (!defined('WPU_REVERSE_INTEGRATION')) ) {
   redirect($login_link, true);
}


// What mode shall we run the integration in? latest posts or normal?
$wpuNoHead = false;
$latest = false;
if ( isset($HTTP_GET_VARS['latest']) ) {
	$latest = true; // run in latest posts mode, for showing latest posts on portal page, etc.
	$wpuNoHead = true;
}


// number of posts to show on portal page in latest posts mode
if ( isset($HTTP_GET_VARS['numposts']) ) {
	$postsToShow = (int) $HTTP_GET_VARS['numposts'];
	$postsToShow = ($postsToShow > 10) ? 10 : $postsToShow;
	$postsToShow = ($postsToShow < 1) ? 3 : $postsToShow;
}


// These will be useful later on
$server = add_http(add_trailing_slash($wpuAbs->config('server_name')));
$scriptPath = add_trailing_slash($wpuAbs->config('script_path'));
$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
$scriptPath = $server . $scriptPath;


// set some strings for the blogs home page. This needs to be moved out.
if ( $phpbb_logged_in ) {
	if ( $wpuAbs->userdata('user_wpublog_id') ) {
		$wpuGetBlog = ($wpSettings['usersOwnBlogs']) ? $wpuAbs->lang('add_to_blog') : $wpuAbs->lang('go_wp_admin');
		$wpuGetBlogIntro = $wpuAbs->lang('blog_intro_add');
	} else {
		$wpuGetBlog = ($wpSettings['usersOwnBlogs']) ? $wpuAbs->lang('get_blog') : $wpuAbs->lang('go_wp_admin');
		$wpuGetBlogIntro = $wpuAbs->lang('blog_intro_get');
	}
} else {
	$wpuGetBlogIntro =  ($wpSettings['usersOwnBlogs']) ? $wpuAbs->lang('blog_intro_loginreg_ownblogs') : $wpuAbs->lang('blog_intro_loginreg');
}

// WP-query screws up the path when we have a single trailing slash in pathinfo.. so remove it:
if ( isset($_SERVER['PATH_INFO']) ) {
	$_SERVER['PATH_INFO'] = ( $_SERVER['PATH_INFO'] == "/" ) ? '' : $_SERVER['PATH_INFO'];
}	

global $pfContent, $pfHead, $retWpInc; // for when not in global scope


//If this is a reverse interation, prepare the phpBB page first
$noIntLogin = FALSE;
if ( defined('WPU_REVERSE_INTEGRATION') ) {

	$pfHead = process_head($pfContent);
	$pfContent = process_body($pfContent);
	
	// If we're only using a simple WP header & footer, we don't bother with integrated login, and we can cache the wordpress parts of the page
	if ( !empty($wpSettings['wpSimpleHdr']) ) {
		if ( (defined('WPU_CACHE_ENABLED')) && (WPU_CACHE_ENABLED) && (!defined('WPU_PERFORM_ACTIONS')) ) {
			$noIntLogin = TRUE;
			global $wpu_cacheLoc;
			$wpu_cacheLoc = '';
			set_wpu_cache();
		}
	}
	
	//Remove the phpBB header if required, preserving the search box
	if ( !empty($wpSettings['fixHeader']) ) {
		global $pHeadRemSuccess, $srchBox;
		if(preg_match('/<div id="search-box">[\s\S]*?<\/div>/', $pfContent, $srchBox)) {
			$srchBox = $srchBox[0];
		}
		$token = '/<div class="headerbar">[\S\s]*?<div class="navbar">/';		$pfContent2 = preg_replace($token, '<br /><div class="navbar">', $pfContent, 1);
		$pHeadRemSuccess = ($pfContent2 == $pfContent); // count paramater to preg_replace only available in php5 :-(
		$pfContent = $pfContent2; unset($pfContent2);
	}
	
	
}


if ( !defined('WPU_USE_CACHE') ) {
	require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
	$wpUtdInt = WPU_Integration::getInstance();

	if ($wpUtdInt->can_connect_to_wp()) {
		$wpUtdInt->enter_wp_integration();
		if ( (!$latest) && (!$noIntLogin) ) {
			$wpUtdInt->integrate_login();
		} 
		
		// set up the page to be grabbed
		$wpUtdInt->get_wp_page('retWpInc');

		// Nothing happens until we execute WordPress with the following:
		eval($wpUtdInt->exec());  

		$wpUtdInt->exit_wp_integration();
		$wpUtdInt = null; unset ($wpUtdInt);
	} else {
		$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_Not_Installed_Yet'), '','','');
	}
} else {
	require($phpbb_root_path . 'wp-united/wp-template-loader.' . $phpEx);
}

//If we want to show the page inside phpBB header/footer, we need to process it first
if ( ($wpSettings['showHdrFtr'] == 'FWD') && (!$wpuNoHead) && (!defined('WPU_REVERSE_INTEGRATION')) ) {
	$wpHdrInfo = process_head($retWpInc);
	
	//export styles to template - before or after phpBB depending on selection.
	$header_info_loc = ( $wpSettings['cssFirst'] == 'P' ) ? 'WP_HEADERINFO_LATE' : 'WP_HEADERINFO_EARLY';
	$template->assign_vars(array($header_info_loc => $wpHdrInfo));
	// We need to set the base HREF correctly, so that images and links in the phpBB header and footer work properly
	$wpuAbs->add_template_switch('PHPBB_BASE', $scriptPath);

	//Char Encoding
	if ( 'PHPBB2' == $wpuAbs->ver ) {
		if ( $wpSettings['charEncoding'] == 'MATCH_WP' ) {
			$lang['ENCODING'] = get_option('blog_charset');
		}
	}
	if ( 'PHPBB2' == $wpuAbs->ver ) {
		include($phpbb_root_path . 'includes/page_header.'.$phpEx); 
	} else {
		global $page_title;
		page_header($page_title);
	}
	//free memory
	unset($wpHdrInfo);
	
	$retWpInc = process_body($retWpInc);
		
	// include page structure for phpBB
	$wpuAbs->add_template_switch('S_SHOW_HDR_FTR', TRUE);
}

//Some login/logout links are hard-coded into the WP templates. We fix them here:
if ( !empty($wpSettings['integrateLogin']) ) {

if ($wpuAbs->ver == 'PHPBB2') {
	$login_link = 'login.'.$phpEx.'?redirect=wp-united-blog&amp;sid='. $phpbb_sid . '&amp;d=';
} else {
	$login_link = 'ucp.'.$phpEx.'?mode=login&amp;sid=' . $phpbb_sid . '&amp;redirect=';
}

	// only phpBB3 can redirect properly	
	$retWpInc = str_replace("$siteurl/wp-login.php?redirect_to=", $scriptPath . $login_link, $retWpInc);
	$retWpInc = str_replace("$siteurl/wp-login.php?redirect_to=", $scriptPath . $login_link, $retWpInc);
	$retWpInc = str_replace("$siteurl/wp-login.php?action=logout", $scriptPath . $logout_link, $retWpInc);
}

// Some trailing slashes are hard-coded into the WP templates. We don't want 'em.
$retWpInc = str_replace(".$phpEx/?",  ".$phpEx?", $retWpInc);
$retWpInc = str_replace(".$phpEx/\"",  ".$phpEx\"", $retWpInc);

//Fix <title> on reverse-integrated page!
if ( defined('WPU_REVERSE_INTEGRATION') ) {
	$token = "/<title>(.*)[^<>]<\/title>/";
	$retWpInc = preg_replace($token, "<title>{$GLOBALS['page_title']}</title>", $retWpInc);
}

	
//optional bandwidth tweak -- this section does a bit of minor extra HTML compression by stripping white space.
if ( (defined('WPU_MAX_COMPRESS')) && (WPU_MAX_COMPRESS) ) {
	$search = array('/\>[^\S ]+/s',	'/[^\S ]+\</s','/(\s)+/s');
	$replace = array('>', '<', '\\1');
	$retWpInc = preg_replace($search, $replace, $retWpInc);
}



// Finally -- show the page!
if ( (!defined('WPU_REVERSE_INTEGRATION')) && (empty($wpuNoHead)) ) { 
	$template->assign_vars(array(
		'WORDPRESS_BODY' => $retWpInc,
		'WP_CREDIT' => sprintf($wpuAbs->lang('WPU_Credit'), '<a href="http://www.wp-united.com" target="_blank">', '</a>'))
	); 
	//free memory
	unset($retWpInc);

	$wpuAbs->show_body('blog');	

	// Show the standard page footer if it's wanted.
	if ( ($wpSettings['showHdrFtr'] == 'FWD') && (!$wpuNoHead) ) {
		if ( 'PHPBB2' == $wpuAbs->ver ) {
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx); 
		} // else phpbb3 footer called from show_body
	}
} else { 
	echo $retWpInc;
}

//
//	PROCESS_HEAD
//	--------------------------------
//	Processes the page head, returns header info to be inserted into the WP or phpBB page head
//	Removes thee had from the rest of the page, which must be passed by ref

function process_head(&$retWpInc) {
	global $wpSettings, $template, $wpuAbs;
	//Locate where the WordPress <body> begins, and snip of everything above and including the statement
	$bodyLocStart = strpos($retWpInc, "<body");
	$bodyLoc = strpos($retWpInc, ">", $bodyLocStart);
	$wpHead = substr($retWpInc, 0, $bodyLoc + 1);
	$retWpInc = substr_replace($retWpInc, '', 0, $bodyLoc + 1);

	//grab the page title
	$begTitleLoc = strpos($wpHead, "<title>");
	$titleLen = strpos($wpHead, "</title>") - $begTitleLoc;
	$wpTitleStr = substr($wpHead, $begTitleLoc +7, $titleLen - 7);

	// set page title 
	$GLOBALS['page_title'] = trim($wpTitleStr); 

	//get anything inportant from the WP or phpBB <head> and integrate it into our phpBB page...
	$header_info = '';

	$findItems = array(
		'<!--[if' => '<![endif]-->',
		'<meta ' => '/>',
		'<script ' => '</script>',
		'<link ' => '/>',
		'<style ' => '</style>',

		'<!-- wpu-debug -->' => '<!-- /wpu-debug -->'
	);
	$header_info = head_snip($wpHead, $findItems);
	//get the DTD if we're doing DTD switching
		if ( ($wpSettings['dtdSwitch']) && !defined('WPU_REVERSE_INTEGRATION') ) {
			$wp_dtd = head_snip($wpHead, array('<!DOCTYPE' => '>'));
			$wpuAbs->add_template_switch('WP_DTD', $wp_dtd);
		}

	//fix font sizes coded in pixels  by phpBB -- un-comment this line if WordPress text looks too small
	//$wpHdrInfo .= "<style type=\"text/css\" media=\"screen\"> body { font-size: 62.5% !important;} </style>";

	return $header_info;
}

//
//	HEAD SNIP
//	----------------
//	snips content out of a given string, and inserts it into a second string that is returned. 
//	Stuff to be found is passed in as an array of starting_token => ending_token.

function head_snip(&$haystack,$findItems) {
	$wpHdrInfo = '';
	foreach ( $findItems as $startToken => $endToken ) {
		$foundStyle = 1;
		$searchOffset = 0;
		$numLoops = 0; 	
		$styleLen = 0;
		$begStyleLoc = false;
		while (($foundStyle == 1) && ($numLoops <=20)) { //If we find more than 20 of one needle, something's probably wrong
		   $numLoops++; 
		   $begStyleLoc = strpos($haystack, $startToken, $searchOffset);
		   if (!($begStyleLoc === false)) { 
		      $styleLen = strpos($haystack, $endToken, $begStyleLoc) - $begStyleLoc;
		      if ($styleLen > 0) {
		        $foundPart = substr($haystack, $begStyleLoc, $styleLen + strlen($endToken));
				$haystack = str_replace($foundPart, '', $haystack);
				$wpHdrInfo .= $foundPart . "\n";
		        $foundStyle = 1;
		        $searchOffset = $begStyleLoc;
		      } else {
		         $searchOffset = $begStyleLoc;
		      }
		   } else {
		     $foundStyle = 0;
		   }
		}
	}
	return $wpHdrInfo;
}


function process_body($retWpInc, $killTags = FALSE) {	
	//Process the body section for integrated page

	// With our Base HREF set, any relative links will point to the wrong location. Let's fix them.
	$fullWpURL = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
	$retWpInc = str_replace("a href=\"#", "a href=\"$fullWpURL#", $retWpInc);

	//cut out any </body> and </html> tags
	$retWpInc = str_replace("</body>", "", $retWpInc);
	$retWpInc = str_replace("</html>", "", $retWpInc);
	
	return $retWpInc;
	
// End of processing of integrated page. 
}


//
//	SET_WPU_CACHE
//	-------------------------
//	If we are putting phpBB inside WordPress, we really want to cache the WP header / footer
//	This function simply sets up caching in wp-united/cache


function set_wpu_cache(){
	global $phpbb_root_path, $wpSettings, $phpEx, $wpu_cacheLoc;
	$cacheLocation = $phpbb_root_path . 'wp-united/cache/';
	@$dir = opendir($cacheLocation);
	$cacheLoc = '';
	$cacheFound = FALSE;
	while( $entry = @readdir($dir) ) {
		if ( strpos($entry, '.wpucache') ) {
			$cacheFound = TRUE;
			$theme = explode('.', $entry);
			$theme = $theme[0];
			$wpu_cacheLoc = $cacheLocation . $entry;
		}
	}
	if ($cacheFound) { // refresh cache if it is older than theme file, or if WP has been upgraded.
		$fileAddress = $wpSettings['wpPath'] . "wp-content/themes/$theme";
		$compareDate = filemtime($wpu_cacheLoc);
		if ( !( ($compareDate < @filemtime("$fileAddress/header.$phpEx")) || 
		  ($compareDate < @filemtime("$fileAddress/footer.$phpEx")) ||
		  ($compareDate < @filemtime($wpSettings['wpPath'] . "wp-includes/version.$phpEx")) ) ) {
			define('WPU_USE_CACHE', TRUE); 
		}
	} 
}
?>
