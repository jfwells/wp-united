<?php
/*
Plugin Name: WP-United Connection 
Plugin URI: http://www.wp-united.com
Description: THis is the "WP-United Connection" -- it handles the connection with phpBB fro mthe WordPress side.
Author: John Wells
Version: 0.9.5-Beta (phpBB2) / v0.6.0 Beta (phpBB3)
Last Updated: 04 June 2007 
Author URI: http://www.wp-united.com

NOTE: This is a WordPress plugin, NOT a phpBB file and so it does not follow phpBB mod conventions. Specifically:
	- different hacking attempt check
	- different templating system
	- WordPress hard-codes php extensions, so so do we

*/ 

// this file will also be called in wp admin panel, when phpBB is not loaded. ABSPATH should *always* be set though!
if ( !defined('ABSPATH') ) {
	die('Hacking attempt!');
}
//
// 	WPU_CHECK_FOR_ACTION
//	----------------------------------
//	Processes the request
//	TODO: CHECK IF WE ARE ALLOWED TO SWITCH THEME / BLOG SETTINGS!
function wpu_check_for_action() {
	global $user_ID, $wp_version;

	if ( isset($_GET['wpu_action']) ) {
		if ('activate' == $_GET['wpu_action']) {
			check_admin_referer('wp-united-switch-theme_' . $_GET['template']);
			if ( isset($_GET['template']) )
					update_usermeta($user_ID,'WPU_MyTemplate',$_GET['template']);
	
			
			if ( isset($_GET['stylesheet']) )
				update_usermeta($user_ID,'WPU_MyStylesheet',$_GET['stylesheet']);
				$wpuConnSettings = get_settings('wputd_connection');
				wp_redirect('admin.php?page=' . $wpuConnSettings['full_path_to_plugin'] . '&activated=true&wputab=themes');
			exit;
		} elseif ('update-blog-profile' == $_GET['wpu_action']) {
			check_admin_referer('update-blog-profile_' . $user_ID);
			$errors = edit_user($user_ID);

			//$errors behaves differently post-WP 2.1
			if ( ((float) $wp_version) >= 2.1 ) {
				//WordPress >= 2.1
				if ( is_wp_error( $errors ) ) {
					foreach( $errors->get_error_messages() as $message ) {
						echo "<li>$message</li>";
					}
				}
			} else {
				//WP 2.0x
				if ( is_array($errors) ) {
					if (count($errors) != 0) {
						foreach ($errors as $id => $error) {
							echo $error . '<br/>';
						}
						exit;
					}
				}
			}	
			if ( !isset( $_POST['rich_editing'] ) )
				$_POST['rich_editing'] = 'false';
			update_user_option( $current_user->id, 'rich_editing', $_POST['rich_editing'], true );
			
			//
			//	UPDATE BLOG DETAILS
			//
			$blog_title = __('My Blog');
			$blog_tagline = __('My description will go here');
			if (isset ($_POST['blog_title']))
				$blog_title = wp_specialchars(trim($_POST['blog_title']));
			if (isset ($_POST['blog_tagline']))
				$blog_tagline = wp_specialchars(trim($_POST['blog_tagline']));			
			
			update_usermeta($user_ID, 'blog_title', $blog_title);
			update_usermeta($user_ID, 'blog_tagline', $blog_tagline);
			$wpuConnSettings = get_settings('wputd_connection');
			wp_redirect('admin.php?page=' . $wpuConnSettings['full_path_to_plugin'] . '&updated=true&wputab=bset');
			exit;
		}
	}
}


//
// 	WPU_PUT_POWERED_TEXT
//	----------------------------------
//	Adds the copyright statement
// 	Please DO NOT remove this!
//
function wpu_put_powered_text() {
	echo '<p  id="poweredby">phpbb integration &copy; 2006-2009 <a href="http://www.wp-united.com" target="_blank">WP-United</a></p>';
	$wpuConnSettings = get_settings('wputd_connection');
	if ( current_user_can('publish_posts') ) {	
		if ( $wpuConnSettings['blogs'] ) {
			echo '<p  id="welcome1">' . __('Welcome to WP-United. Click <strong>Write</strong> to add to your blog');
			if ( $wpuConnSettings['styles'] ) {	
				echo '<br />' . __('You can set how it looks under the <strong>Your Blog</strong> tab!');
			}
			echo '</p>';
		} else {
			echo '<p  id="welcome1">' . __('Welcome to WP-United!');
		}
	}
}


//
// 	WPU_CSS
//	--------------
//	Sets the styles of the messages we want to appear in the Admin Panel
//	Hides the messages we don't want
// 
function wpu_css() {
	global $wp_version;

	echo '
	<style type="text/css">
	#user_info {
		display: none !important;
	}
	#poweredby {
	text-align: center; 
	font-weight: bold;
	}
	#welcome1 {
		position: absolute;
		top: 1.0em;';
	if ($wp_version >= 2.5) {
		echo "\n	margin: 4px 150px 0 0;\n";
	} else {
		echo "\n	margin: 0;\n";
	}
	echo	'padding: 0;
		right: 1em;
		color: #f1f1f1;
	}
	</style>
	';
}

//
// 	WPU_ADMINMENU_INIT
//	----------------------------------
//	Inserts the admin pages we want, and directs top the admin page requested
// 
function wpu_adminmenu_init() {

	$wpuConnSettings = get_settings('wputd_connection');
	
	//Check for action
	if ( isset($_GET['wpu_action']) ) {
		wpu_check_for_action();
		exit();
	}
	global $menu;

	$fullFilePath = $wpuConnSettings['path_to_plugin'];
	
	if (!empty($wpuConnSettings['logins_integrated'])) {
		if (function_exists('add_submenu_page')) {
			if (current_user_can('publish_posts'))  {
				if ( !empty($wpuConnSettings['blogs']) ) {
					add_menu_page(__('Your Blog'), __('Your Blog'), 'publish_posts', $wpuConnSettings['full_path_to_plugin'], 'wpu_menuTopLevel');
				} 
				if ( isset($_GET['page']) ) { // add submenus if we're under the blog main page
				$wpuPage = ( $_GET['page'] == $wpuConnSettings['full_path_to_plugin'] ) ? TRUE : FALSE;
					if ( $wpuPage ) {
						global $parent_file;
						$parent_file = 'wpu';
						add_submenu_page('wpu', __('Your Blog Settings'), __('Your Blog Settings'), 'publish_posts', $wpuConnSettings['full_path_to_plugin'] . '&wputab=bset', 'wpu_menuTopLevel');
						if ( !empty($wpuConnSettings['styles']) ) {
							add_submenu_page('wpu', __('Set Blog Theme'), __('Set Blog Theme'), 'publish_posts', $wpuConnSettings['full_path_to_plugin'] . '&wputab=themes', 'wp_united_display_theme_menu');
						}
					}
				}
			} 
			//Redirect the profile page if own blogs -- if not own blogs, it gets buffered anyway.
			if (preg_match('|/wp-admin/profile.php|', $_SERVER['REQUEST_URI'])) {
				$phpbb_root_path = $wpuConnSettings['path_to_phpbb'];
				if ( (current_user_can('publish_posts')) && ($wpuConnSettings['blogs']==1) )  {
					wp_redirect('admin.php?page=' . $wpuConnSettings['full_path_to_plugin']);
				} else {
					wp_redirect($phpbb_root_path.'ucp.php');
				}
			}
			//Redirect the edit users page (just in case)
			if(preg_match('|/wp-admin/user-edit.php|', $_SERVER['REQUEST_URI'])) {
				wp_redirect('users.php');
			}
		}
	}
}
 
 
//
// 	wpu_menuTopLevel
//	----------------------------------
//	Displays the top-leel menu for WP-United, "Your Blog".
// 
function wpu_menuTopLevel($existing_content = '') {

	if ( isset($_GET['wputab']) ) {
		$tab = ($_GET['wputab'] == 'themes') ? 'THEMES' : 'SETTINGS';
	}
	if ( 'THEMES' != $tab) {

		global $user_ID, $wp_roles;
		$profileuser = get_user_to_edit($user_ID);
		$bookmarklet_height= 440;
		$wpuConnSettings = get_settings('wputd_connection');
		$page_output = '';
		if ( isset($_GET['updated']) ) { 
			$page_output .= '<div id="message" class="updated fade">
			<p><strong>' . __('Settings updated.') . '</strong></p>
			</div>';
		}

		$page_output .= '<div class="wrap">
		<h2>';
		if ( !empty($wpuConnSettings['blogs']) ) {
			$page_output .= __('Your Blog Details');
		} else {
			$page_output .= __('Your Profile');
		}
		$page_output .= '</h2>
		<form name="profile" id="your-profile" action="admin.php?noheader=true&amp;page=' . $wpuConnSettings['full_path_to_plugin'] . '&amp;wpu_action=update-blog-profile" method="post">' . "\n";
		// have to use this, because wp_nonce_field echos. //wp_nonce_field('update-blog-profile_' . $user_ID);
		// beginning of nonce fields
		$page_output .= '<input type="hidden" name="' . attribute_escape('_wpnonce') . '" value="' . wp_create_nonce('update-blog-profile_'.$user_ID) . '" />';
		$ref = attribute_escape($_SERVER['REQUEST_URI']);
		$page_output .= '<input type="hidden" name="_wp_http_referer" value="'. $ref . '" />';
		if ( wp_get_original_referer() ) {
			$original_ref = attribute_escape(stripslashes(wp_get_original_referer()));
			$page_output .= '<input type="hidden" name="_wp_original_http_referer" value="'. $original_ref . '" />';
		}
		// End of nonce fields
		$page_output .= '<p>
			<!--<input type="hidden" name="page" value="' . $wpuConnSettings['full_path_to_plugin'] . '" /> -->
			<!--<input type="hidden" name="action" value="update-blog-profile" />-->
			<!--<input type="hidden" name="from" value="blog_settings" /> -->
			<input type="hidden" name="checkuser_id" value="' . 'echo $user_ID' . '" />
		</p>	
		<fieldset>
		<legend>' . __('Name') . '</legend>

		<input type="hidden" name="user_login" value="' . $profileuser->user_login . '"  />


		<p><label>' . __('First name:') . '<br />
		<input type="text" name="first_name" value="' . $profileuser->first_name . '" /></label></p>

		<p><label>' . ('Last name:') . '<br />
		<input type="text" name="last_name"  value="' . $profileuser->last_name . '" /></label></p>

		<p><label>' . __('Nickname:') . '<br />
		<input type="text" name="nickname" value="' . $profileuser->nickname . '" /></label></p>

		<p><label>' . __('Display name publicly as:') . '<br />
		<select name="display_name">
		<option value="' . $profileuser->display_name . '">' . $profileuser->display_name . '</option>
		<option value="' . $profileuser->nickname . '">' . $profileuser->nickname . '</option>
		<option value="' . $profileuser->user_login . '">' . $profileuser->user_login . '</option>';
		if ( !empty( $profileuser->first_name ) ) {
			$page_output .= '<option value="' . $profileuser->first_name . '">' . $profileuser->first_name . '</option>';
		}
		if ( !empty( $profileuser->last_name ) ) {
			$page_output .= '<option value="' . $profileuser->last_name . '">' . $profileuser->last_name . '</option>';
		}
		if ( !empty( $profileuser->first_name ) && !empty( $profileuser->last_name ) ) {
			$page_output .= '<option value="' . $profileuser->first_name . ' ' . $profileuser->last_name . '">' . $profileuser->first_name . ' ' . $profileuser->last_name . '</option>
			<option value="' . $profileuser->last_name . ' ' . $profileuser->first_name . '">' . $profileuser->last_name . ' ' . $profileuser->first_name . '</option>';
		}
		$page_output .= '</select></label></p>
		</fieldset>';
		if ( !empty($wpuConnSettings['blogs']) ) {
			$page_output .= '<fieldset>
			<legend>' . __('About Your Blog') . '</legend>
			<input type="hidden" name="email" value="' . $profileuser->user_email . '" />';
			// Retrieve blog options
			$blog_title = get_usermeta($user_ID, 'blog_title');
			$blog_tagline = get_usermeta($user_ID, 'blog_tagline');
			$page_output .= '<p><label>' . __('The Title of Your Blog:') . '<br />
			<input type="text" name="blog_title" value="' . $blog_title . '" /></label></p>
			<p><label>' . __('Blog Tagline') . '<br />
			<input type="text" name="blog_tagline" value="' . $blog_tagline . '"</label></p>
			</fieldset>';
		}
		$page_output .= '<br clear="all" />
		<fieldset> 
		<legend>' . __('About yourself') . '</legend>
		<p class="desc">' . __('Share a little biographical information to fill out your profile. This may be shown publicly.') . '</p>
		<p><textarea name="description" rows="5" cols="30">' . $profileuser->description . '</textarea></p>
		</fieldset>';
		do_action('show_user_profile'); 
		$page_output .= '<br clear="all" />	
		<h3>' . __('Personal Options') . '</h3>
		<p><label for="rich_editing"><input name="rich_editing" type="checkbox" id="rich_editing" value="true"' . checked('true', get_user_option('rich_editing')) . '/>' .
		__('Use the visual rich editor when writing') . '</label></p>';
		do_action('profile_personal_options');
		$page_output .= '<table width="99%"  border="0" cellspacing="2" cellpadding="3" class="editform">';
		if(count($profileuser->caps) > count($profileuser->roles)) {
		    $page_output .= '<tr>
		    <th scope="row">' . __('Additional Capabilities:') . '</th>
		    <td>';
			$output = '';
			foreach($profileuser->caps as $cap => $value) {
				if(!$wp_roles->is_role($cap)) {
					if($output != '') $output .= ', ';
					$output .= $value ? $cap : "Denied: {$cap}";
				}
			}
			$page_output .= $output . '
			</td>
		    </tr>';
	    }
		$page_output .= '</table>
		<p class="submit">
		<input type="submit" value="' . __('Update Profile &raquo;') . '" name="submit" />
		</p>
		</form>
			
		</div>';
		//What to do with this page we've just made?
		if (defined('WPU_ALTER_PROFILE')) {
			//replace profile page with it
			return $page_output  . '<div id="footer">';
		} else {
			// display the page
			echo $page_output;
		}
	} else {
	wp_united_display_theme_menu();
	}

}



//
// 	WP_UNITED_DISPLAY_THEME_MENU
//	------------------------------------------------------
//	If Style switching is allowed, displays the author theme switching menu
//	Modelled on WP's existing themes.php
// 
function wp_united_display_theme_menu() {

	global $user_ID;
	global $title, $parent_file;
		$wpuConnSettings = get_settings('wputd_connection');

	if ( ! validate_current_theme() ) : ?>
	<div id="message1" class="updated fade"><p><?php _e('The active theme is broken.  Reverting to the default theme.'); ?></p></div>
	<?php elseif ( isset($_GET['activated']) ) : ?>
	<div id="message2" class="updated fade"><p><?php printf(__('New theme activated. <a href="%s">View site &raquo;</a>'), get_bloginfo('home') . '/'); ?></p></div>
	<?php endif; ?>

	<?php
	$themes = get_themes();
		$theme_names = array_keys($themes);
	$user_theme = 'WordPress Default';

	$user_template = get_usermeta($user_ID, 'WPU_MyTemplate');
	$user_stylesheet = get_usermeta($user_ID, 'WPU_MyStylesheet');

	$current_theme = 'WordPress Default';

	if ( $themes ) {
		foreach ($theme_names as $theme_name) {
			if ( $themes[$theme_name]['Stylesheet'] == $user_stylesheet &&
					$themes[$theme_name]['Template'] == $user_template ) {
				$user_theme = $themes[$theme_name]['Name'];
				break;
			}
		}
	}
	
	$template = $themes[$user_theme]['Template'];
	$stylesheet = $themes[$user_theme]['Stylesheet'];
	$title = $themes[$user_theme]['Title'];
	$version = $themes[$user_theme]['Version'];
	$description = $themes[$user_theme]['Description'];
	$author = $themes[$user_theme]['Author'];
	$screenshot = $themes[$user_theme]['Screenshot'];
	$stylesheet_dir = $themes[$user_theme]['Stylesheet Dir'];
	
	?>

	<div class="wrap">
	<h2><?php _e('Your Current Theme'); ?></h2>
	<div id="currenttheme" style="margin-bottom: 190px;" >
	<?php if ( $screenshot ) : ?>
	<img src="<?php echo get_option('siteurl') . '/' . $stylesheet_dir . '/' . $screenshot; ?>" alt="<?php _e('Current theme preview'); ?>" />
	<?php endif; ?>
	<h3><?php printf(__('%1$s %2$s by %3$s'), $title, $version, $author) ; ?></h3>
	<p><?php echo $description; ?></p>

	</div>

	<h2><?php _e('Available Themes'); ?></h2>
	<?php if ( 1 < count($themes) ) { ?>

	<?php
	$style = '';

	$theme_names = array_keys($themes);
	natcasesort($theme_names);

	foreach ($theme_names as $theme_name) {
		if ( $theme_name == $user_theme )
			continue;
		$template = $themes[$theme_name]['Template'];
		$stylesheet = $themes[$theme_name]['Stylesheet'];
		$title = $themes[$theme_name]['Title'];
		$version = $themes[$theme_name]['Version'];
		$description = $themes[$theme_name]['Description'];
		$author = $themes[$theme_name]['Author'];
		$screenshot = $themes[$theme_name]['Screenshot'];
		$stylesheet_dir = $themes[$theme_name]['Stylesheet Dir'];
		$activate_link = wp_nonce_url('admin.php?page=' . $wpuConnSettings['full_path_to_plugin'] . '&amp;noheader=true&amp;wpu_action=activate&amp;template=' . $template . '&amp;stylesheet=' . $stylesheet, 'wp-united-switch-theme_' . $template);
	?>
	<div class="available-theme">
	<h3><a href="<?php echo $activate_link; ?>"><?php echo "$title $version"; ?></a></h3>

	<a href="<?php echo $activate_link; ?>" class="screenshot">
	<?php if ( $screenshot ) : ?>
	<img src="<?php echo get_option('siteurl') . '/' . $stylesheet_dir . '/' . $screenshot; ?>" alt="" />
	<?php endif; ?>
	</a>

	<p><?php echo $description; ?></p>
	</div>
	<?php } // end foreach theme_names ?>

	<?php } ?>

	

	<h2><?php _e('Want More Themes?'); ?></h2>
	<p><?php _e('If you have found another WordPress theme that you would like to use, please inform an administrator.'); ?></p>

	</div>

<?php
}



//
// 	WPU_GET_TEMPLATE
//	----------------------------------
//	If Style switching is allowed, returns the template for the current author's blog
//	We could do all this much later, in the template loader, but it is safer here so we load in all template-specific widgets, etc.
//
function wpu_get_template($default) {
	global $wpSettings;
	if ( !empty($wpSettings['allowStyleSwitch']) ) {
		//The first time this is called, wp_query, wp_rewrite, haven't been set up, so we can't see what kind of page it's gonna be
		// so set them up now
		if ( !defined('TEMPLATEPATH') ) {
			$GLOBALS['wp_the_query'] =& new WP_Query();
			$GLOBALS['wp_query']     =& $GLOBALS['wp_the_query']; 
			$GLOBALS['wp_rewrite']   =& new WP_Rewrite();
			$GLOBALS['wp']           =& new WP(); 
			$GLOBALS['wp']->init(); 
			$GLOBALS['wp']->parse_request(); 
			$GLOBALS['wp']->query_posts(); 
		}

		if ( $authorID = wpu_get_author() ) {
			$wpu_templatedir = get_usermeta($authorID, 'WPU_MyTemplate');
			$wpu_theme_path = get_theme_root() . "/$wpu_templatedir";
			if ( (file_exists($wpu_theme_path)) && (!empty($wpu_templatedir)) )	{
				return $wpu_templatedir;
			}
		}
	}
	return $default;
}

//
// 	WPU_GET_STYLESHEET
//	----------------------------------
//	If Style switching is allowed, returns the stylesheet for the current author's blog
//
function wpu_get_stylesheet($default) {
	global $wp_query, $wpSettings;
	if ( !empty($wpSettings['allowStyleSwitch']) ) {
		if ( $authorID = wpu_get_author() ) {
			$wpu_stylesheetdir = get_usermeta($authorID, 'WPU_MyStylesheet');
			$wpu_theme_path = get_theme_root() . "/$wpu_stylesheetdir";
			if ( (file_exists($wpu_theme_path)) && (!empty($wpu_stylesheetdir)) )	{
				return $wpu_stylesheetdir;
			}
		}
	}
	return $default;
}

//
// 	WPU_LOGINOUTLINK
//	-----------------------------
//	Modifies the WordPress "Login/Out" link to point to phpBB installation
//	Only modifies it if login is integrated
//
function wpu_loginoutlink($loginLink) {
	global $wpSettings, $phpbb_logged_in, $phpbb_username, $phpbb_sid, $wpuAbs, $phpEx, $scriptPath;
	if ( !empty($wpSettings['integrateLogin']) ) {
		if ($wpuAbs->ver == 'PHPBB2') {
			$logout_link = 'login.'.$phpEx.'?logout=true&amp;redirect=wp-united-blog&amp;sid=' . $phpbb_sid;
			$login_link = 'login.'.$phpEx.'?redirect=wp-united-blog&amp;sid='. $phpbb_sid;
		} else {
			$logout_link = 'ucp.'.$phpEx.'?mode=logout&amp;sid=' . $phpbb_sid;
			$login_link = 'ucp.'.$phpEx.'?mode=login&amp;sid=' . $phpbb_sid . '&amp;redirect=' . attribute_escape($_SERVER["REQUEST_URI"]);		
		}
		if ( $phpbb_logged_in ) {
			$u_login_logout = add_trailing_slash($scriptPath) . $logout_link;
			$l_login_logout = $wpuAbs->lang('Logout') . ' [ ' . $phpbb_username . ' ]';
		} else {
			$u_login_logout = add_trailing_slash($scriptPath) . $login_link;
			$l_login_logout = $wpuAbs->lang('Login');
		}
		return '<a href="' . $u_login_logout . '">' . $l_login_logout . '</a>';
	} else {
		return $loginLink;
	}
}

//
// 	WPU_REGISTERLINK
//	-----------------------------
//	Modifies the WordPress "Register" link to point to phpBB installation
//	Only modifies it if login is integrated
//
function wpu_registerlink($registerLink) {
	global $wpSettings, $phpbb_logged_in, $phpbb_sid, $wpuAbs, $phpEx, $wpuGetBlog, $scriptPath;
	if ( !empty($wpSettings['integrateLogin']) ) {
		//'before' and 'after' can be passed to the links. Let's isolate them.
		$startPos = strpos($registerLink, '<a');
		$endPos = strpos($registerLink, '</a>');
		$before = substr($registerLink, 0, $startPos);
		$after = substr($registerLink, $endPos + 4);
		// TODO -- CHECK WHETHER WE SHOULD DO THIS!!
		$before = empty($before) ? '<li>' : $before;
		$after = empty($after) ? '</li>' : $after;
		if ($wpuAbs->ver == 'PHPBB2') {
			$reg_link =  'profile.'.$phpEx.'?mode=register&amp;redirect=wp-united-blog';
		} else {
			$reg_link =  'ucp.'.$phpEx.'?mode=register';
		}
		if ( ! is_user_logged_in() ) {
			return $before . '<a href="' . append_sid(add_trailing_slash($scriptPath) . $reg_link) . '">' . $wpuAbs->lang('Register') . '</a>' . $after;
		} else {
			return $before . '<a href="' . get_settings('siteurl') . '/wp-admin/">' . $wpuGetBlog . '</a>' . $after;
		}
	} else { 
		return $registerLink;
	}	
}


//
// 	WPU_JUSTEDITING
//	---------------------
//	Called whenever a post is edited
//  Prevents an edited post from showing up on the blogs homepage
//
function wpu_justediting() {
	define('suppress_newpost_action', TRUE);
}

//
// 	WPU_NEWPOST
//	---------------------
//	Called whenever a new post is published.
//	Updates the phpBB user table with the latest author ID, to facilitate direct linkage via blog buttons
//
function wpu_newpost($post_ID) {

	$connSettings = get_settings('wputd_connection');
	global $user_ID, $wpdb, $wp_version;
	$post = get_post($post_ID); 
	if ( $post->post_status == 'publish' ) {
		if (!defined('suppress_newpost_action')) { //This should only happen ONCE, when the post is initially created.
			update_usermeta($post->post_author, 'wpu_last_post', $post->post_author);
		}
		if ( (!defined('IN_PHPBB')) && (!empty($connSettings['logins_integrated'])) ) {
			global $db, $wpuAbs, $user, $phpEx;
			wpu_enter_phpbb();
			
			// Update blog link column
			if ( !empty($post->post_author) ) {
				$sql = 'UPDATE ' . USERS_TABLE . ' SET user_wpublog_id = ' . $post->post_author . " WHERE user_wpuint_id = '{$post->post_author}'";
				if (!$result = $db->sql_query($sql)) {
					$wpuAbs->err_msg(CRITICAL_ERROR, $wpuAbs->lang('WP_DBErr_Retrieve'), __LINE__, __FILE__, $sql);
				}
				$db->sql_freeresult($result);
			}
		//X-Posting
			mysql_select_db(DBNAME);
			// Cross-post to forums if necessary
			if ( isset($_POST['chk_wpuxpost']) && ($wpuAbs->user_logged_in()) && (!isset($_POST['wpu_already_xposted_post'])) ) {
				if ( ((int)$_POST['chk_wpuxpost'] ) && ($forum_id = (int)$_POST['sel_wpuxpost']) && $connSettings['wpu_enable_xpost'] ) { 
					$can_crosspost_list = wpu_forum_xpost_list(); 
					//Check that we have the authority to cross-post there
					if ( in_array($forum_id, $can_crosspost_list['forum_id']) ) { 
						require_once($connSettings['path_to_phpbb'] . 'wp-united/wpu-helper-funcs.' . $phpEx);
						// Get the post excerpt
						if (!$excerpt = $post->post_excerpt) {
							$excerpt = $post->post_content;
							if ( preg_match('/<!--more(.*?)?-->/', $excerpt, $matches) ) {
								$excerpt = explode($matches[0], $excerpt, 2);
								$excerpt = $excerpt[0];
							}
						}							
						$subject = $wpuAbs->lang('blog_title_prefix') . $post->post_title;
						$cats = array(); $tags = array();
						$tag_list = ''; $cat_list = '';
						$cats = get_the_category($post_ID);
						if (sizeof($cats)) {
							foreach ($cats as $cat) {
								$cat_list .= (empty($cat_list)) ? $cat->cat_name :  ', ' . $cat->cat_name;
							}
						}
						if ( ((float) $wp_version) >= 2.3 ) {

                         // Get tags for WP >= 2.3

                         $tags = get_the_term_list($post->ID, 'post_tag', '', ', ', '');
                         if ($tags == "") {
                            $tags = "No tags definied.";
                         }

						}      						
						mysql_select_db($GLOBALS['dbname']);
						$excerpt = sprintf($wpuAbs->lang('blog_post_intro'), '[url=' . get_permalink($post_ID) . ']', '[/url]') . "\n\n" . $excerpt . "\n\n" .
								'[b]' . $wpuAbs->lang('blog_post_tags') . '[/b]' . $tag_list . "\n" .
								'[b]' . $wpuAbs->lang('blog_post_cats') . '[/b]' . $cat_list . "\n" .
								sprintf($wpuAbs->lang('read_more'), '[url=' . get_permalink($post_ID) . ']', '[/url]');
								
						if ($wpuAbs->ver == 'PHPBB3') {
							$excerpt = utf8_normalize_nfc($excerpt, '', true);
							$subject = utf8_normalize_nfc($subject, '', true);
							wpu_html_to_bbcode($excerpt, 0); //$uid=0, but will get removed)
							$uid = $poll = $bitfield = $options = ''; 
							generate_text_for_storage($excerpt, $uid, $bitfield, $options, true, true, true);

						//fix phpBB SEO mod - Uncomment the following 5 lines if using phpbb_seo
//                         global $phpbb_seo;
//                         if (!empty($phpbb_seo) ) {
//                            require_once($connSettings['path_to_phpbb'] . 'phpbb_seo/phpbb_seo_class.'.$phpEx);
//                            $phpbb_seo = new phpbb_seo();
//                         }
                         //fix phpBB SEO mod
						 
							require_once($connSettings['path_to_phpbb'] . 'includes/functions_posting.' . $phpEx);
							$data = array(
								'forum_id' => $forum_id,
								'icon_id' => false,
								'enable_bbcode' => true,
								'enable_smilies' => true,
								'enable_urls' => true,
								'enable_sig' => true,
								'message' => $excerpt,
								'message_md5' => md5($excerpt),
								'bbcode_bitfield' => $bitfield,
								'bbcode_uid' => $uid,
								'post_edit_locked'	=> ITEM_LOCKED,
								'topic_title'		=> $subject,
								'notify_set'		=> false,
								'notify'			=> false,
								'post_time' 		=> 0,
								'forum_name'		=> '',
								'enable_indexing'	=> true,
							); 
							$topic_url = submit_post('post', $subject, $wpuAbs->phpbb_username(), POST_NORMAL, $poll, $data);
							
							//Update the post table with WP post ID so we can remain "in sync" with it (yawn... another query..... need to cut down on these).
							if ( !empty($data['post_id']) ) {
								$sql = 'UPDATE ' . POSTS_TABLE . ' SET post_wpu_xpost = ' . $post_ID . " WHERE post_id = {$data['post_id']}";
								if (!$result = $db->sql_query($sql)) {
									$wpuAbs->err_msg(CRITICAL_ERROR, $wpuAbs->lang('WP_DBErr_Retrieve'), __LINE__, __FILE__, $sql);
								}
								$db->sql_freeresult($result);
							}
						} //end if phpBB3
					} //end have authority to x-post
				}
			} //end isset & user_logged_in
			wpu_exit_phpbb();
		} //end ? logins integrated
	} //post status: publish

}

//
//	WPU_ENTER_PHPBB
//	----------------------------
//	Enters phpBB (for use in WP admin panel)
//
function wpu_enter_phpbb() {
	$connSettings = get_settings('wputd_connection');
	//need board config global for abstractify.php
	global $IN_WORDPRESS, $phpEx, $db, $table_prefix, $wp_table_prefix, $wpuAbs, $phpbb_root_path, $IN_WP_ADMIN, $auth, $user, $cache, $cache_old, $user_old, $config, $template, $dbname;
	//phpBB makes this conflicting var global (in phpBB2). But we want to revert back to WP's afterwards.
	$wp_table_prefix = $table_prefix;
	$user_old = (isset($GLOBALS['user'])) ? $GLOBALS['user']: '';
	$cache_old = (isset($GLOBALS['cache'])) ? $GLOBALS['cache'] : '';
	$IN_WORDPRESS = 1; $IN_WP_ADMIN = 1;
	define('IN_PHPBB', 1);
	// Guess what? Yep, this whole function is phpBB-version agnostic :-)
	$phpbb_root_path = $connSettings['path_to_phpbb'];
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include($phpbb_root_path . 'common.' . $phpEx);
	include($phpbb_root_path . 'wp-united/abstractify.'.$phpEx);

}

//
//	WPU_EXIT_PHPBB
//	--------------------------
//	Exits phpBB (for use in WP admin panel)
//
function wpu_exit_phpbb() {
	//clean up
	global $table_prefix, $wp_table_prefix, $cache, $user;
	$user = $GLOBALS['user_old'];
	$cache = $GLOBALS['cache_old'];
	$table_prefix = $wp_table_prefix;
	
	// just in case
	mysql_select_db(DB_NAME);
}

//
//	WPU_FORUM_XPOST_LIST
//	--------------------------------------
//	Get the list of forums we can cross-post to
//
function wpu_forum_xpost_list() {
	global $wpuAbs, $user, $auth, $db, $userdata, $template, $phpEx;
	$can_xpost_forumlist = array();
	$can_xpost_to = array();
	if ( 'PHPBB2' == $wpuAbs->ver ) {
		// TODO: PHPBB2 BRANCH!!
	} else {
		$can_xpost_to = $auth->acl_get_list($user->data['user_id'], 'f_wpu_xpost');
		if ( sizeof($can_xpost_to) ) {
			$can_xpost_to = array_keys($can_xpost_to);
		}
		//Don't return categories -- just forums!
		if ( sizeof($can_xpost_to) ) {
			$sql = 'SELECT forum_id, forum_name FROM ' . FORUMS_TABLE . ' WHERE ' .
				'forum_type = ' . FORUM_POST . ' AND ' .
				$db->sql_in_set('forum_id', $can_xpost_to);
			if ($result = $db->sql_query($sql)) {
				while ( $row = $db->sql_fetchrow($result) ) {
					$can_xpost_forumlist['forum_id'][] = $row['forum_id'];
					$can_xpost_forumlist['forum_name'][] = $row['forum_name'];
				}
				$db->sql_freeresult($result);
				return $can_xpost_forumlist;
			}
		}
	}
	return array();
}

//
//	WPU_GET_XPOSTED_DETAILS
//	-------------------------------------------
//	Determine if this post is already cross-posted.
//
function wpu_get_xposted_details() {
	if (isset($_GET['post'])) {
		$post_ID = (int)$_GET['post'];
		if ( !empty($post_ID) ) {
			global $db;
			$sql = 'SELECT p.post_id, p.forum_id, f.forum_name FROM ' . POSTS_TABLE . ' AS p, ' . FORUMS_TABLE . ' AS f WHERE ' .
				"p.post_wpu_xpost = $post_ID AND " .
				'f.forum_id = p.forum_id';
			if ($result = $db->sql_query_limit($sql, 1)) {
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				if ( (!empty($row['forum_id'])) && (!empty($row['forum_name'])) ) {
					return $row;
				}
			}
				
		}
	}
	return false;
}
	
//
// 	WPU_BLOGNAME
//	---------------------
//	Returns the name of the current user's blog
//
function wpu_blogname($default) {
	global $wpSettings, $user_ID, $wpuAbs, $adminSetOnce;
	if ( ((!empty($wpSettings['usersOwnBlogs'])) || ((is_admin()) && (!$adminSetOnce)))  ) {
		$authorID = wpu_get_author();
		if ($authorID === FALSE) {
			if ( is_admin() ) {
				$authorID = $user_ID;
				$adminSetOnce = 1; //only set once, for title
			}
		}	
		if ( !empty($authorID) ) {
			$blog_title = get_usermeta($authorID, 'blog_title');
			if ( empty($blog_title) ) {
				if ( !is_admin() ) {
					$blog_title = $wpuAbs->lang('default_blogname'); 
				}
			}
			$blog_title = wpu_censor($blog_title);
			if ( !empty($blog_title) ) {
				return $blog_title;
			}
		}
	}
	return $default;
}

//
// 	WPU_BLOGDESC
//	---------------------
//	Returns the tagline of the current user's blog
//
function wpu_blogdesc($default) {
	global $wpSettings, $wpuAbs;
	if ( !empty($wpSettings['usersOwnBlogs']) ) {
		$authorID = wpu_get_author();
		if ( !empty($authorID) ) {
			$blog_tagline = get_usermeta($authorID, 'blog_tagline');
			if ( empty($blog_tagline) ) {
				$blog_tagline = $wpuAbs->lang('default_blogdesc');
			}
			$blog_tagline = wpu_censor($blog_tagline);
			return $blog_tagline;
		}
	}
	return $default;
}

//
// 	WPU_HOMELINK
//	---------------------
//	Returns the URL of the current user's blog
//
function wpu_homelink($default) {
	global $wpSettings, $user_ID, $wpu_done_head, $altered_link, $wp_version;
	if ( ($wpu_done_head) && (!$altered_link) ) {
		$wpuConnSettings = get_settings('wputd_connection');
		if ( !empty($wpuConnSettings['blogs']) ) {
			$altered_link = TRUE; // prevents this from becoming recursive -- we only wantto do it once anyway
			if ( !is_admin() ) {
				$authorID = wpu_get_author();
			} else {
				$authorID = $user_ID;
			}
			if ( !empty($authorID) ) {
				if ( ((float) $wp_version) >= 2.1 ) {
					//WP >= 2.1 branch
					$blog_url = get_author_posts_url($authorID); 
				} else {
					$blog_url = get_author_link(false, $authorID, ''); 
				}
				$blog_url = ( $blog_url[strlen($blog_url)-1] == "/" ) ? substr($blog_url, 0, -1) : $blog_url; //kill trailing slash
				if ( empty($blog_url) ) {
					$blog_url = $default; 
				}
				return $blog_url;
			}
		}
	}
	return $default;
}




//	WPU_GET_AUTHOR
//	---------------------------
//	Code to figure out which author blog this is. Caches the result.
//
function wpu_get_author() {
	global $wp_query, $wpuCachedAuthor;
	$authorID = FALSE;
	if ( empty($wpuCachedAuthor) ) {
		if ( is_author() ) {	
			$authorID = $wp_query->query_vars['author'] ; 
		} elseif ( is_single() ) {
			$authorID = $wp_query->get_queried_object();
			$authorID = $authorID->post_author;
		} elseif ( isset($_GET['author'] )) { 
			$authorID = (integer)$_GET['author']; 
		} 
		$wpuCachedAuthor = $authorID;
	} else {
		$authorID = $wpuCachedAuthor;
	}
	return $authorID;
}


//
//	WPU_DONE_HEAD
//	--------------------------
//	Sets wpu_done_head to true, so we can alter things like the home link without worrying.
//	We don't wanna change these options before the <head> is finished.
//
function wpu_done_head() {
	global $wpu_done_head, $wpSettings, $scriptPath, $wp_the_query;
	$wpu_done_head = true;
	
	if ( defined('WPU_DEBUG') && (WPU_DEBUG == TRUE) ) {
		echo $GLOBALS['wpUtdInt']->debugBuffer . '</div><!-- /wpu-debug -->';
	}
	//add the frontpage stylesheet, if needed: 
	if ( (!empty($wpSettings['blUseCSS'])) && (!empty($wpSettings['useBlogHome'])) ) {
		echo '<link rel="stylesheet" href="' . add_trailing_slash($scriptPath) . 'wp-united/theme/wpu-blogs-homepage.css" type="text/css" media="screen" />';
	}
	if ( (defined('WPU_REVERSE_INTEGRATION')) && ($wpSettings['cssFirst'] == 'W') ) {
		echo '[--PHPBB*HEAD--]';
	}
	
	
}

//
//	WPU_LOOP_ENTRY
//	---------------------------
//	This is the last chance we have to prevent the home link from being changed
function wpu_loop_entry() {
	$GLOBALS['altered_link'] = TRUE;
}

// 	WPU_CONTENT_PARSE_CHECK
// 	-------------------------------------------
// 	turns our page place holder into the blog list page, or the forum-in-a-full-page
//
function wpu_content_parse_check($postContent) {
	if (! defined('PHPBB_CONTENT_ONLY') ) {
		if ( !(strpos($postContent, "<!--wp-united-home-->") === FALSE) ) {
			$postContent = get_wpu_blogs_home();
		} else {
			$postContent = wpu_censor($postContent);
		}
	} else {
		global $pfContent;
		$postContent = $pfContent;
		$GLOBALS['pfContent'] = null;
	}
	return $postContent;
}

//	 WPU_CENSOR
// 	----------------------------------
//	Handles parsing of posts through the phpBB word censor.
//	We also use this hook to suppress everything if this is aforum page.
//
function wpu_censor($postContent) {
	global $wpSettings, $wpuAbs;
	//if (! defined('PHPBB_CONTENT_ONLY') ) {  Commented out as we DO want this to to work on a full reverse page.
		if ( !is_admin() ) {
			if ( !empty($wpSettings['phpbbCensor'] ) ) { 
				return $wpuAbs->censor($postContent);
			}
		}
		return $postContent;
	//}
}

//
//	WPU_PREV_NEXT_POST
//	------------------------
//	Alters the where clause of the sql for previous/Next post lookup, to ensure we stay on the same author blog
//
function wpu_prev_next_post($where) {
	global $wpSettings, $post;
	$author = $post->post_author;
	
	if ( !empty($wpSettings['usersOwnBlogs']) ) {
		$where = str_replace("AND post_type = 'post'", "AND post_author = '$author' AND post_type = 'post'", $where); 
	}	
	return $where;
}

//
// 	WPU_USER_UPLOAD_DIR
//	------------------------------------
//	If users can have own blogs, uploads attachments to users' own directories.
//	i.e. uploads/username or uploads/username/yyyyy/mm
//
function wpu_user_upload_dir($default) {
	$wpuConnSettings = get_settings('wputd_connection');
	if ( !empty($wpuConnSettings['logins_integrated']) ) {
		global $user_ID;
		$usr = get_userdata($user_ID);
		$usrDir = $usr->user_login;
		if ( get_option('uploads_use_yearmonth_folders')) {
			$inputDir = explode('/', $default['path']);
			$inputUrl = explode('/', $default['url']);
			array_splice($inputDir, count($inputDir) - 2, 0, $usrDir);
			array_splice($inputUrl, count($inputUrl) - 2, 0, $usrDir);
			$inputDir = implode('/', $inputDir);
			$inputUrl = implode('/', $inputUrl);
		} else {
			$inputDir = $default['path'] . '/'.$usrDir;
			$inputUrl = $default['url'] . '/'.$usrDir;
		}
		if ( !wp_mkdir_p($inputDir) ) {
			$message = sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?'), $dir);
			return array('error' => $message);
		}
		$default['path'] = $inputDir;
		$default['url'] = $inputUrl;
	}
	return $default;
}

//
// 	WPU_BROWSE_ATTACHMENTS
//	------------------------------------
//	Adds a filter if we are browsing attachments if users have own blogs but don't have 'edit' permissions
//
function wpu_browse_attachments() {
	global $user_ID;
	$wpuConnSettings = get_settings('wputd_connection');
	if ( (!empty($wpuConnSettings['logins_integrated'])) && (!current_user_can('edit_post', (int) $ID)) ) {
		add_filter( 'posts_where', 'wpu_attachments_where' );
	}
}

//
// 	WPU_ATTACHMENTS_WHERE
//	------------------------------------
//	Filters attachments so they are for the current user only
//
function wpu_attachments_where($where) {
	global $user_ID;
	if (!empty($user_ID) ) {
		return $where . " AND post_author = '" . (int)$user_ID . "'";
	} else {
		die(__('You should not be here'));
	}
}

//
// 	WPU_FEED_LINK
//	------------------------------------
//	Returns an author's feed link on the main page if users can have own blogs.
//
function wpu_feed_link($link) {
	global $wpSettings;
	if ( !empty($wpSettings['usersOwnBlogs']) ) { //only works if not in admin.
		$authorID = wpu_get_author();
		if ( (!strstr($link, 'comment')) ) {
			$link = get_author_rss_link(FALSE, $authorID, '');
		} else {
		//	get author RSS link for comments	
		}
	}
	return $link;
}

//
//	WPU_MUST_INTEGRATE
//	------------------------
//	Redirects to the integrated page, in case WordPress has been accessed directly.
//	This will probably piss some people off -- but it's better than people accessing the wrong page and insisting it is screwed up.
//
function wpu_must_integrate() {
	if ( (!defined('WP_UNITED_ENTRY')) && (!is_admin()) ) {
		$wpuConnSettings = get_settings('wputd_connection');
		if ( $wpuConnSettings ) { //try to avoid infinitely redirecting loops
			wp_redirect(get_option('home'));
			exit();
		}
	}
}



function wpu_load_extra_files() {
	//But wait! There's more! We also include some tasty widgets, free of charge!
	if ( !defined('IN_PHPBB') ) {
		if(preg_match('|/wp-admin/|', $_SERVER['REQUEST_URI'])) { // try not to let things like the mandigo theme, which invoke wordpress just to generate some CSS, load in our widgets.
			$wpuConnSettings = get_settings('wputd_connection');
			include_once($wpuConnSettings['path_to_phpbb'] . 'wp-united/wpu-widgets.php');
			add_action('widgets_init', 'wpu_widgets_init');	
		}
	} else {
		add_action('widgets_init', 'wpu_widgets_init');	
	}

	
}

//
//	WPU_CLEAR_HEADER_CACHE
//	------------------------------------------
//	clears phpbb's cache of WP header/footer.
//	Called when the main WP theme is changed.
//
function wpu_clear_header_cache() {
	$wpuConnSettings = get_settings('wputd_connection');
	$cacheLoc = $wpuConnSettings['path_to_phpbb'] . 'wp-united/cache/';
	@$dir = opendir($cacheLoc);
	while( $entry = readdir($dir) ) {
		if ( strpos($entry, '.wpucache') ) {
			@unlink($cacheLoc . $entry);
		}
	}
}

//
//	WPU_ADD_POSTBOXES
//	----------------------------------
//	Add box to the write/(edit) post page.
//
function wpu_add_postboxes() {
	
	$wpuConnSettings = get_settings('wputd_connection');
	
	//Add the cross-posting box if enabled and the user has forums they can post to
	if ( $wpuConnSettings['wpu_enable_xpost'] && !empty($wpuConnSettings['logins_integrated']) ) {
		wpu_enter_phpbb();
		if ( !($already_xposted = wpu_get_xposted_details()) ) {
			$can_xpost_forumlist = wpu_forum_xpost_list();
		}
		wpu_exit_phpbb();
	// NOTE: TODO: 50: Changes below in Wintermute version yet to be included
		if ( (sizeof($can_xpost_forumlist)) || $already_xposted ) {
			?> 
			<fieldset id="wpuxpostdiv" class="dbx-box">
				<h3 class="dbx-handle"><?php _e('Cross-post to Forums?') ?></h3> 
				<div class="dbx-content">
					<?php if ($already_xposted) echo '<strong><small>' . sprintf(__('Already cross-posted (Post ID = %s)'), $already_xposted['post_id']) . "</small></strong><br /> <input type=\"hidden\" name=\"wpu_already_xposted_post\" value=\"{$already_xposted['post_id']}\" /><input type=\"hidden\" name=\"wpu_already_xposted_forum\" value=\"{$already_xposted['forum_id']}\" />"; ?>
					<label for="wpu_chkxpost" class="selectit">
						<input type="checkbox" <?php if ($already_xposted) echo 'disabled="disabled" checked="checked"'; ?>name="chk_wpuxpost" id="wpu_chkxpost" value="1" />
						<?php _e('Cross-post to Forums?'); ?>
					</label><br />
					<label for="wpu_selxpost">Select Forum:<br />
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
								}
							?>
						</select>
					</label>
				</div>
			</fieldset>
			<?php		
		}
	}
}



//
//	WPU_ADMIN_INIT
//	---------------------------
//	This sets everything admin-y up
//
//

function wpu_admin_init( ) {

	global $wpu_done_head;
	$wpu_done_head = true;
	
	$wpuConnSettings = get_settings('wputd_connection');
	
	// style the header text!
	wpu_css();

	if ( (!empty($wpuConnSettings['logins_integrated'])) && (current_user_can('publish_posts')) ) {
		//Buffer the users page
		if(preg_match('|/wp-admin/users.php|', $_SERVER['REQUEST_URI'])) {
			ob_start('wpu_buffer_userspanel');
		}
	}	
	
	
	// 'Fix' the profile page
	if (!empty($wpuConnSettings['logins_integrated'])) {
		if(preg_match('|/wp-admin/profile.php|', $_SERVER['REQUEST_URI'])) {
			ob_start('wpu_buffer_profile');
		}
	}
	
	//Buffer the Categories box on the post page
	
	// FOR WP < 2.1, THIS SHOULD BE POST.PHP!!!!
	if(preg_match('|/wp-admin/post-new.php|', $_SERVER['REQUEST_URI'])) {
		if(!current_user_can('post_to_any_category')) {   //TODO.... IF USER CAN HAVE A USER BLOG, AND IS < EDITOR LEVEL!!!
			//ob_start('wpu_catlist_alter');  //<--- Commented for release! -- per-user cats not operational
		}
	}
}
	
	
function wpu_buffer_profile($output) {
	define('WPU_ALTER_PROFILE', TRUE);
	$pattern = '/<div class="wrap">.*?<div id="footer">/si';
	return preg_replace_callback($pattern, 'wpu_menuTopLevel', $output);
}	
	

//
//	WPU_BUFFER_USERSPANEL
//	--------------------------------
//	Removes edit links, etc. from users.php
//
function wpu_buffer_userspanel($panelContent) {

	$token = array(
		"/<td><a(.*)[^<>]>" . __('Edit') . "<\/a><\/td>/",
		'/' . __('User List by Role') . "<\/h2>/"
	);
	$replace = array(
		'',
		__('User List by Role') . "</h2>\n<p>" . __('NOTE: User profile information can be edited in phpBB') . "</p>\n"
	);
	$panelContent= preg_replace($token, $replace, $panelContent);
	return $panelContent;
}


//
//*****************************************************************
//
//		P E R  --  U S E R   C A T E G R O R I E S   (experimental, in progress)
//
//*****************************************************************
//


// this is called before a post or attachment is saved.
// The first time this is called, we can make sure they have entered their own category -- otherwise
// we create a base for them and insert a new category under there.
function wpu_cat_presave($catArray) {
	//$userCatId = wpu_get_or_create_usercat(FALSE);

}

//
//	CATEGORY LISTING ON THE WRITE POST PAGE [AFFECTS LISTING ONLY]
//

// Here we buffer the categories box on the post page.
function wpu_catlist_alter($output) {
	
	// for WP < 2.1, this was div id=categorychecklist....!
	$pattern = '/<p id="jaxcat">.*?<\/div>/si';
	return preg_replace_callback($pattern, 'wpu_cat_doctor', $output);
}

//Miaaaow
//show the categories the user can post to -- if none exist, then create some!!!!
function wpu_cat_doctor() {

	//For WP < 2.1, this should be div id=categorychecklist!!!
	//$output = '<div class="dbx-content">';
	$output .= "<p id=\"jaxcat\"></p>\n";
	$output .= "<ul id=\"categorychecklist\">\n";
	
	$userCatId = wpu_get_or_create_usercat(TRUE);

	//Generate the category list we really want
	$categories = get_nested_categories(0, $userCatId);
	$output .= wpu_gen_nested_cats($categories);
	$output .= "</ul></div>\n";
	return $output;

}

function wpu_get_or_create_usercat($addDefaultCat=TRUE) {
	if ( !($baseCatId = wpu_category_exists(__('User Categories'), 0)) ) { 
		$baseCatId = wp_insert_category(array(
			'cat_name' => __('User Categories'),
			'category_parent' => 0,
			'category_description' => __('User Categories'))
		);
	}
	
	global $user_ID;
	$currUser = get_userdata($user_ID);
	$addCat = FALSE;
	if ( !isset($currUser->wpu_my_cats) )  {
		$addCat = TRUE;;
	} else {
		if ( !wpu_cat_id_exists($currUser->wpu_my_cats, $baseCatId) ) {
			$addCat = TRUE;
		}
	}
	
	if ($addCat) {
		$catName = sanitize_title($currUser->user_login);
		$output .= $catName;
		$userCatId = wp_insert_category(array(
			'cat_name' => $catName,
			'category_parent' => $baseCatId,
			'category_description' => sprintf(__('%s\'s Personal Categories'), $currUser->user_nicename))
		);
		update_usermeta($user_ID, 'wpu_my_cats',  $userCatId);
	} else {
		$userCatId = $currUser->wpu_my_cats;
	}
	
	//We now have a user category. Check that there is at least one subcat... if not, create 'uncategorized'
	if ( (!return_categories_list($userCatId)) && ($addDefaultCat) ) {
		$uncatCat = wp_insert_category(array(
			'cat_name' => __('Uncategorized'),
			'category_parent' => $userCatId,
			'category_description' => __('Uncategorized'))
		);
	}
	return $userCatId;
}

function wpu_category_exists($title, $parent=0) {
	global $wpdb;
	return $wpdb->get_var("SELECT cat_ID FROM $wpdb->categories WHERE cat_name = '$title' AND category_parent = $parent");
}

function wpu_cat_id_exists($id, $parent=0) {
	global $wpdb;
	$results = $wpdb->get_results("SELECT cat_name FROM $wpdb->categories WHERE cat_ID = $id AND category_parent = $parent");
	if ( count($results) > 0 ) {
		return TRUE;
	} 
	return FALSE;
}

function wpu_gen_nested_cats($categories) {
	foreach ( $categories as $category ) {
		$catList .= '<li id="category-' . $category['cat_ID'] . '"><label for="in-category-' . $category['cat_ID'] . '" class="selectit"><input value="' . $category['cat_ID'] . '" type="checkbox" name="post_category[]" id="in-category-' . $category['cat_ID'] . '"' . ($category['checked'] ? ' checked="checked"' : "" ) . '/> ' . wp_specialchars( $category['cat_name'] ) . "</label></li>";

		if ( $category['children'] ) {
			$catList .= "<ul>\n";
			$catList .= wpu_gen_nested_cats($category['children']);
			$catList .= "</ul>\n";
		}
	}
	return $catList;
}
// ???
//	CATEGORY CREATION ON WRITE POST PAGE
//	CATEGORY LISTING & CREATION ON MANAGE CATEGORIES PAGE
//	CATEGORY LISTING WHEN VIEWING POSTS




// Add hooks and filters
add_filter('template', 'wpu_get_template');
add_filter('stylesheet', 'wpu_get_stylesheet');
add_filter('loginout', 'wpu_loginoutlink');
add_filter('register', 'wpu_registerlink');
add_filter('option_blogname', 'wpu_blogname');
add_filter('option_blogdescription', 'wpu_blogdesc');
add_filter('option_home', 'wpu_homelink');
add_filter('the_content', 'wpu_content_parse_check' );
add_filter('the_title', 'wpu_censor');
add_filter('the_excerpt', 'wpu_censor');
add_filter('get_previous_post_where', 'wpu_prev_next_post');
add_filter('get_next_post_where', 'wpu_prev_next_post');
add_filter('upload_dir', 'wpu_user_upload_dir');
add_filter('feed_link', 'wpu_feed_link');

//per-user cats in progress
add_filter('wpu_cat_presave', 'category_save_pre');



add_action('edit_post', 'wpu_justediting');
add_action('wp_insert_post', 'wpu_newpost');
add_action('admin_menu', 'wpu_adminmenu_init');
add_action('admin_footer', 'wpu_put_powered_text');
add_action('admin_head', 'wpu_admin_init');
add_action('wp_head', 'wpu_done_head');
add_action('upload_files_browse', 'wpu_browse_attachments');
add_action('upload_files_browse-all', 'wpu_browse_attachments');
add_action('template_redirect', 'wpu_must_integrate');
add_action('plugins_loaded', 'wpu_load_extra_files');
add_action('switch_theme', 'wpu_clear_header_cache');
add_action('loop_start', 'wpu_loop_entry');

//Add additional boxes to write post page
// ADDED IN v0.6.1
// add_action('dbx_post_sidebar', 'wpu_add_postboxes');
       
    add_action('admin_menu', 'wpu_add_meta_box');
       function wpu_add_meta_box() {
          add_meta_box('postWPUstatusdiv', __('Cross-post to Forums?', 'wpu-cross-post'), 'wpu_add_postboxes', 'post', 'side');
       }

//Todo: move somewhere better!
global $siteurl;
$siteurl = get_option('siteurl');


?>
