<?php


class WP_United_Basics {

	protected
		
		$enabled = false,
		$lastRun = false,
		$pluginLoaded = false,
		$version = '',
		
		$styleKeys = array(),
		
		$updatedStyleKeys = false,
		$wordpressLoaded = false,
		$settings = array(); 
	
	public
		$pluginPath = '',
		$wpPath = '',
		$wpHomeUrl = '',
		$wpBaseUrl = '',
		$pluginUrl = '';


	/**
	* Initialise the WP-United class
	*/
	public function __construct() {
		

	}
	
	public function __wakeup() {
		require_once($this->pluginPath . 'functions-general.php');
		require_once ($this->pluginPath .  'options.php');
		$this->wordpressLoaded = false;
	}
	
	public function is_enabled() {
		if($this->wordpressLoaded) {
			$this->enabled = get_option('wpu-enabled'); 
		}
		return $this->enabled;
	}
	public function enable() {
		$this->enabled = true;
		if($this->wordpressLoaded) {
			update_option('wpu-enabled', true);
		}
	}
	public function disable() {
		$this->enabled = false;
		if($this->wordpressLoaded) {
			update_option('wpu-enabled', $this->enabled);
		}
	}

	
	public function is_loaded() {
		return $this->pluginLoaded;
	}
	

	// overridden on WP side
	public function is_phpbb_loaded() {
		return true;
	}
	
	public function phpbb_logout() {
		if($this->is_phpbb_loaded()) {
			global $phpbbForum;
			$phpbbForum->logout();
		}
	}
	
	public function get_version() {
		if(empty($this->version)) {
			require_once ($this->pluginPath . 'version.php');
			global $wpuVersion;
			$this->version = $wpuVersion;
		}
		return $this->version;
	}
	
	private function get_default_settings() {
		return array(
			'phpbb_path' => '',
			'integrateLogin' => 0, 
			'integsource' => 'phpbb',
			'showHdrFtr' => 'NONE',
			'wpSimpleHdr' => 1,
			'dtdSwitch' => 0,
			'usersOwnBlogs' => 0,
			//'buttonsProfile' => 0,
			//'buttonsPost' => 0,
			//'allowStyleSwitch' => 0,
			//'useBlogHome' => 0,
			//'blogListHead' => $user->lang['WPWiz_BlogListHead_Default'],
			//'blogIntro' => $user->lang['WPWiz_blogIntro_Default'],
			//'blogsPerPage' => 6,
			//'blUseCSS' => 1,
			'phpbbCensor' => 1,
			'wpPageName' => 'page.php',
			'phpbbPadding' =>  '6-12-6-12',
			'mustLogin' => 0,
			//'upgradeRun' => 0,
			'xposting' => 0,
			'phpbbSmilies' => 0,
			'xpostautolink' => 0,
			'xpostforce' => -1,
			'xposttype' => 'EXCERPT',	
			'cssMagic' => 1,
			'templateVoodoo' => 1,
			//'pluginFixes' => 0,
			'useForumPage' => 1
	
		);
	}
	
	
	
	public function get_setting($key) { 

		$this->load_settings();

		if(isset($this->settings[$key])) {
			return $this->settings[$key];
		}
		return false;
	}
	
	public function init_style_keys($keys = array()) {
		$this->styleKeys = $keys;
	}
	
	public function get_style_key($key = '') {
		if($key === '') {
			return $this->styleKeys;
		} else {
			if(array_key_exists($key, $this->styleKeys)) {
				return $this->styleKeys[$key];
			} else {
				return false;
			}
		}
	}
	
	
	// adds a new style key, or returns the existing one if it already exists
	public function add_style_key($fileName) {
		$key = array_search($fileName, (array)$this->styleKeys);
		if($key === false) {
			$this->styleKeys[] = $fileName;
			$key = sizeof($this->styleKeys) - 1;
			$this->updatedStyleKeys = true;
		} 
		return $key;
	}
	
	/**
	 * Saves updated style keys to the database.
	 * phpBB $config keys can only store 255 bytes of data, so we usually need to store the data
	 * split over several config keys
	  * We want changes to take place as a single transaction to avoid collisions, so we 
	  * access DB directly rather than using set_config
	 * @return int the number of config keys used
	 */  // @TODO:  PUT THIS INTO PHPBB.PHP!!!!!
	public function commit_style_keys() {
		global $cache, $db;
		
		if(!$this->updatedStyleKeys) {
			return sizeof($this->styleKeys) - 1;
		}
		
		$fullLocs = (base64_encode(serialize($this->styleKeys)));
		$currPtr=1;
		$chunkStart = 0;
		$sql = array();
		wpu_clear_style_keys();
		while($chunkStart < strlen($fullLocs)) {
			$sql[] = array(
				'config_name' 	=> 	"wpu_style_keys_{$currPtr}",
				'config_value' 	=>	substr($fullLocs, $chunkStart, 255)
			);
			$chunkStart = $chunkStart + 255;
			$currPtr++;
		}
		
		$db->sql_multi_insert(CONFIG_TABLE, $sql);
		$cache->destroy('config');
	
		return $currPtr;
	}


	protected function load_settings() { 
		
		if(!sizeof($this->settings)) {
			$savedSettings = array();
			if($this->wordpressLoaded) { 
				$savedSettings = (array)get_option('wpu-settings');
			} 
			
			$defaults = $this->get_default_settings();
			$this->settings = array_merge($defaults, (array)$savedSettings);	
			
		} 

	}
	
	protected function ajax_result($errMsg, $msgType = 'message') {
		if($msgType == 'error') {
			$errMsg = '[ERROR]' . $errMsg;
		}
		die($errMsg);
	}
	
	protected function ajax_ok() {
		$this->ajax_result('OK', 'message');
	}
	
	/**
 * Disable WPU and output result directly to the calling script
 *
 */

public function disable_connection($type) {
	
	if(!$this->is_enabled()) {
		$this->ajax_result(__('WP-United is already disabled'), 'message');
	}
	
	$this->disable();
	
	switch ($type) {
		case 'error':
			switch ($this->get_last_run()) {
				case 'disconnected':
					$this->ajax_result(__('WP-United could not find phpBB at the selected path. WP-United is not connected.'), 'error');
				break;
				case 'connected':
					$this->ajax_result(__('WP-United could not successfully run phpBB at the selected path. WP-United is halted.'), 'error');
				break;
				default:
					$this->ajax_result(__('WP-United could not successfully run phpBB without errors. WP-United has been disconnected.'), 'error');
			}
		break;
		case 'server-error':
			$this->ajax_ok();
		break;
		case 'manual':
			$this->ajax_result(__('WP-United Disabled Successfully'), 'message');
		break;
		default:
			_e('WP-United Disabled');
	}
 	
	return;

}
	

	
}


?>
