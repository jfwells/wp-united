<?php


/**
 *	A simple factory object that can store itself in phpBB.
 *  The constructor either returns ourself, initialised by WordPress, or a stored serialized 
 *  object that was passed to phpBB.
 *
*/
class WP_United_Settings {

	public
		$pluginPath = '',
		$wpPath = '',
		$wpHomeUrl = '',
		$wpBaseUrl = '',
		$pluginUrl = '',
		$enabled = false,
		$lastRun = false,
		$settings = array();

	public function __construct() {
		if(!$s = $this->load_from_wp()) {
			$s = $this->load_from_phpbb();
		}
		return $s;
	}
	

	private function load_from_wp() {
		
		if(function_exists('get_option')) { 
			$savedSettings = (array)get_option('wpu-settings');
			$defaults = $this->get_defaults();
			$this->settings = array_merge($defaults, (array)$savedSettings);
			
			$this->wpPath = ABSPATH;
			$this->pluginPath = plugin_dir_path(__FILE__);
			$this->pluginUrl = plugins_url('wp-united') . '/';
			$this->wpHomeUrl = home_url('/');
			$this->wpBaseUrl = site_url('/');
			

			return $this;
		}
		return false;
	}
	
	private function load_from_phpbb() {
		global $config;
		
		$wpuString = '';
		$key = 1;
		while(isset( $config["wpu_settings_{$key}"])) {
			$wpuString .= $config["wpu_settings_{$key}"];
			$key++;
		}

		// convert config value into something just like me :-)
		if(!empty($wpuString)) {
			$wpuString =  gzuncompress(base64_decode($wpuString));	
			$settingsObj = unserialize($wpuString);
			if(is_object($settingsObj)) {
				return $settingsObj;
			}
		}
		
		// failed on all accounts. Initialise ourselves with defaults
		$this->settings = $this->get_defaults;
		return $this;

	}
	
	
	private function get_defaults() {
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
			//'mustLogin' => 0,
			//'upgradeRun' => 0,
			'xposting' => 0,
			'phpbbSmilies' => 0,
			'avatarsync'	=> 1,
			'xpostautolink' => 0,
			'xpostforce' => -1,
			'xposttype' => 'EXCERPT',	
			'cssMagic' => 1,
			'templateVoodoo' => 1,
			//'pluginFixes' => 0,
			'useForumPage' => 1
	
		);
	}
	

}

class WP_United_Plugin_Base {

	protected
		
		$version = '',
		$styleKeys = array(),
		$updatedStyleKeys = false,
		$settings = false,
		$integActions = array(),
		$integActionsFor = 0,
		$filters = array(),
		$actions = array();


	/**
	* Initialise the WP-United class
	*/
	public function __construct() {
	
		require_once('phpbb.php');
		global $phpbbForum;
		$phpbbForum = new WPU_Phpbb();

		$this->load_settings();
	
		require_once($this->get_plugin_path() . 'functions-general.php');
		require_once ($this->get_plugin_path() . 'options.php');
		require_once($this->get_plugin_path() .  'phpbb.php');
	

	}

	
	public function is_wordpress_loaded() {
		if(defined('ABSPATH')) {
			return true;
		} else {
			return false;
		}
	}

	
	protected function load_settings() {
		$this->settings = new WP_United_Settings();
	}
	

	
	public function get_plugin_path() {
		return $this->settings->pluginPath;
	}
	
	public function get_wp_path() {
		return $this->settings->wpPath;
	}
	
	public function get_wp_home_url() {
		return $this->setings->wpHomeUrl;
	}
	
	public function get_wp_base_url() {
		return $this->settings->wpBaseUrl;
	}
	
	public function get_plugin_url() {
		return $this->settings->pluginUrl;
	}
	
	
	public function is_enabled() {
		
		if (defined('WPU_DISABLE') && WPU_DISABLE) { 
			return false;
		}
	
		if($this->is_wordpress_loaded()) {
			$this->settings->enabled = get_option('wpu-enabled'); 
		}
		return $this->settings->enabled;
	}
	
	public function enable() {
		$this->settings->enabled = true;
		if($this->is_wordpress_loaded()) {
			update_option('wpu-enabled', true);
		}
	}
	public function disable() {
		$this->settings->enabled = false;
		if($this->is_wordpress_loaded()) {
			update_option('wpu-enabled', false);
		}
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
			require_once ($this->get_plugin_path() . 'version.php');
			global $wpuVersion;
			$this->version = $wpuVersion;
		}
		return $this->version;
	}
	
	
	
	public function get_setting($key) { 

		if(isset($this->settings->settings[$key])) {
			return $this->settings->settings[$key];
		}
		return false;
	}
	
	public function init_style_keys() {
		global $config;
		/**
		 * Handle style keys for CSS Magic
		 * We load them here so that we can auto-remove them if CSS Magic is disabled
		 */ // TODO: THIS SHOULD BE IN phpbbForum
		 
		if($this->styleKeysLoaded) {
			return;
		}
		 
		if($this->is_phpbb_loaded()) {
			$this->styleKeysLoaded = true;
			$key = 1;
			if($this->get_setting('cssMagic')) {
				$fullKey = '';
				while(isset( $config["wpu_style_keys_{$key}"])) {
					$fullKey .= $config["wpu_style_keys_{$key}"];
					$key++;
				}
				if(!empty($fullKey)) {
					$this->styleKeys = unserialize(base64_decode($fullKey));
				} else {
					$this->styleKeys = array();
				}
			} else {
				// Clear out the config keys
				$this->clear_style_keys();
			}
		}
	}

	// TODO: PUT THIS IN phpBBFORUM?
	public function clear_style_keys()	{
		global $db, $config;
		
		if(isset($config['wpu_style_keys_1'])) {
			$sql = 'DELETE FROM ' . CONFIG_TABLE . ' 
				WHERE config_name LIKE \'wpu_style_keys_%\'';
			$db->sql_query($sql);
		}	
		$this->styleKeys = array();
		$cache->destroy('config');
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
		$this->clear_style_keys();
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



	
	/**
	 * Determine if we need to load WordPress, and compile a list of actions that will need to take place once we do
	 */
	protected function assess_required_wp_actions() {
		global $phpEx;
		
		if($this->is_wordpress_loaded() || defined('WPU_PHPBB_IS_EMBEDDED')) {
			return 0;
		}
		
		if($numActions = sizeof($this->actions)) {
			return $numActions;
		}
		
		// Check for user integration-related actions
		
		if($this->get_setting('integrateLogin')) {
		
			// Is this a login/out page or profile update?
			if(preg_match("/\/ucp\.{$phpEx}/", $_SERVER['REQUEST_URI'])) { 
				
				$actionMode = request_var('mode', '');	
				
				if($actionMode == 'logout') {
					$this->integActions[] = 'logout';
				//} else if($actionMode == 'login') { 
					//$this->integActions[] = 'login';
				} else if(($actionMode == 'profile_info') || ($actionMode == 'reg_details') || ($actionMode == 'avatar')) {
					
					$didSubmit = request_var('submit', '');
					if(!empty($didSubmit)) {
						$this->integActions[] = 'profile';
						$this->integActionsFor = 0;
					}
				}
			// Or is it an admin editing a user's profile?
			} else if(defined('ADMIN_START')) {
			
				$didSubmit = request_var('update', '');
				
				if(!empty($didSubmit)) {
					$actionMode = request_var('mode', '');
					$wpuActionsFor = (int)request_var('u', '');
					if(!empty($wpuActionsFor) && (($actionMode == 'profile') || ($actionMode == 'overview') || ($actionMode == 'profile'))) {
						$this->integActions[] = 'profile';
						$this->integActionsFor = $wpuActionsFor;
					}
				}
			}
		}
		
		// Check for template integration-related actions:
		if ($this->get_setting('showHdrFtr') == 'REV') {
			$this->integActions[] = 'template-p-in-w';
		}
		
		return sizeof($this->integActions);
	}
	
	public function should_run_wordpress() {
		return $this->assess_required_wp_actions();
	}
	

	public function actions_for_another() {
		return $this->integActionsFor;
	}
	
	
	public function should_do_action($actionName) {
		if(!sizeof($this->integActions)) {
			return false;
		}
		if(in_array($actionName, $this->integActions)) {
			return true;
		}
		return false;
	}
	
	
	public function version() {
		if(empty($this->version)) {
			require_once ($this->get_plugin_path() . 'version.php');
			global $wpuVersion;
			$this->version = $wpuVersion;
		}
		return $this->version;
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
	

	protected function add_actions() { 
		foreach($this->actions as $action => $details) {
			switch(sizeof((array)$details)) {
				case 3:
					add_action($action, array($this, $details[0]), $details[1], $details[2]);
				break;
				case 2:
					add_action($action, array($this, $details[0]), $details[1]);
				break;
				case 1:
				default:
					add_action($action, array($this, $details));
			}
		}	
	}
	
	protected function add_filters() {
		foreach($this->filters as $filter => $details) {
			switch(sizeof((array)$details)) {
				case 3:
					add_filter($filter, array($this, $details[0]), $details[1], $details[2]);
				break;
				case 2:
					add_filter($filter, array($this, $details[0]), $details[1]);
				break;
				case 1:
				default:
					add_filter($filter, array($this, $details));
			}
		}	
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
