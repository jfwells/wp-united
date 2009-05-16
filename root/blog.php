<?php
/** 
*
* WP-United Main Entry Page
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v0.6.0[phpBB3] 2007/07/15 John Wells (Jhong) Exp $
* @copyright (c) 2006, 2007 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/*******************************************************************************
	W  P  --   U N I T E D
	ENTRY POINT TO YOUR INTEGRATED WORDPRESS INSTALL
	-------------------------------------------------------------------------------------
	
	**********************************		
	******* R  E  A  D      M  E  **********
	**********************************		
	Simply move / rename this file wherever you want. For example, you might want to rename it to index.php, and put it in your site root.
	
	If you want to put this in your WordPress folder, you will have to rename your existing index.php to index-old.php. This is OK, it won't affect normal operation.
	
	Once moved, you MUST set the path to your phpBB root folder where indicated just below.
	
	Then run the WP-United Setup Wizard
	

********************************************************************************/


//the path can be absolute or relative... i.e. '/home/www/phpBB2'   './phpBB2'   or  'phpBB2'   could all be correct.
//Set the path to phpBB  below, between the quotes:

$PATH_TO_PHPBB_INSTALL = ''	// <--- set the path to your phpBB root here,  between the quotes				

//If you change this, you MUST run the Setup Wizard again!

//STOP EDITING NOW -
;











//Do not edit any of the lines below:
// standard hack prevent 
define('IN_PHPBB', true);
if (strlen($PATH_TO_PHPBB_INSTALL)) {
	$PATH_TO_PHPBB_INSTALL = ($PATH_TO_PHPBB_INSTALL[strlen($PATH_TO_PHPBB_INSTALL)-1] == "/" ) ? $PATH_TO_PHPBB_INSTALL : $PATH_TO_PHPBB_INSTALL . "/";
	@chdir ($PATH_TO_PHPBB_INSTALL);
}




$phpbb_root_path = './'; 

$phpEx = substr(strrchr(__FILE__, '.'), 1);

if (file_exists($phpbb_root_path . 'common.' . $phpEx)) {
	include($phpbb_root_path . 'common.' . $phpEx);

	require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);	

	define('WPU_BLOG_PAGE', 1);

	require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
	$wpSettings = get_integration_settings();

	include ($phpbb_root_path . 'wp-united/wordpress-entry-point.php');
} else {
	//When this warning appears, we cannot find phpBB - hence we do not use the template engine or $lang array. 'Cos we can't, alright?
	echo "<html><head>
		<style type=\"text/css\">BODY {background-color: #cccccc; text-align: center; margin: 150px; font-weight: bold; color: navy; font-size: 16px; font-family: Verdana, Arial, Helvetica, sans-serif; border: 1px solid #000000;}
		</style></head><body>
		<p style=\"color: #ffffff; font-size: 22px; background-color: navy; padding: 5px; margin: 0px;\">Error!</p>
		<p style=\"background-color: #ffffff; margin: 0px; padding: 20px;\">The path is incorrect.
		Please check the path to phpBB you provided and then run the WP-United Setup Wizard again.</p>
		</body></html>";
}

?>
