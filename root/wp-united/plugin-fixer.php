<?php
/** 
*
* WP-United Plugin Fixer
*
* @package Plugin-Fixer
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.7.1[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* @author John Wells
*
* 
*/

if ( !defined('IN_PHPBB') ) exit;
/**
* 	An abstraction layer for loading WordPress plugins. If Plugin fixes are enabled, this layer
* "compiles" plugins (e.g. parses them and modifies them as appropriate to make them compatible
* and then caches the compiled code for execution on successive runs of wordpress.
* 
*/
class WPU_WP_Plugins {
	
	var $compiled;
	var $pluginDir;
	
	var $wpuVer;
	var $wpVer;
	var $compat;
	var $strCompat;
	
	
	
	/**
	 * This class MUST be called as a singleton through this method
	 */
	function getInstance() {
		static $instance;
		if (!isset($instance)) {
			$instance = new WPU_WP_Plugins();
        } 
        	return $instance;
    }		
    
    /**
     * Class constructor
     */
    function WPU_WP_Plugins() {
		$this->compiled = false;
	}
	
	/**
	 * Compiles plugins as appropriate
	 * @param string $wpPluginDir The WordPress plugin directory
	 * @param string $wpuVer WP-United version
	 * @param string $wpVer WordPress version
	 * @param string $compat True if WordPress is in global scope
	 */
	function initialise($wpPluginDir, $wpuVer, $wpVer, $compat) {
		$this->pluginDir = $wpPluginDir;
		//$this->compile_all($this->pluginDir);
		$this->compat = $compat;
		$this->wpuVer = $wpuVer;
		$this->wpVer = $wpVer;
		$this->strCompat = ($this->wpu_compat) ? "true" : "false";
	}
	
	/**
	 * Returns plugin code to execute
	 * @param string $plugin The full path to the plugin
	 */
	function fix($plugin, $includeType = false) {
		global $wpuCache;
		if (stripos($plugin, 'wpu-plugin') === false) {
			if(file_exists($plugin)) {
				$cached = $wpuCache->get_plugin($plugin, $this->wpuVer, $this->wpVer, $this->strCompat);
				if (!$cached) {
					if(!$cached = $this->process_file($plugin)) {
						$cached = $plugin;
					}
				}
				return $cached;
			}
		}
		return $plugin;
	}	
	/**
	 * Compiles all php files found in wp-content/plugins
	 */
	function compile_all($path) {
		global $phpEx, $wpuCache;


		$dir = @opendir($path);

		if (!$dir) {
			return;
		}
		while (($entry = readdir($dir)) !== false) {
			$fullPath = add_trailing_slash($path) . $entry;
			// no guarantees WordPress is using $phpEx extension
			if (((strpos($entry, "." . $phpEx) !== false) || (strpos($entry, ".php") !== false)) && (stripos($entry, 'wpu-plugin') === false)) {
				if((file_exists($fullPath)) && (!$wpuCache->get_plugin($fullPath, $this->wpuVer, $this->wpVer, $this->strCompat))) {
					$this->process_file($fullPath);
				}
			} else if(is_dir($fullPath) && ($entry != '.') && ($entry != '..')) {
				$this->compile_all($fullPath);
			}
		}
		
		closedir($dir);

	}
	
	/**
	 * Process the file
	 * @access private
	 */
	function process_file($pluginLoc) {
		global $phpEx, $wpuCache;
		
		//echo "<br />--- Processing file: $pluginLoc ----<br />";
		$pluginContent = @file_get_contents($pluginLoc);
		
		/**
		 * @todo: The below should be (;|\n)[\S]*(exit;exit\(
		 */
		//$pluginContent = str_replace(array('exit;', 'exit('), array('wpu_complete(); exit;', 'wpu_complete(); exit('), $pluginContent);
	
		// identify all global vars
		if (!$this->compat) {
			preg_match_all('/\n[\s]*global[\s]*[^\n^\r^;^:]*(;|:|\r|\n)/', $pluginContent, $glVars);
		}
		
		/**
		 * @todo: should make vars global at beginning of each file (Can't do here) --- how to cache?
		 * 
		 * option 1:
		 * in main file: grep all globals, declare before code runs --OK
		 * in include files, do same -- not OK
		 * 
		 * Solution: During compile, step into includes --> they are not being executed yet
		 * Collect global vars and pass back up the chain. Collect in $this->globals
		 * Main plugin entry should be called with ->fix(xxx, entry=true)
		 * if this is main plugin entry, then prepend globals - global $x $y $z
		 * 
		 * TODO: MUST NOT PROCESS FILES OUTSIDE THE PLUGIN FOLDER
		 * => THIS MUST FIRST BE INITIALISED WITH THE PLUGIN FOLDER ROOT
		 */
		
		// prevent including files which WP-United has already processed and included
		$pluginContent = preg_replace('/\n[\s]*((include|require)(_once)?[\s]*\([^\)]*registration\.php)/', "\n if(!function_exists('wp_insert_user')) $1", $pluginContent);
		$pluginContent = preg_replace('/\n[\s]*((include|require)(_once)?[\s]*\([^\(]*(\([\s]*__FILE__[\s]*\))?[^\)]*wp-config\.php)/', "\n if(!defined('ABSPATH')) $1", $pluginContent);
	
	//echo getcwd();
		// identify all includes and redirect to plugin fixer cache, if appropriate
		preg_match_all('/\n[\s]*((include|require)(_once)?[\s]*[\(]?([^\);\n]*\.(' . $phpEx . '|php)[^\);\n]*)(\)|;))/', $pluginContent, $includes);
		//print_r($includes);
		foreach($includes[4] as $key => $value) {	
			if(!empty($includes[4][$key])) {
				$finalChar = ($includes[6][$key] == ';') ? ';' : '';
				$pluginContent = str_replace($includes[1][$key], $includes[2][$key] . $includes[3][$key] . '($GLOBALS[\'wpuPluginFixer\']->fix(' . "{$value})){$finalChar}", $pluginContent);
				//echo '$GLOBALS[\'wpuPluginFixer\']->include_redirect(' . $value . ', \'' .  $includes[2][$key] . $includes[3][$key] . '\'' . "<br />";
			}
		}
	
		$pluginContent = str_replace('__FILE__', "'" . $pluginLoc . "'", $pluginContent);
	
		$startToken = (preg_match('/^[\s]*<\?php/', $pluginContent)) ? '?'.'>' : '';
		$endToken = (preg_match('/\?' . '>[\s]*$/', $pluginContent)) ? '<'.'?php ' : ''; 
	
		$pluginContent = $startToken. trim($pluginContent) . $endToken;
		
		
		return $wpuCache->save_plugin($pluginContent, $pluginLoc, $this->wpuVer, $this->wpVer, $this->strCompat);
		
		
	
	//	echo"result: " . $pluginContent;
		//global $wpuCache;
		//$wpuCache->save($pluginContent, $this->phpbb_root . "wp-united/cache/pluginfix" . basename($pluginLoc) . '.wpuplg');
	
		//return $pluginContent; 
	}
	
	
	
}