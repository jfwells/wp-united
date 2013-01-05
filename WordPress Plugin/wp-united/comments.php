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
		$status,
		$offset,
		$postID,
		$userID,
		$userEmail,
		$topicUser,
		$count,
		$order,
		$phpbbOrderBy,
		$finalOrderBy,
		$orderFieldsMap;
		
		
	public $links;
	

	/**
	 * Class initialisation
	 */
	public function __construct() {
		$this->comments = array();
		$this->links = array();
		$this->usingPhpBBComments = false;
		$this->postID = 0;
		$this->userID = '';
		$this->userEmail = '';
		$this->topicUser = '';
		$this->limit = 25;
		$this->offset = 0;
		$this->count = false;
		$this->status = 'all';
		$this->order = 'DESC';
		$this->phpbbOrderBy = '';
		$this->finalOrderBy = array();
		
		// variables we could sort our query by.
		// Commented items are criteria WordPress could send which we will drop.
		$this->orderFieldsMap = array(
			//'comment_agent'			=> 	'', // same for all phpBB comments,
			'comment_approved'		=>	'p.post_approved',
			'comment_author'		=>	'u.username',
			'comment_author_email'	=>	'u.user_email',
			'comment_author_IP'		=> 	'u.user_ip',
			'comment_author_url'	=>	'u.user_website',
			'comment_content'		=>	'p.post_text',
			'comment_date'			=>	'p.post_time',
			'comment_date_gmt'		=>	'p.post_time',
			'comment_ID'			=>	'p.post_id',
			//'comment_karma'		=>	'', // n/a
			//'comment_parent'		=>	'', //n/a
			'comment_post_ID'		=>	't.topic_id',
			//'comment_type'			=> 	'',		// only interested in 'comments'
			'user_id'				=>	'u.user_wpuint_id',
		);
		
	}
	
	/**
	 * We can only handle certain types of comment queries right now.
	 */
	private function can_handle_query($query) {
	

		if( 
			preg_match("/\/edit-comments\.php/", $_SERVER['REQUEST_URI'])	||
			
			// we have no karma
			!empty($query->query_vars['karma'])							||
			
			// we cannot calculate offsets without re-pulling WP data
			!empty($query->query_vars['offset'])						||
			
			// phpBB is not threaded
			!empty($query->query_vars['parent'])						||
			
			// TODO: Allow comment filtering by topic title
			!empty($query->query_vars['post_name'])						||
			
			// TODO: Allow comment filtering by WP post parent
			!empty($query->query_vars['post_parent'])					||
			
			// we only understand published, cross-posted posts
			!(	
				empty($query->query_vars['post_status']) 	||
				($query->query_vars['post_status'] == 'publish')
			)															||
			
			// ignore attachments or anything other than posts
			!(
				empty($query->query_vars['post_type'])		||
				($query->query_vars['post_type'] == 'post')
			)															||
			
			// ignore trackbacks, pingbacks or anything other than comments
			!(
				empty($query->query_vars['type'])			||
				($query->query_vars['type'] == 'comment')
			)															||
			
			// cannot translate meta-queries to phpBB
			!empty($query->query_vars['meta_key'])						||
			!empty($query->query_vars['meta_value'])					||
			!empty($query->query_vars['meta_query'])
        ) {
			return false;
		}
		
		return true;
	
	
	}
	
	/**
	 * Translate query vars from WordPress format to our standardised / phpBB format
	 * @return void
	 */
	private function setup_query_vars($query) {
	
		if(!is_object($query)) {
			return;
		}
		
		$this->postID = $query->query_vars['post_id'];
		$this->limit = $query->query_vars['number'];
		$this->offset = $query->query_vars['offset'];
		$this->count = $query->query_vars['count'];
		
		// set up vars for status clause
		if(!empty($query->query_vars['status'])) {
			if($query->query_vars['status'] == 'hold') {
				$this->status = 'unapproved';
			}
			else if($query->query_vars['status'] != 'approve') {
				$this->status = 'approved';
			}
		}
		
		// set up vars for user clause
		if(!empty($query->query_vars['user_id'])) {
			$this->userID = $query->query_vars['user_id'];
		}
		
		// set up vars for e-mail clause
		if(!empty($query->query_vars['author_email'])) {
			$this->userEmail = $query->query_vars['author_email'];
		}
		
		// set up vars for topic author ID clause
		if(!empty($query->query_vars['post_author'])) {
			$this->topicUser = $query->query_vars['post_author'];
		}			
	
	}
	
	private function setup_sort_vars($query) {
		
		if(!is_object($query)) {
			$this->order = 'ASC';
			$this->phpbbOrderBy = 'p.post_time';
			$this->finalOrderBy = array('p.post_time');
			return;
		} 
			
		
		// set up vars for ordering clauses
		if(empty($this->count)) {
			if (!empty($query->query_vars['orderby'])) {
				$ordersBy = is_array($query->query_vars['orderby']) ? $query->query_vars['orderby'] : preg_split('/[,\s]/', $query->query_vars['orderby']);
			
				
				$ordersBy = array_intersect($ordersBy, array_keys($this->orderFieldsMap));
				
				foreach($ordersBy as $orderBy) {
					if(!empty($this->phpbbOrderBy)) {
						$this->phpbbOrderBy .= ', ';
					}
					$this->phpbbOrderBy .= $this->orderFieldsMap[$orderBy];
					$this->finalOrderBy[] = $orderBy;
				}
				
			}
			
			$this->phpbbOrderBy = empty($this->phpbbOrderBy) ? 'p.post_time' : $this->phpbbOrderBy;
		}
		if(!sizeof($this->finalOrderBy)) {
			$this->finalOrderBy[] = 'comment_date_gmt';
		}
		
		if(!empty($this->phpbbOrderBy)) {
			$this->order = ( 'ASC' == strtoupper($query->query_vars['order']) ) ? 'ASC' : 'DESC';
		} else {
			$this->order = '';
		}
	
	}
	

	public function populate_comments($query, $comments) {
		
		if(!$this->can_handle_query($query)) {
			return false;
		}
		
		$this->setup_query_vars($query);
		
		$this->setup_sort_vars($query);
			
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
		
		$this->setup_sort_vars();
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


		//first get forum permissions
		$allowedForums = array_unique(array_keys($auth->acl_getf('f_read', true))); 
		
		// user can't read any forums
		if(!sizeof($allowedForums)) {
			$phpbbForum->restore_state($fStateChanged);
			return false;
		}
		
		//Add global topics
		$allowedForums[] = 0;
		
		//Get permissions for unapproved comments
		if($this->status != 'approved') {
			$canViewUnapproved = array_unique(array_keys($auth->acl_getf('m_approve', true))); 
		}
		
		$phpbbID = $phpbbForum->get_userdata('user_id');
		
		$where = array();
		
		

		
		if($this->count) {
			$query = array(
				'SELECT' 	=> 'COUNT(p.*) AS count'
			);
		} else {
			
			$query = array(
				'SELECT'	=> 'p.post_id, p.poster_id, p.poster_ip, p.post_time, 
								p.post_approved, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, 
								p.enable_sig, p.post_username, p.post_subject, 
								p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.post_edit_locked,
								p.topic_id,
								t.topic_wpu_xpost, t.forum_id, t.topic_id, t.topic_replies AS all_replies, t.topic_replies_real AS replies, 
								u.user_id, u.username, u.user_wpuint_id, u.user_email',

				'ORDER_BY'	=> $this->phpbbOrderBy . ' ' . $this->order
			);
		}
		
		$query['FROM'] = array(
			TOPICS_TABLE 	=> 	't',
			POSTS_TABLE		=> 	'p',
			USERS_TABLE		=>	'u'
		);
		
		
		if($this->postID) {
			$where[] = sprintf(' t.topic_wpu_xpost = %d', $this->postID);
		} else {
			$where[] = ' t.topic_wpu_xpost > 0';
		}
		
		if($this->userID) {
			$where[] = sprintf(' u.user_wpuint_id = %d', $this->userID);
		}
		
		if($this->userEmail) {
			$string = esc_sql(like_escape($this->userEmail));
			$where[] = " u.user_email LIKE '%$string%'";
		}
		if($this->topicUser) {
			$where[] = sprintf(" t.topic_poster = %s", wpu_get_integrated_phpbbuser($this->topicUser));
		}		
		
		
		if($this->status == 'unapproved') {
			$where[] = ' p.post_approved = 0 AND (' .
				$db->sql_in_set('t.forum_id', $canViewUnapproved) . ' OR 
				u.user_id = ' . $phpbbID . ' 
				)';
		} else if($this->status == 'approved') {
			$where[] = ' p.post_approved = 1';
		} else {
			$where[] = ' (p.post_approved = 1 OR ( 
				p.post_approved = 0 AND (' .
				$db->sql_in_set('t.forum_id', $canViewUnapproved) . ' OR 
				u.user_id = ' . $phpbbID . '
				)))';
		}
			
		
		$where[] = '
			((p.poster_id = u.user_id) AND 
			(p.topic_id = t.topic_id) AND 
			(t.topic_replies > 0) AND
			(t.topic_first_post_id <> p.post_id) AND (' .
			$db->sql_in_set('t.forum_id', $allowedForums) . '
			))';
			
		$query['WHERE'] = implode(' AND ', $where);
					
					
		$sql = $db->sql_build_query('SELECT', $query);
		
		if(!($result = $db->sql_query_limit($sql, $this->limit, $this->offset))) {
			$db->sql_freeresult($result);
			$phpbbForum->restore_state($fStateChanged);
			return false;
		}
		
		if($this->count) {
			$countRow = $db->sql_fetchrow($result);
			$this->comments = $countRow['count'];
			$phpbbForum->restore_state($fStateChanged);
			return true;
		}
		
		$randID = rand(10000,99999);
		
		$phpbbCommentLinks = array();
		while ($comment = $db->sql_fetchrow($result)) {
			
			$comment['bbcode_options'] = (($comment['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
				(($comment['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
				(($comment['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
			
			
			$parentPost = (empty($this->postID)) ? $comment['topic_wpu_xpost'] : $this->postID;
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
				'comment_approved' => $comment['post_approved'],
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
	
	private function sort() {
		usort($this->comments, array($this, 'comment_sort_callback'));
	}
	
	private function comment_sort_callback($a, $b){
		
		$criteriaCounter = 0;
		$criterion = $this->finalSortBy[$criteriaCounter];
		
		while( ($a->$criterion == $b->$criterion) && ($criteriaCounter < (sizeof($this->finalSortBy) - 1)) ) {
			$criteriaCounter++;
			$criterion = $this->finalSortBy[$criteriaCounter];
		}

		if($this->order == 'ASC') {
			return $a->$criterion == $b->$criterion ? 0 : ($a->$criterion > $b->$criterion) ? 1 : -1;
		} else {
			return $a->$criterion == $b->$criterion ? 0 : ($a->$criterion < $b->$criterion) ? 1 : -1;
		}
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