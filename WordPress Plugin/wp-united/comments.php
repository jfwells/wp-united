<?php
/** 
*
* @package WP-United
* @version $Id: 0.9.2.0  2012/01/05 John Wells (Jhong) Exp $
* @copyright (c) 2006-2013 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
* The main class for retrieving cross-posted comments.
* Access is via an access layer that pulls and caches comments as necessary.
* 
*/

/**
*
*/
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) exit;




class WPU_XPost_Query_Store {
	
	private 
		
		$queries,
		$currentQuery,
		$maxLimit,
		$currentProvidedLimit,
		$links,
		$doingQuery,
		$orderFieldsMap;
		
		/**
	 * Class initialisation
	 */
	public function __construct() {
		
		$this->maxLimit = 10000;
		$this->queries = array();
		$this->links = array();
		$this->currentQuery = array();
		
		$this->doingQuery = false;

		
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
	
	private function init_defaults() {
	
		$this->currentQuery = array(
			'passedResult'	=> array(),
			'signature' 	=> '',
			'postID'		=> 0,
			'userID' 		=> '',
			'userEmail' 	=> '',
			'topicUser' 	=> '',
			'limit' 		=> 25,
			'offset' 		=> 0,
			'count' 		=> false,
			'groupByStatus' => false,
			'status' 		=> 'all',
			'order' 		=> 'DESC',
			'phpbbOrderBy' 	=> '',
			'finalOrderBy' 	=> array()
		);
		
		$this->currentProvidedLimit = 0;

	}
	
	public function get($query, $comments, $count = false) {
		
		global $wpuDebug;
		
		if(!$this->can_handle_request($query) || $this->doingQuery) {
			return $comments;
		}
		
		$this->doingQuery = true;
	
		$this->set_current_query($query, $comments, $count);
	
		$this->create_request_signature();

		
		$sig = implode(',', $this->currentQuery['signature']);
		
		if(!isset($this->queries[$sig])) {
			$wpuDebug->add('New XPost query created for query ' . $sig);
			$this->queries[$sig] = new WPU_XPost_Query();
			$this->links = array_merge($this->links, $this->queries[$sig]->links);
		} else {
			$wpuDebug->add('Re-using XPost query from store for query ' . $sig);
		}
		
		$result = $this->queries[$sig]->get_result($this->currentQuery);
		
		$this->doingQuery = false;
		
		return $result;
	
	}
		
	
	/**
	 * We can only handle certain types of comment queries right now.
	 * This is used to reject queries that we either can't understand or make no sense in a cross-posted
	 * context.
	 *
	 * Not to be called for integer queries -- only for full query objects!.
	 * @pram WP_Comment_Query $query query object
	 * @return bool true if we can handle it!
	 */
	private function can_handle_request($query) {
		
		if(!is_object($query)) {
			return true;
		}

		if( // temp; the comments page needs offsets to work
			//preg_match("/\/edit-comments\.php/", $_SERVER['REQUEST_URI'])	||
			
			// we have no karma
			!empty($query->query_vars['karma'])							||
			
			// TODO: we cannot calculate offsets without re-pulling WP data
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
	 * Not to be called for integer queries.
	 * @param WP_Comment_Query $query our query object
	 * @return void
	 */
	private function set_current_query($query, $comments, $count) {
	
		$this->init_defaults();
		
		$this->currentQuery['passedResult'] = $comments;
	

		if(!is_object($query)) {
			$this->currentQuery['postID'] = ((int)$query > 0) ? $query : false;
			$this->currentQuery['limit'] = $this->maxLimit;
			$this->currentQuery['offset'] = 0;
		
			if($count) { 
				$this->currentQuery['count'] = true;
				$this->currentQuery['groupByStatus'] = true;
			}
		} else {
			
			$this->currentProvidedLimit = $query->query_vars['number'];
			
			$this->currentQuery['postID'] = $query->query_vars['post_id'];
			$this->currentQuery['limit'] = ((int)$query->query_vars['number'] > 0) ? $query->query_vars['number'] : $this->maxLimit;
			$this->currentQuery['offset'] = $query->query_vars['offset'];
			$this->currentQuery['count'] = $query->query_vars['count'];
			
			// set up vars for status clause
			if(!empty($query->query_vars['status'])) {
				if($query->query_vars['status'] == 'hold') {
					$this->currentQuery['status'] = 'unapproved';
				}
				else if($query->query_vars['status'] != 'approve') {
					$this->currentQuery['status'] = 'approved';
				}
			}
			
			// set up vars for user clause
			if(!empty($query->query_vars['user_id'])) {
				$this->currentQuery['userID'] = $query->query_vars['user_id'];
			}
			
			// set up vars for e-mail clause
			if(!empty($query->query_vars['author_email'])) {
				$this->currentQuery['userEmail'] = $query->query_vars['author_email'];
			}
			
			// set up vars for topic author ID clause
			if(!empty($query->query_vars['post_author'])) {
				$this->currentQuery['topicUser'] = $query->query_vars['post_author'];
			}			
		}
		
		$this->setup_sort_vars($query);
		
		return;
	}
	
		/**
	*	Sets up the class sorting parameters. Sorting happens at two levels:
	*	- in the SQL when fetching results from the database (using $this->phpbbOrderBy)
	* 	- when mixing together the WordPress and phpBB comments (using $this->finalOrderBy)
	*/
	private function setup_sort_vars($query) {
		
		if($this->currentQuery['count']) {
			$this->currentQuery['order'] = '';
			return;
		}
		
		if(!is_object($query)) {
			$this->currentQuery['order'] = ('DESC' == strtoupper(get_option('comment_order'))) ? 'DESC' : 'ASC';
			$this->currentQuery['phpbbOrderBy'] = 'p.post_time';
			$this->currentQuery['finalOrderBy'] = array('comment_date_gmt');
			return;
		} 
			
		
		// set up vars for ordering clauses
		if (!empty($query->query_vars['orderby'])) {
			$ordersBy = is_array($query->query_vars['orderby']) ? $query->query_vars['orderby'] : preg_split('/[,\s]/', $query->query_vars['orderby']);
		
			
			$ordersBy = array_intersect($ordersBy, array_keys($this->orderFieldsMap));
			
			foreach($ordersBy as $orderBy) {
				if(!empty($this->currentQuery['phpbbOrderBy'])) {
					$this->currentQuery['phpbbOrderBy'] .= ', ';
				}
				$this->currentQuery['phpbbOrderBy'] .= $this->orderFieldsMap[$orderBy];
				$this->currentQuery['finalOrderBy'][] = $orderBy;
			}
			
		}
		
		$this->currentQuery['phpbbOrderBy'] = empty($this->currentQuery['phpbbOrderBy']) ? 'p.post_time' : $this->currentQuery['phpbbOrderBy'];
		
		if(!sizeof($this->currentQuery['finalOrderBy'])) {
			$this->currentQuery['finalOrderBy'] = array('comment_date_gmt');
		}
		
		
		if(empty($query->query_vars['order'])) {
			$this->currentQuery['order'] = ('DESC' == strtoupper(get_option('comment_order'))) ? 'DESC' : 'ASC';
		} else {
			$this->currentQuery['order'] = ('DESC' == strtoupper($query->query_vars['order'])) ? 'DESC' : 'ASC';
		}
			
	
	}
	
	
	
	/**
	 * Get the signature of a request to determine if we can re-use the 
	 * results, of if we need to run another query.
	 * 
	 * Even slightly varying requests could be served from the same 
	 * query results.
	 * 
	 */
	private function create_request_signature() {
		
		// order is of no significance if no limit or offset is set
		$order = (empty($this->currentProvidedLimit)) ? 0 : $this->currentQuery['order'];
		$orderBy = (empty($this->currentProvidedLimit)) ? '' : $this->currentQuery['phpbbOrderBy'];
		
		$this->currentQuery['signature'] = array(
			(int)$this->currentQuery['postID'], 
			$this->currentQuery['userID'], 
			$this->currentQuery['userEmail'], 
			$this->currentQuery['topicUser'], 
			$this->currentQuery['limit'], 
			$this->currentQuery['offset'], 
			(int)$this->currentQuery['count'], 
			(int)$this->currentQuery['groupByStatus'], 
			$this->currentQuery['status'], 
			$oder,
			$orderBy
		);
		
	}
	
	
	/**
	* Returns a link for this cross-posted comment.
	* @param mixed $commentID an identifier for our cross-posted comment
	* @return StdObject comment object, or false if no link exists.
	*/
	public function get_link($commentID) {
					
		if(isset($this->links['comment' . $commentID])) {
			return $this->links['comment' . $commentID];
		}
				
		return false;
	}
	
	
	
	
}





/**
 * A comment object to store cross-posted comment results, and retrieve various other info.
 * Each WPU_Comments object is a query result.
 */
class WPU_XPost_Query {
	
	private 
		$success,
		$result,
		$usingPhpBBComments,
		$queryExecuted,
		
		
		$passedResult,
		$signature,
		$postID,
		$userID,
		$userEmail,
		$topicUser,
		$limit,
		$offset,
		$count,
		$groupByStatus,
		$status,
		$order,
		$phpbbOrderBy,
		$finalOrderBy;
		

	public $links;
	

	/**
	 * Class initialisation
	 */
	public function __construct() {
		
		$this->success = false;
		$this->queryExecuted = false;
		
		$this->result = array();
		$this->links = array();
		$this->usingPhpBBComments = false;
		
	}
	

	public function get_result($queryArgs) {
	
		$this->populate_vars($queryArgs);
	
		if(!$this->queryExecuted) {
			$this->queryExecuted = true;
			return $this->execute_query();
		}
		
		if(!$this->success) { 
			return false;
		}
		
		if(!$this->count && sizeof($this->result)) {
			$this->sort();
			if(is_array($this->result)) {
				return array_slice($this->result, 0, $this->limit);
			}
		}
		
		return $this->result;
		
	}

	private function populate_vars($queryArgs) {
		foreach($queryArgs as $arg => $value) {
			$this->$arg = $value;
		}
	}
	
	/**
	* Populates comments (or an integer count result) for a full WordPress query
	*/
	public function execute_query() {

		$this->success = $this->perform_phpbb_comment_query();

		if($this->success === false) {
			return false;
		}
		
		if($this->count) {

			if(!$this->groupByStatus) {
				$this->result = (int)$this->result + (int)$this->passedResult;
			} else {

				// Now we fetch the native WP count
				$totalCount = wp_count_comments($this->postID);
				if(is_object($totalCount)) {
					$totalCount->moderated 		= $this->result['moderated'] 		+ $totalCount->moderated;
					$totalCount->approved 		= $this->result['approved'] 		+ $totalCount->approved;
					$totalCount->total_comments = $this->result['total_comments'] 	+ $totalCount->total_comments;
				}
				$this->result = $totalCount;
			}
			
			return $this->result;
		}
		
		
		$phpbbResult = sizeof($this->result);
		if(is_array($this->passedResult) && sizeof($this->passedResult)) {
			$this->add_wp_comments($this->passedResult);
			if($phpbbResult) {
				$this->sort();
				if(is_array($this->result)) {
					return array_slice($this->result, 0, $this->limit);
				}
			}
		}

		return $this->result;
	}
	
	
	
	/**
	* Adds the WordPress comments to the class
	* @param array $comments the WordPress comments
	* @return void
	*/
	private function add_wp_comments($comments) {
		$this->result = array_merge($comments, $this->result);
	}
	
	/**
	* Populates the class with phpBB comments or a count result, according to the query requirements.
	* the query must already have been processed.
	* @return bool true if it is possible to read cross-posted comments here, even if there are none.
	*/
	private function perform_phpbb_comment_query() {
		
		global $wpuDebug, $phpbbForum, $auth, $db, $phpEx, $user, $phpbb_root_path;

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


		// Now, time to build the query.... It's a many-faceted one but can be done in one go....
		$where = array();
		
		if($this->count) { 
			if($this->groupByStatus) {
				$query = array(
					'SELECT' 	=> 'p.post_approved, COUNT(p.post_id) AS num_comments',
					'GROUP_BY'	=> 'p.post_approved'
				);
			} else { 
				$query = array(
					'SELECT' 	=> 'COUNT (p.post_id) AS num_comments'
				);
			}
		} else {
			
			$query = array(
				'SELECT'	=> 'p.post_id, p.poster_id, p.poster_ip, p.post_time, 
								p.post_approved, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, 
								p.enable_sig, p.post_username, p.post_subject, 
								p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.post_edit_locked,
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
			$where[] = sprintf('(t.topic_wpu_xpost = %d)', $this->postID);
		} else {
			$where[] = '(t.topic_wpu_xpost > 0)';
		}
		
		if($this->userID) {
			$where[] = sprintf('(u.user_wpuint_id = %d)', $this->userID);
		}
		
		if($this->userEmail) {
			$string = esc_sql(like_escape($this->userEmail));
			$where[] = "(u.user_email LIKE '%$string%')";
		}
		if($this->topicUser) {
			$where[] = sprintf("(t.topic_poster = %s)", wpu_get_integrated_phpbbuser($this->topicUser));
		}		
		
		$canViewUnapproved = (sizeof($canViewUnapproved)) ? $db->sql_in_set('t.forum_id', $canViewUnapproved) . ' OR ' : '';
		
		if($this->status == 'unapproved') {
			$where[] = '(p.post_approved = 0 AND (' .
				$canViewUnapproved . ' 
				u.user_id = ' . $phpbbID . ' 
				))';
		} else if($this->status == 'approved') {
			$where[] = '(p.post_approved = 1)';
		} else {
			$where[] = '(p.post_approved = 1 OR ( 
				p.post_approved = 0 AND (' .
				$canViewUnapproved . ' 
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
					
		// Phew. Done. Now run it.			
		$sql = $db->sql_build_query('SELECT', $query);

		$wpuDebug->add('Performing cross-post query: ' . htmlentities(str_replace(array("\n", "\t"), '', $sql)));
		
		if(!($result = $db->sql_query_limit($sql, $this->limit, $this->offset))) {
			$db->sql_freeresult($result);
			$phpbbForum->restore_state($fStateChanged);
			return false;
		}

		if($this->count) {
			if($this->groupByStatus) {
				$stats = array(
					'moderated'			=> 	0,
					'approved'			=> 	0,
					'total_comments'	=>	0
				);
				while ($stat = $db->sql_fetchrow($result)) {
					if($stat['post_approved'] == 0) {
						$stats['moderated'] = $stat['num_comments'];
					} else {
						$stats['approved'] = $stat['num_comments'];
					}
					$stats['total_comments'] = $stats['total_comments'] + $stat['num_comments'];
				}
				$this->result = $stats;	
				
			} else {
				$countRow = $db->sql_fetchrow($result);
				$this->result = $countRow['num_comments'];
			}
			$phpbbForum->restore_state($fStateChanged);
			return true;
		}
		
		$randID = rand(10000,99999);
		
		// Now fill the comments and links arrays
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

			$this->result[] = (object) $args;
			
			//don't use numerical keys to avoid renumbering on array_merge
			$this->links['comment' . $commentID] = $phpbbForum->get_board_url() . (($phpbbForum->seo) ? "post{$comment['post_id']}.html#p{$comment['post_id']}" : "viewtopic.{$phpEx}?f={$comment['forum_id']}&t={$comment['topic_id']}&p={$comment['post_id']}#p{$comment['post_id']}");

		}
		$db->sql_freeresult($result);
		
		$phpbbForum->restore_state($fStateChanged);

		$this->usingPhpBBComments = true;
		
		return true;
	
	}
	
	/**
	* same as the parent calling the result of the query operation.
	* @return bool
	*/
	public function using_phpbb() {
		return $this->usingPhpBBComments;
	}
	
	/**
	* Sorts the comments, even when there are mixed WordPress and phpBB comments.
	* @return void
	*/
	private function sort() {
		usort($this->result, array($this, 'comment_sort_callback'));
	}
	
	// internal callback for the sort function.
	private function comment_sort_callback($a, $b){
		
		$criteriaCounter = 0;
		$criterion = $this->finalOrderBy[$criteriaCounter];

		if(!empty($criterion)) {
			while(($a->$criterion == $b->$criterion) && ($criteriaCounter < (sizeof($this->finalOrderBy) - 1)) ) {
				$criteriaCounter++;
				$criterion = $this->finalOrderBy[$criteriaCounter];
			}
			$result = strcmp((string)$a->$criterion, (string)$b->$criterion);

			if($this->order == 'ASC') {
				return ($result == 0) ? 0 : (($result > 0) ? 1 : -1);
			} else {
				return ($result == 0) ? 0 : (($result < 0) ? 1 : -1);
			}
		}

		return 0;
	}
	
	

	
	
}



// End the comments file with a comment: Done.
