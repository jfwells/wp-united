<?php
/** 
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* A comment object to store cross-posted comment results, and retrieve various other info
* 
*/

if ( !defined('IN_PHPBB') ) exit;

class WPU_Comments {
	
	var $comments;
	

	/**
	 * Class initialisartion
	 */
	function WPU_Comments() {
		$this->comments = array();
	}

	function populate($wpPostID) {
		
		global $phpbbForum, $phpbbCommentLinks, $auth, $db, $phpEx, $user, $phpbb_root_path;
		
		$phpbbForum->enter();
		
		$auth_f_read = array_keys($auth->acl_getf('f_read', true));
		
		/**
		 *  unfortunately xpost ID is in POSTS table rather than TOPICS table, so we need two queries :-(
		 */
		
		$sql = 'SELECT topic_id, forum_id from ' . POSTS_TABLE . ' WHERE post_wpu_xpost = ' . $wpPostID;

		if($result = $db->sql_query_limit($sql, 1)) {
			$dets = $db->sql_fetchrow($result);
			if(isset($dets['topic_id'])) {
				$topicID = (int)$dets['topic_id'];
			}
		} 
		
		if(!isset($topicID)) {
			$db->sql_freeresult($result);
			$phpbbForum->leave();
			return false;
		}
		
		// check permissions
		$canPost = false;
		if($dets['forum_id'] == 0) {
			// The topic has become a global announcement
			global $user;
			$forumList = $auth->acl_get_list($user->data['user_id'], 'f_noapprove');
                        if ( sizeof($forumList) ) {
                                $forumList = array_keys($forumList);
                                if(!empty($forumList[0])) {
					$canPost = true;
				}
                        }
		} else {
			$auth_f_read = array_keys($auth->acl_getf('f_read', true));
			if(sizeof($auth_f_read)) {
				if(in_array($dets['forum_id'], $auth_f_read)) {
					$canPost = true;
				}
			}
		}
		if(!$canPost) {
			$phpbbForum->leave();
			return false;
		}

		$sql = 'SELECT 
						p.post_id, 
						p.poster_id, 
						p.poster_ip, 
						p.post_time, 
						p.enable_bbcode, 
						p.enable_smilies, 
						p.enable_magic_url, 
						p.enable_sig, 
						p.post_username, 
						p.post_subject, 
						p.post_text, 
						p.bbcode_bitfield, 
						p.bbcode_uid, 
						p.post_edit_locked,
						p.topic_id,
						t.topic_replies AS all_replies,
						t.topic_replies_real AS replies, 
						u.username,
						u.user_wpuint_id,
						u.user_email 
					FROM ' . 
						TOPICS_TABLE . ' AS t , ' .
						POSTS_TABLE . ' AS p  INNER JOIN ' .
						USERS_TABLE . ' AS u ON 
						p.poster_id = u.user_id
					WHERE 
						p.topic_id = t.topic_id AND
						t.topic_id = ' . $topicID . ' AND 
						p.post_approved = 1 AND
						t.topic_replies_real > 0 AND 
						p.post_wpu_xpost IS NULL 
						ORDER BY p.post_id ASC';

				if(!($result = $db->sql_query($sql))) {
					$db->sql_freeresult($result);
					$phpbbForum->leave();
					return false;
				} 
				$phpbbCommentLinks = array();
				while ($comment = $db->sql_fetchrow($result)) {
					
					$comment['bbcode_options'] = (($comment['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
						(($comment['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
						(($comment['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
					
					$link = $phpbbForum->url . "memberlist.$phpEx?mode=viewprofile&amp;u=" . $comment['poster_id'];
					$args = array(
						'comment_ID' => $comment['post_id'],
						'comment_post_ID' => $wpPostID,
						'comment_author' => $comment['username'],
						'comment_author_email' => $comment['user_email'],
						'comment_author_url' => $link,
						'comment_author_IP' => $comment['poster_ip'],
						'comment_date' => $user->format_date($comment['post_time'], "Y-m-d H:i:s"), //Convert phpBB timestamp to mySQL datestamp
						'comment_date_gmt' =>  $user->format_date($comment['post_time'] - ($user->timezone + $user->dst), "Y-m-d H:i:s"), 
						'comment_content' => generate_text_for_display($comment['post_text'], $comment['bbcode_uid'], $comment['bbcode_bitfield'], $comment['bbcode_options']),
						'comment_karma' => 0,
						'comment_approved' => 1,
						'comment_agent' => 'phpBB forum',
						'comment_type' => '',
						'user_id' => $comment['user_wpuint_id'],
						'phpbb_id' => $comment['poster_id']
					);
					
					// Fix relative paths in comment text
					$pathsToFix = array('src="' . $phpbb_root_path, 'href="' . $phpbb_root_path);
					$pathsFixed = array('src="' . $phpbbForum->url, 'href="' . $phpbbForum->url);
					$args['comment_content'] = str_replace($pathsToFix, $pathsFixed, $args['comment_content']);
					
					$phpbbCommentLinks[$comment['post_id']] = $phpbbForum->url;
					$phpbbCommentLinks[$comment['post_id']] .= ($phpbbForum->seo) ? "post{$comment['post_id']}.html#p{$comment['post_id']}" : "viewtopic.{$phpEx}?f={$comment['forum_id']}&t={$comment['topic_id']}&p={$comment['post_id']}#p{$comment['post_id']}";
					
					$this->comments[] = new WPU_Comment($args);
				}
				$db->sql_freeresult($result);
				
				$phpbbForum->leave();

				return true;
	}
}


class WPU_Comment {

	var $comment_ID;
	var $comment_post_ID;
	var $comment_author;
	var $comment_author_email;
	var $comment_author_url;
	var $comment_author_IP;
	var $comment_date;
	var $comment_date_gmt;
	var $comment_content;
	var $comment_karma;
	var $comment_approved;
	var $comment_agent;
	var $comment_type;
	var $phpbb_id;
	var $user_id;
	
	/**
	 * Class initialisartion
	 * All required args must be passed in
	 * @param array $args: Array with all WordPress comment fields provided as 'variablename' => 'variablevalue'
	 */
	function WPU_Comment($args) {
		if(is_array($args)) {
			foreach($args as $argName => $argVal) {
				$this->$argName = $argVal;
			}
		}
	}
	
	
}

?>