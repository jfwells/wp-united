<?php
/** 
*
* WP-United CSS Magic style call backend
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

/**
 * Process the inbound args
 */

if(!isset($_GET['usecssm'])) exit;
if(!isset($_GET['style'])) exit;

/**
 * We load in a simplified skeleton phpBB, based on the code in style.php
 * We just need enough to get $config filled so we can get our cache salt and unencrypt the passed strings.
 * @todo move to phpBB abstraction layer $phpbb->load_simple();
 */

// Report all errors, except notices
error_reporting(E_ALL ^ E_NOTICE);
require($phpbb_root_path . 'config.' . $phpEx);

if (!defined('PHPBB_INSTALLED') || empty($dbms) || empty($acm_type)) {
	exit;
}

if (version_compare(PHP_VERSION, '6.0.0-dev', '<')) {
	@set_magic_quotes_runtime(0);
}

// Include files
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
require($phpbb_root_path . 'includes/cache.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/functions.' . $phpEx);

$db = new $sql_db();
$cache = new cache();

// Connect to DB
if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false)) {
	exit;
}
unset($dbpasswd);

$config = $cache->obtain_config();
$user = false;



/**
 * Initialise variables
 */
$pos = (request_var('pos', 'outer') == 'inner') ? 'inner' : 'outer';

$cssFileToFix = request_var('style', 0);

$useTV = -1;
if(isset($_GET['tv']) && $pos == 'inner') { 
	$useTV = request_var('tv', -1);
}



// We load the bare minimum to get our data
require($phpbb_root_path . 'wp-united/functions-css-magic.' . $phpEx);

require($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 

require($phpbb_root_path . 'wp-united/version.' . $phpEx);
require($phpbb_root_path . 'wp-united/cache.' . $phpEx);
$wpuCache = WPU_Cache::getInstance();


$cssFileToFix = $wpSettings['styleKeys'][$cssFileToFix];

/*
 * Some rudimentary additional security
 */
$cssFileToFix = str_replace("http:", "", $cssFileToFix);
$cssFileToFix = str_replace("//", "", $cssFileToFix);
$cssFileToFix = str_replace("@", "", $cssFileToFix);
$cssFileToFix = str_replace(".php", "", $cssFileToFix);


if(file_exists($cssFileToFix)) {
	/**
	 * First check cache
	 */
	$css = '';
	if($useTV > -1) {
		// template voodoo-modified CSS already cached?
		if($cacheLocation = $wpuCache->get_css_magic($cssFileToFix, $pos, $useTV)) {
			$css = @file_get_contents($cacheLocation);
		}
	} else {
		// Try loading CSS-magic-only CSS from cache
		if($cacheLocation = $wpuCache->get_css_magic($cssFileToFix, $pos, -1)) {
			$css = @file_get_contents($cacheLocation);
		}
	}
	
	// Load and CSS-Magic-ify the CSS file. If an outer file, just cache it
	if(empty($css)) {
		require($phpbb_root_path . 'wp-united/css-magic.' . $phpEx);
		$cssMagic = CSS_Magic::getInstance();
		if($cssMagic->parseFile($cssFileToFix)) {

			if($pos=='inner') {
				// Apply Template Voodoo
				if($useTV > -1) {
					if(!apply_template_voodoo($cssMagic, $useTV)) {
						// set useTV to -1 so that cache name reflects that we weren't able to apply TemplateVoodoo
						$useTV = -1;
					}
				}	
				// Apply CSS Magic
				$cssMagic->makeSpecificByIdThenClass('wpucssmagic', false);
			}
			$css = $cssMagic->getCSS();
			$cssMagic->clear();
	
			//fix relative URLs in the CSS
			wpu_fix_css_urls($cssFileToFix, $css);
		}
			
		//cache fixed CSS
		$wpuCache->save_css_magic($css, $cssFileToFix, $pos, $useTV);
		


	}
		
	$expire_time = 7*86400;
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $expire_time));
	header('Content-type: text/css; charset=UTF-8');

	echo $css;
	
}

if (!empty($cache)) {
	$cache->unload();
}
$db->sql_close();

?>