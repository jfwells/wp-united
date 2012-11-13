<?php
/** 
*
* WP-United Main Integration File -- where the magic happens
*
* @package WP-United
* @version $Id: v0.8.5RC2 2010/02/06 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/

//@TODO: CLEAN UP ALL THIS NONSENSE
global $wpUnited, $forum_page_ID, $phpbbForum, $wpUnited;

global $user, $wpuNoHead, $wpUtdInt, $phpbbForum, $template, $wpu_page_title, $wp_version, $lDebug;
global $innerHeadInfo;
global $phpbb_root_path, $phpEx, $wpuCache;


if ( $wpuCache->use_template_cache() || $wpUnited->ran_patched_wordpress() ) {
	wpu_get_wordpress();
}



require($wpUnited->get_plugin_path() . 'template-integrator.php');


	
?>
