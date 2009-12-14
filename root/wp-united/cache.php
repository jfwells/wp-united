<?php
/** 
*
* WP-United Cache -- various forms of caching to speed up WP-United and/or reduce resource consumption
*
* @package WP-United
* @version $Id: wp-united/cache.php,v0.8.0 2009/07/25 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* @author John Wells
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
* @todo CSSM & Template voodoo caches
*/
class WPU_Cache {

	var $_useTemplateCache;
	var $baseCacheLoc;
	var $themePath;
	var $templateCacheLoc;
	var $salt;
	var $logged;
	var $fullPage;


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
    		$this->fullPage =  !(bool)$wpSettings['wpSimpleHdr'];
    		
    		$this->initialise_salt();
    		
    		$this->log = array();
    		    		
    	}
    	/**
    	 * The salt is used when hashing filenames and is stored poermanently in the phpBB database
    	 * It does not need to be particularly secure, so we still use MD5 -- it will be enough to stop
    	 * script kiddies from guessing filenames in the cache folder
    	 */
    	function initialise_salt() {
			if(!isset( $GLOBALS['config']['wpu_cache_hash'])) {
				// Generate a 10-digit random number
				$this->salt = rand(10000, 99999);
				set_config('wpu_cache_hash', $this->salt);
			} else {
				$this->salt = $GLOBALS['config']['wpu_cache_hash'];
			}
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
		
		if($this->fullPage) {
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
					if ( strpos($entry, 'theme-') === 0 ) {
						$parts = str_replace('theme-', '', $entry);
						$parts = explode('-', $parts);
						if ($parts[2] == md5("{$this->salt}-{$wpuAbs->wpu_ver}")) {
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
		global $latest, $phpEx;
		
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
					if ( $entry == "core-" . md5("{$this->salt}-{$wpVer}-{$wpuVer}{$compat}") . ".{$phpEx}") {
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
	 * When restoring, we won't know variables such as WordPress theme and WordPress version, 
	 * so we only encrypt the things we know (WP-United version).
	 * @param string $wpuVer WP-United version number
	 * @param string $wpVer WordPress version number
	 * @param string All WordPress portions of the page to save, with a delimiter set for where phpBB should be spliced in.
	 */
	function save_to_template_cache($wpuVer, $wpVer, $content) {
		
		if ( $this->template_cache_enabled() ) {
			$theme = str_replace('-', '__sep__', array_pop(explode('/', TEMPLATEPATH))); 
			$fnDest = $this->baseCacheLoc . "theme-{$theme}-{$wpVer}-". md5("{$this->salt}-{$wpuVer}");
			$this->save($content, $fnDest);
			$this->_log("Generated template cache: $fnDest");		
		
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
		global $phpEx;
		if ( $this->core_cache_enabled() ) {
			$compat = ($compat) ? "_fast" : "_slow";
			$fnDest = $this->baseCacheLoc . "core-" . md5("{$this->salt}-{$wpVer}-{$wpuVer}{$compat}") . ".{$phpEx}";
			$content = $this->prepare_content($content); 
			$this->save($content, $fnDest);
			$this->_log("Generated core cache: $fnDest");	
		
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
	 * Saves a "compiled" worked-around plugin
	 * @param string $pluginPath Full path to plugin
	 * @param bool $compat Whether the plugin should be run in compatibility (slow) mode or not.
	 */
	function save_plugin($content, $pluginPath, $wpuVer, $wpVer, $compat) {
		global $phpEx;
		$compat = ($compat) ? "_fast" : "_slow";
		$fnDest = $this->baseCacheLoc . "plugin-" . md5("{$this->salt}-{$pluginPath}-{$wpVer}-{$wpuVer}{$compat}") . ".{$phpEx}";
		$content = $this->prepare_content($content); 
		$this->save($content, $fnDest);
		$this->_log("Generated plugin cache: $fnDest");	
		// update plugin compile time
		$GLOBALS['wpUtdInt']->switch_db('TO_P');
		set_config('wpu_plugins_compiled', filemtime($fnDest));
		$GLOBALS['wpUtdInt']->switch_db('TO_W');
		return $fnDest;
	}
	
	/**
	 * Pulls a "compiled" worked-around plugin
	 * and returns the filename, or false if it needs to be created
	 */
	function get_plugin($pluginPath,$wpuVer, $wpVer, $compat) {
		global $phpEx;
		$lastCompiled = $GLOBALS['config']['wpu_plugins_compiled'];
		$compat = ($compat) ? "_fast" : "_slow";
		$fnPlugin = $this->baseCacheLoc . "plugin-" . md5("{$this->salt}-{$pluginPath}-{$wpVer}-{$wpuVer}{$compat}") . ".{$phpEx}";
		if(file_exists($fnPlugin)) {
			if(filemtime($fnPlugin) <= $lastCompiled) {
				return $fnPlugin;
			}
		}
		return false;

	}	
	
	/**
	 * Prepares content for saving to cache -- ensuring it can't be called directly, and that it can be properly eval()d
	 */
	function prepare_content($content) {
		return '<' ."?php\n\n if(!defined('IN_PHPBB')){exit();}\n\n$content\n\n?" . '>';
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
	 * @todo : Implement
	 */
	function purge() {
		
	}
	
	/**
	 * Logs an action
	 * @access private
	 *	@param string $action the action to log
	 */
	function _log($action) {
		$this->logged[] = $action;
	}
	/**
	 * Returns a string for display, with all the instances where a cache file was generated.
	 * If an item is not listed, we can assume it was already cached
	 */
	function get_logged_actions() {
		$strLog = implode('<br />', $this->logged);
		return $strLog;
	}
	
}
?>