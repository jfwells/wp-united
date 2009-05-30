<?php
/** 
*
* WP-United Permissions
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.0 japgalaxy (japgalaxy.altervista.org) 2009/05/18
* @copyright (c) 2006, 2007 wp-united.com
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
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// Adding new category
$lang['permission_cat']['wputd'] = 'WP-United';

	// Adding new permission set
	//$lang['permission_type']['wp_'] = 'WordPress Permissions';

// Adding the permissions
$lang = array_merge($lang, array(
    'acl_u_wpu_subscriber'    => array('lang' => 'Integra come Sottoscrittore WordPress (pu&ograve; visualizzare il profilo e scrivere commenti)', 'cat' => 'wputd'),
    'acl_u_wpu_contributor'    => array('lang' => 'Integra come Collaboratore WordPress (pu&ograve; scrivere ma non pubblicare i posts)', 'cat' => 'wputd'),
    'acl_u_wpu_author'    => array('lang' => 'Integra come Autore WordPress (pu&ograve; scrivere e pubblicare i posts)', 'cat' => 'wputd'),
    'acl_m_wpu_editor'    => array('lang' => 'Integra come Editore WordPress (pu&ograve; modificare gli altri posts)', 'cat' => 'wputd'),
    'acl_a_wpu_administrator'    => array('lang' => 'Integra come Amministratore WordPress', 'cat' => 'wputd'),
    'acl_a_wpu_manage'    => array('lang' => 'Pu&ograve; gestire le opzioni di WP-United nell\'ACP', 'cat' => 'wputd'),
	'acl_f_wpu_xpost'	=>	array('lang' => 'Pu&ograve; postare i post del blog nel forum (Cross Post)', 'cat' => 'wputd'),
));
?>
