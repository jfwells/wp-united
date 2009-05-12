<?php
/** 
*
* WP-United WordPress template tags
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v0.6.0[phpBB3] 2007/12/01 John Wells (Jhong) Exp $
* @copyright (c) 2006, 2007 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
//
// WordPress template functions -- see the readme included in the mod download for how to use these.
//


if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}


//
// 	WPU_REGISTER_INTRO
//	-----------------------------
//	Displays a sentence soliciting users to get started with their blogs
//

function wpu_intro() {
 echo get_wpu_intro();
}

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


//SHOW A LIST OF PHPBB BLOGS
//displays a nice paginated list of phpBB blogs
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
				$blogList .=  '<small class="wpublastpost">' . __('Last Entry:') . ' <a href="' . $lastPostURL . '">' . $lastPostTitle . '</a>, posted on '. "$time</small><br />\n\n";
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

function wpu_bloglist($showAvatars = TRUE, $maxEntries = 10) {
	echo wpu_bloglist($showAvatars, $maxEntries);
}



// INSERT COMMENTER AVATAR
//Inserts the commenter's phpBB avatar

function avatar_commenter($default = TRUE, $id = '') {
	echo get_avatar_commenter($default, $id);
}

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



// INSERT AUTHOR AVATAR
//Inserts the author's phpBB avatar

function avatar_poster($default = TRUE) {
	echo get_avatar_poster($default);
}

function get_avatar_poster($default = TRUE) {
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




// INSERT USER AVATAR
//Inserts the user's phpBB avatar
//
function avatar_reader($default = TRUE) {
	echo get_avatar_reader($default);
}

function get_avatar_reader($default = TRUE) {
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



// AVATAR_CREATE_IMAGE
// Creates a suitable avatar image

function avatar_create_image($user) {
	$avatar = '';
	if ( !empty($user->ID) ) {
		global $wpuAbs, $scriptPath, $phpbb_root_path, $phpEx;
		if ($wpuAbs->ver == 'PHPBB2') {
			$avPath = $scriptPath . $wpuAbs->config('avatar_path');
			$gallPath = $scriptPath . $wpuAbs->config('avatar_gallery_path');
			if ( $user->wpu_avatar_type && $user->wpu_allowavatar ) {
				switch( $user->wpu_avatar_type ) {
					case USER_AVATAR_UPLOAD:
						$avatar = ( $wpuAbs->config('allow_avatar_upload') ) ? $avPath . '/' . $user->wpu_avatar  : '';
						break;
					case USER_AVATAR_REMOTE:
						$avatar = ( $wpuAbs->config('allow_avatar_remote') ) ? $user->wpu_avatar  : '';
						break;
					case USER_AVATAR_GALLERY:
						$avatar = ( $wpuAbs->config('allow_avatar_local') ) ? $gallPath . '/' . $user->wpu_avatar : '';
						break;
				}
			}
		} else {
			if ($user->wpu_avatar_type && $user->wpu_avatar) {
				require_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);
				$avatar = get_user_avatar($user->wpu_avatar, $user->wpu_avatar_type, $user->wpu_avatar_width, $user->wpu_avatar_height);
				$avatar = explode('"', $avatar);
				$avatar = str_replace($phpbb_root_path, $scriptPath, $avatar[1]); //stops trailing slashes in URI from killing avatars
			}
		}
	} 
	return $avatar;
}

function wpu_blogs_home() {
	echo get_wpu_blogs_home();
}

function get_wpu_blogs_home() {
	global $wpSettings;
	$postContent = get_wpu_intro(); 
	$postContent .= get_wpu_bloglist(TRUE, $wpSettings['blogsPerPage']); 
	return $postContent;
}


//
//	LATEST UPDATED BLOGS
//	get_wpu_latest_blogs
//	based on contribution by  Quentin, qsc@mypozzie.co.za 
//
//	wpu_latest_blogs('limit=20&before=<li>&after=</li>');
//

function wpu_latest_blogs($args = '') {
	echo get_wpu_latest_blogs($args);
}
	
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

//
//	LATEST BLOGS AND POSTS
//	get_wpu_latest_blogposts
//
//	wpu_latest_blogs('limit=20&before=<li>&after=</li>');
//

function wpu_latest_blogposts($args = '') {
	echo get_wpu_latest_blogposts($args);
}
	
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

// INSERT PHPBB USERNAME
//Inserts the user's phpBB username if they are logged in, or displays 'Guest' if not.

function wpu_phpbb_username() {
	echo get_wpu_phpbb_username();
}

function get_wpu_phpbb_username() {
	global $wpuAbs;
	$usrName = '';
	if ( $wpuAbs->user_logged_in() ) {
		$usrName = $wpuAbs->phpbb_username();
	} 
	return ($usrName == '') ? $wpuAbs->lang('Guest') : $usrName;
}

//	PHPBB PROFILE LINK
//	Returns a link to the users' phpBB profile
//	Will NOT work with older (phpBB2) versions of WP-United unless properly upgraded
function wpu_phpbb_profile_link($wpID = '') {
	echo get_wpu_phpbb_profile_link($wpID);
}

function get_wpu_phpbb_profile_link($wpID = '') {
	$phpbb_usr_id = get_usermeta($wpID, 'phpbb_userid');
	if (!empty($usr_data)) {
		$profile_path = ($wpuAbs->ver == 'PHPBB2') ? "profile.$phpEx" : "memberlist.$phpEx";
		return add_trailing_slash($scriptPath) . "$profile_path?mode=viewprofile&amp;u=" . $phpbb_usr_id;
	}
}

//
//	PHPBB USER RANKS
//	The below tags all deal with ranks in some form or another
//	wpu_phpbb_ranktitle: The title of the rank
//	wpu_phpbb_rankimage: The rank image
//	wpu_phpbb_rankblock : Both the title and image in a 'lockup'
//	$wpID can be passed in to retrieve a specific user's rank, otherwise we retrieve them for the reader (logged in user). $wpID  is the WORDPRESS user ID!!!
//
function wpu_phpbb_ranktitle($wpID = '') {
	echo get_wpu_phpbb_ranktitle($wpID);
}

function get_wpu_phpbb_ranktitle($wpID = '') {
	$rank = _wpu_get_user_rank_info($wpID);
	if ( $rank ) {
		return $rank['text'];
	}
}

function wpu_phpbb_rankimage($wpID = '') {
	echo get_wpu_phpbb_rankimage($wpID);
}

function get_wpu_phpbb_rankimage($wpID = '') {
	$rank = _wpu_get_user_rank_info($wpID);
	if ( $rank ) {
		return $rank['image'];
	}
}

function wpu_phpbb_rankblock($wpID = '') {
	echo get_wpu_phpbb_rankblock($wpID);
}

function get_wpu_phpbb_rankblock($wpID = '') {
	$rank = _wpu_get_user_rank_info($wpID);
	if ( $rank ) {
		$block = '<p>' . $rank['text'];
		if ( $rank['image'] ) {
			$block .= '<br />' . '<img src="' . $rank['image'] . '" alt="' . __('rank') . '" />';
		}
		$block .= '</p>';
		return $block;
	}
}




//
//	PHPBB FORUM STATS
//	-----------------------------
//	RETURNS A LIST OF FORUM STATS
//	wpu_phpbb_stats('limit=20&before=<li>&after=</li>');
//
function wpu_phpbb_stats($args='') {
	echo get_wpu_phpbb_stats($args);
}

function get_wpu_phpbb_stats($args='') {
	global $wpuAbs, $phpEx, $scriptPath;
	$defaults = array('before' => '<li>', 'after' => '</li>');
	extract(_wpu_process_args($args, $defaults));

	
	$profile_path = ($wpuAbs->ver == 'PHPBB2') ? "profile.$phpEx" : "memberlist.$phpEx";
	
	$GLOBALS['wpUtdInt']->switch_db('TO_P');
	$output .= $before .  __('Forum Posts: ') 		. '<strong>' 	. $wpuAbs->stats('num_posts') 	. "</strong>$after\n";
	$output .= $before .  __('Forum Threads: ') 	. '<strong>' 	. $wpuAbs->stats('num_topics') . "</strong>$after\n";
	$output .= $before .  __('Registered Users: ') . '<strong>' 	. $wpuAbs->stats('num_users') 	. "</strong>$after\n";	
	   $output .= $before .  __('Newest User: ') . '<a href="' . add_trailing_slash($scriptPath) . "$profile_path?mode=viewprofile&amp;u=" . $wpuAbs->stats('newest_user_id') . '"><strong>' . $wpuAbs->stats('newest_username') . "</strong></a>$after\n";
	$GLOBALS['wpUtdInt']->switch_db('TO_W');
	return $output;

}




//
//	PHPBB POSTS SINCE LAST VISIT LINK
//	-----------------------------------------------------
//	Returns the # of posts since the user last visited with a link to the post search
//

function wpu_newposts_link() {
	echo get_wpu_newposts_link();
}

function get_wpu_newposts_link() {
	global $wpuAbs, $phpEx, $scriptPath;
	if( $wpuAbs->user_logged_in() ) {
		return '<a href="'. append_sid($scriptPath . 'search.'.$phpEx.'?search_id=newposts') . '">' . $wpuAbs->lang('Search_new') . "&nbsp;(" . get_wpu_newposts() . ")</a>";
	}
}

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


//
//	LATEST TOPICS
//	---------------------
//	Nice list of latest forum topics
//	wpu_latest_phpbb_posts('limit=10&forum=1,2,3&before=<li>&after=</li>')
function wpu_latest_phpbb_topics($args = '') {
	echo get_wpu_latest_phpbb_topics($args);
}

function get_wpu_latest_phpbb_topics($args = '') {
	global $scriptPath, $phpEx, $wpuAbs;
	$defaults = array('limit' => 10, 'before' => '<li>', 'after' => '</li>', 'forum' => '');
	extract(_wpu_process_args($args, $defaults));
	
	$limit = ($limit > 50 ) ? 50 : $limit;
	
	if ($posts = $wpuAbs->get_recent_topics($forum, $limit)) {
		$profile_path = ($wpuAbs->ver == 'PHPBB2') ? "profile.$phpEx" : "memberlist.$phpEx";
		foreach ($posts as $post) {
			$topic_link = '<a href="' . add_trailing_slash($scriptPath) . "viewtopic.$phpEx?t=" . $post['topic_id'] . '">' . $post['topic_title'] . '</a>';
			$forum_link = '<a href="' . add_trailing_slash($scriptPath) . "viewforum.$phpEx?f=" . $post['forum_id'] . '">' . $post['forum_name'] . '</a>';
			$user_link = '<a href="' . add_trailing_slash($scriptPath) . "$profile_path.$phpEx?mode=viewprofile&amp;u=" . $post['user_id'] . '">' . $post['username'] . '</a>';
			$output .= $before . sprintf(__('%1s, posted by %2s in %3s'),$topic_link, $user_link, $forum_link)  ."$after\n";
		}
	} else {
		$output = __('No topics to show');
	}
	return $output;
	
}






//
//
//	Helper / Private functions
//
//


//
//	RANK HELPER FUNCTIONS
//	These deal with loading in the rank info.
//
function _wpu_get_user_rank_info($userID = '') {

	global $wpuAbs, $scriptPath;
	$GLOBALS['wpUtdInt']->switch_db('TO_P');
	$rank = $wpuAbs->get_user_rank_info($userID);
	$GLOBALS['wpUtdInt']->switch_db('TO_W');
	$rank['image'] = (empty($rank['image'])) ? '' : $scriptPath . $rank['image'];
	return $rank;
}
	

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
