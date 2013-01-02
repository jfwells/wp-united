<?php
/** 
*
* @package WP-United
* @version $Id: 0.9.1.5  2012/12/28 John Wells (Jhong) Exp $
* @copyright (c) 2006-2013 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* A comment object to store cross-posted comment results, and retrieve various other info
* 
*/

/**
*
*/
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) exit;

/**
 * A comment object to store cross-posted comment results, and retrieve various other info
 */
class WPU_Comments {
	
	private 
		$comments,
		$usingPhpBBComments,
		$limit,
		$offset,
		$postID;
		
	public $links;
	

	/**
	 * Class initialisation
	 */
	public function __construct() {
		$this->comments = array();
		$this->links = array();
		$this->usingPhpBBComments = false;
		$this->postID = 0;
		$this->limit = 25;
		$this->offset = 0;
		
	}
	

	public function populate_comments($query, $comments) {
				
		//print_r($query);
		
		
		//$query->query_vars['ID'];
		//$query->query_vars['orderby'];
		//$query->query_vars['order'];
		
		/** TODO: CHANGE THIS TO:
		 1. store wp comments and phpBB comments in separate array
		 2. on get_comments, sort & apply limits/offsets
		 */
		
		
		$this->postID = $query->query_vars['post_id'];
		$this->limit = $query->query_vars['number'];
		$this->offset = $query->query_vars['offset'];
		
		$this->populate_phpbb_comments();
		if(sizeof($this->comments)) {
			$result = true;
		}
		
		$this->add_wp_comments($comments);
		
		if($result) {
			$this->sort();
		}
	}
	
	public function populate_for_post($postID) {
		
		$this->postID = $postID;
		$this->limit = 10000;
		$this->offset = 0;
		
		return $this->populate_phpbb_comments();
		
	}
	

	public function get_comments() {
		return array_slice($this->comments, 0, $this->limit);
	}
	
	private function add_wp_comments($comments) {
		$this->comments = array_merge($comments, $this->comments);
	}

	private function populate_phpbb_comments() {
		
		global $phpbbForum, $auth, $db, $phpEx, $user, $phpbb_root_path;

		$fStateChanged = $phpbbForum->foreground();
		
		if(!empty($this->postID)) {
			$sql = 'SELECT topic_id, forum_id from ' . POSTS_TABLE . ' WHERE post_wpu_xpost = ' . $this->postID;
		
			if($result = $db->sql_query_limit($sql, 1)) {
				$dets = $db->sql_fetchrow($result);
				if(isset($dets['topic_id'])) {
					$topicID = (int)$dets['topic_id'];
					$forumID = (int)$dets['forum_id'];
				}
			} 
			$db->sql_freeresult($result);
		
			if(!isset($topicID)) { 
				$phpbbForum->restore_state($fStateChanged);
				return false;
			}
		
			// check permissions
			if(!$this->can_read_forum($forumID)) {
				$phpbbForum->restore_state($fStateChanged);
				return false;
			}
			
			$topicIDs = 't.topic_id = ' . $topicID;
		
		} else { //pulling for multiple articles at once

			$sql = 'SELECT forum_id, topic_id, post_wpu_xpost from ' . POSTS_TABLE . ' WHERE post_wpu_xpost > 0';
		
			$xPostedTopics = array();
			if($result = $db->sql_query($sql)) {
				while($dets = $db->sql_fetchrow($result)) {
					if($this->can_read_forum($dets['forum_id'])) {
						$xPostedTopics[$dets['topic_id']] = $dets['post_wpu_xpost'];
					}
				}
			}
			
			$xPostedTopicList = array_keys($xPostedTopics);
			
			if(!sizeof($xPostedTopicList)) {
				$phpbbForum->restore_state($fStateChanged);
				return false;
			}
			
			$topicIDs = $db->sql_in_set('t.topic_id', $xPostedTopicList);

		}
		
		$sql = 	'SELECT p.post_id, p.poster_id, p.poster_ip, p.post_time, 
					p.enable_bbcode, p.enable_smilies, p.enable_magic_url, 
					p.enable_sig, p.post_username, p.post_subject, 
					p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.post_edit_locked,
					p.topic_id, p.forum_id,
					t.topic_id, t.topic_replies AS all_replies, t.topic_replies_real AS replies, 
					u.username, u.user_wpuint_id, u.user_email 
				FROM ' . 
					TOPICS_TABLE . ' AS t , ' .
					POSTS_TABLE . ' AS p  INNER JOIN ' .
					USERS_TABLE . ' AS u ON 
					p.poster_id = u.user_id
				WHERE 
					p.topic_id = t.topic_id AND ' .
					$topicIDs . ' AND 
					p.post_approved = 1 AND
					t.topic_replies_real > 0 AND 
					p.post_wpu_xpost IS NULL 
					ORDER BY p.post_id ASC';

		if(!($result = $db->sql_query_limit($sql, $this->limit, $this->offset))) {
			$db->sql_freeresult($result);
			$phpbbForum->restore_state($fStateChanged);
			return false;
		}
		
		$randID = rand(10000,99999);
		
		$phpbbCommentLinks = array();
		while ($comment = $db->sql_fetchrow($result)) {
			
			$comment['bbcode_options'] = (($comment['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
				(($comment['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
				(($comment['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
			
			
			$parentPost = (empty($this->postID)) ? $xPostedTopics[$comment['topic_id']] : $this->postID;
			$commentID = $randID + $comment['post_id'];
			
			$link = $phpbbForum->get_board_url() . "memberlist.$phpEx?mode=viewprofile&amp;u=" . $comment['poster_id'];
			$args = array(
				'comment_ID' => $commentID,
				'comment_post_ID' => $parentPost,
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
				'comment_parent' => 0,
				'user_id' => $comment['user_wpuint_id'],
				'phpbb_id' => $comment['poster_id'],
			);

			// Fix relative paths in comment text
			$pathsToFix = array('src="' . $phpbb_root_path, 'href="' . $phpbb_root_path);
			$pathsFixed = array('src="' . $phpbbForum->get_board_url(), 'href="' . $phpbbForum->get_board_url());
			$args['comment_content'] = str_replace($pathsToFix, $pathsFixed, $args['comment_content']);

			$this->comments[] = new WPU_Comment($args);
			
			$this->links[$commentID] = $phpbbForum->get_board_url() . (($phpbbForum->seo) ? "post{$comment['post_id']}.html#p{$comment['post_id']}" : "viewtopic.{$phpEx}?f={$comment['forum_id']}&t={$comment['topic_id']}&p={$comment['post_id']}#p{$comment['post_id']}");

		}
		$db->sql_freeresult($result);
		
		$phpbbForum->restore_state($fStateChanged);

		$this->usingPhpBBComments = true;
		
		return true;
	}
	
	public function using_phpbb() {
		return $this->usingPhpBBComments;
	}
	
	private function sort($criterion='comment_date', $dir='DESC') {
		usort($this->comments, array($this, 'dateSort'));
	}
	
	private function dateSort($a, $b){
		return $a->comment_date == $b->comment_date ? 0 : ($a->comment_date < $b->comment_date) ? 1 : -1;
	}
	
	private function can_read_forum($forumID) {
		
		global $phpbbForum, $auth;
		static $authList = -1;
		
		$fStateChanged = $phpbbForum->foreground();
		if($authList === -1) {
			$authList = $auth->acl_getf('f_read', true);
		}
		$phpbbForum->restore_state($fStateChanged);
		
		if(!is_array($authList) || !sizeof($authList)) {
			return false;
		}
	
		// global announcement
		if($forumID == 0) {
			return true;
		}
		
		if(in_array($forumID, array_keys($authList))) {
			return true;
		}

		return false;
	}
}

/**
 * An individual comment placeholder
 */
class WPU_Comment {

	public
		$comment_ID,
		$comment_post_ID,
		$comment_author,
		$comment_author_email,
		$comment_author_url,
		$comment_author_IP,
		$comment_date,
		$comment_date_gmt,
		$comment_content,
		$comment_karma,
		$comment_approved,
		$comment_agent,
		$comment_type,
		$comment_parent,
		$phpbb_id,
		$user_id;
	
	/**
	 * Class initialisation
	 * All required args must be passed in
	 * @param array $args: Array with all WordPress comment fields provided as 'variablename' => 'variablevalue'
	 */
	public function __construct($args) {
		if(is_array($args)) {
			foreach($args as $argName => $argVal) {
				$this->$argName = $argVal;
			}
		}
	}
	
	
}

// End the comments file with a comment: Done.