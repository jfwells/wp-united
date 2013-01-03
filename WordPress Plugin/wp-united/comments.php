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


class WPU_Comments_Access_Layer {
	
	private 
		$queries,
		$links;
	
	public function __construct() {
		$this->queries = array();
		$this->links = array();
	}
	
	public function process($query, $comments = false) {
		
		if(is_object($query)) {
			$queryArgs = $query->query_vars;
		} else {
			$queryArgs = $query;
		}
		
		foreach($this->queries as $queryObj) {
			if($queryObj['queryargs'] === $queryArgs) {
				return $queryObj['result'];
			}
		}
		
		$newQuery = array(
			'queryargs'		=>	$queryArgs,
			'comments'		=> 	new WPU_Comments()
		);
		
		if(is_object($query)) {
			$newQuery['result'] = $newQuery['comments']->populate_comments($query, $comments);
		} else {
			$newQuery['result'] = $newQuery['comments']->populate_for_post($query);
		}

		//array keys are strings so not renumbered
		$this->links = array_merge($this->links, $newQuery['comments']->links);
		
		$this->queries[] = $newQuery;
		
		return $newQuery['result'];
	}
	
	public function get_comments($query) {
		if(is_object($query)) {
			$query = $query->query_vars;
		} 
		
		foreach($this->queries as $queryObj) {
			if($queryObj['queryargs'] === $query) {
				return $queryObj['comments']->get_comments();
			}
		}
		
		return false;
		
	}
	
	public function get_link($commentID) {
					
		if(isset($this->links['comment' . $commentID])) {
			return $this->links['comment' . $commentID];
		}
				
		return false;
	}
}


/**
 * A comment object to store cross-posted comment results, and retrieve various other info
 */
class WPU_Comments {
	
	private 
		$comments,
		$usingPhpBBComments,
		$limit,
		$offset,
		$postID,
		$count;
		
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
		$this->count = false;
		
	}
	

	public function populate_comments($query, $comments) {
				
		$this->postID = $query->query_vars['post_id'];
		$this->limit = $query->query_vars['number'];
		$this->offset = $query->query_vars['offset'];
		$this->count = $query->query_vars['count'];

		/**
		 * We can only handle VERY LIMITED types of comment queries right now.
		 */
		if( 
			preg_match("/\/edit-comments\.php/", $_SERVER['REQUEST_URI'])	||
			!empty($query->query_vars['author_email'])					||
			!empty($query->query_vars['karma'])							||
			!empty($query->query_vars['offset'])						||
			!empty($query->query_vars['parent'])						||
			!empty($query->query_vars['post_author'])					||
			!empty($query->query_vars['post_name'])						||
			!empty($query->query_vars['post_parent'])					||
			!(	
				empty($query->query_vars['post_status']) 	||
				($query->query_vars['post_status'] == 'publish')
			)															||
			!empty($query->query_vars['post_type'])						||
			!(
				empty($query->query_vars['status'])		||
				($query->query_vars['status'] == 'approve')
			)															||
			!empty($query->query_vars['type'])							||
			!empty($query->query_vars['user_id'])						||
			!empty($query->query_vars['meta_key'])						||
			!empty($query->query_vars['meta_value'])					||
			!empty($query->query_vars['meta_query'])
        ) {
			return false;
		};
		
		$this->populate_phpbb_comments();
		
		if($this->count) {
			return $this->comments + (int)$comments;
		}
		
		
		if(sizeof($this->comments)) {
			$result = true;
		}
		
		$this->add_wp_comments($comments);
		
		if($result) {
			$this->sort();
		}
		
		return $result;
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
		
		$addlJoinFields = '';
				
		if($this->count) {
			$query = array(
				'SELECT' 	=> 'COUNT(p.*) AS count',
				'FROM'		=> 	array(
									TOPICS_TABLE 	=> 	't',
									POSTS_TABLE		=>	'p'
								)
			);
		} else {
			
			$query = array(
				'SELECT'	=> 'p.post_id, p.poster_id, p.poster_ip, p.post_time, 
								p.enable_bbcode, p.enable_smilies, p.enable_magic_url, 
								p.enable_sig, p.post_username, p.post_subject, 
								p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.post_edit_locked,
								p.topic_id, p.forum_id,
								t.topic_id, t.topic_replies AS all_replies, t.topic_replies_real AS replies, 
								u.username, u.user_wpuint_id, u.user_email',
				'FROM'		=>	array(
									TOPICS_TABLE 	=> 	't',
									POSTS_TABLE		=> 	'p',
									USERS_TABLE		=>	'u'
								),
				'ORDER_BY'	=> 'p.post_time DESC'
			);
			$addlJoinFields = ' p.poster_id = u.user_id AND ';
		}
		
		$query['WHERE'] = '
			p.topic_id = t.topic_id AND ' .
			$addlJoinFields .
			$topicIDs . ' AND 
			p.post_approved = 1 AND
			t.topic_replies_real > 0 AND 
			p.post_wpu_xpost IS NULL ';
					
					
		$sql = $db->sql_build_query('SELECT', $query);
					

		if(!($result = $db->sql_query_limit($sql, $this->limit, $this->offset))) {
			$db->sql_freeresult($result);
			$phpbbForum->restore_state($fStateChanged);
			return false;
		}
		
		if($this->count) {
			$countRow = $db->sql_fetchrow($result);
			$this->comments = $countRow['count'];
			return true;
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
			
			//don't use numerical keys to avoid renumbering on array_merge
			$this->links['comment' . $commentID] = $phpbbForum->get_board_url() . (($phpbbForum->seo) ? "post{$comment['post_id']}.html#p{$comment['post_id']}" : "viewtopic.{$phpEx}?f={$comment['forum_id']}&t={$comment['topic_id']}&p={$comment['post_id']}#p{$comment['post_id']}");

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