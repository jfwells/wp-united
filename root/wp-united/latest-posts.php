<?php
/** 
*
* WP-United Latest Posts Add-on
*
* @package WP-United
* @version $Id: v0.8.0RC2 2010/01/14 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
* @deprecated
*
*/


/**
 */
if ( !defined('IN_PHPBB') ) {
	exit;
}

/*	WP-UNITED -- LATEST WORDPRESS POSTS ON PORTAL PAGE
*
*	Just include this file to generate the page content for the previous x posts
*	lib_curl is required. It is enabled by default on most shared hosts. 
* 	If you're unfortunate enough to be on Windows, you'll proabaly have to enable the specified module in apache or php.ini
*
*
*
*/

if ( defined('GET_PORTAL_CONTENT') ) { 
	//
	//	RETRIEVE WORDPRESS PAGE
	//	----------------------------------------
	//
	require_once($phpbb_root_path . 'wp-united/functions-general.' . $phpEx);
	global $config, $user;
	$numPosts = 3;
	if ( defined('GET_NUM_POSTS') ) {
		$numPosts = (GET_NUM_POSTS > 10) ? 10 : GET_NUM_POSTS;
		$numPosts = ($numPosts < 1) ? 3 : $numPosts;
	}
	$server = add_http(add_trailing_slash($config['server_name']));
	$scriptPath = add_trailing_slash($config['script_path']);
	$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
	$scriptPath = $server . $scriptPath;

	//in case page_header hasn't been called yet:
	require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
	$wpSettings = get_integration_settings();
	$path_to_blog = $wpSettings['blogsUri'] . "?latest=1&numposts={$numPosts}";
	$cWpu= curl_init();
	$cTimeout = 5;
	curl_setopt($cWpu, CURLOPT_URL, $path_to_blog);
	curl_setopt($cWpu, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($cWpu, CURLOPT_CONNECTTIMEOUT, $cTimeout);
	curl_setopt($cWpu, CURLOPT_FOLLOWLOCATION,1);
	$portal_content = curl_exec($cWpu); 
	curl_close($cWpu); 
	if ( !($portal_content == "{****NO_POSTS****}") ) { 
		$portal_posts = explode('{**|**}', $portal_content);
		array_pop($portal_posts);
		foreach ($portal_posts as $post) {
			$post_info = explode('{****}', $post);
			$template->assign_block_vars('latest_blog_posts', array(
				'U_BLOGPOST_LINK' =>$post_info[0],
				'BLOGPOST_TITLE' =>$post_info[1],
				'BLOGPOST_TIME' =>$post_info[2],
				'BLOGPOST_AUTHOR' =>$post_info[3],
				'BLOGPOST_EXCERPT' =>$post_info[4],
				'BLOGPOST_CAT' =>$post_info[5])
			);
		} 
	$template->assign_vars(array(
		'LATEST_BLOG_POSTS' => $user->lang['Latest_Blog_Posts'),
		'ARTICLE_BY' => $user->lang['Article_By'),
		'WP_CATEGORY' => $user->lang['WP_Category'),
		'WP_POSTED_ON' => $user->lang['WP_Posted_On'))
	);
	}
	

} else {
	//
	//	GENERATE WORDPRESS PAGE
	//	-----------------------------------------
	//
	global $postsToShow;
	query_posts('showposts=' . $postsToShow);
	//$GLOBALS['wp_query'] = '';
	$i = 1;
	if (have_posts()) {
		while ( (have_posts()) & ($i <= $postsToShow) ) {
			the_post(); 
		
			the_permalink(); 		echo "{****}";
			the_title(); 			echo "{****}";
			the_time('F jS, Y'); 	echo "{****}";
			the_author();			echo "{****}";
			echo str_replace('</p>', '', str_replace('<p>', '', get_the_excerpt('Read more &raquo;')));	echo "{****}";
			the_category(', '); 	echo "{**|**}";
			
			$i++;
		}
	} else {
		echo "{****NO_POSTS****}";
	}
}	

?>