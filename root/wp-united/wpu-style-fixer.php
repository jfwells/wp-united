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

	// First check cache (TODO: port to cache class)
	if(file_exists($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm")) {
		$css = @file_get_contents($phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm");

	} else {
		//include($phpbb_root_path . 'common.' . $phpEx);
		include($phpbb_root_path . 'wp-united/options.' . $phpEx);
		include($phpbb_root_path . 'wp-united/wpu-helper-funcs.' . $phpEx);
		include($phpbb_root_path . 'wp-united/wpu-css-magic.' . $phpEx);
		$cssMagic = CSS_Magic::getInstance();
		if($cssMagic->parseFile($cssFileToFix)) {
	
		/*	if(defined('USE_TEMPLATE_VOODOO') && USE_TEMPLATE_VOODOO) {
				if(isset($_GET['tv'])) {
					$tvFile = (string) $_GET['tv'];
					$tvFile = urldecode($tvFile);
					$tvFile = $phpbb_root_path . "wp-united/cache/tvoodoo-" . $tvFile . ".tv";
					if(file_exists($tvFile)) {
						$tvFc = file_get_contents($tvFile);
						$tvFc = unserialize($tvFc);
						$tvIds = $tvFc[0];
						$tvClasses = $tvFc[1];
						$cssMagic->renameIds("wpu", $tvIds);
						$cssMagic->renameClasses("wpu", $tvClasses);
				
					}
				}
			}*/
			if($pos=='inner') {
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
	
	
			/*$css = str_replace("#header", "#wpuheader", $css);
			$css = str_replace("#tab", "#wputab", $css);
			$css = str_replace("#footer", "#wpufooter", $css);
			$css = str_replace("#copyright", "#wpucopyright", $css);*/
		}
			
			//cache fixed CSS
			$fnTemp = $phpbb_root_path . "wp-united/cache/" . 'temp_' . floor(rand(0, 9999)) . 'cssmcache';
			$fnDest = $phpbb_root_path . "wp-united/cache/{$cacheLocation}-{$pos}.cssm";
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
