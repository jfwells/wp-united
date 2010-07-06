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
	
	protected $className;
	protected $loginClassName;
	
	protected $side;
	
	
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
		$template = '<div class="' . $this->className . $side . '">' . 
					'<p class="' . $this->loginClassName . '"><a href="#">' . $this->loginName . '</a></p>' . 
					$this->avatar . 
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

		$phpbbForum->background($fStateChanged);
		
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