<?php
/*
Plugin Name: WP-United Connection 
Plugin URI: http://www.wp-united.com
Description: This is the "WP-United Connection" -- it handles the connection with phpBB fro mthe WordPress side.
Author: John Wells
Version: 0.9.5-Beta (phpBB2) / v0.7.1 Beta (phpBB3)
Last Updated: 18 May 2009
Author URI: http://www.wp-united.com

NOTE: This is a WordPress plugin, NOT a phpBB file and so it does not follow phpBB mod conventions. Specifically:
	- different hacking attempt check
	- different templating system
	- WordPress hard-codes php extensions, so so do we

DO NOT MODIFY THE BELOW LINE:
||WPU-PLUGIN-VERSION=701||
*/ 
/** 
*
* @package WP-United Connection Plugin
* @version $Id: wp-united.php,v0.8.0 2009/12/20 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* @author John Wells
*/

// this file will also be called in wp admin panel, when phpBB is not loaded. ABSPATH should *always* be set though!
if ( !defined('ABSPATH') ) {
	exit;
}

/**
 * Initialise WP-United
 */
function wpu_init_plugin() {
	global $phpbb_root_path, $phpEx, $phpbbForum;
	
	$wpuConnSettings = get_settings('wputd_connection');
	
	if ( !defined('IN_PHPBB') ) {
		$phpbb_root_path = $wpuConnSettings['path_to_phpbb'];
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
	}
	
	require_once($phpbb_root_path . 'wp-united/phpbb.'.$phpEx);
	$phpbbForum = new WPU_Phpbb();
	
	if ( !defined('IN_PHPBB') ) {
		$phpbbForum->load($phpbb_root_path);
		if(is_admin()) { // try not to let things like the mandigo theme, which invoke wordpress just to generate some CSS, load in our widgets.
			include_once($phpbb_root_path . 'wp-united/widgets.' .$phpEx);
			include_once($phpbb_root_path . 'wp-united/template-tags.' .$phpEx);
			add_action('widgets_init', 'wpu_widgets_init');	
		}
	} else {
		add_action('widgets_init', 'wpu_widgets_init');	
	}
	
}

/**
 * Checks and processes the inbound dashboard request
 * 
 * @todo Perhaps add additional perms check to see if we are allowed to switch theme/blog settings
 */
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


/**
 * Adds the WP-United copyright statement in all dashboards
 * Please DO NOT remove this!
 */
function wpu_put_powered_text() {
	global $wp_version;
	echo '<p  id="poweredby"> phpbb integration &copy; 2006-2009 <a href="http://www.wp-united.com" target="_blank">WP-United</a></p>';
	$wpuConnSettings = get_settings('wputd_connection');
	if ( current_user_can('publish_posts') ) {	
		if ( $wpuConnSettings['blogs'] ) {
			if($wp_version < 2.5) {
				echo '<p  id="welcome1">' . __('Welcome to WP-United. Click <strong>Write</strong> to add to your blog');
			} else {
				echo '<p  id="welcome1">' . __('Welcome to WP-United. Click <strong>Posts</strong> &rarr; <strong>Add New</strong> to add to your blog');
			}
			if ( $wpuConnSettings['styles'] ) {	
				echo '<br />' . __('You can set how it looks under the <strong>Your Blog</strong> tab!');
			}
			echo '</p>';
		} else {
			echo '<p  id="welcome1">' . __('Welcome to WP-United!');
		}
	}
}


/**
 * Sets the CSS styles of messages we put in the dashboard
 * Hides the messages we don't want using CSS
 */
function wpu_css() {
	global $wp_version;
	$wpuConnSettings = get_settings('wputd_connection');
	$top = ( $wpuConnSettings['blogs'] && $wpuConnSettings['styles'] ) ? "0.3" : "1";
		
	if ($wp_version >= 2.5) {
		echo '
			<style type="text/css">
			#user_info {
				display: none !important;
			}
			#poweredby {
				text-align: center;
				font-style: italic;
				font-size: 12px;
				font-family: Georgia, "Times New Roman", "Bitstream Charter", Times, serif;
				background-color: #464646; 
				margin-top: 0; 
				padding: 0 0 8px 0; 
				color: #999;"
			}
			#poweredby a {
				color: #ccc; 
				text-decoration: none;
			}
			#welcome1 {
				position: relative; /* ie6 bug */
				position: absolute;
				top: ' . $top . 'em;
				margin: 4px 150px 0 0;
				padding: 0;
				right: 1em;
				color: #f1f1f1;
			}
			</style>
			';
	} else {
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
				top: ' . $top . 'em;
				margin: 0;
				padding: 0;
				right: 1em;
				color: #f1f1f1;
			}
			</style>
			';	
	
	}
	


}

/**
 * Initialises the dashboard options
 * Inserts the admin pages we want, and directs to the admin page requested
 * @todo neaten wp 2.7/2.8+
 */
function wpu_adminmenu_init() {

	$wpuConnSettings = get_settings('wputd_connection');
	
	//Check for action
	if ( isset($_GET['wpu_action']) ) {
		wpu_check_for_action();
		exit();
	}
	global $menu, $wp_version;

	$fullFilePath = $wpuConnSettings['path_to_plugin'];
	
	if (!empty($wpuConnSettings['logins_integrated'])) {
		if (function_exists('add_submenu_page')) {
			if (current_user_can('publish_posts'))  {
				if($wp_version < 2.7) {
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
				} else {
				//	WP 2.7 ADMIN PANEL PAGE FOR OWN BLOGS
			
					if ( !empty($wpuConnSettings['blogs']) ) {
						$top = add_menu_page(__('Your Blog'), __('Your Blog'), 'publish_posts', 'wpu-plugin.php', 'wpu_menuTopLevel', $wpuConnSettings['path_to_phpbb'] . 'wp-united/images/tiny.gif' );
						
						add_submenu_page('wpu-plugin.php', __('Your Blog Setings'), __('Your Blog Settings'), 'publish_posts', 'wpu-plugin.php' , 'wpu_menuTopLevel');						
						if ( !empty($wpuConnSettings['styles']) ) {
							add_submenu_page('wpu-plugin.php', __('Set Blog Theme'), __('Set Blog Theme'), 'publish_posts','wpu-plugin.php&wputab=themes', 'wpu_menuTopLevel');
						}
					} 
		
				//
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
 
 
/**
 * Displays the top-leel menu for WP-United, "Your Blog".
 */
function wpu_menuTopLevel() {
	if ( isset($_GET['wputab']) ) {
		$tab = ($_GET['wputab'] == 'themes') ? 'THEMES' : 'SETTINGS';
	}
	if ( 'THEMES' != $tab) { 
		wpu_menuSettings();
	} else { 
		wp_united_display_theme_menu();
	}
}

/**
 * Shows the "Your blog settings" menu
 * 
 */
function wpu_menuSettings() { 
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
	if ( !empty($wpuConnSettings['blogs']) ) {
		$pageTitle .= __('Your Blog Details');
	} else {
		$pageTitle .= __('Your Profile');
	}
	$page_output .= '<div class="wrap">';
	echo $page_output;
	screen_icon();
	$page_output = '<h2>' . wp_specialchars($pageTitle) . '</h2>';

	$page_output .= '<form name="profile" id="your-profile" action="admin.php?noheader=true&amp;page=' . $wpuConnSettings['full_path_to_plugin'] . '&amp;wpu_action=update-blog-profile" method="post">' . "\n";
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
	$richEditing = (get_user_option('rich_editing')) ? "checked='checked'" : "";
	$page_output .= '<br clear="all" />	
	<h3>' . __('Personal Options') . '</h3>
	<p><label for="rich_editing"><input name="rich_editing" type="checkbox" id="rich_editing" value="true" ' . $richEditing .' />' .
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

}

/**
 * If Style switching is allowed, displays the author theme switching menu
 *	Modelled on WP's existing themes.php
 */
function wp_united_display_theme_menu() {

	global $user_ID, $title, $parent_file, $wp_version;
	$wpuConnSettings = get_settings('wputd_connection');
	
	if ( ! validate_current_theme() ) { ?>
	<div id="message1" class="updated fade"><p><?php _e('The active theme is broken.  Reverting to the default theme.'); ?></p></div>
	<?php } elseif ( isset($_GET['activated']) ) { ?>
	<div id="message2" class="updated fade"><p><?php printf(__('New theme activated. <a href="%s">View your blog &raquo;</a>'), wpu_homelink('wpu-activate-theme') . '/'); ?></p></div>
	<?php }
	

	$themes = get_themes();


	$theme_names = array_keys($themes);
	$user_theme = 'WordPress Default';

	$user_template = get_usermeta($user_ID, 'WPU_MyTemplate'); 
	$user_stylesheet = get_usermeta($user_ID, 'WPU_MyStylesheet');

	$site_theme = current_theme_info();

	$user_theme = $site_theme->title; // if user hasn't set a theme yet, it is the same as site default

	
	// get current user theme
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
	$tags = $themes[$user_theme]['Tags'];	

	if ($wp_version > 2.50) {
	
		// paginate if necessary
		ksort( $themes );
		$theme_total = count( $themes );
		$per_page = 15;

		if ( isset( $_GET['pagenum'] ) )
			$page = absint( $_GET['pagenum'] );

		if ( empty($page) )
			$page = 1;

		$start = $offset = ( $page - 1 ) * $per_page;

		$page_links = paginate_links( array(
			'base' => add_query_arg( 'pagenum', '%#%' ) . '#themenav',
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => ceil($theme_total / $per_page),
			'current' => $page
		));

		$themes = array_slice( $themes, $start, $per_page );
	
		$pageTitle = __('Set Your Blog Theme');
		$parent_file = 'wpu-plugin.php&wputab=themes'; ?>
		
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php echo wp_specialchars( $pageTitle ); ?></h2>
		<?php /* CURRENT THEME */ ?>
			<h3><?php _e('Current Theme'); ?></h3>
			<div id="current-theme">
				<?php if ( $screenshot ) : ?>
				<img src="<?php echo WP_CONTENT_URL . $stylesheet_dir . '/' . $screenshot; ?>" alt="<?php _e('Current theme preview'); ?>" />
				<?php endif; ?>
				<h4><?php printf(_c('%1$s %2$s by %3$s|1: theme title, 2: theme version, 3: theme author'), $title, $version, $author) ; ?></h4>
				<p class="description"><?php echo $description; ?></p>
				<?php if ( $tags ) : ?>
					<p><?php _e('Tags:'); ?> <?php echo join(', ', $tags); ?></p>
				<?php endif; ?>
			</div>
			
			<div class="clear"></div>
			<h3><?php _e('Available Themes'); ?></h3>
			<div class="clear"></div>
			
			<?php /* PAGINATION */ ?>
			<?php if ( $page_links ) : ?>
			<div class="tablenav">
			<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
				number_format_i18n( $start + 1 ),
				number_format_i18n( min( $page * $per_page, $theme_total ) ),
				number_format_i18n( $theme_total ),
				$page_links
			); echo $page_links_text; ?></div>
			</div>
			<?php endif; ?>
		
		
			<?php /* OTHER THEMES */ ?>
			
			<?php if ( 1 < $theme_total ) { ?>
			<table id="availablethemes" cellspacing="0" cellpadding="0">
			<?php
			$style = '';

			$theme_names = array_keys($themes);
			natcasesort($theme_names);

			$rows = ceil(count($theme_names) / 3);
			for ( $row = 1; $row <= $rows; $row++ )
				for ( $col = 1; $col <= 3; $col++ )
					$table[$row][$col] = array_shift($theme_names);

			foreach ( $table as $row => $cols ) {
			?>
			<tr>
			<?php
			foreach ( $cols as $col => $theme_name ) {
				if ($theme_name != $user_theme) {
					$class = array('available-theme');
					if ( $row == 1 ) $class[] = 'top';
					if ( $col == 1 ) $class[] = 'left';
					if ( $row == $rows ) $class[] = 'bottom';
					if ( $col == 3 ) $class[] = 'right';?>
					<td class="<?php echo join(' ', $class); ?>">
					<?php if ( !empty($theme_name) ) {
						$template = $themes[$theme_name]['Template'];
						$stylesheet = $themes[$theme_name]['Stylesheet'];
						$title = $themes[$theme_name]['Title'];
						$version = $themes[$theme_name]['Version'];
						$description = $themes[$theme_name]['Description'];
						$author = $themes[$theme_name]['Author'];
						$screenshot = $themes[$theme_name]['Screenshot'];
						$stylesheet_dir = $themes[$theme_name]['Stylesheet Dir'];
						$preview_link = clean_url( get_option('home') . '/');
						$preview_link = htmlspecialchars( add_query_arg( array('preview' => 1, 'template' => $template, 'stylesheet' => $stylesheet, 'TB_iframe' => 'true', 'width' => 600, 'height' => 400 ), $preview_link ) );
						$preview_text = attribute_escape( sprintf( __('Preview of "%s"'), $title ) );
						$tags = $themes[$theme_name]['Tags'];
						$thickbox_class = 'thickbox';
						$activate_link = wp_nonce_url('admin.php?page=wpu-plugin.php&amp;wputab=themes&amp;noheader=true&amp;wpu_action=activate&amp;template=' . $template . '&amp;stylesheet=' . $stylesheet, 'wp-united-switch-theme_' . $template);
						$activate_text = attribute_escape( sprintf( __('Activate "%s"'), $title ) );
						?>
						<?php if ( $screenshot ) { ?>
							<a href="<?php echo $preview_link; ?>" title="<?php echo $preview_text; ?>" class="<?php echo $thickbox_class; ?> screenshot">
								<img src="<?php echo WP_CONTENT_URL . $stylesheet_dir . '/' . $screenshot; ?>" alt="" />
							</a>
						<?php } ?>
						<h3><a class="<?php echo $thickbox_class; ?>" href="<?php echo $activate_link; ?>"><?php echo $title; ?></a></h3>
						<p><?php echo $description; ?></p>
						<?php if ( $tags ) { ?>
							<p><?php _e('Tags:'); ?> <?php echo join(', ', $tags); ?></p>
						<?php } ?>
						<span class="action-links"><a href="<?php echo $preview_link; ?>" class="<?php echo $thickbox_class; ?>" title="<?php echo $preview_text; ?>"><?php _e('Preview'); ?></a> <a href="<?php echo $activate_link; ?>" title="<?php echo $activate_text; ?>"><?php _e('Activate'); ?></a></span>
	
					<?php }
				} ?>
				</td>
			<?php } // end foreach $cols ?>
			</tr>
			<?php } // end foreach $table ?>
			</table>
		<?php } ?>

		<br class="clear" />

		<?php if ( $page_links ) { ?>
			<div class="tablenav">
			<?php echo "<div class='tablenav-pages'>$page_links_text</div>"; ?>
			<br class="clear" />
			</div>
		<?php } ?>
		<br class="clear" />
	</div>
			
			
<?php
		
		
		
	} else { // old WordPress (temporary -- to remove in WP-United v0.8)
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
	<?php }

}



/**
 * If Style switching is allowed, returns the template for the current author's blog
 * We could do all this much later, in the template loader, but it is safer here so we load in all template-specific widgets, etc.
 */
function wpu_get_template($default) {
	global $wpSettings;
	if ( !empty($wpSettings['allowStyleSwitch']) ) {
		//The first time this is called, wp_query, wp_rewrite, haven't been set up, so we can't see what kind of page it's gonna be
		// so set them up now
		if ( !defined('TEMPLATEPATH') && !isset($GLOBALS['wp_the_query']) ) {
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

/**
 * If Style switching is allowed, returns the stylesheet for the current author's blog
 * 
 */
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

/**
 * Filters the WordPress "Login/Out" link to point to phpBB installation
 * Only modifies it if login is integrated
 */
function wpu_loginoutlink($loginLink) {
	global $wpSettings, $phpbb_logged_in, $phpbb_username, $phpbb_sid, $wpuAbs, $phpEx, $scriptPath;
	if ( !empty($wpSettings['integrateLogin']) ) {
		$logout_link = 'ucp.'.$phpEx.'?mode=logout&amp;sid=' . $phpbb_sid;
		$login_link = 'ucp.'.$phpEx.'?mode=login&amp;sid=' . $phpbb_sid . '&amp;redirect=' . attribute_escape($_SERVER["REQUEST_URI"]);		
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

/**
 * Filters the WordPress "Register" link to point to phpBB installation
 * Only modifies it if login is integrated
 */
function wpu_registerlink($registerLink) {
	global $wpSettings, $phpbb_logged_in, $phpbb_sid, $wpuAbs, $phpEx, $wpuGetBlog, $scriptPath;
	if ( !empty($wpSettings['integrateLogin']) ) {
		//'before' and 'after' can be passed to the links. Let's isolate them.
		$startPos = strpos($registerLink, '<a');
		$endPos = strpos($registerLink, '</a>');
		$before = substr($registerLink, 0, $startPos);
		$after = substr($registerLink, $endPos + 4);
		/**
		 * @todo this could probably be done better
		 */
		$before = empty($before) ? '<li>' : $before;
		$after = empty($after) ? '</li>' : $after;
		$reg_link =  'ucp.'.$phpEx.'?mode=register';
		/**
		 * @todo move $wpuGetxxx calculation here
		 */
		if ( ! is_user_logged_in() ) {
			return $before . '<a href="' . append_sid(add_trailing_slash($scriptPath) . $reg_link) . '">' . $wpuAbs->lang('Register') . '</a>' . $after;
		} else {
			return $before . '<a href="' . get_settings('siteurl') . '/wp-admin/">' . $wpuGetBlog . '</a>' . $after;
		}
	} else { 
		return $registerLink;
	}	
}


/**
 * Called whenever a post is edited
 * Prevents an edited post from showing up on the blogs homepage
 * And allows us to differentiate between edits and new posts for crosws-posting
 */
function wpu_justediting() {
	define('suppress_newpost_action', TRUE);
}

/*
 * Called whenever a new post is published.
 * Updates the phpBB user table with the latest author ID, to facilitate direct linkage via blog buttons
 * Also handles cross-posting
 */
function wpu_newpost($post_ID, $post) {
	global $phpbbForum;
	
	$connSettings = get_settings('wputd_connection');
	global $user_ID, $wpdb, $wp_version;
	$did_xPost = false;
	if ( $post->post_status == 'publish' ) { 
		if (!defined('suppress_newpost_action')) { //This should only happen ONCE, when the post is initially created.
			update_usermeta($post->post_author, 'wpu_last_post', $post->post_author); 
		} 

		if ( (!defined('IN_PHPBB')) && (!empty($connSettings['logins_integrated'])) ) {
			global $db, $wpuAbs, $user, $phpEx; 
			
			$phpbbForum->enter();
			
			// Update blog link column
			if ( !empty($post->post_author) ) {
				$sql = 'UPDATE ' . USERS_TABLE . ' SET user_wpublog_id = ' . $post->post_author . " WHERE user_wpuint_id = '{$post->post_author}'";
				if (!$result = $db->sql_query($sql)) {
					$wpuAbs->err_msg(CRITICAL_ERROR, $wpuAbs->lang('WP_DBErr_Retrieve'), __LINE__, __FILE__, $sql);
				}
				$db->sql_freeresult($result);
			}
		//X-Posting
			// Cross-post to forums if necessary
			if ( isset($_POST['chk_wpuxpost']) && ($wpuAbs->user_logged_in()) && (!isset($_POST['wpu_already_xposted_post'])) ) {
				if ( ((int)$_POST['chk_wpuxpost'] ) && ($forum_id = (int)$_POST['sel_wpuxpost']) && $connSettings['wpu_enable_xpost'] ) { 
					$can_crosspost_list = wpu_forum_xpost_list();  
					//Check that we have the authority to cross-post there
					if ( in_array($forum_id, $can_crosspost_list['forum_id']) ) { 
						require_once($connSettings['path_to_phpbb'] . 'wp-united/functions-general.' . $phpEx);
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
							 $tag_list = get_the_term_list($post->ID, 'post_tag', '', ', ', '');
							 if ($tag_list == "") {
							    $tag_list = __('No tags defined.');
							 }
						}      						
						$phpbbForum->enter();
						$excerpt = sprintf($wpuAbs->lang('blog_post_intro'), '[url=' . get_permalink($post_ID) . ']', '[/url]') . "\n\n" . $excerpt . "\n\n" .
								'[b]' . $wpuAbs->lang('blog_post_tags') . '[/b]' . $tag_list . "\n" .
								'[b]' . $wpuAbs->lang('blog_post_cats') . '[/b]' . $cat_list . "\n" .
								sprintf($wpuAbs->lang('read_more'), '[url=' . get_permalink($post_ID) . ']', '[/url]');
								
						$excerpt = utf8_normalize_nfc($excerpt, '', true);
						$subject = utf8_normalize_nfc($subject, '', true);
						wpu_html_to_bbcode($excerpt, 0); //$uid=0, but will get removed)
						$uid = $poll = $bitfield = $options = ''; 
						generate_text_for_storage($excerpt, $uid, $bitfield, $options, true, true, true);
						 
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
						
						//Update the posts table with WP post ID so we can remain "in sync" with it.
						if ( !empty($data['post_id']) ) {
							$sql = 'UPDATE ' . POSTS_TABLE . ' SET post_wpu_xpost = ' . $post_ID . " WHERE post_id = {$data['post_id']}";
							if (!$result = $db->sql_query($sql)) {
								$wpuAbs->err_msg(CRITICAL_ERROR, $wpuAbs->lang('WP_DBErr_Retrieve'), __LINE__, __FILE__, $sql);
							}
							$db->sql_freeresult($result);
							
							$did_xPost = true;
						}
					} //end have authority to x-post
				}
			} //end isset & user_logged_in
			$phpbbForum->leave();

			if($did_xPost) { //Need to do this after we exit phpBB code
				$topic_url = str_replace($connSettings['path_to_phpbb'], "", $topic_url);
				$topic_url = $connSettings['phpbb_url'] . $topic_url;
			
			
				if (!empty($connSettings['autolink_xpost'])) {
				
			  		$thePost = array(
				  		'ID' 			=> 	$post_ID,
				  		'comment_status' 	=> 	'closed',
				  		'post_content'		=>	$post->post_content . "<br /><br /><a href=\"$topic_url\" title=\"" . __('Comments') . "\">" . __('Comment on this post in our forums') . "</a>"
			  		); 
			  		wp_update_post($thePost);
				}
			}

			define('suppress_newpost_action', TRUE);
		} //end ? logins integrated
		$phpbbForum->leave();
	} //post status: publish

}


/**
 * Get the list of forums we can cross-post to
 */
function wpu_forum_xpost_list() {
	global $wpuAbs, $user, $auth, $db, $userdata, $template, $phpEx;
	
	$can_xpost_forumlist = array();
	$can_xpost_to = array();
	
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
	
	return array();
}

/**
 * Determine if this post is already cross-posted.
 */
function wpu_get_xposted_details($postID = false) {
	if($postID === false) {
		if (isset($_GET['post'])) {
			$postID = (int)$_GET['post'];
		}
	}
	if(empty($postID)) {
		return false;
	}
	global $db;
	
	$sql = 'SELECT p.post_id, p.post_subject, p.forum_id, f.forum_name FROM ' . POSTS_TABLE . ' AS p, ' . FORUMS_TABLE . ' AS f WHERE ' .
		"p.post_wpu_xpost = $postID AND " .
		'f.forum_id = p.forum_id';
	if ($result = $db->sql_query_limit($sql, 1)) {
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if ( (!empty($row['forum_id'])) && (!empty($row['forum_name'])) ) {
			return $row;
		}
	}
	return false;
}
	
/**
 * Returns the name of the current user's blog
 */
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

/**
 * Returns the tagline of the current user's blog
 */
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

/**
 * Returns the URL of the current user's blog
 */
function wpu_homelink($default) {
	global $wpSettings, $user_ID, $wpu_done_head, $altered_link, $wp_version, $wputab_altered_link; 
	if ( ($wpu_done_head && !$altered_link) || ($default=="wpu-activate-theme")  ) {
		$wpuConnSettings = get_settings('wputd_connection');
		if ( !empty($wpuConnSettings['blogs']) ) {

			$altered_link = TRUE; // prevents this from becoming recursive -- we only want to do it once anyway

			if ( !is_admin() ) {
				$authorID = wpu_get_author();
			} else {
				$authorID = $user_ID;
			}
			if ( !empty($authorID) ) { 
				if(get_usernumposts($authorID)) { // only change URL if author has posts
					if ( ((float) $wp_version) >= 2.1 ) {
						//WP >= 2.1 branch
						$blog_url = get_author_posts_url($authorID); 
					} else {
						$blog_url = get_author_link(false, $authorID, ''); 
					}
					$blog_url = ( $blog_url[strlen($blog_url)-1] == "/" ) ? substr($blog_url, 0, -1) : $blog_url; //kill trailing slash
				}
				if ( empty($blog_url) ) {
					$blog_url = $default; 
				}
				return $blog_url;
			}
		}
	}
	return $default;
}




/**
 *  Figure out which author's blog this is. Caches the result.
 */
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


/**
 * Sets wpu_done_head to true, so we can alter things like the home link without worrying.
 * (before the <HEAD>, we don't want to modify any links)
 * We also add the blog homepage stylesheet here, and add the head marker for 
 * template integration when WordPress CSS is first.
*/
function wpu_done_head() {
	global $wpu_done_head, $wpSettings, $scriptPath, $wp_the_query;
	$wpu_done_head = true;
	//add the frontpage stylesheet, if needed: 
	if ( (!empty($wpSettings['blUseCSS'])) && (!empty($wpSettings['useBlogHome'])) ) {
		echo '<link rel="stylesheet" href="' . add_trailing_slash($scriptPath) . 'wp-united/theme/wpu-blogs-homepage.css" type="text/css" media="screen" />';
	}
	if ( (defined('WPU_REVERSE_INTEGRATION')) && ($wpSettings['cssFirst'] == 'W') ) {
		echo '<!--[**HEAD_MARKER**]-->';
	}
	
	
}

/**
 * Add a marker -- this is the last chance we have to prevent the home link from being changed
 */
function wpu_loop_entry() {
	$GLOBALS['altered_link'] = TRUE;
}

/**
 * Turns our page place holder into the blog list page, or the forum-in-a-full-page
 */
function wpu_content_parse_check($postContent) {
	if (! defined('PHPBB_CONTENT_ONLY') ) {
		if ( !(strpos($postContent, "<!--wp-united-home-->") === FALSE) ) {
			$postContent = get_wpu_blogs_home();
		} else {
			$postContent = wpu_censor($postContent);
		}
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

/**
 * Alters the where clause of the sql for previous/Next post lookup, to ensure we stay on the same author blog
 */
function wpu_prev_next_post($where) {
	global $wpSettings, $post;
	$author = $post->post_author;
	
	if ( !empty($wpSettings['usersOwnBlogs']) ) {
		$where = str_replace("AND post_type = 'post'", "AND post_author = '$author' AND post_type = 'post'", $where); 
	}	
	return $where;
}

/**
 * If users can have own blogs, uploads attachments to users' own directories.
 * i.e. uploads/username or uploads/username/yyyyy/mm
 * This prevents users from browsing other users' media
 */
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

/**
 * Adds a filter if we are browsing attachments if users have own blogs but don't have 'edit' permissions
 */
function wpu_browse_attachments() {
	global $user_ID;
	$wpuConnSettings = get_settings('wputd_connection');
	if ( (!empty($wpuConnSettings['logins_integrated'])) && (!current_user_can('edit_post', (int) $ID)) ) {
		add_filter( 'posts_where', 'wpu_attachments_where' );
	}
}

/**
 * Filters attachments (media) so they are for the current user only
 */
function wpu_attachments_where($where) {
	global $user_ID;
	if (!empty($user_ID) ) {
		return $where . " AND post_author = '" . (int)$user_ID . "'";
	} else {
		die(__('You should not be here'));
	}
}

/**
 * Returns an author's feed link on the main page if users can have own blogs.
 */
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

/**
 * Redirects to the integrated page, in case WordPress has been accessed directly.
 * This will probably piss some people off -- but it's better than people accessing the wrong page and insisting it is screwed up.
 */
function wpu_must_integrate() {
	if ( (!defined('WP_UNITED_ENTRY')) && (!is_admin()) ) {
		$wpuConnSettings = get_settings('wputd_connection');
		if ( $wpuConnSettings ) { //try to avoid infinitely redirecting loops
			wp_redirect(get_option('home'));
			exit();
		}
	}
}


/**
 * Clears phpbb's cache of WP header/footer.
 * We need to do this whenever the main WP theme is changed,
 * because when WordPress header/footer cache are called from phpBB, we have
 * no way of knowing what the theme should be a WordPress is not invoked
 */
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

/**
 * Add box to the write/(edit) post page.
 */
function wpu_add_postboxes() {
	global $wp_version, $can_xpost_forumlist, $already_xposted;
	$wpuConnSettings = get_settings('wputd_connection');
	if ( $wp_version >= 2.5 ) { ?> 
		<div id="wpuxpostdiv" class="inside">
		<?php //_e('Cross-post to Forums?'); ?>
	<?php } else { ?>
		<fieldset id="wpuxpostdiv" class="dbx-box">
		<h3 class="dbx-handle"><?php _e('Cross-post to Forums?') ?></h3> 
		<div class="dbx-content">
	<?php } ?>
		
	<?php if ($already_xposted) echo '<strong><small>' . sprintf(__('Already cross-posted (Post ID = %s)'), $already_xposted['post_id']) . "</small></strong><br /> <input type=\"hidden\" name=\"wpu_already_xposted_post\" value=\"{$already_xposted['post_id']}\" /><input type=\"hidden\" name=\"wpu_already_xposted_forum\" value=\"{$already_xposted['forum_id']}\" />"; ?>
	<label for="wpu_chkxpost" class="selectit">
		<input type="checkbox" <?php if ($already_xposted) echo 'disabled="disabled" checked="checked"'; ?>name="chk_wpuxpost" id="wpu_chkxpost" value="1" />
		<?php _e('Cross-post to Forums?'); ?><br />
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
	<?php if ( $wp_version < 2.5 ) echo "</fieldset>";

}

/**
 *  Here we decide whether to display the cross-posting box, and store the permissions list in global vars for future use.
 * For WP >= 2.5, we set the approproate callback function. For older WP, we can go directly to the func now.
 */
function wpu_add_meta_box() {
	global $phpbbForum, $wp_version, $can_xpost_forumlist, $already_xposted, $wpuAbs, $user, $auth;
	// this func is called early
	if ( (preg_match('|/wp-admin/post.php|', $_SERVER['REQUEST_URI'])) || (preg_match('|/wp-admin/post-new.php|', $_SERVER['REQUEST_URI'])) ) {
		if ( (!isset($_POST['action'])) && (($_POST['action'] != "post") || ($_POST['action'] != "editpost")) ) {
			$wpuConnSettings = get_settings('wputd_connection'); 
	
			//Add the cross-posting box if enabled and the user has forums they can post to
			if ( $wpuConnSettings['wpu_enable_xpost'] && !empty($wpuConnSettings['logins_integrated']) ) { 
				$phpbbForum->enter();
				if ( !($already_xposted = wpu_get_xposted_details()) ) { 
					$can_xpost_forumlist = wpu_forum_xpost_list(); 
				}
				$phpbbForum->leave();
				
				if ( (sizeof($can_xpost_forumlist)) || $already_xposted ) {
					if($wp_version >= 2.5) { 
						add_meta_box('postWPUstatusdiv', __('Cross-post to Forums?', 'wpu-cross-post'), 'wpu_add_postboxes', 'post', 'side');
					} else {
						wpu_add_postboxes();
					}
				}
			}
		}
	}
}


/**
 * This initialises all the admin changes and functions
 */
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
	
/**
 * Buffers the profile page, so it can be modified
 * @todo this is not curently being used, reinstate it
 */
function wpu_buffer_profile($output) {
	define('WPU_ALTER_PROFILE', TRUE);
	$pattern = '/<div class="wrap">.*?<div id="footer">/si';
	return preg_replace_callback($pattern, 'wpu_menuTopLevel', $output);
}	
	

/**
 * Removes edit links, etc. from users.php
 * @todo this is not currently being used, reinstate it
 */
function wpu_buffer_userspanel($panelContent) {

	$token = array("/<td><a(.*)[^<>]>" . __('Edit') . "<\/a><\/td>/", '/' . __('User List by Role') . "<\/h2>/");
	$replace = array('', __('User List by Role') . "</h2>\n<p>" . __('NOTE: User profile information can be edited in phpBB') . "</p>\n");
	$panelContent= preg_replace($token, $replace, $panelContent);
	return $panelContent;
}

/**
 * disable access to wp-login.php if logins are integrated
 */
function wpu_disable_wp_login() { 
	if (preg_match('|/wp-login.php|', $_SERVER['REQUEST_URI'])) {	
		$wpuConnSettings = get_settings('wputd_connection');
		if (!empty($wpuConnSettings['logins_integrated'])) {
			if ($wpuAbs->ver == 'PHPBB2') {
				$login = 'login.php?redirect=wp-united-blog';
			} else {
				$login = 'ucp.php';
			}
			// path back has one too many ../, so we just add on another path element
			wp_redirect("wp-includes/".$wpuConnSettings['path_to_phpbb'].$login);
		}
	}
}

/**
 * Add script to our user blog theme selection page
 */
function wpu_prepare_admin_pages() {
	if ( isset($_GET['wputab']) ) {
		if ($_GET['wputab'] == 'themes') {
			add_thickbox();
			wp_enqueue_script( 'theme-preview' );
			
		}
	}
}


/**
* Function 'get_avatar()' - Retrieve the phpBB avatar of a user
* @since WP-United 0.7.0
*/

function wpu_get_phpbb_avatar($avatar, $id_or_email, $size = '96', $default = '', $alt = 'avatar' ) { 
	$connSettings = get_settings('wputd_connection');
	if (empty($connSettings['logins_integrated'])) { 
		return $avatar;
	}

	if ( false === $alt)
		$safe_alt = '';
	else
		$safe_alt = esc_attr( $alt );

	if ( !is_numeric($size) )
		$size = '96';

	if ( !is_numeric($size) )
		$size = '96';
	// Figure out if this is an ID or e-mail --sourced from WP's pluggables.php
	$email = '';
	if ( is_numeric($id_or_email) ) {
		$id = (int) $id_or_email;
		$user = get_userdata($id);
		if ($user) $email = $user->user_email;
	} elseif ( is_object($id_or_email) ) {
		if ( !empty($id_or_email->user_id) ) {
			$id = (int) $id_or_email->user_id;
			$user = get_userdata($id);
			if ($user) $email = $user->user_email;
		} elseif ( !empty($id_or_email->comment_author_email) ) {
			$email = $id_or_email->comment_author_email;
		}
	} else {
		$email = $id_or_email;
   	}

	global $scriptPath; 
	$path = (empty($scriptPath)) ? $connSettings['path_to_phpbb'] : $scriptPath;	
		
	if($user) {
		// use default WordPress or WP-United image
		if(!$image = avatar_create_image($user)) { 
			if(empty($default)) {
				$image = $path . 'wp-united/images/wpu_unregistered.gif';
			} else {
				return $avatar;
			}
		} 
	} else {
	       $image = $path . 'wp-united/images/wpu_no_avatar.gif';
	}
	return "<img alt='{$safe_alt}' src='{$image}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
} 



/**
 * Function 'wpu_smilies' replaces the phpBB smilies' code with the corresponding smilies into comment text
 * @since WP-United 0.7.0
 */
function wpu_smilies($postContent, $max_smilies = 0) {
	// since this only takes place outside of WP-Admin, we can just check the global var $wpSettings
	global $phpbbForum, $wpSettings;
	
	if ( !empty($wpSettings['phpbbSmilies'] ) ) { 
		static $match;
		static $replace;
		global $scriptPath, $db;
	

		// See if the static arrays have already been filled on an earlier invocation
		if (!is_array($match)) {
		
			$phpbbForum->enter();
			
			$result = $db->sql_query('SELECT code, emotion, smiley_url FROM '.SMILIES_TABLE.' ORDER BY smiley_order', 3600);

			while ($row = $db->sql_fetchrow($result)) {
				if (empty($row['code'])) {
					continue; 
				} 
				$match[] = '(?<=^|[\n .])' . preg_quote($row['code'], '#') . '(?![^<>]*>)';
				$replace[] = '<!-- s' . $row['code'] . ' --><img src="' . $scriptPath . '/images/smilies/' . $row['smiley_url'] . '" alt="' . $row['code'] . '" title="' . $row['emotion'] . '" /><!-- s' . $row['code'] . ' -->';
			}
			$db->sql_freeresult($result);
			
			$phpbbForum->leave();
			
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
 * Function 'wpu_print_smilies' prints phpBB smilies into comment form
 * @since WP-United 0.7.0
*/
function wpu_print_smilies() {
	global $phpbbForum, $wpSettings;
	if ( !empty($wpSettings['phpbbSmilies'] ) ) {
		global $scriptPath, $db;

		$phpbbForum->enter();
	
		$result = $db->sql_query('SELECT code, emotion, smiley_url FROM '.SMILIES_TABLE.' GROUP BY emotion ORDER BY smiley_order ', 3600);

		$i = 0;
		while ($row = $db->sql_fetchrow($result)) {
			if (empty($row['code'])) {
				continue;
			}
			if ($i == 20) {
				echo '<span id="wpu-smiley-more" style="display:none">';
			}
		
			echo '<a href="#" onclick = "return insert_text(\''.$row['code'].'\')"><img src="'.$scriptPath.'/images/smilies/' . $row['smiley_url'] . '" alt="' . $row['code'] . '" title="' . $row['emotion'] . '" class="wpu_smile" /></a> ';
			$i++;
		}
		$db->sql_freeresult($result);
	
		$phpbbForum->leave();
	
	
		if($i >= 20) {
			echo '</span>';
			if($i>20) {
				echo '<a id="wpu-smiley-toggle" href="#" onclick="return moreSmilies();">' . __("More smilies") . '&nbsp;&raquo;</a></span>';
			}
		}
	}
}



/**
 * Function 'wpu_javascript' inserts the javascript code required by smilies' function.
 * @since WP-United 0.7.0
 */
function wpu_javascript () {
	global $wpSettings;
	if ( !empty($wpSettings['phpbbSmilies'] ) ) {

		echo "
<script language=\"javascript\">
	//<![CDATA[
	funct" . "ion insert_text(text, spaces, popup) {
		var tb = document.getElementById('comment');
		if (document.selection) { // IE
			tb.focus();
			sel = document.selection.createRange();
			sel.text = ' ' + text + ' ';
		} else if (tb.selectionStart || tb.selectionStart == 0) { //compliant browsers
			tb.value = tb.value.substring(0, tb.selectionStart) + ' ' + text + ' ' + tb.value.substring(tb.selectionEnd,tb.value.length);
		} else { //fallback
		 tb.value += ' ' + text + ' ';
		}
		return false;
	}

	funct" . "ion moreSmilies() {
		document.getElementById('wpu-smiley-more').style.display = 'inline';
		var toggle = document.getElementById('wpu-smiley-toggle');
		toggle.setAttribute(\"onclick\", \"return lessSmilies();\");
		toggle.firstChild.nodeValue =\"\\u00AB\\u00A0" . __("Less smilies") . "\"
		return false;
	}
    
	funct" . "ion lessSmilies() {
		document.getElementById('wpu-smiley-more').style.display = 'none';
		var toggle = document.getElementById('wpu-smiley-toggle');
		toggle.setAttribute(\"onclick\", \"return moreSmilies();\");
		toggle.firstChild.nodeValue =\"" . __("More smilies") . "\\u00A0\\u00BB\";
		return false;
	}
	// ]]>
</script>
";
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
	$connSettings = get_settings('wputd_connection');
	if (!empty($connSettings['logins_integrated'])) { 
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
 * Loads comments from phpBB rather than WordPress
 * if Xpost-autoloading is on
 * @since v0.8.0
 */
function wpu_load_phpbb_comments($commentArray, $postID) {
	global $phpbb_root_path, $comments, $wp_query, $overridden_cpage, $usePhpBBComments;
	
	$connSettings = get_settings('wputd_connection');
	
	if ( (empty($phpbb_root_path)) || (empty($connSettings['wpu_enable_xpost'])) ) {
		 //&& (!empty($connSettings['autolink_xpost']))
		return $commentArray;
	}
		
	require_once($phpbb_root_path . 'wp-united/comments.php');

	
	$phpBBComments = new WPU_Comments();
	if ( !$phpBBComments->populate($postID) ) {
		$usePhpBBComments = false;
		return $commentArray;
	}
	
	$usePhpBBComments = true;
	$comments = $phpBBComments->comments;
	
	$wp_query->comments = $comments;
	$wp_query->comment_count = sizeof($comments);
	$wp_query->rewind_comments();
	
	update_comment_cache($comments);
	
	
	$overridden_cpage = FALSE;
	if ( '' == get_query_var('cpage') && get_option('page_comments') ) {
		set_query_var( 'cpage', 'newest' == get_option('default_comments_page') ? get_comment_pages_count($comments) : 1 );
		$overridden_cpage = TRUE;
	}

	
	return $comments;
}

function wpu_comments_count($count, $postID) {
	global $wp_query, $usePhpBBComments;

	if ( !empty($usePhpBBComments) ) {
		return sizeof($wp_query->comments);
	}
	
	return $count;
}

function wpu_comment_redirector($postID) {
	global $phpbb_root_path, $phpEx, $phpbbForum, $wpuAbs;
	
	$connSettings = get_settings('wputd_connection');

	if( (empty($connSettings['logins_integrated'])) || (empty($connSettings['wpu_enable_xpost'])) ) {
		return false;
	}

	$phpbbForum->enter();	

	if(!$wpuAbs->user_logged_in()) {
		$phpbbForum->leave();
		return;
	}	

	if ( !$xPostDetails = wpu_get_xposted_details($postID) ) { 
		$phpbbForum->leave();
		return;
	}

	$permissionsList = wpu_forum_xpost_list();  
	if ( !in_array($xPostDetails['forum_id'], $permissionsList['forum_id']) ) { 
		$phpbbForum->leave();
		wp_die( __('You do not have permissions to comment in the forum'));
	}


	require_once($phpbb_root_path . 'wp-united/functions-general.' . $phpEx);
	
	$content = ( isset($_POST['comment']) ) ? trim($_POST['comment']) : null;
	
	wpu_html_to_bbcode($content, 0); //$uid=0, but will get removed)
	$uid = $poll = $bitfield = $options = ''; 
	generate_text_for_storage($content, $uid, $bitfield, $options, true, true, true);

	require_once($connSettings['path_to_phpbb'] . 'includes/functions_posting.' . $phpEx);
	
	$subject = $xPostDetails['post_subject'];
	
	$data = array(
		'forum_id' => $xPostDetails['forum_id'],
		'topic_id' => $xPostDetails['post_id'],
		'icon_id' => false,
		'enable_bbcode' => true,
		'enable_smilies' => true,
		'enable_urls' => true,
		'enable_sig' => true,
		'message' => $content,
		'message_md5' => md5($content),
		'bbcode_bitfield' => $bitfield,
		'bbcode_uid' => $uid,
		'post_edit_locked'	=> 0,
		'notify_set'		=> false,
		'notify'			=> false,
		'post_time' 		=> 0,
		'forum_name'		=> '',
		'enable_indexing'	=> true,
	); 
	$postUrl = submit_post('reply', $subject, $wpuAbs->phpbb_username(), POST_NORMAL, $poll, $data);
	
	
	$phpbbForum->leave();
	
	$location = empty($_POST['redirect_to']) ? get_comment_link($comment_id) : $_POST['redirect_to'] . '#comment-' . $comment_id;
	$location = apply_filters('comment_post_redirect', $location, $comment);
	wp_redirect($location);
}

/*
function wpu_buffer_comment_form($open, $postID) {
	global $usePhpBBComments;

	//echo (int)$GLOBALS['usePhpBBComments'] . "||";

	if ( !empty($usePhpBBComments) ) {
		return false;
	}
	
	return $open;
	
}
*/

/**
* Under consideration for future rewrite: Function 'wpu_validate_username_conflict()' - Handles the conflict between validate_username
* in WP & phpBB. This is only really a problem in integrated pages when naughty WordPress plugins pull in
* registration.php. 
* 
* These functions should NOT collide in usage -- only in namespace. If user integration is turned on, we don't need
* WP's validate_user. 
* 
* Furthermore, if phpbb_validate_username is defined, then we know we most likely need to use the phpBB version.
* 
* We unfortunately cannot control their usage -- phpbb expects 2 arguments, whereas WordPress only expects one.
* 
* Therefore here we just try to avoid namespace errors. If they are actually invoked while renamed, the result is undefined
*/
/*
function wpu_validate_username_conflict($wpValdUser, $username) {
	global $IN_WORDPRESS;
	$connSettings = get_settings('wputd_connection');
	if(function_exists('phpbb_validate_username')) { // we are probably expecting a phpBB response
		if (!empty($connSettings['logins_integrated']) || (!$IN_WORDPRESS) ) { 
			// We unfortunately can't get to the second phpBB argument
			return phpbb_validate_username($username, false);
		
		} 
	}
	return $wpValdUser;
}
*/

/**
 * here we add all the hooks and filters
 */

//since v0.7.1
add_filter('pre_user_login', 'wpu_fix_blank_username');
//add_filter('validate_username', 'wpu_validate_username_conflict');

//since v0.7.0
add_filter('get_comment_author_link', 'wpu_get_comment_author_link');
add_action('comment_author_link', 'wpu_comment_author_link');
add_filter('comment_text', 'wpu_censor');
add_filter('comment_text', 'wpu_smilies');
add_filter('get_avatar', 'wpu_get_phpbb_avatar', 10, 5);
add_action('comment_form', 'wpu_print_smilies');
add_action('wp_head', 'wpu_javascript');

if ( isset($_GET['wputab']) ) {
	if ($_GET['wputab'] == 'themes') {
		add_action('admin_init', 'wpu_prepare_admin_pages');
	}
}
if (preg_match('|/wp-admin/profile.php|', $_SERVER['REQUEST_URI'])) {
	add_action('init', 'wpu_disable_wp_login');
}

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

add_filter( 'comments_array', 'wpu_load_phpbb_comments', 10, 2);
add_filter( 'get_comments_number', 'wpu_comments_count', 10, 2);

//add_filter( 'comments_open', 'wpu_buffer_comment_form', 10, 2);
add_action( 'pre_comment_on_post', 'wpu_comment_redirector');


//per-user cats in progress
//add_filter('wpu_cat_presave', 'category_save_pre');



add_action('edit_post', 'wpu_justediting');
add_action('publish_post', 'wpu_newpost', 10, 2); //updated 
add_action('admin_menu', 'wpu_adminmenu_init');
add_action('admin_footer', 'wpu_put_powered_text');
add_action('admin_head', 'wpu_admin_init');
add_action('wp_head', 'wpu_done_head');
add_action('upload_files_browse', 'wpu_browse_attachments');
add_action('upload_files_browse-all', 'wpu_browse_attachments');
add_action('template_redirect', 'wpu_must_integrate');
add_action('plugins_loaded', 'wpu_init_plugin');
add_action('switch_theme', 'wpu_clear_header_cache');
add_action('loop_start', 'wpu_loop_entry'); 


if ( $wp_version >= 2.5 ) {       
	add_action('admin_menu', 'wpu_add_meta_box'); // <--- this is being called too early :-(
} else {
	add_action('dbx_post_sidebar', 'wpu_add_meta_box');
}

/**
 * @todo move $siteurl global declaration somewhere better and review usage
 */
global $siteurl;
$siteurl = get_option('siteurl');


?>
