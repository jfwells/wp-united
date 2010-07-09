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
	protected $loginClassName;
	
	protected $side;
	
	protected $type;
	
	
	public $userID;

	
	public function __construct($userID) {
		$this->userID = $userID;
		$this->isLoaded = true;
		$this->integrated = false;
		$this->side = 'left';
	}
	
	/**
	 * Returns whether this user is integrated
	 */
	public function is_integrated() {
		return $this->integrated;
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
	 * Returns html markup for a break action
	 */
	public function break_action() {
		
		if(!$this->integrated) {
			return '';
		}
		
		$action = sprintf(
			'<a href="#" class="wpumapactionbrk" onclick="return wpuMapBreak(%d, %d, \'%s\', \'%s\', \'%s\', \'%s\');">' . __('Break Integration') . '</a>',
			$this->userID,
			$this->integratedUser->userID,
			$this->loginName,
			$this->integratedUser->get_username(),
			$this->userDetails['email'],
			$this->integratedUser->get_email()
		);
		
		return $action;
	}
	
	/**
	 * Returns html markup for create counterpart action
	 */
	public function create_action() {
		$altPackage = ($this->type == 'wp') ? __('phpBB') : __('WordPress');
		$action = sprintf(
			'<a href="#" class="wpumapactioncreate" onclick="return wpuMapCreate(%d, \'%s\', \'%s\', \'%s\');">' . sprintf(__('Create user in %s'), $altPackage) . '</a>',
			$this->userID,
			($this->type == 'wp') ? 'phpbb' : 'wp',
			$this->loginName,
			$this->userDetails['email']
		);
		
		return $action;
	}
	
	/**
	 * Returns the html markup for a "delete both users" action
	 */
	public function delboth_action() {
		$action = sprintf(
			'<a href="#" class="wpumapactiondel" onclick="return wpuMapDelBoth(%d, %d, \'%s\', \'%s\', \'%s\', \'%s\');">' . __('Delete user from both phpBB and WordPress') . '</a>',
			$this->userID,
			($this->integrated) ? $this->integratedUser->userID : 0,
			$this->loginName,
			($this->integrated) ? $this->integratedUser->get_username() : '',
			$this->userDetails['email'],
			($this->integrated) ? $this->integratedUser->get_email() : ''	
		);
		
		return $action;
	}
	
	public function del_action() {
		$package = ($this->type == 'phpbb') ? __('phpBB') : __('WordPress');
		$action = sprintf(
			'<a href="#" class="wpumapactiondel" onclick="return wpuMapDel(%d, \'%s\', \'%s\', \'%s\');">' . sprintf(__('Delete user from %s'), $package) . '</a>',
			$this->userID,
			$this->type,
			$this->loginName,
			$this->userDetails['email']
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
		$template = '<div class="wpuuser ' . $this->className . $side . '">' . 
					'<p class="' . $this->loginClassName . '"><a class="wpuprofilelink" href="' . $this->get_profile_link() . '">' . $this->loginName . '</a></p>' . 
					'<div class="avatarblock">' .
					$this->avatar . 
					 '<small>' . $this->del_action() . '</small>' . 
					 '<small>' . $this->edit_action() . '</small>' . 
					'</div>' .
					'<div style="float: left;" class="wpudetails">' ;
		
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
		
		$this->className = "wpuwpuser";
		$this->loginClassName = "wpuwplogin";
		$this->type = 'wp';
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
		
		$this->className = "wpuphpbbuser";
		$this->loginClassName = "wpuphpbblogin";
		
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
	 * For phpBB phpBB users we provide all the data to the constructor in an array to create the user
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
	
	public function __toString() {
		return parent::__toString();
	}
	
}
	


?>