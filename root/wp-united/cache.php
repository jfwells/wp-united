<?php
/** 
*
* WP-United Cache -- various forms of caching to speed up WP-United and/or reduce resource consumption
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
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
	var $wpuVer;


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
		
		$this->wpuVer = $GLOBALS['wpuVersion'];
		
		$this->numStyleKeys = sizeof($GLOBALS['wpSettings']['styleKeys']);
					
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
		if ( defined('WPU_PERFORM_ACTIONS') ) {
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
				while( $entry = @readdir($dir) ) {
					if ( strpos($entry, 'theme-') === 0 ) {
						$parts = str_replace('theme-', '', $entry);
						$parts = explode('-', $parts);
						if ($parts[2] == md5("{$this->salt}-{$this->wpuVer}")) {
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
	 * @param string $wpVer WordPress version number
	 * @param bool $compat False if WordPress should be run in compatibility (slow) mode
	 */
	function use_core_cache($wpVer, $compat) {
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
				while( $entry = @readdir($dir) ) {
					if ( $entry == $this->_get_core_cache_name($wpVer, $compat) )  {
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
	 * @param string $wpVer WordPress version number
	 * @param string All WordPress portions of the page to save, with a delimiter set for where phpBB should be spliced in.
	 */
	function save_to_template_cache($wpVer, $content) {
		if ( $this->template_cache_enabled() ) {
			$theme = str_replace('-', '__sep__', array_pop(explode('/', TEMPLATEPATH))); 
			$fnDest = $this->baseCacheLoc . "theme-{$theme}-{$wpVer}-". md5("{$this->salt}-{$this->wpuVer}");
			$this->save($content, $fnDest);
			$this->_log("Generated template cache: $fnDest");		
		
			return true;
		}

		return false;

	}

	/**
	 * Generate core cache name
	 * @access private
	 */
	function _get_core_cache_name($wpVer, $compat) {
		global $phpEx;
		$compat = ($compat) ? "_fast" : "_slow";
		return "core-" . md5("{$this->salt}-{$wpVer}-{$this->wpuVer}{$compat}") . ".{$phpEx}";
	}

	/**
	 * Saves WordPress core to disk
	 * @param string $wpVer WordPress version number
	 * @param bool $compat False if WordPress should be run in compatibility (slow) mode
	 */
	function save_to_core_cache($content, $wpVer, $compat) {
		if ( $this->core_cache_enabled() ) {
			$fnDest = $this->baseCacheLoc . $this->_get_core_cache_name($wpVer, $compat);
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
	function save_plugin($content, $pluginPath, $wpVer, $compat, $addPHP = '') {
		global $phpEx;
		$compat = ($compat) ? "_fast" : "_slow";
		$fnDest = $this->baseCacheLoc . "plugin-" . md5("{$this->salt}-{$pluginPath}-{$wpVer}-{$this->wpuVer}{$compat}") . ".{$phpEx}";
		$content = $this->prepare_content($content, $addPHP); 
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
	function get_plugin($pluginPath, $wpVer, $compat) {
		global $phpEx;
		$lastCompiled = $GLOBALS['config']['wpu_plugins_compiled'];
		$compat = ($compat) ? "_fast" : "_slow";
		$fnPlugin = $this->baseCacheLoc . "plugin-" . md5("{$this->salt}-{$pluginPath}-{$wpVer}-{$this->wpuVer}{$compat}") . ".{$phpEx}";
		if(file_exists($fnPlugin)) {
			if(filemtime($fnPlugin) <= $lastCompiled) {
				return $fnPlugin;
			}
		}
		return false;

	}

	/**
	 * Returns a key number for a CSS file or a CSS magic cache
	 */
	function get_style_key($fileName, $pos) {
		if(stripos($fileName, 'style.php?') !== false) {
			/**
			 * For style.php, we just need to create a style key for the cache
			 */
			$fileName = preg_replace('/sid=[^&]*?&amp;/', '', $fileName);
			$fileName = $this->get_css_magic_cache_name($fileName, $pos);
			return $this->_generate_style_key($fileName);
		} else {
			/**
			 * For css files, we need to create a style key for the filename
			 */
			$fileName = explode('?', $fileName);
			return $this->_generate_style_key($fileName[0]);
		}
	}

	/**
	 * returns a key number for a template voodoo instruction cache
	 */
	function get_template_voodoo_key($path1, $arr1, $arr2, $arr3, $arr4) {		
		$fileName = 'tplvoodoo-' . md5( $this->salt . array_pop(explode('/', $path1)) .  implode('.', $arr1) . implode('.', $arr2) . implode('.', $arr2) . implode('.', $arr3) . "-{$this->wpuVer}");
		return $this->_generate_style_key($fileName);
	}

	/**
	 * gets the Template Voodoo instructions, if they exist
	 */
	function get_template_voodoo($key) {
		global $wpSettings;
		if($key < 0) {
			return false;
		}
		$fileName = $this->baseCacheLoc . $wpSettings['styleKeys'][$key];
		if(file_exists($fileName)) { 
			$templateVoodoo = @file_get_contents($fileName);
			if(!empty($templateVoodoo)) {
				$templateVoodoo = @unserialize($templateVoodoo);
				return $templateVoodoo;
			}
		}
		return false;
	}

	/**
	 * Saves Template Voodoo instructions
	 */
	function save_template_voodoo($contents, $key) {
		global $wpSettings;
		$fileName = $this->baseCacheLoc . $wpSettings['styleKeys'][$key];
		$templateVoodoo = serialize($contents);
		$this->save($templateVoodoo, $fileName);
		$this->_log("Generated Template Voodoo cache: $fileName");
	}
		
	/**
	 * generates a style key, or returns the correct one if it already exists
	 * @access private
	 */
	function _generate_style_key($fileName) {
		global $wpSettings;
		$key = array_search($fileName, (array)$wpSettings['styleKeys']);
		if($key === false) {
			$wpSettings['styleKeys'][] = $fileName;
			$key = sizeof($wpSettings['styleKeys']) - 1;
		}
		return $key;
	}

	/**
	 * Gets the CSS magic cache if it exists
	 */
	function get_css_magic($fileName, $pos, $incTplVoodoo = -1) {
		$cacheFileName =$this->baseCacheLoc . $this->get_css_magic_cache_name($fileName, $pos, $incTplVoodoo);
		if(file_exists($cacheFileName)) {
			if(@filemtime($cacheFileName) > @filemtime($fileName)) {
				return  $cacheFileName;
			}
		}
		return false;
	}

	/**
	 * Generates a name for the CSS Magic Cache
	 */
	function get_css_magic_cache_name($fileName, $pos, $incTplVoodoo = -1) {
		$tpl = ($incTplVoodoo > -1) ? 'tplvd-' : '';
		return "cssmagic-{$tpl}" . md5("{$this->salt}{$fileName }-{$pos}-{$incTplVoodoo}-{$this->wpuVer}") . '.css';
	}

	/**
	 * Saves the CSS Magic 
	 */
	function save_css_magic($content, $fileName, $pos, $incTplVoodoo = -1) {
		$cacheFileName =$this->baseCacheLoc . $this->get_css_magic_cache_name($fileName, $pos, $incTplVoodoo);
		$this->save($content, $cacheFileName);
		$this->_log("Generated CSS Magic cache: $cacheFileName");
	}


	/**
	 * Saves updated style keys to the database.
	 * phpBB $config keys can only store 255 bytes of data, so we usually need to store the data
	 * split over several config keys
	 * @return int the number of config keys used
	 */
	function update_style_keys() {
		global $wpSettings; 
		if(sizeof($wpSettings['styleKeys']) > $this->numStyleKeys) {
			$fullLocs = (base64_encode(serialize($wpSettings['styleKeys'])));
			$currPtr=1;
			$chunkStart = 0;
			while($chunkStart < strlen($fullLocs)) {
				set_config("wpu_style_keys_{$currPtr}", substr($fullLocs, $chunkStart, 255), true);
				$chunkStart = $chunkStart + 255;
				$currPtr++;
			}
			return $currPtr;
		}
		return $this->numStyleKeys;
	}




	/**
	 * Prepares content for saving to cache -- ensuring it can't be called directly, and that it can be properly eval()d
	 */
	function prepare_content($content, $addPHP = '') {
		$addPHP = (!empty($addPHP)) ? "\n\n$addPHP" : '';
		return '<' ."?php\n\n if(!defined('IN_PHPBB')){exit();}$addPHP\n\n$content\n\n?" . '>';
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
		@$dir = opendir($this->baseCacheLoc);
			while( $entry = @readdir($dir) ) {
				if ( (strpos($entry, '.htaccess') === false) && ((strpos($entry, '.txt') === false)) ) {
					if(!is_dir($this->baseCacheLoc . $entry)) {
						@unlink($this->baseCacheLoc . $entry);
					}
				}
			}
			// purge style keys
			global $config, $wpSettings, $db;
			if(isset($config['wpu_style_keys_1'])) {
				$sql = 'DELETE FROM ' . CONFIG_TABLE . ' 
					WHERE config_name LIKE \'wpu_style_keys_%\'';
				@$db->sql_query($sql);
			}
			$wpSettings['styleKeys'] = array();
	}

	
	/** 
	 * Clears the cache of template files. Used when a new template is selected.
	 */
	function template_purge() {
		@$dir = opendir($this->baseCacheLoc);
			while( $entry = @readdir($dir) ) {
				if ( strpos($entry, 'theme-') !== false) {
					if(!is_dir($this->baseCacheLoc . $entry)) {
						@unlink($this->baseCacheLoc . $entry);
					}
				}
			}		
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
		if(sizeof($this->logged)) {
			$strLog = implode('<br />', $this->logged);
		}
		return $strLog;
	}
	
}
?>