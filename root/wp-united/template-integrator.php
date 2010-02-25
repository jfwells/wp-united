<?php
/** 
*
* WP-United Main Integration  -- template portion
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
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
 *  Just output WordPress if $wpuNoHead
 */
if($wpuNoHead) {
	wpu_output_page($$wpContentVar);
}

/**
 * Get phpBB header/footer
 */
if ( ($wpSettings['showHdrFtr'] == 'FWD') && (!$wpuNoHead) && (!defined('WPU_REVERSE_INTEGRATION')) ) {
	//export header styles to template - before or after phpBB's CSS depending on wpSettings.
	// Since we might want to do operations on the head info, 
	//we just insert a marker, which we will substitute out later
	$wpStyleLoc = ( PHPBB_CSS_FIRST ) ? 'WP_HEADERINFO_LATE' : 'WP_HEADERINFO_EARLY';
	
	$template->assign_vars(array(
		$wpStyleLoc => '<!--[**HEAD_MARKER**]-->',
		'S_SHOW_HDR_FTR' => TRUE,
		// We need to set the base HREF correctly, so that images and links in the phpBB header and footer work properly
		'PHPBB_BASE' =>  $phpbbForum->url
	));
	
	
	// If the user wants CSS magic, we will need to inspect the phpBB Head, so we buffer the output 
	ob_start();
	page_header('[**PAGE_TITLE**]');
	
	
	$template->assign_vars(array(
		'WORDPRESS_BODY' => '<!--[**INNER_CONTENT**]-->',
		'WP_CREDIT' => sprintf($user->lang['WPU_Credit'], '<a href="http://www.wp-united.com" target="_blank">', '</a>')
	)); 
	
	//Stop phpBB from exiting
	define('PHPBB_EXIT_DISABLED', true);

	$template->set_filenames(array( 'body' => 'blog.html') ); 
	page_footer();
	
	//restore the DB connection that phpBB tried to close
	$GLOBALS['db'] = $GLOBALS['bckDB'];
	$GLOBALS['cache'] = $GLOBALS['bckCache'];
	
	$outerContent = ob_get_contents();
	
	ob_end_clean();
}


//$sizeUsed = (strlen($outerContent) + strlen($innerContent)) / 1024;


// Add copyright comment to the bottom of the page. It is also useful as a quick check to see if users actually have
// WP-United installed.
$copy = "\n\n<!--\n phpBB <-> WordPress integration by John Wells, (c) 2006-2010 www.wp-united.com \n-->\n\n";
$innerContent = $innerContent . $copy;

/**
 * Clean up the WordPress body content as necessary
 */

// Some trailing slashes are hard-coded into the WP templates. We don't want 'em.
$$wpContentVar = str_replace(".$phpEx/?",  ".$phpEx?", $$wpContentVar);
$$wpContentVar = str_replace(".$phpEx/\"",  ".$phpEx\"", $$wpContentVar);

// re-point unintegrated login/out links
if ( !empty($wpSettings['integrateLogin']) ) {
	$login_link = append_sid('ucp.'.$phpEx.'?mode=login') . '&amp;redirect=';
	$logout_link = append_sid('ucp.'.$phpEx.'?mode=logout') . '&amp;redirect=';
	global $siteUrl;
	$$wpContentVar = str_replace("$siteUrl/wp-login.php?redirect_to=", $phpbbForum->url . $login_link, $$wpContentVar);
	$$wpContentVar = str_replace("$siteUrl/wp-login.php?redirect_to=", $phpbbForum->url . $login_link, $$wpContentVar);
	$$wpContentVar = str_replace("$siteUrl/wp-login.php?action=logout", $phpbbForum->url . $logout_link, $$wpContentVar);
}



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

	require($phpbb_root_path . 'wp-united/css-magic.' . $phpEx);
	require($phpbb_root_path . 'wp-united/functions-css-magic.' . $phpEx);

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
	
	

	/**
	 * Template Voodoo
	 */
	if (!empty($wpSettings['templateVoodoo'])) {
		
		//For template voodoo, we also need the outer styles
		$outerSSLinks = wpu_get_stylesheet_links($outerContent, "outer");
		$inCSSOuter = wpu_extract_css($outerContent);
		
		// First check if the cached CSS Magic files exist, and insert placeholders for TV cache location if they do
		$foundInner = array();
		$foundOuter = array();

		foreach ((array)$innerSSLinks['keys'] as $index => $key) {
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
	$reset = "<link href=\"{$phpbbForum->url}wp-united/theme/reset.css\" rel=\"stylesheet\" media=\"all\" type=\"text/css\" />";
	$innerHeadInfo = $reset . $innerHeadInfo;

	//write out the modified stylesheet links
	$innerHeadInfo = str_replace($innerSSLinks['links'], $innerSSLinks['replacements'], $innerHeadInfo);
	
	if ($wpSettings['templateVoodoo']) {
		$outerContent = str_replace($outerSSLinks['links'], $outerSSLinks['replacements'], $outerContent);
	}
	
	/**
	 * Elements (mainly third-party BBCodes) with height="" cannot override the height CSS rule set in CSS Magic's reset.css
	 * This adds an inline CSS style attribute to any such elements
	 * If the element already has an inline style attribute, the height rule will be appended to it
	 */
	$innerContent = preg_replace_callback(
		'/((<[^>]+\b)(?=height\s?=\s?[\'"]?\s?([0-9]+)\s?[\'"]?)([^>]*?))(\/?\s*>)/',
		create_function(
			'$m',
			'if(preg_match(\'/(style\s?=\s?[\\\'"]([^\\\'"]+))([\\\'"])/\', $m[1], $r)) 
				return  str_replace($r[0], "{$r[1]};height:{$m[3]}px;{$r[3]}", $m[1]) . $m[5];
			return $m[1] . \' style="height:\' . $m[3] . \'px;" \' . $m[5];'
		),
		$innerContent
	);

	
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
 * @return string the page <HEAD>
 */
function process_head(&$retWpInc) {
	global $wpSettings, $template;
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
			$template->assign_var('WP_DTD', $wp_dtd);
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
	if(defined('WPU_BLOG_PAGE')) {
		$fullWpURL = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
		$pageContent = str_replace("a href=\"#", "a href=\"$fullWpURL#", $pageContent);
	}

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
	global $wpuNoHead;
	
	//Add title back
	global $wpu_page_title;
	$content = str_replace("[**PAGE_TITLE**]", $wpu_page_title, $content);

	// Add login debugging if requested
	if ( defined('WPU_DEBUG') && (WPU_DEBUG == TRUE) && !$wpuNoHead ) {
		global $lDebug;
		$content = str_replace('</body>', $lDebug . '</body>', $content);
	}


	// Add stats if requested
	if(defined('WPU_SHOW_STATS') && WPU_SHOW_STATS && !$wpuNoHead) {
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
	if ( (defined('WPU_MAX_COMPRESS')) && (WPU_MAX_COMPRESS) && !$wpuNoHead ) {
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

?>