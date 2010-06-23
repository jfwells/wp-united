<?php
/** 
*
* WP-United WordPress template tags
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
* 
* WordPress template functions -- see the readme included in the mod download for how to use these.
*
*/

/**
 */

if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) {
	exit;
}


/**
 * Displays a sentence soliciting users to get started with their blogs
 * @author John Wells
 */
function wpu_intro() {
 echo get_wpu_intro();
}
/**
 * Prepares a sentence soliciting users to get started with their blogs
 */
function get_wpu_intro() {
	global $wpSettings, $phpEx, $wpuGetBlogIntro, $phpbbForum;
	if ( (!empty($wpSettings['useBlogHome'])) && (!empty($wpSettings['usersOwnBlogs'])) ) {
		$reg_link =  'ucp.'.$phpEx.'?mode=register';
		$redir = wpu_get_redirect_link();
		$login_link = 'ucp.'.$phpEx.'?mode=login&amp;redirect='. $redir;	
		
		$isReg = $phpbbForum->get_userdata('is_registered');
		if ( !empty($isReg) ) {
			$wpuGetBlogIntro = ($phpbbForum->get_userdata('user_wpublog_id') > 0 ) ? $phpbbForum->lang['blog_intro_add'] : $phpbbForum->lang['blog_intro_get'];
		} else {
			$wpuGetBlogIntro =  ($wpSettings['usersOwnBlogs']) ? $phpbbForum->lang['blog_intro_loginreg_ownblogs'] : $phpbbForum->lang['blog_intro_loginreg'];
		}
		
		if ( ! $phpbbForum->user_logged_in() ) {
			$getStarted = '<p class="wpuintro">' . sprintf($wpuGetBlogIntro,'<a href="' . $phpbbForum->url . append_sid($reg_link) . '">', '</a>',  '<a href="'. $phpbbForum->url . $login_link . '">', '</a>') . '</p>';
		} else {
			$getStarted = '<p class="wpuintro">' . sprintf($wpuGetBlogIntro, '<a href="' . get_settings('siteurl') . '/wp-admin/">','</a>') . '</p>';
		}
		$intro = '<p>' . str_replace('{GET-STARTED}', $getStarted, $wpSettings['blogIntro']);
		return $intro;
	} 	
}


/**
 * Returns a nice paginated list of phpBB blogs
 * @param bool $showAvatars Show phpBB avatars? Defaults to true
 * @param int $maxEntries Maximum number to show per page. Defaults to 5.
 */
function get_wpu_bloglist($showAvatars = TRUE, $maxEntries = 5) {
	global $wpdb, $authordata, $phpbbForum, $wpSettings, $phpEx;
	$start = 0;
	$start = (integer)trim($_GET['start']);
	$start = ($start < 0) ? 0 : $start;
	//get total count
	$sql = "SELECT count(DISTINCT u.ID) AS total
			FROM {$wpdb->users} AS u 
			INNER JOIN {$wpdb->posts} AS p
			ON p.ID = p.post_author
			WHERE u.user_login <> 'admin' 
			AND p.post_type = 'post' 
			AND p.post_status = 'publish'
			";
	$count = $wpdb->get_results($sql);
	$numAuthors = $count[0]->total;
	
	$maxEntries = ($maxEntries < 1) ? 5 : $maxEntries;
	//pull the data we want to display -- this doesn't appear to be very efficient, but it is the same method as  the built-in WP function
	// wp_list_authors uses. Let's hope the data gets cached!
	$sql = "SELECT DISTINCT u.ID, u.user_login, u.user_nicename 
			FROM {$wpdb->users} AS u
			INNER JOIN {$wpdb->posts} AS p 
			ON u.ID=p.post_author 
			WHERE u.user_login<>'admin' 
			AND p.post_type = 'post' 
			AND p.post_status = 'publish'
			ORDER BY u.display_name LIMIT $start, $maxEntries";
	$authors= $wpdb->get_results($sql);

	if ( count($authors) > 0 ) {
		$d = get_settings('time_format');
		$time = mysql2date($d, $time);
		$itern = 1;
		$blogList = '';
		foreach ( (array) $authors as $author ) {
			$posts = 0;  $avatar = '';
			$blogTitle = ''; $blogDesc = ''; $blogPath = '';
			$path_to_profile = ''; $lastPostID = 0; $post = ''; 
			$lastPostTitle = ''; $lastPostURL = ''; $time = ''; $lastPostTime = '';

			$posts = get_usernumposts($author->ID);
			if ($posts) {
				$author = get_userdata( $author->ID );
				$pID = (int) $author->phpbb_userid;
				$name = $author->nickname;
				if ( $show_fullname && ($author->first_name != '' && $author->last_name != '') ) {
					$name = "{$author->first_name} {$author->last_name}";
				}
				$avatar = avatar_create_image($author); 
				$blogTitle = ( empty($author->blog_title) ) ? $phpbbForum->lang['default_blogname'] : wpu_censor($author->blog_title);
				$blogDesc = ( empty($author->blog_tagline) ) ? $phpbbForum->lang['default_blogdesc'] : wpu_censor($author->blog_tagline);
				$blogPath = get_author_posts_url($author->ID, $author->user_nicename);
				$wUsrName = sanitize_user($author->user_login, true);
				if ( ($wUsrName == $author->user_login) ) {
					$pUsrName = $author->user_login;
				} else {
					$pUsrName == $author->phpbb_userLogin;
				}
				$profile_path =  "memberlist.$phpEx";
				$path_to_profile = ( empty($pID) ) ? append_sid($blogPath) : append_sid($phpbbForum->url . $profile_path .'?mode=viewprofile&amp;u=' .$pID); 
				$rssLink = get_author_rss_link(0, $author->ID, $author->user_nicename);
				$lastPostID = $author->wpu_last_post;
				if ( empty($lastPostID) ) {
					global $wp_query, $post;
					$_oldQuery = $wp_query;
					$_oldPost = $post;
					$lastPost = new WP_Query();
					$lastPost->query('author=' . $author->ID . '&showposts=1&post_status=publish&orderby=date');
					$lastPost->the_post();
					$lastPostID = get_the_ID();
					update_usermeta($author->ID, 'wpu_last_post', $lastPostID);
					unset($wp_query); $wp_query = $_oldQuery;
					unset($GLOBALS['post']);
					$GLOBALS['post'] = $_oldPost;
				}
				$post = get_post($lastPostID);
				$lastPostTitle = wpu_censor($post->post_title); 
				$blogTitle = wpu_censor($blogTitle);
				$blogDesc = wpu_censor($blogDesc);

				$lastPostURL = get_permalink($lastPostID); 
				$time = $post->post_date;
				$lastPostTime = apply_filters('get_the_time', $time, $d, FALSE);
				$itern = ( $itern == 0 ) ? 1 : 0;
				$blogList .= "<div class=\"wpubl$itern\">\n\n";
				if ( !empty($avatar) ) {
					$blogList .=  "<img src=\"$avatar\" alt=\"avatar\"/>\n"; 
				}
				$blogList .=  sprintf($phpbbForum->lang['wpu_blog_intro'], "<h2 class=\"wpublsubject\" ><a href=\"$blogPath\">$blogTitle</a>", ' <a href="' . $path_to_profile . '">' . $name . "</a></h2>\n\n");
				$blogList .=  '<p class="wpubldesc">' . $blogDesc . "</p>\n\n";
				$blogList .=  '<small class="wpublnumposts">' .$phpbbForum->lang['wpu_total_entries'] . $posts . "</small><br />\n\n";
				$blogList .=  '<small class="wpublastpost">' . sprintf($phpbbForum->lang['wpu_last_entry'],  ' <a href="' . $lastPostURL . '">' . $lastPostTitle . '</a>',   $time) . "</small><br />\n\n";
				if ( !empty($rssLink) ) {
					$blogList .=  '<small class="wpublrss">' . $phpbbForum->lang['wpu_rss_feed'] . ' <a href="' . $rssLink . '">' . $phpbbForum->lang['wpu_rss_subscribe'] . "</a></small><br />\n\n";
				}
				$blogList .=  "<p class=\"wpublclr\">&nbsp;</p></div>\n\n";
			}
		}
	} else {
		$blogList .= "<div class=\"wpubl\">\n";
		$blogList .= '<p class="wpubldesc">' . $phpbbForum->lang['wpu_no_user_blogs'] . "</p>\n";
		$blogList .= "</div>\n";
	}
	if ( $numAuthors > $maxEntries ) { 
		$phpbbForum->enter();
		$base_url = append_sid(strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']);
		$pagination = generate_pagination($base_url, $numAuthors, $maxEntries, $start, TRUE);
		$phpbbForum->leave();
		$blogList .= '<p class="wpublpages">' . $pagination . '</p>';
	}

	return $blogList;
}

/**
 * Displays the blog list
 * @param bool $showAvatars Show phpBB avatars? Defaults to true
 * @param int $maxEntries Maximum number to show per page. Defaults to 5.
 * @author John Wells
 */
function wpu_bloglist($showAvatars = true, $maxEntries = 10) {
	echo wpu_bloglist($showAvatars, $maxEntries);
}

/**
 * Displays the blog list
 * Synonym of wpu_bloglist
 * @author John Wells
 */
function wpu_blogs_home() {
	echo get_wpu_blogs_home();
}

/**
 * Returns the blog listing without displaying it.
 * @author John Wells
 */
function get_wpu_blogs_home() {
	global $wpSettings;
	$postContent = get_wpu_intro(); 
	$postContent .= get_wpu_bloglist(true, $wpSettings['blogsPerPage']); 
	return $postContent;
}


/**
 * Inserts the commenter's avatar
 * @param bool $default Use default avatars if no avatar is present? Defaults to true
 * @param int $id User ID (optional)
 * @author John Wells
 */
function avatar_commenter($default = true, $id = '') {
	echo get_avatar_commenter($default, $id);
}

/** 
 * Returns the commenter avatar without displaying it.
 * @param bool $default Use default avatars if no avatar is present? Defaults to true
 * @param int $id User ID (optional)
 * @author John Wells
 */
function get_avatar_commenter($default=TRUE, $id = '') {
global $comment, $images, $phpbbForum;

	if ( empty($id) ) {
		if ( !empty($comment) ) {
			$id = $comment->user_id;
		} 
		if ( empty($id) ) {
			if ( $default ) {
				return $phpbbForum->url . 'wp-united/images/wpu_unregistered.gif';
			}
			return '';
		}
	}
	//Now we have ID
	$author = get_userdata($id);
	$image = avatar_create_image($author);
	if ( !empty($image) ) {
		return $image;
	} 
	if ( $default ) {
		return $phpbbForum->url . 'wp-united/images/wpu_no_avatar.gif';
	}
	return '';
}



/**
 * Inserts the author's avatar
 * @param bool $default Use default avatars if no avatar is present? Defaults to true
  * @author John Wells
 */
function avatar_poster($default = true) {
	echo get_avatar_poster($default);
}

/** 
 * Returns the author's avatar without displaying it.
 * @param bool $default Use default avatars if no avatar is present? Defaults to true
 * @author John Wells
 */
function get_avatar_poster($default = true) {
	global $images, $authordata, $phpbbForum;
	$image = avatar_create_image($authordata);
	if ( !empty($image) ) {
		return $image;
	} 
	if ( $default ) {
		return $phpbbForum->url . 'wp-united/images/wpu_no_avatar.gif';
	}
	return '';
}




/**
 * Inserts the reader's (logged in user's) avatar
 * @param bool $default Use default avatars if no avatar is present? Defaults to true
 * @author John Wells
 */
function avatar_reader($default = true) {
	echo get_avatar_reader($default);
}

/**
 * Returns the reader's avatar without displaying it
 * @param bool $default Use default avatars if no avatar is present? Defaults to true
 * @author John Wells
 */
function get_avatar_reader($default = true) {
	global $images, $phpbbForum, $userdata, $user_ID;
	get_currentuserinfo();
	if ( !empty($user_ID) ) {
		$image = avatar_create_image($userdata);
	}
	if ( !empty($image) ) {
		return $image;
	} elseif ( $image === FALSE ) {
		if ( $default ) {
			return $phpbbForum->url . 'wp-united/images/wpu_unregistered.gif';
		}
	}
	if ( $default ) {
		return $phpbbForum->url . 'wp-united/images/wpu_no_avatar.gif';
	}
	return '';
}



/**
 * Generates the avatar image
 * @author John Wells
 * @access private
 */
function avatar_create_image($user) {
	$avatar = '';
	if ( !empty($user->ID) ) {
		global $phpbbForum, $phpbb_root_path, $phpEx;
		
		if(isset($user->wpu_avatar)) {
			if ($user->wpu_avatar_type && $user->wpu_avatar) {
				require_once($phpbb_root_path . 'includes/functions_display.' . $phpEx); 
				$avatar = get_user_avatar($user->wpu_avatar, $user->wpu_avatar_type, $user->wpu_avatar_width, $user->wpu_avatar_height);
				$avatar = explode('"', $avatar);
				$avatar = str_replace($phpbb_root_path, $phpbbForum->url, $avatar[1]); //stops trailing slashes in URI from killing avatars
			}
		}

	} 
	return $avatar;
}


/**
 * Displays the latest updated user blogs
 * Based on a contribution by Quentin qsc AT mypozzie DOT co DOT za
 * @param string $args
 * @example wpu_latest_blogs('limit=20&before=<li>&after=</li>');
 */
function wpu_latest_blogs($args = '') {
	echo get_wpu_latest_blogs($args);
}

/**
 * Returns the latest updated user blogs without displaying them
 * Based on a contribution by Quentin qsc AT mypozzie DOT co DOT za
 * @param string $args
 * @example wpu_latest_blogs('limit=20&before=<li>&after=</li>');
 */
function get_wpu_latest_blogs($args = '') {
	global $phpbbForum, $wpdb;

	$defaults = array('limit' => '20','before' => '<li>', 'after' => '</li>');
	extract(_wpu_process_args($args, $defaults));

	if ( '' != $limit ) {
		$limit = (int) $limit;
		$limit_sql = ' LIMIT '.$limit;
	} 
	$orderby_sql = "post_date DESC ";
	
	$posts = $wpdb->get_results("SELECT DISTINCT post_author FROM $wpdb->posts 
		WHERE post_type = 'post' 
		AND post_author <> 1
		AND post_status = 'publish' 
		ORDER BY $orderby_sql 
		$limit_sql");
	if ( $posts ) {
		$blogLinks = ''; 
		foreach ( $posts as $post ) {
			$blogTitle = wpu_censor(strip_tags(get_usermeta($post->post_author, 'blog_title')));
			$blogTitle = ( $blogTitle == '' ) ? $phpbbForum->lang['default_blogname'] : $blogTitle;
			if ( function_exists('get_author_posts_url') ) {
				//WP >= 2.1 branch
				$blogPath = get_author_posts_url($post->post_author);
			} else {
				//deprecated branch
				$blogPath = get_author_link(false, $author->post_author); 
			}
			$blogLinks .= get_archives_link($blogPath, $blogTitle, '', $before, $after);
		}
		return $blogLinks;
	}
} 

/**
 * Displays the latest user blog posts, together with blog details
 * @example wpu_latest_blogs('limit=20&before=<li>&after=</li>');
 */
function wpu_latest_blogposts($args = '') {
	echo get_wpu_latest_blogposts($args);
}

/**
 * Returns the latest user blog posts, together with blog details
 * @example wpu_latest_blogs('limit=20&before=<li>&after=</li>');
 * @author John Wells
 */
function get_wpu_latest_blogposts($args = '') {
	global $phpbbForum, $wpdb;
	
	$defaults = array('limit' => '20','before' => '<li>', 'after' => '</li>');
	extract(_wpu_process_args($args, $defaults));

	if ( '' != $limit ) {
		$limit = (int) $limit;
		$limit_sql = ' LIMIT '.$limit;
	} 
	$orderby_sql = "post_date DESC ";
	
	$posts = $wpdb->get_results("SELECT ID, post_author, post_title FROM $wpdb->posts 
		WHERE post_type = 'post' 
		AND post_author <> 1
		AND post_status = 'publish' 
		ORDER BY $orderby_sql 
		$limit_sql");
	if ( $posts ) {
		$htmlOut = ''; 
		foreach ( $posts as $post ) {
			$lastPostTitle = wpu_censor(strip_tags($post->post_title));
			$blogTitle = wpu_censor(strip_tags(get_usermeta($post->post_author, 'blog_title')));
			$blogTitle = ( $blogTitle == '' ) ? $phpbbForum->lang['default_blogname'] : $blogTitle;
			$lastPostURL = get_permalink($post->ID); 
			if ( function_exists('get_author_posts_url') ) {
				//WP >= 2.1 branch
				$blogPath = get_author_posts_url($post->post_author);
			} else {
				//deprecated branch
				$blogPath = get_author_link(false, $author->post_author); 
			}
			$postLink = get_archives_link($lastPostURL, $lastPostTitle, '', $before, '');
			$blogLink = get_archives_link($blogPath, $blogTitle, '', '', $after);
			$htmlOut .= sprintf($phpbbForum->lang['wpu_latest_blogposts_format'], trim($postLink), $blogLink);
		}
		return $htmlOut;
	}
} 

/**
 * Displays the logged in user's phpBB username, or 'Guest' if they are logged out
 * @author John Wells
 */
function wpu_phpbb_username() {
	echo get_wpu_phpbb_username();
}

/**
 * Returns the phpBB username without displaying it
 * @author John Wells
 */
function get_wpu_phpbb_username() {
	global $phpbbForum;
	$usrName = '';
	if ( $phpbbForum->user_logged_in() ) {
		$usrName = $phpbbForum->get_username();
	} 
	return ($usrName == '') ? $phpbbForum->lang['GUEST'] : $usrName;
}

/**
 * Displays a link to the user's phpBB profile
 * @param int $wpID the WordPress ID, leave blank for currently logged-in user
 * @author John Wells
 */
function wpu_phpbb_profile_link($wpID = '') {
	echo get_wpu_phpbb_profile_link($wpID);
}

/**
 * Returns a link to the user's phpBB profile without displaying it
 * @param int $wpID the WordPress ID, leave blank for currently logged-in user
 */
function get_wpu_phpbb_profile_link($wpID = '') {
	global $phpbbForum, $user_ID, $phpEx;
	if(empty($wpID)) {
		get_currentuserinfo();
		$wpID = $user_ID;
	}
	$phpbb_usr_id = get_usermeta($wpID, 'phpbb_userid');
	if (!empty($phpbb_usr_id)) {
		$profile_path = "memberlist.$phpEx";
		return add_trailing_slash($phpbbForum->url) . "$profile_path?mode=viewprofile&amp;u=" . $phpbb_usr_id;
	}
	return false;
}

/**
 * Displays the user's phpBB rank
 * @param int $wpID the WordPress ID, leave blank for currently logged-in user
 */
function wpu_phpbb_ranktitle($wpID = '') {
	echo get_wpu_phpbb_ranktitle($wpID);
}

/**
 * Returns the user's phpBB rank without displaying it
 * @param int $wpID the WordPress ID, leave blank for currently logged-in user
 */
function get_wpu_phpbb_ranktitle($wpID = '') {
	$rank = _wpu_get_user_rank_info($wpID);
	if ( $rank ) {
		return $rank['text'];
	}
}

/**
 * Displays the user's phpBB rank image
 * @param int $wpID the WordPress ID, leave blank for currently logged-in user
 * @author John Wells
 */
function wpu_phpbb_rankimage($wpID = '') {
	echo get_wpu_phpbb_rankimage($wpID);
}

/**
 * Returns the user's phpBB rank image without displaying it
 * @param int $wpID the WordPress ID, leave blank for currently logged-in user
 * @author John Wells
 */
function get_wpu_phpbb_rankimage($wpID = '') {
	$rank = _wpu_get_user_rank_info($wpID);
	if ( $rank ) {
		return $rank['image'];
	}
}

/**
 * Displays a phpBB rank lockup with rank and image
 * @param int $wpID the WordPress ID, leave blank for currently logged-in user
 * @author John Wells
 */
function wpu_phpbb_rankblock($wpID = '') {
	echo get_wpu_phpbb_rankblock($wpID);
}

/**
 * Returns a phpBB rank lockup without displaying it
 * @param int $wpID the WordPress ID, leave blank for currently logged-in user
 * @author John Wells
 */
function get_wpu_phpbb_rankblock($wpID = '') {
	global $phpbbForum;
	$rank = _wpu_get_user_rank_info($wpID);
	if ( $rank ) {
		$block = '<p class="wpu_rank">' . $rank['text'];
		if ( $rank['image'] ) {
			$block .= '<br />' . '<img src="' . $rank['image'] . '" alt="' . $phpbbForum->lang['SORT_RANK'] . '" />';
		}
		$block .= '</p>';
		return $block;
	}
}



/**
 * Displays phpBB forum stats
 * @param string args
 * @example wpu_phpbb_stats('limit=20&before=<li>&after=</li>');
 * @author John Wells
 */
function wpu_phpbb_stats($args='') {
	echo get_wpu_phpbb_stats($args);
}

/**
 * Returns phpBB forum stats without displaying them
 * @param string args
 * @example wpu_phpbb_stats('limit=20&before=<li>&after=</li>');
 * @author John Wells
 */
function get_wpu_phpbb_stats($args='') {
	global $phpbbForum,  $phpEx;
	$defaults = array('before' => '<li>', 'after' => '</li>');
	extract(_wpu_process_args($args, $defaults));

	
	$phpbbForum->enter();
	
	$output .= $before .  sprintf($phpbbForum->lang['wpu_forum_stats_posts'],  '<strong>' 	. $phpbbForum->stats('num_posts') . '</strong>') . "$after\n";
	$output .= $before .  sprintf($phpbbForum->lang['wpu_forum_stats_threads'], '<strong>' 	. $phpbbForum->stats('num_topics') . '</strong>') . "$after\n";
	$output .= $before .  sprintf($phpbbForum->lang['wpu_forum_stats_users'], '<strong>' 	. $phpbbForum->stats('num_users')  . '</strong>') . "$after\n";	
	$output .= $before . sprintf($phpbbForum->lang['wpu_forum_stats_newest_user'], '<a href="' . $phpbbForum->url . "memberlist.$phpEx?mode=viewprofile&amp;u=" . $phpbbForum->stats('newest_user_id') . '"><strong>' . $phpbbForum->stats('newest_username') . '</strong></a>') . "$after\n";
	$phpbbForum->leave();
	return $output;

}


/**
 * Displays a link to search phpBB posts since the user's last visit (together with number of posts)
 * @param string args
 * @author John Wells
 */
function wpu_newposts_link() {
	echo get_wpu_newposts_link();
}
/**
 * Returns the link to phpBB posts since the user's last visit without displaying it
 * @param string args
 * @author John Wells
 */
function get_wpu_newposts_link() {
	global $phpbbForum, $phpEx;
	if( $phpbbForum->user_logged_in() ) {
		return '<a href="'. append_sid($phpbbForum->url . 'search.'.$phpEx.'?search_id=newposts') . '"><strong>' . get_wpu_newposts() ."</strong>&nbsp;". $phpbbForum->lang['Search_new'] . "</a>";
	}
}

/**
 * Returns the number of posts since the user's last visit
 * @author John Wells
 */
function get_wpu_newposts() {
	global $db, $phpbbForum;
	if( $phpbbForum->user_logged_in() ) {
		$phpbbForum->enter();
		$sql = "SELECT COUNT(post_id) as total
				FROM " . POSTS_TABLE . "
				WHERE post_time >= " . $phpbbForum->get_userdata('user_lastvisit');
		$result = $db->sql_query($sql);
		if( $result ) {
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$phpbbForum->leave();
			return $row['total'];
		}
	}
}

/**
 * Displays a nice list of latest phpBB forum posts
 * @author Japgalaxy & John Wells
 * @example: wpu_latest_phpbb_posts('limit=10&forum=1,2,3&before=<li>&after=</li>&dateformat=Y-m-j')
 */
function wpu_latest_phpbb_posts($args='') {
	echo get_wpu_latest_phpbb_posts($args);
}
/**
 * Returns a nice list of latest phpBB forum posts without displaying them
 * @author Japgalaxy & John Wells
 * @example: get_wpu_latest_phpbb_posts('limit=10&forum=1,2,3&before=<li>&after=</li>')
 * Modified for v0.8x to use proper WP widget styling and args, and date format
 */
function get_wpu_latest_phpbb_posts($args='') {
	global $phpbbForum, $db, $auth, $phpEx, $user;
	
	$defaults = array('limit' => 10, 'before' => '<li>', 'after' => '</li>', 'forum' => '');
	
	extract(_wpu_process_args($args, $defaults));
	$limit = ($limit > 50 ) ? 50 : $limit;
	
	$ret = '';
	
	$phpbbForum->enter();
	$forum_list = (empty($forum)) ? array() :  explode(',', $forum); //forums to explicitly check
	$forums_check = array_unique(array_keys($auth->acl_getf('f_read', true)));
	if (sizeof($forum_list)) {
		$forums_check = array_intersect($forums_check, $forum_list);
	}
	if (!sizeof($forums_check)) {
		$phpbbForum->leave();
		return $before. $phpbbForum->lang['wpu_no_access'] . $after;
	}	
	$sql = 'SELECT p.post_id, p.topic_id, p.forum_id, p.post_time, t.topic_title, f.forum_name, p.poster_id, u.username, f.forum_id
            FROM ' . POSTS_TABLE . ' AS p, ' . TOPICS_TABLE . ' AS t, ' . FORUMS_TABLE . ' AS f, ' . USERS_TABLE . ' AS u
			WHERE ' . $db->sql_in_set('f.forum_id', $forums_check)  . ' 
			AND  p.topic_id = t.topic_id
			AND u.user_id = p.poster_id
			AND f.forum_id = p.forum_id
			AND p.forum_id = t.forum_id
			AND p.post_id = t.topic_last_post_id
			GROUP BY p.topic_id
			ORDER BY post_time DESC'; 
			
	if(!($result = $db->sql_query_limit($sql, $limit, 0))) { 
		$ret = $phpbbForum->lang['WP_DBErr_Gen'] . $after;
	}
	
	if (!sizeof($result)) {
		$ret = $before. $phpbbForum->lang['wpu_nothing'] . $after;
	} else {
		$i =0;
		while ($row = $db->sql_fetchrow($result)) {
			$first = ($i==0) ? 'wpufirst ' : '';
			$topic_link = ($phpbbForum->seo) ? "post{$row['post_id']}.html#p{$row['post_id']}" : "viewtopic.{$phpEx}?f={$row['forum_id']}&t={$row['topic_id']}&p={$row['post_id']}#p{$row['post_id']}";
			$topic_link = '<a href="' . $phpbbForum->url. $topic_link . '" title="' . wpu_censor($row['topic_title']) . '">' . wpu_censor($row['topic_title']) . '</a>';
			$user_link = ($phpbbForum->seo) ? 'member' . $row['poster_id'] . '.html' : "memberlist.{$phpEx}?mode=viewprofile&u=" . $row['poster_id'];
			$user_link = '<a href="' . $phpbbForum->url . $user_link . '">' . $row['username'] .'</a>';
			$ret .= _wpu_add_class($before, $first . 'wpuforum' . $row['forum_id']) .  sprintf($phpbbForum->lang['wpu_phpbb_post_summary'],$topic_link, $user_link,  $user->format_date($row['post_time']))  ."$after\n";
			$i++;
		}
	}
	
	$db->sql_freeresult($result);
	$phpbbForum->leave();
	return $ret;
}


/**
 * Displays a nice list of latest phpBB forum topics
 * @author John Wells
 * @example: wpu_latest_phpbb_topics('limit=10&forum=1,2,3&before=<li>&after=</li>')
 */
function wpu_latest_phpbb_topics($args = '') {
	echo get_wpu_latest_phpbb_topics($args);
}

/**
 * Returns a nice list of latest phpBB forum topics without displaying it
 * @author John Wells
 * @example: get_wpu_latest_phpbb_topics('limit=10&forum=1,2,3&before=<li>&after=</li>')
 */
function get_wpu_latest_phpbb_topics($args = '') {
	global $phpEx, $phpbbForum;
	$defaults = array('limit' => 10, 'before' => '<li>', 'after' => '</li>', 'forum' => '');
	extract(_wpu_process_args($args, $defaults));
	
	$limit = ($limit > 50 ) ? 50 : $limit;
	
	if ($posts = $phpbbForum->get_recent_topics($forum, $limit)) {
		$profile_path = "memberlist.$phpEx";
		$i=0;
		$output = '';
		foreach ($posts as $post) {
			$first = ($i==0) ? 'wpufirst ' : '';
			$topic_link = '<a href="' . $phpbbForum->url . "viewtopic.$phpEx?f={$post['forum_id']}&t={$post['topic_id']}\">" . wpu_censor($post['topic_title']) . '</a>';
			$forum_link = '<a href="' . $phpbbForum->url . "viewforum.$phpEx?f=" . $post['forum_id'] . '">' . $post['forum_name'] . '</a>';
			$user_link = '<a href="' . $phpbbForum->url . "$profile_path?mode=viewprofile&amp;u=" . $post['user_id'] . '">' . $post['username'] . '</a>';
			$output .= _wpu_add_class($before, $first . 'wpuforum' . $post['forum_id']) . sprintf($phpbbForum->lang['wpu_phpbb_topic_summary'],$topic_link, $user_link, $forum_link)  ."$after\n";
			$i++;
		}
	} else {
		$output = $before. $phpbbForum->lang['wpu_nothing'] . $after;
	}
	return $output;
	
}



/**
 * Retrieve the phpBB user ID from a given WordPress ID
 * @author John Wells
 * @param $wp_userID. The WordPress user ID. Leave blank to use the currently logged-in user.
 * @since v0.7.0
 */
function get_wpu_user_id($wp_userID = '') {
	global $userdata, $user_ID;
	
	if (!$wp_userID ) { 
		get_currentuserinfo();
		$uID = $userdata->phpbb_userid;	
	} else {
		$uID = get_usermeta($wp_userID, 'phpbb_userid');
	}
	return $uID;
}

/**
 * Display the phpBB user ID from a given WordPress ID
 * @author John Wells
 * @param $wp_userID. The WordPress user ID. Leave blank to use the currently logged-in user.
 * @since v0.7.0
 */
function wpu_user_id($wp_userID = '') {
	echo get_wpu_user_id($wp_userID);
}

/**
 * Returns the phpBB user profile link for the current commenter
 * @author John Wells
 * @since v0.7.0
 */
function wpu_get_comment_author_link($link = '') {
global $comment, $phpbb_root_path, $phpbbForum;
	
	// comment URL could already be filled by cross-posted comments
	if(!empty($comment->phpbb_id) && !empty($link)) {
		return $link;
	}
	
	if(empty($comment->user_id)) {
		return (empty($link)) ? '<a href="' . $comment->comment_author_url . '" rel="nofollow">' . $comment->comment_author . '</a>' : $link;
	}
	$uID = get_wpu_user_id($comment->user_id);
	
	if (empty($uID)) { 
		return (empty($link)) ? '<a href="' . $comment->comment_author_url . '" rel="nofollow">' . $comment->comment_author . '</a>' : $link;
	} else {
		if ($phpbbForum->seo) {
			return $wpu_link = '<a href="' . $phpbbForum->url . 'member' . $uID . '.html">' . $comment->comment_author . '</a>';
		} else {
			return $wpu_link = '<a href="' . $phpbbForum->url . 'memberlist.php?mode=viewprofile&u=' . $uID  . '" rel="nofollow">' . $comment->comment_author . '</a>';
		}
	}
}

/**
 * Displays the phpBB user profile link for the current commenter
 * @author John Wells
 * @since v0.7.0
 */
function wpu_comment_author_link () {
	echo  wpu_get_comment_author_link();
}

/**
 * Displays the logged in user list
 * @author John Wells
 * @since v0.8.0
 * @example wpu_useronlinelist('before=<li>&after=</li>&showBreakdown=1&showRecord=1&showLegend=1');
 */
function wpu_useronlinelist($args = '') {
	echo get_wpu_useronlinelist($args);
}

/**
 * Returns the logged in user list without displaying it
 * @author John Wells
 * @since v0.8.0
 * @example wpu_useronlinelist('before=<li>&after=</li>&showBreakdown=1&showRecord=1&showLegend=1');
 */
function get_wpu_useronlinelist($args = '') {
	global $phpbbForum, $template, $auth, $db, $config, $user, $phpEx, $phpbb_root_path;
	
	$defaults = array('before' => '<li>', 'after' => '</li>', 'showCurrent' => 1, 'showRecord' => 1, 'showLegend' => 1);
	extract(_wpu_process_args($args, $defaults));
	
	if( (!empty($template)) && (!empty($legend))  && ($theList = $template->_rootref['LOGGED_IN_USER_LIST'])) {
		// On the phpBB index page -- everything's already in template
		$legend = $template->_rootref['LEGEND'];
		$l_online_users = $template->_rootref['TOTAL_USERS_ONLINE'];
		$l_online_time = $template->_rootref['L_ONLINE_EXPLAIN'];
		$l_online_record = $template->_rootref['RECORD_USERS'];
		
	} else {
		// On other pages, get the list
		
		$online_users = obtain_users_online();
		$list = obtain_users_online_string($online_users);
		
		$phpbbForum->enter();
		// Grab group details for legend display
		if ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))	{
			$sql = 'SELECT group_id, group_name, group_colour, group_type
				FROM ' . GROUPS_TABLE . '
				WHERE group_legend = 1
				ORDER BY group_name ASC';
		} else {
			$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type
				FROM ' . GROUPS_TABLE . ' g
				LEFT JOIN ' . USER_GROUP_TABLE . ' ug
					ON (
						g.group_id = ug.group_id
						AND ug.user_id = ' . $phpbbForum->get_userdata('user_id') . '
						AND ug.user_pending = 0
					)
				WHERE g.group_legend = 1
					AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $phpbbForum->get_userdata('user_id') . ')
				ORDER BY g.group_name ASC';
		}
		$result = $db->sql_query($sql);

		$legend = array();
		while ($row = $db->sql_fetchrow($result)) {
			$colour_text = ($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . '"' : '';
			$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $phpbbForum->lang['G_' . $row['group_name']] : $row['group_name'];

			if ($row['group_name'] == 'BOTS' || ($phpbbForum->get_userdata('user_id') != ANONYMOUS && !$auth->acl_get('u_viewprofile'))) {
				$legend[] = '<span' . $colour_text . '>' . $group_name . '</span>';
			} else {
				$legend[] = '<a' . $colour_text . ' href="' . append_sid("{$phpbbForum->url}memberlist.{$phpEx}", 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
			}
		}
		$db->sql_freeresult($result);
		

		$legend = implode(', ', $legend);
		$l_online_time = ($config['load_online_time'] == 1) ? 'VIEW_ONLINE_TIME' : 'VIEW_ONLINE_TIMES';
		$l_online_time = sprintf($phpbbForum->lang[$l_online_time], $config['load_online_time']);
		$l_online_record = sprintf($phpbbForum->lang['RECORD_ONLINE_USERS'], $config['record_online_users'], $user->format_date($config['record_online_date']));
		$l_online_users = $list['l_online_users'];
		$theList = str_replace($phpbb_root_path, $phpbbForum->url, $list['online_userlist']);
		
		$phpbbForum->leave();	
			
	} 
		
	if ($showBreakdown) {
		$ret .= "{$before}{$l_online_users} ({$l_online_time}){$after}";
	}
	if ($showRecord) {
		$ret .= "{$before}{$l_online_record}{$after}";
	}
	$ret .= "{$before}{$theList}{$after}";
	if($showLegend) {
		$ret .= "{$before}<em>{$phpbbForum->lang['LEGEND']}: {$legend}</em>{$after}";
	}
	
	return $ret;
	
}

/**
 * Displays info about the current user, or a login form if they are logged out
 */
function wpu_login_user_info($args) {
	echo get_wpu_login_user_info($args);
}

/**
 * Gets info about the current user, or a login form if they are logged out, without displaying it
 * @author Japgalaxy, updated by John Wells
 * @example wpu_login_user_info("before=<li>&after=</li>&showLoginForm=1&showRankBlock=1&showNewPosts=1&showWriteLink=1&showAdminLinks=1&showPMs=1");
 */
function get_wpu_login_user_info($args) {
	global $user_ID, $db, $wpSettings, $auth, $phpbbForum, $wpSettings, $phpEx, $config;
	
	$defaults = array('before' => '<li>', 'after' => '</li>', 'showPMs' => 1, 'showLoginForm' => 1, 'showRankBlock' => 1, 'showNewPosts' => 1, 'showWriteLink' => 1, 'showAdminLinks' => 1);
	extract(_wpu_process_args($args, $defaults));

	$ret = '';

	get_currentuserinfo();
	$loggedIn = $phpbbForum->user_logged_in();
	
	if($loggedIn) {
		$wpu_usr = get_wpu_phpbb_username(); 

			$ret .= _wpu_add_class($before, 'wpu-widget-lu-username'). '<a href="' . $phpbbForum->url . 'ucp.' . $phpEx . '"><strong>' . $wpu_usr . '</strong></a>' . $after;
			$ret .= _wpu_add_class($before, 'wpu-widget-lu-avatar') . '<img src="' . get_avatar_reader() . '" alt="' . $phpbbForum->lang['USER_AVATAR'] . '" />' . $after; 

		if ( $showRankBlock ) {
			$ret .= _wpu_add_class($before, 'wpu-widget-lu-rankblock') . get_wpu_phpbb_rankblock() . $after;
		}

		if ( $showNewPosts ) {
			$ret .= $before .  get_wpu_newposts_link() . $after;
		}
		
		// Handle new PMs
		if($showPMs) {
			if ($phpbbForum->get_userdata('user_new_privmsg')) {
				$l_message_new = ($phpbbForum->get_userdata('user_new_privmsg') == 1) ? $phpbbForum->lang['NEW_PM'] : $phpbbForum->lang['NEW_PMS'];
				$l_privmsgs_text = sprintf($l_message_new, $phpbbForum->get_userdata('user_new_privmsg'));
				$ret .= _wpu_add_class($before, 'wpu-has-pms'). '<a title="' . $l_privmsgs_text . '" href="' . $phpbbForum->url . 'ucp.php?i=pm&folder=inbox">' . $l_privmsgs_text . '</a>' . $after;
			} else {
				$l_privmsgs_text = $phpbbForum->lang['NO_NEW_PM'];
				$s_privmsg_new = false;
				$ret .= _wpu_add_class($before, 'wpu-no-pms') . '<a title="' . $l_privmsgs_text . '" href="' . $phpbbForum->url . 'ucp.php?i=pm&folder=inbox">' . $l_privmsgs_text . '</a>' . $after;
			}	
		}

		if ($showWriteLink) {
			if (current_user_can('publish_posts')) {
				$ret .= $before . '<a href="'.$wpSettings['wpUri'].'wp-admin/post-new.php" title="' . $phpbbForum->lang['wpu_write_post'] . '">' . $phpbbForum->lang['wpu_write_post'] . '</a>' . $after;
			}
		}
		if ($showAdminLinks) {
			if (current_user_can('publish_posts')) {
				$ret .= $before . '<a href="'.$wpSettings['wpUri'].'wp-admin/" title="Admin Site">' . __('Dashboard') . '</a>' . $after;
			}
			$phpbbForum->enter();
			if($auth->acl_get('a_')) {
				$ret .= $before . '<a href="'.$phpbbForum->url . append_sid('adm/index.php', false, false, $GLOBALS['user']->session_id) . '" title="Admin Forum">' . $phpbbForum->lang['ACP'] . '</a>' . $after;
			}
			$phpbbForum->leave();
		}
		$ret .= $before . get_wp_loginout() . $after;
	} else {
		if ( $showLoginForm ) {
			$redir = wpu_get_redirect_link();
			$login_link = append_sid('ucp.'.$phpEx.'?mode=login') . '&amp;redirect=' . $redir;
			$ret .= '<form class="wpuloginform" method="post" action="' . $phpbbForum->url . $login_link . '">';
			$ret .= $before . '<label for="phpbb_username">' . $phpbbForum->lang['USERNAME'] . '</label> <input tabindex="1" class="inputbox autowidth" type="text" name="username" id="phpbb_username"/>' . $after;
			$ret .= $before . '<label for="phpbb_password">' . $phpbbForum->lang['PASSWORD'] . '</label> <input tabindex="2" class="inputbox autowidth" type="password" name="password" id="phpbb_password" maxlength="32" />' . $after;
			if ( $config['allow_autologin'] ) {
				$ret .= $before . '<input tabindex="3" type="checkbox" id="phpbb_autologin" name="autologin" /><label for="phpbb_autologin"> ' . $phpbbForum->lang['LOG_ME_IN'] . '</label>' . $after;
			}
			$ret .= $before . '<input type="submit" name="login" class="wpuloginsubmit" value="' . $phpbbForum->lang['LOGIN'] . '" />' . $after;
			$ret .= $before . '<a href="' . append_sid($phpbbForum->url."ucp.php?mode=register") . '">' . $phpbbForum->lang['REGISTER'] . '</a>' . $after;
			$ret .= $before . '<a href="'.append_sid($phpbbForum->url).'ucp.php?mode=sendpassword">' . $phpbbForum->lang['FORGOT_PASS'] . '</a>' . $after;
			$ret .= '</form>';
		} else {
			$ret .= $before . get_wp_loginout() . $after;
		}
	}
	
	return $ret;
}



/**
 * Displays the comment link, with the number of phpBB comments
 * @author Japgalaxy
 * @todo allow to specify a specific post ID
 * @todo combine these three queries with a JOIN
 * @version v0.8.0
 * @access private
 */
function wpu_comment_number () {
	echo get_wpu_comment_number();
}


/**
 * Display replies of a cross-posted post and a comment-form if user is logged in.
 * @author Japgalaxy
 * @since v0.8.0
 */



/**
 * Helper / Private functions
 */

/**
 * Returns a URL suitable for sending as a redirect instruction to phpBB
  * @ since v0.8.1
 */
function wpu_get_redirect_link() {
	global $phpbbForum;
	if(!empty( $_SERVER['REQUEST_URI'])) {
		$protocol = empty($_SERVER['HTTPS']) ? 'http:' : ((strtolower($_SERVER["HTTPS"]) == 'on') ? 'https:' : 'http:');
		$protocol = ($_SERVER['SERVER_PORT'] == '80') ? $protocol : $protocol . $_SERVER['SERVER_PORT'];
		$link = $protocol . '//' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	} else {
		$link = get_option('home');
	}
	$phpbbForum->enter_if_out();
	$link = reapply_sid($link);
	$phpbbForum->leave_if_just_entered();
	return urlencode(attribute_escape($link));
}

/**
 * In order to make the comment link a consistent template tag, and split to get_/echo
 * we need to create this missing WordPress get_ equivalent for comment link
 * @author John Wells
 * @since v0.8.0
 */
if(!function_exists('get_comments_popup_link')) {
	function get_comments_popup_link($no, $com, $coms, $closed) {
		ob_start();
		comments_popup_link($no, $com, $coms, $closed);
		$link = ob_get_contents();
		ob_end_clean();
		return $link;
	}
}

/**
 * In order to make the loginout link a consistent template tag, and split to get_/echo
 * we need to create this missing WordPress get_ equivalent
 * @author John Wells
 * @since v0.8.0
 */
if(!function_exists('get_wp_loginout')) {
	function get_wp_loginout() {
		ob_start();
		wp_loginout();
		$link = ob_get_contents();
		ob_end_clean();
		return $link;
	}
}

/**
 * Adds a classname to an element if it doesn't already exist
 * @since v0.8.5/v0.9.0
 * @param string $el the element in which to add a class
 * @param string $class the name of the class to insert
 * @access private
 */
function _wpu_add_class($el, $class) {
	$find = '>';
	$repl = ' class="%s">';
	if(stristr($el, 'class="') > 0) {
		$find = 'class="';
		$repl = 'class="%s ';
	} else if(stristr($el, "class='") > 0) {
		$find = "class='";
		$repl = "class='%s ";
	}
	return str_replace($find, sprintf($repl, $class), $el);
	
}


/**
 * Load the rank details for the user
 * @access private
 */
function _wpu_get_user_rank_info($userID = '') {

	global $phpbbForum;
	$phpbbForum->enter();
	$rank = $phpbbForum->get_user_rank_info($userID);
	$phpbbForum->leave();
	$rank['image'] = (empty($rank['image'])) ? '' : $phpbbForum->url . $rank['image'];
	return $rank;
}
	
/**
 * Process argument string for template functions
 * @author John Wells
 * @access private
 */
function _wpu_process_args($args, $defaults='') {
	if ( is_array($args) ) {
		$r = &$args;
	} else {
		parse_str($args, $r);
	}
	if ( is_array($defaults) ) {
		$r = array_merge($defaults, $r);
	}
	return $r;
}


?>