<?php
/**
*
* WP-United Permissions [Spanish]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.0 2009/05/18 Raistlin (Raistlin) Exp $
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
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// Adding new category
$lang['permission_cat']['wputd'] = 'WP-United';

   // Adding new permission set
   //$lang['permission_type']['wp_'] = 'Permisos de Wordpress';

// Adding the permissions
$lang = array_merge($lang, array(
    'acl_u_wpu_subscriber'    => array('lang' => 'Puede integrarse como WordPress subscriber (ver perfil y escribir comentarios)', 'cat' => 'wputd'),
    'acl_u_wpu_contributor'    => array('lang' => 'Puede integrarse como WordPress contributor (puedes escribir pero no publicar posts)', 'cat' => 'wputd'),
    'acl_u_wpu_author'    => array('lang' => 'Puede integrarse como WordPress author (puede escribir blog posts)', 'cat' => 'wputd'),
    'acl_m_wpu_editor'    => array('lang' => 'Puede integrarse como WordPress editor (puede editar posts de otros)', 'cat' => 'wputd'),
    'acl_a_wpu_administrator'    => array('lang' => 'Puede integrarse como WordPress administrator', 'cat' => 'wputd'),
    'acl_a_wpu_manage'    => array('lang' => 'Puede manejar las opciones de WP-United en el  ACP', 'cat' => 'wputd'),
	// Please translate me :-)
	'acl_f_wpu_xpost'	=>	array('lang' => '[pls translate]Can post blog posts to this forum', 'cat' => 'wputd'),	
));
?>
