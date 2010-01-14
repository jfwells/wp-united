<?php
/** 
*
* WP-United ACP Panels
*
* @package WP-United
* @version $Id: v0.8.0RC2 2010/01/14 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
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
* @package module_install
*/

class acp_wp_united_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_wp_united',
			'title'		=> 'ACP_WP_UNITED',
			'version'	=> '0.7.0',
			'modes'		=> array(
				'index'		=> array('title' => 'ACP_WPU_MAINPAGE', 'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
				'wizard'	=> array('title' => 'ACP_WPU_WIZARD', 'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
				'detailed'	=> array('title' => 'ACP_WPU_DETAILED', 'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
				'usermap'	=> array('title' => 'ACP_WPU_USERMAP', 'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
				'permissions' => array('title' => 'ACP_WPU_PERMISSIONS',  'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
				'donate'	=> array('title' => 'ACP_WPU_DONATE', 'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
				'uninstall'	=> array('title' => 'ACP_WPU_UNINSTALL', 'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
				'reset'		=> array('title' => 'ACP_WPU_RESET', 'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
				'debug'		=> array('title' => 'ACP_WPU_DEBUG', 'auth' => 'acl_a_wpu_manage', 'cat' => array('ACP_WP_UNITED')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>
