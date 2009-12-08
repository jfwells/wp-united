<?php
/** 
*
* WP-United [German]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.1 2009/05/18 psm (psm) Exp $
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

	//Moved out from install.xml
	'BLOG'         =>    'WordPress Blog',
	'VISIT_BLOG'      =>   'Benutzer\'s Blog besuchen',
	'ACP_WP_UNITED'    =>    'WP-United',
	'ACP_WPU_MAINPAGE'   =>   'WP-United Administration',
	'ACP_WPU_CATMAIN'   =>    'WP-United Admin',
	'ACP_WPU_CATSETUP'   =>   'WP-United Einstellung',
	'ACP_WPU_CATMANAGE'   =>   'Benutzer-Integration einstellen',
	'ACP_WPU_CATSUPPORT'   =>   'WP-United unterstützen',
	'ACP_WPU_CATOTHER'   =>   'Diverses',
	'ACP_WPU_MAINTITLE'   =>   'Hauptseite',
	'ACP_WPU_DETAILED'   =>   'Alle Einstellungen auf einer Seite',
	'ACP_WPU_WIZARD'   =>    'Setup Wizard',
	'ACP_WPU_USERMAP'   =>    'Benutzer mit WordPress synchronisieren',
	'ACP_WPU_PERMISSIONS'   =>    'Berechtigungen einstellen',
	'ACP_WPU_DONATE'   =>    'Spende an WP-United',
	'ACP_WPU_UNINSTALL'   =>    'WP-United deinstallieren',
	'ACP_WPU_RESET'      =>    'WP-United zurücksetzen',
	'ACP_WPU_DEBUG'		=>	'Debug Info to Post',	
	'WP_UNINSTALLED'    =>    'WP-United deinstalliert',
	'WP_INSTALLED'       =>    'WP-United installiert',


    'WP_DBErr_Gen'          =>   'Die Datenbanktabelle der WordPress-Integration konnte wurde nicht gefunden. Bitte stelle sicher, dass Du das mitgeliferte SQL-query beim installieren ausgeführt und das Mod über das Administrationsmenü korrekt konfiguriert hast.',
    'WP_No_Login_Details'    =>   'Fehler: Es konnte kein WordPress für Dich erstellt werden. Bitte wende Dich an einen Administrator.',
    'Function_Duplicate'    =>   'Der Name einer PHP-Funktion scheint doppelt vorhanden zu sein. Dies könnte unter anderem daran liegen, dass ein stark moddifiziertes Forum benutzt wird. Bitte melde diesen Fehler auf www.wp-united.com.',
    'WP_Not_Installed_Yet'    =>   'Das WP-United WordPress Integration Mod wurde nicht korrekt eingrichtet. Bitte benutze den Setup-Wizard. Diesen findest Du im Reiter "WP-United" im phpBB-Administrationsbereich.',
    'WPU_Credit'          =>   '%sWP-United%s Integration',
    'get_blog'             =>   'Erstelle Deinen Blog',
    'add_to_blog'          =>   'Blog-Eintrag',
    'go_wp_admin'      =>      'Zum Adminstrations-Panel',
    'blog_intro_get'       =>   "%sKlicke hier%s, um Deinen Blog heute noch zu starten!",
    'blog_intro_add'       =>   "%sKlicke hiere%s, um Deinen Blog einen Eintrag hinzuuufügen.",
    'blog_intro_loginreg_ownblogs'    =>   "%sRegistriere%s oder %slog%s Dich ein um Deinen Blog zu starten.",
    'blog_intro_loginreg'    =>   "%sRegistriere%s oder %slog%s Dich zum teilnehmen ein",
    'Latest_Blog_Posts'    =>   'Neuesten Blog Einträge',
    'Article_By'          =>   'von',
    'WP_Category'          =>   'Kategorie',
    'WP_Posted_On'          =>   'Erstellt am',
    'default_blogname'       =>   'Mein Blog',
    'default_blogdesc'       =>   'Ich brauche eine Blog-tagline...',
    'Log_me_in'          =>   'Merken',
    'submit'               =>   'Senden',
    'Search_new'      => 'Nachrichten seit dem letzten Besuch',
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
