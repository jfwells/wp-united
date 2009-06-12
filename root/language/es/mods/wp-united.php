<?php
/**
*
* WP-United [Spanish]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.1 2009/05/18 Raistlin (Raistlin) Exp $
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
	'Blog'                =>   'WordPress Blog',
	'WP_DBErr_Gen'          =>   'No se ha podido acceder a la tabla de configuración de Wordpress en la base de datos. Por favor,asegurate de que usaste la consulta SQL incluida cuando instalaste el mod,y luego lo configuraste en el panel de administración.',
	'WP_No_Login_Details'    =>   'Error: No se ha podido crear una cuenta de Wordpress para ti. Por favor,contacta al administrador.',
	'WP_DTD'             =>   '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	'Function_Duplicate'    =>   'Un nombre duplicado de función ha sido detectadp. Esto es probablemente a un foro muy modificado. Porfavor,visita www.wp-united.com para reportar el error.',
	'WP_Not_Installed_Yet'    =>   'WP-United no ha sido instalado correctamente todavía en tu sitio. Por favor,utiliza el instalador localizado en el panel de administración.',
	'WPU_Credit'          =>   'Integración por %sWP-United%s',
	'get_blog'             =>   'Crea tu blog',
	'add_to_blog'          =>   'Añade a tu Blog',
	'go_wp_admin'      =>      'Ve al Panel de Administración',
	'blog_intro_get'       =>   "%sClick aquí%s para comenzar hoy con tu blog!",
	'blog_intro_add'       =>   "%sClick aquí%s para añadir tu blog.",
	'blog_intro_loginreg_ownblogs'    =>   "%sRegístrate%s o %sLogeate%s para iniciar tu blog.",
	'blog_intro_loginreg'    =>   "%sRegistrate%s o %sLogeate%s para participar",
	'Latest_Blog_Posts'    =>   'Últimos Posts de Blog',
	'Article_By'          =>   'por',
	'WP_Category'          =>   'Categoría',
	'WP_Posted_On'          =>   'Posteado en',
	'default_blogname'       =>   'Mi Blog',
	'default_blogdesc'       =>   'Necesito dar una descripción de mi blog...',
	'Log_me_in'          =>   'Recordarme',
	'submit'               =>   'Enviar',
	'Search_new'      => 'Posts desde la última visita',
	// Please translate me:
	'read_more'				=> '%1sRead the rest of this blog post blog &raquo;%3s',
	'blog_post_cats'		=>	'Posted under: ',	
	'write_post'			=>	'Write Post',
	'admin_site'			=>	'Admin Site',
	'admin_forum'			=>	'Admin Forum',
	'newest_user'			=>	'Newest User: ',
	'registered_users'		=>	'Registered Users: ',
	'forum_posts'			=>	'Forum Posts: ',
	'forum_topics'			=>	'Forum Topics: ',
));

?>
