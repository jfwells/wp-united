<?php
/** 
*
* WP-United Main Integration File -- where the magic happens
*
* @package WP-United
* @version $Id: integrator.php,v0.8.0 2009/06/20 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*

This is the third incarnation of the main integration file (was wp-united.php, 
then wordpress-entry-point.php). It has been rewritten/refactored from scratch to clarify the code flow
for various different types of integration.

This file can be invoked in a few ways:
(a) via blog.php. This means we should show WordPress, and do user integration, etc. if required. If needed, we also
need to prepare the phpBB header and footer

(b) as a callback from hook_wp-united.php. This means the user has turned on "reverse integration" -- the phpBB page
has already been prepared, and phpBB has exited. All the page contents are stored in $innerContent. We need to run WordPress,
or pull stuff from our WordPress header/footer cache as necessary
if(empty $wpSettings)

(c) via blog.php using cURL from wpu-latest-posts.php or similar. This is used for grabbing latest blog data for, e.g. integration
into a separate portal page block. This functionality is not advertised (it is a throwback to phpBB2), but it still works.

(d) via an xmlHttpRequest. This is similar to (c), but it just requires us to login an integrated user silently, and return status via XML.
This is not implemented yet, but will be added in a future release. The request will need to come in via another file, which cleanses the request.

*/

/* ******** Initialisation *************/

// Prevent direct accesses
if ( !defined('IN_PHPBB') ) exit;

// Set a define to inform people that we are WP-United -- WP plugin authors, etc. might find this useful.
// It is also another way to test that things are running OK from the WordPress side.
define('WP_UNITED_ENTRY', 1);

// Start measuring script execution time
global $wpuScriptTime;
if(isset($GLOBALS['starttime'])) {
	$wpuScriptTime = $GLOBALS['starttime'];
} else {
	$wpuScriptTime = explode(' ', microtime());
	$wpuScriptTime = $starttime[1] + $starttime[0];
}


// All accesses via blog.php will already have some required files included. If we're being called from elsewhere
// or via a callback, we need to include them
// abstractify also pulls in mod-settings.php
if ( !defined('WPU_BLOG_PAGE') ) {
	global $wpuAbs;
	require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);
}

// More required files
require_once($phpbb_root_path . 'wp-united/wpu-helper-funcs.' . $phpEx);
require_once($phpbb_root_path . 'wp-united/options.' . $phpEx);
require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);

//Initialise the cache
global $wpuCache;
$wpuCache = WPU_Cache::getInstance();

// There are several variables we need to have around in the global scope. We only need to
// do this if we are being called from a function, but for convenience, we just do it anyway
global $wpSettings, $user, $userdata, $wpuNoHead, $wpUtdInt, $scriptPath, $template, $latest, $wpu_page_title, $wp_version, $lDebug;
global $innerHeadInfo, $innerContent;
$lDebug = '';

// This is another way for WP-United or WordPress elements to test if they are running in the global scope.
// They can test for $GLOBALS['amIGlobal']
$amIGlobal = true;

// Our Mod Settings should have been loaded by now. If not, either WP-United hasn't been set up, or something
// is seriously screwed.
if  ( $wpSettings == FALSE ) {
	$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_DBErr_Gen'), 'WordPress Integration Error','','',''); 
} elseif ( ($wpSettings['installLevel'] < 10) || ($wpSettings['wpUri'] == '') || ($wpSettings['wpPath'] == '') ) {
	$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_Not_Installed_Yet'), 'WordPress Integration Error','','','');
}


// For convenience, we set several variables that are useful in WordPress. Most template-related strings should be
// taking place elsewhere, e.g. in wpu-template-funcs.php, but for some commonly used items which we also use here in
// integrator.php, it makes sense to declare them up-front.

// $scriptPath is a global variable that provides the fully qualified path to phpBB. Used all over the place when we're in WordPress
$server = $wpuAbs->config('server_protocol') . add_trailing_slash($wpuAbs->config('server_name'));
$scriptPath = add_trailing_slash($wpuAbs->config('script_path'));
$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
$scriptPath = $server . $scriptPath;


// set some vars for wpu-plugin to use. 
global $phpbb_logged_in, $phpbb_username, $phpbb_sid, $login_link, $logout_link;
$phpbb_logged_in = $wpuAbs->user_logged_in();
$phpbb_username = $wpuAbs->phpbb_username();
$phpbb_sid = $wpuAbs->phpbb_sid();

// redirect to login if not logged in and blogs are private
if ( (!$wpuAbs->user_logged_in()) && ($wpSettings['must_login'])  && (!defined('WPU_REVERSE_INTEGRATION')) ) {
   redirect($login_link, true);
}



// set some strings for the WordPress page
if ( $phpbb_logged_in ) {
	if ( $wpuAbs->userdata('user_wpublog_id') ) {
		$wpuGetBlog = ($wpSettings['usersOwnBlogs']) ? $wpuAbs->lang('add_to_blog') : $wpuAbs->lang('go_wp_admin');
		$wpuGetBlogIntro = $wpuAbs->lang('blog_intro_add');
	} else {
		$wpuGetBlog = ($wpSettings['usersOwnBlogs']) ? $wpuAbs->lang('get_blog') : $wpuAbs->lang('go_wp_admin');
		$wpuGetBlogIntro = $wpuAbs->lang('blog_intro_get');
	}
} else {
	$wpuGetBlogIntro =  ($wpSettings['usersOwnBlogs']) ? $wpuAbs->lang('blog_intro_loginreg_ownblogs') : $wpuAbs->lang('blog_intro_loginreg');
}

// When PermaLinks are turned on, a trailing slash is added to the blog.php. Some templates also have trailing slashes hard-coded.
// This results in a single slash in PATH_INFO, which screws up WP_Query.
if ( isset($_SERVER['PATH_INFO']) ) {
	$_SERVER['PATH_INFO'] = ( $_SERVER['PATH_INFO'] == "/" ) ? '' : $_SERVER['PATH_INFO'];
}

// If we have been called only to provide the latest posts via cURL, we won't want to do any integration
$wpuNoHead = false;
$latest = false;
if ( isset($HTTP_GET_VARS['latest']) ) {
	$latest = true; // run in latest posts mode, for showing latest posts on portal page, etc.
	$wpuNoHead = true;
}
// number of posts to show on portal page in latest posts mode
if ( isset($HTTP_GET_VARS['numposts']) ) {
	$postsToShow = (int) $HTTP_GET_VARS['numposts'];
	$postsToShow = ($postsToShow > 10) ? 10 : $postsToShow;
	$postsToShow = ($postsToShow < 1) ? 3 : $postsToShow;
}




/********************** Initialise cache ***********************/
//TODO: integrate different cache types
$useCache = false;
if ( defined('WPU_REVERSE_INTEGRATION') ) {
	// If we're only using a simple WP header & footer, we don't bother with integrated login, and we can cache the wordpress parts of the page
	if ( !empty($wpSettings['wpSimpleHdr']) ) {
		if ( $wpuCache->template_cache_enabled() && !defined('WPU_PERFORM_ACTIONS') ) { 
			$useCache = true; 
			$wpuCache->use_template_cache();
		}
	}
}


/* ***************** Run WordPress ****************/

// If this is phpBB-in-wordpress, we just need to get WordPress header & footer, and store them in $outerContent
// if a valid WordPress template cache is available, we just do that and don't need to run WordPress at all.
// If this is WordPress-in-phpBB, now we call WordPress too, but store it in $innerContent

$wpContentVar = (defined('WPU_REVERSE_INTEGRATION')) ? 'outerContent' : 'innerContent';
$phpBBContentVar = (defined('WPU_REVERSE_INTEGRATION')) ? 'innerContent' : 'outerContent';
$connectSuccess = false;

if ( !$wpuCache->use_template_cache() ) { 
	require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
	$wpUtdInt = WPU_Integration::getInstance();

	//We really want WordPress to run in the global scope. So, our integration class really just prepares
	// a whole set of code to run, and passes it back to us for us to eval.
	if ($wpUtdInt->can_connect_to_wp()) {
		// This generates the code for all the preparatory steps -- cleans up the scope, and 
		// analyses and modifies WordPress core files as appropriate
		$wpUtdInt->enter_wp_integration();
				
		// This generates the code for integrating logins, synchronising user profiles, and managing WordPress permissions.
		// integrate_login handles whether logins should be integrated or not, so we can just call it without checking.
		if (!$latest) {
			$wpUtdInt->integrate_login();
		} 
		
		// We pass the name of the variable to populate as a string to be added to the invoked code.
		$wpUtdInt->get_wp_page($wpContentVar);

		// finally do the integration, execute all the prepared code.	
		eval($wpUtdInt->exec()); 
		$connectSuccess = true;
		
	} else {
		$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_Not_Installed_Yet'), '','','');
	}
}

if ( $useCache || $connectSuccess ) { 

	/****** If phpBB-in-wordpress, we need to generate the WP header/footer ****/
	if ( defined('WPU_REVERSE_INTEGRATION') ) {

		//prevent WP 404 error
		if ( !$wpuCache->use_template_cache() ) {
			query_posts('showposts=1');
		}

		if ( !empty($wpSettings['wpSimpleHdr']) ) {
			//
			//	Simple header and footer
			//
			if ( !$wpuCache->use_template_cache() ) {
				//
				// Need to rebuld the cache
				//
				
				// some theme/plugin options and workarounds for reverse integration
				// inove -- no sidebar on simple page
				$GLOBALS['inove_nosidebar'] = true;
		
				ob_start();
				get_header();
				$outerContent = ob_get_contents();
				ob_end_clean();
		
	
				$outerContent .= "<!--[**INNER_CONTENT**]-->";
				if ( $wpuCache->template_cache_enabled() ) {
					$outerContent .= "<!--cached-->";
				}				
				
				ob_start();
				get_footer();
				$outerContent .= ob_get_contents();
				ob_end_clean();
				
				if ( $wpuCache->template_cache_enabled() ) {
					$wpuCache->save_to_template_cache($wpuAbs->wpu_ver, $wp_version, $outerContent);
				}
				
			} else {
				//
				// Just pull the header and footer from the cache
				//
				$outerContent = $wpuCache->get_from_template_cache();

			}
		} else {
			//
			//	Full WP page
			//
			define('PHPBB_CONTENT_ONLY', TRUE);
	
			ob_start();
	
			$wpTemplateFile = TEMPLATEPATH . '/' . strip_tags($wpSettings['wpPageName']);
			if ( !file_exists($wpTemplateFile) ) {
				$wpTemplateFile = TEMPLATEPATH . "/page.php";
				// Fall back to index.php for Classic template
				if(!file_exists($wpTemplateFile)) {
					$wpTemplateFile = TEMPLATEPATH . "/index.php";
				}
			}
			include($wpTemplateFile);
	
			$outerContent = ob_get_contents();
			ob_end_clean();

		}
		
		if ( !$wpuCache->use_template_cache() ) {
			wp_reset_query();
		}
		
		if ( $wpSettings['cssFirst'] == 'P' ) {
			$outerContent = str_replace('</title>', '</title>' . "\n\n" . '<!--[**HEAD_MARKER**]-->', $outerContent);
		}

	}


	// clean up, go back to normal :-)
	if ( !$wpuCache->use_template_cache() ) {
		$wpUtdInt->exit_wp_integration();
		$wpUtdInt = null; unset ($wpUtdInt);
	}

}



/*************************** Get phpBB header/footer *****************************************/

if ( ($wpSettings['showHdrFtr'] == 'FWD') && (!$wpuNoHead) && (!defined('WPU_REVERSE_INTEGRATION')) ) {
	
	//export header styles to template - before or after phpBB's CSS depending on wpSettings.
	// Since we might want to do operations on the head info, 
	//we just insert a marker, which we will substitute out later
	$wpStyleLoc = ( $wpSettings['cssFirst'] == 'P' ) ? 'WP_HEADERINFO_LATE' : 'WP_HEADERINFO_EARLY';
	$template->assign_vars(array($wpStyleLoc => "<!--[**HEAD_MARKER**]-->"));
	
	$wpuAbs->add_template_switch('S_SHOW_HDR_FTR', TRUE);
	// We need to set the base HREF correctly, so that images and links in the phpBB header and footer work properly
	$wpuAbs->add_template_switch('PHPBB_BASE', $scriptPath);
	
	
	// If the user wants CSS magic, we will need to inspect the phpBB Head, so we buffer the output 
	ob_start();
	page_header("[**PAGE_TITLE**]");
	
	
	$template->assign_vars(array(
		'WORDPRESS_BODY' => "<!--[**INNER_CONTENT**]-->",
		'WP_CREDIT' => sprintf($wpuAbs->lang('WPU_Credit'), '<a href="http://www.wp-united.com" target="_blank">', '</a>'))
	); 
	
	//Stop phpBB from exiting
	define('PHPBB_EXIT_DISABLED', true);
	
	$wpuAbs->show_body('blog');
	
	$outerContent = ob_get_contents();
	
	ob_end_clean();
}

// Now, $innerContent and $outerContent are populated. We can now modify them and interleave them as necessary
// All template modifications take place in template-integrator.php

require_once($phpbb_root_path . 'wp-united/template-integrator.' . $phpEx);





/*
echo "------------ OUTER CONTENT: --------------<br />";
echo $outerContent . "<br />";
echo "------------ INNER CONTENT: --------------<br />";
echo $innerContent . "<br />";
*/

// Finally -- clean up
(empty($config['gzip_compress'])) ? @flush() : @ob_flush();







	?>
