<?php
/** 
*
* WP-United [Svenska]
*
* @package WP-United
* @version $Id: v0.8.0RC2 2010/01/14 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* @translation by Oz
*/


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
	'BLOG' 					=>	'WordPress Blogg',
	'VISIT_BLOG'			=>	'Besök Användarens Blogg',
	'ACP_WP_UNITED' 		=> 	'WP-United',
	'ACP_WPU_MAINPAGE'		=>	'WP-United Administration',
	'ACP_WPU_CATMAIN'		=> 	'WP-United Admin',
	'ACP_WPU_CATSETUP'		=>	'Ställ in WP-United',
	'ACP_WPU_CATMANAGE'		=>	'Hantera Användar-Integration',
	'ACP_WPU_CATSUPPORT'	=>	'Support WP-United',
	'ACP_WPU_CATOTHER'		=>	'Övrigt',
	'ACP_WPU_MAINTITLE'		=>	'Huvudsida',
	'ACP_WPU_DETAILED'		=>	'Alla Inställningar på en sida',
	'ACP_WPU_WIZARD'		=> 	'Installationsguide',
	'ACP_WPU_USERMAP'		=> 	'Användar Integration Kartläggningsverktyg',
	'ACP_WPU_PERMISSIONS'	=> 	'Administrera behörigheter',		
	'ACP_WPU_DONATE'		=> 	'Donera till WP-United',
	'ACP_WPU_UNINSTALL'		=> 	'Avinstallera WP-United',
	'ACP_WPU_RESET'			=> 	'Återställ WP-United',
	'ACP_WPU_DEBUG'			=>	'Debug Info till Inlägg',	
	'WP_UNINSTALLED' 		=> 	'Avinstallerad WP-United',
	'WP_INSTALLED' 			=> 	'Installerad WP-United',

	'WP_DBErr_Gen' 			=>	'Kunde få tillgång WordPress integration konfiguration i databasen. Se till att du har installerat WP-United ordentligt.',
	'WP_No_Login_Details' 	=>	'Fel: Ett WordPress konto kan inte skapas åt dig. Vänligen kontakta en administratör.',
	'WP_DTD' 				=>	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	'Function_Duplicate' 	=>	'En dubblett funktionsnamnet har upptäckts. Detta beror förmodligen på ett kraftigt moddad forum. Besök www.wp-united.com för att rapportera felet.',
	'WP_Not_Installed_Yet' 	=>	'WP-United har ännu inte korrekt inställd på din webbplats. Kör installationsguiden som finns i phpBB Admin Control Panel.',
	'WPU_Credit' 			=>	'Integration av %sWP-United%s',
	'get_blog' 				=>	'Skapa Din Blogg',
	'add_to_blog' 			=>	'Lägg till Din Blogg',
	'go_wp_admin'			=>	'Gå till Admin Panelen',
	'blog_intro_get' 		=>	"%sKlicka Här%s för att sätta igång med din blogg idag!",
	'blog_intro_add' 		=>	"%sKlicka Här%s för att skriva i blogg.",
	'blog_intro_loginreg_ownblogs' 	=>	"%sRegistrera%s eller %sLogga in%s för att komma igång med din blogg.",
	'blog_intro_loginreg' 	=>	"%sRegistrera%s eller %sLogga in%s för att delta",
	'Latest_Blog_Posts' 	=>	'Senaste blogg-inlägg',
	'Article_By' 			=>	'av',
	'WP_Category' 			=>	'Kategori',
	'WP_Posted_On' 			=>	'Postad den',
	'default_blogname' 		=>	'Min Blogg',
	'default_blogdesc' 		=>	'Jag behöver en blogg tagline...',
	'Log_me_in' 			=>	'Kom ihåg mig',
	'submit'				=>	'Skicka',
	'Search_new'			=> 'Inlägg sedan senaste besök',
	'blog_title_prefix'	=>	'[BLOGG]: ',
	'blog_post_intro'		=>	'Detta är ett [b]blogg-inlägg[/b]. För att läsa orginella inlägget, %1sklicka här &raquo;%2s',
	'read_more'				=> '%1sLäs detta inlägg &raquo;%2s',
	'blog_post_cats'		=>	'Postad under: ',
	'blog_post_tags'		=>	'Etiketter: ',
	'write_post'			=>	'Skriv Inlägg',
	'admin_site'			=>	'Admin Sidan',
	'admin_forum'			=>	'Admin Forum',
	'newest_user'			=>	'Senaste Användare: ',
	'registered_users'		=>	'Registrerade Användare: ',
	'forum_posts'			=>	'Forum Inlägg: ',
	'forum_topics'			=>	'Forum Ämne: ',
	'wpu_dash_copy'	=> 'phpbb integration &copy; 2006-2010 %1sWP-United%2s',
	'wpu_welcome'		=>	'Välkommen till WP-United.',
	'wpu_write_blog_pre250'	=>	'Klicka %1sSkriv%2s för att skriva i din blogg',
	'wpu_write_blog'	=>	'Klicka %1sInlägg%2sNy%3s för att skriva nytt inlägg',
	'wpu_blog_intro_appearance' => 'Du kan ställa in utseendet under %1sDin Blogg%2s tabben.',
	'wpu_blog_panel_heading' => 'Din Blogg',
	'wpu_blog_settings' => 'Din Blogg Deta',
	'wpu_blog_theme'	=> 'Ställ in Blogg Tema',
	'wpu_blog_your_theme' => 'Ställ in Din Blogg Tema',
	'wpu_blog_details'	=> 'Din Blogg Detailjer',
	'wpu_blog_about' => 'Om Din Blogg',
	'wpu_blog_about_title' => 'Din Bloggs Titel:',
	'wpu_blog_about_tagline' => 'Blogg Tagline:',
	'wpu_update_blog_details' => 'Updatera blogg detailjer',
	'wpu_theme_broken' => 'Temat du valt för din blogg funkar inte. Återgå till den förvalda temat.',
	'wpu_theme_activated' => 'Ny tema aktiverad. %1sSe din blogg &raquo;%2s',
	'wpu_more_themes_head' => 'Vill ha flera teman??',
	'wpu_more_themes_get' => 'Om du har hittat en annan WordPress tema som du vill använda, vänligen meddela en administratör.',
	'wpu_user_media_dir_error' => 'Går inte att skapa katalogen %s. Detta beror förmodligen på dess ovanliggande  katalog är inte skrivbar. Tala om det för en administratör.',
	'wpu_access_error' => 'Du borde inte vara här.',
	'wpu_no_access' => 'Ingen åtkomst',
	'wpu_xpost_box_title' => 'Cross-post till Forums?',
	'wpu_already_xposted' => 'Redan cross-postad (Ämnes ID = %s)',
	'wpu_forcexpost_box_title' => 'Forum Inlägg',
	'wpu_forcexpost_details' => 'Detta inlägg kommer att cross-postas till forum: \'%s\'',
	'wpu_user_edit_use_phpbb' => '<strong>OBS:</strong> Merparten av denna användares information kan skötas med hjälp av forumet. %1sKlicka här för att se/redigera%2s.',
	'wpu_profile_edit_use_phpbb' => '<strong>OBS:</strong> De flesta av dina uppgifter kan redigeras i din forum-profil. %1sKlicka här för att se/redigera%2s.',
	'wpu_userpanel_use_phpbb' => '<strong>OBS:</strong> Integrerade användares information kan och bör redigeras med hjälp av forumet via länkarna nedan.',
	'wpu_more_smilies' => 'Flera smilies',
	'wpu_less_smilies' => 'Mindre smilies',
	'wpu_blog_intro' => '%1s, av %2s',
	'wpu_total_entries' => 'Total Inlägg: ',
	'wpu_last_entry' => 'Senaste Inlägg: %1s, postad den %2s',
	'wpu_rss_feed' => 'RSS Feed: ',
	'wpu_rss_subscribe' => 'Prenumerera',
	'wpu_no_user_blogs' => 'Det finns inga bloggar för att visa',
	'wpu_latest_blogposts_format' => '%1s, i %2s',
	'wpu_forum_stats_posts' => 'Foruminlägg: %s',
	'wpu_forum_stats_threads' => 'Forum Trådar: %s',
	'wpu_forum_stats_users' => 'Registrerade användare: %s',
	'wpu_forum_stats_newest_user' => 'Senaste användare: %s',
	'wpu_nothing' => 'Ingenting hittades.',
	'wpu_phpbb_post_summary' => '%1s, postad av %2s den %3s',
	'wpu_phpbb_topic_summary' => '%1s, postad av %2s i %3s',
	'wpu_write_post' => 'Skriv ett inlägg',
	'wpu_loginbox_desc' => 'WP-United Login/Användar-Info',
	'wpu_forumtopics_desc' => 'WP-United Senaste phpBB Ämnen',
	'wpu_stats_desc' => 'WP-United Forum Statistik',
	'wpu_forumposts_desc' => 'WP-United Senaste phpBB Inlägg',
	'wpu_online_desc' => 'WP-United Användare Online',
	'wpu_bloglist_desc' => 'WP-United Nyligen Updaterade Blogg Lista',
	'wpu_blogposts_desc' => 'WP-United Senaste inlägg i Bloggar',
	'wpu_loginbox_loggedin' => 'Du är inloggad som:',
	'wpu_loginbox_loggedout' => 'Du är inte inloggad.',
	'wpu_loginbox_panel_loggedin' => 'Rubrik att visa när läsaren är inloggad:',
	'wpu_loginbox_panel_loggedout' =>'Rubrik att visa när läsare är inte inloggad:',
	'wpu_loginbox_panel_rank' => 'Visa rank titel och bild?',
	'wpu_loginbox_panel_newposts' => 'Visa nya inlägg?',
	'wpu_loginbox_panel_write' => 'Visa Skriv ett inläg länk?',
	'wpu_loginbox_panel_admin' => 'Visa Admin länk?',
	'wpu_loginbox_panel_loginform' => 'Visa phpBB logga-in formulär om utloggad?',
	'wpu_bloglist_panel_title' => 'Nyligen uppdaterade bloggar',
	'wpu_panel_heading' => 'Rubrik:',
	'wpu_panel_max_entries' => 'Högsta Inlägg:',
	'wpu_blogposts_panel_title' => 'Senaste inlägg i Bloggar',
	'wpu_forumtopics_panel_title' => 'Senaste forumämnen',
	'wpu_forumposts_panel_title' => 'Senaste foruminlägg',
	'wpu_forumposts_panel_date' => 'Datum format:',
	'wpu_stats_panel_title' => 'Forum Stats',
	'wpu_online_panel_title' => 'Användare Online',
	'wpu_online_panel_breakdown' => 'Visa en uppdelning av användar-typer?',
	'wpu_online_panel_record' => 'Visa rekord antal användare?',
	'wpu_online_panel_legend' => 'Show legend?',
	'edit_phpbb_details' => 'Redigera forum detaljer',
	'WP_DBErr_Retrieve' => 'Inte kunde komma åt databasen. Se till att du körde wpu-install.php vid installation av WP-United.'
	'wpu_hook_error' => 'Den WP-United phpBB hook filen, hook_wp-united.php, har inte laddats. Antingen saknas, eller så behöver du rensa phpBB cache. <br /> <br /> Försökte att automatiskt rensa phpBB cache. Prova <a href="#" onclick="document.location.reload(); return false;"> uppdatera sidan </ a> för att se om det fungerade. <br /> <br /> Om felet kvarstår, kontrollera att includes/hooks/hook_wp-united.php existerar, och försök att manuellt rensa din phpBB cache.',
	'wpu_smiley_error' => 'Din smiley kunde inte införas. Du kan lägga till det manuellt genom att skriva in denna kod: %s',
	'wpu_comment_view_link' => '(Visa i forum)'

));

?>