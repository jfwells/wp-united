<?php

/** 
*
* WP-United Debugger
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/

/**
 */
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) {
	exit;
}

class WPU_Debug {
	
	private $debugBuffer;
	
	public function __construct() {
		$this->debugBuffer = array();
	}
	
	public function add($debug, $type = 'login') {
		if ( defined('WPU_DEBUG') && (WPU_DEBUG == TRUE) ) {
				if(!isset($this->debugBuffer[$type])) {
					$this->debugBuffer[$type] = array();
				}
				$this->debugBuffer[$type][] = $debug;
		}
	}
	
	private function get($type) {
		if(!isset($this->debugBuffer[$type])) {
			return '';
		}
		$result = implode('<br />', $this->debugBuffer[$type]);
		$this->debugBuffer[$type] = array();
		return $result;
	}
	
	public function get_block($type = 'login') {
		$result = '<!-- wpu-debug --><div style="border: 1px solid #8f1fff; background-color: #cc99ff; padding: 3px; margin: 6px; color: #ffff99;">';
		$result .= '<strong>DEBUG</strong><br />WP Version = ' . $GLOBALS['wp_version'] . '<br />';
		$result .= $this->get($type);
		$result .= '</div><!-- /wpu-debug -->';
		return $result;
	}
	
	public function display($type = 'login') {
		echo $this->get_block($type);
	}
	
	public function add_debug_box($content, $type = 'login') {
		if(stristr($content, '</body>') !== false) {
			return str_replace('</body>', $this->get_block($type) . '</body>', $content);
		} else {
			return $content . $$this->get_block($type);
		}
		
	}
	
	public function start_stats() {
		$timeStart = explode(' ', microtime());
		$this->scriptTime = $timeStart[0] + $timeStart[1];
	}
	
	public function get_stats() {
		global $wpuCache;
		
		$endTime = explode(' ', microtime());
		$endTime = $endTime[1] + $endTime[0];
		$pageLoad = round($endTime - $this->scriptTime, 4) . " seconds";
	
		$memUsage = (function_exists('memory_get_peak_usage')) ? round(memory_get_peak_usage()/1024, 0) . "kB" : (function_exists('memory_get_usage')) ? round(memory_get_usage() / 1024, 0) . "kB" : "[Not supported on your server]";
		return "<p style='background-color: #999999;color: #ffffff !important;display: block;'><strong style='text-decoration: underline;'>WP-United Statistics </strong><br />Script Time: " . $pageLoad . "<br />Memory usage: " . $memUsage . "<br />" . $wpuCache->get_logged_actions() . "</p>";		
	}
	
	public function display_stats() {
		echo $this->get_stats();
	}
	
	public function add_stats_box($content) {
		if(stristr($content, '</body>') !== false) {
			return str_replace('</body>', $this->get_stats() . '</body>', $content);
		} else {
			return $content . $$this->get_stats();
		}
		
	}
	
	public function get_debug_info() {
		global $wpUnited, $wpuVersion, $wp_version;
		
		$settings = $wpUnited->get_setting();
		$mainEntries = array(
			'WP-United Version' 	=> 	$wpu_version,
			'WordPress Version' 	=> 	$wp_version,
			'PHP Version'				=>	PHP_VERSION
		); 
		$settings = array_merge($mainEntries, $settings);
		$result  = '';
		foreach($settings as $setting => $value) {
			$result .= '[b]<strong>' . $setting . '</strong>[/b]: ' . $value . '<br />';
		}
		return $result;
		
	}
}


?>
