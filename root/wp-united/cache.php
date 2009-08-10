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
    		$this->_useCoreCache = 'UNKNOWN';
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
	//	CORE CACHE ENABlED
	//	----------------------
	//	Returns whether the template cache should be used
	//    	
    	function core_cache_enabled() {
    		if (defined('WPU_CORE_CACHE_ENABLED') && WPU_CORE_CACHE_ENABLED) {
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
		if ( !defined('WPU_REVERSE_INTEGRATION') ) {
			return false;
		}
		if(!$this->template_cache_enabled()) {
			return false;
		}

		switch($this->_useTemplateCache) {
			case "USE":
				return true;
				break;
			case "REFRESH":
				return false;
				break;
			default:
				
				@$dir = opendir($this->baseCacheLoc);
				$cacheFound = false;
				global $wpuAbs;
				while( $entry = @readdir($dir) ) {
					if ( strpos($entry, '.wpucache') ) {
						$parts = str_replace('.wpucache', '', $entry);
						$parts = explode('-', $parts);
						if ($parts[2] == $wpuAbs->wpu_ver) {
							$cacheFound = true;
							$theme = $parts[0];
							$this->templateCacheLoc = $this->baseCacheLoc . $entry;
						}
					}
				}
				if ($cacheFound) { // refresh cache if it is older than theme file, or if WP has been upgraded.
					
					$fileAddress = $this->themePath . $theme;
					$compareDate = filemtime($this->templateCacheLoc);
					if ( !( ($compareDate < @filemtime("$fileAddress/header.$phpEx")) || 
					  ($compareDate < @filemtime("$fileAddress/footer.$phpEx")) ||
					  ($compareDate < @filemtime($this->wpVersionLoc)) ) ) {
						$this->_useTemplateCache = "USE";
						// Since the cache isn't being used, WordPress won't run. We can
						// set some useful global variables and defines from the filename
						// They shouldn't be relied upon, but they're useful for various things
						define('TEMPLATEPATH', $fileAddress);
						global $wp_version;
						$wp_version = $parts[1];
						return true;
					}
				} 
				$this->_useTemplateCache = "REFRESH";
				return false;			
		
		}



	}
	
	//
	//	USE CORE CACHE
	//	----------------------
	//	Decides whether to use, or recreate the core cache
	//
	function use_core_cache($wpuVer, $wpVer, $compat) {
		global $latest;
		
		if($latest) {
			return false;
		}
		
		switch($this->_useCoreCache) {
			case "USE":
				return true;
				break;
			case "REFRESH":
				return false;
				break;
			default:
				
				@$dir = opendir($this->baseCacheLoc);
				$compat = ($compat) ? "_fast" : "_slow";
				while( $entry = @readdir($dir) ) {
					if ( $entry == "core.wpucorecache-{$wpVer}-{$wpuVer}{$compat}.php") {
						$entry = $this->baseCacheLoc . $entry;
						$compareDate = filemtime($entry);
						if ( !($compareDate < @filemtime($this->wpVersionLoc))  ) {
							$this->coreCacheLoc = $entry;
							$this->_useCoreCache = "USE";
							return true;
						}
					}
				}
				$this->_useCoreCache = "REFRESH";
				return false;
		}	
				
				
		
			
	
	}
	
	//
	//	SAVE TO TEMPLATE CACHE
	//	----------------------
	//	Saves template header/footer to disk
	//	
	function save_to_template_cache($wpuVer, $wpVer, $content) {
		
		if ( $this->template_cache_enabled() ) {
			$theme = array_pop(explode('/', TEMPLATEPATH)); 
			$fnTemp = $this->baseCacheLoc . 'temp_' . floor(rand(0, 9999)) . 'cache';
			$fnDest = $this->baseCacheLoc . $theme. "-{$wpVer}-{$wpuVer}.wpucache";
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
	//	SAVE TO CORE CACHE
	//	----------------------
	//	Saves WordPress core to disk
	//	
	function save_to_core_cache($content, $wpuVer, $wpVer, $compat) {
		
		if ( $this->core_cache_enabled() ) {
			$compat = ($compat) ? "_fast" : "_slow";
			$fnTemp = $this->baseCacheLoc . 'temp_' . floor(rand(0, 9999)) . 'wpucorecache-' . $this->wpVersion . '-' . $wpuAbs->wpu_ver . $compat . '.php';
			$fnDest = $phpbb_root_path . "wp-united/cache/core.wpucorecache-{$wpVer}-{$wpuVer}{$compat}.php";
			$hTempFile = @fopen($fnTemp, 'w+');
			@fwrite($hTempFile, '<' ."?php\n\n if(!defined('IN_PHPBB')){die('Hacking attempt');exit();}\n\n$content\n\n?" . '>');
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
