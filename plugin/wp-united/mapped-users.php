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
	
	protected $className;
	protected $loginClassName;
	
	public $userID;
	protected $userDetails;
	
	
	
	public function __construct($userID) {
		$this->userID = $userID;
		$this->isLoaded = true;
	}
	
	
	/**
	 * Presents a nicely formatted block of the user details in a standardised format
	 */
	public function toString() {
		$template = '<div class="' . $this->className . '">' . 
					'<p class="' . $this->loginClassName . '"><a href="#">' . $this->loginName . '</a></p>' . 
					$this->avatar . 
					'<div style="float: left;">' ;
					
		foreach($this->templateFields as $field => $show) {
			$template .= sprintf($show, $this->userDetails[$field]);
		}
					
		$template .= '</div><br /></div>';
		
		return $template
	}
	
	
}

/**
 * WordPress mapped user class
 * Corresponds to a WordPress user in the mapping tree.
 * echoing out an instance of this class displays a nicely-formatted user block containing all their details.
 */
class WPU_Mapped_WP_User extends WPU_Mappped_User {

	public function __construct($userID) {
		parent::__construct($userID);
		
		$this->templateFields = array(
			'displayname' => '<p class="wpuwpdetails"><strong>' . __('Display name:') . '</strong> %s</p>',
			'email' => '<p class="wpuwpdetails"><strong>' . __('E-mail:') . '</strong> %s</p>',
			'website' => '<p class="wpuwpdetails"><strong>' . __('Website:') . '</strong> %s</p>',
			'rolelist' => '<p class="wpuwpdetails"><strong>%s</strong> ',
			'roletext'		=>	'%s</p>',
			'posts' => '<p class="wpuwpdetails"><strong>' . __('Posts:') . '</strong> %s / <strong>',
			'comments'	=>	__('Comments:') . '</strong> %s</p>',
			'regdate' => '<p class="wpuwpdetails"><strong>' . __('Registered:') . '</strong> %s</p>',
		);
		
		$this->className = "wpuwpuser";
		$this->loginClassName = "wpuwplogin";
		
		$this->loadDetails();
		
	}
	
	private function loadDetails() {
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
			'displayname'			=>	$wpUser->display_name,
			'email'					=>	$wpUser->user_email,
			'website'				=>	 !empty($wpUser->user_url) ? $wpUser->user_url : __('n/a'),
			'rolelist'				=>	 implode(', ', (array)$wpUser->roles),
			'roletext'				=>	 (sizeof($wpUser->roles) > 1) ? __('Roles:') : __('Role:'),
			/* @TODO in wp3: this is count_user_posts() */ 
			'posts'			=>	(string)get_usernumposts($result->ID),
			
			'numcomments'	=>	(string) $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = %d ", $result->ID)),
			'regdate'				=>	$wpRegDate
		);
	
	}
	
	
	function __toString() {
		return parent::toString();
	}

}
?>