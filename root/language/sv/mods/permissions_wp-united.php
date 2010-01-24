<?php
/** 
*
* WP-United Permissions
*
* @package WP-United
* @version $Id: v0.8.0RC2 2010/01/14 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* @translation by Oz
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
    'acl_u_wpu_subscriber'    => array('lang' => 'Kan integreras som en WordPress prenumerant (kan se profiler, skriva kommentarer)', 'cat' => 'wputd'),
    'acl_u_wpu_contributor'    => array('lang' => 'Kan integreras som en WordPress medarbetare (kan skriva men inte publicera inlägg)', 'cat' => 'wputd'),
    'acl_u_wpu_author'    => array('lang' => 'Kan integreras som en WordPress författare (kan skriva blogginlägg)', 'cat' => 'wputd'),
    'acl_m_wpu_editor'    => array('lang' => 'Kan integreras som en WordPress redaktör (kan redigera andras inlägg)', 'cat' => 'wputd'),
    'acl_a_wpu_administrator'    => array('lang' => 'Kan integreras som en WordPress administratör', 'cat' => 'wputd'),
    'acl_a_wpu_manage'    => array('lang' => 'Kan hantera WP-United alternativ i admin kontrollpanelen', 'cat' => 'wputd'),
	'acl_f_wpu_xpost'	=>	array('lang' => 'Kan skriva blogg inlägg till detta forum', 'cat' => 'wputd'),
));
?>
