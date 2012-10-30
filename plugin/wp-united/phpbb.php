<?php
/** 
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* phpBB status abstraction layer
* When in WordPress, we often want to switch between phpBB & WordPress functions
* By accessing through this class, it ensures that things are done cleanly.
* This will eventually replace much of the awkward variable swapping that wp-integration-class is
* doing.
*/

/**
 */
if ( !defined('ABSPATH') && !defined('IN_PHPBB') ) exit;

/**
 * phpBB abstraction class -- a neat way to access phpBB from WordPress
 * 
 */
class WPU_Phpbb {


	private $wpTablePrefix;
	private $wpUser;
	private $wpCache;
	private $phpbbTablePrefix;
	private $phpbbUser;
	private $phpbbCache;
	private $phpbbDbName;
	private $phpbbTemplate;
	private $wpTemplate;
	private $state;
	private $was_out;
	private $_savedID;
	private $_savedIP;
	private $_savedAuth;
	private $tokens;
	private $_loaded;
	
	public $lang;
	public $seo;
	public $url;
	public $_transitioned_user;		
	
	/**
	 * Class initialisation
	 */
	public function __construct() {
		if(defined('IN_PHPBB')) { 
			$this->lang = $GLOBALS['user']->lang;
			$this->state = 'wp';
			$this->phpbbTemplate = $GLOBALS['template'];
			$this->phpbbTablePrefix = $GLOBALS['table_prefix'];
			$this->phpbbUser = $GLOBALS['user'];
			$this->phpbbCache = $GLOBALS['cache'];
		}
		$this->tokens = array();
		$this->was_out = false;
		$this->seo = false;
		
		$this->_transitioned_user = false;
		$this->_savedID = -1;
		$this->_savedIP = '';
		$this->_savedAuth = NULL;
		
		$this->_loaded = false;
		
	}	
	
	public function is_phpbb_loaded() {
		return $this->_loaded;
	}
	
	/**
	 * Loads the phpBB environment if it is not already
	 */
	public function load($rootPath) {
		global $phpbb_hook, $phpbb_root_path, $phpEx, $IN_WORDPRESS, $db, $table_prefix, $wp_table_prefix, $wpSettings;
		global $dbms, $auth, $user, $cache, $cache_old, $user_old, $config, $template, $dbname, $SID, $_SID;
		
		if($this->is_phpbb_loaded()) {
			return;
		}
		$this->_loaded = true;
			
		$this->backup_wp_conflicts();
		
		define('IN_PHPBB', true);
		
		$phpbb_root_path = $rootPath;
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		
		$this->make_phpbb_env();
		
		if(!file_exists($phpbb_root_path . 'common.' . $phpEx)) {
			wpu_disable_connection('error');
		}
		require_once($phpbb_root_path . 'common.' . $phpEx);
		
		if(!isset($user)) {
			wpu_disable_connection('error');
		}
		
		if(!is_object($user)) {
			wpu_disable_connection('error');
		}
		
		
		
		$this->calculate_url();
		
		// phpBB's deregister_globals is unsetting $template if it is also set as a WP post var
		// so we just set it global here
		$GLOBALS['template'] = &$template;

		$user->session_begin();
		$auth->acl($user->data);

		if(!is_admin()) {
			if ($config['board_disable'] && !defined('IN_LOGIN') && !$auth->acl_gets('a_', 'm_') && !$auth->acl_getf_global('m_')) {
				// board is disabled. 
				$user->add_lang('common');
				define('WPU_BOARD_DISABLED', (!empty($config['board_disable_msg'])) ? '<strong>' . $user->lang['BOARD_DISABLED'] . '</strong><br /><br />' . $config['board_disable_msg'] : $user->lang['BOARD_DISABLE']);
			} else {
				if(($wpSettings['showHdrFtr'] == 'FWD') && (defined('WPU_INTEG_DEFAULT_STYLE') && WPU_INTEG_DEFAULT_STYLE)) {
					// This option forces the default phpBB style in a forward integration
					$user->setup('mods/wp-united', $config['default_style']);
				} else {
					$user->setup('mods/wp-united');
				}
			}
		} else {	
			$user->setup('mods/wp-united');
		}
		
		if(defined('WPU_BLOG_PAGE') && !defined('WPU_HOOK_ACTIVE')) {
			$cache->purge();
			trigger_error($user->lang['wpu_hook_error'], E_USER_ERROR);
		}
		
		if(!defined('WPU_BLOG_PAGE')) {
			$wpSettings = (empty($wpSettings)) ? get_integration_settings() : $wpSettings; 
		}
		
		//fix phpBB SEO mod
		global $phpbb_seo;
		if (empty($phpbb_seo) ) {
			if(file_exists($phpbb_root_path . 'phpbb_seo/phpbb_seo_class.'.$phpEx)) {
				require_once($phpbb_root_path . 'phpbb_seo/phpbb_seo_class.'.$phpEx);
				$phpbb_seo = new phpbb_seo();
				$this->seo = true;
			}
		}

		$this->lang = $GLOBALS['user']->lang;
		
		$this->backup_phpbb_state();
		$this->switch_to_wp_db();
		$this->restore_wp_conflicts();
		$this->make_wp_env();
	}
	
	/**
	 * Gets the current forum/WP status
	 */
	public function get_state() {
		return $this->state;
	}
	
	/**
	 * Enters the phpBB environment
	 * @access private
	 */
	private function enter() { 
		$this->lang = (isset($this->phpbbUser->lang)) ? $this->phpbbUser->lang : $this->lang;
		if($this->state != 'phpbb') {
			$this->backup_wp_conflicts();
			$this->restore_phpbb_state();
			$this->make_phpbb_env();
			$this->switch_to_phpbb_db();
		}
	}
	
	/**
	 * Returns to WordPress
	 */
	private function leave() { 
		if(!isset($GLOBALS['user'])) {
			$this->lang = (sizeof($GLOBALS['user']->lang)) ? $GLOBALS['user']->lang : $this->lang;
		}
		if($this->state == 'phpbb') {
			$this->backup_phpbb_state();
			if(defined('DB_NAME')) {
				$this->switch_to_wp_db();
			}
			$this->restore_wp_conflicts();
			$this->make_wp_env();
		}
	}
	
	/**
	 * Passes content through the phpBB word censor
	 */
	public function censor($content) { 

		if(!$this->is_phpbb_loaded()) {
			return $content;
		}
		$fStateChanged = $this->foreground();
		$content = censor_text($content);
		$this->restore_state($fStateChanged);
		return $content;
	}
	
	/**
	 * Returns if the current user is logged in
	 */
	public function user_logged_in() {
		$fStateChanged = $this->foreground();
		$result = ( empty($GLOBALS['user']->data['is_registered']) ) ? false : true;
		$this->restore_state($fStateChanged);
		return $result;
	}
	
	/**
	 * Returns the currently logged-in user's username
	 */
	public function get_username() {
		$fStateChanged = $this->foreground();
		$result = $GLOBALS['user']->data['username'];
		$this->restore_state($fStateChanged);
		return $result;
	}
	
	/**
	 * Returns something from $user->userdata
	 */
	public function get_userdata($key = '') {
		$fStateChanged = $this->foreground();
		if ( !empty($key) ) {
			$result = $GLOBALS['user']->data[$key];
		} else {
			$result = $GLOBALS['user']->data;
		}
		$this->restore_state($fStateChanged);
		return $result;		
	}
	
	/**
	 * Returns the user's IP address
	 */
	public function get_userip() {
		$fStateChanged = $this->foreground();
		$result = $GLOBALS['user']->ip;
		$this->restore_state($fStateChanged);
		return $result;			
	}
	
	/**
	 * Returns a statistic
	 */
	public function stats($stat) {
		 return $GLOBALS['config'][$stat];
	}
	
	
	/**
	 * Returns rank info for currently logged in, or specified, user.
	 */
	public function get_user_rank_info($userID = '') {
		global $db;
		$fStateChanged = $this->foreground();
		
		if (!$userID ) {
			if( $this->user_logged_in() ) {
				$usrData = $this->get_userdata();
			} 
		} else {
			$sql = 'SELECT user_rank, user_posts 
						FROM ' . USERS_TABLE .
						' WHERE user_wpuint_id = ' . $userID;
				if(!($result = $db->sql_query($sql))) {
					wp_die($this->lang['WP_DBErr_Retrieve']);
				}
				$usrData = $db->sql_fetchrow($result);
		}
		if( $usrData ) {
				global $phpbb_root_path, $phpEx;
				if (!function_exists('get_user_rank')) {
					require_once($phpbb_root_path . 'includes/functions_display.php');
				}
				$rank = array();
				$rank['text'] = $rank['image_tag'] = $rank['image']  = '';
				get_user_rank($usrData['user_rank'], $usrData['user_posts'], $rank['text'], $rank['image_tag'], $rank['image']);
				$this->restore_state($fStateChanged);
				return $rank;
		}
		$this->restore_state($fStateChanged);
	}
	
	
	/**
	 * Lifts latest phpBB topics from the DB. (this is the phpBB2 version) 
	 * $forum_list limits to a specific forum (comma delimited list). $limit sets the number of posts fetched. 
	 */
	public function get_recent_topics($forum_list = '', $limit = 50) {
		global $db, $auth;
		
		$fStateChanged = $this->foreground();

		$forum_list = (empty($forum_list)) ? array() :  explode(',', $forum_list); //forums to explicitly check
		$forums_check = array_unique(array_keys($auth->acl_getf('f_read', true))); //forums authorised to read posts in
		if (sizeof($forum_list)) {
			$forums_check = array_intersect($forums_check, $forum_list);
		}
		if (!sizeof($forums_check)) {
			return FALSE;
		}
		$sql = 'SELECT t.topic_id, t.topic_time, t.topic_title, u.username, u.user_id,
				t.topic_replies, t.forum_id, t.topic_poster, t.topic_status, f.forum_name
			FROM ' . TOPICS_TABLE . ' AS t, ' . USERS_TABLE . ' AS u, ' . FORUMS_TABLE . ' AS f 
			WHERE ' . $db->sql_in_set('f.forum_id', $forums_check)  . ' 
				AND t.topic_poster = u.user_id 
					AND t.forum_id = f.forum_id 
						AND t.topic_status <> 2 
			ORDER BY t.topic_time DESC';
			
		if(!($result = $db->sql_query_limit($sql, $limit, 0))) {
			wp_die($this->lang['WP_DBErr_Retrieve']);
		}		

		$posts = array();
		$i = 0;
		while ($row = $db->sql_fetchrow($result)) {
			$posts[$i] = array(
				'topic_id' 		=> $row['topic_id'],
				'topic_replies' => $row['topic_replies'],
				'topic_title' 	=> wpu_censor($row['topic_title']),
				'user_id' 		=> $row['user_id'],
				'username' 		=> $row['username'],
				'forum_id' 		=> $row['forum_id'],
				'forum_name' 	=> $row['forum_name']
			);
			$i++;
		}
		$db->sql_freeresult($result);
		$this->restore_state($fStateChanged);
		return $posts;
	}	
	
	/**
	 * Transitions to/from the currently logged-in user
	 */
	 public function transition_user($toID = false, $toIP = false) {
		 global $auth, $user, $db;
		 
		 $fStateChanged = $this->foreground();
		 
		 if( ($toID === false) && ($this->_transitioned_user == true) ) {
			  // Transition back to the currently logged-in user
			$user->data = $this->_savedData;
			$user->ip = $this->_savedIP;
			$auth = $this->_savedAuth;
			$this->_transitioned_user = false;
		} else if(($toID !== false) && ($toID !== $user->data['user_id'])) {
			// Transition to a new user
			if($this->_transitioned == false) {
				// backup current user
				$this->_savedData= $user->data;
				$this->_savedIP = $user->ip;
				$this->_savedAuth = $auth;
			}
			$sql = 'SELECT *
				FROM ' . USERS_TABLE . "
				WHERE user_id = {$toID}";

			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			$user->data = array_merge($user->data, $row);
			$user->ip = $toIP;
			$auth->acl($user->data);
			$this->_transitioned_user = true;
		}
		
		$this->restore_state($fStateChanged);
		 
	}	
	
	/**
	 * Logs out the current user
	 */
	 public function logout() {
		 global $user;
		 
		 $fStateChanged = $this->foreground();
		 
		 if($user->data['user_id'] != ANONYMOUS) {
			$user->session_kill();
		}
		
		$this->restore_state($fStateChanged);
	}
	
	/**
	 * Returns a list of smilies
	 */
	public function get_smilies() {
		global $db;
		
		if(!$this->is_phpbb_loaded()) {
			return '';
		}
		
		$fStateChanged = $this->foreground();
	
		$result = $db->sql_query('SELECT code, emotion, smiley_url FROM '.SMILIES_TABLE.' GROUP BY emotion ORDER BY smiley_order ', 3600);

		$i = 0;
		$smlOutput =  '<span id="wpusmls">';
		while ($row = $db->sql_fetchrow($result)) {
			if (empty($row['code'])) {
				continue;
			}
			if ($i == 20) {
				$smlOutput .=  '<span id="wpu-smiley-more" style="display:none">';
			}
		
			$smlOutput .= '<a href="#"><img src="'.$this->url.'images/smilies/' . $row['smiley_url'] . '" alt="' . $row['code'] . '" title="' . $row['emotion'] . '" /></a> ';
			$i++;
		}
		$db->sql_freeresult($result);
	
		$this->restore_state($fStateChanged);
	
	
		if($i >= 20) {
			$smlOutput .= '</span>';
			if($i>20) {
				$smlOutput .= '<a id="wpu-smiley-toggle" href="#" onclick="return wpuSmlMore();">' . $this->lang['wpu_more_smilies'] . '&nbsp;&raquo;</a></span>';
			}
		}
		$smlOutput .= '</span>';
	
		return $smlOutput;
		 
	}
	
	
	/**
	 * transmits new settings from the WP settings panel to phpBB
	 */
	public function synchronise_settings($settings) {
		global $wpSettings, $cache, $user, $auth, $config, $db, $phpbb_root_path, $phpEx;
		$fStateChanged = $this->foreground();
		if(is_array($settings)) {
			if(sizeof($settings)) {
				
				// generate unique ID for details ID
				$randSeed = rand(0, 99999);

				$bodyContent = '<div style="display: none; border: 1px solid #cccccc; background-color: #ccccff; padding: 3px;" id="wpulogdetails' . $randSeed . '">';
				$bodyContent .= "Sending settings from WordPress to phpBB<br />\n\n";
				if  ( !array_key_exists('user_wpuint_id', $user->data) ) {
					$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
						  ADD user_wpuint_id VARCHAR(10) NULL DEFAULT NULL';

					if (!$result = $db->sql_query($sql)) {
						trigger_error('ERROR: Cannot add the integration column to the users table', E_USER_ERROR); exit();
					}
					$bodyContent .= "Modified USERS Table (Integration ID)<br />\n\n";
				}
				
				if  ( !array_key_exists('user_wpublog_id', $user->data) ) {
					$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
						ADD user_wpublog_id VARCHAR(10) NULL DEFAULT NULL';
					if (!$result = $db->sql_query($sql)) {
						trigger_error('ERROR: Cannot add blog ID column to users table', E_USER_ERROR); exit();
					}
					$bodyContent .= "Modified USERS Table (Blog ID)<br />\n\n";
				}
				
				$sql = 'SELECT * FROM ' . POSTS_TABLE;
				$result = $db->sql_query_limit($sql, 1);

				$row = (array)$db->sql_fetchrow($result);

				if (!array_key_exists('post_wpu_xpost', $row) ) {
					$sql = 'ALTER TABLE ' . POSTS_TABLE . ' 
						ADD post_wpu_xpost VARCHAR(10) NULL DEFAULT NULL';

					if (!$result = $db->sql_query($sql)) {
						trigger_error('ERROR: Cannot add cross-posting column to posts table', E_USER_ERROR); exit();
					}
					$bodyContent .= "Modified POSTS Table (Cross-Posting Link)<br />\n\n";
				}
				
				$db->sql_freeresult($result);

				$bodyContent .= "Adding WP-United Permissions....<br />\n\n";
				
				
				// Setup $auth_admin class so we can add permission options
				include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);


				$auth_admin = new auth_admin();

				// Add permissions
				$auth_admin->acl_add_option(array(
					'local'      => array('f_wpu_xpost'),
					'global'   => array('u_wpu_subscriber','u_wpu_contributor','u_wpu_author','m_wpu_editor','a_wpu_administrator')
				));

				$bodyContent .= '</div>';
				$ln = "<script type=\"text/javascript\">
				// <![CDATA[
				function toggleWpuLog{$randSeed}() {
					var lg = document.getElementById('wpulogdetails{$randSeed}');
					var lgP = document.getElementById('wpulogexpand{$randSeed}');
					if(lg.style.display == 'none') {
						lg.style.display='block';
						lgP.firstChild.nodeValue = '-';
					} else {
						lg.style.display='none';
						lgP.firstChild.nodeValue = '+';			
					}
					return false;
				}
				// ]]>
				</script>";
				
				$ln .= '*}<strong><a href="#" onclick="return toggleWpuLog' . $randSeed . '();" title="click to see details">Changed WP-United Settings (click for details)<span id="wpulogexpand' . $randSeed . '">+</span></a></strong>' . $bodyContent . '{*';

				add_log('admin', $ln);
				
				$cache->purge();
				

				set_integration_settings($settings);
				$wpSettings = $settings;
				
				// clear out the WP-United cache on settings change
				require_once($wpSettings['wpPluginPath'] . 'cache.' . $phpEx);
				$wpuCache = WPU_Cache::getInstance();
				$wpuCache->purge();

				$this->restore_state($fStateChanged);
				return true;
			}
		}
		$this->restore_state($fStateChanged);
		echo "No settings to process";
		return false;
	}
	
	
	/**
	 * Calculates the URL to the forum
	 * @access private
	 */
	private function calculate_url() {
			global $config;
			$server = $config['server_protocol'] . add_trailing_slash($config['server_name']);
			$scriptPath = add_trailing_slash($config['script_path']);
			$scriptPath= ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
			$this->url = $server . $scriptPath;
	}
	
	
	/**
	 * @access private
	 */
	private function make_phpbb_env() {
		global $IN_WORDPRESS;
		
		// WordPress removes $_COOKIE from $_REQUEST, which is the source of much wailing and gnashing of teeth
		$IN_WORDPRESS = 1; 
		$this->state = 'phpbb';
		$_REQUEST = array_merge($_COOKIE, $_REQUEST);
	}

	/**
	 * @access private
	 */	
	private function make_wp_env() {
		$this->state = 'wp';
		$_REQUEST = array_merge($_GET, $_POST);
		restore_error_handler();
	}

	/**
	 * @access private
	 */	
	private function backup_wp_conflicts() {
		global $table_prefix, $user, $cache, $template;
		
		$this->wpTemplate = $template;
		$this->wpTablePrefix = $table_prefix;
		$this->wpUser = (isset($user)) ? $user: '';
		$this->wpCache = (isset($cache)) ? $cache : '';
	}

	/**
	 * @access private
	 */	
	private function backup_phpbb_state() {
		global $table_prefix, $user, $cache, $dbname, $template;

		$this->phpbbTemplate = $template;
		$this->phpbbTablePrefix = $table_prefix;
		$this->phpbbUser = (isset($user)) ? $user: '';
		$this->phpbbCache = (isset($cache)) ? $cache : '';
		$this->phpbbDbName = $dbname;
	}

	/**
	 * @access private
	 */	
	private function restore_wp_conflicts() {
		global $table_prefix, $user, $cache, $template;
		
		$template = $this->wpTemplate;
		$user = $this->wpUser;
		$cache = $this->wpCache;
		$table_prefix = $this->wpTablePrefix;
	}

	/**
	 * @access private
	 */	
	private function restore_phpbb_state() {
		global $table_prefix, $user, $cache, $template;
		
		$template = $this->phpbbTemplate;
		$table_prefix = $this->phpbbTablePrefix;
		$user = $this->phpbbUser;
		$cache = $this->phpbbCache;
		
		// restore phpBB error handler
		set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');


	}

	/**
	 * @access private
	 */	
	private function switch_to_wp_db() {
		global $wpdb;
		if (($this->phpbbDbName != DB_NAME) && (!empty($wpdb->dbh))) {
			@mysql_select_db(DB_NAME, $wpdb->dbh);
		}      
	}
	
	/**
	 * @access private
	 */	
	private function switch_to_phpbb_db() {
		global $db, $dbms; 
		if (($this->phpbbDbName != DB_NAME) && (!empty($db->db_connect_id))) {
			if($dbms=='mysqli') {
				@mysqli_select_db($this->phpbbDbName, $db->db_connect_id);
			} else if($dbms=='mysql') {
				@mysql_select_db($this->phpbbDbName, $db->db_connect_id);
			}
		}
	}
	
	/**
	 * Originally by Poyntesm
	 * http://www.phpbb.com/community/viewtopic.php?f=71&t=545415&p=3026305
	 * @access private
	 */
	private function get_role_by_name($name) {
		$fStateChanged = $this->foreground();
	   global $db;
	   $data = null;

	   $sql = "SELECT *
		  FROM " . ACL_ROLES_TABLE . "
		  WHERE role_name = '$name'";
	   $result = $db->sql_query($sql);
	   $data = $db->sql_fetchrow($result);
	   $db->sql_freeresult($result);
		$this->restore_state($fStateChanged);
	   return $data;
	}
	
	/**
	* Set role-specific ACL options without deleting enter existing options. If option already set it will NOT be updated.
	* 
	* @param int $role_id role id to update (a role_id has to be specified)
	* @param mixed $auth_options auth_options to grant (a auth_option has to be specified)
	* @param ACL_YES|ACL_NO|ACL_NEVER $auth_setting defines the mode acl_options are getting set with
	* @access private
	*
	*/
	private function acl_update_role($role_id, $auth_options, $auth_setting = ACL_YES) {
	   global $db, $cache, $auth;
		$fStateChanged = $this->foreground();
		$acl_options_ids = $this->get_acl_option_ids($auth_options);

		$role_options = array();
		$sql = "SELECT auth_option_id
			FROM " . ACL_ROLES_DATA_TABLE . "
			WHERE role_id = " . (int) $role_id . "
			GROUP BY auth_option_id";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))	{
			$role_options[] = $row;
		}
		$db->sql_freeresult($result);

		$sql_ary = array();
		foreach($acl_options_ids as $option)	{
			if (!in_array($option, $role_options)) {
				$sql_ary[] = array(
					'role_id'      		=> (int) $role_id,
					'auth_option_id'	=> (int) $option['auth_option_id'],
					'auth_setting'      => $auth_setting
				);	
			}
		}

	   $db->sql_multi_insert(ACL_ROLES_DATA_TABLE, $sql_ary);

	   $cache->destroy('acl_options');
	   $auth->acl_clear_prefetch();
	   $this->restore_state($fStateChanged);
	}

	/**
	* Get ACL option ids
	*
	* @param mixed $auth_options auth_options to grant (a auth_option has to be specified)
	* @access private
	*/
	private function get_acl_option_ids($auth_options) {
		$fStateChanged = $this->foreground();
	   global $db;

	   $data = array();
	   $sql = "SELECT auth_option_id
		  FROM " . ACL_OPTIONS_TABLE . "
		  WHERE " . $db->sql_in_set('auth_option', $auth_options) . "
		  GROUP BY auth_option_id";
	   $result = $db->sql_query($sql);
	   while ($row = $db->sql_fetchrow($result))  {
		  $data[] = $row;
	   }
	   $db->sql_freeresult($result);
		$this->restore_state($fStateChanged);
	   return $data;
	}
	
	/**
	 * Brings phpBB (db, conflicting vars, etc) to the foreground
	 * calling functions can track the returned $state parameter and use it to restore the same state when
	 * they exit.
	 * @return bool whether phpBB was in the background and we actually had to do anything.
	 */
	public function foreground() {
		if($this->state != 'phpbb') {
			$this->enter();
			return true;
		}
		return false;
	}
	
	/**
	 * Sends phpBB into the background, restoring WP database and vars
	 * @param bool $state whether to perform the action. Optional -- shortcut for if in the calling function
	 */
	public function background($state = true) {
		if($state) {
			$this->leave();
		}
	}
	
	/**
	 * Alias for background(). However $state must be provided
	 * @ param bool $state whether to perform the action (shortcut for if in the calling function.)
	 */
	public function restore_state($state) {
		$this->background($state);
	}
	
}

?>
