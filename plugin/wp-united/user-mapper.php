<?php

/**
 * The main user mapper class
 * Parses user mapper options and loads in data for all required users
 */
class WPU_User_Mapper {
	
	private $leftSide = 'wp';
	private $numToShow = 50;
	private $showUsers = 0;
	private $usersToShow = 0;
	private $showSpecificUsers = false;
	private $showUsersLike = false;
	private $numStart = 0;
	private $showOnlyInt = 0;
	private $showOnlyUnInt = 0;
	private $showOnlyPosts = 0;
	private $showOnlyNoPosts = 0;
	
	public $users;
	
	/**
	 * Constructor. Parses the incoming options and filters and loads in the relevant user objects
	 * @param string $args: A url-style list of args and filters to process:
	 *	leftSide: The main user list to show (on the LHS of the user mapper)
	 *  numToShow: The number of records to return
	 *  numStart:  The offset to start showing records from
	 *  showOnlyInt: Filter out unintegrated users
	 *	showOnlyUnInt: Filter out integrated users
	 *  showOnlyPosts: Filter out users without any posts
	 *  showOnlyNoPosts: Filter out users with posts
	 */
	public function __construct($args, $showSpecificUserIDs = 0, $showLike = '') {
		global $phpbb_root_path, $phpEx;
		
		$argDefaults = array(
			'leftSide' 					=> 	'wp',
			'numToShow' 				=> 	50,
			'numStart'					=> 	0,
			'showOnlyInt' 				=> 	0,
			'showOnlyUnInt' 		=> 	0,
			'showOnlyPosts' 		=> 	0,
			'showOnlyNoPosts' 	=> 	0
		);
		$procArgs = array();
		parse_str($args, $procArgs);
		extract(array_merge($argDefaults, (array)$procArgs));

		@include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	
		$this->leftSide = ($leftSide == 'phpbb') ? 'phpbb' : 'wp';
		$this->numToShow = $numToShow;
		$this->numStart = $numStart;
		$this->showOnlyInt = (int)$showOnlyInt;
		$this->showOnlyUnInt = (int)$showOnlyUnInt;
		$this->showOnlyPosts= (int)$showOnlyPosts;
		$this->showOnlyNoPosts= (int)$showOnlyNoPosts;
		$this->showSpecificUsers = false;
		$this->showUsersLike = (empty($showLike)) ? false : (string)$showLike;

		if(is_array($showSpecificUsers)) { 
			$this->showSpecificUsers = true;
			$this->usersToShow = $showSpecificUsers;
		} else {
			if(!empty($showSpecificUsers)) {
				$this->showSpecificUsers = true;
				$this->usersToShow = (array)$showSpecificUsers;
			} 
			// else leave set at default
		}
		

		$this->users = array();
	
		if($this->leftSide != 'phpbb') { 
			// Process WP users on the left
			$this->load_wp_users();
			$this->find_phpbb_for_wp_users();
			if(!empty($this->showOnlyInt)) {
				foreach($this->users as $key => $usr) {
					if(!$usr->is_integrated()) {
						unset($this->users[$key]);
					}
				}
			}
		} else { 
			// Process phpBB users on the left
			$this->load_phpbb_users();
		}

	}
	
	/**
	 * Loads a list of WordPress users according to the user mapper options
	 * @access private
	 */
	private function load_wp_users() {
		global $wpdb, $phpbbForum, $user;
		
		$where = '';
		if(!empty($this->showSpecificUsers)) {
			/**
			 * @TODO: Add specific user clause cretion here
			 */
			$where = '';
		} else if(!empty($this->showUsersLike)) {
			$where =  $wpdb->prepare('WHERE user_login LIKE %s', '%' . $this->showUsersLike . '%');
		}
		
		$sql = "SELECT ID
				FROM {$wpdb->users} 
				{$where} 
				ORDER BY user_login 
				LIMIT {$this->numStart}, {$this->numToShow}";
				

		$results = $wpdb->get_results($sql);
		
		foreach ((array) $results as $item => $result) {
			$user =  new WPU_Mapped_WP_User($result->ID);
			$this->users[$result->ID] = $user;
		}
		
	
	}
	
	/**
	 * Loads a list of phpBB users according to the loaded user mapper options
	 * @access private
	 */
	private function load_phpbb_users() {
		global $db, $phpbbForum;
		
		$fStateChanged = $phpbbForum->foreground();
		
		$sql =$this->phpbb_userlist_sql($this->showSpecificUsers, $this->showUsersLike);

		if($result = $db->sql_query_limit($sql, $this->numToShow)) {
			while($r = $db->sql_fetchrow($result)) { 
				if( (!empty($this->showOnlyInt)) && (empty($r['user_wpuint_id'])) ) {
					continue;
				}
				if( (!empty($this->showOnlyUnInt)) && (!empty($r['user_wpuint_id'])) ) {
					continue;
				}				
				if( (!empty($this->showOnlyPosts)) && (empty($r['user_posts'])) ) {
					continue;
				}
				if( (!empty($this->showOnlyNoPosts)) && (!empty($r['user_posts'])) ) {
					continue;
				}				
				$user = new WPU_Mapped_Phpbb_User(
					$r['user_id'], 
					$this->transform_result_to_phpbb($r),
					'left'
				);
				$this->users[$r['user_id']] = $user;
				if(!empty($r['user_wpuint_id'])) {
					
					$integWpUser = new WPU_Mapped_WP_User($r['user_wpuint_id']);
					$this->users[$r['user_id']]->set_integration_partner($integWpUser);
				}
			}
		}
		
		$db->sql_freeresult();
		$phpbbForum->background($fStateChanged);
	
	}
	
	/**
	 * Find phpBB users that are integrated to WP users
	 * We treat phpBB users differently from WP users, since we can run a single
	 * query to pull in all the phpBB details rather than looping thru one by one
	 * @access private
	 */
	private function find_phpbb_for_wp_users() {
		global $phpbbForum, $db, $user;
		
		if(!sizeof($this->users)) {
			return;
		}
		
		$arrUsers = array_keys((array)$this->users);
		
		// The phpBB DB is the canonical source for user integration -- don't trust the WP marker
		$fStateChanged = $phpbbForum->foreground();
		
		$sql =$this->phpbb_userlist_sql($arrUsers);
		
		$results = array();
		
		if($pResult = $db->sql_query_limit($sql, $this->numToShow)) {
			while($r = $db->sql_fetchrow($pResult)) {
				$pUser = new WPU_Mapped_Phpbb_User(
					$r['user_id'], 
					$this->transform_result_to_phpbb($r),
					'right'
				);
				$this->users[$r['user_id']]->set_integration_partner($pUser);
			}
		}
		
		$db->sql_freeresult();
		$phpbbForum->background($fStateChanged);
		
		return $results;
		
	}
	/**
	 * Transforms the returned DB object into something our mapped phpBB user class will accept
	 * @access private
	 */
	private function transform_result_to_phpbb($dbResult) {
		global $user, $phpbbForum;
		
		$fStateChanged = $phpbbForum->foreground();
		
		$arrToLoad = array(
			'loginName'				=> 	$dbResult['username'],
			'user_avatar'				=> 	$dbResult['user_avatar'],
			'user_avatar_type'		=> 	$dbResult['user_avatar_type'],
			'user_avatar_width'	=> 	$dbResult['user_avatar_width'], 
			'user_avatar_height'	=> 	$dbResult['user_avatar_height'],
			'email'							=> 	$dbResult['user_email'],
			'group'							=> 	(isset($phpbbForum->lang[$dbResult['group_name']])) ? $phpbbForum->lang[$dbResult['group_name']] : $dbResult['group_name'],
			'rank'							=> 	(isset($phpbbForum->lang[$dbResult['rank_title']])) ? $phpbbForum->lang[$dbResult['rank_title']] : $dbResult['rank_title'],
			'numposts'					=> 	(int)$dbResult['user_posts'],
			'regdate'						=> 	$user->format_date($dbResult['user_regdate']),
			'lastvisit'						=> 	$user->format_date($dbResult['user_lastvisit'])
		);

		$phpbbForum->background($fStateChanged);
		
		return $arrToLoad;
	}
	
	/**
	 * Generates the phpBB SQL for finding users
	 * @access private
	 */
	private function phpbb_userlist_sql($arrUsers = false, $showLike = false) {
		global $db, $phpbbForum;
		
		$fStateChanged = $phpbbForum->foreground();
		
		$where = '';
		if(!empty($arrUsers)) {
			$where = ' AND ' . $db->sql_in_set('u.user_wpuint_id', (array)$arrUsers);
		} else if(!empty($showLike)) {
			$where = " AND u.username LIKE '%" . $db->sql_escape($showLike) . "%'";
		}
		
		 $sql = $db->sql_build_query('SELECT', array(
			'SELECT'	=> 	'u.user_wpuint_id, u.username, u.user_id, u.user_email, r.rank_title, u.user_posts, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_regdate, u.user_lastvisit, g.group_name',
			
			'FROM'	=>	array(
				USERS_TABLE		=> 	'u',
				GROUPS_TABLE	=>	'g'
			),
			
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> 	array(RANKS_TABLE => 'r'),
					'ON'			=>	'u.user_rank = r.rank_id'
				)		
			),
			
			'WHERE'	=>		'g.group_id = u.group_id 
											AND (u.user_type = ' . USER_NORMAL . ' OR u.user_type = ' . USER_FOUNDER . ') ' . 
											$where
		)); 		
		
		$phpbbForum->background($fStateChanged);
		
		return $sql;
		
	}
	
	/**
	 * Sends a JSON string to the browser with enough information to create e.g. an autocomplete dropdown
	 */
	
	public function send_json() {
		
		header('Content-type: application/json; charset=utf-8');
		
		$json = array();
		foreach($this->users as $user) {
			$statusCode = ($user->is_integrated()) ? 0 : 1;
			$status = ($statusCode == 0) ? __('Already integrated') : __('Available');
			
			$data = '{' .
				'"value": ' . $user->userID . ',' . 
				'"label": "' . $user->get_username() . '",' . 
				'"desc": "' . $user->get_email() . '",' . 
				'"status": "' . $status . '",' . 
				'"statuscode": ' . $statusCode . ',' . 
				'"avatar": "' . str_replace('"', "'", $user->get_avatar()) . '"' . 
				'}';
				
				$json[] = $data;
		}
		if(sizeof($json)) {
			die('[' . implode($json, ',') . ']');
		} else {
			die('{}');
		}
	}
}

?>