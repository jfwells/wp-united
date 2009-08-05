<?php

/*

Here we modify and integrate templates as necessary. When this file is called, we have the following:

-- WordPress only: WordPress is in $innerContent
-- phpBB-in-WordPress: WordPress is in $outerContent, $phpBB in in $innerContent
-- WordPress-in-phpBB: phpBB is in $outerContent, $phpBB is in $innerContent

In addition:
-- $$wpContentVar always points to the WordPress content
-- $$phpBBContentVar always points to the phpBB content

*/


//$sizeUsed = (strlen($outerContent) + strlen($innerContent)) / 1024;


// Add copyright comment to the bottom of the page. It is also useful as a quick check to see if users actually have
// WP-United installed.
$copy = "\n\n<!--\n phpBB <-> WordPress integration by John Wells, (c) 2006-2009 www.wp-united.com \n-->\n\n";
$innerContent = $innerContent . $copy;


/********* Clean up the WordPress body content as necessary *************************/

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

// TODO: wpuNoHead here!!!


/**********************************************************************
		OUTPUT PLAIN WORDPRESS
***********************************************************************/
// Now, if this is a plain WordPress page, we can just output it
if ( !defined('WPU_REVERSE_INTEGRATION') && !($wpSettings['showHdrFtr'] == 'FWD') ) {
	wpu_output_page($$wpContentVar);
	unset($outerContent); unset($innerContent);
}


/******** Make modifications to $innerContent, and extract items for interleaving into $outerContent <head> *****/

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
}


// So, we generate the phpBB outer page if required, then we're all set.


/************************ CSS MAGIC **********************************************/

// Now we have our outer page in $outerContent, and our inner page in $innerContent.

// We now use CSS Magic to parse all the related stylesheets. CSS Magic also modifies CSS files when they are requested
// by the browser. This means it can also deal with PHP-created stylesheets. However, looking at them
// now, we can compare all the different selectors and look for conflicts.
//
// This is *very* resource intensive, but should only take place *once*, and then be cached
// The alternative is to inspect the HTML for conflicting classes/IDs, but every page is different, and so caching is
// less efective. This way, there is only one version.

// NEW VERSION -- TEMPORARILY DISABLED
if(defined('USE_CSS_MAGIC') && USE_CSS_MAGIC && (1==1)) {

	// check cache age
	
	
	include($phpbb_root_path . 'wp-united/wpu-css-magic.' . $phpEx);

	// We try to discover all the stylesheet links, and then find them on the server disk
	//$outerStylesheets = get_stylesheet_files($outerContent);
	//$innerStylesheets = get_stylesheet_files($innerHeadInfo);
	$innerHeadInfo = wpu_modify_stylesheet_links($innerHeadInfo, "inner");
//echo $outerContent;	
$outerContent = wpu_modify_stylesheet_links($outerContent, "outer");
	
	// 1 modify stylesheet links as before
	// 2 CSS magic, when generating stylesheets, stores a cache of css content and css keywords
	// 3 here we look for the stored content. See if we have generated a tv cache yet
	// 4 if no tv cache, we load in the css keywords and create it
	// 5 modify the output files and the cached css based on the css keywords

	// so -- on first pass, the page will have fixed css, but template conflicts
	// on subsequent passes, template conflicts will be fixed.
	
	// TODO: NOW WE CAN OUTPUT MODIFIED HEADER, TO SAVE MEMORY
	
	// (TODO: -- not necessary) Modify CSS Magic to be aware of several templates, with the ability to (merge ?) and separate them
	
	// TODO: style.php and other .php stylesheets will still need to go through style-fixer
	
	/*$outerCSS = new CSS_Magic();
	foreach($outerStylesheets as $ss) {
		$outerCSS->parseFile($ss);
	}
	
	$innerCSS = new CSS_Magic();
	foreach($innerStylesheets as $ss) {
		$innerCSS->parseFile($ss);
	}*/
	
	// Detect ID duplicates
	
	// Detect Class duplicates
	
	// modify $innerContent using templateVoodoo
	
	// Save class/ID duplicates in templateVoodoo cache
	
	// Replace stylesheet links with our modified ones -- but don't modify CSS until it is output. That way we
	// can still handle PHP stylesheets.

}
// OLD VERSION -- TEMP
if(defined('USE_CSS_MAGIC') && USE_CSS_MAGIC) {
	//$innerHeadInfo = wpu_modify_stylesheet_links($innerHeadInfo, "inner");
	
}


// Now all is done


//Wrap $innerContent in CSS Magic, padding, etc.
$padding = '';
if ($wpSettings['phpbbPadding'] != 'NOT_SET') {
	$pad = explode('-', $wpSettings['phpbbPadding']);
	$padding = 'padding: ' . (int)$pad[0] . 'px ' .(int)$pad[1] . 'px ' .(int)$pad[2] . 'px ' .(int)$pad[3] . 'px;';
}
if(defined('USE_CSS_MAGIC') && USE_CSS_MAGIC) {
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

function wpu_output_page(&$content) {
//optional bandwidth tweak -- this section does a bit of minor extra HTML compression by stripping white space.
// It is unnecessary for gzipped setups, and might be liable to kill some JS or CSS, so it is hidden in options.php
	if ( (defined('WPU_MAX_COMPRESS')) && (WPU_MAX_COMPRESS) ) {
		$search = array('/\>[^\S ]+/s',	'/[^\S ]+\</s','/(\s)+/s');
		$replace = array('>', '<', '\\1');
		$content = preg_replace($search, $replace, $content);
	}

	//Add title back
	global $wpu_page_title;
	$content = str_replace("[**PAGE_TITLE**]", $wpu_page_title, $content);
	
	echo $content;
}

//
// Modify links in header to stylesheets to use CSS Magic instead
//
function wpu_modify_stylesheet_links($headerInfo, $position="outer") {
	global $scriptPath, $wpSettings, $phpbb_root_path;
	preg_match_all('/<link[^>]*?href=[\'"][^>]*?(style\.php\?|\.css)[^>]*?\/>/i', $headerInfo, $matches);
	preg_match_all('/@import url\([^\)]+?\)/i', $headerInfo, $matches2);
	preg_match_all('/@import "[^"]+?"/i', $headerInfo, $matches3);
	$matches = array_merge($matches[0], $matches2[0], $matches3[0]);
	if(is_array($matches)) {
		$pos = "pos=" . $position;
		foreach($matches as $match) {
			// extract css location
			if(stristr($match, "@import url") !== false) {
				$el = str_replace(array("@import", "(", "url",  ")", " ", "'", '"'), "", $match);
				$and = "&";
			} elseif(stristr($match, "@import") !== false) {
				$el = str_replace(array("@import", "(",  ")", " ", "'", '"'), "", $match);
				$and = "&";
			
			} else {
				$cssLoc = '';
				$stylePhpLoc = '';
				$els = explode("href", $match);
				//an '=' could be in the stylesheet name, so rather than replace, we explode around the first =.
				$els = explode('=', $els[1]);
				array_shift($els);
				$els = implode("=", $els);

				$els = explode('"', $els);
				$el = str_replace(array(" ", "'", '"'), "", $els[1]);
				$and = "&amp;";
			}
			if(stristr($el, ".css") !== false) { 
				$cssLoc = $el;
			} elseif(stristr($el, "style.php?") !== false) {
					$stylePhpLoc = $el;
			}
			if($cssLoc) { // Redirect stylesheet
				$findLoc = $cssLoc;
				// We try to translate the URL to a local path
				// type 1: Absolute path to CSS, in phpBB
				if(stristr($findLoc, $scriptPath) !== false) {
					$findLoc = str_replace($scriptPath, "", $findLoc);
				//type 2: Absolute path to CSS, in WordPress
				} elseif(stristr($findLoc, $wpSettings['wpUri']) !== false) {
					$findLoc = str_replace($wpSettings['wpUri'], $wpSettings['wpPath'], $findLoc);
				}
				// else: relative path
				$findLoc = (stristr( PHP_OS, "WIN")) ? str_replace("/", "\\") : $findLoc;
				if( file_exists($findLoc) && (stristr($findLoc, "http:") === false) ) { 
					$newLoc = "wp-united/wpu-style-fixer.php?usecssm=1{$and}style=" . urlencode(base64_encode(htmlentities($findLoc))) . $and . $pos;
					$headerInfo = str_replace($cssLoc, $newLoc, $headerInfo);
				}
			}
			if($stylePhpLoc) { //  style.php
				$headerInfo = str_replace($stylePhpLoc, $stylePhpLoc . "&amp;usecssm=1&amp;".$pos , $headerInfo);
			}
		}
	}
	return $headerInfo;
}



?>
