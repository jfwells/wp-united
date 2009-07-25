<?php
/** 
*
* WP-United Cache -- various forms of caching to speed up WP-United and/or reduce resource consumption
*
* @package WP-United
* @version $Id: wp-united/cache.php,v0.8.0 2009/07/25 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*


*/

if ( !defined('IN_PHPBB') ) exit;

class WPU_Cache {

	var $_useTemplateCache;
	var $baseCacheLoc;
	var $themePath;
	var $templateCacheLoc;


	//
	//	GET INSTANCE
	//	----------------------
	//	Makes class a Singleton.
	//	
	function getInstance () {
		static $instance;
		if (!isset($instance)) {
			$instance = new WPU_Cache();
        } 
        	return $instance;
    	}
    	
    	function WPU_Cache() {
    		
    		global $phpbb_root_path, $wpSettings, $phpEx;
    		
    		$this->_useTemplateCache = 'UNKNOWN';
    		$this->baseCacheLoc = $phpbb_root_path . 'wp-united/cache/';
    		$this->themePath = $wpSettings['wpPath'] . 'wp-content/themes/';
    		$this->wpVersionLoc = $wpSettings['wpPath'] . "wp-includes/version.$phpEx";
    	}
    	
	//
	//	TEMPLATE CACHE ENABlED
	//	----------------------
	//	Returns whether the template cache should be used
	//    	
    	function template_cache_enabled() {
    		if (defined('WPU_CACHE_ENABLED') && WPU_CACHE_ENABLED) {
    			return true;
    		}
    		return false;
    	}	


	//
	//	USE TEMPlATE CACHE
	//	----------------------
	//	Decides whether to use, or recreate the template cache
	//

	function use_template_cache() {

		switch($this->_useTemplateCache) {
			case "USE":
				return true;
				break;
			case "REFRESH":
				return false;
				break;
			default:
				
				@$dir = opendir($this->baseCacheLoc);
				$cacheLoc = '';
				$cacheFound = FALSE;
				while( $entry = @readdir($dir) ) {
					if ( strpos($entry, '.wpucache') ) {
						$cacheFound = TRUE;
						$theme = explode('.', $entry);
						$theme = $theme[0];
						$this->templateCacheLoc = $this->baseCacheLoc . $entry;
					}
				}
				if ($cacheFound) { // refresh cache if it is older than theme file, or if WP has been upgraded.
					$fileAddress = $this->themePath . $theme;
					$compareDate = filemtime($wpu_cacheLoc);
					if ( !( ($compareDate < @filemtime("$fileAddress/header.$phpEx")) || 
					  ($compareDate < @filemtime("$fileAddress/footer.$phpEx")) ||
					  ($compareDate < @filemtime($this->wpVersionLoc)) ) ) {
						$this->_useTemplateCache = "USE";
						return true;
					}
				} 
				$this->_useTemplateCache = "REFRESH";
				return false;			
		
		}



	}
	
	//
	//	SAVE TO TEMPLATE CACHE
	//	----------------------
	//	Saves template header/footer to disk
	//	
	function save_to_template_cache($content) {
		
		if ( $this->template_cache_enabled() ) {
			$theme = array_pop(explode('/', TEMPLATEPATH)); 
			$fnTemp = $this->baseCacheLoc . 'temp_' . floor(round(0, 9999)) . 'cache';
			$fnDest = $this->baseCacheLoc . $theme.wpucache;
			$hTempFile = @fopen($fnTemp, 'w+');
			
			@fwrite($hTempFile, $content);
			@fclose($hTempFile);
			@copy($fnTemp, $fnDest);
			@unlink($fnTemp);				
		
			return true;
		}

		return false;

	}

	//
	//	GET FROM TEMPLATE CACHE
	//	----------------------
	//	Retrieves header / footer from disk
	//		
	function get_from_template_cache() {
		if ( $this->template_cache_enabled() && $this->_useTemplateCache) {
			return file_get_contents($this->templateCacheLoc);
		}
	}



}




?>
