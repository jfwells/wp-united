<?php

/** 
* @package WP-United
* @version $Id: 0.9.2.0  2013/01/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2013 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* This is the main WP-United WordPress Plugin file.
* 
* At the top all the filters and hooks can be found, and they are dynamically loaded according to what is needed.
* All of the resultant hooks and filter destinations are within this class.
* This class extends the WP_United_Plugin_Base class, which contains the methods that make sense in both phpBB and WordPress.
* 
*/

if ( !defined('ABSPATH') && !defined('IN_PHPBB') ) exit;

class WP_United_Plugin extends WP_United_Plugin_Main_Base {

	protected
		// Actions and filters. These are loaded as needed depending on which WP-United portions are active.
		// Format: array( event | function in this class(in an array if optional arguments are needed) | loading circumstances)
		$actions = array(
			// required actions on all pages
			array('plugins_loaded', 					'init_plugin',								'all'),  // this should be 'init', but we want to play with current_user, which comes earlier
			//array('shutdown', 						array('buffer_end_flush_all', 100),			'all'),
			array('wp_head', 							'add_scripts',								'all'),
			array('wp_footer', 							'add_footer_output',						'all'),
			array('admin_footer', 						'add_footer_output',						'all'),
			array('admin_bar_menu',						array('add_to_menu_bar', 100),				'all'),
			
			// required admin ajax actions
			array('wp_ajax_wpu_filetree', 				'filetree',									'all'),
			array('wp_ajax_wpu_disable', 				'ajax_auto_disable',						'all'),
			array('wp_ajax_wpu_disableman', 			'ajax_manual_disable',						'all'),
			array('wp_ajax_wpu_settings_transmit', 		'ajax_settings_transmit',					'all'),
			
			// behaviour actions
			array('comment_form', 						'generate_smilies',							'phpbb-smilies'),
			
			// template integration actions
			array('wp_head', 							'add_head_marker',							'template-int'),
			array('switch_theme', 						'clear_header_cache',						'template-int'),
			
			// user integration actions
			array('set_current_user', 					'integrate_users',							'user-int'),
			array('wp_logout', 							'phpbb_logout',								'user-int'),
			array('registration_errors', 				array('validate_new_user', 10, 3),			'user-int'),
			array('user_register', 						array('process_new_wp_reg', 10, 1),			'user-int'),
			array('profile_update', 					array('profile_update', 10, 2),				'user-int'),
			
			// cross-posting actions
			array('admin_menu', 						'add_xposting_box',							'x-posting'),
			array('edit_post', 							'just_editing_post',						'x-posting'),
			array('wp_insert_post', 					array('capture_future_post', 10, 2),		'x-posting'),
			array('publish_post', 						array('handle_new_post', 10, 2),			'x-posting'),
			array('future_to_publish', 					array('future_to_published', 10),			'x-posting'),
			array('comment_form', 						'comment_redir_field',						'x-posting'),
			array('pre_comment_on_post', 				'comment_redirector',						'x-posting'),
			array('comments_open', 						array('comments_open', 10, 2),				'x-posting'),
			array('pre_get_comments', 					array('check_comments_query', 10, 2),		'x-posting')
		),

		$filters = array(
			// required filters
			array('plugin_row_meta', 					array('add_plugin_menu_link', 10, 2), 		'all'),
			array('page_link', 							array('fix_forum_link', 10, 2), 			'all'),
			array('admin_footer_text', 					'admin_footer_text', 						'all'),
			array('the_content', 						'check_content_for_forum', 					'all'),
			
			// behaviour filters
			array('comment_text', 						'censor_content', 							'phpbb-censor'),
			array('the_title', 							'censor_content', 							'phpbb-censor'),
			array('the_excerpt', 						'censor_content', 							'phpbb-censor'),
			array('comment_text', 						'smilies', 									'phpbb-smilies'),
			
			// user integration filters
			array('get_avatar', 						array('get_avatar', 10, 5), 				'user-int'),
			array('pre_user_login', 					'fix_blank_username', 						'user-int'),
			array('validate_username', 					array('validate_username_conflict', 10, 2),	'user-int'),
			array('authenticate', 						array('authenticate', 21, 3), 				'user-int'),
			
			// cross-posting filters
			array('get_comment_author_link',			'get_comment_author_link',					'x-posting'),
			array('comments_array', 					array('fetch_comments_query', 10, 2),		'x-posting'),
			array('the_comments', 						array('fetch_comments_query', 10, 2),		'x-posting'),
			array('comment_row_actions', 				array('integrated_comment_actions', 10, 2),	'x-posting'),
			array('get_comments_number', 				array('comments_count', 10, 2),				'x-posting'),
			array('wp_count_comments', 					array('comments_count_and_group', 10, 2),	'x-posting'),
			array('pre_option_comment_registration', 	'no_guest_comment_posting',					'x-posting'),
			
			array('get_edit_comment_link', 				'comment_edit_link',						'x-posting'),
			array('admin_comment_types_dropdown', 		'add_to_comment_dropdown',					'x-posting'),
			
			
			array('get_comment_link', 					array('comment_link', 10, 3),				'x-posting')
		);
		
		private
			$doneInit 		= false,
			$extras 		= false,
			$integComments 	= false,
			$xPoster		= false;
	
	
	/**
	* All base init is done by the parent class.
	*/
	public function __construct() 

		parent::__construct();
		
	}
	
	/**
	 * Initialises the plugin from WordPress.
	 * This is not in the constructor, as this class can be instantiated from either phpBB or WordPress.
	 * @return void
	 */
	public function wp_init() {

		// (re)load our settings
		$this->load_settings();

		load_plugin_textdomain('wp-united', false, 'wp-united/languages/');
		
		require_once($this->get_plugin_path() . 'template-tags.php');
		
		// some login integration routines may be needed even when user integration is disabled.	
		require_once($this->get_plugin_path() . 'user-integrator.php'); 
		
		if($this->get_setting('xposting')) {		
			require_once($this->get_plugin_path() . 'cross-posting.php');
			require_once($this->get_plugin_path() . 'comments.php');
			$this->xPoster = new WPU_Plugin_XPosting($this->settings);
			$this->integComments = new WPU_XPost_Query_Store();
		}


		// add new actions and filters
		$this->add_actions();
		$this->add_filters();
		unset($this->actions, $this->filters);

	}
	
	/**
	 * The main invocation logic -- if enabled, load phpBB too!
	 * Called on plugins_loaded hook, so we can get phpBB ready in advance of user integration when set_current_user is called.
	 * @return void
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
		
		if($shouldRun) {
			$this->set_last_run('connected');
		}
		
		$versionCheck = $this->check_mod_version();
		if($versionCheck['result'] != 'OK') {
			$this->disable();
			$shouldRun = false;
		}
		
		if($this->is_enabled() && $shouldRun) { 
		
			$this->load_phpbb();
			
			//Run any upgrade actions once the phpBB environment has been loaded
			$this->upgrade();
			
			$this->set_last_run('working');
		
			// Load and initialise any WP-United Extras (sub-plugins):
			require_once($this->get_plugin_path() . 'extras.php');
			$this->extras = new WP_United_Extras_Loader();
			$this->extras->init();
			
			// Load default widgets:
			require_once($this->get_plugin_path() . 'widgets.php');
			add_action('widgets_init', array($this, 'widgets_init'));
			
				
			// The end flush action stops fwd template integration from working.
			if($this->should_do_action('template-w-in-p')) {
				// must match priority etc of the built-in
				remove_action('shutdown', 'wp_ob_end_flush_all', 1);
			}
			
			
		}
		
		$this->process_frontend_actions();

		return true; 
			
	}
	
	/**
	 * 
	 * Admin AJAX actions
	 *
	*/
	public function filetree() {
		if(check_ajax_referer( 'wp-united-filetree')) {
			wpu_filetree();
		}
		die();
	}
	public function ajax_auto_disable() {
		if(check_ajax_referer( 'wp-united-disable')) {
			$this->disable_connection('server-error'); 
			die('OK');
		}
		
	}
	public function ajax_manual_disable() {
		if(check_ajax_referer( 'wp-united-disable')) {
			$this->disable_connection('manual');
			die('OK');
		}
		
	}
	public function ajax_settings_transmit() {
		if(check_ajax_referer( 'wp-united-transmit')) {
			wpu_process_settings();
			$this->transmit_settings();
			die('OK');
		}
		die();
	}	
	
	// returns a WP-United sub-plugin object, if it exists
	public function get_extra($extraName) {
		if($this->is_working() && is_object($this->extras)) {
			return $this->extras->get_extra($extraName);
		}
		return false;
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
	 * This could either be an update settings or enable rquest, or a disable request.
	 * @param bool $enable true to enable WP-United in phpBB config
	 * @return void
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

	/**
	 * Returns true if WP-United has already initialised
	 * @return bool true if already inited
	 */
	public function has_inited() {
		return $this->doneInit;
	}
	
	/**
	 * A way of storing how the last run of phpBB went -- only used during connecting and enabling phpBB
	 * States transition through disconnected -> connected -> working
	 * @param string $status disconnected|connected|working
	 */
	private function set_last_run($status) {
		if($this->get_last_run() != $status) { 
			// transitions cannot go from 'working' to 'connected' if wp-united is enabled OK.
			if( ($this->lastRun == 'working') && ($status == 'connected') && $this->is_enabled() ) {
				return;
			} 
			$this->lastRun = $status;
			update_option('wpu-last-run', $status);
		}
	}
	
	
	/**
	 * Logs the current user out of phpBB
	 * @return void
	 */
	public function phpbb_logout() {
		if($this->is_working()) {
			global $phpbbForum;
			$phpbbForum->logout();
		}
	}
	
	/**
	 * Updates the stored WP-United settings
	 * The Wp-United class decorates itself with the stored phpBB or WordPress settings class, invoked as appropriate. 
	 * WP settings take priority
	 * @param array an array of all settings keys
	 * @return void
	 */
	public function update_settings($data) {
		$this->settings->update_settings($data);
	}
	
	/**
	 * Wrapper for wpu_integrate_login. Integrates users if appropriate options are enabled
	 * @return void
	 */
	public function integrate_users() { 
		global $wpuDebug;
		
		$wpuDebug->add('Integrate users hook called.');
		
		if(!$this->has_inited()) {
			$wpuDebug->add('WARNING: A plugin has called set_current_user too early! Initing phpBB environment.');
			$this->init_plugin();
		}

		if($this->is_working() && $this->get_setting('integrateLogin') && !defined('WPU_DISABLE_LOGIN_INT')) {
			wpu_integrate_login();
		}
	}
	
	/**
	 * Adds a link to the setup/status page on the WordPress plugins menu
	 * @param array $links provided by WordPress action hook
	 * @param string $file provided by WordPress action hook
	 * @return array inbound links returned to WordPress with new link added
	 */
	public function add_plugin_menu_link($links, $file) {

		if ($file == 'wp-united/wp-united.php') {
			$links[] = '<a href="admin.php?page=wp-united-setup">' . __('Setup / Status', 'wp-united') . '</a>';
		}
		return $links;
	}
	

	/**
	 * Process inbound actions and set up the settings panels
	 * Runs only in admin
	 * @return void
	 */
	private function process_adminpanel_actions() {

		if(is_admin()) {
			
			require_once($this->get_plugin_path() . 'settings-panel.php');
			
			// the settings page has detected an error and asked to abort
			if( isset($_POST['wpudisable']) && check_ajax_referer( 'wp-united-disable') ) {
				$this->ajax_auto_disable();
			}	

			// the user wants to manually disable
			if( isset($_POST['wpudisableman']) && check_ajax_referer( 'wp-united-disable') ) {
				$this->ajax_manual_disable();
			}
						
			if($this->is_working() && is_object($this->extras)) {
				$this->extras->admin_load_actions();
			}
			
		}
	}
	
	/**
	 * Register the WP-United default widgets and any widgets hiding in wp-united extras
	 * @return void
	 */
	public function widgets_init() {
			
		// init default widgets
		wpu_widgets_init();
		
		// register any sub-plugin widget
		if($this->is_working() && is_object($this->extras)) {
			$this->extras->widgets_init();
		}
	}
	
	/**
	 * Process any inbound AJAX requests or perform any actions that should only happen outside admin
	 * @return void
	 */
	private function process_frontend_actions() {
		
		if(is_admin()) {
			return;
		}

		if($this->is_working() && is_object($this->extras)) {
			$this->extras->page_load_actions();
		}

	
	}
	
	/**
	 * Check the permalink to see if this is a link to the forum. 
	 * If it is, replace it with the real forum link
	 * @param string $permalink provided by WordPress filter hook
	 * @param WP_Post $post provided by WordPress filter hook
	 * @return string the original permalink, modified if this is a forum page
	 */
	public function fix_forum_link($permalink, $post) {
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
	
	/**
	 * Puts a link to WP-United in the WordPress admin footer.
	 */
	public function admin_footer_text($inbound) {
		$inbound .= ' <span id="footer-wpunited">' . __('phpBB integration by <a href="http://www.wp-united.com/">WP-United</a>.', 'wp-united') . '</span>';
		return $inbound;
	}
	
	/**
	 * Adds a marker in the <head> when template integration is set to phpBB-in-WordPress and 
	 * phpBB CSS is set to come first. Echoes directly to page buffer.
	 * @return void
	*/
	public function add_head_marker() {
		global $wpUnited;

		if ($wpUnited->should_do_action('template-p-in-w') && (!PHPBB_CSS_FIRST)) {
			echo '<!--[**HEAD_MARKER**]-->';
		}
	}
	
	/**
	 * Called whenever a post is edited
	 * Allows us to differentiate between edits and new posts for updating the user's personal blog column.
	 * @return void
	 */
	public function just_editing_post() {
		define('suppress_newpost_action', TRUE);
	}
	

	
	
	/*
	 * Called whenever a new post is published.
	 * Updates the phpBB user table with the latest author ID, to facilitate direct linkage via blog buttons
	 * Also handles cross-posting
	 * Used for user-blogging
	 * @param int $postID The post ID
	 * @param WP_Post $post the WordPress post object
	 * @param boolean $future true if this is a future post
	 * @return void
	 */
	public function handle_new_post($postID, $post, $future=false) {
		global $phpbbForum;
		
		if( (!$future) && (defined("WPU_JUST_POSTED_{$postID}")) ) {
			return;
		}

		$did_xPost = false;

		if (($post->post_status == 'publish' ) || $future) { 
			if (!defined('suppress_newpost_action')) { //This should only happen ONCE, when the post is initially created.
				update_user_meta($post->post_author, 'wpu_last_post', $postID); 
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
					$did_xPost = $this->xPoster->do_crosspost($postID, $post, $future);
				} 

				define('suppress_newpost_action', TRUE);
			}
			
		}
	}
	
	/**
	 * Called when a post is transitioned from future to published
	 * Since wp-cron could be invoked by any user, we treat logged in status etc differently
	 * @param WP_Post $post the WordPress post object
	 * @return void
	 */
	public function future_to_published($post) {
		
		define("WPU_JUST_POSTED_{$postID}", TRUE);
		$this->handle_new_post($post->ID, $post, true);
	}
	
	/**
	 * Either turns the page content into the forum content by adding a tag; or censors the post content
	 * @param string $postContent the post content
	 * @return string the modified post content
	 */
	public function check_content_for_forum($postContent) {
		
		if(!$this->is_working()) {
			return $postContent;
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
	 * Really just a wrapper around WPU_Phpbb->censor
	 * @param string $postContent the content to be displayed
	 * @return string the potentially modified content;
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
	
	/*
	********************************************
	WARNING: MESSY UNDER-CONSTRUCTION AREA BELOW
	*********************************************
	*/
	
	
	
	/**
	 *  Adds a cross-posting box to the posting page if required.
	 * @return void
	 */
	public function add_xposting_box() {
		// this func is called early so we need to do some due diligence (TODO: CHECK THIS IS STILL NECESSARY!)
		if (preg_match('/\/wp-admin\/(post.php|post-new.php|press-this.php)/', $_SERVER['REQUEST_URI'])) {
			if ( (!isset($_POST['action'])) && (($_POST['action'] != "post") || ($_POST['action'] != "editpost")) ) {
		
				//Add the cross-posting box if enabled and the user has forums they can post to
				if ( $this->get_setting('xposting') && $this->get_setting('integrateLogin') ) { 
					$this->xPoster->add_xposting_box();
				}
			}
		}
	}
	
	/**
	* The following nine functions are stubs for cross-posting. The actual functions are in cross-posting.php.
	* They are wrapped here to take advantage of the dynamic WP-United hook/filter loader.
	* @param various
	* @return various
	*/
	public function comment_redir_field() {
		return $this->xPoster->comment_redir_field();
	}
	public function comment_redirector($postID) {
		return $this->xPoster->post_comment($postID);
	}
	public function comments_open($open, $postID) {
		return $this->xPoster->are_comments_open($open, $postID);
	}
	public function get_comment_author_link($link) {
		return wpu_get_comment_author_link($link);
	}

	public function integrated_comment_actions($actions, $comment) {
		
		if (!$this->is_working() || !$this->get_setting('xpostautolink') || 
			(!is_object($comment)) || empty($comment->comment_ID)) {
			return $actions;
		}

		// returns false if no permission, or 0 if doesn't exist
		$link = $this->integComments->get_comment_action('view', $comment->comment_ID);
		
		if(!empty($link)) {
			$actions = array(
				'view'	=> '<a href="' . $link . '" class="vim-r hide-if-no-js">' . __('View in forum', 'wp-united') . '</a>'
			);

			$editLink = $this->integComments->get_comment_action('edit', $comment->comment_ID);
			$delLink = $this->integComments->get_comment_action('delete', $comment->comment_ID);
			
			if(!empty($editLink)) {
				$actions['edit'] = '<a href="' . $editLink . '" class="vim-r hide-if-no-js">' . __('Edit forum post', 'wp-united') . '</a>';
			}
			
			if(!$comment->comment_approved) {
				$apprLink = $this->integComments->get_comment_action('approve', $comment->comment_ID);
				if(!empty($apprLink)) {
					$actions['approve']	= '<a href="' . $apprLink . '" class="vim-r hide-if-no-js">' . __('Approve', 'wp-united') . '</a>';
				}
			}
			
			
			if(!empty($delLink)) {
				$actions['delete'] = '<a href="' . $delLink . '" class="vim-r hide-if-no-js">' . __('Delete forum post', 'wp-united') . '</a>';
			}
			
		}
	
		return $actions;
	
	}
	
	public function comments_count($count, $postID = false) {
		return $this->xPoster->comment_count($count, $postID);
	}
	public function no_guest_comment_posting() {
		return $this->xPoster->no_guest_comment_posting();
	}
	
	
	public function comment_edit_link($link) {
	
		// the comment ID isn't provided, so grep it
		$id = 0;
		$idParts = explode('&amp;c=', $link);
		if(isset($idParts[1])) {
			$id = (int)$idParts[1];
		}
		if(!$id) {
			return $link;
		}
		
		// returns 0 if no such comment, or false if no permission
		$pLink = $this->integComments->get_comment_action('edit', $id);
		if(!empty($pLink)) {
			return $pLink;
		}
		
		return $link;
	}
	
	
	public function check_permission($allUserCaps, $requiredCaps, $args) {
	
		if (!$this->is_working() || !$this->get_setting('xpostautolink')) {
			return $allUserCaps;
		}
		
		// there must be at least three arguments
		if(!is_array($args) || (sizeof($args) < 3)) {
			return $allUserCaps;
		}
		

		
		// The first argument is the capability requested
		$perm = $args[0];
		if(!in_array($perm, array('view_comment', 'edit_comment', 'delete_comment', 'approve_comment'))) {
			return $allUserCaps;
		}
		
		// The second argument is the user ID
		$userID = (int)$args[1];
		$c = wp_get_current_user();
		
		if(empty($c) && ($userID > 0)) {
			return $allUserCaps;
		} else if($c->ID != $userID) {
			return $allUserCaps;
		}
		

		// The third argument is the comment ID
		if(empty($args[2])) {
			return $allUserCaps;
		}
		$id = $args[2];
		


		$action = '';
		switch($perm) {
			case 'view_comment':
				$action = 'view';
			break;
			case 'edit_comment':
				$action = 'edit';
			break;
			case 'delete_comment':
				$action = 'delete';
			break;
			case 'approve_comment':
				$action = 'approve';
			break;
			default:
				return $alluserCaps;
			break;
		}
			
		$canDo = $this->integComments->get_comment_action($action, 'comment' . $id);
		
		if($canDo === false) {
			// the comment is cross-posted but the user has no permission
			$allUserCaps[$requiredCaps[0]] = false;
		} elseif($canDo === 0) {
			// the comment is not cross-posted
			return $allUserCaps;
		} elseif(empty($canDo)) {
			// the link is empty -- an error or not implemented. Return false so the link doesn't display
			$allUserCaps[$requiredCaps[0]] = false;
		} else {
			// the comment is cross-posted and the user has permission
			$allUserCaps = array();
			foreach($requiredCaps as $req) {
				$allUserCaps[$req] = true;
			}
		}
		
		return $allUserCaps;
	}	
	
	public function add_to_comment_dropdown($dropdown) {
		
		if ($this->is_working() && $this->get_setting('xpostautolink')) {
	
			$dropdown['wpuxpostonly']	=	__('Show only cross-posted', 'wp-united');
			$dropdown['wpunoxpost']		=	__('Show only not cross-posted', 'wp-united');
		}
		return $dropdown;
		
	}
	
	public function comment_link($url, $comment, $args) {
		
		if (!$this->is_working() || !$this->get_setting('xpostautolink')) {
			return $url;
		}
			
		$wpuLink = $this->integComments->get_comment_action('view', $comment->commentID);
		
		if (!empty($wpuLink)) {
			return $wpuLink;
		}

		return $url;
	}
	

	public function fetch_comments_query($comments, $query) {

		if (!$this->is_working() || !$this->get_setting('xpostautolink')) {
			return $comments;
		}
	
		$result = $this->integComments->get($query, $comments);
	
		if($result === false) {
			return $comments;
		}

		return $result;
		
	}
	
	// modify query offsets
	public function check_comments_query($query) {
		if (!$this->is_working() || !$this->get_setting('xpostautolink')) {
			return;
		}
		
		$this->integComments->get($query, false, false, true);
		
	}
	
	public function comments_count_and_group($comments, $postID) {
		
		if (!$this->is_working() || !$this->get_setting('xpostautolink')) {
			return $comments;
		}
	
		$result = $this->integComments->get($postID, $comments, true);
		
		if($result === false) {
			return $comments;
		}

		return $result;
		
	}
	
	/**
	 * Catches posts scheduled for future publishing
	 * Since these posts won't retain the cross-posting HTTP vars, we add a post meta to future posts
	 * then we can process them as if they were just posted when the time arises.
	 * Wrapper for wpu_capture_future_post - see functions-cross-posting.php.
	 * @param int $postID provided by WordPress action hook
	 * @param WP_Post $post provided by WordPress action hook
	 * @return void
	 */
	public function capture_future_post($postID, $post) {
		 $this->xPoster->capture_future_post($postID, $post);
	}
	
	
	
	/**
	*************************************
	*************************************
	*************************************
	*/
	

	/**
	* Retrieve the phpBB avatar of a user
	* @param string $avatar the inbound avatar
	* @param string $id_or_email either a WordPress user ID or an e-mail
	* @param string $size a string representation of an integer denoting avatar size
	* @param string $default the default avatar
	* @param string $alt the image alt text
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

		$user = false;

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
	 * Originally function 'wpu_print_smilies'; prints phpBB smilies into comment form.
	 * Echoes directly to the page buffer.
	 * @return void
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
	 * @param string $postContent the content to modify
	 * @return string the content, with smilies rendered
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
	 * @TODO: standardize
	 * Echoes directly to the page buffer
	 * @return void
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
	* Generates a username in WP when the sanitized username is blank; as WP is less forgiving of usernames than phpBB
	* Wrapper for wpu_fix_blank_username
	* @param string $userLogin the username that would be blank
	* @return string a generated username, in the form 'wpuXXXXXX';
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
	* Therefore here we just try to avoid namespace errors. 
	* If they are actually invoked while renamed, the result is undefined and things could spontaneously ignite.
	*
	* @param string $wpValidUser the validated WP username
	* @param string $username the original WP username
	* @return string hopefully something nice
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
	 * @return mixed true or WP_Error
	 */
	public function validate_new_user($errors, $username, $email) {

		if (!$this->get_setting('integrateLogin')) {
			return;
		}
		
		$result = wpu_validate_new_user($username, $email, $errors);
				
		if($result !== false) {
			return $result; // return our errors obj
		}
		
		return $errors; // return their errors obj
	}
	
	/**
	 * checks a new registration 
	 * This occurs after the account has been created, so it is only for naughty plugins that
	 * leave no other way to intercept them.
	 * If it is found to be an erroneous user creation, then we remove the newly-added user.
	 * This action is removed by WP-United when adding a user, so we avoid unsetting our own additions
	 * @param int $userID the user ID
	 * @ return mixed void or hellfire
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
	 * @param int $userId WordPress user ID
	 * @param array $oldUserData unused -- the data before update
	 * @return void
	 */
	public function profile_update($userID, $oldUserData) {
		global $phpbbForum;
		 
		if($this->get_setting('integrateLogin')) {
			$wpData = get_userdata($userID);
			$phpbbID = wpu_get_integrated_phpbbuser($userID);
			if($phpbbID) {
				 // only sync password if it has changed, not just because it is different
				 $ignorePassword = ($wpData->data->user_pass == $oldUserdata->user_pass);
				 wpu_sync_profiles($wpData, $phpbbForum->get_userdata('', $phpbbID), 'wp-update', $ignorePassword); 
			}
		}
	}
	
	/**
	 * checks a login with username and password. If it failed, but the user they tried to log in as
	 * has an integrated phpBB user with a correct username and password, allow the login to proceed
	 * @param mixed $user WP_User|WP_Error|null a user object if the user has already successfully authenticated
	 * @param string $username attempted username
	 * @param string $password attempted password
	 * @return an authenticated WP_User object, or WP_Error or void on error
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
	 * Adds sexy things to the WordPress admin bar (they like to call it the menu bar in docs but not in code)
	 * @param object $adminBar the admin bar before being sexy
	 * @return void (giant void)
	 */
	public function add_to_menu_bar($adminBar) {
		global $wpUnited, $phpbbForum, $phpEx;
 
 
		if (current_user_can('manage_options'))  {
			$adminBar->add_menu(array(
				'id'    => 'wpu-extl',
				'title' => 'WP-United',
				'href'  => 'http://www.wp-united.com',
				'parent' => 'wp-logo-external',
				'meta'  => array(
					'title' => __('Visit wp-united.com', 'wp-united')
				),
			));
			if(!is_admin()) {
				$adminBar->add_menu(array(
					'id'    => 'wpu-main',
					'title' => __('WP-United', 'wp-united'),
					'href'  => get_admin_url() . 'admin.php?page=wp-united-setup',
					'parent' => 'site-name',
					'meta'  => array(
						'title' => __('WP-United', 'wp-united')
					),
				));	
			}
		}
		
		
		if(!$wpUnited->is_working()) {
			return;
		}
		
		$adminBar->add_menu(array(
			'id'    => 'wpu-forum-link',
			'title' => __('Visit Forum', 'wp-united'),
			'href'  => $phpbbForum->get_board_url() . 'index.' . $phpEx,
			'parent' => 'site-name',
			'meta'  => array(
				'title' => __('Visit Forum', 'wp-united')
			),
		));	
		
		
		if(current_user_can('manage_options'))  {
			$acpLink = $phpbbForum->get_acp_url();
			if($acpLink) {
				$adminBar->add_menu(array(
					'id'    => 'wpu-acp',
					'title' => __('Visit phpBB ACP', 'wp-united'),
					'href'  => $acpLink,
					'parent' => 'site-name',
					'meta'  => array(
						'title' => __('Visit phpBB ACP', 'wp-united')
					),
				));	
				
			}
		}
		
		/*
		 * Search redirector
		 * Since we use JavaScript to direct search, 
		 * we start with it hidden and show it through JS too
		 * 
		 * Bit of ugly inline JS for now, but it works. TODO: decrapify
		 */
		$adminBar->add_menu(array(
			'id'    => 'wpu-search-site',
			'title' => '<div id="wpu-srch-s" style="display: none;">&nbsp;&nbsp;<input type="radio" checked onchange="wpu_redir_srch()" id="wpu-search-site" name="wpu-search" value="site" /><label for="wpu-search-site">&nbsp;'. __('Search Site') . '</label></div>',
			'parent' => 'search'
		));	
		$adminBar->add_menu(array(
			'id'    => 'wpu-search-forum',
			'title' => '<div id="wpu-srch-f" style="display: none;">&nbsp;&nbsp;<input type="radio" id="wpu-search-forum" onchange="wpu_redir_srch()" name="wpu-search" value="forum" /><label for="wpu-search-forum">&nbsp;' . __('Search Forum') . '</label></div>
			<script type="text/javascript">//<![CDATA
				var c,d=document,b="block";
				d.getElementById("wpu-srch-f").style.display=b;
				d.getElementById("wpu-srch-s").style.display=b;				
				function wpu_redir_srch() {
					d.getElementById("adminbar-search").name=(c=d.getElementById("wpu-search-site").checked)?"s":"keywords";
					d.getElementById("adminbarsearch").action=c?"' . $wpUnited->get_wp_home_url() . '":"' . $phpbbForum->get_board_url() . "search.$phpEx" . '";
				}
			// ]]>
			</script>
			
			',
			'parent' => 'search'
		));		
	}
	
	/**
	 * Clears phpbb's cache of WP header/footer.
	 * We need to do this whenever the main WP theme is changed,
	 * because when WordPress header/footer cache are called from phpBB, we have
	 * no way of knowing what the theme should be a WordPress is not invoked
	 * @return void
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
	 * @TODO: Revisit GZIP and try to match settings across WP & phpBB, including when themes turn it on/off ( :-( )
	 * @return void
	 */
	public function buffer_end_flush_all() {
		//if($this->get_setting('showHdrFtr') == 'FWD') {
			$levels = ob_get_level();
			for ($i=0; $i<$levels; $i++){
				$obStatus = ob_get_status();
				if (!empty($obStatus['type']) && $obStatus['status']) {
					ob_end_flush();
				}
			}
		//}
	}
	
		public function add_footer_output() {
		global $wpuDebug;
	
		if(
			$this->is_enabled() && 
			!$this->should_do_action('template-p-in-w') && 
			!$this->should_do_action('template-w-in-p')
		) {
	
			// Add login debugging if requested
			if (defined('WPU_DEBUG') && WPU_DEBUG) {
				$wpuDebug->display('login');
			}

			// Add stats if requested
			if(defined('WPU_SHOW_STATS') && WPU_SHOW_STATS) {
				$wpuDebug->display_stats();
			}
		}
		
	}
	
}

// That's all. Easy, right?
