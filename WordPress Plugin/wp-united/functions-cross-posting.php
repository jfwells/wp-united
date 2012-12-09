<?php

/** 
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* Cross-post helper functions for wpu-plugin
*/

/**
 */
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) {
	exit;
}


/**
 *  Display the relevant cross-posting box, and store the permissions list in global vars for future use.
 * For WP >= 2.5, we set the approproate callback function. 
 */
function wpu_add_xposting_box() {
	global $phpbbForum, $wpUnited, $can_xpost_forumlist, $already_xposted, $forceXPosting;

	if($wpUnited->get_setting('xpostforce') > -1) {
		// Add forced xposting info box
		$forceXPosting = wpu_get_forced_forum_name($wpUnited->get_setting('xpostforce'));
		if($forceXPosting !== false) {
			add_meta_box('postWPUstatusdiv', __('Forum Posting', 'wpu-cross-post', 'wp-united'), 'wpu_add_forcebox', 'post', 'side');
		}
	} else {	
		// Add xposting choice box
		if ( !($already_xposted = wpu_get_xposted_details()) ) { 
			$can_xpost_forumlist = wpu_forum_xpost_list(); 
		}

		if ( (sizeof($can_xpost_forumlist)) || $already_xposted ) {
			add_meta_box('postWPUstatusdiv', __('Cross-post to Forums?', 'wpu-cross-post', 'wp-united'), 'wpu_add_postboxes', 'post', 'side');
		}
	}
}




/**
 * Callback function to add box to the write/(edit) post page.
 */
function wpu_add_postboxes() {
	global $can_xpost_forumlist, $already_xposted, $wpUnited;
?>
	<div id="wpuxpostdiv" class="inside">
	<?php if ($already_xposted) echo '<strong><small>' . sprintf(__('Already cross-posted (Topic ID = %s)', 'wp-united'), $already_xposted['topic_id']) . "</small></strong><br /> <input type=\"hidden\" name=\"wpu_already_xposted_post\" value=\"{$already_xposted['post_id']}\" /><input type=\"hidden\" name=\"wpu_already_xposted_forum\" value=\"{$already_xposted['forum_id']}\" />"; ?>
	<label for="wpu_chkxpost" class="selectit">
		<input type="checkbox" <?php if ($already_xposted) echo 'disabled="disabled" checked="checked"'; ?>name="chk_wpuxpost" id="wpu_chkxpost" value="1001" />
		<?php _e('Cross-post to Forums?', 'wp-united'); ?><br />
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
				echo '<br /><input type="radio" name="rad_xpost_type" value="excerpt" ' . $excerptState . ' />' . __('Post Excerpt', 'wp-united'). '<br />';
				echo '<input type="radio" name="rad_xpost_type" value="fullpost" ' . $fullState . ' />' . __('Post Full Post', 'wp-united');
			} ?>

	</div>
<?php
}

/**
 * Adds a "Force cross-posting" info box
 */
function wpu_add_forcebox($forumName) {
	global $forceXPosting, $phpbbForum, $wpUnited;

	$showText =  (wpu_get_xposted_details()) ? __("This post is already cross-posted. It will be edited in forum '%s'", 'wp-united') : __("This post will be cross-posted to the forum: '%s'", 'wp-united');

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
				echo '<br /><input type="radio" name="rad_xpost_type" value="excerpt" ' . $excerptState . ' />' . __('Post Excerpt', 'wp-united'). '<br />';
				echo '<input type="radio" name="rad_xpost_type" value="fullpost" ' . $fullState . ' />' . __('Post Full Post', 'wp-united');
			} ?>
	</div>
<?php
}


/**
 * Cross-posts a blog-post that was just added, to the relevant forum
 */
function wpu_do_crosspost($postID, $post, $future=false) {
	global $wpUnited, $phpbbForum, $phpbb_root_path, $phpEx, $db, $auth;
	$forum_id = false;
	$found_future_xpost = false;
	
	$fStateChanged = $phpbbForum->foreground();
	
	if ( (isset($_POST['sel_wpuxpost'])) && (isset($_POST['chk_wpuxpost'])) ) {
		$forum_id = (int)$_POST['sel_wpuxpost'];
	} else if ( $wpUnited->get_setting('xpostforce') > -1 ) {
		$forum_id = $wpUnited->get_setting('xpostforce');
	} else if($future) {
		$phpbbForum->background();
		$forum_id = get_post_meta($postID, '_wpu_future_xpost', true);
		if ($forum_id === '')  {
			$forum_id = false;
		} else {
			$forum_id = (int)$forum_id;
			delete_post_meta($postID, '_wpu_future_xpost');
		}
		$phpbbForum->foreground();
	}
	

	
	// If this is already cross-posted, then edit the post
	$details = wpu_get_xposted_details($postID);
	if(($forum_id === false) && ($details === false)) {
		$phpbbForum->restore_state($fStateChanged);
		return false;
	}
	
	$mode = 'post';
	$subject = __('[BLOG] ', 'wp-united') . $post->post_title;
	$data = array();
	$data['post_time'] = 0;
	$topicUsername = $phpbbForum->get_username();
	if($details !== false) {
		if(isset($details['post_id'])) {
			$mode = 'edit';
			//$subject = $details['post_subject']; // commented, because we may want to edit the post title after xposting
			$forum_id = $details['forum_id'];
			$data['topic_id'] = $details['topic_id'];
			$data['post_id'] = $details['post_id'];
			$data['poster_id'] = $details['poster_id'];
			$data['post_time'] = $details['post_time'];
			$data['topic_type'] = $details['topic_type'];
			$topicUsername = $details['topic_first_poster_name'];
		}
	}
	
	// If this is a future xpost, authenticate as the user who made the post
	if($future) {
		// get phpBB user ID (from WP meta, so need to exit phpBB env)
		$phpbbForum->background();
		$phpbbID = get_wpu_user_id($post->post_author);
		$phpbbIP =  get_post_meta($postID, '_wpu_future_ip', true);
		delete_post_meta($postID, '_wpu_future_ip');
		$phpbbForum->foreground();
		$phpbbForum->transition_user($phpbbID, $phpbbIP);
	}

	//Check that user has the authority to cross-post there
	// If we are editing a post, check other permissions if it has been made global/sticky etc.
	if($mode == 'edit') {
		if( ($data['topic_type'] == POST_GLOBAL)  && (!$auth->acl_getf('f_announce', 0)) )  {
			wp_die(__('You do not have permission required to edit global announcements', 'wp-united'));		
		}
		
		if( ($data['topic_type'] == POST_ANNOUNCE) && (!$auth->acl_getf('f_announce', $forum_id)) )  {
			wp_die(__('You do not have the permission required to edit this announcement', 'wp-united'));
		}
		if( ($data['topic_type'] == POST_STICKY) && (!$auth->acl_getf('f_sticky', $forum_id)) ) {
			wp_die(__('You do not have the permission required to edit stickies', 'wp-united'));
		}
		
		if(!$auth->acl_getf('f_edit', $forum_id)) {
			wp_die(__('You do not have the permission required to edit posts in this forum', 'wp-united'));		
		}
		
	}
	
	if($forum_id > 0) {
		$can_crosspost_list = wpu_forum_xpost_list(); 
		
		if ( !in_array($forum_id, (array)$can_crosspost_list['forum_id']) ) { 
			$phpbbForum->restore_state($fStateChanged);
			return false;
		}
	}
	$phpbbForum->background();
	$content = $post->post_content;
	
	// should we post an excerpt, or a full post?
	$postType = 'excerpt';
	if($wpUnited->get_setting('xposttype') == 'ASKME') { 
		if (isset($_POST['rad_xpost_type'])) {
			$postType = ($_POST['rad_xpost_type'] == 'fullpost') ? 'fullpost' : 'excerpt';
		}
	} else if($wpUnited->get_setting('xposttype') == 'FULLPOST') {
		$postType = 'fullpost';
	}
	update_post_meta($postID, '_wpu_posttype', $postType);
	
	// Get the post excerpt
	if($postType == 'excerpt') {
		if (!$excerpt = $post->post_excerpt) {
			if ( preg_match('/<!--more(.*?)?-->/', $content, $matches) ) {
				$excerpt = explode($matches[0], $content, 2);
				$content = $excerpt[0];
			}
		}	
	}
	
	if(defined('WPU_SHOW_TAGCATS') && WPU_SHOW_TAGCATS) {
		$cat_list = $tag_list = '';
	} else {				
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
			$tag_list = __('No tags defined.', 'wp-united');
		}
		
		$tags = (!empty($tag_list)) ? '[b]' . __('Tags: ', 'wp-united') .  "[/b]{$tag_list}\n" : '';
		$cats = (!empty($cat_list)) ? '[b]' . __('Posted under: ', 'wp-united') . "[/b]{$cat_list}\n" : '';
	}
	
	$content = sprintf(__('This is a %1$sblog post%2$s. To read the original post, please %3sclick here &raquo;%4s', 'wp-united'), '[b]', '[/b]', '[url=' . get_permalink($postID) . ']', '[/url]') . "\n\n" . $content . "\n\n" . $tags . $cats;

	$phpbbForum->foreground();

	$content = utf8_normalize_nfc($content, '', true);
	$subject = utf8_normalize_nfc($subject, '', true);
	
	
	wpu_html_to_bbcode($content, 0); //$uid=0, but will get removed)
	$uid = $poll = $bitfield = $options = ''; 
	generate_text_for_storage($content, $uid, $bitfield, $options, true, true, true);
		 
	require_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
	

	$data = array_merge($data, array(
		'forum_id' => $forum_id,
		'icon_id' => false,
		'enable_bbcode' => true,
		'enable_smilies' => true,
		'enable_urls' => true,
		'enable_sig' => true,
		'message' => $content,
		'message_md5' => md5($content),
		'bbcode_bitfield' => $bitfield,
		'bbcode_uid' => $uid,
		'post_edit_locked'	=> ITEM_LOCKED,
		'topic_title'		=> $subject,
		'notify_set'		=> false,
		'notify'			=> false,
		'enable_indexing'	=> true,
	)); 

	$topic_url = submit_post($mode, $subject, $topicUsername, POST_NORMAL, $poll, $data);
	
	// If this is a future xpost, switch back to current user
	if($future) {
		$phpbbForum->transition_user();
	}
	
	//Update the posts table with WP post ID so we can remain "in sync" with it, and set the post time/date
	if(($data !== false) && ($mode == 'post') && (!empty($data['post_id'])) ) {
			
			// Get timestamp for WP's gmt date. the fallback options won't give correct timezones, but should
			// only get used if the WP time is really unexpected (e.g. broken by a plugin).
			// We need to set this because this could be a past or a future post
			$utcTime = strtotime($post->post_date_gmt . ' UTC');
			$utcTime = 		($utcTime === false) 	? 	strtotime($post->post_date) : $utcTime;
			$utcTimeSql = 	($utcTime !== false) 	?  ', post_time =' . $utcTime 		: '' ;
			
			$sql = 'UPDATE ' . POSTS_TABLE . ' SET post_wpu_xpost = ' . $postID .  "{$utcTimeSql} WHERE post_id = {$data['post_id']}";
			if (!$result = $db->sql_query($sql)) {
				wp_die(__('Could not access the WP-United database fields. Please ensure WP-United is installed correctly. ', 'wp-united'));
			}
			if($utcTime !== false) {
				$sql = 'UPDATE ' . TOPICS_TABLE . " SET topic_time = {$utcTime} WHERE topic_id = {$data['topic_id']}";
				$result = $db->sql_query($sql);		
			}	
			$db->sql_freeresult($result);
			$phpbbForum->restore_state($fStateChanged);
			return true;
	} else if ( ($mode == 'edit') && (!empty($data['topic_id'])) ) {
		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET topic_type = ' . $data['topic_type'] . " WHERE topic_id = {$data['topic_id']}";
			$result = $db->sql_query($sql);			
			$db->sql_freeresult($result);
	}
	$phpbbForum->restore_state($fStateChanged);
}

/**
 * Get the list of forums we can cross-post to
 */
function wpu_forum_xpost_list() {
	global $phpbbForum, $user, $auth, $db, $userdata, $template, $phpEx;
	
	$fStateChanged = $phpbbForum->foreground();
	
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
			$phpbbForum->restore_state($fStateChanged);
			return $can_xpost_forumlist;
		}
	}
	$phpbbForum->restore_state($fStateChanged);
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
	
	global $phpbbForum, $db, $phpbbForum;
	
	$fStateChanged = $phpbbForum->foreground();
	
	$sql = 'SELECT p.topic_id, p.post_id, p.post_subject, p.forum_id, p.poster_id, t.topic_replies, t.topic_time, t.topic_approved, t.topic_type, t.topic_status, t.topic_first_poster_name, f.forum_name FROM ' . POSTS_TABLE . ' AS p, ' . TOPICS_TABLE . ' AS t, ' . FORUMS_TABLE . ' AS f WHERE ' .
		"p.post_wpu_xpost = $postID AND " .
		't.topic_id = p.topic_id AND (' .
		'f.forum_id = p.forum_id OR ' .
		'p.forum_id = 0)';
	if ($result = $db->sql_query_limit($sql, 1)) {
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		
		if(!empty($row['post_id'])) {
			if($row['topic_type'] == POST_GLOBAL) {
				$row['forum_name'] = $phpbbForum->lang['VIEW_TOPIC_GLOBAL'];
			}
			$phpbbForum->restore_state($fStateChanged);
			return $row;
		}

	}
	$phpbbForum->restore_state($fStateChanged);
	return false;
}

/**
 * Returns the forced xposting forum name from an ID, or false if it does not exist or cannot be posted to
 */
function wpu_get_forced_forum_name($forumID) {
	global $user, $db, $auth, $phpbbForum;
	
	$fStateChanged = $phpbbForum->foreground();
	
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
	$phpbbForum->restore_state($fStateChanged);
	
	return (empty($forumName)) ? false : $forumName;
	
}

/**
 * Loads comments from phpBB rather than WordPress
 * if Xpost-autoloading is on
 * @since v0.8.0
 */
function wpu_load_phpbb_comments($commentArray, $postID) {
	global $wpUnited, $comments, $wp_query, $overridden_cpage, $usePhpBBComments;

	if ( 
		(!$wpUnited->is_enabled()) || 
		(!$wpUnited->get_setting('xposting')) || 
		(!$wpUnited->get_setting('xpostautolink')) ||
		(empty($postID))
	) {
		return $commentArray;
	}
	
	require_once($wpUnited->get_plugin_path() . 'comments.php');

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

	// remove the WP make clickable filter, smilies, etc, as we've already done it
	remove_filter('comment_text', 'make_clickable', 9);
	
	return $comments;
}

/**
 * Returns the number of follow-up posts in phpBB in response to the cross-posted blog post
 * @since v0.8.0
 * @param int $count a WordPress comment count to be returned if the post is not cross-posted
 * @param int $postID the WordPress post ID
 */
function wpu_comments_count($count, $postID = false) {
	global $wp_query, $usePhpBBComments, $phpbbForum, $wpUnited;

	// In WP < 2.9, $postID is not provided
	if($postID === false) {
		global $id;
		$postID = (int) $id;
	}

	// if we already have the xposted details, use those
	if ( !empty($usePhpBBComments) ) {
		return sizeof($wp_query->comments);
	} 
	// else, get the details
	if ( 
		(!$wpUnited->is_enabled()) || 
		(!$wpUnited->get_setting('integrateLogin')) || 
		(!$wpUnited->get_setting('xposting')) || 
		(!$wpUnited->get_setting('xpostautolink')) 
	) {
		return $count;
	}
	$fStateChanged = $phpbbForum->foreground();
	
	if ( $xPostDetails = wpu_get_xposted_details($postID) ) { 
		$count = $xPostDetails['topic_replies'];
	}

	$phpbbForum->restore_state($fStateChanged);
	
	return $count;

}


/**
 * If the blog post is cross-posted, and comments are redirected from phpBB,
 * this catches posted comments and sends them to the forum
 */
function wpu_comment_redirector($postID) {
	global $wpUnited, $phpbb_root_path, $phpEx, $phpbbForum, $xPostDetails, $auth, $user;
	if ( 
		(!$wpUnited->is_enabled()) || 
		(!$wpUnited->get_setting('integrateLogin')) || 
		(!$wpUnited->get_setting('xposting')) || 
		(!$wpUnited->get_setting('xpostautolink')) 
	) {
		return;
	}

	$fStateChanged = $phpbbForum->foreground();


	if ( !$xPostDetails = wpu_get_xposted_details($postID) ) { 
		$phpbbForum->restore_state($fStateChanged);
		return;
	}
	
	
	if ($phpbbForum->user_logged_in()) {
		$username = $phpbbForum->get_username();
	} else {
		$username = strip_tags(stripslashes(request_var('author', 'Anonymous')));
		$username = wpu_find_next_avail_name($username, 'phpbb');
	}
	
	if( empty($xPostDetails['topic_approved'])) {
		wp_die($phpbbForum->lang['ITEM_LOCKED']);
	}
	
	if( $xPostDetails['topic_status'] == ITEM_LOCKED) {
		wp_die($phpbbForum->lang['TOPIC_LOCKED']);
	}

	if ($xPostDetails['forum_id'] == 0) {
		// global announcement
		if(!$auth->acl_getf_global('f_reply') ) {
			wp_die( __('You do not have permission to respond to this announcement', 'wp-united'));			
		}
	} else {
		if (!$auth->acl_get('f_reply', $xPostDetails['forum_id'])) { 
			wp_die( __('You do not have permission to comment in this forum', 'wp-united'));
		}
	}
	$content = ( isset($_POST['comment']) ) ? trim($_POST['comment']) : null;
	
	if(empty($content)) {
		wp_die(__('Error: Please type a comment!', 'wp-united'));
	}
	
	wpu_html_to_bbcode($content); 
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
		'topic_title' => $subject
	); 

	$postUrl = submit_post('reply', $subject, $username, POST_NORMAL, $poll, $data);

	$phpbbForum->restore_state($fStateChanged);
	
	
/**
 * Redirect back to WP if we can.
 * NOTE: if the comment was the first on a new page, this will redirect to the old page, rather than the new
 * one. 
 * @todo: increment page var if necessary, or remove it if comment order is reversed, by adding hidden field with # of comments
 */
	if (!empty($_POST['wpu-comment-redirect'])) {
		$location  = urldecode($_POST['wpu-comment-redirect']) . '#comment-' . $data['post_id'];
	} else if(!empty($_POST['redirect_to'])) {
		$location = $_POST['redirect_to'] . '#comment-' . $data['post_id'];
	} else {
		$location = str_replace(array('&amp;', $phpbb_root_path), array('&', $phpbbForum->get_board_url()), $postUrl);
	}
	wp_redirect($location); exit();
}

/**
 * Creates a redirect field for the comments box if the post is cross-posted and comments are to be.
 * This is the only way we can know how to get back here when posting.
 */
function wpu_comment_redir_field() {
		global $usePhpBBComments, $wp_query;
	
	if($usePhpBBComments) {
		$commID =  sizeof($wp_query->comments) + 1;
		$redir =  wpu_get_redirect_link(); // . '#comment-' $commID;
		echo '<input type="hidden" name="wpu-comment-redirect" value="' . $redir . '" />';
	}
	
}


/**
 * Modifies the "(Edit)" comment links and text for cross-posted comments
 * Checking whether the user can edit individual posts is too onerous
 * So we change the link to a "View in forum" one
 */
function wpu_edit_comment_link($link, $comment_ID) {
	global $phpbbForum,  $usePhpBBComments, $phpbbCommentLinks;
	
	if($usePhpBBComments) {
		if(!isset($phpbbCommentLinks[$comment_ID])) {
			return $link;
		}
		$href = $phpbbCommentLinks[$comment_ID];
		return '<a class="comment-edit-link" href="' . $href . '" title="' . __('(View in Forum)', 'wp-united') . '">' . __('(View in Forum)', 'wp-united'). '</a>';

	}
	
	return $link;
	
}
/**
 * Returns the general coment link -- points to the forum if the comment is cross-posted
 */
function wpu_comment_link($url, $comment, $args) {
	global $phpbbForum,  $usePhpBBComments, $phpbbCommentLinks;

	if($usePhpBBComments) {
		if(isset($phpbbCommentLinks[$comment->comment_ID])) {
			return $phpbbCommentLinks[$comment->comment_ID];
		}
	}
	return $url;
}

/**
 * returns if the comments box should display or not
 * 
 */
function wpu_comments_open($open, $postID) {
	global $wpUnited, $phpbb_root_path, $phpEx, $phpbbForum, $auth, $user, $wpuPermsProblem;
	static $status;
	
	if(isset($status)) {
		return $status;
	}
	
	$wpuPermsProblem = false;
	
	if($wpUnited->should_do_action('template-p-in-w')) {
		$status = false;
		return $status;
	}
	
	if($postID == NULL) {
		$postID = $GLOBALS['post']->ID;
	}
	
	if ( 
		(!$wpUnited->is_enabled()) || 
		(!$wpUnited->get_setting('integrateLogin')) || 
		(!$wpUnited->get_setting('xposting')) || 
		(!$wpUnited->get_setting('xpostautolink')) 
	) {
		$status = $open;
		return $status;
	}
	
	
	$fStateChanged = $phpbbForum->foreground();
	if(!($dets = wpu_get_xposted_details($postID))) {
		$phpbbForum->restore_state($fStateChanged);
		$status = $open;
		return $status;			
	}

	$permsProblem = false;

	if (
		(empty($dets['topic_approved'])) || 
		($dets['topic_status'] == ITEM_LOCKED) ||
		(($dets['forum_id'] == 0) && (!$auth->acl_getf_global('f_reply'))) || // global announcement
		(!$auth->acl_get('f_reply', $dets['forum_id']) )
	) { 
			$permsProblem = true;		
		}

	$phpbbForum->restore_state($fStateChanged);
	
	/** if user is logged out, we need to return default wordpress comment status
	 * Then the template can display "you need to log in", as opposed to "comments are closed"
	 */
	if($permsProblem && !$phpbbForum->user_logged_in()) { 
		$wpuPermsProblem = true;
		$status = $open;
		return $status;
	}

	
	$status = true;
	return $status;
}

/**
 * This short-circuits the WordPress get_option('comment_registration')
 * We only want to intervene if we are in the midst of a cross-posted comment
 * If we return false, get_option does its thing.
 */
function wpu_no_guest_comment_posting() {
	global $usePhpBBComments, $wpuPermsProblem;
	
	// users don't need to register if permissions have already been resolved for them
	if($usePhpBBComments) {
		return $wpuPermsProblem;
	}
	

	
	return false;
}


/**
 * Catches posts scheduled for future publishing
 * Since these posts won't retain the cross-posting HTTP vars, we add a post meta to future posts
 */
function wpu_capture_future_post($postID, $post) {
	global $wpUnited, $phpbbForum;
	
	if ( ($post->post_status == 'future') && ($wpUnited->get_setting('integrateLogin')) ) {
		if ( ($phpbbForum->user_logged_in()) && ($wpUnited->get_setting('xposting')) ) {
			// If x-post forcing is turned on, we don't need to do anything
			if( $wpUnited->get_setting('xpostforce') == -1) {
				if ( (isset($_POST['sel_wpuxpost'])) && (isset($_POST['chk_wpuxpost'])) ) {
					
					$forumID = (int)$_POST['sel_wpuxpost'];
					
					//only needs doing once
					if(get_post_meta($postID, '_wpu_future_xpost', true) === $forumID) {
						return;
					}
					
					// Need to check authority here -- as we won't know for sure when the time comes to xpost
					$can_crosspost_list = wpu_forum_xpost_list(); 
					
					if ( !in_array($forumID, (array)$can_crosspost_list['forum_id']) ) { 
						return;
					}
					update_post_meta($postID, '_wpu_future_xpost', $forumID);
					update_post_meta($postID, '_wpu_future_ip', $phpbbForum->get_userip());
				}
			} else {
				update_post_meta($postID, '_wpu_future_ip', $phpbbForum->get_userip());
			}
		}
	}
}


?>
