<?php

/** 
*
* @package WP-United
* @version $Id: v0.9.1.0  2012/12/17 John Wells (Jhong) Exp $
* @copyright (c) 2006-2012 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* CSS Magic & Template Voodoo procedural functions.
* The legwork of CSS Magic is done by the CSS Magic class itself. 
* 
*/

/**
 */
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) exit;


/**
 * CSS MAGIC
 * 
 * We now have properly separated inner/outer content
 * 
 * We now identify all the stylesheets and redirect them to CSS Magic.
 * We don't parse them now, as style.php is used for phpBB styles.
 * 
 * Once they have been parsed by CSS Magic, on subsequent run-throughs here, 
 * we can read CSS Magic's cache and look at the stylesheets for conflicting 
 * classes and IDs. Then, we modify the templates accordingly, and instruct CSS Magic
 * to make additional changes to the CSS the next time around.
 */

function wpu_css_magic() {
	global $wpUnited;


	$wpuCache = WPU_Cache::getInstance();
	
	/** 
	 * Get all links to stylesheets, and prepare appropriate replacement links to insert into the page content
	 * The generated CSS links to insert into the HTML will need to carry information for the browser on the 
	 * physical disk location of the CSS files on the server.
	 * 
	 * We cannot allow browsers to just request any file on the server by filename, so get_stylesheet_links pre-approves the
	 * files and stores them in the DB under "style keys". Browsers then only need to know the 
	 * appropriate style key, not the filename
	 */
	
	$innerSSLinks = wpu_get_stylesheet_links($wpUnited->get_inner_headinfo(), 'inner');
	// also grep all inline css out of headers
	$inCSSInner = wpu_extract_css($wpUnited->get_inner_headinfo());
	
	

	/**
	 * Template Voodoo
	 */
	if ($wpUnited->get_setting('templateVoodoo')) {
		
		//For template voodoo, we also need the outer styles
		$outerSSLinks = wpu_get_stylesheet_links($wpUnited->get_outer_content(), 'outer');
		$inCSSOuter = wpu_extract_css($wpUnited->get_outer_content());
		
		// First check if the cached CSS Magic files exist, and insert placeholders for TV cache location if they do
		$foundInner = array();
		$foundOuter = array();

		foreach ((array)$innerSSLinks['keys'] as $index => $key) {
			if($found = $wpuCache->get_css_magic($wpUnited->get_style_key($key), 'inner', -1)) {
				$foundInner[] = $found;
				$innerSSLinks['replacements'][$index] .=  '[*FOUND*]';
			} else {
				$innerSSLinks['replacements'][$index] .=  '-1';
			}
		}
		foreach ($outerSSLinks['keys'] as $index => $key) {
			if($found = $wpuCache->get_css_magic($wpUnited->get_style_key($key), 'outer', -1)) {
				$foundOuter[] = $found;
			}
		}	

		/**
		 * Now we create a unique hash based on everything we've found, and use this to 
		 * store our Template Voodoo instructions.
		 * We append the template voodoo hash key to the end of the redirected stylesheets
		 */
		$tplVoodooKey = $wpuCache->get_template_voodoo_key(TEMPLATEPATH, $foundInner, $foundOuter, (array)$inCSSInner['orig'], (array)$inCSSOuter['orig']);	
		$innerSSLinks['replacements'] = str_replace('[*FOUND*]', $tplVoodooKey, $innerSSLinks['replacements']);

		if((sizeof($foundInner) || $inCSSInner['orig'] ) && (sizeof($foundOuter) || $inCSSOuter['orig'])) {
			$classDupes = array();
			$idDupes = array();
			
			if($templateVoodoo = $wpuCache->get_template_voodoo($tplVoodooKey)) {
				/**
				 * The template voodoo instructions already exist for this CSS combination
				 */
				if(isset($templateVoodoo['classes']) && isset($templateVoodoo['ids'])) {
					$classDupes = $templateVoodoo['classes'];
					$idDupes = $templateVoodoo['ids'];
				} 
			} else { 
				/**
				 * We don't have template voodoo for this yet, we need to do some legwork
				 * and generate a set of instructions.
				 * @todo move to separate function, generate_instructions().
				 */
				$outerCSS = new CSS_Magic();
				$innerCSS = new CSS_Magic();
		
				foreach ($foundInner as $index => $cacheFile) {
					$innerCSS->parseFile($cacheFile);
				}
				foreach ($foundOuter as $index => $cacheFile) {
					$outerCSS->parseFile($cacheFile);
				}				
				foreach($inCSSInner['css'] as $index => $css) {
					$innerCSS->parseString($css);
				}
				foreach($inCSSOuter['css'] as $index => $css) {
					$outerCSS->parseString($css);
				}

				$innerCSS->removeCommonKeyEl('#wpucssmagic .wpucssmagic');
				$innerKeys = $innerCSS->getKeyClassesAndIDs();
				$outerKeys = $outerCSS->getKeyClassesAndIDs();

				$innerCSS->clear();
				$outerCSS->clear();
				unset($innerCSS, $outerCSS);
	
				$classDupes = array_intersect($innerKeys['classes'], $outerKeys['classes']);
				$idDupes = array_intersect($innerKeys['ids'], $outerKeys['ids']);

				unset($innerKeys, $outerKeys);

				// save to cache
				$wpuCache->save_template_voodoo(array('classes' => $classDupes, 'ids' => $idDupes), $tplVoodooKey);
			}
	
			/**
			 * Now, we can modify the page, removing class and ID duplicates from the inner content
			 */
			foreach($classDupes as $dupe) {
				$findClass = substr($dupe, 1); //remove leading '.'
				$wpUnited->set_inner_content(preg_replace('/(class=["\']([^\s^\'^"]*\s+)*)'.$findClass.'([\s\'"])/', '\\1wpu'.$findClass.'\\3', $wpUnited->get_inner_content()));
			}
			foreach($idDupes as $dupe) {
				$findId = substr($dupe, 1); //remove leading '.'
				$wpUnited->set_inner_content(preg_replace('/(id=["\']\s*)'.$findId.'([\s\'"])/', '\\1wpu'.$findId.'\\2', $wpUnited->get_inner_content()));
			}
		}
	} // end template voodoo
	
		
	/**
	 * Now we can apply the CSS magic to any inline CSS
	 */
	$useTVStr =  ($wpUnited->get_setting('templateVoodoo')) ? 'TV' : '';
	$tvKey = ($wpUnited->get_setting('templateVoodoo')) ? $tplVoodooKey : -1;
	$numFixes = 0;
	foreach($inCSSInner['css'] as $index => $innerCSSItem) {

		if($inlineCache = $wpuCache->get_css_magic("{$index}-{$useTVStr}", 'inline', $tvKey)) {
			$result = @file_get_contents($inlineCache);
		} else {
			$cssM = new CSS_Magic();
			$cssM->parseString($innerCSSItem);
			/**
			 * @todo could split out to templatevoodoo file
			 */
			if ($wpUnited->get_setting('templateVoodoo')) {
				if(isset($classDupes) && isset($idDupes)) {
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

					$cssM->modifyKeys($finds, $repl);
				}
			}
			$cssM->makeSpecificByIdThenClass('wpucssmagic', false);
			$result = $cssM->getCSS();
			// save to cache
			$wpuCache->save_css_magic($result, "{$index}-{$useTVStr}", 'inline', $tvKey);
		
		} 
		if(!empty($result)) {
			//$result = '<style type="text/css">'  . $result . '</style>';
			$wpUnited->set_inner_headinfo(str_replace($inCSSInner['orig'][$index], $result, $wpUnited->get_inner_headinfo()));
			$numFixes++;
		}
	}
	
	// Store the updated style keys
	$wpUnited->commit_style_keys();
	
	// add link to reset stylesheet
	$reset = "<link href=\"{$wpUnited->get_plugin_url()}theme/reset.css\" rel=\"stylesheet\" media=\"all\" type=\"text/css\" />";
	$wpUnited->set_inner_headinfo($reset . $wpUnited->get_inner_headinfo());

	//write out the modified stylesheet links
	$wpUnited->set_inner_headinfo(str_replace($innerSSLinks['links'], $innerSSLinks['replacements'], $wpUnited->get_inner_headinfo()));
	
	if ($wpUnited->get_setting('templateVoodoo')) {
		$wpUnited->set_outer_content(str_replace($outerSSLinks['links'], $outerSSLinks['replacements'], $wpUnited->get_outer_content()));
	}
	
	/**
	 * Elements (mainly third-party BBCodes) with height="" cannot override the height CSS rule set in CSS Magic's reset.css
	 * This adds an inline CSS style attribute to any such elements
	 * If the element already has an inline style attribute, the height rule will be appended to it
	 */
	$withInline = preg_replace_callback(
		'/((<[^>]+\b)(?=height\s?=\s?[\'"]?\s?([0-9]+)\s?[\'"]?)([^>]*?))(\/?\s*>)/',
		create_function(
			'$m',
			'if(preg_match(\'/(style\s?=\s?[\\\'"]([^\\\'"]+))([\\\'"])/\', $m[1], $r)) 
				return  str_replace($r[0], "{$r[1]};height:{$m[3]}px;{$r[3]}", $m[1]) . $m[5];
			return $m[1] . \' style="height:\' . $m[3] . \'px;" \' . $m[5];'
		),
		$wpUnited->get_inner_content()
	);
	
	$wpUnited->set_inner_content($withInline);
	

	
}




/**
 * Modify links in header to stylesheets to use CSS Magic instead
 * @param string $headerInfo The snipped head of the page (By reference)
 * @param mixed $position Set to "inner" if we are processing HEAD of the application that is destined
 * for the inner portion of the page (defaults to "outer")
 * @return array an array of stylesheet links and modifications
 */
function wpu_get_stylesheet_links($headerInfo, $position='outer') {
	global $phpbb_root_path, $wpUnited, $phpbbForum;
	
	$wpuCache = WPU_Cache::getInstance();

	$package = ($position == 'outer') ? $wpUnited->get_outer_package() : $wpUnited->get_inner_package();
	$pkg = 'pkg=' . $package;
	$pos = "pos=" . $position;
	
	// grep all styles
	preg_match_all('/<link[^>]*?href=[\'"][^>]*?(style\.php\?|\.css)[^>]*?\/>/i', $headerInfo, $matches);
	preg_match_all('/@import url\([^\)]+?\)/i', $headerInfo, $matches2);
	preg_match_all('/@import "[^"]+?"/i', $headerInfo, $matches3);
	$matches = array_merge($matches[0], $matches2[0], $matches3[0]);
	$links = array(); $repl = array(); $keys = array();
	if(is_array($matches)) {
		
		foreach($matches as $match) {
			
			// ignore wp-united widget islands
			if(strstr($match, 'island=1') !== false) {
				if(strstr($match, 'usecssm=1') !== false) {
					continue;
				}
			}
			
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
				$els = explode("href", $match); 		// split around href, RHS will get ="xxxx" foo="bar"
				$els = explode('=', $els[1]); 			// could also split = in stylesheet URL ..
				array_shift($els); 						// so ditch start and rejoin
				$els = implode('=', $els); 
				
				
				// elements of a stylesheet tag could be delimited by ",' or ' ', in that order of likelihood:
				$delimChars = array('"', "'", ' ');
				foreach($delimChars as $delimChar) {
					if(strpos($els, $delimChar) !== FALSE) {
						$els = explode($delimChar, $els);
						break;
					}
				}
				$el = str_replace($delimChars, "", $els[1]);  
				$and = '&amp;';
			}
			$tv = (($position == 'inner') && ($wpUnited->get_setting('templateVoodoo'))) ? "{$and}tv=" : '';
			if(stristr($el, ".css") !== false) {
				/**
				 * We need to ensure the stylesheet maps to a real file on disk as fopen_url will not work on most 
				 * servers.
				 * We try various methods to find the file
				 */
				// Absolute path to CSS, in phpBB
				if(stristr($el, $phpbbForum->get_board_url()) !== false) {
					$cssLnk = str_replace($phpbbForum->get_board_url(), "", $el); 
				// Absolute path to CSS, in WordPress
				} elseif(stristr($el, $wpUnited->get_wp_base_url()) !== false) {
					$cssLnk = str_replace($wpUnited->get_wp_base_url(), $wpUnited->get_wp_path(), $el);
				} else {
					// else: relative path
					$cssLnk = $phpbb_root_path . $el;
				}
				// remove query vars
				$cssLnk = explode('?', $cssLnk);
				$cssLnk = $cssLnk[0];
				$cssLnk = (stristr( PHP_OS, "WIN")) ? str_replace("/", "\\", $cssLnk) : $cssLnk;
				
				if(  (stristr($cssLnk, 'http:') === false) && (stristr($cssLnk, 'https:') === false) && @file_exists($cssLnk) ) { 
					$links[] = $el;
					$cssLnk = realpath($cssLnk);
					$key = $wpuCache->issue_style_key($cssLnk, $position);
					$keys[] = $key;
					$repl[] = "{$phpbbForum->get_board_url()}wp-united/style-fixer.php?usecssm=1{$and}style={$key}{$and}{$pos}{$and}{$pkg}{$tv}";
				}
			} elseif(stristr($el, "style.php?") !== false) {
				/**
				 * phpBB style.php css
				 */ 
				$links[] = $el; 
				$key = $wpuCache->issue_style_key($el, $position);
				$keys[] = $key;
				$repl[] = "{$el}{$and}usecssm=1{$and}{$pos}{$and}cloc={$key}{$and}{$pkg}{$tv}";
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
	preg_match_all('/<style type=\"text\/css\"(.*?)>(.*?)<\/style>/', $content, $cssStr);

	foreach($cssStr[2] as $index => $c) {
		$cssFixed = str_replace(array('<!--', '-->'), '', $c);
		if(!empty($cssFixed)) {
			$css['css'][] = $cssFixed;
			$css['orig'][] = $c; 
		}
	}
	return $css;
}

/**
 * Cleans up relative URLs in stylesheets so that they still work even through style-fixer
 * @param string $filePath the path to the current file
 * @param string $css a string containing valid CSS to be modified
 */
function wpu_fix_css_urls($filePath, &$css, $pkg='wp') {
	global $phpbb_root_path, $wpUnited, $phpbbForum;
	require_once($wpUnited->get_plugin_path() . 'functions-general.php');
	$relPath = wpu_compute_path_difference($filePath, realpath(add_trailing_slash(getcwd()) . 'style-fixer.php'));
	
	$urlToCssfile = false;
	if($pkg == 'phpbb') {
		$urlToCssFile = str_replace(realPath($phpbb_root_path), $phpbbForum->get_board_url(), dirname(filePath));
	} else if($pkg == 'wp') {
		$urlToCssFile = str_replace(realPath($wpUnited->get_wp_path()), $wpUnited->get_wp_base_url(), dirname($filePath));
	}
	if($urlToCssFile) {
		$urlToCssFile = explode('/', str_replace('\\', '/', $urlToCssFile));
	}
	preg_match_all('/url\(.*?\)/', $css, $urls);
	if(is_array($urls[0])) {
		foreach($urls[0] as $url) {
			$replaceUrl = false;
			
			if((stristr($url, "http:") === false)  && (stristr($url, "https:") === false)) {
			
				$out = str_replace(array('url', '(', ')', "'", '"', ' '), '', $url);
				if ($out != "/") {
					$replace = true;
				}
			}
			if ($replace) {
			
				// We try to sub in the absolute URL for the file path. If that fails then we use the computed relative path difference.
				if((stristr($out, "http:") === false)  && (stristr($url, "https:") === false)) {
					if($urlToCssFile) {
						$urlParts = explode('/', $out);
						$canModify = true;
						$result = $urlToCssFile;
						foreach($urlParts as $part) {
							if (($part == '.') || ($part == '')) {
								continue;
							} else if ($part == '..') {
								if(!sizeof($result)) {
									$canModify = false;
									break;
								}
								array_pop($result);
							} else {
								$result[] = $part;
							}
						}
						if($canModify) {
							$out = implode('/', $result);
						}	
					}
					
					if((stristr($out, "http:") === false)  && (stristr($url, "https:") === false)) {
						$out = $relPath.$out;
					}
										
					$css = str_replace($url, "url('{$out}')", $css);
				}
			}
		}
	}
}

function apply_template_voodoo(&$cssMagic, $tplVoodooKey) {
	
	
	$wpuCache = WPU_Cache::getInstance();
	
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

// Done. End of file.