<?php

/** 
*
* @package WP-United
* @version $Id: phpbb.php,v0.8.0 2009/06/23 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
* Cross-post helper functions for wpu-plugin
*/

/**
 */
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) {
	exit;
}


/**
 * Cross-posts a blog-post that was just added, to the relevant forum
 */
function wpu_do_crosspost($postID, $post) {
	global $wpSettings, $phpbbForum, $phpbb_root_path, $phpEx, $db;
	
	$forum_id = false;
	if ( (isset($_POST['sel_wpuxpost'])) && (isset($_POST['chk_wpuxpost'])) ) {
		$forum_id = (int)$_POST['sel_wpuxpost'];
	} else if ($wpSettings['xpostforce'] > -1) {
		$forum_id = $wpSettings['xpostforce'];
	}

	$phpbbForum->enter();
	
	$mode = 'post';
	$subject = $phpbbForum->lang['blog_title_prefix'] . $post->post_title;
	$data = array();
	
	// If this is already cross-posted, then edit the post
	$details = wpu_get_xposted_details($postID);
	if(($forum_id === false) && ($details === false)) {
		return false;
	}
	
	if($details !== false) {
		if(isset($details['post_id'])) {
			$mode = 'edit';
			//$subject = $details['post_subject']; // commented, because we may want to edit the post title after xposting
			$forum_id = $details['forum_id'];
			$data['topic_id'] = $details['topic_id'];
			$data['post_id'] = $details['post_id'];
			$data['poster_id'] = $details['poster_id'];
		}
	}
	
	//Check that we have the authority to cross-post there
	$can_crosspost_list = wpu_forum_xpost_list(); 
	if ( !in_array($forum_id, (array)$can_crosspost_list['forum_id']) ) { 
		return false;
	}

	// Get the post excerpt
	if (!$excerpt = $post->post_excerpt) {
		$excerpt = $post->post_content;
		if ( preg_match('/<!--more(.*?)?-->/', $excerpt, $matches) ) {
			$excerpt = explode($matches[0], $excerpt, 2);
			$excerpt = $excerpt[0];
		}
	}	
							
	$cats = array(); $tags = array();
	$tag_list = ''; $cat_list = '';
	$cats = get_the_category($postID);
	if (sizeof($cats)) {
		foreach ($cats as $cat) {
			$cat_list .= (empty($cat_list)) ? $cat->cat_name :  ', ' . $cat->cat_name;
		}
	}
	
	$tag_list = '';
	$tag_list = get_the_term_list($post->ID, 'post_tag', '', ', ', '');
	if ($tag_list == "") {
	$tag_list = __('No tags defined.');
	}
	
	$tags = (!empty($tag_list)) ? "[b]{$phpbbForum->lang['blog_post_tags']}[/b]{$tag_list}\n" : '';
	$cats = (!empty($cat_list)) ? "[b]{$phpbbForum->lang['blog_post_cats']}[/b]{$cat_list}\n" : '';
	
	$phpbbForum->leave();
	$excerpt = sprintf($phpbbForum->lang['blog_post_intro'], '[url=' . get_permalink($postID) . ']', '[/url]') . "\n\n" . $excerpt . "\n\n" . $tags . $cats;

	sprintf($phpbbForum->lang['read_more'], '[url=' . get_permalink($postID) . ']', '[/url]');
	
	
	$excerpt = utf8_normalize_nfc($excerpt, '', true);
	$subject = utf8_normalize_nfc($subject, '', true);
	
	$phpbbForum->enter(); 
	
	wpu_html_to_bbcode($excerpt, 0); //$uid=0, but will get removed)
	$uid = $poll = $bitfield = $options = ''; 
	generate_text_for_storage($excerpt, $uid, $bitfield, $options, true, true, true);
		 
	require_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
	$data = array_merge($data, array(
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
	)); 

	$topic_url = submit_post($mode, $subject, $phpbbForum->get_username(), POST_NORMAL, $poll, $data);
	
	//Update the posts table with WP post ID so we can remain "in sync" with it.
	if(($data !== false) && ($mode == 'post')) {
		if ( !empty($data['post_id']) ) {
			$sql = 'UPDATE ' . POSTS_TABLE . ' SET post_wpu_xpost = ' . $postID . " WHERE post_id = {$data['post_id']}";
			if (!$result = $db->sql_query($sql)) {
				wp_die($phpbbForum->lang['WP_DBErr_Retrieve']);
			}
			$db->sql_freeresult($result);
			$phpbbForum->leave(); 
			return true;
		}
	}
	$phpbbForum->leave(); 
}

/**
 * Get the list of forums we can cross-post to
 */
function wpu_forum_xpost_list() {
	global $phpbbForum, $user, $auth, $db, $userdata, $template, $phpEx;
	
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
 * Determine if this post is already cross-posted. If it is, it returns an array of details
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
	
	$sql = 'SELECT p.topic_id, p.post_id, p.post_subject, p.forum_id, p.poster_id, f.forum_name, t.topic_replies, t.topic_approved, t.topic_status FROM ' . POSTS_TABLE . ' AS p, ' . TOPICS_TABLE . ' AS t, ' . FORUMS_TABLE . ' AS f WHERE ' .
		"p.post_wpu_xpost = $postID AND " .
		't.topic_id = p.topic_id and ' .
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
 * Returns the forced xposting forum name from an ID, or false if it does not exist or cannot be posted to
 */
function wpu_get_forced_forum_name($forumID) {
	global $user, $db, $auth, $phpbbForum;
	
	$phpbbForum->enter();
	
	$forumName = false;
	
	$can_xpost_to = $auth->acl_get_list($user->data['user_id'], 'f_wpu_xpost');
	
	if ( sizeof($can_xpost_to) ) { 
		$can_xpost_to = array_keys($can_xpost_to); 
	} 

	if(in_array($forumID, $can_xpost_to)) {
		
		$sql = 'SELECT forum_name FROM ' . FORUMS_TABLE . ' WHERE forum_id = ' . $forumID;
			
		if($result = $db->sql_query($sql)) {
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			if (isset($row['forum_name'])) {
				$forumName = $row['forum_name'];
			}
		}
	}
	$phpbbForum->leave();
	
	return (empty($forumName)) ? false : $forumName;
	
}

/**
 * Loads comments from phpBB rather than WordPress
 * if Xpost-autoloading is on
 * @since v0.8.0
 */
function wpu_load_phpbb_comments($commentArray, $postID) {
	global $wpSettings, $phpbb_root_path, $phpEx, $comments, $wp_query, $overridden_cpage, $usePhpBBComments;

	if ( 
		(empty($phpbb_root_path)) || 
		(empty($wpSettings['xposting'])) || 
		(empty($wpSettings['xpostautolink'])) 
	) {
		return $commentArray;
	}
	
	require_once($phpbb_root_path . 'wp-united/comments.' . $phpEx);

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

/**
 * Returns the number of follow-up posts in phpBB in response to the cross-posted blog post
 * @since v0.8.0
 * @param int $count a WordPress comment count to be returned if the post is not cross-posted
 * @param int $postID the WordPress post ID
 */
function wpu_comments_count($count, $postID) {
	global $wp_query, $usePhpBBComments, $phpbbForum, $wpSettings, $phpbb_root_path;

	// if we already have the xposted details, use those
	if ( !empty($usePhpBBComments) ) {
		return sizeof($wp_query->comments);
	} 
	// else, get the details
	if ( 
		(empty($phpbb_root_path)) || 
		(empty($wpSettings['xposting'])) || 
		(empty($wpSettings['xpostautolink'])) 
	) {
		return $count;
	}
	$phpbbForum->enter();
	
	if ( $xPostDetails = wpu_get_xposted_details($postID) ) { 
		$count = $xPostDetails['topic_replies'];
	}

	$phpbbForum->leave();
	
	return $count;

}


/**
 * If the blog post is cross-posted, and comments are redirected from phpBB,
 * this catches posted comments and sends them to the forum
 */
function wpu_comment_redirector($postID) {
	global $wpSettings, $phpbb_root_path, $phpEx, $phpbbForum;
	
	if ( 
		(empty($phpbb_root_path)) || 
		(empty($wpSettings['xposting'])) || 
		(empty($wpSettings['xpostautolink'])) 
	) {
		return false;
	}

	$phpbbForum->enter();	

	if(!$phpbbForum->user_logged_in()) {
		$phpbbForum->leave();
		return;
	}	

	if ( !$xPostDetails = wpu_get_xposted_details($postID) ) { 
		$phpbbForum->leave();
		return;
	}
	
	if( empty($xPostDetails['topic_approved'])) {
		wp_die($phpbbForum->lang['ITEM_LOCKED']);
	}
	
	if( $xPostDetails['topic_status'] == ITEM_LOCKED) {
		wp_die($phpbbForum->lang['TOPIC_LOCKED']);
	}

	$permissionsList = wpu_forum_xpost_list();  
	if ( !in_array($xPostDetails['forum_id'], (array)$permissionsList['forum_id']) ) { 
		$phpbbForum->leave();
		wp_die( __('You do not have permissions to comment in the forum'));
	}
	
	$content = ( isset($_POST['comment']) ) ? trim($_POST['comment']) : null;
	
	wpu_html_to_bbcode($content, 0); //$uid=0, but will get removed)
	$content = utf8_normalize_nfc($content);
	$uid = $poll = $bitfield = $options = ''; 
	generate_text_for_storage($content, $uid, $bitfield, $options, true, true, true);

	require_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
	
	$subject = $xPostDetails['post_subject'];
	
	$data = array(
		'forum_id' => $xPostDetails['forum_id'],
		'topic_id' => $xPostDetails['topic_id'],
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

	$postUrl = submit_post('reply', $subject, $phpbbForum->get_username(), POST_NORMAL, $poll, $data);

	$phpbbForum->leave();
	
	// We redirect back to the forum, because we cannot make a reliable WordPress comment link to redirect to.
	$location = str_replace(array('&amp;', $phpbb_root_path), array('&', $phpbbForum->url), $postUrl);
	wp_redirect($location); exit();
}

?>