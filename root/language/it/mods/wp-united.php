<?php
/** 
*
* WP-United [Italiano]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.1 japgalaxy (japgalaxy.altervista.org) 2009/05/18
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
'Blog' 					=>	'WordPress Blog',
'WP_DBErr_Gen' 			=>	'Impossibile accedere alla tabella nel database della configurazione dell\integrazione di Wordpress. Si prega di accertarsi che sia stata eseguita la query SQL durante l\'installazione di WP-United, e di averlo configurato utilizzando l\'apposito modulo dal pannello di Amministratore di phpBB.',
'WP_No_Login_Details' 	=>	'L\'account Wordpress non pu&ograve; essere creato. Si prega di contattare un amministratore.',
'WP_DTD' 				=>	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//IT" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
'Function_Duplicate' 	=>	'E\' stato rilevato un duplicato del nome di una funzione. Probabilmente questo &egrave; dovuto ad un forum con molte Mod installate. Per favore visita www.nuovosito.com e riporta l\'errore riscontrato.',
'WP_Not_Installed_Yet' 	=>	'WP-United non &egrave; ancora stato impostato correttamente sul tuo sito. Si prega di eseguire il programma di installazione guidata che si trova nel modulo "WP-United" dal Pannello di Amministrazione di phpBB.',
'WPU_Credit' 			=>	'Integration by %sWP-United%s',
'get_blog' 				=>	'Crea Il Tuo Blog',
'add_to_blog' 			=>	'Scrivi sul tuo Blog',
'go_wp_admin'			=>	'Vai al Pannello di Amministrazione',
'blog_intro_get' 		=>	"%sClicca qui%s per iniziare con il tuo blog oggi!",
'blog_intro_add' 		=>	"%sClicca qui%s per scrivere sul tuo blog.",
'blog_intro_loginreg_ownblogs' 	=>	"%sRegistrati%s o %seffettua il Login%s per iniziare a creare il tuo blog.",
'blog_intro_loginreg' 	=>	"%sRegistrati%s o %seffettua il Login%s per partecipare",
'Latest_Blog_Posts' 	=>	'Gli ultimi posts del Blog',
'Article_By' 			=>	'da',
'WP_Category' 			=>	'Categorie',
'WP_Posted_On' 			=>	'Postato in',
'default_blogname' 		=>	'Il mio Blog',
'default_blogdesc' 		=>	'Devo ancora inserire una descrizione per il mio blog...',
'Log_me_in' 			=>	'Ricordami',
'submit'				=>	'Invia',
'Search_new'			=> 'Nuovi posts dalla tua ultima visita',
'blog_title_prefix'	=>	'[BLOG]: ',
'blog_post_intro'		=>	'Questo &egrave; un [b]post del blog[/b]. Per leggere il post originale, %1sclicca qui &raquo;%2s',
'read_more'				=> '%1sLeggi questo post &raquo;%2s',
'blog_post_cats'		=>	'Postato in: ',
'blog_post_tags'		=>	'Tags: ',
'write_post'			=>	'Scrivi Articolo',
'admin_site'			=>	'Amministra Sito',
'admin_forum'			=>	'Amministra Forum',
'newest_user'			=>	'Ultimo iscritto: ',
'registered_users'		=>	'Totale iscritti: ',
'forum_posts'			=>	'Totale messaggi: ',
'forum_topics'			=>	'Totale argomenti: ',
));

?>
