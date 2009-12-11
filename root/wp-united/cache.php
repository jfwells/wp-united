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
/**
*  The WP-Unted cache stores data for:
* (a) the WordPress core execution cache; 
* (b) the WordPress header and footer cache for when phpBB is inside WordPress (in 'simple' mode).
* (c) Cached CSS for when CSS Magic is enabled
* (d) Cached template Voodoo instructions
* (e) Cached plugin modifications
*/
class WPU_Cache {

	var $_useTemplateCache;
	var $baseCacheLoc;
	var $themePath;
	var $templateCacheLoc;


	/**
	 *  Makes class a singleton. Class must be invoked through this method
	 */
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
    	
		/**
		 * Determines if the template cache is active
		 * Currently can be enabled/disabled in options.php
		 */
    	function template_cache_enabled() {
    		if (defined('WPU_CACHE_ENABLED') && WPU_CACHE_ENABLED) {
    			return true;
    		}
    		return false;
    	}
    	
	/**
	 * Determine whether the core cache can be used.	
	 */
    	function core_cache_enabled() {
    		if (defined('WPU_CORE_CACHE_ENABLED') && WPU_CORE_CACHE_ENABLED) {
    			return true;
    		}
    		return false;
    	}    		


	/**
	 * Decides whether to use, or regenerate, the cache for WordPress template header and footers.
	 */

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
							$theme = str_replace('__sep__', '-', $parts[0]);
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
	
	/**
	 * Decides whether to use the core cache, or whether it is stale and due for regeneration
	 * @param string $wpuVer WP-United version number
	 * @param string $wpVer WordPress version number
	 * @param bool $compat False if WordPress should be run in compatibility (slow) mode
	 */
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
	
	/**
	 * Saves WordPress header/footer to disk
	 * @param string $wpuVer WP-United version number
	 * @param string $wpVer WordPress version number
	 * @param string All WordPress portions of the page to save, with a delimiter set for where phpBB should be spliced in.
	 */
	function save_to_template_cache($wpuVer, $wpVer, $content) {
		
		if ( $this->template_cache_enabled() ) {
			$theme = str_replace('-', '__sep__', array_pop(explode('/', TEMPLATEPATH))); 
			$fnDest = $this->baseCacheLoc . $theme. "-{$wpVer}-{$wpuVer}.wpucache";
			$this->save($content, $fnDest);			
		
			return true;
		}

		return false;

	}
	
	/**
	 * Saves WordPress core to disk
	 * @param string $wpuVer WP-United version number
	 * @param string $wpVer WordPress version number
	 * @param bool $compat False if WordPress should be run in compatibility (slow) mode
	 */
	function save_to_core_cache($content, $wpuVer, $wpVer, $compat) {
		
		if ( $this->core_cache_enabled() ) {
			$compat = ($compat) ? "_fast" : "_slow";
			$fnDest = $phpbb_root_path . "wp-united/cache/core.wpucorecache-{$wpVer}-{$wpuVer}{$compat}.php";
			$content = '<' ."?php\n\n if(!defined('IN_PHPBB')){die('Hacking attempt');exit();}\n\n$content\n\n?" . '>';
			$this->save($content, $fnDest);
		
			return true;
		}

		return false;

	}	

	/**
	 * Retrieves a WordPress header/footer frim disk, so we can perform a template integration
	 * without having to invoke WordPress at all
	 * use_template_cache() must have already been called to set up cache parameters
	 */		
	function get_from_template_cache() {
		if ( $this->template_cache_enabled() && $this->_useTemplateCache) {
			return file_get_contents($this->templateCacheLoc);
		}
	}


	/**
	 * A general save function, intended as a private member, but can be called publicly for cache types
	 * that are not yet defined
	 * @param string $content The content to save
	 * @param string $fileName The complete path and filename
	 */
	function save($content, $fileName) {
			$fnTemp = $this->baseCacheLoc . 'temp_' . floor(rand(0, 999999)) . '-temp';
			$hTempFile = @fopen($fnTemp, 'w+');
			@fwrite($hTempFile, $content);
			@fclose($hTempFile);
			@copy($fnTemp, $fileName);
			@unlink($fnTemp); 
			return true;
	}
	
	/**
	 * Purge the WP-United cache
	 * Deletes all files from the wp-united/cache directory
	 * @TODO: Implement
	 */
	function purge() {
		
	}
	


}




?>
