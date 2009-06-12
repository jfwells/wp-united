<?php 
/** 
*
* WP-United Helper Functions
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
// Helper Functions used in WP-United scripts. Need to make more use of these.
//

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}


//
//	ADD TRAILING SLASH 
//	--------------------------------
//	Adds a traling slash to a string if one is not already present.
//
function add_trailing_slash($path) {
	return ( $path[strlen($path)-1] == "/" ) ? $path : $path . "/";
}

//
//	ADD HTTP:// 
//	-----------------
//	Adds http:// to the URL if it is not already present
//	TODO: Check if https:// is already present, too!!
//
function add_http($path) {

	return ( strpos($path, "http://") === FALSE ) ? "http://" . $path : $path;
}


// 
// 	The following functions are used by the cross-posting feature. They are lifted  from an early version of the WYSIWIG MOD
//	 We have made several changes and fixes . Originally (C) DeViAnThans3 - 2005 (GPL v2)
// 


function wpu_html_to_bbcode(&$string, $uid) { 
	// 
	// Function convert HTML to BBCode 
	// Cut down from DeViAnThans3's version.

    // Strip slashes ! 
	$string = stripslashes($string); 
	$string = strip_tags($string, '<p><a><img><br><strong><em><blockquote><b><u><i><ul><ol><li><code>');
	$from = array( 
		'~<i>(.*?)</i>~is', 
		'~<span.*?font-style: italic.*?' . '>(.*?)</span>~is',
		'~<span.*?text-decoration: underline.*?' . '>(.*?)</span>~is',
		'~<em(.*?)>(.*?)</em>~is',
		'~<b(.*?)>(.*?)</b>~is',
		'~<strong(.*?)>(.*?)</strong>~is',
		'~<u(.*?)>(.*?)</u>~is',
		'~<code(.*?)>(.*?)</code>~is', 	
		'~<blockquote(.*?)>(.*?)</blockquote>~is', 	
		'~<img.*?src="(.*?)".*?' . '>~is',
		'~<a.*?href="(.*?)".*?' . '>(.*?)</a>~is',
		'~<p(.*?)>(.*?)</p>~is',
		'~<br(.*?)>~is',
		'~<li(.*?)>(.*?)</li>~is',
		'~<ul(.*?)>(.*?)</ul>~is',
		'~<ol(.*?)>(.*?)</ol>~is',
	);

	$to = array(
		'[i:' . $uid . ']\\1[/i:' . $uid . ']',
		'[i:' . $uid . ']\\1[/i:' . $uid . ']',
		'[u:' . $uid . ']\\1[/u:' . $uid . ']', 
		'[i:' . $uid . ']\\2[/i:' . $uid . ']', 
		'[b:' . $uid . ']\\2[/b:' . $uid . ']', 
		'[b:' . $uid . ']\\2[/b:' . $uid . ']', 
		'[u:' . $uid . ']\\2[/u:' . $uid . ']', 
		'[code:' . $uid . ']\\2[/code:' . $uid . ']',
		'[quote:' . $uid . ']\\2[/quote:' . $uid . ']', 
		'[img:' . $uid . ']\\1[/img:' . $uid . ']',
		'[url=\\1]\\2[/url]',
		'\\2[br][br]', 
		'[br]', 
		"\n" . '[*:' . $uid . ']\\2',
		'[list:' . $uid . ']\\2[/list:' . $uid . ']',
		'[list=1:' . $uid . ']\\2[/list:' . $uid . ']', 
	);

	$string = preg_replace($from, $to, $string); 
	$string = str_replace("<br />", "[br]", $string); 
	$string = str_replace("&nbsp;", " ", $string); 
	// kill any remaining
	$string = htmlspecialchars(strip_tags($string)); 
	// prettify estranged tags
	$string = str_replace('&amp;lt;', '<', $string); 
	$string = str_replace('&amp;gt;', '>', $string); 
	$string = str_replace('&lt;', '<', $string); 
	$string = str_replace('&gt;', '>', $string); 
	$string = str_replace('&quot;', '"', $string); 
	$string = str_replace('&amp;', '&', $string); 
	
	//remove uid if phpBB3
	if ($GLOBALS['wpuAbs']->ver == 'PHPBB3') {
		$string = str_replace(':' . $uid, '', $string);
	}
} 

function wpu_bbcode_to_html($string, $uid) { 
	// May use this in the future for forum -> blog posting (Not yet).
	// Bold text 
	$bbcode_initial = "/([[Bb]:$uid])(.+)([/[Bb]:$uid])/"; 
	$bbcode_replacements[] = "<strong>2</strong>";  

	// Italic text 
	$bbcode_initial[] = "/([[Ii]:$uid])(.+)([/[Ii]:$uid])/";
	$bbcode_replacements[] = "<em>2</em>"; 

	// Underlined text 
	$bbcode_initial[] = "/([[Uu]:$uid])(.+)([/[Uu]:$uid])/"; 
	$bbcode_replacements[] = "<span style=\"text-decoration: underline;\">2</span>";

	// hr 
	$string = str_replace("[hr]", "<hr />", $string); 

	// br 
	$string = str_replace("[br]", "<br />", $string); 

	// Links with alternative text 
	$bbcode_initial[] = "/([url=)(.+)(])(.+)([/url])/"; 
	$bbcode_replacements[] = "<a href=\"2\">4</a>"; 

	// Links (for the web) 
	$bbcode_initial[] = "/([url])(.+)([/url])/"; 
	$bbcode_replacements[] = "<a href=\"2\">2</a>"; 

	// Images (from the web) 
	$bbcode_initial[] = "/([img:$uid])(.+)([/img:$uid])/"; 
	$bbcode_replacements[] = "<img src=\"2\" alt=\"2\" />"; 

	// Quote (with authour) 
	$bbcode_initial[] = "/([quote:$uid=\")(.+)(\"])(.+)([/quote:$uid])/"; 
	$bbcode_replacements[] = "2 post: <div style=\"background-color: #757575; color: #FFFFFF; border-style: solid; border-width: 1px; border-color: #FFFFFF; text-indent: 20px;\">4</div>"; 

	// Normal quote without author 
	$bbcode_initial[] = "/([quote:$uid])(.+)([/quote:$uid])/"; 
	$bbcode_replacements[] = "Quote: <div style=\"background-color: #757575; color: #FFFFFF; border-style: solid; border-width: 1px; border-color: #FFFFFF; text-indent: 20px;\">2</div>"; 

	// Code Block 
	$bbcode_initial[] = "/([code:[0-9]:$uid])(.+)([/code:[0-9]:$uid])/"; 
	$bbcode_replacements[] = "<code>2</code>"; 

	// Font size 
	$bbcode_initial[] = "/([size=)([0-9|][0-9])(:$uid])(.+)([/size:$uid])/"; 
	$bbcode_replacements[] = "<span style=\"font-size: 2px;\">4</span>"; 

	// Font colour 
	$bbcode_initial[] = "/([color=)(.+)(:$uid])(.+)([/color:$uid])/"; 
	$bbcode_replacements[] = "<span style=\"color: 2\">4</span>"; 

	// List Parser 
	$string = str_replace("[list:$uid]", "<ul>", $string); 
	$string = str_replace("[*:$uid]", "<li>", $string); 
	$string = str_replace("[/list:u:$uid]", "</ul>", $string); 
	$string = str_replace("[/list:o:$uid]", "</ol>", $string); 
	$bbcode_initial[] = "/[list=([a1]):$uid]/si"; 
	$bbcode_replacements[] = "<ol type=\"1\">"; 

	// Sorts out the HTML using regular expressions 
	$string = preg_replace($bbcode_initial, $bbcode_replacements, $string); 

	// remove any carriage returns (mysql) 
	$string = str_replace("\n", '<br />', $string); 
	$string = str_replace("\r", '', $string); 

	// replace any newlines that aren't preceded by a > with a <br /> 
	$string = preg_replace('/(?<!>)n/', "<br />\n", $string); 

	// Safe postings 
	$string = wpu_safe_post($string); 

return $string; 
} 


function wpu_safe_post($string) { 
	$tmpString = $string; 
	$tmpString = trim($string); 

	//convert all types of single quotes 
	$tmpString = str_replace(chr(145), chr(39), $tmpString); 
	$tmpString = str_replace(chr(146), chr(39), $tmpString); 
	$tmpString = str_replace("'", "'", $tmpString); 

	//convert all types of double quotes 
	$tmpString = str_replace(chr(147), chr(34), $tmpString); 
	$tmpString = str_replace(chr(148), chr(34), $tmpString); 

	//replace carriage returns & line feeds 
	$tmpString = str_replace(chr(10), " ", $tmpString); 
	$tmpString = str_replace(chr(13), " ", $tmpString); 


	return $tmpString; 
} 






?>
