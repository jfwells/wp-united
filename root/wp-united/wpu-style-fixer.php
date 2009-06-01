<?php

// DO NOT PLACE THIS ON A SITE OPEN TO THE INTERNET!!! 
// -- iT *IS* SUBJECT TO PATH TRAVERSAL HACKING. UNDER DEVELOPMENT

$phpbb_root_path = "../";
define('IN_PHPBB', 1);

if(!isset($_GET['style'])) exit;

$cssFileToFix = (string) $_GET['style'];
$cssFileToFix = urldecode($cssFileToFix);
$cssFileToFix = html_entity_decode($cssFileToFix);
// Some rudimentary security, but we really need to be reading in WP-United options, getting the path to WordPress
// and phpBB templates, and then ensuring the passed file corresponds to them.
// Don't rely on the below to protect your server
$cssFileToFix = str_replace("http:", "", $cssFileToFix);
$cssFileToFix = str_replace("//", "", $cssFileToFix);
$cssFileToFix = str_replace("@", "", $cssFileToFix);
$cssFileToFix = str_replace(".php", "", $cssFileToFix);
$cssFileToFix = str_replace("../../", "", $cssFileToFix); // temporary -- will kill some setups where wordpress is somewhere else

if(file_exists($phpbb_root_path . $cssFileToFix)) {
	include($phpbb_root_path . 'wp-united/options.php');
	include($phpbb_root_path . 'wp-united/wpu-css-magic.php');
	$cssMagic = CSS_Magic::getInstance();
	if($cssMagic->parseFile($phpbb_root_path . $cssFileToFix)) {
		if(defined('USE_TEMPLATE_VOODOO') && USE_TEMPLATE_VOODOO) {
			$cssMagic->renameIds("wpu");
			$cssMagic->renameClasses("wpu");
		}
		$cssMagic->makeSpecificByIdThenClass('wpucssmagic', false);
		$css = $cssMagic->getCSS();
		$cssMagic->clear();
		//clean up relative urls
		preg_match_all('/url\(.*?\)/', $css, $urls);
		if(is_array($urls[0])) {
			foreach($urls[0] as $url) {
				$replaceUrl = false;
				if(stristr($url, "http:") === false) {
					$out = str_replace("url(", "", $url);
					$out = str_replace(")", "", $out);
					$out = str_replace("'", "", $out);
					if ($out[0] != "/") {
						$out = $phpbb_root_path . dirname($cssFileToFix) . "/" . $out;
						$replace = true;
					}
				}
				if ($replace) {
					$css = str_replace($url, "url('{$out}')", $css);
				}
			}
		
		}
		
		
		/*$css = str_replace("#header", "#wpuheader", $css);
		$css = str_replace("#tab", "#wputab", $css);
		$css = str_replace("#footer", "#wpufooter", $css);
		$css = str_replace("#copyright", "#wpucopyright", $css);*/
	}
	
	$reset = @file_get_contents($phpbb_root_path . "wp-united/theme/reset.css");


	$expire_time = 7*86400;
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $expire_time));
	header('Content-type: text/css; charset=UTF-8');

	echo $reset . $css;

}


?>
