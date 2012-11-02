<?php

/*
Plugin Name: WP-United
Plugin URI: http://www.wp-united.com
Description: WP-United connects to your phpBB forum and integrates user sign-on, behaviour and theming. Once your forum is up and running, you should not disable this plugin.
Author: John Wells
Author URI: http://www.wp-united.com
Version: v0.9.0 RC3 
Last Updated: 23 October 2012
* 
*/
 
/** 
*
* @package WP-United Connection Plugin
* @version $Id: wp-united.php,v0.8.0 2010/01/29 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* @author John Wells
*/

// this file will also be called in wp admin panel, when phpBB is not loaded. So we don't check IN_PHPBB.
// ABSPATH should *always* be set though!
if ( !defined('ABSPATH') ) {
	exit;
}


add_action('comment_form', 'wpu_comment_redir_field');
add_action('wp_head', 'wpu_inline_js');
add_action('admin_menu', 'wpu_add_meta_box'); 
add_action('register_post', 'wpu_check_new_user', 10, 3);
add_action('pre_comment_on_post', 'wpu_comment_redirector');
add_action('comments_open', 'wpu_comments_open', 10, 2);


add_filter('pre_user_login', 'wpu_fix_blank_username');
add_filter('validate_username', 'wpu_validate_username_conflict');
add_filter('get_comment_author_link', 'wpu_get_comment_author_link');
add_filter('comment_text', 'wpu_smilies');
add_filter('get_avatar', 'wpu_get_phpbb_avatar', 10, 5);
add_filter('comments_array', 'wpu_load_phpbb_comments', 10, 2);
add_filter('get_comments_number', 'wpu_comments_count', 10, 2);
add_filter('pre_option_comment_registration', 'wpu_no_guest_comment_posting');
add_filter('edit_comment_link', 'wpu_edit_comment_link', 10, 2);
add_filter('get_comment_link', 'wpu_comment_link', 10, 3);



if( !class_exists( 'WP_United_Plugin' ) ):


require_once(plugin_dir_path(__FILE__) . 'basics.php');


class WP_United_Plugin extends WP_United_Plugin_Base {

	protected
		$actions = array(
			'init'					=>		'init_plugin',
			'wp_logout'				=>		'phpbb_logout',
			'comment_form'			=> 		'generate_smilies',
			'wp_head'				=>		'add_head_marker',
			'edit_post'				=>		'just_editing_post',
			'wp_insert_post'		=>		array('capture_future_post', 10, 2),
			'publish_post'			=>		array('handle_new_post', 10, 2),
			'future_to_publish'		=>		array('future_to_published', 10),
			'switch_theme'			=>		'clear_header_cache',
			'shutdown'				=>		array('buffer_end_flush_all', 1)
		),

		$filters = array(
			'plugin_row_meta'		=>		array('wpu_pluginrow_link', 10, 2),
			'page_link'				=>		array('fix_forum_link', 10, 2),
			'admin_footer_text'		=>		'admin_footer_text',
			'the_content'			=>		'check_content_for_forum',
			'comment_text'			=>		'censor_content',
			'the_title'				=>		'censor_content',
			'the_excerpt'			=>		'censor_content',
		),
			
		$wordpressLoaded = true;

	/**
	* Initialise the WP-United class
	*/
	public function __construct(WP_United_Basics $existingObject) {
		
		//Rather than using the decorator pattern, we can just import the style keys.
		$this->styleKeys = $existingObject->get_style_key();
		
		// we want to override some actions. These must match the priority of the built-ins 
		remove_action('shutdown', 'buffer_end_flush_all', 1);
		
		// add new actions and filters
		$this->add_actions();
		$this->add_filters();
		unset($this->actions, $this->filters);
		
		
	}


	/**
	 * Adds main menu
	 */
	public function add_plugin_menu_link($links, $file) {

		if ($file == 'wp-united/wp-united.php') {
			$links[] = '<a href="admin.php?page=wp-united-setup">' . __('Setup / Status') . '</a>';
		}
		return $links;
	}
	
	/**
	 * The main invocation logic -- if enabled, load phpBB too!
	 */
	public function init_plugin() { 
		global $phpbbForum;
		
		
		if($this->pluginLoaded) {
				return false;
		}
		$this->pluginLoaded = true;
		
		$this->wpPath = ABSPATH;
		$this->pluginPath = plugin_dir_path(__FILE__);
		$this->pluginUrl = plugins_url('wp-united') . '/';
		$this->wpHomeUrl = home_url('/');
		$this->wpBaseUrl = site_url('/');
		
	

		require_once($this->pluginPath . 'options.php');
		require_once($this->pluginPath . 'functions-general.php');
		require_once($this->pluginPath . 'template-tags.php');
		require_once($this->pluginPath . 'login-integrator.php'); 
		require_once($this->pluginPath . 'functions-cross-posting.php');
	
		require_once($this->pluginPath .  'phpbb.php');
		$phpbbForum = new WPU_Phpbb();	
		
		// this has to go prior to phpBB load so that connection can be disabled in the event of an error on activation.
		$this->process_adminpanel_actions();


		if(!$this->is_enabled()) { 
			$this->set_last_run('disconnected');
			return;
		} else {
			$this->get_last_run();
		}		
		
		// disable login integration if we couldn't override pluggables
		if(defined('WPU_CANNOT_OVERRIDE')) {
			$this->settings['integrateLogin'] = 0;
		}		

		if(!$this->get_setting('phpbb_path') || !file_exists($this->get_setting('phpbb_path'))) {
			$this->set_last_run('disconnected');
			return;
		}
		
		if($this->get_last_run() == 'connected') {
			return;
		}
		
		$this->set_last_run('connected');

		if($this->is_enabled()) {
		
			$this->load_phpbb();
			
			$this->set_last_run('working');
		
			require_once($this->pluginPath . 'widgets.php');
			require_once($this->pluginPath . 'widgets2.php');
			
			//add_action('widgets_init', 'wpu_widgets_init_old');
			add_action('widgets_init', 'wpu_widgets_init');
		
			if(!is_admin()) {
				if ( (stripos($_SERVER['REQUEST_URI'], 'wp-login') !== false) && $this->get_setting('integrateLogin') ) {
					global $user_ID;
					get_currentuserinfo();
					if( ($phpbbForum->user_logged_in()) && ($id = get_wpu_user_id($user_ID)) ) {
						wp_redirect(admin_url());
					} else if ( (defined('WPU_MUST_LOGIN')) && WPU_MUST_LOGIN ) {
						$login_link = append_sid('ucp.'.$phpEx.'?mode=login&redirect=' . urlencode(esc_attr(admin_url())), false, false, $GLOBALS['user']->session_id);		
						wp_redirect($phpbbForum->url . $login_link);
					}
				} 
			}
		}

		
		// This variable is used in phpBB template integrator || TODO: KILL!!!
		global $siteUrl;
		$siteUrl = get_option('siteurl');
		
		// enqueue any JS we need
		if ( $this->get_setting('phpbbSmilies') && !is_admin() ) {
			wp_enqueue_script('wp-united', $this->pluginUrl . 'js/wpu-min.js', array(), false, true);
		}
		
		// fix broken admin bar on integrated page
		if(($this->get_setting('showHdrFtr') == 'FWD') && $this->get_setting('cssMagic')) {
			wp_enqueue_script('wpu-fix-adminbar', $this->pluginUrl . 'js/wpu-fix-bar.js', array('admin-bar'), false, true);
		}
		
		if($this->get_setting('integrateLogin') && !defined('WPU_DISABLE_LOGIN_INT') ) {
				wpu_integrate_login();
		}
		
		return true; 
			
	}
	
	public function load_phpbb() {
		global $phpbb_root_path, $phpEx, $phpbbForum;

		if ( !defined('IN_PHPBB') ) {
			$phpbb_root_path = $this->get_setting('phpbb_path');
			$phpEx = substr(strrchr(__FILE__, '.'), 1);
		}
		
		if ( !defined('IN_PHPBB') ) {
			if(is_admin()) {
				define('WPU_PHPBB_IS_EMBEDDED', TRUE);
			} else {
				define('WPU_BLOG_PAGE', 1);
			}
			$phpbbForum->load($phpbb_root_path);
			
			$this->set_last_run('working');
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
			$this->load_phpbb();
		}
	
		// settings must be loaded before we transmit ourselves
		// If this is an enable or disable request, they might not be as they are lazily loaded.
		$this->load_settings();
		
		// store data before transmitting
		if($enable) {
			$this->enable();
		} else {
			$this->disable();
		}
		
		$dataToStore = array($this->pluginPath, serialize($this));
		$dataToStore = base64_encode(gzcompress(serialize($dataToStore)));
		
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


	public function is_loaded() {
		return $this->pluginLoaded;
	}
	
	private function set_last_run($status) {
		if($this->lastRun != $status) {
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
	
	public function is_phpbb_loaded() {
		if($this->is_enabled() && ($this->get_last_run() == 'working')) {
			return true;
		}
		return false;
	}
	
	public function phpbb_logout() {
		if($this->is_phpbb_loaded()) {
			global $phpbbForum;
			$phpbbForum->logout();
		}
	}
	
	public function update_settings($data) {

		// settings must be loaded before we transmit ourselves
		// they might not be as they are lazily loaded.
		$this->load_settings();
		
		
		$data = array_merge($this->settings, (array)$data); 
		update_option('wpu-settings', $data);
		$this->settings = $data;
		
		
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
	 * Process inbound actions and set up the settings panels
	 */
	private function process_adminpanel_actions() {

		if(is_admin()) {
			
			require_once($this->pluginPath . 'settings-panel.php');
			
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
	 * Check the permalink to see if this is a link to the forum. 
	 * If it is, replace it with the real forum link
	 */
	public function fix_forum_link($permalink, $post) { // wpu_modify_pagelink($permalink, $post) {
		global $phpbbForum, $phpEx;
		
		if ( $this->get_setting('useForumPage') ) { 
			$forumPage = get_option('wpu_set_forum');
			if(!empty($forumPage) && ($forumPage == $post)) {
				// If the forum and blog are both in root, add index.php to the end
				$forumPage = ($phpbbForum->url == get_option('siteurl')) ? $phpbbForum->url . 'index.' . $phpEx : $phpbbForum->url;
				return $forumPage; 
			}
		}
		
		return $permalink;
	}
	
	public function admin_footer_text($inbound) {
		$inbound .= ' <span id="footer-wpunited">' . __('phpBB integration by <a href="http://www.wp-united.com/">WP-United</a>.') . '</span>';
		return $inbound;
	}
	
	/**
	 * Sets wpu_done_head to true, so we can alter things like the home link without worrying.
	 * (before the <HEAD>, we don't want to modify any links)
	 * We also add the head marker for 
	 * template integration when WordPress CSS is first.
	*/
	public function add_head_marker() {
		global $wpuIntegrationMode;

		if (($wpuIntegrationMode == 'template-p-in-w') && (!PHPBB_CSS_FIRST)) {
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
				if(!$phpbbForum->update_blog_link($post->post_author())) {
					wp_die(__('Error accessing the phpBB database when updating Blog ID'));
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
		if (! defined('PHPBB_CONTENT_ONLY') ) {
			$postContent = wpu_censor($postContent);
		} else {
			global $innerContent, $wpuOutputPreStr, $wpuOutputPostStr;
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
		
		if(!$this->is_phpbb_loaded()) {
			return $postContent;
		}
		//if (! defined('PHPBB_CONTENT_ONLY') ) {  Commented out as we DO want this to to work on a full reverse page.
			if ( !is_admin() ) {
				if ( $this->get_setting('phpbbCensor') ) { 
					return $this->censor($postContent);
				}
			}
			return $postContent;
		//}
	}
	
	/**
	 * Clears phpbb's cache of WP header/footer.
	 * We need to do this whenever the main WP theme is changed,
	 * because when WordPress header/footer cache are called from phpBB, we have
	 * no way of knowing what the theme should be a WordPress is not invoked
	 */
	public function clear_header_cache() {
		require_once($this->pluginPath . 'cache.php');
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


/**
* If  WordPress has been invoked from phpBB, we keep the WP-United object around.
* We upgrade it with a pseudo-decorator by feeding it the existing WPU object (or a blank one)
*/
global $wpUnited;
if(!isset($wpUnited)) {
	$wpUnited = new WP_United_Basics;
}
$wpUnited = new WP_United_Plugin($wpUnited);


endif;






/**
 * Add box to the write/(edit) post page.
 */
function wpu_add_postboxes() {
	global $can_xpost_forumlist, $already_xposted, $phpbbForum, $wpUnited;
?>
	<div id="wpuxpostdiv" class="inside">
	<?php if ($already_xposted) echo '<strong><small>' . sprintf($phpbbForum->lang['wpu_already_xposted'], $already_xposted['topic_id']) . "</small></strong><br /> <input type=\"hidden\" name=\"wpu_already_xposted_post\" value=\"{$already_xposted['post_id']}\" /><input type=\"hidden\" name=\"wpu_already_xposted_forum\" value=\"{$already_xposted['forum_id']}\" />"; ?>
	<label for="wpu_chkxpost" class="selectit">
		<input type="checkbox" <?php if ($already_xposted) echo 'disabled="disabled" checked="checked"'; ?>name="chk_wpuxpost" id="wpu_chkxpost" value="1001" />
		<?php echo $phpbbForum->lang['wpu_xpost_box_title']; ?><br />
	</label><br />
	<label for="wpu_selxpost">Select Forum:</label><br />
		<select name="sel_wpuxpost" id="wpu_selxpost" <?php if ($already_xposted) echo 'disabled="disabled"'; ?>> 
		<?php
			if ($already_xposted) {
				echo "<option value=\"{$already_xposted['forum_id']}\">{$already_xposted['forum_name']}</option>";
			} else {
				foreach ( $can_xpost_forumlist['forum_id'] as $key => $value ) {
					echo "<option value=\"{$value}\" ";
					echo ($key == 0) ? 'selected="selected"' : '';
					echo ">{$can_xpost_forumlist['forum_name'][$key]}</option>";
				}
			} ?>
			</select>
	
			 <?php if($wpUnited->get_setting('xposttype') == 'ASKME') {
				$excerptState = 'checked="checked"';
				$fullState = '';
				if (isset($_GET['post'])) {
					$postID = (int)$_GET['post'];
					if(get_post_meta($postID, '_wpu_posttype', true) != 'excerpt') {
						$fullState = 'checked="checked"';
						$excerptState = '';
					}
				}
				echo '<br /><input type="radio" name="rad_xpost_type" value="excerpt" ' . $excerptState . ' />' . $phpbbForum->lang['wpu_excerpt'] . '<br />';
				echo '<input type="radio" name="rad_xpost_type" value="fullpost" ' . $fullState . ' />' . $phpbbForum->lang['wpu_fullpost'];
			} ?>

	</div>
<?php
}
/**
 * Adds a "Force cross-posting" info box
 */
function wpu_add_forcebox($forumName) {
	global $forceXPosting, $phpbbForum, $wpUnited;

	$showText =  (wpu_get_xposted_details()) ? $phpbbForum->lang['wpu_forcexpost_update'] : $phpbbForum->lang['wpu_forcexpost_details'];

?>
	<div id="wpuxpostdiv" class="inside">
	<p> <?php echo sprintf($showText, $forceXPosting); ?></p>
	<?php if($wpUnited->get_setting('xposttype') == 'ASKME') {
				$excerptState = 'checked="checked"';
				$fullState = '';
				if (isset($_GET['post'])) {
					$postID = (int)$_GET['post'];
					if(get_post_meta($postID, '_wpu_posttype', true) != 'excerpt') {
						$fullState = 'checked="checked"';
						$excerptState = '';
					}
				}
				echo '<br /><input type="radio" name="rad_xpost_type" value="excerpt" ' . $excerptState . ' />' . $phpbbForum->lang['wpu_excerpt'] . '<br />';
				echo '<input type="radio" name="rad_xpost_type" value="fullpost" ' . $fullState . ' />' . $phpbbForum->lang['wpu_fullpost'];
			} ?>
	</div>
<?php
}

/**
 *  Here we decide whether to display the cross-posting box, and store the permissions list in global vars for future use.
 * For WP >= 2.5, we set the approproate callback function. For older WP, we can go directly to the func now.
 */
function wpu_add_meta_box() {
	global $phpbbForum, $wpUnited, $can_xpost_forumlist, $already_xposted;
	// this func is called early
	if (preg_match('/\/wp-admin\/(post.php|post-new.php|press-this.php)/', $_SERVER['REQUEST_URI'])) {
		if ( (!isset($_POST['action'])) && (($_POST['action'] != "post") || ($_POST['action'] != "editpost")) ) {
	
			//Add the cross-posting box if enabled and the user has forums they can post to
			if ( $wpUnited->get_setting('xposting') && $wpUnited->get_setting('integrateLogin') ) { 
				
				if($wpUnited->get_setting('xpostforce') > -1) {
					// Add forced xposting info box
					global $forceXPosting;
					$forceXPosting = wpu_get_forced_forum_name($wpUnited->get_setting('xpostforce'));
					if($forceXPosting !== false) {
						add_meta_box('postWPUstatusdiv', __($phpbbForum->lang['wpu_forcexpost_box_title'], 'wpu-cross-post'), 'wpu_add_forcebox', 'post', 'side');
					}
				} else {	
					// Add xposting choice box
					if ( !($already_xposted = wpu_get_xposted_details()) ) { 
						$can_xpost_forumlist = wpu_forum_xpost_list(); 
					}
			
					if ( (sizeof($can_xpost_forumlist)) || $already_xposted ) {
						add_meta_box('postWPUstatusdiv', __($phpbbForum->lang['wpu_xpost_box_title'], 'wpu-cross-post'), 'wpu_add_postboxes', 'post', 'side');
					}
				}
			}
		}
	}
}



	



/**
* Function 'get_avatar()' - Retrieve the phpBB avatar of a user
* @since WP-United 0.7.0
*/

function wpu_get_phpbb_avatar($avatar, $id_or_email, $size = '96', $default = '', $alt = false ) { 
   global $wpUnited, $phpbbForum;
   if (!$wpUnited->get_setting('integrateLogin')) { 
      return $avatar;
   }

   $safe_alt = esc_attr( $phpbbForum->lang['USER_AVATAR'] );

   if ( !is_numeric($size) )
      $size = '96';

   if ( !is_numeric($size) )
      $size = '96';
   // Figure out if this is an ID or e-mail --sourced from WP's pluggables.php
   $email = '';
   if ( is_numeric($id_or_email) ) {
      $id = (int) $id_or_email;
      $user = get_userdata($id);
   } elseif ( is_object($id_or_email) ) {
      if ( !empty($id_or_email->user_id) ) {
		  // $id_or_email is probably a comment object
         $user = get_userdata($id_or_email->user_id);
      } 
   }

   if($user) {
      // use default WordPress or WP-United image
      if(!$image = avatar_create_image($user)) { 
         if(stripos($avatar, 'blank.gif') !== false) {
            $image = $wpUnited->get_setting('wpPluginUrl') . 'images/wpu_no_avatar.gif';
         } else {
            return $avatar;
         }
      } 
   } else {
      if(stripos($avatar, 'blank.gif') !== false) {
          $image = $wpUnited->get_setting('wpPluginUrl') . 'images/wpu_unregistered.gif';
       } else {
         return $avatar;
      }
   }
   return "<img alt='{$safe_alt}' src='{$image}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
}


/**
 * Function 'wpu_smilies' replaces the phpBB smilies' code with the corresponding smilies into comment text
 * @since WP-United 0.7.0
 */
function wpu_smilies($postContent, $max_smilies = 0) {
	global $phpbbForum;
	
	if ($wpUnited->get_setting('phpbbSmilies')  ) { 
		static $match;
		static $replace;
		global $db;
	

		// See if the static arrays have already been filled on an earlier invocation
		if (!is_array($match)) {
		
			$fStateChanged = $phpbbForum->foreground();
			
			$result = $db->sql_query('SELECT code, emotion, smiley_url FROM '.SMILIES_TABLE.' ORDER BY smiley_order', 3600);

			while ($row = $db->sql_fetchrow($result)) {
				if (empty($row['code'])) {
					continue; 
				} 
				$match[] = '(?<=^|[\n .])' . preg_quote($row['code'], '#') . '(?![^<>]*>)';
				$replace[] = '<!-- s' . $row['code'] . ' --><img src="' . $phpbbForum->url . '/images/smilies/' . $row['smiley_url'] . '" alt="' . $row['code'] . '" title="' . $row['emotion'] . '" /><!-- s' . $row['code'] . ' -->';
			}
			$db->sql_freeresult($result);
			
			$phpbbForum->restore_state($fStateChanged);
			
		}
		if (sizeof($match)) {
			if ($max_smilies) {
				$num_matches = preg_match_all('#' . implode('|', $match) . '#', $postContent, $matches);
				unset($matches);
			}
			// Make sure the delimiter # is added in front and at the end of every element within $match
			$postContent = trim(preg_replace(explode(chr(0), '#' . implode('#' . chr(0) . '#', $match) . '#'), $replace, $postContent));
		}
	}
	return $postContent;
}




/**
 * Adds any required inline JS (for language strings)
 */
function wpu_inline_js() {
	global $wpUnited, $phpbbForum;
	
	// Rather than outputting the script, we just signpost any language strings we will need
	// The scripts themselves are already enqueud.
	if ( $wpUnited->get_setting('phpbbSmilies') ) {
		echo "\n<script type=\"text/javascript\">//<![CDATA[\nvar wpuLang ={";
		$langStrings = array('wpu_more_smilies', 'wpu_less_smilies', 'wpu_smiley_error');
		for($i=0; $i<sizeof($langStrings);$i++) {
			if($i>0) {
				echo ',';
			}
			echo "'{$langStrings[$i]}': '" . str_replace("\\\\'", "\\'", str_replace("'", "\\'",  $phpbbForum->lang[$langStrings[$i]])) . "'";
		}
		echo "} // ]]>\n</script>";
	}
}

/**
* Function 'wpu_fix_blank_username()' - Generates a username in WP when the sanitized username is blank,
* as phpbb is more liberal in user naming
* Originally by Wintermute
* If the sanitized user_login is blank, create a random
* username inside WP. The user_login begins with WPU followed
* by a random number (1-10) of digits between 0 & 9
* Also, check to make sure the user_login is unique
* @since WP-United 0.7.1
*/
function wpu_fix_blank_username($user_login) {
	global $wpUnited;

	if ($wpUnited->get_setting('integrateLogin')) { 
	    if ( empty($user_login) ){
			$foundFreeName = FALSE;
			while ( !$foundFreeName ) {
				$user_login = "WPU";
				srand(time());
				for ($i=0; $i < (rand()%9)+1; $i++)
					$user_login .= (rand()%9);
				if ( !username_exists($user_login) )
					$foundFreeName = TRUE;
			}
		}
	}
	return $user_login;
}



/**
* Under consideration for future rewrite: Function 'wpu_validate_username_conflict()' - Handles the conflict between validate_username
* in WP & phpBB. This is only really a problem in integrated pages when naughty WordPress plugins pull in
* registration.php. 
* 
* These functions should NOT collide in usage -- only in namespace. If user integration is turned on, we don't need
* WP's validate_username. 
* 
* Furthermore, if phpbb_validate_username is defined, then we know we most likely need to use the phpBB version.
* 
* We unfortunately cannot control their usage -- phpbb expects 2 arguments, whereas WordPress only expects one.
* 
* Therefore here we just try to avoid namespace errors. If they are actually invoked while renamed, the result is undefined
*/

function wpu_validate_username_conflict($wpValdUser, $username) {
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
function wpu_check_new_user($username, $email, $errors) {
	$result = wpu_validate_new_user($username, $email, $errors);
	
	if($result !== false) {
		$errors = $result;
	}
}

/**
 * checks a new registration 
 * This occurs after the account has been created, so it is only for naughty plugins that
 * leave no other way to intercept them.
 * If it is found to be an erroneous user creation, then we remove the newly-added user.
 * This action is removed by WP-United when adding a user, so we avoid unsetting our own additions
 */
add_action('user_register', 'wpu_check_new_user_after', 10, 1); 
function wpu_check_new_user_after($userID) { 
		global $wpUnited, $phpbbForum, $wpUnited, $wpuJustCreatedUser;
	
	
		/*
		 * if we've already created a user in this session, 
		 * it is likely an error from a plugin calling the user_register hook 
		 * after wp_insert_user has already called it. The Social Login plugin does this
		 * 
		 * At any rate, it is pointless to check twice
		 */
		if($wpuJustCreatedUser == $userID) {
				return;
		}

		// some registration plugins don't init WP. This is enough to get us a phpBB env
		$wpUnited->init_plugin();
			
		// neeed some user add / delete functions
		if ( ! defined('WP_ADMIN') ) {
			require_once(ABSPATH . 'wp-admin/includes/user.php');
		}


		if ($wpUnited->get_setting('integrateLogin')) { 
			
			$errors = new WP_Error();
			$user = get_userdata($userID);
			

			$result = wpu_validate_new_user($user->user_login, $user-->user_email , $errors);

			if($result !== false) { 
				// An error occurred validating the new WP user, remove the user.
				
				wp_delete_user($userID,  0);
				$message = '<h1>' . __('Error:') . '</h1>';
				$message .= '<p>' . implode('</p><p>', $errors->get_error_messages()) . '</p><p>';
				$message .= __('Please go back and try again, or contact an administrator if you keep seeing this error.') . '</p>';
				wp_die($message);
				
				exit();
			} else { 
	
				// create new integrated user in phpBB to match
				$phpbbID = wpu_create_phpbb_user($userID);
				$wpuJustCreatedUser = true;
			}
			
		}
}

/**
 * Validates a new or prospective WordPress user in phpBB
 * @param string $username username
 * @param string $email e-mail
 * @param WP_Error $errors WordPress error object
 * @return bool|WP_Error false (on success) or modified WP_Error object (on failure)
 */
function wpu_validate_new_user($username, $email, $errors) {
	global $wpUnited, $phpbbForum;
	$foundErrors = 0;
	if ($wpUnited->get_setting('integrateLogin')) {
		if(function_exists('phpbb_validate_username')) {
			$fStateChanged = $phpbbForum->foreground();
			$result = phpbb_validate_username($username, false);
			$emailResult = validate_email($email);
			$phpbbForum->restore_state($fStateChanged);

			if($result !== false) {
				switch($result) {
					case 'INVALID_CHARS':
						$errors->add('phpbb_invalid_chars', __('The username contains invalid characters'));
						$foundErrors++;
						break;
					case 'USERNAME_TAKEN':
						$errors->add('phpbb_username_taken', __('The username is already taken'));
						$foundErrors++;
						break;
					case 'USERNAME_DISALLOWED':
						default;
						$errors->add('phpbb_username_disallowed', __('The username you chose is not allowed'));
						$foundErrors++;
						break;
				}
			}
			
			if($emailResult !== false) {
				switch($emailResult) {
					case 'DOMAIN_NO_MX_RECORD':
						$errors->add('phpbb_invalid_email_mx', __('The email address does not appear to exist (No MX record)'));
						$foundErrors++;
						break;
					case 'EMAIL_BANNED':
						$errors->add('phpbb_email_banned', __('The e-mail address is banned'));
						$foundErrors++;
						break;
					case 'EMAIL_TAKEN':
						$errors->add('phpbb_email_taken', __('The e-mail address is already taken'));
						break;
					case 'EMAIL_INVALID':
						default;
						$errors->add('phpbb_invalid_email', __('The email address is invalid'));
						$foundErrors++;
						break;									
				}
			}

		}
	}
	
	return ($foundErrors) ? $errors : false;
	
	
}





function wpu_deactivate() {
	// No actions currently defined
	wpu_uninstall();  /** TEMP FOR RESETTING WHILE TESTING **/
}

/**
 * Removes all WP-United settings.
 * As the plugin is deactivated at this point, we can't reliably uninstall from phpBB (yet)
 */
function wpu_uninstall() {
	
	$forum_page_ID = get_option('wpu_set_forum');
	if ( !empty($forum_page_ID) ) {
		@wp_delete_post($forum_page_ID);
	}
		
	
	delete_option('wpu_set_forum');
	delete_option('wpu-settings');
	delete_option('wpu-last-run');
	delete_option('wpu-enabled');
	delete_option('widget_wp-united-loginuser-info');
	delete_option('widget_wp-united-latest-topics');
	delete_option('widget_wp-united-latest-posts');
	
	
	/*
	if(isset($wpUnited->get_setting('phpbb_path'))) {
		
		global $db;
		
		$phpbb_root_path = $wpUnited->get_setting('phpbb_path');
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
	
		define('IN_PHPBB', true);
		define('WPU_UNINSTALLING', true);
		
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		
		$commonLoc = $phpbb_root_path . 'common.' . $phpEx;
		
		if(file_exists($commonLoc)) {
			include($phpbb_root_path . 'common.' . $phpEx);
			
			$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
						  DROP user_wpuint_id';
			$db->sql_query($sql);
			
			$sql = 'ALTER TABLE ' . USERS_TABLE . '
						DROP user_wpublog_id';
			$db->sql_query($sql);
					
			$sql = 'ALTER TABLE ' . POSTS_TABLE . ' 
						DROP post_wpu_xpost';
			$db->sql_query($sql);
			
		}
	} */
	
	
}

register_deactivation_hook('wp-united/wp-united.php', 'wpu_deactivate');
register_uninstall_hook('wp-united/wp-united.php', 'wpu_uninstall');

?>
