<?php
/** 
*
* WP-United Plugin Fixer
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
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
	
	var $pluginDir;
	var $wpuVer;
	var $wpVer;
	var $compat;
	var $strCompat;
	var $globals;
	var $mainEntry;
	var $type;
	var $fixCoreFiles;
		
	/**
	 * Class constructor
	 * @param string $wpPluginDir The WordPress plugin directory
	 * @param string $wpuVer WP-United version
	 * @param string $wpVer WordPress version
	 * @param string $compat True if WordPress is in global scope
	 */
	function WPU_WP_Plugins($wpPluginDir, $type, $wpuVer, $wpVer, $compat) {
		$this->pluginDir =  add_trailing_slash(realpath($wpPluginDir)); 
		$this->compat = $compat;
		$this->wpuVer = $wpuVer;
		$this->wpVer = $wpVer;
		$this->strCompat = ($this->compat) ? "true" : "false";
		$this->mainEntry = false;
		$this->type = $type;
		$this->globals = get_option("wpu_{$this->type}_globals", array());
		$this->oldGlobals = $this->globals;
		
		// problematic WordPress files that could be require()d by a function
		$this->fixCoreFiles = array(
			add_trailing_slash(realpath(ABSPATH)) . 'wp-config.php',
			add_trailing_slash(realpath(ABSPATH . WPINC)) . 'registration.php'
		);
	}
	
	/**
	 * Returns compiled plugin file to execute
	 * @param string $plugin The full path to the plugin
	 */
	function fix($plugin, $mainEntry = false, $workingDir = false) {
		global $wpuCache;
		
		if($workingDir === false) {
			$workingDir = $this->pluginDir;
		} else {
			$workingDir = add_trailing_slash($workingDir);
		}
		
		$this->mainEntry = $mainEntry;
		
		if (stripos($plugin, 'wpu-plugin') === false) {
			if( !file_exists($plugin) ) {
				// plugin file not found -- not an absolute path. Look in plugin folder
				$plugin = $workingDir . $plugin; 
			}
			if(file_exists($plugin)) {
				$cached = $wpuCache->get_plugin($plugin, $this->wpVer, $this->strCompat);
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
	 * Process the file
	 * @access private
	 */
	function process_file($pluginLoc) {
		global $phpEx, $wpuCache;
		
		$prefixContent = '';
		
		// We only process files in the plugins directory, unless that file is a known problem
		$thisLoc = add_trailing_slash(dirname(realpath($pluginLoc)));
		if(strpos($thisLoc, $this->pluginDir) === false) {
			if(in_array(realpath($pluginLoc), $this->fixCoreFiles)) {
				return $wpuCache->save_plugin('', $pluginLoc, $this->wpVer, $this->strCompat);
			}
			return $pluginLoc;
		}
			
		$pluginContent = @file_get_contents($pluginLoc);
		
		// prevent plugins from calling exit
		$pluginContent = preg_replace(array('/[;\s]exit;/', '/[;\s]exit\(/'), array('wpu_complete(); exit;', 'wpu_complete(); exit('), $pluginContent);
	
		// identify all global vars
		if (!$this->compat) { 
			preg_match_all('/\n[\s]*global[\s]*([^\n^\r^;^:]*)(;|:|\r|\n)/', $pluginContent, $glVars);
		
			$globs = array();
			foreach($glVars[1] as $varSec) {
				$vars = explode(',', $varSec);
				foreach($vars as $var) {
					$globs[] = trim(str_replace('$', '',$var));
				}
			}
			if(sizeof($globs)) {
				if(is_array($this->globals)) {
					if(sizeof($this->globals)) {
						$globs = array_merge($this->globals, $globs);
					}
				}
				$globs = array_merge(array_unique($globs));
				$this->globals = $globs;
			}
	
		}
		
		// prevent including files which WP-United has already processed and included
		$pluginContent = preg_replace('/\n[\s]*((include|require)(_once)?[\s]*\([^\)]*registration\.php)/', "\n if(!function_exists('wp_insert_user')) $1", $pluginContent);
		$pluginContent = preg_replace('/\n[\s]*((include|require)(_once)?[\s]*\([^\(]*(\([\s]*__FILE__[\s]*\))?[^\)]*wp-config\.php)/', "\n if(!defined('ABSPATH')) $1", $pluginContent);
	
		//prevent buggering up of include paths
		$pluginContent = str_replace('__FILE__', "'" . $pluginLoc . "'", $pluginContent);
	
		// identify all includes and redirect to plugin fixer cache, if appropriate
		preg_match_all('/\n\s*((include|require)(_once)?\s*(\(?[^;\n]*\.(' . $phpEx . '|php)[^;\n]*))(\n|;)/', $pluginContent, $includes); 

		foreach($includes[4] as $key => $value) {	
			if(!empty($includes[4][$key])) {
				$finalChar = ($includes[6][$key] == ';') ? ';' : '';
				$pluginContent = str_replace($includes[0][$key], "\n" . $includes[2][$key] . $includes[3][$key] . '($GLOBALS[\'wpuPluginFixer\']->fix(' . "{$value}, false, '" . dirname($pluginLoc) . "')){$finalChar}", $pluginContent);
			}
		}
	
		
	
		$startToken = (preg_match('/^[\s]*<\?/', $pluginContent)) ? '?'.'>' : '';
		$endToken = (preg_match('/\?' . '>[\s]*$/', $pluginContent)) ? '<'.'?php ' : ''; 
	
		$pluginContent = $startToken. trim($pluginContent) . $endToken;
	
		return $wpuCache->save_plugin($pluginContent, $pluginLoc, $this->wpVer, $this->strCompat, $prefixContent);

	}
	
	/**
	 * remove any blanks, and remove anything that could wreck global references
	 */
	function clean_globals($globs = false) {
		if($globs === false) {
			$globs = $this->globals;
		}
		if(!sizeof($globs)) {
			return array();
		}
		return array_diff((array)$globs, array_merge(array(''), $GLOBALS['wpUtdInt']->globalRefs));
	}
	
	function save_globals() {
		$this->globals = $this->clean_globals();
		if($this->globals != $this->oldGlobals) {
			update_option("wpu_{$this->type}_globals", $this->globals);
		}
	}
	
	function get_globalString() {
		if(!$this->compat) {
			$ret = '';
			foreach($this->globals as $g) {
				if(!isset($GLOBALS[$g])) {
					$ret .= '$GLOBALS[\'' . $g . '\'] = $' . $g . ';';
				}
			}
			if(sizeof($this->globals) && (is_array($this->globals))) {
				$this->save_globals();
				return $ret; 
			}
		}
		return '';
	}
	
	
}

?>