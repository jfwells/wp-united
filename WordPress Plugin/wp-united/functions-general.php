<?php 
/** 
*
* @package WP-United
* @version $Id: 0.9.1.5  2012/12/28 John Wells (Jhong) Exp $
* @copyright (c) 2006-2013 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* Generic WP-United functions that don't have anywhere better to go (yet).
*/

/**
 */
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) exit;


/**
 * Adds a traling slash to a string if one is not already present.
 * @param string $path
 * @return string modified path
 */
function add_trailing_slash($path) {
	return ( $path[strlen($path)-1] == "/" ) ? $path : $path . "/";
}

/**
 * Adds http:// to the URL if it is not already present
 * TODO: Kill off or check protocol
 */
function add_http($path) {
	if ( (strpos($path, "http://") === FALSE) && (strpos($path, "https://") === FALSE) ) {
		return "http://" . $path;
	}
	return $path;
}



/**
 * Convert HTML to BBCode 
 * Cut down version of function from early version of the WYSIWIG MOD by DeViAnThans3, 2005 (GPL v2)
 * Several changes and fixes for WP-United
 * @author DeViAnThans3
 */
function wpu_html_to_bbcode(&$string, $uid = '123456') { 
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
		"\n\\2", 
		"\n", 
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
	

	$string = str_replace(':' . $uid . ']', ']', $string);
} 


/**
 * Clean and standardise a provided file path
 */
function clean_path($value) {
	$value = trim($value);
	$value = str_replace('\\', '/', $value);
	$value = (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
	return $value;
}

/**
 * Calculates a relative path from the current location to a given absolute file path
 */
function wpu_compute_path_difference($filePath, $currLoc = false) {
	
	$absFileLoc = clean_path(realpath($filePath));

	if(is_dir($absFileLoc)) {
		$absFileLoc = add_trailing_slash($absFileLoc);
	}

	if($currLoc === false) {
		$currLoc = getcwd();
	}
	
	$absCurrLoc = clean_path(realpath($currLoc));
	
	if(is_dir($absCurrLoc)) {
		$absCurrLoc = add_trailing_slash($absCurrLoc);
	}
	
	// A fix for the WP-United build environment symlinks
	$absCurrLoc = str_replace('wpu-buildenv/sources/wp-united/root/wp-united/', 'wpu-buildenv/sources/phpbb/wp-united/', $absCurrLoc);
	
	$pathSep = (stristr( PHP_OS, "WIN")) ? "\\": "/";

	$absFileLoc = explode($pathSep, $absFileLoc);
	$absCurrLoc = explode($pathSep, $absCurrLoc);
	array_pop($absFileLoc);

	while($absCurrLoc[0]==$absFileLoc[0]) { 
		array_shift($absCurrLoc);
		array_shift($absFileLoc);
	}
	$pathsBack = array(".");
	for($i=0;$i<(sizeof($absCurrLoc)-1);$i++) {
		$pathsBack[] = "..";
	}
	$relPath = add_trailing_slash(implode("/", $pathsBack)) . add_trailing_slash(implode("/", $absFileLoc));
	
	return $relPath;
}

/**
 * General error handler for arbitrating phpBB & WordPress errors.
 */
function wpu_msg_handler($errno, $msg_text, $errfile, $errline) {
	global $phpbbForum, $IN_WORDPRESS;
	switch ($errno) {
		case E_NOTICE:
		case E_WARNING:
			return false;
		break;
	}
	if(!$IN_WORDPRESS) {
		return msg_handler($errno, $msg_text, $errfile, $errline);
	}
	 return false;
}

function wpu_ajax_header() {
	header('Content-Type: application/xml; charset=UTF-8'); 
	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');
	
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
}

function wpu_get_curr_page_link() {
	
	$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === false ? 'http://' : 'https://';
	$currURL = $protocol . $_SERVER['HTTP_HOST']  . htmlspecialchars($_SERVER['REQUEST_URI']);
	return $currURL;
}

function wpu_get_doc_root() {
	$docRoot =  (isset($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF']) );
	$docRoot = @realpath($docRoot); 
	$docRoot = str_replace( '\\', '/', $docRoot);
	$docRoot = ($docRoot[strlen($docRoot)-1] == '/') ? $docRoot : $docRoot . '/';
}

// Done. End of file.