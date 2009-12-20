<?php
/** 
*
* WP-United Main Integration  -- template portion
*
* @package WP-United
* @version $Id: integrator.php,v0.8.0 2009/12/20 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* 
* Here we modify and integrate templates as necessary. When this file is called, we have the following:
* WordPress only: WordPress is in $innerContent
* phpBB-in-WordPress: WordPress is in $outerContent, $phpBB in in $innerContent
* WordPress-in-phpBB: phpBB is in $outerContent, $phpBB is in $innerContent
* 
* In addition:
* $$wpContentVar always points to the WordPress content
* $$phpBBContentVar always points to the phpBB content
* 
*/

/**
 * Get phpBB header/footer
 */
if ( ($wpSettings['showHdrFtr'] == 'FWD') && (!$wpuNoHead) && (!defined('WPU_REVERSE_INTEGRATION')) ) {

	//export header styles to template - before or after phpBB's CSS depending on wpSettings.
	// Since we might want to do operations on the head info, 
	//we just insert a marker, which we will substitute out later
	$wpStyleLoc = ( $wpSettings['cssFirst'] == 'P' ) ? 'WP_HEADERINFO_LATE' : 'WP_HEADERINFO_EARLY';
	$template->assign_vars(array($wpStyleLoc => "<!--[**HEAD_MARKER**]-->"));
	
	$wpuAbs->add_template_switch('S_SHOW_HDR_FTR', TRUE);
	// We need to set the base HREF correctly, so that images and links in the phpBB header and footer work properly
	$wpuAbs->add_template_switch('PHPBB_BASE', $scriptPath);
	
	
	// If the user wants CSS magic, we will need to inspect the phpBB Head, so we buffer the output 
	ob_start();
	page_header("[**PAGE_TITLE**]");
	
	
	$template->assign_vars(array(
		'WORDPRESS_BODY' => "<!--[**INNER_CONTENT**]-->",
		'WP_CREDIT' => sprintf($wpuAbs->lang('WPU_Credit'), '<a href="http://www.wp-united.com" target="_blank">', '</a>'))
	); 
	
	//Stop phpBB from exiting
	define('PHPBB_EXIT_DISABLED', true);

	$wpuAbs->show_body('blog');
	
	//restore the DB connection that phpBB tried to close
	$GLOBALS['db'] = $GLOBALS['bckDB'];
	$GLOBALS['cache'] = $GLOBALS['bckCache'];
	
	$outerContent = ob_get_contents();
	
	ob_end_clean();
}


//$sizeUsed = (strlen($outerContent) + strlen($innerContent)) / 1024;


// Add copyright comment to the bottom of the page. It is also useful as a quick check to see if users actually have
// WP-United installed.
$copy = "\n\n<!--\n phpBB <-> WordPress integration by John Wells, (c) 2006-2009 www.wp-united.com \n-->\n\n";
$innerContent = $innerContent . $copy;


/**
 * Clean up the WordPress body content as necessary
 */

// Some trailing slashes are hard-coded into the WP templates. We don't want 'em.
$$wpContentVar = str_replace(".$phpEx/?",  ".$phpEx?", $$wpContentVar);
$$wpContentVar = str_replace(".$phpEx/\"",  ".$phpEx\"", $$wpContentVar);

// re-point login/out links
if ( !empty($wpSettings['integrateLogin']) ) {
	$login_link = 'ucp.'.$phpEx.'?mode=login&amp;sid=' . $phpbb_sid . '&amp;redirect=';

	$$wpContentVar = str_replace("$siteurl/wp-login.php?redirect_to=", $scriptPath . $login_link, $$wpContentVar);
	$$wpContentVar = str_replace("$siteurl/wp-login.php?redirect_to=", $scriptPath . $login_link, $$wpContentVar);
	$$wpContentVar = str_replace("$siteurl/wp-login.php?action=logout", $scriptPath . $logout_link, $$wpContentVar);
}

/**
 * @todo wpuNoHead for embedding in portals should be dealt with here
 */


/**
 * Output WordPress -- If this is a plain WordPress page, we can just output it here.
 */
if ( !defined('WPU_REVERSE_INTEGRATION') && !($wpSettings['showHdrFtr'] == 'FWD') ) {
	wpu_output_page($$wpContentVar);
	unset($outerContent); unset($innerContent);
}


/** 
 * Make modifications to $innerContent, and extract items for interleaving into $outerContent <head>
 */
if ( defined('WPU_REVERSE_INTEGRATION') || ($wpSettings['showHdrFtr'] == 'FWD') )  { // phpBB is inner:

	//Get ltr, rtl & bgcolor, etc, from the body tag
	preg_match('/<body[^>]+>/i', $innerContent, $pfBodyMatches);
	if($pfBodyMatches[0]) {
		$bodyDetails = trim(str_replace(array("<body", ">"), "", $pfBodyMatches[0]));
		preg_match('/class\s*=\s*"[^"]+"/', $bodyDetails, $bodyClass);
		if($bodyClass[0]) {
			$bodyDetails = str_replace($bodyClass[0], "", $bodyDetails);
			$bodyClass=trim(str_replace(array("class", "=", " ", '"'), "", $bodyClass[0]));
		}
	}
	// $innerContent is passed by reference -- the <head> is removed during the process, leaving us with an insertable body (hehe).
	$innerHeadInfo = process_head($innerContent);
	
	
	process_body($innerContent);
} 

if (defined('WPU_REVERSE_INTEGRATION')) {
	//Remove the phpBB header if required, preserving the search box. This still needs more work
	if ( !empty($wpSettings['fixHeader']) && !DISABLE_HEADER_FIX ) {
		global $pHeadRemSuccess, $srchBox;
		if(preg_match('/<div id="search-box">[\s\S]*?<\/div>/', $innerContent, $srchBox)) {
			$srchBox = $srchBox[0];
		}
		$token = '/<div class="headerbar">[\S\s]*?<div class="navbar">/';		$innerContent2 = preg_replace($token, '<br /><div class="navbar">', $innerContent, 1);
		$pHeadRemSuccess = ($innerContent2 != $innerContent); // count paramater to preg_replace only available in php5 :-(
		$innerContent = $innerContent2; unset($innerContent2);
	}
	
	// replace outer title with phpBB title
	$outerContent = preg_replace('/<title>[^<]*<\/title>/', '<title>[**PAGE_TITLE**]</title>', $outerContent);
}


// So, we generate the phpBB outer page if required, then we're all set.


/**
 * CSS MAGIC
 * 
 * Now we have our outer page in $outerContent, and our inner page in $innerContent.
 * 
 * We now identify all the stylesheets and redirect them to CSS Magic.
 * We don't parse them now, as style.php is used for phpBB styles.
 * 
 * Once they have been parsed by CSS Magic, on subsequent run-throughs here, 
 * we can read CSS Magic's cache and look at the stylesheets for conflicting 
 * classes and IDs. Then, we modify the templates accordingly, and instruct CSS Magic
 * to make additional changes to the CSS the next time around.
 */
if (!empty($wpSettings['cssMagic'])) {

	include($phpbb_root_path . 'wp-united/css-magic.' . $phpEx);

	/** 
	 * Get all links to stylesheets, and prepare appropriate replacement links to insert into the page content
	 * The generated CSS links to insert into the HTML will need to carry information for the browser on the 
	 * physical disk location of the CSS files on the server.
	 * 
	 * We cannot allow browsers to just request any file on the server, so get_stylesheet_links pre-approves the
	 * files and stores them in the DB under $wpSettings['styleKeys']. Browsers then only need to know the 
	 * appropriate style key, not the filename
	 */
	
	$innerSSLinks = wpu_get_stylesheet_links($innerHeadInfo, "inner");
	// also grep all inline css out of headers
	$inCSSInner = wpu_extract_css($innerHeadInfo);
	
	

	// TEMPLATE VOODOO
	if (!empty($wpSettings['templateVoodoo'])) {
		
		//For template voodoo, we also need the outer styles
		$outerSSLinks = wpu_get_stylesheet_links($outerContent, "outer");
		$inCSSOuter = wpu_extract_css($outerContent);
		
		// First check if the cached CSS Magic files exist, and insert placeholders for TV cache location if they do
		$foundInner = array();
		$foundOuter = array();

		foreach ($innerSSLinks['keys'] as $index => $key) {
			if($found = $wpuCache->get_css_magic($wpSettings['styleKeys'][$key], "inner", -1)) {
				$foundInner[] = $found;
				$innerSSLinks['replacements'][$index] .=  "[*FOUND*]";
			} else {
				$innerSSLinks['replacements'][$index] .=  "-1";
			}
		}
		foreach ($outerSSLinks['keys'] as $index => $key) {
			if($found = $wpuCache->get_css_magic($wpSettings['styleKeys'][$key], "outer", -1)) {
				$foundOuter[] = $found;
			}
		}	

		/**
		 * Now we create a unique hash based on everything we've found, and use this to 
		 * store our Template Voodoo instructions.
		 * We append the template voodoo hash key to the end of the redirected stylesheets
		 */
		$tplVoodooKey = $wpuCache->get_template_voodoo_key(TEMPLATEPATH, $foundInner, $foundOuter, (array)$inCSSInner['orig'], (array)$innCSSOuter['orig']);	
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
					$innerCSS->parseFile($cacheFile);
				}				
				foreach($inCSSInner['css'] as $index => $css) {
					$innerCSS->parseString($css);
				}
				foreach($inCSSOuter['css'] as $index => $css) {
					$innerCSS->parseString($css);
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
			 * Now, we can modify the page, removing class and ID duplicates from innerContent
			 */
			foreach($classDupes as $dupe) {
				$findClass = substr($dupe, 1); //remove leading '.'
				$innerContent = preg_replace('/(class=["\']([^\s^\'^"]*\s+)*)'.$findClass.'([\s\'"])/', '\\1wpu'.$findClass.'\\3', $innerContent);
			}
			foreach($idDupes as $dupe) {
				$findId = substr($dupe, 1); //remove leading '.'
				$innerContent = preg_replace('/(id=["\']\s*)'.$findId.'([\s\'"])/', '\\1wpu'.$findId.'\\2', $innerContent);
			}
		}
	} // end template voodoo
	
		
	/**
	 * Now we can apply the CSS magic to any inline CSS
	 */
	$useTVStr =  ($wpSettings['templateVoodoo']) ? 'TV' : '';
	$tvKey = ($wpSettings['templateVoodoo']) ? $tplVoodooKey : -1;
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
			if ($wpSettings['templateVoodoo']) {
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
			$result = '<style type="text/css">'  . $result . '</style>';
			$innerHeadInfo = str_replace($inCSSInner['orig'][$index], $result, $innerHeadInfo);
			$numFixes++;
		}
	}
	
	// Store the updated style keys
	$wpuCache->update_style_keys();
	
	// add link to reset stylesheet
	$reset = "<link href=\"{$scriptPath}wp-united/theme/reset.css\" rel=\"stylesheet\" media=\"all\" type=\"text/css\" />";
	$innerHeadInfo = $reset . $innerHeadInfo;

	//write out the modified stylesheet links
	$innerHeadInfo = str_replace($innerSSLinks['links'], $innerSSLinks['replacements'], $innerHeadInfo);
	
	if ($wpSettings['templateVoodoo']) {
		$outerContent = str_replace($outerSSLinks['links'], $outerSSLinks['replacements'], $outerContent);
	}
}


//Wrap $innerContent in CSS Magic, padding, etc.
$padding = '';
if ($wpSettings['phpbbPadding'] != 'NOT_SET') {
	$pad = explode('-', $wpSettings['phpbbPadding']);
	$padding = 'padding: ' . (int)$pad[0] . 'px ' .(int)$pad[1] . 'px ' .(int)$pad[2] . 'px ' .(int)$pad[3] . 'px;';
}
if ($wpSettings['cssMagic']) {
	$wpuOutputPreStr = '<div id="wpucssmagic" style="' . $padding . 'margin: 0;"><div class="wpucssmagic"><div class="' . $bodyClass . '" ' . $bodyDetails . '>';
	$wpuOutputPostStr = '</div></div></div>';
} else {
	$wpuOutputPreStr = '<div style="'. $padding .' margin: 0px;" class="' . $bodyClass . '" ' . $bodyDetails . '>';
	$wpuOutputPostStr = '</div>';
}

// Add lDebug if requested
if ( defined('WPU_DEBUG') && (WPU_DEBUG == TRUE) ) {
	$wpuOutputPreStr = $lDebug . $wpuOutputPreStr;
}


// Substitute in content
if ( defined('WPU_REVERSE_INTEGRATION') || ($wpSettings['showHdrFtr'] == 'FWD') ) {
	$outerContent = str_replace("<!--[**HEAD_MARKER**]-->", $innerHeadInfo, $outerContent); unset($innerHeadInfo);
	$outerContent = str_replace("<!--[**INNER_CONTENT**]-->", $wpuOutputPreStr . $innerContent . $wpuOutputPostStr, $outerContent); unset($innerContent);
	
	
	wpu_output_page($outerContent); unset($outerContent);
}

/*
 * Processes the page head, returns header info to be inserted into the WP or phpBB page head.
 * Removes the head from the rest of the page.
 * @param string $retWpInc The page content for modification, must be passed by reference.
 * @param string $template The phpBB template object
 * @param abstractify $wpuAbs The WP-United phpBB abstraction layer object.
 * @return string the page <HEAD>
 */
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
	$GLOBALS['wpu_page_title'] = trim($wpTitleStr); 
	

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

/**
 * snips content out of a given string, and inserts it into a second string that is returned. 
 * @param string $haystack the page to be modified -- or <head> -- or whatever -- to find items and snip them out. 
 * @param array $findItems stuff to be found, provided as an array of starting_token => ending_token.
 */
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

/**
 * Process the <body> section of the integrated page
 * @param string $pageContent The page to be processed and modified. Must be passed by ref.
 */
function process_body(&$pageContent) {	
	//Process the body section for integrated page

	// With our Base HREF set, any relative links will point to the wrong location. Let's fix them.
	$fullWpURL = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
	$pageContent = str_replace("a href=\"#", "a href=\"$fullWpURL#", $pageContent);

	//cut out any </body> and </html> tags
	$pageContent = str_replace("</body>", "", $pageContent);
	$pageContent = str_replace("</html>", "", $pageContent);
	
	
	return $pageContent;
	
// End of processing of integrated page. 
}

/**
 * Does final clean-up of the integrated page, and sends it to the browser.
 * @param string $content The fully integrated page.
 */
function wpu_output_page(&$content) {

	//Add title back
	global $wpu_page_title;
	$content = str_replace("[**PAGE_TITLE**]", $wpu_page_title, $content);
	
	if(defined('WPU_SHOW_STATS') && WPU_SHOW_STATS) {
		global $wpuScriptTime, $wpuCache;
		$endTime = explode(' ', microtime());
		$endTime = $endTime[1] + $endTime[0];
		$pageLoad = round($endTime - $wpuScriptTime, 4) . " seconds";
	
		$memUsage = (function_exists('memory_get_peak_usage')) ? round(memory_get_peak_usage()/1024, 0) . "kB" : (function_exists('memory_get_usage')) ? round(memory_get_usage() / 1024, 0) . "kB" : "[Not supported on your server]";
		$stats = "<p style='background-color: #999999;color: #ffffff !important;display: block;'><strong style='text-decoration: underline;'>WP-United Statistics </strong><br />Script Time: " . $pageLoad . "<br />Memory usage: " . $memUsage . "<br />" . $wpuCache->get_logged_actions() . "</p>";
		$content = str_replace('</body>', $stats . '</body>', $content);
	
	}
	
	//optional bandwidth tweak -- this section does a bit of minor extra HTML compression by stripping white space.
	// It is unnecessary for gzipped setups, and might be liable to kill some JS or CSS, so it is hidden in options.php
	if ( (defined('WPU_MAX_COMPRESS')) && (WPU_MAX_COMPRESS) ) {
		$search = array('/\>[^\S ]+/s',	'/[^\S ]+\</s','/(\s)+/s');
		$replace = array('>', '<', '\\1');
		$content = preg_replace($search, $replace, $content);
	}	
	
	echo $content;
	// Finally -- clean up
	define('WPU_FINISHED', true);
	garbage_collection();
	exit_handler();
}

/**
 * Modify links in header to stylesheets to use CSS Magic instead
 * @param string $headerInfo The snipped head of the page (By reference)
 * @param mixed $position Set to "inner" if we are processing HEAD of the application that is destined
 * for the inner portion of the page (defaults to "outer")
 * @return array an array of stylesheet links and modifications
 */
function wpu_get_stylesheet_links(&$headerInfo, $position="outer") {
	global $scriptPath, $phpbb_root_path, $wpuCache, $wpSettings;

	// grep all styles
	preg_match_all('/<link[^>]*?href=[\'"][^>]*?(style\.php\?|\.css)[^>]*?\/>/i', $headerInfo, $matches);
	preg_match_all('/@import url\([^\)]+?\)/i', $headerInfo, $matches2);
	preg_match_all('/@import "[^"]+?"/i', $headerInfo, $matches3);
	$matches = array_merge($matches[0], $matches2[0], $matches3[0]);
	$links = array(); $repl = array(); $cacheLinks = array();
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
				if(stristr($el, $scriptPath) !== false) {
					$cssLnk = str_replace($scriptPath, "", $el); 
				// Absolute path to CSS, in WordPress
				} elseif(stristr($el, $wpSettings['wpUri']) !== false) {
					$cssLnk = str_replace($wpSettings['wpUri'], $wpSettings['wpPath'], $el);
				} else {
					// else: relative path
					$cssLnk = $phpbb_root_path . $el;
				}
				
				$cssLnk = (stristr( PHP_OS, "WIN")) ? str_replace("/", "\\", $cssLnk) : $cssLnk;
				
				if( file_exists($cssLnk) && (stristr($cssLnk, "http:") === false) ) { 
					$links[] = $el;
					$cssLnk = realpath($cssLnk);
					$key = $wpuCache->get_style_key($cssLnk, $position);
					$keys[] = $key;
					$repl[] = "{$scriptPath}wp-united/wpu-style-fixer.php?usecssm=1{$and}style={$key}{$and}{$pos}{$tv}";
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




?>