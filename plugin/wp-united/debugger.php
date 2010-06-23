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
if ( !defined('IN_PHPBB') ) {
	exit;
}

class WPU_Debug {
	
	var $debugBuffer;
	var $debugBufferFull;
	
	function WPU_Debug() {
		
		$this->debugBuffer = '';
		
	}
	
	function add($add_to_debug) {
		if ( defined('WPU_DEBUG') && (WPU_DEBUG == TRUE) ) {
			if ( empty($this->debugBuffer) ) {
				$this->debugBuffer = '<!-- wpu-debug --><div style="border: 1px solid #8f1fff; background-color: #cc99ff; padding: 3px; margin: 6px; color: #ffff99;"><strong>DEBUG</strong><br />WP Version = ' . $GLOBALS['wp_version'] . '<br />';
			}
			if ( !empty($add_to_debug) ) {
				$this->debugBuffer .= $add_to_debug . '<br />';
			}
		}			
	}
	
	function get() {
		if(empty($this->debugBuffer)) {
			return '';
		}
		return $this->debugBuffer . '</div><!-- /wpu-debug -->';
		
	}
	
	
}


?>