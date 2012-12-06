<?php
/**
*
* WP-United [Spanish]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.1 2009/05/18 Raistlin (Raistlin) Exp $
* @copyright (c) 2006-2012 wp-united.com
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

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(

	'BLOG'         =>    'WordPress Blog',
	'VISIT_BLOG'      =>   'Visitar el Blog del Usuario',
	'Function_Duplicate'    =>   'Un nombre duplicado de función ha sido detectadp. Esto es probablemente a un foro muy modificado. Porfavor,visita www.wp-united.com para reportar el error.',
	'WPU_Credit'          =>   'Integración por %sWP-United%s',
));

?>
