<?php
/** 
*
* WP-United phpBB3 Install Script
*
* @package WP-United
* @version $Id: v0.8.0RC2 2010/01/14 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*/

//
//	DELETE THIS FILE AFTER USE!


define('IN_PHPBB', true);
$phpbb_root_path = './'; 
$phpEx = substr(strrchr(__FILE__, '.'), 1);
define('IN_INSTALL', true);
include($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/functions_module.' . $phpEx);

// check our hook has been loaded
$cache->purge();
if(!defined('WPU_HOOK_ACTIVE')) {
	require_once($phpbb_root_path . 'includes/hooks/hook_wp-united.' . $phpEx);
}

$user->session_begin();
$auth->acl($user->data);
$user->setup('acp/common');
$user->setup('mods/wp-united');

$server = $config['server_protocol'] . add_trailing_slash($config['server_name']);
$scriptPath = add_trailing_slash($config['script_path']);
$scriptPath= ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;

if ($user->data['user_type'] != USER_FOUNDER) {
    if ($user->data['user_id'] == ANONYMOUS) {
        login_box('');
    }
	trigger_error('NOT_AUTHORISED');
}

$bodyContent = "<img src=\"{$server}{$scriptPath}wp-united/images/wp-united-logo.gif\" style=\"float: right;\" />";


$bodyContent .= "<br />Modifying USERS Table (Integration ID)... ";

if  ( !array_key_exists('user_wpuint_id', $user->data) ) {
 	$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
		ADD user_wpuint_id VARCHAR(10) NULL DEFAULT NULL';

	if (!$result = $db->sql_query($sql)) {
		trigger_error('ERROR: Cannot add the integration column to the users table');
	}
	$bodyContent .= "Done!<br />\n\n";
} else {
	$bodyContent .= "Already modified!<br />\n\n";
}

$bodyContent .= "Modifying USERS Table (Blog ID)...";

if  ( !array_key_exists('user_wpublog_id', $user->data) ) {
 	$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
		ADD user_wpublog_id VARCHAR(10) NULL DEFAULT NULL';
	if (!$result = $db->sql_query($sql)) {
		trigger_error('ERROR: Cannot add blog ID column to users table');
	}
	$bodyContent .= "Done!<br />\n\n";
} else {
	$bodyContent .= "Already modified!<br />\n\n";
}		

$bodyContent .= "Modifying POSTS Table (Cross-Posting Link)... ";

$sql = 'SELECT * FROM ' . POSTS_TABLE;
$result = $db->sql_query_limit($sql, 1);

$row = (array)$db->sql_fetchrow($result);

if (!array_key_exists('post_wpu_xpost', $row) ) {
 	$sql = 'ALTER TABLE ' . POSTS_TABLE . ' 
		ADD post_wpu_xpost VARCHAR(10) NULL DEFAULT NULL';

	if (!$result = $db->sql_query($sql)) {
		trigger_error('ERROR: Cannot add cross-posting column to posts table');
	}
	$bodyContent .= "Done!<br />\n\n";
} else {
	$bodyContent .= "Already modified!<br />\n\n";
}

$db->sql_freeresult($result);

$bodyContent .= "Adding WP-United Permissions....<br />\n\n";

// Setup $auth_admin class so we can add permission options
include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);


$auth_admin = new auth_admin();

// Add permissions
$auth_admin->acl_add_option(array(
    'local'      => array('f_wpu_xpost'),
    'global'   => array('u_wpu_subscriber','u_wpu_contributor','u_wpu_author','m_wpu_editor','a_wpu_administrator', 'a_wpu_manage')
));

$bodyContent .= "granting access....<br />\n\n";
// give standard admins access
$role = get_role_by_name('ROLE_ADMIN_STANDARD');
if ($role) {
   acl_update_role($role['role_id'], array('a_wpu_manage'));
}



$bodyContent .= "Adding WP-United ACP Modules...<br />\n\n";

include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
$acp_modules = new acp_modules();
$acp_modules->module_class = 'acp';

$bodyContent .= "Adding main tab...\n\n";

$modData = array(
	'module_basename'	=> '', // must be blank for category
	'module_mode'		=> '', // must be blank for category
	'module_auth'		=> '', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> 0,
	'module_langname'	=> 'ACP_WP_UNITED', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$tabId = wpu_add_acp_module($modData);

$bodyContent .= "Adding main subcat...\n\n";
$modData = array(
	'module_basename'	=> '', // must be blank for category
	'module_mode'		=> '', // must be blank for category
	'module_auth'		=> '', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $tabId,
	'module_langname'	=> 'ACP_WPU_CATMAIN', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catMainId = wpu_add_acp_module($modData);

$bodyContent .= "Adding setup subcat...\n\n";
$modData = array(
	'module_basename'	=> '', // must be blank for category
	'module_mode'		=> '', // must be blank for category
	'module_auth'		=> '', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $tabId,
	'module_langname'	=> 'ACP_WPU_CATSETUP', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catSetupId = wpu_add_acp_module($modData);

$bodyContent .= "Adding manage users subcat...\n\n";
$modData = array(
	'module_basename'	=> '', // must be blank for category
	'module_mode'		=> '', // must be blank for category
	'module_auth'		=> '', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $tabId,
	'module_langname'	=> 'ACP_WPU_CATMANAGE', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catManageId = wpu_add_acp_module($modData);

$bodyContent .= "Adding support subcat...\n\n";
$modData = array(
	'module_basename'	=> '', // must be blank for category
	'module_mode'		=> '', // must be blank for category
	'module_auth'		=> '', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $tabId,
	'module_langname'	=> 'ACP_WPU_CATSUPPORT', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catSupportId = wpu_add_acp_module($modData);

$bodyContent .= "Adding 'other' subcat...\n\n";
$modData = array(
	'module_basename'	=> '', // must be blank for category
	'module_mode'		=> '', // must be blank for category
	'module_auth'		=> '', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $tabId,
	'module_langname'	=> 'ACP_WPU_CATOTHER', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catOtherId = wpu_add_acp_module($modData);

$bodyContent .= "Adding main page...";
$modData = array(
	'module_basename'	=> 'wp_united', // must be blank for category
	'module_mode'		=> 'index', // must be blank for category
	'module_auth'		=> 'acl_a_wpu_manage', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $catMainId,
	'module_langname'	=> 'ACP_WPU_MAINTITLE', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catMainPageId = wpu_add_acp_module($modData);

$bodyContent .= "Adding setup wizard...";
$modData = array(
	'module_basename'	=> 'wp_united', // must be blank for category
	'module_mode'		=> 'wizard', // must be blank for category
	'module_auth'		=> 'acl_a_wpu_manage', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $catSetupId,
	'module_langname'	=> 'ACP_WPU_WIZARD', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catSetupWizId = wpu_add_acp_module($modData);

$bodyContent .= "Adding settings-on-a-page...";
$modData = array(
	'module_basename'	=> 'wp_united', // must be blank for category
	'module_mode'		=> 'detailed', // must be blank for category
	'module_auth'		=> 'acl_a_wpu_manage', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $catSetupId,
	'module_langname'	=> 'ACP_WPU_DETAILED', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catDetailedId = wpu_add_acp_module($modData);



// User Mapping Tool under catManageId  ((only applied by wizard/setup if user integration is turned on).


$bodyContent .= "Adding ACP donate link...";
$modData = array(
	'module_basename'	=> 'wp_united', // must be blank for category
	'module_mode'		=> 'donate', // must be blank for category
	'module_auth'		=> 'acl_a_wpu_manage', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $catSupportId,
	'module_langname'	=> 'ACP_WPU_DONATE', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catDetailedId = wpu_add_acp_module($modData);

$bodyContent .= "Adding uninstaller...";
$modData = array(
	'module_basename'	=> 'wp_united', // must be blank for category
	'module_mode'		=> 'uninstall', // must be blank for category
	'module_auth'		=> 'acl_a_wpu_manage', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $catOtherId,
	'module_langname'	=> 'ACP_WPU_UNINSTALL', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catDetailedId = wpu_add_acp_module($modData);

$bodyContent .= "Adding reseter...";
$modData = array(
	'module_basename'	=> 'wp_united', // must be blank for category
	'module_mode'		=> 'reset', // must be blank for category
	'module_auth'		=> 'acl_a_wpu_manage', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $catOtherId,
	'module_langname'	=> 'ACP_WPU_RESET', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catDetailedId = wpu_add_acp_module($modData);

$bodyContent .= "Adding debugging tool...";
$modData = array(
	'module_basename'	=> 'wp_united', // must be blank for category
	'module_mode'		=> 'debug', // must be blank for category
	'module_auth'		=> 'acl_a_wpu_manage', // must be blank for category
	'module_enabled'	=> 1,
	'module_display'	=> 1, // must be 1 for tab
	'parent_id'			=> $catOtherId,
	'module_langname'	=> 'ACP_WPU_DEBUG', //langname -- key or name -- must include
	'module_class'		=>'acp',
);
$catDetailedId = wpu_add_acp_module($modData);

$bodyContent = str_replace('<br />', "<br /><img src=\"{$server}{$scriptPath}wp-united/images/tiny.gif\" style=\"float: left; margin-right: 3px;\" />", $bodyContent);

$bodyContent .= "<strong style=\"color: green;\">All done!<br />\n\n";
$bodyContent .= "<hr /><br />";
$bodyContent .= "To complete the setup, please run the Setup Wizard under your WP-United ACP tab. <br />\n\n";
$bodyContent .= "You can control access to this tab via the 'Can manage WP-United options in the ACP' permission.<br /><hr /><br />\n\n";
$bodyContent .= "Please delete this file -- and enjoy WP-United!</strong>\n\n";

//page_header('WP-United Installer');



define('PHPBB_EXIT_DISABLED', true);
trigger_error($bodyContent);


add_log('admin', 'WP_INSTALLED', 'Ran the WP-United Install Script');	










function wpu_add_acp_module(&$module_data) {
	global $acp_modules, $cache, $bodyContent;

	$mod_id = module_exists($module_data['module_langname'], $module_data['parent_id']);
	
	if ( !empty($mod_id) ) {
		$module_data['module_id'] = $mod_id;
	}
	
	
	// Adjust auth row if not category
	if ($module_data['module_basename'] && $module_data['module_mode']) {
		$fileinfo = $acp_modules->get_module_infos($module_data['module_basename']);
		$module_data['module_auth'] = $fileinfo[$module_data['module_basename']]['modes'][$module_data['module_mode']]['auth'];
	} 

	$errors = $acp_modules->update_module_data($module_data, TRUE);
	if (!sizeof($errors)) {
		if ( !empty($mod_id) ) {
			$bodyContent .= "Tab already exists!<br />\n\n";
		} else {
			$bodyContent .= "Added item!<br />";
		}
		$acp_modules->remove_cache_file();
	} else {
		$bodyContent .= 'Could not add item!<br />' . implode('<br />', $errors);
	}
	
	$cache->destroy('_modules_');
	$cache->destroy('_sql_', MODULES_TABLE);
	$cache->purge();
	
	return $module_data['module_id'];
}


function module_exists($modName, $parent = 0) {
	global $db;
	$sql = "SELECT module_id FROM " . MODULES_TABLE . "
				WHERE parent_id = $parent
				AND module_langname = '$modName'";
	if (!$result = $db->sql_query($sql)) {
		trigger_error('ERROR: Cannot get module details');
	}
	//there could be a duplicate module, but screw it -- just deal with the first one we find. Alternative is to abort and tell user we don't know what to do with dupes, which isn't better.
	if ( $row = $db->sql_fetchrow($result) ) {
		if ( !empty($row['module_id']) ) {
			return $row['module_id'];
		}
	}
	return '';

}



// PERMISSION TO ROLE ASSIGNMENT FUNCTIONS BY POYNTESM
// http://www.phpbb.com/community/viewtopic.php?f=71&t=545415&p=3026305

function get_role_by_name($name) {
   global $db;
   $data = null;

   $sql = "SELECT *
      FROM " . ACL_ROLES_TABLE . "
      WHERE role_name = '$name'";
   $result = $db->sql_query($sql);
   $data = $db->sql_fetchrow($result);
   $db->sql_freeresult($result);

   return $data;
}

/**
* Set role-specific ACL options without deleting enter existing options. If option already set it will NOT be updated.
* 
* @param int $role_id role id to update (a role_id has to be specified)
* @param mixed $auth_options auth_options to grant (a auth_option has to be specified)
* @param ACL_YES|ACL_NO|ACL_NEVER $auth_setting defines the mode acl_options are getting set with
*
*/
function acl_update_role($role_id, $auth_options, $auth_setting = ACL_YES) {
   global $db, $cache, $auth;

	$acl_options_ids = get_acl_option_ids($auth_options);

	$role_options = array();
	$sql = "SELECT auth_option_id
		FROM " . ACL_ROLES_DATA_TABLE . "
		WHERE role_id = " . (int) $role_id . "
		GROUP BY auth_option_id";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))	{
		$role_options[] = $row;
	}
	$db->sql_freeresult($result);

	$sql_ary = array();
	foreach($acl_options_ids as $option)	{
		if (!in_array($option, $role_options)) {
			$sql_ary[] = array(
				'role_id'      		=> (int) $role_id,
				'auth_option_id'	=> (int) $option['auth_option_id'],
				'auth_setting'      => $auth_setting			);	
		}
	}

   $db->sql_multi_insert(ACL_ROLES_DATA_TABLE, $sql_ary);

   $cache->destroy('acl_options');
   $auth->acl_clear_prefetch();
}

/**
* Get ACL option ids
*
* @param mixed $auth_options auth_options to grant (a auth_option has to be specified)
*/
function get_acl_option_ids($auth_options) {
   global $db;

   $data = array();
   $sql = "SELECT auth_option_id
      FROM " . ACL_OPTIONS_TABLE . "
      WHERE " . $db->sql_in_set('auth_option', $auth_options) . "
      GROUP BY auth_option_id";
   $result = $db->sql_query($sql);
   while ($row = $db->sql_fetchrow($result))  {
      $data[] = $row;
   }
   $db->sql_freeresult($result);

   return $data;
}




?>
