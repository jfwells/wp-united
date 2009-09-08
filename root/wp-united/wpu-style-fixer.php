<?php

// DO NOT PLACE THIS ON A SITE OPEN TO THE INTERNET!!! 
// -- iT *IS* SUBJECT TO PATH TRAVERSAL HACKING. UNDER DEVELOPMENT

$phpbb_root_path = "../";
define('IN_PHPBB', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);

if(!isset($_GET['usecssm'])) exit;
if(!isset($_GET['style'])) exit;

$pos = "outer";
if(isset($_GET['pos'])) {
	$pos = ($_GET['pos'] == 'inner') ? 'inner' : 'outer';
}





$cssFileToFix = (string) $_GET['style'];
$cssFileToFix = base64_decode(urldecode($cssFileToFix));
$cacheLocation = explode('?', $cssFileToFix);
$cacheLocation = urlencode(base64_encode($cacheLocation[0]));
$cssFileToFix = html_entity_decode($cssFileToFix); 
// Some rudimentary security, but we really need to be reading in WP-United options, getting the path to WordPress
// and phpBB templates, and then ensuring the passed file corresponds to them.
// Don't rely on the below to protect your server
$cssFileToFix = str_replace("http:", "", $cssFileToFix);
$cssFileToFix = str_replace("//", "", $cssFileToFix);
$cssFileToFix = str_replace("@", "", $cssFileToFix);
$cssFileToFix = str_replace(".php", "", $cssFileToFix);
$cssFileToFix = str_replace("../../", "", $cssFileToFix); // temporary -- will kill some setups where wordpress is somewhere else

if(!file_exists($cssFileToFix)) $cssFileToFix = $phpbb_root_path . $cssFileToFix;




if(file_exists($cssFileToFix)) {


	$useTV = '';
	if(isset($_GET['tv']) && $pos == 'inner') { 
		$useTV = $_GET['tv'];
		//prevent path traversal
		$useTV = str_replace(array('/', '\\', '..', ';', ':'), '', $useTV);
	}
	$css = '';
	// first check caches (TODO: port to cache class):
	if(!empty($useTV)) {
		// template voodoo-modified CSS already cached?
		if(file_exists($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$useTV}.cssmtv")) {
			$css = @file_get_contents($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$useTV}.cssmtv");
		}
	} else {
		// No template voodoo needed -- check for plain cache
		if(file_exists($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm")) {
			$css = @file_get_contents($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm");
		}
	}

	if(empty($css)) {
		//include($phpbb_root_path . 'common.' . $phpEx);
		include($phpbb_root_path . 'wp-united/wpu-helper-funcs.' . $phpEx);
		include($phpbb_root_path . 'wp-united/wpu-css-magic.' . $phpEx);
		$cssMagic = CSS_Magic::getInstance();
		if($cssMagic->parseFile($cssFileToFix)) {

			if($pos=='inner') {
				
				// Apply Template Voodoo
				if(!empty($useTV)) {
					
					$tvCacheLoc = $phpbb_root_path . "wp-united/cache/" . $useTV;
						
					if(file_exists($tvCacheLoc)) { 
						$templateVoodoo = @file_get_contents($tvCacheLoc);
						$templateVoodoo = @unserialize($templateVoodoo);

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
					}
				
				}				
				
				// Apply CSS Magic
				$cssMagic->makeSpecificByIdThenClass('wpucssmagic', false);
			}
			$css = $cssMagic->getCSS();
			$cssMagic->clear();
	
			//clean up relative urls
	
			//We need to find the absolute URL to the image dir. We can infer it by comparing the
			// current path (wp-united/wpu-style-fixer) against the provided one.
	
			$absCssLoc = clean_path(realpath($cssFileToFix));
			$absCurrLoc = add_trailing_slash(clean_path(realpath(getcwd())));
	
	
			$pathSep = (stristr( PHP_OS, "WIN")) ? "\\": "/";
			$absCssLoc = explode($pathSep, $absCssLoc);
			$absCurrLoc = explode($pathSep, $absCurrLoc);
	
			array_pop($absCssLoc);
	
			while($absCurrLoc[0]==$absCssLoc[0]) { 
				array_shift($absCurrLoc);
				array_shift($absCssLoc);
			}
			$pathsBack = array(".");
			for($i=0;$i<(sizeof($absCurrLoc)-1);$i++) {
				$pathsBack[] = "..";
			}
			$relPath = add_trailing_slash(implode("/", $pathsBack)) . add_trailing_slash(implode("/", $absCssLoc));
	
	
			preg_match_all('/url\(.*?\)/', $css, $urls);
			if(is_array($urls[0])) {
				foreach($urls[0] as $url) {
					$replaceUrl = false;
					if(stristr($url, "http:") === false) {
						$out = str_replace("url(", "", $url);
						$out = str_replace(")", "", $out);
						$out = str_replace("'", "", $out);
						if ($out[0] != "/") {
							//$out = $phpbb_root_path . dirname($cssFileToFix) . "/" . $out;
							$replace = true;
						}
					}
					if ($replace) {
						$css = str_replace($url, "url('{$relPath}{$out}')", $css);
					}
				}
	
			}

		}
			
		//cache fixed CSS
		$fnTemp = $phpbb_root_path . "wp-united/cache/" . 'temp_' . floor(rand(0, 9999)) . 'cssmcache';
		
		$lastPart = (!empty($useTV)) ? "{$useTV}.cssmtv" : "{$pos}.cssm";
		
		$fnDest = $phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$lastPart}";
		$hTempFile = @fopen($fnTemp, 'w+');
		
		@fwrite($hTempFile, $css);
		@fclose($hTempFile);
		@copy($fnTemp, $fnDest);
		@unlink($fnTemp);	


	}
	
	$reset = '';
	if($pos == 'inner') {
		$reset = @file_get_contents($phpbb_root_path . "wp-united/theme/reset.css");
	}
	
	$expire_time = 7*86400;
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $expire_time));
	header('Content-type: text/css; charset=UTF-8');

	echo $reset . $css;
}


?>
