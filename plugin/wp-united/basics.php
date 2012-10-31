<?php


class WP_United_Basics {

	private
		
		$enabled = false,
		$lastRun = false,
		$pluginLoaded = false;

	
	public
		$pluginPath = '',
		$wpPath = '',
		$wpHomeUrl = '',
		$wpBaseUrl = '',
		$pluginUrl = '',
		$settings = array();

	/**
	* Initialise the WP-United class
	*/
	public function __construct() {
		

	}
	

	// Must be overridden on WP side
	public function is_enabled() { 
		return $this->enabled;
	}
	
	// Must be overridden on WP side
	public function enable() {
		$this->enabled = true;
	}
	// Must be overridden on WP side
	public function disable() {
		$this->enabled = false;
	}

	
	public function is_loaded() {
		return $this->pluginLoaded;
	}
	

	// Must be overridden on WP side
	public function get_last_run() {
		 return $this->lastRun;
	}
	
	public function is_phpbb_loaded() {
		return true;
	}
	
	public function phpbb_logout() {
		if($this->is_phpbb_loaded()) {
			global $phpbbForum;
			$phpbbForum->logout();
		}
	}
	
}

?>
