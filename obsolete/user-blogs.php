<?php
/**
 *	This is the dumping ground for per-user blog code that was mixed in with wp-united.php
 *  it does not work and will do nothing, but is combined here for posterity / future use.
 *  It must not be placed on live servers.
*/

/**
 * Shows the "Your blog settings" menu
 * 
*/
function wpu_menuSettings() { 
	global $user_ID, $wp_roles, $phpbbForum, $phpEx;
	$profileuser = get_user_to_edit($user_ID);
	$bookmarklet_height= 440;
	$page_output = '';

	if ( isset($_GET['updated']) ):  ?>
		<div id="message" class="updated fade">
		<p><strong>  <?php _e('Settings updated.'); ?> </strong></p>
		</div>
	<?php endif; ?>
	
	<div class="wrap" id="profile-page">
	<?php screen_icon('profile'); ?>
	<h2> <?php echo $phpbbForum->lang['wpu_blog_details']?> </h2>
	<form name="profile" id="your-profile" action="admin.php?noheader=true&amp;page=wp-united&amp;wpu_action=update-blog-profile" method="post">
	<?php wp_nonce_field('update-blog-profile_' . $user_ID); 	?>
	<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" />
	<?php if ( $ref = wp_get_original_referer() ): ?>
		<input type="hidden" name="_wp_original_http_referer" value="<?php echo esc_attr(stripslashes($ref)); ?>" />
	<?php endif; ?>
		<input type="hidden" name="checkuser_id" value="<?php echo $user_ID; ?>" />
	</p>	

	
	<h3><?php echo $phpbbForum->lang['wpu_blog_about']; ?></h3>
		<input type="hidden" name="email" value="<?php echo $profileuser->user_email; ?>" />
		<?php /* Retrieve blog options */
		$blog_title = get_user_meta($user_ID, 'blog_title', true);
		$blog_tagline = get_user_meta($user_ID, 'blog_tagline', true); ?>
		<table class="form-table">
			<tr>
				<th><label><?php echo $phpbbForum->lang['wpu_blog_about_title']; ?></label></th>
				<td><input type="text" name="blog_title" value="<?php echo $blog_title; ?>" /></td>
			</tr>
			<tr>
				<th><label><?php echo $phpbbForum->lang['wpu_blog_about_tagline']; ?></label></th>
				<td><input type="text" name="blog_tagline" value="<?php echo $blog_tagline ; ?>" /> <span class="description"><?php _e('In a few words, explain what this blog is about.'); ?></span></td>
			</tr>			
		</table>

	<p class="submit">
		<input type="submit" class="button-primary" value="<?php  echo $phpbbForum->lang['wpu_update_blog_details'] ?>" name="submit" />
	</p>
	</form>
		
	</div>
	<?php

 
}

/**
 * If Style switching is allowed, displays the author theme switching menu
 *	Modelled on WP's existing themes.php
 */
function wp_united_display_theme_menu() {

	global $user_ID, $title, $parent_file, $wp_version, $phpEx, $phpbbForum;
	
	if ( ! validate_current_theme() ) { ?>
	<div id="message1" class="updated fade"><p><?php echo $phpbbForum->lang['wpu_theme_broken']; ?></p></div>
	<?php } elseif ( isset($_GET['activated']) ) { ?>
	<div id="message2" class="updated fade"><p><?php echo sprintf($phpbbForum->lang['wpu_theme_activated'], '<a href="' . wpu_homelink('wpu-activate-theme') . '/">', '</a>'); ?></p></div>
	<?php }
	
	$themes = get_themes();

	$theme_names = array_keys($themes);
	$user_theme = 'WordPress Default';

	$user_template = get_user_meta($user_ID, 'WPU_MyTemplate', true); 
	$user_stylesheet = get_user_meta($user_ID, 'WPU_MyStylesheet', true);

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
	$theme_root = $themes[$theme_name]['Theme Root'];
	$theme_root_uri = $themes[$theme_name]['Theme Root URI'];	
	$tags = $themes[$user_theme]['Tags'];	

	
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

	$pageTitle = $phpbbForum->lang['wpu_blog_your_theme']; ?>
	
	<div class="wrap">
		<?php screen_icon('themes'); ?>
		<h2><?php echo wp_specialchars( $pageTitle ); ?></h2>
	<?php /* CURRENT THEME */ ?>
		<h3><?php _e('Current Theme'); ?></h3>
		<div id="current-theme">
			<?php if ( $screenshot ) : ?>

			<img src="<?php echo $theme_root_uri  . '/' . $stylesheet . '/' . $screenshot; ?>" alt="<?php _e('Current theme preview'); ?>" />
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
					$theme_root = $themes[$theme_name]['Theme Root'];
					$theme_root_uri = $themes[$theme_name]['Theme Root URI'];							
					$preview_link = clean_url( get_option('home') . '/');
					$preview_link = htmlspecialchars( add_query_arg( array('preview' => 1, 'template' => $template, 'stylesheet' => $stylesheet, 'TB_iframe' => 'true', 'width' => 600, 'height' => 400 ), $preview_link ) );
					$preview_text = esc_attr( sprintf( __('Preview of "%s"'), $title ) );
					$tags = $themes[$theme_name]['Tags'];
					$thickbox_class = 'thickbox';
					$activate_link = wp_nonce_url('admin.php?page=wp-united-theme-menu&amp;noheader=true&amp;wpu_action=activate&amp;template=' . $template . '&amp;stylesheet=' . $stylesheet, 'wp-united-switch-theme_' . $template);
					$activate_text = esc_attr( sprintf( __('Activate "%s"'), $title ) );
					?>
					<?php if ( $screenshot ) { ?>
						<a href="<?php echo $preview_link; ?>" title="<?php echo $preview_text; ?>" class="<?php echo $thickbox_class; ?> screenshot">
							<img src="<?php echo $theme_root_uri  . '/' . $stylesheet . '/' . $screenshot; ?>" alt="" />
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

	<h2><?php echo $phpbbForum->lang['wpu_more_themes_head']; ?></h2>
	<p><?php echo $phpbbForum->lang['wpu_more_themes_get']; ?></p>

	</div>

<?php
}



/**
 * If Style switching is allowed, returns the template for the current author's blog
 * We could do all this much later, in the template loader, but it is safer here so we load in all template-specific widgets, etc.
 */
 add_filter('template', 'wpu_get_template');
function wpu_get_template($default) {
	global $wpUnited;
	if ( $wpUnited->get_setting('allowStyleSwitch') ) {
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
			$wpu_templatedir = get_user_meta($authorID, 'WPU_MyTemplate', true);
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
 add_filter('stylesheet', 'wpu_get_stylesheet');
function wpu_get_stylesheet($default) {
	global $wp_query, $wpUnited;
	if ( $wpUnited->get_setting('allowStyleSwitch') ) {
		if ( $authorID = wpu_get_author() ) { 
			$wpu_stylesheetdir = get_user_meta($authorID, 'WPU_MyStylesheet', true);
			$wpu_theme_path = get_theme_root() . "/$wpu_stylesheetdir";
			if ( (file_exists($wpu_theme_path)) && (!empty($wpu_stylesheetdir)) )	{
				return $wpu_stylesheetdir;
			}
		}
	} 
	return $default;
}


/**
 * Returns the name of the current user's blog
 */
 add_filter('option_blogname', 'wpu_blogname');
function wpu_blogname($default) {
	global $wpUnited, $user_ID, $phpbbForum, $adminSetOnce;
	if ( $wpUnited->get_setting('usersOwnBlogs') || (is_admin() && !$adminSetOnce) )   {
		$authorID = wpu_get_author();
		if ($authorID === FALSE) {
			if ( is_admin() ) {
				$authorID = $user_ID;
				$adminSetOnce = 1; //only set once, for title
			}
		}	
		if ( !empty($authorID) ) {
			$blog_title = get_user_meta($authorID, 'blog_title', true);
			if ( empty($blog_title) ) {
				if ( !is_admin() ) {
					$blog_title = $phpbbForum->lang['default_blogname']; 
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
 add_filter('option_blogdescription', 'wpu_blogdesc');
function wpu_blogdesc($default) {
	global $wpUnited, $phpbbForum;
	if ( $wpUnited->get_setting('usersOwnBlogs') ) {
		$authorID = wpu_get_author();
		if ( !empty($authorID) ) {
			$blog_tagline = get_user_meta($authorID, 'blog_tagline', true);
			if ( empty($blog_tagline) ) {
				$blog_tagline = $phpbbForum->lang['default_blogdesc'];
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
 add_filter('option_home', 'wpu_homelink');
function wpu_homelink($default) {
	global $wpUnited, $user_ID, $wpu_done_head, $altered_link;
	if ( ($wpu_done_head && !$altered_link) || ($default=="wpu-activate-theme")  ) {

		if ( $wpUnited->get_setting('usersOwnBlogs') ) {

			$altered_link = TRUE; // prevents this from becoming recursive -- we only want to do it once anyway

			if ( !is_admin() ) {
				$authorID = wpu_get_author();
			} else {
				$authorID = $user_ID;
			}
			if ( !empty($authorID) ) { 
				if(count_user_posts($authorID)) { // only change URL if author has posts
					$blog_url = get_author_posts_url($authorID); 
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
 * Add a marker -- this is the last chance we have to prevent the home link from being changed
 */
add_action('loop_start', 'wpu_loop_entry'); 
function wpu_loop_entry() {
	$GLOBALS['altered_link'] = TRUE;
}


/**
 * Alters the where clause of the sql for previous/Next post lookup, to ensure we stay on the same author blog
 */
add_filter('get_previous_post_where', 'wpu_prev_next_post');
add_filter('get_next_post_where', 'wpu_prev_next_post');
function wpu_prev_next_post($where) {
	global $wpUnited, $post;
	$author = $post->post_author;
	
	if ( $wpUnited->get_setting('usersOwnBlogs') ) {
		$where = str_replace("AND post_type = 'post'", "AND post_author = '$author' AND post_type = 'post'", $where); 
	}	
	return $where;
}

/**
 * If users can have own blogs, uploads attachments to users' own directories.
 * i.e. uploads/username or uploads/username/yyyyy/mm
 * This prevents users from browsing other users' media
 */
add_filter('upload_dir', 'wpu_user_upload_dir');
function wpu_user_upload_dir($default) {
	global $wpUnited, $phpbbForum;

	if ( $wpUnited->get_setting('integratelogin') ) {
		global $user_ID, $phpbbForum;
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
			$message = sprintf($phpbbForum->lang['wpu_user_media_dir_error'], $dir);
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
add_action('upload_files_browse', 'wpu_browse_attachments');
add_action('upload_files_browse-all', 'wpu_browse_attachments');
function wpu_browse_attachments() {
	global $user_ID, $wpUnited;

	if ( ($wpUnited->get_setting('integrateLogin')) && (!current_user_can('edit_post', (int) $ID)) ) {
		add_filter( 'posts_where', 'wpu_attachments_where' );
	}
}

/**
 * Filters attachments (media) so they are for the current user only
 */
function wpu_attachments_where($where) {
	global $user_ID, $phpbbForum;
	if (!empty($user_ID) ) {
		return $where . " AND post_author = '" . (int)$user_ID . "'";
	} else {
		die($phpbbForum->lang['wpu_access_error']);
	}
}

/**
 * Returns an author's feed link on the main page if users can have own blogs.
 */
add_filter('feed_link', 'wpu_feed_link');
function wpu_feed_link($link) {
	global $wpUnited;
	if ( $wpUnited->get_setting('usersOwnBlogs') ) { 
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
 * Add script to our user blog theme selection page
 */
function wpu_prepare_admin_pages() {
	if ( isset($_GET['page']) ) {
		if ($_GET['page'] == 'wp-united-theme-menu') {
			add_thickbox();
			wp_enqueue_script( 'theme-preview' );
			
		}
	}
}



if ( isset($_GET['page']) ) {
	if ($_GET['page'] == 'wp-united-theme-menu') {
		add_action('admin_init', 'wpu_prepare_admin_pages');
	}
}


// OLDER VERSIONS OF FUNCTIONS THAT INCLUDED USERBLOG FEATURES:


/**
 * Sets wpu_done_head to true, so we can alter things like the home link without worrying.
 * (before the <HEAD>, we don't want to modify any links)
 * We also add the blog homepage stylesheet here, and add the head marker for 
 * template integration when WordPress CSS is first.
*/
function wpu_done_head() {
	global $wpu_done_head, $wpUnited, $wp_the_query;
	$wpu_done_head = true; 
	//add the frontpage stylesheet, if needed: 
	if ( ($wpUnited->get_setting('blUseCSS')) && ($wpUnited->get_setting('useBlogHome')) ) {
		echo '<link rel="stylesheet" href="' . $wpUnited->get_plugin_url() . 'theme/wpu-blogs-homepage.css" type="text/css" media="screen" />';
	}
	if ( ($wpUnited->should_do_action('template-p-in-w')) && (!PHPBB_CSS_FIRST) ) {
		echo '<!--[**HEAD_MARKER**]-->';
	}
	
	
}

/**
 * Called whenever a post is edited
 * Prevents an edited post from showing up on the blogs homepage
 * And allows us to differentiate between edits and new posts for cross-posting
 */
function wpu_justediting() {
	define('suppress_newpost_action', TRUE);
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
