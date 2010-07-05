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
	
	public function is_integrated() {
		return $this->integrated;
	}
	
	public function get_partner() {
		return $this->integratedUser;	
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
			'posts' 				=> 	'<p"><strong>' . __('Posts:') . '</strong> %s / <strong>',
			'comments'			=>	__('Comments:') . '</strong> %s</p>',
			'regdate' 			=> 	'<p>' . __('Registered:') . '</strong> %s</p>',
		);
		
		$this->className = "wpuwpuser";
		$this->loginClassName = "wpuwplogin";
		$this->load_details();
	}
	
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
			'posts'			=>	(string)get_usernumposts($result->ID),
			
			'numcomments'	=>	(string) $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = %d ", $result->ID)),
			'regdate'				=>	$wpRegDate
		);
	
	}
	
	public function find_integration_partner() {
				
		if( ($testUser = new WPU_Mapped_Phpbb_User($this->userID, true)) && ($testUser->is_integrated()) ){
			$this->integrated = true;
			$this->integratedUser = $testUser; 
		} else {
			$this->integrated = false;
		}
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

	public function __construct($userID, $findFromWP = false) { 
		parent::__construct($userID);
		
		$this->templateFields = array(
			'group' 		=> '<p><strong>' . __('Default group:') . '</strong> %s</p>',
			'email' 		=> '<p><strong>' . __('E-mail:') . '</strong> %s</p>',
			'rank' 			=> '<p><strong>' . __('Rank:') . '</strong> %s</p>',
			'numposts' 	=> '<p><strong>' . __('Posts:') . '</strong> %s</p>',
			'regdate' 	=> '<p><strong>' . __('Registered:') . '</strong> %s</p>',
			'lastvisit' 	=> '<p><strong>' . __('Last visited:') . '</strong> %s</p>',
		);
		
		$this->className = "wpuphpbbuser";
		$this->loginClassName = "wpuphpbblogin";
		
		if($findFromWP) {
			$this->side = 'right';
			$this->userID = 0;
			if($this->load_details_from_wp_id($userID)) {
				$this->integrated = true;
			} 
		} else {
			$this->load_details($userID);  // not implemented yet
		}
	}
	
	private function load_details_from_wp_id($wpID) {
		global $phpbbForum, $db, $user;
		
		// The phpBB DB is the canonical source for user integration -- don't trust the WP marker
		$fStateChanged = $phpbbForum->foreground();
		
		$sql = $db->sql_build_query('SELECT', array(
			'SELECT'	=> 	'u.username, u.user_id, u.user_email, r.rank_title, u.user_posts, u.user_regdate, u.user_lastvisit, g.group_name',
			
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
			
			'WHERE'	=>		"g.group_id = u.group_id 
											AND u.user_wpuint_id = {$wpID}"
		
		)); 
	
		$pResult = $db->sql_query_limit($sql, 1);
		
		$this->integrated = false;
		
		if ( $pResult && (sizeof($pResult)) ) {
			if ($integDets = $db->sql_fetchrow($pResult)) {
				
				$this->userID = $integDets['user_id'];
				$this->loginName = $integDets['username'];
				$this->avatar = '<img src="#" style="width: 50px; height: 50px;" />'; // temp
				
				$this->userDetails = array(
					'email'			=> $integDets['user_email'],
					'group'			=> (isset($phpbbForum->lang[$integDets['group_name']])) ? $phpbbForum->lang[$integDets['group_name']] : $integDets['group_name'],
					'rank'			=> (isset($phpbbForum->lang[$integDets['rank_title']])) ? $phpbbForum->lang[$integDets['rank_title']] : $integDets['rank_title'],
					'numposts'	=> $integDets['user_posts'],
					'regdate'		=> $user->format_date($integDets['user_regdate']),
					'lastvisit'		=> $user->format_date($integDets['user_lastvisit'])
				);
				$this->integrated = true;
			}
		}

		$db->sql_freeresult();
		$phpbbForum->background($fStateChanged);

		return $this->integrated;
		
	}
	
	public function __toString() {
		return parent::__toString();
	}
	
}
	


?>