<?php
/**
 * Represents individual users in the mapper -- either on the left-hand or right-hand side.
 * Echoing out an instance of this class directly provides a nicely formatted user block
 */
abstract class WPU_Mapped_User {

	protected $templateFields;
	protected $userDetails;
	protected $loginName;
	protected $avatar;
	protected $integratedUser;
	protected $integrated;
	
	protected $adminLink;
	
	protected $className;
	protected $htmlID;
	protected $loginHtmlID;
	protected $loginClassName;
	
	protected $side;
	
	protected $type;
	
	
	public $userID;

	
	public function __construct($userID) {
		$this->userID = $userID;
		$this->isLoaded = true;
		$this->integrated = false;
		$this->side = 'left';
		$this->htmlID = '';
		$this->loginHtmlID = '';
	}
	
	/**
	 * Returns whether this user is integrated
	 */
	public function is_integrated() {
		return $this->integrated;
	}
	
	/**
	 * Returns whether the user has posts
	 */
	public function has_posts() {
		if($this->userDetails['posts'] > 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * Returns the user object that this user is integrated to in the internal data structure
	 */
	public function get_partner() {
		return $this->integratedUser;	
	}
	
	/**
	 * Returns the current user's e-mail address
	 */
	public function get_email() {
		return $this->userDetails['email'];
	}
	
	/** 
	 * Returns the current user's username
	 */
	public function get_username() {
		return $this->loginName;
	}
	
	/**
	 * Returns the current user's avatar
	 */
	public function get_avatar() {
		return $this->avatar;
	}
	
	/**
	 * Returns html markup for a break action
	 */
	public function break_action() {
		
		if(!$this->integrated) {
			return '';
		}
		
		$action = sprintf(
			'<a href="#" class="wpumapactionbrk" id="wpuaction-break-%s-%d-%d">' . __('Break Integration') . '</a>',
			$this->type,
			$this->userID,
			$this->integratedUser->userID
		);
		return $action;
	}
	
	/**
	 * Returns html markup for create counterpart action
	 */
	public function create_action() {
		$altPackage = ($this->type == 'wp') ? __('phpBB') : __('WordPress');
		
		$action = sprintf(
			'<a href="#" class="wpumapactioncreate" id="wpuaction-create-%s-%d">' . sprintf(__('Create user in %s'), $altPackage) . '</a>',
			$this->type,
			$this->userID
		);
		return $action;
	}
	
	/**
	 * Returns the html markup for a "delete both users" action
	 */
	public function delboth_action() {
		
$action = sprintf(
			'<a href="#" class="wpumapactiondel" id="wpuaction-delboth-%s-%d-%d">' . __('Delete user from both phpBB and WordPress') . '</a>',
			$this->type,
			$this->userID,
			($this->integrated) ? $this->integratedUser->userID : 0
		);		
		return $action;
	}
	
	public function del_action() {
		$package = ($this->type == 'phpbb') ? __('phpBB') : __('WordPress');
		$action = sprintf(
			'<a href="#" class="wpumapactiondel" id="wpuaction-del-%s-%d">' . sprintf(__('Delete user from %s'), $package) . '</a>',
			$this->type,
			$this->userID
			
		);		
		return $action;
	}
	
	
	/**
	 * Sets the user object that this user is integrated to in the internal data structure
	 * @param WPU_Mapped_User $user the user to integrate to in the internal data structure
	 */
	public function set_integration_partner($user) {	
		$this->integratedUser = $user; 
		$this->integrated = true;
	}	
	
	
	/**
	 * Presents a nicely formatted block of the user details in a standardised format
	 */
	public function __toString() {
		$side = ($this->side == 'left') ? '' : ' wpuintuser';
		$template = '<div class="wpuuser ' . $this->className . $side . '" id="' . $this->htmlID . '">' . 
			'<p class="' . $this->loginClassName . '" id="' . $this->loginHtmlID . '"><a class="wpuprofilelink" href="' . $this->get_profile_link() . '">' . htmlentities($this->loginName) . '</a></p>' . 
			'<div class="avatarblock">' .
			 $this->avatar . 
			 $this->del_action()  .
			 $this->edit_action() .
			'</div>' .
			'<div class="wpudetails">' ;
		
		foreach($this->templateFields as $field => $show) {
			$template .= sprintf($show, $this->userDetails[$field]);
		}
		
					
		$template .= '</div><br /></div>';
		
		return $template;
	}
	
	
}

/**
 * WordPress mapped user class
 * Corresponds to a WordPress user in the mapping tree.
 * echoing out an instance of this class displays a nicely-formatted user block containing all their details.
 */
class WPU_Mapped_WP_User extends WPU_Mapped_User {

	public function __construct($userID) { 
		parent::__construct($userID);
		
		$this->templateFields = array(
			'displayname' 	=> 	'<p><strong>' . __('Display name:') . '</strong> %s</p>',
			'email' 				=> 	'<p><strong>' . __('E-mail:') . '</strong> %s</p>',
			'website' 			=> 	'<p><strong>' . __('Website:') . '</strong> %s</p>',
			'rolelist' 				=> 	'<p><strong>%s</strong> ',
			'roletext'				=>	'%s</p>',
			'posts' 				=> 	'<p><strong>' . __('Posts:') . '</strong> %s / <strong>',
			'comments'			=>	__('Comments:') . '</strong> %s</p>',
			'regdate' 			=> 	'<p>' . __('Registered:') . '</strong> %s</p>',
		);
		
		$this->className = 'wpuwpuser';
		$this->loginClassName = 'wpuwplogin';
		$this->loginHtmlID = 'wpuwplogin' . $userID;
		$this->type = 'wp';
		$this->htmlID = 'wpuwpuser' . $userID;
		$this->load_details();
	}
	
	/**
	 * Loads in all the details for this user from WordPress
	 * Note that this is inefficient, as for phpBB users we can pull all users with a single query
	 * @TODO: externalise this to mapper class
	 * @access private
	 */
	private function load_details() {
		global $phpbbForum, $wpdb, $user;
		
		$wpUser = new WP_User($this->userID);	
			
		/**
		* @TODO: This is inefficient, change to wordpress format date func
		*/
		$phpbbForum->foreground();
		$wpRegDate = $user->format_date($wpUser->user_registered);
		$phpbbForum->background();
		
		$this->loginName = $wpUser->user_login;
		$this->avatar = get_avatar($wpUser->ID, 50);
		
		$this->userDetails = array(
			'displayname'		=>	$wpUser->display_name,
			'email'					=>	$wpUser->user_email,
			'website'				=>	 !empty($wpUser->user_url) ? $wpUser->user_url : __('n/a'),
			'rolelist'				=>	 implode(', ', (array)$wpUser->roles),
			'roletext'				=>	 (sizeof($wpUser->roles) > 1) ? __('Roles:') : __('Role:'),
			/* @TODO in wp3: this is count_user_posts() */ 
			'posts'					=>	get_usernumposts($this->userID),
			
			'comments'			=>	$wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = %d ", $this->userID)),
			'regdate'				=>	$wpRegDate
		);
	
	}
	
	public function edit_action() {
		$action = '<a href="user-edit.php?user_id=' . $this->userID . '" class="wpumapactionedit">' . __('Edit user') . '</a>';
		return $action;
	}
	
	public function get_profile_link() {
		return get_author_posts_url($this->userID);
	}
	
	/**
	 * Provides a formatted block of suggested usernames this use could integrate to
	 */
	public function get_suggested_matches() {
		if($this->is_integrated()) {
			return;
		}
		
		$noMatch = '<p><em>' . __('No suggested matches found.') . '</em></p>';
		
		// Search for suggested matches in phpBB...
		
		// if last char of username is number, kill it to make stub
		// look for uppercase match of e-mail OR uppercase match of stub to username
		$username = $this->get_username();
		$stub = is_numeric($username[strlen($username)-1]) ? substr($username, 0, -1) : $username;
		
		global $phpbbForum, $db;
		$fStateChanged = $phpbbForum->foreground();
		
		$sql = 'SELECT user_id, username, user_email, user_wpuint_id 
			FROM ' . USERS_TABLE . "
			WHERE UCASE(username) LIKE '%" . $db->sql_escape(strtoupper($stub)) . "%' 
				OR UCASE(user_email) = '" . $db->sql_escape(strtoupper($this->get_email())) . "'";

		
		if(!$results = $db->sql_query($sql)) {
			echo $noMatch;
			return;
		}
		
		if(!sizeof($results)) {
			echo $noMatch;
			return;
		}
		
		$matches = '';
		while($result = $db->sql_fetchrow($results)) {
			$integText = (empty($result['user_wpuint_id'])) ? __('Available') : __('Cannot integrate (already integrated)');
			$integLink =  (!empty($result['user_wpuint_id'])) ? '' : sprintf(
				'<a href="#" class="wpumapactionlnk" onclick="return wpuMapIntegrate(this, %d, %d, \'%s\', \'%s\', \'%s\', \'%s\');">' . __('Integrate') . '</a>',
				$this->userID,
				$result['user_id'],
				$this->loginName,
				$result['username'],
				$this->get_email(),
				$result['user_email']
			);
			$matches .= '<p><strong>' . $result['username'] . '</strong> <em>' . $result['user_email'] . '</em><br />' . $integText . ' ' . $integLink . '</p>';
		}
		
		$db->sql_freeresult();
		$phpbbForum->background($fStateChanged);
		
		echo $matches;
		
	}
	
	
	
	public function __toString() {
		return parent::__toString();
	}

}


/**
 * phpBB mapped user class
 * Corresponds to a WordPress user in the mapping tree.
 * echoing out an instance of this class displays a nicely-formatted user block containing all their details.
 */
class WPU_Mapped_Phpbb_User extends WPU_Mapped_User {

	public function __construct($userID, $userData = false, $pos = 'left') { 
		parent::__construct($userID);
		
		$this->templateFields = array(
			'group' 		=> '<p><strong>' . __('Default group:') . '</strong> %s</p>',
			'email' 		=> '<p><strong>' . __('E-mail:') . '</strong> %s</p>',
			'rank' 			=> '<p><strong>' . __('Rank:') . '</strong> %s</p>',
			'posts' 		=> '<p><strong>' . __('Posts:') . '</strong> %s</p>',
			'regdate' 	=> '<p><strong>' . __('Registered:') . '</strong> %s</p>',
			'lastvisit' 	=> '<p><strong>' . __('Last visited:') . '</strong> %s</p>',
		);
		
		$this->className = 'wpuphpbbuser';
		$this->loginClassName = 'wpuphpbblogin';
		$this->htmlID = 'wpuphpbbuser' . $userID;
		$this->loginHtmlID = 'wpuphpbblogin' . $userID;
		
		$this->userID = $userID;
		$this->side = $pos;
		$this->type = 'phpbb';
		
		if(is_array($userData)) {
			if($this->load_from_userdata($userData)) {
				$this->integrated = true;
			} 
		} else {
			$this->load_details($userID);  // not implemented yet
		}
	}
	
	/**
	 * For phpBB users we provide all the data to the constructor in an array to create the user
	 * @access private
	 */
	private function load_from_userdata($data) {
		global $phpbbForum;
		
		// The phpBB DB is the canonical source for user integration -- don't trust the WP marker
		$fStateChanged = $phpbbForum->foreground();
		
		$this->loginName = $data['loginName'];
				
		$this->load_avatar($data['user_avatar'], $data['user_avatar_type'], $data['user_avatar_width'], $data['user_avatar_height']);
				
		$this->userDetails = array(
			'email'			=> $data['email'],
			'group'			=> $data['group'],
			'rank'			=> $data['rank'],
			'posts'			=> $data['numposts'],
			'regdate'		=> $data['regdate'],
			'lastvisit'		=> $data['lastvisit'],
		);
		
		$this->set_admin_link();

		$phpbbForum->background($fStateChanged);
		
	}
	
	public function get_profile_link() {
		global $phpEx, $phpbbForum;
		return add_trailing_slash($phpbbForum->url) .  "memberlist.{$phpEx}?mode=viewprofile&amp;u={$this->userID}";
	}
	
	/**
	 * We use a setter rather than a getter to avoid the overhead of forum context switching
	 * for append_sid()
	 */
	private function set_admin_link() {
		global $phpbbForum;
		
		$fStateChanged = $phpbbForum->foreground();
		$this->adminLink = $phpbbForum->url . append_sid('adm/index.php?i=users&amp;mode=overview&amp;u=' . $this->userID, false, true, $GLOBALS['user']->session_id);
		$phpbbForum->background($fStateChanged);
	}
	

	public function edit_action() {
		$action = '<a href="' . $this->adminLink . '" class="wpumapactionedit">' . __('Edit user') . '</a>';
		return $action;
	}	
	
	/**
	 * Creates the avatar for the current user
	 * @access private
	 */
	private function load_avatar($avatar, $type, $width, $height) {
		global $phpbbForum, $phpbb_root_path;
		
		$fStateChanged = $phpbbForum->foreground();
				
		$av = '';
		if(function_exists('get_user_avatar')) { 
			$av = get_user_avatar($avatar, $type, $width, $height);
		}
		
		if(!empty($av)) {
			$av = explode('"', $av); 
			$av = str_replace($phpbb_root_path, $phpbbForum->url, $av[1]);
			$av = "<img src='{$av}' class='avatar avatar-50'  />";
		}
		
		$avClass = (empty($av)) ?  'avatar wpuempty' : 'avatar';
		$this->avatar = '<div class="' . $avClass . '">' . $av . '</div>';	
		
		$phpbbForum->background($fStateChanged);
		
		return $this->avatar;	
	}
	
	/**
	 * Provides a formatted block of suggested usernames this user could integrate to
	 */
	public function get_suggested_matches() {
		global $phpbbForum, $wpdb, $db;
		
		
		if($this->is_integrated()) {
			return;
		}
		
		$noMatch = '<p><em>' . __('No suggested matches found.') . '</em></p>';
		
		// Search for suggested matches in WordPress
		
		// if last char of username is number, kill it to make stub
		// look for uppercase match of e-mail OR uppercase match of stub to username
		$username = $this->get_username();
		$stub = is_numeric($username[strlen($username)-1]) ? substr($username, 0, -1) : $username;
		
		$sql = $wpdb->prepare("SELECT ID, user_login, user_email
				FROM {$wpdb->users} 
				WHERE UCASE(user_login) LIKE %s
					OR UCASE(user_email) = %s
				ORDER BY user_login ", '%' . strtoupper($stub) . '%', strtoupper($this->get_email()));
				
		if(!$results = $wpdb->get_results($sql)) {
			return $noMatch;
		}
		
		if(!sizeof($results)) {
			return $noMatch;
		}
		$users = array();
		foreach ((array) $results as $item => $result) {
			$users[$result->ID] = array(
				'username'	=>	$result->user_login,
				'email'			=>	$result->user_email,
				'integrated'	=>	false
			);
		}
		
		$fStateChanged = $phpbbForum->foreground();
		
		$sql = 'SELECT user_id, user_wpuint_id FROM ' . USERS_TABLE . ' 
					WHERE ' . $db->sql_in_set('user_wpuint_id', array_keys($users));
					
		if($pResults = $db->sql_query($sql)) {
			while($pResult = $db->sql_fetchrow($pResults)) {
				if(!empty($pResult['user_wpuint_id'])) {
					$users[$pResult['user_wpuint_id']]['integrated'] = true;
				}
			}
		}
		$db->sql_freeresult();	
		$phpbbForum->background($fStateChanged);
		
		$matches = '';
		foreach($users as $userID => $user) {
			$integText = (!$user['integrated']) ? __('Available') : __('Cannot integrate (already integrated)');
			$integLink = ($user['integrated']) ? '' : sprintf(
				'<a href="#" class="wpumapactionlnk" onclick="return wpuMapIntegrate(this, %d, %d, \'%s\', \'%s\', \'%s\', \'%s\');">' . __('Integrate') . '</a>',
				$this->userID,
				$userID,
				$this->loginName,
				$user['username'],
				$this->get_email(),
				$user['email']
			);
			$matches .= '<p><strong>' . $user['username'] . '</strong> <em>' . $user['email'] . '</em><br />' . $integText . ' ' . $integLink . '</p>';
		}
		
		
		
		echo $matches;
	}
	
	public function __toString() {
		return parent::__toString();
	}
	
}
	


?>