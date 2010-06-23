<?php 
/** 
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/01/25 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* Generic WP-United functions that don't have anywhere better to go (yet).
*/

if ( !defined('IN_PHPBB') ) {
	exit;
}


/**
 * Adds a traling slash to a string if one is not already present.
 */
function add_trailing_slash($path) {
	return ( $path[strlen($path)-1] == "/" ) ? $path : $path . "/";
}

/**
 * Adds http:// to the URL if it is not already present
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
	
	if($currLoc === false) {
		$currLoc = getcwd();
	}
	
	$absCurrLoc = add_trailing_slash(clean_path(realpath($currLoc)));

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


?>