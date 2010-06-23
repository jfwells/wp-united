<?php

/** 
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* CSS Magic & Template Voodoo procedural functions.
* The legwork of CSS Magic is done by the CSS Magic class itself. 
* 
*/

/**
 */
if ( !defined('IN_PHPBB') ) {
	exit;
}



/**
 * Modify links in header to stylesheets to use CSS Magic instead
 * @param string $headerInfo The snipped head of the page (By reference)
 * @param mixed $position Set to "inner" if we are processing HEAD of the application that is destined
 * for the inner portion of the page (defaults to "outer")
 * @return array an array of stylesheet links and modifications
 */
function wpu_get_stylesheet_links(&$headerInfo, $position="outer") {
	global $phpbb_root_path, $wpuCache, $wpSettings, $phpbbForum;

	// grep all styles
	preg_match_all('/<link[^>]*?href=[\'"][^>]*?(style\.php\?|\.css)[^>]*?\/>/i', $headerInfo, $matches);
	preg_match_all('/@import url\([^\)]+?\)/i', $headerInfo, $matches2);
	preg_match_all('/@import "[^"]+?"/i', $headerInfo, $matches3);
	$matches = array_merge($matches[0], $matches2[0], $matches3[0]);
	$links = array(); $repl = array(); $keys = array();
	if(is_array($matches)) {
		$pos = "pos=" . $position;
		foreach($matches as $match) {
			// extract css location
			$and = '&';
			if(stristr($match, "@import url") !== false) { // import URL
				$el = str_replace(array("@import", "(", "url",  ")", " ", "'", '"'), "", $match);
			} elseif(stristr($match, "@import") !== false) { // import
				$el = str_replace(array("@import", "(",  ")", " ", "'", '"'), "", $match);
			} else { // link href -- xxx.css or style.php
				/**
				 * Need to extract the stylesheet name, by extracting from between 'href =' and ('|"| )
				 * Bearing in mind that there could be an = in the stylesheet name
				 */
				$els = explode("href", $match);
				$els = explode('=', $els[1]);
				array_shift($els);
				$els = implode("=", $els);
				$els = explode('"', $els);
				$el = str_replace(array(" ", "'", '"'), "", $els[1]);
				$and = '&amp;';
			}
			$tv = (($position == 'inner') && (!empty($wpSettings['templateVoodoo']))) ? "{$and}tv=" : '';
			if(stristr($el, ".css") !== false) {
				/**
				 * We need to ensure the stylesheet maps to a real file on disk as fopen_url will not work on most 
				 * servers.
				 * We try various methods to find the file
				 */
				// Absolute path to CSS, in phpBB
				if(stristr($el, $phpbbForum->url) !== false) {
					$cssLnk = str_replace($phpbbForum->url, "", $el); 
				// Absolute path to CSS, in WordPress
				} elseif(stristr($el, $wpSettings['wpUri']) !== false) {
					$cssLnk = str_replace($wpSettings['wpUri'], $wpSettings['wpPath'], $el);
				} else {
					// else: relative path
					$cssLnk = $phpbb_root_path . $el;
				}
				// remove query vars
				$cssLnk = explode('?', $cssLnk);
				$cssLnk = $cssLnk[0];
				$cssLnk = (stristr( PHP_OS, "WIN")) ? str_replace("/", "\\", $cssLnk) : $cssLnk;
				
				if( file_exists($cssLnk) && (stristr($cssLnk, "http:") === false) ) { 
					$links[] = $el;
					$cssLnk = realpath($cssLnk);
					$key = $wpuCache->get_style_key($cssLnk, $position);
					$keys[] = $key;
					$repl[] = "{$phpbbForum->url}wp-united/style-fixer.php?usecssm=1{$and}style={$key}{$and}{$pos}{$tv}";
				}
			} elseif(stristr($el, "style.php?") !== false) {
				/**
				 * phpBB style.php css
				 */
				$links[] = $el;
				$key = $wpuCache->get_style_key($el, $position);
				$keys[] = $key;
				$repl[] = "{$el}{$and}usecssm=1{$and}{$pos}{$and}cloc={$key}{$tv}";
			} 
		}
	}
	return array('links' => $links, 'keys' => $keys, 'replacements' => $repl); 
}

/**
 * Extracts inline CSS from an HTML string
 * @param $content the HTML string
 * @return array of css blocks found
 */
function wpu_extract_css($content) {
	$css = array('css' => array(), 'orig' => array());
	preg_match_all('/<style type=\"text\/css\">(.*?)<\/style>/', $content, $cssStr);
	foreach($cssStr[1] as $index => $c) {
		$cssFixed = str_replace(array('<!--', '-->'), '', $c);
		if(!empty($cssFixed)) {
			$css['css'][] = $cssFixed;
			$css['orig'][] = $cssStr[0][$index];
		}
	}
	return $css;
}

/**
 * Cleans up relative URLs in stylesheets so that they still work even through style-fixer
 * @param string $filePath the path to the current file
 * @param string $css a string containing valid CSS to be modified
 */
function wpu_fix_css_urls($filePath, &$css) {
	global $phpbb_root_path, $phpEx;
	require_once($phpbb_root_path . 'wp-united/functions-general.' . $phpEx);
	$relPath = wpu_compute_path_difference($filePath);
	
	preg_match_all('/url\(.*?\)/', $css, $urls);
	if(is_array($urls[0])) {
		foreach($urls[0] as $url) {
			$replaceUrl = false;
			if(stristr($url, "http:") === false) {
				$out = str_replace("url(", "", $url);
				$out = str_replace(")", "", $out);
				$out = str_replace("'", "", $out);
				$out = str_replace('"', '', $out);
				if ($out[0] != "/") {
					$replace = true;
				}
			}
			if ($replace) {
				$css = str_replace($url, "url('{$relPath}{$out}')", $css);
			}
		}
	}
}

function apply_template_voodoo(&$cssMagic, $tplVoodooKey) {
	global $wpuCache;
	
	$templateVoodoo = $wpuCache->get_template_voodoo($tplVoodooKey);
	

	if(empty($templateVoodoo)) {
		return false;
	}

	if(isset($templateVoodoo['classes']) && isset($templateVoodoo['ids'])) {
		
		$classDupes = $templateVoodoo['classes'];
		$idDupes = $templateVoodoo['ids'];
		$finds = array();
		$repl = array();
		foreach($classDupes as $classDupe) {
			$finds[] = $classDupe;
			$repl[] = ".wpu" . substr($classDupe, 1);
		}
		foreach($idDupes as $idDupe) {
			$finds[] = $idDupe;
			$repl[] = "#wpu" . substr($idDupe, 1);
		}	

		$cssMagic->modifyKeys($finds, $repl);
	}
	
	return true;
	
}

?>