<?php
/** 
*
* WP-United Mod Settings
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.7.1[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
//

/**
 * A few functions to write and retrieve the integration package settings from the DB
 * This is a bit messy & unneccesarily complex, a result of our previous simultaneous support for phpBB2 & phpBB3.
 * The phpBB2 version used its own integration table, hence the schema.
 * 
 * In phpBB3, we just pull them from the config table.
 * Why don't we access them directly?  We may do in the future, but for now, this affords us more flexibility, and saves rewriting the rest of the mod.
 * @todo remove schema for v1.0
 */

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);

/**
 * Returns a map of the structure of our database against the variables we use in WP-United.
 * LEFT SIDE = VARIABLE NAMES
 * RIGHT SIDE = DB FIELD NAMES
 */

function get_db_schema() {

	$dbSchema = array( 
		'blogsUri' => 'blogsEntry',
		'wpUri' => 'fullUri' ,
		'wpPath' => 'fullPath', 
		'integrateLogin' => 'wpLogin', 
		'permList' => 'permMapping',
		'showHdrFtr' => 'showInside',
		'cssFirst' => 'cssFirst',
		'wpSimpleHdr' => 'wpHdrSimple',
		'dtdSwitch' => 'dtdChange',
		'installLevel' => 'installStage',
		'usersOwnBlogs' => 'ownBlogs',
		'buttonsProfile' => 'profileBtn',
		'buttonsPost' => 'postBtn',
		'allowStyleSwitch' => 'styleSwitch',
		'useBlogHome' => 'blogHomePage',
		'blogListHead' => 'blogHomeTitle',
		'blogIntro' => 'blogIntro',
		'blogsPerPage' => 'numBlogsPerPage',
		'blUseCSS' => 'wpublStyles',
		'charEncoding' => 'encoding',
		'phpbbCensor' => 'censorPosts',
		'wpuVersion' => 'wpuVer',
		'wpPageName' => 'wpPage',
		'phpbbPadding' => 'pPadding',
		'mustLogin' => 'mustLogin',
		'upgradeRun' => 'ugRun',
		'xposting' => 'xposting',
		// Added in v0.7.0
		'phpbbSmilies' => 'phpbbSmilies',
		'fixHeader' => 'fixHeader',
		// Added in 0.7.1
		'xpostautolink' => 'xpostautolink',
		// Added in 0.8.0
		'cssMagic' => 'cssmagic',
		'templateVoodoo' => 'tempvoodoo',
		'pluginFixes' => 'pluginfixes'
	);
	
	return $dbSchema;
}

/**
 * Set default values for WPU settings
 */
function set_default($setting_key) {
	global $phpEx, $wpuAbs, $phpbb_root_path, $phpEx;
	require_once($phpbb_root_path . 'wp-united/wpu-helper-funcs.' . $phpEx);
	$server = add_http(add_trailing_slash($wpuAbs->config('server_name')));
	$scriptPath = add_trailing_slash($wpuAbs->config('script_path'));
	$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
	$defaultBlogUri = $server . $scriptPath . "blog." . $phpEx;
	
	$defaults = array(
		'blogsUri' => $defaultBlogUri,
		'wpUri' => '' ,
		'wpPath' => '', 
		'integrateLogin' => 0, 
		'permList' => ($wpuAbs->ver == 'PHPBB3') ? '' : '<S:><C:><A:>PHPBB:[USER]<E:>PHPBB:[MOD]</:>PHPBB:[ADMIN]',
		'showHdrFtr' => 'FWD',
		'cssFirst' => 0,
		'wpSimpleHdr' => 1,
		'dtdSwitch' => 0,
		'installLevel' => 0,
		'usersOwnBlogs' => 0,
		'buttonsProfile' => 0,
		'buttonsPost' => 0,
		'allowStyleSwitch' => 0,
		'useBlogHome' => 0,
		'blogListHead' => $wpuAbs->lang('WPWiz_BlogListHead_Default'),
		'blogIntro' => $wpuAbs->lang('WPWiz_blogIntro_Default'),
		'blogsPerPage' => 6,
		'blUseCSS' => 1,
		'charEncoding' => ($wpuAbs->ver == 'PHPBB3') ? 'NO_CHANGE' : 'MATCH_WP',
		'phpbbCensor' => 1,
		'wpuVersion' => $wpuAbs->lang('WPU_Not_Installed'),
		'wpPageName' => 'page.php',
		'phpbbPadding' => ($wpuAbs->ver == 'PHPBB3') ? '6-12-6-12' : '20-20-20-20',
		'mustLogin' => 0,
		'upgradeRun' => 0,
		'xposting' => 0,
		// Added in 0.7.0
		'phpbbSmilies' => 0,
		'fixHeader' => 1,
		// Added in 0.8.0
		'xpostautolink' => 0,
		'cssMagic' => 1,
		'templateVoodoo' => 1,
		'pluginFixes' => 0	
		
	);
	
	return $defaults[$setting_key];

}


/**
 * Get configuration setings from database
 * Gets the configuration settings from the db, and returns them in $wpSettings.
 * Sets initial values to sensible deafaults if they haven't been set yet.
 */
function get_integration_settings($set_admin_defaults = FALSE) {
	global $db, $wpuAbs;
	
	$config_fields = get_db_schema();
	$wpSettings = array();
	foreach($config_fields as $var_name => $field_name) {
		if ($wpuAbs->config('wpu_'.$field_name) !== FALSE) {
			$wpSettings[$var_name] = $wpuAbs->config('wpu_'.$field_name);
			//unset($GLOBALS['config']['wpu_'.$field_name]);
		} elseif ($set_admin_defaults) {
			$wpSettings[$var_name] = set_default($var_name);
		}
	}
	return $wpSettings;	
	
}
/**
 * Clear integration settings
 * Completely removes all traces of WP-united settings
 * @deprecated -- only used by removed phpBB2 code paths
 */
function clear_integration_settings() {
	global $db;
	
	$config_fields = get_db_schema();
	$key_names = array();
	foreach ($config_fields as $config_field) {
		$key_names[] = 'wpu_' . $config_field;
	}
	
	$sql = 'DELETE FROM ' . CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $key_names);
	$db->sql_query($sql);

}

/**
 * Write config settings to the database
 * Writes any configuration settings that are passed to the integration settings table.
 * phpBB2 code path removed for v0.8
*/
function set_integration_settings($dataIn) {
	global $db;
	
	// Map DB schema to our data keys
	$fullFieldSet = get_db_schema();
	
	//Clean data, and convert it to our DB schema
		foreach ($fullFieldSet as $var_name => $field_name ) {
			if ( array_key_exists($var_name, $dataIn) ) {
				$data[$field_name] =	$dataIn[$var_name];
				//$GLOBALS['config']['wpu_'.$field_name] = $dataIn[$var_name]; //if we unset them before, update config fails
				set_config('wpu_'.$field_name, $dataIn[$var_name]);
			}
		}
	
	return true;
}

/**
 * Clean for db reinsertion
 * @todo check magic quotes etc
 */
function clean_for_db_reinsert($value) {
//$value = str_replace("'", "''", $value);
$value = addslashes($value);
$value = str_replace("\'", "''", $value);
	return $value;

}


?>
