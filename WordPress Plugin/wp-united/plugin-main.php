<?php

/** 
*
* Plugin-main: The main WordPress plugin class
*
* @package WP-United
* @version $Id: v0.9.0.3 2012/12/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2012 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/

if ( !defined('ABSPATH') && !defined('IN_PHPBB') ) exit;


class WP_United_Plugin extends WP_United_Plugin_Base {

	protected
		// Actions and filters. These are loaded as needed depending on which WP-United portions are active.
		// Format: array( event	|	function in this class -- in an array if optional arguments are needed | loading circumstances)
		$actions = array(
			array('plugins_loaded', 					'init_plugin',								'all'),  // this should be 'init', but we want to play with current_user, which comes earlier
			array('shutdown', 							array('buffer_end_flush_all', 1),			'all'),
			array('wp_head', 							'add_scripts',								'all'),
			array('comment_form', 						'generate_smilies',							'phpbb-smilies'),
			array('wp_head', 							'add_head_marker',							'template-int'),
			array('switch_theme', 						'clear_header_cache',						'template-int'),
			array('set_current_user', 					'integrate_users',							'user-int'),
			array('wp_logout', 							'phpbb_logout',								'user-int'),			
			array('register_post', 						array('validate_new_user', 10, 3),			'user-int'),
			array('user_register', 						array('process_new_wp_reg', 10, 1),			'user-int'),
			array('profile_update', 					array('profile_update', 10, 2),				'user-int'),
			array('admin_menu', 						'add_xposting_box',							'x-posting'),			
			array('edit_post', 							'just_editing_post',						'x-posting'),
			array('wp_insert_post', 					array('capture_future_post', 10, 2),		'x-posting'),
			array('publish_post', 						array('handle_new_post', 10, 2),			'x-posting'),
			array('future_to_publish', 					array('future_to_published', 10),			'x-posting'),			
			array('comment_form', 						'comment_redir_field',						'x-posting'),
			array('pre_comment_on_post', 				'comment_redirector',						'x-posting'),
			array('comments_open', 						array('comments_open', 10, 2),				'x-posting')	
		),

		$filters = array(
			array('plugin_row_meta', 					array('add_plugin_menu_link', 10, 2), 		'all'),
			array('page_link', 							array('fix_forum_link', 10, 2), 			'all'),
			array('admin_footer_text', 					'admin_footer_text', 						'all'),
			array('the_content', 						'check_content_for_forum', 					'all'),
			array('comment_text', 						'censor_content', 							'phpbb-censor'),
			array('the_title', 							'censor_content', 							'phpbb-censor'),
			array('the_excerpt', 						'censor_content', 							'phpbb-censor'),
			array('comment_text', 						'smilies', 									'phpbb-smilies'),
			array('get_avatar', 						array('get_avatar', 10, 5), 				'user-int'),			
			array('pre_user_login', 					'fix_blank_username', 						'user-int'),
			array('validate_username', 					array('validate_username_conflict', 10, 2),	'user-int'),
			array('authenticate', 						array('authenticate', 21, 3), 				'user-int'),
			array('get_comment_author_link',			'get_comment_author_link',					'x-posting'),
			array('comments_array', 					array('load_phpbb_comments', 10, 2),			'x-posting'),
			array('get_comments_number', 				array('comments_count', 10, 2),				'x-posting'),
			array('pre_option_comment_registration', 	'no_guest_comment_posting',					'x-posting'),
			array('edit_comment_link', 					array('edit_comment_link', 10, 2),			'x-posting'),
			array('get_comment_link', 					array('comment_link', 10, 3),				'x-posting')		
		);
		
		private
			$doneInit = false;


	
	public function wp_init() {
		
		// (re)load our settings
		$this->load_settings();
		
		load_plugin_textdomain('wp-united', false, 'wp-united/languages/');
		
		require_once($this->get_plugin_path() . 'template-tags.php');
		
		if($this->get_setting('integrateLogins') != 'NONE') {		
			require_once($this->get_plugin_path() . 'user-integrator.php'); 
		}
		if($this->get_setting('xposting')) {		
			require_once($this->get_plugin_path() . 'functions-cross-posting.php');
		}
		
		// we want to override some actions. These must match the priority of the built-ins 
		remove_action('shutdown', 'wp_ob_end_flush_all', 1);
		
		// add new actions and filters
		$this->add_actions();
		$this->add_filters();
		unset($this->actions, $this->filters);
	
	}



	
	/**
	 * The main invocation logic -- if enabled, load phpBB too!
	 */
	public function init_plugin() { 
		global $phpbbForum;
		
		
		if($this->has_inited()) {
			return false;
		}
		$this->doneInit = true;
		
		$shouldRun = true;
		
		// this has to go prior to phpBB load so that connection can be disabled in the event of an error on activation.
		$this->process_adminpanel_actions();
		
		
		// disable login integration if we couldn't override pluggables
		if(defined('WPU_CANNOT_OVERRIDE')) {
			$this->settings['integrateLogin'] = 0;
		}		

		if(!$this->get_setting('phpbb_path') || !$phpbbForum->can_connect_to_phpbb()) {
			$this->set_last_run('disconnected');
			$shouldRun = false;
		}
		
		if($this->get_last_run() == 'connected') {
			$shouldRun = false;
		}
		
		$this->set_last_run('connected');
		
		$versionCheck = $this->check_mod_version();
		if($versionCheck['result'] != 'OK') {
			$this->disable();
			$shouldRun = false;
		}
		
		if($this->is_enabled() && $shouldRun) { 
		
			$this->load_phpbb();
			
			$this->set_last_run('working');
		
			require_once($this->get_plugin_path() . 'widgets.php');
			add_action('widgets_init', 'wpu_widgets_init');
		
		}
		
		$this->process_frontend_actions();

		return true; 
			
	}
	
	public function load_phpbb() {
		global $phpbbForum;


		if ( !defined('IN_PHPBB') ) { 
			if(is_admin()) {
				define('WPU_PHPBB_IS_EMBEDDED', TRUE);
			} else {
				define('WPU_BLOG_PAGE', 1);
			}
			$phpbbForum->load();
			
			$this->set_last_run('working');
			$this->init_style_keys();
		}
		
	}
	
	/**
	 * Transmit settings to phpBB
	 */
	public function transmit_settings($enable = true) {
		global $phpbbForum;
		
		//if WPU was disabled, we need to initialise phpBB first
		// phpbbForum is already inited, however -- we just need to load
		if (!defined('IN_PHPBB')) {
			
			$this->set_last_run('connected');
			
			$this->load_phpbb();
		}
	
		// store data before transmitting
		if($enable) {
			$this->enable();
		} else {
			$this->disable();
		}
		
		$dataToStore = $this->settings;
		
		if($phpbbForum->synchronise_settings($dataToStore)) { 
			if($enable) {
				$this->set_last_run('working');
			}
			die('OK');
		} else {
			$this->set_last_run('connected');
			die('NO');
		}	
	}


	public function has_inited() {
		return $this->doneInit;
	}
	
	private function set_last_run($status) {
		if($this->get_last_run() != $status) { 
			// transitions cannot go from 'working' to 'connected'.
			if( ($this->lastRun == 'working') && ($status == 'connected') ) {
				return;
			} 
			$this->lastRun = $status;
			update_option('wpu-last-run', $status);
		}
	}
	
	public function get_last_run() {
	
		if(empty($this->lastRun)) {
			$this->lastRun = get_option('wpu-last-run');
		}

		 return $this->lastRun;
	}
	
	public function phpbb_logout() {
		if($this->is_working()) {
			global $phpbbForum;
			$phpbbForum->logout();
		}
	}
	
	public function update_settings($data) {
		$this->settings->update_settings($data);
	}
	
	public function integrate_users() {
		if($this->is_working() && $this->get_setting('integrateLogin') && !defined('WPU_DISABLE_LOGIN_INT')) {
			wpu_integrate_login();
		}
	}
	
	/**
	 * Adds main menu
	 */
	public function add_plugin_menu_link($links, $file) {

		if ($file == 'wp-united/wp-united.php') {
			$links[] = '<a href="admin.php?page=wp-united-setup">' . __('Setup / Status', 'wp-united') . '</a>';
		}
		return $links;
	}
	

	/**
	 * Process inbound actions and set up the settings panels
	 */
	private function process_adminpanel_actions() {

		if(is_admin()) {
			
			require_once($this->get_plugin_path() . 'settings-panel.php');
			
			// the settings page has detected an error and asked to abort
			if( isset($_POST['wpudisable']) && check_ajax_referer( 'wp-united-disable') ) {
				$this->disable_connection('server-error'); 
			}	

			// the user wants to manually disable
			if( isset($_POST['wpudisableman']) && check_ajax_referer( 'wp-united-disable') ) {
				$this->disable_connection('manual');
			}		

			if( isset($_POST['wpusettings-transmit']) && check_ajax_referer( 'wp-united-transmit') ) { 
				wpu_process_settings();
			}
	
			// file tree
			if( isset($_POST['filetree']) && check_ajax_referer( 'wp-united-filetree') ) {
				wpu_filetree();
			}	
			
		}
	}
	
	/**
	 * Process any requests bound for AJAX backend, etc..
	 */
	private function process_frontend_actions() {
		global $phpbbForum;
		
		if(!is_admin() && $this->is_working()) {
			if( isset($_POST['wpupoll']) && check_ajax_referer( 'wpu-poll-submit') ) {
				$phpbbForum->get_poll();
				exit;
			}
		}
	
	}
	
	/**
	 * Check the permalink to see if this is a link to the forum. 
	 * If it is, replace it with the real forum link
	 */
	public function fix_forum_link($permalink, $post) { // wpu_modify_pagelink($permalink, $post) {
		global $phpbbForum, $phpEx;
		
		if ( $this->is_working() && $this->get_setting('useForumPage') ) { 
			$forumPage = get_option('wpu_set_forum');
			if(!empty($forumPage) && ($forumPage == $post)) {
				// If the forum and blog are both in root, add index.php to the end
				$forumPage = ($phpbbForum->get_board_url() == get_option('siteurl')) ? $phpbbForum->get_board_url() . 'index.' . $phpEx : $phpbbForum->get_board_url();
				return $forumPage; 
			}
		}
		
		return $permalink;
	}
	
	public function admin_footer_text($inbound) {
		$inbound .= ' <span id="footer-wpunited">' . __('phpBB integration by <a href="http://www.wp-united.com/">WP-United</a>.', 'wp-united') . '</span>';
		return $inbound;
	}
	
	/**
	 * add the head marker for 
	 * template integration when WordPress CSS is first.
	*/
	public function add_head_marker() {
		global $wpUnited;

		if ($wpUnited->should_do_action('template-p-in-w') && (!PHPBB_CSS_FIRST)) {
			echo '<!--[**HEAD_MARKER**]-->';
		}
	}
	
	/**
	 * Called whenever a post is edited
	 * Allows us to differentiate between edits and new posts for cross-posting
	 */
	public function just_editing_post() {
		define('suppress_newpost_action', TRUE);
	}
	
	/**
	 * Catches posts scheduled for future publishing
	 * Since these posts won't retain the cross-posting HTTP vars, we add a post meta to future posts
	 * See functions-cross-posting.php
	 */
	public function capture_future_post($postID, $post) {
		 wpu_capture_future_post($postID, $post);
	}
	
	
	/*
	 * Called whenever a new post is published.
	 * Updates the phpBB user table with the latest author ID, to facilitate direct linkage via blog buttons
	 * Also handles cross-posting
	 */
	public function handle_new_post($post_ID, $post, $future=false) {
		global $phpbbForum;
		
		if( (!$future) && (defined("WPU_JUST_POSTED_{$postID}")) ) {
			return;
		}

		$did_xPost = false;

		if (($post->post_status == 'publish' ) || $future) { 
			if (!defined('suppress_newpost_action')) { //This should only happen ONCE, when the post is initially created.
				update_user_meta($post->post_author, 'wpu_last_post', $post_ID); 
			} 

			if ( $this->get_setting('integrateLogin') )  {
				
				
				// Update blog link column
				/**
				 * @todo this doesn't need to happen every time
				 */			
				if(!$phpbbForum->update_blog_link($post->post_author)) {
					wp_die(__('Error accessing the phpBB database when updating Blog ID', 'wp-united'));
				}
				
				if ( (($phpbbForum->user_logged_in()) || $future) && ($this->get_setting('xposting')) ) {
					$did_xPost = wpu_do_crosspost($post_ID, $post, $future);
				} 

				define('suppress_newpost_action', TRUE);
			}
			
		}
	}
	
	/**
	 * Called when a post is transitioned from future to published
	 * Since wp-cron could be invoked by any user, we treat logged in status etc differently
	 */
	public function future_to_published($post) {
		
		define("WPU_JUST_POSTED_{$postID}", TRUE);
		$this->handle_new_post($post->ID, $post, true);
	}
	
	/**
	 * Turns our page place holder into the forum-in-a-full-page
	 */
	public function check_content_for_forum($postContent) {
		
		if(!$this->is_working()) {
			return;
		}
		
		if (! defined('PHPBB_CONTENT_ONLY') ) {
			$postContent = $this->censor_content($postContent);
		} else {
			$postContent = "<!--[**INNER_CONTENT**]-->";
		}
		return $postContent;
	}
	
	/**
	 * Handles parsing of posts through the phpBB word censor.
	 * We also use this hook to suppress everything if this is a forum page.
	*/
	public function censor_content($postContent) { 
		global $phpbbForum; 
		
		if(!$this->is_working()) {
			return $postContent;
		}

		if ( !is_admin() ) {
			if ( $this->get_setting('phpbbCensor') ) { 
				return $phpbbForum->censor($postContent);
			}
		}
		return $postContent;
	}
	
	/**
	 *  Decide whether to display the cross-posting box.
	 */
	public function add_xposting_box() {
		// this func is called early so we need to do some due diligence (TODO: CHECK THIS IS STILL NECESSARY!)
		if (preg_match('/\/wp-admin\/(post.php|post-new.php|press-this.php)/', $_SERVER['REQUEST_URI'])) {
			if ( (!isset($_POST['action'])) && (($_POST['action'] != "post") || ($_POST['action'] != "editpost")) ) {
		
				//Add the cross-posting box if enabled and the user has forums they can post to
				if ( $this->get_setting('xposting') && $this->get_setting('integrateLogin') ) { 
					wpu_add_xposting_box();
				}
			}
		}
	}
	
	/**
	* Stubs for cross-posting hooks and filters in functions-cross-posting.php
	*/
	public function comment_redir_field() {
		return wpu_comment_redir_field();
	}
	public function comment_redirector($postID) {
		return wpu_comment_redirector($postID);
	}
	public function comments_open($open, $postID) {
		return wpu_comments_open($open, $postID);
	}
	public function get_comment_author_link($link) {
		return wpu_get_comment_author_link($link);
	}
	public function load_phpbb_comments($commentArray, $postID) {
		return wpu_load_phpbb_comments($commentArray, $postID);
	}
	public function comments_count($count, $postID = false) {
		return wpu_comments_count($count, $postID);
	}
	public function no_guest_comment_posting() {
		return wpu_no_guest_comment_posting();
	}
	public function edit_comment_link($link, $comment_ID) {
		return wpu_edit_comment_link($link, $comment_ID);
	}
	public function comment_link($url, $comment, $args) {
		return wpu_comment_link($url, $comment, $args);
	}
	

	
	/**
	* Retrieve the phpBB avatar of a user
	* @return phpBB avatar html tag, or the WordPress avatar if the phpBB one is empty or user integration is disabled
	* @since WP-United 0.7.0
	* 
	* TODO: let wp override phpBB avatar if it is newer!
	*/

	public function get_avatar($avatar, $id_or_email, $size = '96', $default = '', $alt = false ) { 
		global $phpbbForum;

		if (!$this->is_working() || !$this->get_setting('integrateLogin') || !$this->get_setting('avatarsync')) { 
			return $avatar;
		}

		$safe_alt = (false === $alt) ? esc_attr(__('Avatar image', 'wp-united')) : esc_attr($alt);


		if ( !is_numeric($size) )
			$size = '96';

		// Figure out if this is an ID or e-mail --sourced from WP's pluggables.php
		if ( is_numeric($id_or_email) ) {
			$id = (int) $id_or_email;
			$user = get_userdata($id);
		} elseif (is_object($id_or_email)) {
			if (!empty($id_or_email->user_id)) {
				$id = (int)$id_or_email->user_id;
				$user = get_userdata($id);
			}
		}
		

		if(!$user) {
			return $avatar;
		}

		$wpuIntID = wpu_get_integrated_phpbbuser($user->ID);
		
		if(!$wpuIntID) { 
			// the user isn't integrated, show WP avatar
			return $avatar;
			
		} else {
			
			$phpbbAvatar = $phpbbForum->get_avatar($wpuIntID, $size, $size, $safe_alt);	
			
			if(!empty($phpbbAvatar) && (stristr($phpbbAvatar, 'wpuput=1') === false)) {
				return $phpbbAvatar;
			}
			
			return $avatar;
			
		}
	}

	/**
	 * Originally function 'wpu_print_smilies' prints phpBB smilies into comment form
	 * @since WP-United 0.7.0
	 */
	public function generate_smilies() { 
		global $phpbbForum;
		if ($this->get_setting('phpbbSmilies')) {
			echo $phpbbForum->get_smilies();
		}
	}
	
	/**
	 * Function 'wpu_smilies' replaces the phpBB smilies' code with the corresponding smilies into comment text
	 * @since WP-United 0.7.0
	 */
	public function smilies($postContent) {
		global $phpbbForum;
		
		if ($this->get_setting('phpbbSmilies')  ) {
			
			return $phpbbForum->add_smilies($postContent);
			
		}
		return $postContent;
	}
	
	/**
	 * Adds any required scripts and inline JS (for lang strings)
	 */
	public function add_scripts() {
		
		if(!$this->is_enabled()) {
			return;
		}
		
		// enqueue any JS we need
		if ( $this->get_setting('phpbbSmilies') && !is_admin() ) {
			wp_enqueue_script('wp-united', $this->get_plugin_url() . 'js/smilies.js', array(), false, true);
		}
		
		// fix broken admin bar on integrated page
		if(($this->get_setting('showHdrFtr') == 'FWD') && $this->get_setting('cssMagic')) {
			wp_enqueue_script('wpu-fix-adminbar', $this->get_plugin_url() . 'js/fix-admin-bar.js', array('admin-bar'), false, true);
		}

		// Rather than outputting the script, we just signpost any language strings we will need
		// The scripts themselves are already enqueud.
		if ( $this->get_setting('phpbbSmilies') ) {
			echo "\n<script type=\"text/javascript\">//<![CDATA[\nvar wpuLang ={";
			$langStrings = array(
				'wpu_more_smilies' 	=> 		__('More smilies', 'wp-united'), 
				'wpu_less_smilies'	=>		__('Less smilies', 'wp-united'), 
				'wpu_smiley_error'	=>		__('Your smiley could not be inserted. You can add it manually by typing this code: %s', 'wp-united')
			);
			
			foreach($langStrings as $key => $lang) {
				if($key != 'wpu_more_smilies') {
					echo ',';
				}
				echo "'{$key}': '" . str_replace("\\\\'", "\\'", str_replace("'", "\\'",  $lang)) . "'";
			}
			echo "} // ]]>\n</script>";
		}
	}
	
	/**
	*  Generate a username in WP when the sanitized username is blank,
	*/
	public function fix_blank_username($userLogin) {

		if ($this->get_setting('integrateLogin')) { 
			$userLogin = wpu_fix_blank_username($userLogin);
		}
		return $userLogin;
	}
	
	/**
	* TODO: Under consideration for future rewrite: Function 'validate_username_conflict()' - Handles the conflict between validate_username
	* in WP & phpBB. This is only really a problem in integrated pages when naughty WordPress plugins pull in
	* registration.php. 
	* 
	* These functions should NOT collide in usage -- only in namespace. If user integration is turned on, we don't need
	* WP's validate_username. 
	* 
	* Furthermore, if phpbb_validate_username is defined, then we know we most likely need to use the phpBB version.
	* 
	* We unfortunately cannot control their usage -- phpbb expects 2 arguments, whereas WordPress only expects one.
	* Therefore here we just try to avoid namespace errors. If they are actually invoked while renamed, the result is undefined
	*/

	public function validate_username_conflict($wpValdUser, $username) {
		global $phpbbForum;
		if($phpbbForum->get_state() == 'phpbb') {
			if(function_exists('phpbb_validate_username')) {
				return phpbb_validate_username($username, false);
			}
		}
		return $wpValdUser;
	}
	
	
	/**
	 * Checks username and e-mail requested for a new registration.
	 * Validates against phpBB if user integration is working.
	 * @param string $username username
	 * @param string $email e-mail
	 * @param WP_Error $errors WordPress error object
	 */
	public function validate_new_user($username, $email, $errors) {

		if ($this->get_setting('integrateLogin')) {
			$result = wpu_validate_new_user($username, $email, $errors);
		}
		
		if($result !== false) {
			$errors = $result;
		}
		
		return $result;
	}
	
	/**
	 * checks a new registration 
	 * This occurs after the account has been created, so it is only for naughty plugins that
	 * leave no other way to intercept them.
	 * If it is found to be an erroneous user creation, then we remove the newly-added user.
	 * This action is removed by WP-United when adding a user, so we avoid unsetting our own additions
	 */
	public function process_new_wp_reg ($userID) { 
		global $phpbbForum;
		
		static $justCreatedUser = -1;

		/*
		 * if we've already created a user in this session, 
		 * it is likely an error from a plugin calling the user_register hook 
		 * after wp_insert_user has already called it. The Social Login plugin does this
		 * 
		 * At any rate, it is pointless to check twice
		 */
		if($justCreatedUser === $userID) {
				return;
		}

		// some registration plugins don't init WP and call the action hook directly. This is enough to get us a phpBB env
		$this->init_plugin();
			
		// neeed some user add / delete functions
		if ( ! defined('WP_ADMIN') ) {
			require_once(ABSPATH . 'wp-admin/includes/user.php');
		}


		if ($this->get_setting('integrateLogin')) {
			
			$errors = new WP_Error();
			$user = get_userdata($userID);

			$result = wpu_validate_new_user($user->user_login, $user->user_email , $errors);

			if($result !== false) { 
				// An error occurred validating the new WP user, remove the user.
				
				wp_delete_user($userID,  0);
				$message = '<h1>' . __('Error:', 'wp-united') . '</h1>';
				$message .= '<p>' . implode('</p><p>', $errors->get_error_messages()) . '</p><p>';
				$message .= __('Please go back and try again, or contact an administrator if you keep seeing this error.', 'wp-united') . '</p>';
				wp_die($message);
				
				exit();
			} else { 
				// create new integrated user in phpBB to match
				$phpbbID = wpu_create_phpbb_user($userID);
				$justCreatedUser = $userID;
				wpu_sync_profiles($user, $phpbbForum->get_userdata('', $phpbbID), 'sync');
			}
			
		}
	}
	
	/**
	 * Sync details to phpBB on a profile update
	 * 
	 */
	public function profile_update($userId, $oldUserData) {
		global $phpbbForum;
		 
		if($this->get_setting('integrateLogin')) {
			$wpData = get_userdata($userId);
			$phpbbId = wpu_get_integrated_phpbbuser($userID);
			if($phpbbId) {
				 // only sync password if it has changed, not just because it is different
				 $ignorePassword = ($wpData->data->user_pass == $oldUserdata->user_pass);
				 wpu_sync_profiles($wpData, $phpbbForum->get_userdata('', $phpbbId), 'wp-update', $ignorePassword); 
			}
		}
	}
	
	/**
	 * checks a login with username and password. If it failed, but the user they tried to log in as
	 * has an integrated phpBB user with a correct username and password, allow the login to proceed
	 * @param mixed $user WP_User|WP_Error|null a user object if the user has already successfully authenticated
	 * @param string $username attempted username
	 * @param string $password attempted password
	 */
	 
	public function authenticate($user, $username, $password) {
		global $phpbbForum;

		if (is_a($user, 'WP_User')) { 
			return $user;
		}
		
		if(!$this->is_working()) {
			return $user;
		}

		if(!$this->get_setting('integrateLogin')) {
			return;
		}
		
		// phpBB does some processing of inbound strings so password could be modified
		set_var($phpbbPass, stripslashes($password), 'string', true);



		if(!$phpbbForum->login($username, $phpbbPass)) {
			return $user; // return an error
		}

		if($integratedID = wpu_get_integration_id() ) {
			return(get_userdata($integratedID));
		}

		// If we've got here, we have a valid phpBB user that isn't integrated in WordPress

		// Should this phpBB user get an account? If not, we can just stay unintegrated
		if(!$this->get_setting('integcreatewp') || !$userLevel = wpu_get_user_level()) {
			return $user;
		}

		$signUpName = $phpbbForum->get_username();
		$newUserID = wpu_create_wp_user($signUpName, $phpbbForum->get_userdata('user_password'), $phpbbForum->get_userdata('user_email'));

		if($newUserID) { 
			if(!is_a($newUserID, 'WP_Error')) {
				return(get_userdata($newUserID));
			}
		}


		//just return whatever error was passed in
		return $user;

	}
	
	/**
	 * Clears phpbb's cache of WP header/footer.
	 * We need to do this whenever the main WP theme is changed,
	 * because when WordPress header/footer cache are called from phpBB, we have
	 * no way of knowing what the theme should be a WordPress is not invoked
	 */
	public function clear_header_cache() {
		$wpuCache = WPU_Cache::getInstance();
		$wpuCache->template_purge();
	}
	
	
	/**
	 * The default shutdown action, wp_ob_end_flush_all, causes PHP notices with zlib compression. 
	 * So we turn it off and replace it with the diff suggested at
	 * http://rustyroy.blogspot.jp/2010/12/various-stuff_20.html
	 * See also http://core.trac.wordpress.org/attachment/ticket/18525/18525.6.diff 
	 */
	public function buffer_end_flush_all() {
		$levels = ob_get_level();
		for ($i=0; $i<$levels; $i++){
			$obStatus = ob_get_status();
			if (!empty($obStatus['type']) && $obStatus['status']) {
				ob_end_flush();
			}
		}
	}
	
	
}


?>
