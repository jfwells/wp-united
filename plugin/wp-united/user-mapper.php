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
	private $numStart = 0;
	
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
	public function __construct($args, $showSpecificUsers = 0) {
	
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

	
		$this->leftSide = ($leftSide == 'phpbb') ? 'phpbb' : 'wp';
		$this->numToShow = $numToShow;
		$this->numStart = $numStart;
		
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
		
		/**
		 * @TODO: complete where clause creation here
		 */
		
		if(!$this->showSpecificUsers) {
			$where = '';
		}
		
		$sql = "SELECT ID
				FROM {$wpdb->users}
				ORDER BY user_login {$where}
				LIMIT {$this->numStart}, {$this->numToShow}";
				

		$results = $wpdb->get_results($sql);

		foreach ((array) $results as $item => $result) {
			
			$user =  new WPU_Mapped_WP_User($result->ID);
			
			$user->find_integration_partner();

			$this->users[$result->ID] = $user;
			
			//if($pos=='left') {
		/////		$this->users[$result->ID]['integration'] = $this->get_phpbb_users('right', 0, $result->ID);
			//}
			
		}
	
	}
	
	/**
	 * Loads a list of phpBB users according to the loaded user mapper options
	 * @access private
	 */
	private function load_phpbb_users() {
	
	
	}
	
	
}

?>