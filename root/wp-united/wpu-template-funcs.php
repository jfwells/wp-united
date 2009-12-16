<?php
/** 
*
* WP-United WordPress template tags
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.7.1[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
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
	global $wpSettings, $phpbb_logged_in, $phpbb_sid, $wpuAbs, $phpEx, $wpuGetBlogIntro, $scriptPath;
	if ( (!empty($wpSettings['useBlogHome'])) && (!empty($wpSettings['usersOwnBlogs'])) ) {
		$reg_link = ($wpuAbs->ver == 'PHPBB2') ? 'profile.'.$phpEx.'?mode=register&amp;redirect=wp-united-blog' : 'ucp.'.$phpEx.'?mode=register';
		$login_link = ($wpuAbs->ver == 'PHPBB2') ? 'login.'.$phpEx.'?redirect=wp-united-blog&amp;sid='. $phpbb_sid : 'ucp.'.$phpEx.'?mode=login&amp;sid=' . $phpbb_sid . '&amp;redirect='. attribute_escape($_SERVER["REQUEST_URI"]);	
		if ( ! $phpbb_logged_in ) {
			$getStarted = '<p class="wpuintro">' . sprintf($wpuGetBlogIntro,'<a href="' . $scriptPath . append_sid($reg_link) . '">', '</a>',  '<a href="'. $scriptPath . $login_link . '">', '</a>') . '</p>';
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
	global $wpdb, $authordata, $scriptPath, $wpuAbs, $wpSettings, $wp_version, $phpEx;
	$start = 0;
	$start = (integer)trim($_GET['start']);
	$start = ($start < 0) ? 0 : $start;
	//get total count
	$sql = "SELECT count(DISTINCT {$wpdb->users}.ID) AS total
			FROM {$wpdb->users} 
			INNER JOIN {$wpdb->posts}
			ON {$wpdb->users}.ID={$wpdb->posts}.post_author
			WHERE {$wpdb->users}.user_login <> 'admin'";
	$count = $wpdb->get_results($sql);
	$numAuthors = $count[0]->total;
	
	$maxEntries = ($maxEntries < 1) ? 5 : $maxEntries;
	//pull the data we want to display -- this doesn't appear to be very efficient, but it is the same method as  the built-in WP function
	// wp_list_authors uses. Let's hope the data gets cached!
	$sql = "SELECT DISTINCT {$wpdb->users}.ID, {$wpdb->users}.user_login, {$wpdb->users}.user_nicename 
			FROM {$wpdb->users}
			INNER JOIN {$wpdb->posts} 
			ON {$wpdb->users}.ID={$wpdb->posts}.post_author 
			WHERE {$wpdb->users}.user_login<>'admin' 
			ORDER BY {$wpdb->users}.display_name LIMIT $start, $maxEntries";
	$authors= $wpdb->get_results($sql);

	if ( count($authors) > 0 ) {
		$d = get_settings('time_format');
		$time = mysql2date($d, $time);
		$itern = 1;
		$blogList = '';
		foreach ( (array) $authors as $author ) {
			$posts = 0; $_oldQuery = ''; $avatar = '';
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
				$blogTitle = ( empty($author->blog_title) ) ? $wpuAbs->lang('default_blogname') : $author->blog_title;
				$blogDesc = ( empty($author->blog_tagline) ) ? $wpuAbs->lang('default_blogdesc') : $author->blog_tagline;
				if ( ((float) $wp_version) >= 2.1 ) {
					//WP >= 2.1 branch
					$blogPath = get_author_posts_url($author->ID, $author->user_nicename);
				} else {
					//deprecated branch
					$blogPath = get_author_link(false, $author->ID, $author->user_nicename); 
				}
				$wUsrName = sanitize_user($author->user_login, true);
				if ( ($wUsrName == $author->user_login) ) {
					$pUsrName = $author->user_login;
				} else {
					$pUsrName == $author->phpbb_userLogin;
				}
				$profile_path = ($wpuAbs->ver == 'PHPBB2') ? "profile.$phpEx" : "memberlist.$phpEx";
				$path_to_profile = ( empty($pID) ) ? append_sid($blogPath) : append_sid(add_trailing_slash($scriptPath) . $profile_path .'?mode=viewprofile&amp;u=' .$pID); 
				$rssLink = get_author_rss_link(0, $author->ID, $author->user_nicename);
				$lastPostID = $author->wpu_last_post;
				if ( empty($lastPostID) ) {
					$_oldQuery = $GLOBALS['wp_query'];
					query_posts('author=' . $author->ID . '&showposts=1');
					if ( have_posts() ) {
						the_post();
						if ( ((float) $wp_version) >= 2.1 ) {
							//WP >= 2.1 branch
							$lastPostID = get_the_ID();
						} else {
							//deprecated branch
							global $id;
							$lastPostID = $id;
						}
						update_usermeta($author->ID, 'wpu_last_post', $lastPostID);
					} 

					$GLOBALS['wp_query'] = $_oldQuery;
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
				$blogList .=  "<h2 class=\"wpublsubject\" ><a href=\"$blogPath\">$blogTitle</a>, " . __('by') . ' <a href="' . $path_to_profile . '">' . $name . "</a></h2>\n\n";
				$blogList .=  '<p class="wpubldesc">' . $blogDesc . "</p>\n\n";
				$blogList .=  '<small class="wpublnumposts">' . __('Total Entries:') . ' ' . $posts . "</small><br />\n\n";
				$blogList .=  '<small class="wpublastpost">' . __('Last Entry:') . ' <a href="' . $lastPostURL . '">' . $lastPostTitle . '</a>, ' . __('posted on') . " $time</small><br />\n\n";
				if ( !empty($rssLink) ) {
					$blogList .=  '<small class="wpublrss">' . __('RSS Feed:') . ' <a href="' . $rssLink . '">' . __('Subscribe') . "</a></small><br />\n\n";
				}
				$blogList .=  "<p class=\"wpublclr\">&nbsp;</p></div>\n\n";
			}
		}
	} else {
	$blogList .= "<div class=\"wpubl\">\n";
	$blogList .= '<p class="wpubldesc">' . __('There are no authors to show') . "</p>\n";
	$blogList .= "</div>\n";
	}
	if ( $numAuthors > $maxEntries ) { 
		$base_url = append_sid(strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']);
		$pagination = generate_pagination($base_url, $numAuthors, $maxEntries, $start, TRUE);
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
global $comment, $images, $scriptPath;

	if ( empty($id) ) {
		if ( !empty($comment) ) {
			$id = $comment->user_id;
		} 
		if ( empty($id) ) {
			if ( $default ) {
				return $scriptPath . 'wp-united/images/wpu_unregistered.gif';
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
		return $scriptPath . 'wp-united/images/wpu_no_avatar.gif';
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
	global $images, $authordata, $scriptPath;
	$image = avatar_create_image($authordata);
	if ( !empty($image) ) {
		return $image;
	} 
	if ( $default ) {
		return $scriptPath . 'wp-united/images/wpu_no_avatar.gif';
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
	global $images, $scriptPath, $userdata, $user_ID;
	get_currentuserinfo();
	if ( !empty($user_ID) ) {
		$image = avatar_create_image($userdata);
	}
	if ( !empty($image) ) {
		return $image;
	} elseif ( $image === FALSE ) {
		if ( $default ) {
			return $scriptPath . 'wp-united/images/wpu_unregistered.gif';
		}
	}
	if ( $default ) {
		return $scriptPath . 'wp-united/images/wpu_no_avatar.gif';
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
		global $wpuAbs, $scriptPath, $phpbb_root_path, $phpEx;
		if(empty($phpbb_root_path)) {
			$connSettings = get_settings('wputd_connection');
			$phpbb_root_path = $connSettings['path_to_phpbb'];
			$phpEx = substr(strrchr(__FILE__, '.'), 1);	
			define('IN_PHPBB', TRUE);
			require_once($phpbb_root_path . 'includes/constants.' . $phpEx); 
			$scriptPath = $phpbb_root_path;			
		}			
		if ($user->wpu_avatar_type && $user->wpu_avatar) {
			require_once($phpbb_root_path . 'includes/functions_display.' . $phpEx); 
			$avatar = get_user_avatar($user->wpu_avatar, $user->wpu_avatar_type, $user->wpu_avatar_width, $user->wpu_avatar_height);
			$avatar = explode('"', $avatar);
			$avatar = str_replace($phpbb_root_path, $scriptPath, $avatar[1]); //stops trailing slashes in URI from killing avatars
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
	global $wpuAbs, $wpdb;

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
			$blogTitle = ( $blogTitle == '' ) ? $wpuAbs->lang('default_blogname') : $blogTitle;
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
	global $wpuAbs, $wpdb;
	
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
			$blogTitle = ( $blogTitle == '' ) ? $wpuAbs->lang('default_blogname') : $blogTitle;
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
			$htmlOut .= sprintf(__('%s, in %s'), trim($postLink), $blogLink);
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
	global $wpuAbs;
	$usrName = '';
	if ( $wpuAbs->user_logged_in() ) {
		$usrName = $wpuAbs->phpbb_username();
	} 
	return ($usrName == '') ? $wpuAbs->lang('Guest') : $usrName;
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
	$phpbb_usr_id = get_usermeta($wpID, 'phpbb_userid');
	if (!empty($usr_data)) {
		$profile_path = "memberlist.$phpEx";
		return add_trailing_slash($scriptPath) . "$profile_path?mode=viewprofile&amp;u=" . $phpbb_usr_id;
	}
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
	$rank = _wpu_get_user_rank_info($wpID);
	if ( $rank ) {
		$block = '<p class="wpu_rank">' . $rank['text'];
		if ( $rank['image'] ) {
			$block .= '<br />' . '<img src="' . $rank['image'] . '" alt="' . __('rank') . '" />';
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
	global $wpuAbs, $phpEx, $scriptPath;
	$defaults = array('before' => '<li>', 'after' => '</li>');
	extract(_wpu_process_args($args, $defaults));

	
	$profile_path = "memberlist.$phpEx";
	
	$GLOBALS['wpUtdInt']->switch_db('TO_P');
	$output .= $before .  __('Forum Posts: ') 		. '<strong>' 	. $wpuAbs->stats('num_posts') 	. "</strong>$after\n";
	$output .= $before .  __('Forum Threads: ') 	. '<strong>' 	. $wpuAbs->stats('num_topics') . "</strong>$after\n";
	$output .= $before .  __('Registered Users: ') . '<strong>' 	. $wpuAbs->stats('num_users') 	. "</strong>$after\n";	
	   $output .= $before .  __('Newest User: ') . '<a href="' . add_trailing_slash($scriptPath) . "$profile_path?mode=viewprofile&amp;u=" . $wpuAbs->stats('newest_user_id') . '"><strong>' . $wpuAbs->stats('newest_username') . "</strong></a>$after\n";
	$GLOBALS['wpUtdInt']->switch_db('TO_W');
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
	global $wpuAbs, $phpEx, $scriptPath;
	if( $wpuAbs->user_logged_in() ) {
		return '<a href="'. append_sid($scriptPath . 'search.'.$phpEx.'?search_id=newposts') . '"><strong>' . get_wpu_newposts() ."</strong>&nbsp;". $wpuAbs->lang('Search_new') . "</a>";
	}
}

/**
 * Returns the number of posts since the user's last visit
 * @author John Wells
 */
function get_wpu_newposts() {
	global $db, $wpuAbs;
	if( $wpuAbs->user_logged_in() ) {
		$GLOBALS['wpUtdInt']->switch_db('TO_P');
		$sql = "SELECT COUNT(post_id) as total
				FROM " . POSTS_TABLE . "
				WHERE post_time >= " . $wpuAbs->userdata('user_lastvisit');
		$result = $db->sql_query($sql);
		if( $result ) {
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$GLOBALS['wpUtdInt']->switch_db('TO_W');
			return $row['total'];
		}
	}
}

/**
 * Displays a nice list of latest phpBB forum posts
 * @author Japgalaxy & John Wells
 * @example: wpu_latest_phpbb_posts('limit=10&forum=1,2,3&before=<li>&after=</li>&seo=0&dateformat=Y-m-j')
 */
function wpu_latest_phpbb_posts($args='') {
	echo get_wpu_latest_phpbb_posts($args);
}
/**
 * Returns a nice list of latest phpBB forum posts without displaying them
 * @author Japgalaxy & John Wells
 * @example: get_wpu_latest_phpbb_posts('limit=10&forum=1,2,3&before=<li>&after=</li>&seo=0&dateformat=Y-m-j')
 * @todo dateformat to use phpBB format
 * Modified for v0.8 to use proper WP widget styling and args
 */
function get_wpu_latest_phpbb_posts($args='') {
	global $scriptPath, $wpuAbs, $db, $auth, $phpEx;
	
	$defaults = array('limit' => 10, 'before' => '<li>', 'after' => '</li>', 'forum' => '', 'dateformat' => 'Y-m-j', 'seo' => 0);
	
	extract(_wpu_process_args($args, $defaults));
	$limit = ($limit > 50 ) ? 50 : $limit;
	
	$ret = '';
	
	$GLOBALS['wpUtdInt']->switch_db('TO_W');
	$forum_list = (empty($forum)) ? array() :  explode(',', $forum); //forums to explicitly check
	$forums_check = array_unique(array_keys($auth->acl_getf('f_read', true)));
	if (sizeof($forum_list)) {
		$forums_check = array_intersect($forums_check, $forum_list);
	}
	if (!sizeof($forums_check)) {
		$GLOBALS['wpUtdInt']->switch_db('TO_W');
		return $before.__('No access') . $after;
	}	
	$sql = 'SELECT p.post_id, p.topic_id, p.forum_id, post_time, topic_title, f.forum_name, p.poster_id, u.username, f.forum_id
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
		$ret = $before.__('Could not retrieve phpBB data') . $after;
	}
	
	if (!sizeof($result)) {
		$ret = $before.__('Nothing found').$after;
	} else {
		$i =0;
		while ($row = $db->sql_fetchrow($result)) {
			$class = ($i==0) ? 'class="wpufirst" ' : '';
			$thisBefore = (($i==0)  && ($before == '<li>')) ? '<li class="wpufirst">' : $before;
			$topic_link = ($seo) ? "post{$row['post_id']}.html#p{$row['post_id']}" : "viewtopic.{$phpEx}?f={$row['forum_id']}&t={$row['topic_id']}&p={$row['post_id']}#p{$row['post_id']}";
			$topic_link = '<a ' . $class . 'href="' . add_trailing_slash($scriptPath) . $topic_link . '" title="' . $row['topic_title'] . '">' . $row['topic_title'] . '</a>';
			$user_link = ($seo) ? 'member' . $row['poster_id'] . '.html' : "memberlist.{$phpEx}?mode=viewprofile&u=" . $row['poster_id'];
			$user_link = '<a ' . $class . 'href="' . add_trailing_slash($scriptPath) . $user_link . '">' . $row['username'] .'</a>';
			$ret .= $thisBefore . sprintf(__('%1s, posted by %2s at %3s'),$topic_link, $user_link,  date($dateformat, $row['post_time']))  ."$after\n";
			$i++;
		}
	}
	
	$db->sql_freeresult($result);
	$GLOBALS['wpUtdInt']->switch_db('TO_W');
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
	global $scriptPath, $phpEx, $wpuAbs;
	$defaults = array('limit' => 10, 'before' => '<li>', 'after' => '</li>', 'forum' => '');
	extract(_wpu_process_args($args, $defaults));
	
	$limit = ($limit > 50 ) ? 50 : $limit;
	
	if ($posts = $wpuAbs->get_recent_topics($forum, $limit)) {
		$profile_path = "memberlist.$phpEx";
		$i=0;
		foreach ($posts as $post) {
			$class = ($i==0) ? 'class="wpufirst" ' : '';
			$thisBefore = (($i==0)  && ($before == '<li>')) ? '<li class="wpufirst">' : $before;
			$topic_link = '<a ' . $class . 'href="' . add_trailing_slash($scriptPath) . "viewtopic.$phpEx?t=" . $post['topic_id'] . '">' . $post['topic_title'] . '</a>';
			$forum_link = '<a ' . $class . 'href="' . add_trailing_slash($scriptPath) . "viewforum.$phpEx?f=" . $post['forum_id'] . '">' . $post['forum_name'] . '</a>';
			$user_link = '<a ' . $class . 'href="' . add_trailing_slash($scriptPath) . "$profile_path.$phpEx?mode=viewprofile&amp;u=" . $post['user_id'] . '">' . $post['username'] . '</a>';
			$output .= $thisBefore . sprintf(__('%1s, posted by %2s in %3s'),$topic_link, $user_link, $forum_link)  ."$after\n";
			$i++;
		}
	} else {
		$output = __('No topics to show');
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
function wpu_get_comment_author_link () {
global $comment;
 
	$uID = get_wpu_user_id($comment->user_id);
	
	if (empty($uID)) { 
		return $wpu_link = get_comment_author();
	} else {
		global $scriptPath;
		if(empty($scriptPath)) {
			$connSettings = get_settings('wputd_connection');
			$phpbbPath = $connSettings['path_to_phpbb'];
		} else { 
			$phpbbPath = $scriptPath;
		}
		if (file_exists($phpbb_root_path . 'phpbb_seo/phpbb_seo_class.php')) {
			return $wpu_link = '<a href="' . $phpbbPath . 'member' . $uID . '.html">' . $comment->comment_author . '</a>';
		} else {
			return $wpu_link = '<a href="' . $phpbbPath . 'memberlist.php?mode=viewprofile&u=' . $uID  . '" rel="nofollow">' . $comment->comment_author . '</a>';
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
	global $template, $user, $auth, $db, $config;
	
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
		
		$GLOBALS['wpUtdInt']->switch_db('TO_P');
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
						AND ug.user_id = ' . $user->data['user_id'] . '
						AND ug.user_pending = 0
					)
				WHERE g.group_legend = 1
					AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $user->data['user_id'] . ')
				ORDER BY g.group_name ASC';
		}
		$result = $db->sql_query($sql);

		$legend = array();
		while ($row = $db->sql_fetchrow($result)) {
			$colour_text = ($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . '"' : '';
			$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];

			if ($row['group_name'] == 'BOTS' || ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile'))) {
				$legend[] = '<span' . $colour_text . '>' . $group_name . '</span>';
			} else {
				$legend[] = '<a' . $colour_text . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
			}
		}
		$db->sql_freeresult($result);
		$GLOBALS['wpUtdInt']->switch_db('TO_W');

		$legend = implode(', ', $legend);
		$l_online_time = ($config['load_online_time'] == 1) ? 'VIEW_ONLINE_TIME' : 'VIEW_ONLINE_TIMES';
		$l_online_time = sprintf($user->lang[$l_online_time], $config['load_online_time']);
		$l_online_record = sprintf($user->lang['RECORD_ONLINE_USERS'], $config['record_online_users'], $user->format_date($config['record_online_date']));
		$l_online_users = $list['l_online_users'];
		$theList = $list['online_userlist'];
			
	} 
		
	if ($showBreakdown) {
		$ret .= "{$before}{$l_online_users} ({$l_online_time}){$after}";
	}
	if ($showRecord) {
		$ret .= "{$before}{$l_online_record}{$after}";
	}
	$ret .= "{$before}{$theList}{$after}";
	if($showLegend) {
		$ret .= "{$before}<em>{$user->lang['LEGEND']}: {$legend}</em>{$after}";
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
 * @example wpu_login_user_info("before=<li>&after=</li>&showLoginForm=1&showRankBlock=1&showNewPosts=1&showWriteLink=1&showAdminLinks=1");
 */
function get_wpu_login_user_info($args) {
	global $user_ID, $user, $db, $scriptPath, $wpSettings, $auth, $wpuAbs, $phpbb_sid, $wpSettings, $phpEx;
	
	$defaults = array('before' => '<li>', 'after' => '</li>');
	extract(_wpu_process_args($args, $defaults));
	
	
	$ret = '';
	
	get_currentuserinfo();
	$title =  (!empty($user_ID)) ? $titleLoggedIn : $titleLoggedOut;
	$loggedIn = (!empty($user_ID)) ? true: false;
	
	if($loggedIn) {
		$wpu_usr = get_wpu_phpbb_username(); 

			$ret .= $before . '<a href="' . add_trailing_slash($scriptPath) . 'ucp.php?i=164"><strong>' . $wpu_usr . '</strong></a>' . $after;
			$ret .= $before . '<img src="' . get_avatar_reader() . '" alt="' . __(avatar) . '" />' . $after; 


		if ( $showRankBlock ) {
			$ret .= $before . get_wpu_phpbb_rankblock() . $after;
		}

		if ( $showNewPosts ) {
			$ret .= $before .  get_wpu_newposts_link() . $after;
		}

		// Handle new PMs
		if ($user->data['user_new_privmsg']) {
			$l_message_new = ($user->data['user_new_privmsg'] == 1) ? $wpuAbs->lang('NEW_PM') : $wpuAbs->lang('NEW_PMS');
			$l_privmsgs_text = sprintf($l_message_new, $user->data['user_new_privmsg']);
			$ret .= $before. '<a title="' . $l_privmsgs_text . '" href="' . add_trailing_slash($scriptPath) . 'ucp.php?i=pm&folder=inbox">' . $l_privmsgs_text . '</a>' . $after;
		} else {
			$l_privmsgs_text = $wpuAbs->lang('NO_NEW_PM');
			$s_privmsg_new = false;
			$ret .= $before . '<a title="' . $l_privmsgs_text . '" href="' . add_trailing_slash($scriptPath) . 'ucp.php?i=pm&folder=inbox">' . $l_privmsgs_text . '</a>' . $after;
		}	

		if ($showWriteLink) {
			if (current_user_can('publish_posts')) {
				$ret .= $before . '<a href="'.$wpSettings['wpUri'].'wp-admin/post-new.php" title="' . __('Write a Post') . '">' . __('Write a Post') . '</a>' . $after;
			}
		}
		if ($showAdminLinks) {
			$connSettings = get_settings('wputd_connection');
			if (current_user_can('publish_posts')) {
				$ret .= $before . '<a href="'.$wpSettings['wpUri'].'wp-admin/" title="Admin Site">' . __('Dashboard') . '</a>' . $after;
			}
			if($auth->acl_get('a_')) {
				$ret .= $before . '<a href="'.$scriptPath.'adm/index.php?'.$phpbb_sid.'" title="Admin Forum">' . $wpuAbs->lang('ACP') . '</a>' . $after;
			}
		}
		$ret .= $before . get_wp_loginout() . $after;
	} else {
		if ( $showLoginForm ) {
			$login_link = 'ucp.'.$phpEx.'?mode=login&amp;sid=' . $phpbb_sid . '&amp;redirect=http://' . $_SERVER['SERVER_NAME'] .''. attribute_escape($_SERVER["REQUEST_URI"]);
			$ret .= '<form class="wpuloginform" method="post" action="' . add_trailing_slash($scriptPath) . $login_link . '">';
			$ret .= $before . '<label for="phpbb_username">' . $wpuAbs->lang('USERNAME') . '</label> <input tabindex="1" class="inputbox autowidth" type="text" name="username" id="phpbb_username"/>' . $after;
			$ret .= $before . '<label for="phpbb_password">' . $wpuAbs->lang('PASSWORD') . '</label> <input tabindex="2" class="inputbox autowidth" type="password" name="password" id="phpbb_password" maxlength="32" />' . $after;
			if ( $wpuAbs->config('allow_autologin') ) {
				$ret .= $before . '<input tabindex="3" type="checkbox" id="phpbb_autologin" name="autologin" /><label for="phpbb_autologin"> ' . $wpuAbs->lang('LOG_ME_IN') . '</label>' . $after;
			}
			$ret .= $before . '<input type="submit" name="login" class="wpuloginsubmit" value="' . $wpuAbs->lang('LOGIN') . '" />' . $after;
			$ret .= $before . '<a href="' . append_sid(add_trailing_slash($scriptPath)."ucp.php?mode=register") . '">' . $wpuAbs->lang('REGISTER') . '</a>' . $after;
			$ret .= $before . '<a href="'.append_sid(add_trailing_slash($scriptPath)).'ucp.php?mode=sendpassword">' . $wpuAbs->lang('FORGOT_PASS') . '</a>' . $after;
			$ret .= '</form>';
		} else {
			$ret .= $before . get_wp_loginout() . $after;
		}
	}
	
	return $ret;
}

/**
 * Get the phpBB topic ID of the current cross-posted post
 * @author Japgalaxy
 * @todo allow to specify a specific post ID
 * @version v0.8.0
 * @access private
 * @todo if this will not echo anything, should be named get_xxx_xxx()
 */
function wpu_topic_xposted() {
global $post;
$post_ID = $post->ID;

		if ( !empty($post_ID) ) {
			global $db;
			/**
			 * @Japgalaxy: We cannot use $db directly in WordPress without switching DB first like this
			 * Otherwise users with separate DBs get fatal errors
			 */
			$GLOBALS['wpUtdInt']->switch_db('TO_P');
			$sql = 'SELECT p.topic_id FROM ' . POSTS_TABLE . ' AS p WHERE ' . "p.post_wpu_xpost = '$post_ID'";
			
			if ($result = $db->sql_query_limit($sql, 1)) {
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				$GLOBALS['wpUtdInt']->switch_db('TO_W');
				if  (!empty($row) ) {
					return $row['topic_id'];
				}
			}
				
		}
}

/**
 * Gets the comment link, with the number of phpBB comments, without displaying it
 * @author Japgalaxy
 * @todo allow to specify a specific post ID
 * @todo combine these three queries with a JOIN
 * @todo split this -- this can be used to override wp's comment link
 * @version v0.8.0
 * @access private
 */
function get_wpu_comment_number () {
	//get the Topic_ID corrispondent of Wordpress post
	$topic_ID = wpu_topic_xposted();

		if ( !empty($topic_ID) ) {
		//if a Topic_ID exists get the number of replies:
			global $db;
			$sql = 'SELECT t.topic_replies as number FROM ' . TOPICS_TABLE . ' AS t WHERE ' . "t.topic_id = $topic_ID";
			$GLOBALS['wpUtdInt']->switch_db('TO_P');
			if ($result = $db->sql_query($sql)) {
				$row = $db->sql_fetchrow($result);
				$comment_count = $row['number'];
				$db->sql_freeresult($result);
				$GLOBALS['wpUtdInt']->switch_db('TO_W');				
				if ($comment_count==0) {
					return "<a href=\"".get_permalink()."#reply\" title=\" ".get_the_title()." \">".__('No Comments', '')."</a>";
				} else if ($comment_count==1) {
					return "<a href=\"".get_permalink()."#comments\" title=\" ".get_the_title()." \">".__('1 Comment', '')."</a>";
				} else if ($comment_count>=2) {
					return "<a href=\"".get_permalink()."#comments\" title=\" ".get_the_title()." \">".$comment_count." Comments</a>";
				}
			}
		} else {
			//default wordpress function:
			get_comments_popup_link(__('No Comments', ''), __('1 Comment', ''), __('% Comments', ''), '', __('Comments Closed', '') );
		}

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
function wpu_reply_xposted() {
	echo get_wpu_reply_xposts();
}

/**
 * Return replies of a cross-posted post and a comment-form if user is logged in, but do not display it
 * @author Japgalaxy
 * @since v0.8.0
 * @todo cleanup, strings
 */
function get_wpu_reply_xposted() {
	global $post, $wpuAbs;

	$ret='';

	$post_ID = $post->ID;
	//get the Topic_ID corresponding of Wordpress post
	$topic_ID = wpu_topic_xposted($post_ID);
	$GLOBALS['wpUtdInt']->switch_db('TO_P');
		if ( !empty($topic_ID) ) {
		//if a Topic_ID exists get all posts' topic:
			global $scriptPath, $db;
			$sql = 'SELECT p.topic_id, p.bbcode_uid, p.bbcode_bitfield, p.forum_id, p.post_id, p.poster_id, u.username, p.post_text, p.post_time FROM ' . POSTS_TABLE . ' AS p, ' . USERS_TABLE . ' AS u WHERE ' . "p.topic_id = $topic_ID AND p.poster_id = u.user_id ORDER BY post_time ASC";
			
			if ($result = $db->sql_query($sql)) {
				$comment_count = mysql_num_rows($result);
				//removing the first post (it isn't a reply)...
				$real_comments = $comment_count-1;
				if ($real_comments == 0) {
					$ret .= '<p id="reply">' . __('There aren\'t comments. Post the first one!') . '</p>';
				} else {
					$ret .= '<p id="reply">There are '.$real_comments.' comments</p>';
				}
				$ret .= '<ul class="wpu_commentlist" id="comments">';
				$i = 0;
				while ($row = $db->sql_fetchrow($result)){
					//set value for comment form
					$link = $scriptPath."/posting.php?mode=reply&f=".$row['forum_id']."&t=".$row['topic_id']."";
					$hiddenvalue = '<input type="hidden" value="'.$row['topic_id'].'" name="topic_id"/><input type="hidden" value="'.$row['forum_id'].'" name="forum_id"/>';

					if (($real_comments >= 1) && ($i > 0)) {
					$ret .=  "<li>";
						//Userdata
						$ret .=  '<div class="wpu_comment_info">
							<div class="wpu_avatar_comment">'.get_avatar($row['poster_id'])."</div>
							<a href=\"".$scriptPath."/memberlist.php?mode=viewprofile&u=".$row['poster_id']." \" />".$row['username']."</a><br/>
							Posted at: ".date("d/m/Y, H:i",$row['post_time']).":</div>";	
						//building comment_text:
						$ret .=  '<div style="clear: both;"></div>';
						$uid = $row['bbcode_uid'];
						$bitfield = $row['bbcode_bitfield'];
						$row['post_text'] = wpu_censor($row['post_text']);  //IT WORKS!!! ;-)
						$row['post_text'] = generate_text_for_display($row['post_text'], $uid, $bitfield, 1);  //IT WORKS!!! ;-)
						$row['post_text'] = wpu_smilies($row['post_text'], $max = 0);  //IT DOESN'T WORK! WHY?? O_o
						
						$ret .=  "<div class=\"wpu_comment_text\">".$row['post_text']."</div>";
						$ret .=  '<div class="wpu_action">';
							$ret .=  '<a class="wpu_quote" href="'.$scriptPath.'/posting.php?mode=quote&f='.$row['forum_id'].'&p='.$row['post_id'].'" />Quote</a> ';
							$ret .=  '<a class="wpu_report" href="'.$scriptPath.'/report.php?f='.$row['forum_id'].'&p='.$row['post_id'].'" />Report</a>';
						$ret .=  '</div>';
					$ret .=  "</li>";
					
					} //end if
					$i++;
				} //end while
			$ret .=  '</ul>';
					echo '<div class="wpu_reply"><a href="'.$link.'"/>Reply</a></div>';
				$db->sql_freeresult($result);
				
				//get max post_id (for comment form topic_cur_post_id value)
				$sql = 'SELECT MAX(post_id) AS max FROM ' . POSTS_TABLE;
				if ($result = $db->sql_query($sql)) {
					$row = $db->sql_fetchrow($result);
					$phpbb_cur_post_id = $row['max']+1;
				}
				$db->sql_freeresult($result);
				$GLOBALS['wpUtdInt']->switch_db('TO_W');
				//-----get max post_id
				
				//check if user is logged in
				$usrName = get_wpu_phpbb_username();

				if ( $usrName == $wpuAbs->lang('Guest') ) {
					$ret .=  __('You must be logged in to comment.');
				} else {
					//user is logged, show comment form:
					$ret .= '<form action="'.$link.'" method="post">
					<input type="hidden" id="submit" value="Re: '.get_the_title().'" name="subject" />
					<textarea class="inputbox" tabindex="3" cols="76" rows="7" name="message" style="height: 9em;"></textarea>
					<input type="hidden" value="'.time().'" name="creation_time" />
					<input type="hidden" value="'.$token.'" name="form_token" />
					<input type="hidden" value="'.$phpbb_cur_post_id.'" name="topic_cur_post_id" />
					<input type="hidden" value="'.time().'" name="lastclick" />
					'.$hiddenvalue.'
					<input type="hidden" value="1" name="attach_sig" />
					<input class="button1" type="submit" value="Submit" name="post" tabindex="6" accesskey="s" />
					<input class="button2" type="submit" value="Preview" name="full_editor" tabindex="6" accesskey="f" />
					</form>';
				}
			}
		
	} else {
		//return default wordpress comments:
		$ret .= '<ol class="commentlist">'.wp_list_comments().'</ol>';
	}
	return $ret;
}




/**
 * Helper / Private functions
 */

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
 * Load the rank details for the user
 * @access private
 */
function _wpu_get_user_rank_info($userID = '') {

	global $wpuAbs, $scriptPath;
	$GLOBALS['wpUtdInt']->switch_db('TO_P');
	$rank = $wpuAbs->get_user_rank_info($userID);
	$GLOBALS['wpUtdInt']->switch_db('TO_W');
	$rank['image'] = (empty($rank['image'])) ? '' : $scriptPath . $rank['image'];
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