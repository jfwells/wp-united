<?php
/**
*
* WP-United [Spanish]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.5.2 2007/07/15 Raistlin (Raistlin) Exp $
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
    'ACP_WPU_INDEX_TITLE'   => 'WP-United',
        
    'WPU_Default_Version' => 'v0.6.0-RC1',
    'WP_Version_Text' => 'WP-United %s',

    'WP_Title' => 'WP-United Opciones',
    'WP_Intro1' => 'Bienvenido a WP-United RC1.',
    'WP_Intro2' => 'Los ajustes de arriba necesitan ajustarse correctamente para que este mod funcione correctamente.',
    'WP_Settings' => 'Ajustes básicos',
    'WP_UriTitle' => 'URL a tu instalación de Wordpress',
    'WP_UriExplain' => 'Esta es la url completa a tu instalación de Wordpress. Es la url que tu wordpress tendría si no estuviera integrado con phpBB. Por ejemplo, http://www.ejemplo.com/wordpress/',
    'WP_UriProb' => 'No he podido encontrar una instalación de Wordpress aquí. Asegúrate de que este campo contiene la url externa completa a tu instalación de wordpress.',
    'WP_PathTitle' => 'Ruta a la carpeta de Wordpress',
    'WP_PathExplain' => 'Esta es la ruta completa a la carpeta de WordPress. Si dejas esto en blanco, intentaremos detectarlo automáticamente. Si la detección automática no funciona, tendrás que escribir la ruta a la instalación de wordpress a mano. Debería ser algo como /home/public/www/wordpress -- pero en cada host esto será diferente.',
    'WP_PathProb' => 'No he podido encontrar una instalación de Wordpress aquí, o detectarlo automáticamente. Porfavor,escribe la ruta correcta a tu instalación de Wordpress aquí.',
    'WP_Submit' => 'Enviar',
    'WP_Login_Title' => 'Dejar que phpBB maneje automáticamente los logins de Wordpress',
    'WP_Login_Explain' => 'Si activas esta opción, phpBB creará una cuenta en Wordpress la primera vez que cada usuario de phpBB se identifique (login). Si tu instalación de Wordpress no va a ser interactiva (por ejemplo,blogs por usuario,un portal,etc), puedes querer desactivar esta opción, ya que para leer no se necesita cuenta en Wordpress. Puedes también integrar usuarios de Wordpress con los de phpBB, usando la utilidad de intrgración que aparecerá cuando actives esta opción.<br /> Puedes definir los privilegios para cada suario usando el menú de permisos de WP-United dentro del sistema de permisos de phpBB3.',
    'WP_Yes' => 'Sí',
    'WP_No' => 'No',
    'WP_Footer' => 'WP-United está desarrollado por John Wells, www.wp-united.com . Si encontraste este mod útil, ¡por favor,haz una donación!',

    'WPU_Must_Upgrade_Title' => 'Actualización detectada',
    'WPU_Must_Upgrade_Explain1' => 'Has actualizado WP-United. Debes usar la %sUtilidad de Actualización%s antes de continuar.<br /><br />Luego puedes volver y usar el Instalador.',
    'WPU_Must_Upgrade_Explain2' => '[Nota: Si nunca has activado la integración de usuarios, puedes %spulsar aquí%s para ignorar este aviso.]',
    'WPU_Not_Installed' => '(No Instalado)',


    'WPU_URL_Not_Provided' => 'ERROR: ¡No añadiste la URL de Wordpress! La ruta a menudo puede ser detectada automáticamente, pero DEBES dar la URL a tu instalación de Wordpress. Por favor,inténtalo de nuevo.',
    'WPU_URL_Diff_Host' => 'AVISO: La URL de Wordpress que has introducido parece estar en un dominio diferente a tu phpBB. Tiene que accederse a tu instalación de Wordpress por el mismo host que a tu instalaciónd e phpBB. No se podrá detectar automáticamente la ruta.',
    'WPU_OK' => 'OK',
    'WPU_URL_No_Exist' => 'AVISO: The Base WordPress URL you entered does not appear to exist (it returned a 404 error)! Please check that you have typed it correctly, and that you have installed WordPress.',
    'WPU_Cant_Autodet' => 'ERROR: The path cannot be autodetected as the URL you provided appears to be on a different host. Please correct the URL or provide the file path to WordPress.<br />',
    'WPU_Path_Autodet' => 'Trying to automatically detect the file path... ',
    'WPU_Autodet_Error' => 'ERROR: The path to your WordPress install could not be auto-detected. Please type it manually.<br />',
    'WPU_Pathfind_Warning' => 'AVISO: No se ha podido encontrar una instalación activa de Wordpress(Buscando por: %s). La ruta ha sido escrita o detectada automáticamente de forma incorrecta, o Wordpress no ha sido instalado. Por favor,revisa la ruta que has introducido y prueba de nuevo.',
    'WPU_Conn_InstallError' => 'ERROR: La Conexión Wordpress United no ha podido ser instalada. Por favor,revisa que la ruta que has introducido (o ha sido detectada automáticamente) sea correcta, y que Wordpress está instalado y funcionando.<br />',
    'WPU_PathIs' => "Ruta a la carpeta=> ",
    'WPU_Checking_URL' => 'Revisando la URL de Wordpress: ',
    'WPU_Process_Settings' => 'Procesando Ajustes...',
    'WPU_Checking_URL' => 'Revisando que la URL existe... ',
    'WPU_Checking_WPExists' => 'Revisando que una instalación de Wordpress existe aquí... ',
    'WPU_Conn_Installing' => 'Instalando WP-United Connection... ',


    'WP_AllOK' => 'Todo OK. Los ajustes han sido guardados.',
    'WP_Saved_Caution' => 'Tus ajustes han sido guardados, pero probablemente no funcionen hasta que retrocedas y corrijas los errores de arriba.',
    'WP_Errors_NoSave' => 'Se encontraron errores y tus ajustes no fueron guardados. Por favor,retrocede y corrige los errores.',
    'WP_DBErr_Retrieve' => 'No se ha podido acceder a la tabla de integración de Wordpress en la base de datos. Por favor,asegúrate de que utilizaste la consulta SQL incluída cuando instalaste el mod.',
    'WP_DBErr_Write' => 'No se pudieron añadir nuevos valores en la base de datos. Por favor,asegúrate de que utilizaste la consulta SQL incluída cuando instalaste el mod.',
    'WP_Config_GoBack' => 'Click %sAquí%s para volder a la Página Principal de Administración de WP-United.',
    'WP_Perm_Title' => 'PhpBB &lt;-&gt; WordPress Permissions Mapping (requiere JavaScript)',

    'WP_Perm_Explain1' => 'Si phpBB maneja los logins de Wordpress, puedes integrar los roles de Wordpress con los permisos de phpBB y/o grupos aquí. ¡Ten cuidado de no dar a posteadores regulares permisios demasiado altos!',
    'WP_Perm_Explain2' => 'A la izquierda están los grupos de phpBB basados en permisos(como Usuarios Regulares, Moderadores y Administradores) y los grupos de phpBB definidos por el usuario. Las cajas de la derecha representan permisos de Wordpress.',
    'WP_Perm_Explain3' => 'Haz click en un grupo de phpBB de la izquierda, y luego selecciona la caja de la derecha correspondiente a los permisos de Wordpress que quieres dar a todos los miembros que sean de ese grupo. Luego haz click en la flecha que apunta a la derecha para asignarlos. Los grupos que queden en la caja de la izqueirda no serán integrados. Para una mejor comprensión de los permisos de Wordpress 2 revisa el <a href=>"http://codex.wordpress.org/Roles_and_Capabilities">WordPress Codex</a>.',
    'WP_Perm_Explain4' => 'PRECAUCIÓN -- ASEGÚRATE DE QUE ENTIENDES ESTO: Cuando hay un conflicto de permisos, los permisos más altos sobreescriben a los permisos más bajos. Por ejemplo, si un miembro pertenece a más de un grupo, él tendrá el rol más alto disponible en los dos grupos.Igualmente,si un usuario es Moderador, él tendrá el rol asignado para Moderador si ese rol es más alto que el rol asignado a un grupo del que también sea miembro.',

    'WP_Login_Head' => 'Ajustes de integración de Login y Permisos',

    'WP_User_Title' => 'Usuarios registrados de phpBB',

    'WP_User_Explain' => 'Asigna el rol que los usuarios normales de PhpBB deberían tener en WordPress.',
    'WP_Mod_Title' => 'Moderadores de phpBB',

    'WP_Mod_Explain' => 'Asigna el rol que los moderadores de PhpBB deberían tener en WordPress.',

    'WP_Admin_Title' => 'Administradores de Wordpress',

    'WP_Admin_Explain' => 'Asigna el rol que los moderadores de PhpBB deberían tener en WordPress. NOTA: El usuario de wordpress llamado \'admin\', no será integrado y continuará teniendo derechos completos de administrador.',

    'WP_Advanced_Head' => 'Ver ajustes',
    'WP_DTD_Title' => '¿Usar Differente Tipo de Declaración de Documento?',
    'WP_DTD_Explain' => 'El Tipe de Declaración de Documento, o DTD, viene dada arriba de todas las páginas webs para dejar que el explorador sepa qué tipo de lenguaje está siendo usado.<br /><br />phpBB3\'s prosilver usa XHTML 1.0 Strict DTD por defecto. La mayoría de los templates de Wordpress, sin embargo, usan XHTML 1 transitional DTD.<br /><br />En la mayoría de los casos,esto no importa-- Sin embargo, si quieres usar el Wordpress\' DTD en páginas en las que Wordpress está dentro de phpBB, entonces puedes activar esta opción. Esto debería prevenir a los exploradores de entrar en &quot;quirks mode&quot;, y asegurará de que incluso más templates de Wordpress se muestran como se diseñaron.',
    'WP_Role_None' => 'ninguno',
    'WP_Role_Subscriber' => 'Subscriber',
    'WP_Role_Contributor' => 'Contributor',
    'WP_Role_Author' => 'Author',
    'WP_Role_Editor' => 'Editor',
    'WP_Role_Administrator' => 'Administrador',

    //Main Page
    'WP_Main_Title' => 'Bienvenido to WP-United BETA',
    'WP_Main_Intro' => 'Desde aquí puedes ajustar WP-United, y elegir los ajustes que necesites para hacer que funcione como tú quieras.',
    'WP_Main_IntroFirst' => 'No has ajustado el mod. Recomendamos usar el Instalador,arriba.',
    'WP_Main_IntroAdd' => 'Ya has ajustado el mod. Puedes cambiar las opciones clickeando en el botón de Ajustes arriba.',
    'WP_Recommended' => 'Recomendado',
    'WP_Wizard_Title' => 'Instalador',
    'WP_Detailed_Title' => 'Ajustes',
    'WP_Wizard_Explain' => 'Este instalador te guiará por la instalación incial de WP-United de nuevo. Ten en cuenta que debes seguirlo hasta la última página.',
    'WP_Wizard_ExplainFirst' => 'Este instalador te guiará por los ajustes de WP-United. Como no has ajustado todavía el Mod, debes usar esto antes.',
    'WP_Detailed_ExplainFirst' => 'Aquí puedes revisar todos los ajustes asociados con WP-United. Como no has ajustado todavía el Mod, debes usar el instalador de arriba primero.',
    'WP_Detailed' => 'Aquí puedes revisar todos los ajustes asociados con WP-United.',
    'WP_Detailed_Explain' => 'Aquí están todos los ajustes en una sola página.',
    'WP_SubmitWiz' => 'Instalador',
    'WP_SubmitDet' => 'Ajustes',
    'WP_MapLink_Title' => 'Utilidad de Integración de Usuarios',
    'WP_MapLink' => 'Ir',
    'WP_MapLink_Explain' => 'La integración de usuarios está activada. Para revisar el estado de los usuarios de Wordpress, haz click en el botón para acceder a la Utilidad de Integración de Usuarios',

    'WP_Support' => 'Ayuda a apoyar a WP-United',
    'WP_Support_Explain' => 'WP-United software gratuito, y esperamos que lo encuentres útil. Si es así, por favor,¡ayudanos haciendo una donación aquí! Cualquier cantidad,aunque sea pequeña,será muy agradecida. ¡Gracias!<br /><br />El link de PayPal te llevará a una página de donación para nuestra cuenta de Paypal, \'Pet Pirates\'',



    //Wizard
    'WP_Wizard_H1' => 'WP-United Instalador',
    'WP_Wizard_Step' => 'Paso %s de %s.',
    'WP_Wizard_Next' => 'Continua al paso %s -&gt;',
    'WP_Wizard_Back' => '&lt;- Vuelve al paso %s',
    'WP_Wizard_BackStart' => '&lt;- Vuelve al incio',
    'wizErr_invalid_URL' => "La URL a WordPress que has escrito parece no válida. (Debería ser la URL completa,no relativa). ",
    'wizErr_invalid_Blog_URL' => "La URL a la integración que escribiste parece ser inválida. (Debería ser la URL completa,no relativa). ",
    'wizErr_invalid_Path' =>  "La ruta que escribiste parece ser inválida. ",

    //Wizard Step 1
    'WP_Wizard_Step1_Title' => 'Instalación de Wordpress',
    'WP_Wizard_Step1_Explain1' => 'Asegúrate de que has instalado Wordpress.',
    'WP_Wizard_Step1_Explain2' => 'Puedes instalarlo en cualquier lugar del mismo espacio web que tu phpBB. No tiene que estar dentro del directorio de tu phpBB, pero puede estarlo si tú quieres.',
    'WP_Wizard_Step1_Explain2b' => 'El archivo dado, blog.php, Será usado para acceder a las páginas integradas de tu Wordpress. Puedes renombrar este archivo y ponerlo donde quieras(por ejemplo, puedes renombrarlo a index.php y ponerlo en la raíz de tu sitio). Si lo renombras o lo mueves, debes abrir el archivo e introducir la ruta a phpBB donde se indica.',
    'WP_Wizard_Step1_Explain3' => 'WordPress requiere una base de datos MySQL. Si lo necesitas, puedes usar tu base de datos de phpBB existente. Si lo haces,debes asegurarte que las tablas de Wordpress usan un prefijo diferente a las de phpBB.',
    'WP_Wizard_Step1_Explain4' => 'Si estás planeando integrar una instalación de Wordpress existente, por favor revisa los nombres de usuario de Wordpress. Cuando WP-United esté instalado, podrás integrar los usuarios de Wordpress con los de phpBB si lo necesitas.',
    'WP_Wizard_Step1_Explain5' => 'Una vez hayas instalado Wordpress,Haz click en "Siguiente" para continuar. Si quieres cerrar el instalador, puedes volver a empezar por la misma localización después.',


    //Step Wizard 1B
    'WP_Wizard_Step1b_Title' => 'Localización de la Instalación de Wordpess',
    'WP_Wizard_Step1b_Explain1' => 'Aquí necesitamos algunos detalles para saber donde instalaste Wordpress',

    'WP_Wizard_Step1b_TH1' => 'WordPress URL',
    'WP_Wizard_URI_Explain' => 'Por favor,escribe la URL completa a la instalación de Wordpress. This is the URL that WordPress would have if it were not integrated into phpBB. For example, http://www.example.com/wordpress/',
    'WP_Wizard_URI_Test_Title' => 'Test URL',
    'WP_Wizard_URI_Test_Explain' => 'Click en el boton de la derecha para comprobar que la url que entraste es válida.',
    'WP_URI_Test' => 'Test URL',

    'WP_Wizard_Step1b_TH2' => 'Ruta de instalacion de',
    'WP_Wizard_Path_Explain1' => 'Aquí necesitamos la ruta completa a Wordpress. Debería ser algo como /home/public/www/wordpress -- pero es diferente según el host.',
    'WP_Wizard_Path_Explain2' => 'Este instalador puede intentar detectar automáticamente la ruta por ti para no tener que escribirla. Si la prueba falla, o si prefieres escribirla por ti mismo, puedes introducirla manualmente.',
    'WP_Path_Test' => 'Detectar ruta',
    'WP_Wizard_Path_Explain3' => ' or introducirla manualmente aquí: ',

    'WP_Wizard_URI_Error' => "ERROR! Debes introducir una URL válida!",
    'WP_Wizard_Path_Error' => "ERROR! debes introducir una ruta válida!",

    'WPWiz_BlogURI_TH' => 'Nueva dirección de la integración',
    'WPWiz_BlogURI_Title' => 'La dirección que usarás para acceder a Wordpress a partir de ahora',
    'WPWiz_BlogURI_Explain1' => 'Por defecto, blog.php, en tu carpeta de phpBB,será usada para acceder a Wordpress a partir de ahora.',
    'WPWiz_BlogURI_Explain2' => 'Sin embargo,si quieres cambiar esto, mueve y/o renombra blog.php a la localización que desees. Por ejemplo, podrías renombrarlo a index.php y ponerlo en la raiz de tu sitio.',
    'WPWiz_BlogURI_Explain3' => 'Ncesitarás abrir el archivo y escribir la ruta a la carpeta de phpBB.',
    'WPWiz_BlogURI_Explain4' => 'Si quieres moverlo a la raíz de Wordpress,puedes renombrar el antiguo index.php a index-old.php por ejemploesto no será necesitado.',
    'WPWiz_BlogURI_Explain5' => 'Por favor,haz esto <strong>ahora</strong>, y luedo vuelve y escribe la dirección desde la cual quieres acceder a Wordpress.',


    'WP_No_JavaScript' => 'NOTICE: Esta página tiene algunas características que no puedes ver,porque tu explorador es antiguo o no tienes Javascript activado.',
    'WP_AJAX_DataError' => 'ERROR: No se pudo entender la respuesta dada por el servidor!',

    //Wizard Step 2
    'WP_Wizard_Step2_Title' => 'Ajustes de integración',
    'WP_Wizard_Step2_Explain' => 'En este paso, ajustaremos la forma en el que los users se logean en Wordpress',

    'WPWiz_IntLogin_title' => 'Las opciones siguientes deben ser ajustadas si quieres integrar los logins...',


    //Wizard Step 3
    'WP_Wizard_Step3_Title' => 'Ajustes de Vision e &amp; Integración',
    'WP_Wizard_Display_Title' => 'Ajustes de Visión',
    'WP_Wizard_Behave_Title' => 'Ajustes de Integración',
    'WP_Wizard_Step3_Explain' => 'Necesitamos ajustar la forma en la que phpBB y Wordpress se ven e interaccionan.',
    'WPWiz_Inside_Title' => 'Integrar phpBB &amp; WordPress Templates?',
    'WPWiz_Template_Forward' => 'WordPress dentro phpBB',
    'WPWiz_Template_Reverse' => 'phpBB dentro WordPress',
    'WPWiz_Template_None' => 'No integrar templates',
    'WPWiz_Inside_Explain1' => "WP-United puede integrar tus templates de phpBB &amp; Wordpress.",
    'WPWiz_Inside_Explain2' => "You can choose to have WordPress appear inside your phpBB header and footer, or have phpBB appear inside your WordPress page, or neither. The options below will vary depending on which you choose.",
    'WPWiz_Template_Forward_Title' => 'The following options must be set if you choose to put WordPress inside phpBB...',
    'WPWiz_Template_Reverse_Title' => 'The following options must be set if you choose to put phpBB inside WordPress...',

    'WPWiz_Padding_Title' => 'Padding around phpBB',
    'WPWiz_Padding_Explain1' => 'phpBB is inserted on the WordPress page inside a DIV. Here you can set the padding of that DIV',
    'WPWiz_Padding_Explain2' => 'This is useful because otherwise the phpBB content may not line up properly on the page. The defaults here are good for most WordPress templates.',
    'WPWiz_Padding_Explain3' => 'If you would prefer set this yourself, just leave these boxes blank (not \'0\'), and style the \'phpbbforum\' DIV in your stylesheet.',
    'WPWiz_Pixels' => 'pixels',
    'WPWiz_PaddingTop' => 'Top',
    'WPWiz_PaddingRight' => 'Right',
    'WPWiz_PaddingBottom' => 'Bottom',
    'WPWiz_PaddingLeft' => 'Left',

    'WPWiz_WPPage_OptTitle' => 'If you want to use a full WordPress page, you must set the following option...',
    'WPWiz_Page_Title' => 'Select a Full Page Template',
    'WPWiz_Page_Explain1' => 'Here you can choose the WordPress page template to be used for you phpBB forum. For example, it could be your index page, your single post content page, or an archives page.',
    'WPWiz_Page_Explain2' => 'Just type in the name of the template (e.g. \'index.php\', \'single.php\' or \'archive.php\') here. If the file can\'t be found WP-United will default to using page.php.',



    'WPWiz_CharEnc_Title' => 'Character Encoding',
    'WPWiz_CharEnc_Explain1' => 'phpBB2 by default uses the iso-8859-1 character set, while WordPress is by default set to use UTF-8. Therefore, when the page is integrated, some characters in WordPress or phpBB posts may not display correctly.',
    'WPWiz_CharEnc_Explain2' => 'Here, you can choose to alter the phpBB character set to match that of WordPress for the integrated page, or change WordPress\' character set to match that of phpBB. It is recommended that you try &quot;Change phpBB\'s&quot; first -- if templates fail to display correctly, you can then try &quot;Change WordPress\'&quot; or &quot;No Change&quot;',
    'WPChar_MatchW' => 'Change phpBB\'s',
    'WPChar_MatchP' => 'Change WordPress\'',
    'WPChar_NoChange' => 'No Change',

    'WPWiz_PStyles_Early_Title' => 'Include phpBB Styles First?',
    'WPWiz_PStyles_Early_Explain1' => 'When templates are integrated, you will have two sets of styles for phpBB and WordPress. Sometimes, some CSS definitions can conflict with each other.',
    'WPWiz_PStyles_Early_Explain2' => 'On a page, styles that are defined later override those that are defined previously. So setting the order of the styles in the document is a quick way to resolve some style conflicts.',
    'WPWiz_PStyles_Early_Explain3' => 'For most template combinations, you will find that including phpBB styles first (so that they can be overridden by WordPress) is the best choice, however, you may want to try both to see which looks better.',
    'WPWiz_PStyles_Early_Explain4' => 'If you plan on putting all your styles, including those for phpBB, into a single template, you may want to turn phpBB styles off altogether.',

    'WPWiz_WPSimple_Title' => 'Simple Header and footer or full page?',
    'WPWiz_WPSimple_Explain1' => 'Do you want phpBB to simply appear inside your WordPress header and footer, or do you want it to show up in a fully featured WordPress page?',
    'WPWiz_WPSimple_Explain2' => 'Simple header and footer will work best for most WordPress themes &ndash; it is faster, works better, and will need less tweaks to the stylesheets.',
    'WPWiz_WPSimple_Explain3' => 'However, if you want the WordPress sidebar to show up, or use other WordPress features on the integrated page, you could try \'full page\'. This option could be a little slow.',

    'WPWiz_Simple_Yes' => 'Simple (recommended)',
    'WPWiz_Simple_No' => 'Full page',


    'WP_Yes_Recommend' => 'Yes (recommended)',
    'WP_No_Recommend' => 'No (recommended)',
    'WPWiz_No_PStyles' => 'Do not include phpBB styles',




    'WPWiz_Censor_Title' => 'Use phpBB Word Censor?',
    'WPWiz_Censor_Explain' => 'Turn this option on if you want WordPress posts to be passed through the phpBB word censor.',


    'WPWiz_Private_Title' => 'Make Blogs Private?',
    'WPWiz_Private_Explain' => 'If you turn this on, users will have to be logged in to VIEW blogs. This is not recommended for most set-ups, as WordPress will lose search engine visibility',


    //Wizard Step 5
    'WP_Wizard_Connection_Title' => 'WP-United Connection',
    'WP_Wizard_Connection_Title2' => 'Installing WP-United Connection...',
    'WP_Wizard_Connection_Explain1' => 'The WP-United Connection is the bridge between WordPress and phpBB. It controls how WordPress behaves when it is integrated.',
    'WP_Wizard_Connection_Explain2' => 'The Setup Wizard will now try to install the WP-United connection...',
    'WP_Wizard_Connection_Success' => 'Success! The WP-United Connection has been installed.',
    'WP_Wizard_Connection_Fail' => 'Error! The WP-United Connection could not be installed. This is probably due to one of the following being set incorrectly: (a) invalid path to WordPress, (b) invalid script path set for phpBB in board config. Please correct these and try again.',




    //Wizard Step 4
    'WP_Wizard_Step4_Title' => '&quot;To Blog or Not To Blog?&quot;',
    'WP_Wizard_Step4_Explain' => 'You have selected to integrate logins between phpBB and WordPress. If your intention is to allow your community members to create their own blogs, you can fine-tune the settings below.',
    'WPWiz_OwnBlogs_Title' => 'Give users their own blogs?',
    'WPWiz_OwnBlogs_Explain1' => 'If you turn this option on, each community member with an access level of "author" or above, can create their own blog. They will be able to choose the title, description, and (optionally), the appearance of their blog.',
    'WPWiz_BtnsProf_Title' => 'Blog links in profiles',
    'WPWiz_BtnsProf_Explain' => 'Turning this option on will put &quot;Blog&quot; links in the profiles of users which have active blogs. The links will go directly to their blogs.',
    'WPWiz_BtnsPost_Title' => 'Blog links under posts',
    'WPWiz_BtnsPost_Explain' => 'Turning this option on will put &quot;Blog&quot; links under posts (next to the PM, WWW, etc. buttons), of users who have active blogs. The links will go directly to their blogs.',
    'WPWiz_StyleSwitch_Title' => 'Users can choose theme',
    'WPWiz_StyleSwitch_Explain1' => 'This option gives users who can author posts the ability to choose the theme (template) for their own blog.',
    'WPWiz_StyleSwitch_Explain12' => 'The users can simply select the theme they want from the installed WordPress themes.',

    'WPWiz_Bloglist_Head' => 'Blogs Listing <em>(WP 2.1 or later only)</em>',
    'WPWiz_Bloglist_Title' => 'Blogs listing on Index Page',
    'WPWiz_Bloglist_Explain' => 'If you select this option, a page will be created that automatically shows a nice list of users\' blogs, with various information such as avatars, last post, etc. If you are running WordPress 2.1, this page will be automatically set as your home page.',
    'WPWiz_Bloglist_Explain2' => 'If you are giving users their own blogs, it is recommended that you turn this option on.',

    'WPWiz_BlogListHead_Title' => 'Blogs Homepage Title',
    'WPWiz_BlogListHead_Explain' => 'This is the title of your Blogs Home Page.',
    'WPWiz_BlogListHead_Default' => 'Blogs Home',
            
    'WPWiz_BlogIntro_Title' => 'Blogs Homepage Introduction',
    'WPWiz_BlogIntro_Explain' => 'This is the introdtory text to show on your blogs home page. ',
    'WPWiz_BlogIntro_Explain2' => 'The tag {GET-STARTED} will be replaced with a contextual link sentence encouraging people to register, login or create/add to their blog.',
    'WPWiz_NumBlogList_Title' => 'Blogs to list per page',
    'WPWiz_NumBlogList_Explain' => 'This set sthe number of blogs to show on each page of the list',
    'WPWiz_LatestPosts_Title' => 'Also show latest posts list?',
    'WPWiz_LatestPosts_Explain' => 'Here you can set if you also want the usual WordPress posts listing to appear below the blogs list. \'0\' will disable the listing. Set a number greater than 0 to set the number of posts to show.',
    'WPWiz_blCSS_Title' => 'Style list using WP-United CSS',
    'WPWiz_blCSS_Explain' => 'Leave this option on to use the provided WP-United CSS to style the blog list. The CSS is provided in the file wpu-blogs-homepage.css, in your template folder.',
    'WPWiz_blCSS_Explain2' => 'It is recommended that you leave this option on to begin with. However, once you are hapy with the styling of the list, you will probably want to copy the CSS from wpu-blogs-homepage.css into your main site stylesheet. Once you have done this, you can turn this option off to improve site performance.',
    'WPWiz_blogIntro_Default' => 'Welcome to our blogs! Here, community members can create their very own blogs. {GET-STARTED} Or, you can browse our members\' blogs below:',


    'WP_OwnBlogs_OptTitle' => 'The following options must be set if you allow users to have their own blogs...',
    'WP_Bloglist_OptTitle' => 'The following options must be set if you want to use the blogs listing...',


    //Phew! Wizard End
    'Wizard_Success1' => 'Success! You have completed the Setup Wizard. You can now access your integration %shere%s.',
    'Wizard_Success2' => 'You can change these settings at any time, by visiting the "WordPress Integration" page in the admin Control Panel.',

    //Other strings that get returned from functions
    'WP_URI_Found' => 'Success! A page was found at that location',
    'WP_cURL_Not_Avail' => 'ERROR: This test requires the cURL library, which is not available on your server. This test cannot proceed. However, if you are confident with the setting you may proceed below.',
    'WP_URI_Not_Found' => 'ERROR: Could not connect to URL (a 404 error was returned). Please check that you have typed it correctly, and that a page exists at that location. If you are confident that the setting is correct, you may proceed, but you probably need to address the problem first.',
    'WP_URI_OK_Diff_Host' => 'WARNING: A page was found at this location, but it appears to be on a different domain. You may proceed, but the automatic detection of file path below will not work, and the integration package may produce undesirable results. It is recommended that you correct this problem first.',
    'WP_URI_No_Diff_Host' => 'ERROR: Could not connect to URL. In addition, the domain name does not match the current domain, which is inadvisable. Please check that you have typed it correctly, and that a page exists at that location. It is recommended that you correct this problem first.',

    'WP_PathTest_Diff_Host' => 'ERROR: The path cannot be detected because the URL you typed above is on a different domain. Please correct the error or type the path manually.',
    'WP_PathTest_Invalid_URL' => 'ERROR: The path cannot be detected because you have not entered a URI above, or it is invalid',
    'WP_PathTest_Not_Detected' => 'ERROR: The wizard cannot detect the path. Please type it in manually, and then click the button to test it.',
    'WP_PathTest_Success' => 'SUCCESS! A WordPress install was detected at %s',
    'WP_PathTest_GuessedOnly' => 'WARNING: The wizard suggested the path %s - however a WordPress install cannot be found at that location. You may continue, but the integration may not work. Please check that you have installed WordPress, or type the path manually.',
    'WP_PathTest_TestOnly_NotFound' => 'ERROR: A WordPress install cannot be found at that location. You may continue, but the integration may not work. Please check that you have installed WordPress, or type the path manually.',

    'WP_Wizard_Complete_Title' => 'Setup Wizard Complete!',
    'WP_Wizard_Complete_Explain0' => 'Congratulations, the setup is complete! For advanced integration options, such as caching and compression, please visit the options.php file in your wp-united folder.',
    'WP_Wizard_Complete_Explain1' => 'If you already have WordPress Permalinks turned on, you should visit the WordPress Permalink options page, and re-apply the settings.',
    'WP_Wizard_Complete_Explain2' =>'Doing this will ensure all WordPress links point to the correct location.',
    'WP_Wizard_Complete_Explain3' => 'Thank you for installing WP-United. If you enjoy the mod, please consider supporting us by making a donation here! Any amount, however small, is much appreciated!',


    //USER MAPPING STRINGS
    'L_MAP_TITLE'    =>    'WP-United User Integration Manager',
    'L_MAP_INTRO1'    =>   'This tool allows you to map WordPress users to specific phpBB users.',
    'L_MAP_INTRO2'   =>   'What This Tool Will Do:',
    'L_MAP_INTRO3'   =>   'The script will read through and list out each of your WordPress users. If they are not integrated, the tool will try to find a phpBB user with a matching username, on the assumption that you will probably want to integrate these users.',
    'L_MAP_INTRO4'    =>    'You will then be given the choice to integrate to this user, or type in the name of a different phpBB user. Alternatively, you can leave this WordPress user unintegrated. You also have the option to delete the user from WordPress, or create a new corresponding user in phpBB.',
    'L_MAP_INTRO5'    =>    'If the user is already mapped to a phpBB user, you will be given the option to break the integration, or leave it alone.',
    'L_MAP_INTRO6'    =>    'NOTE: Before running this tool, you MUST back up your WordPress database (and your phpBB database).',
    'L_MAP_INTRO7'    =>    'Click &quot;Begin&quot; to get started.',
    'L_COL_WP_DETAILS'   =>   'WordPress Details',
    'L_COL_MATCHED_DETAILS'   =>   'Matched/suggested phpBB Details',
    'L_USERID'   =>   'User ID',
    'L_USERNAME'   =>   'Username',
    'L_NICENAME'   =>   '\'Nicename\'',
    'L_NUMPOSTS'   =>   'No. of Posts',
    'L_MAP_STATUS'   =>   'Status',
    'L_MAP_ACTION'   =>   'Action',

    'L_MAPMAIN_1'      =>   'On the left, your WordPress users are listed. On the right, the status of each user (integrated or not integrated) is shown. If the user is already integrated, the phpBB user will be shown in the middle. If the user is not integrated, but a suggested match is found, the match will be shown. If it is not right, you can type in a different username. If no match is found, you can leave them unintegrated, or create a user in phpBB (the default).',
    'L_MAPMAIN_2'      =>   'On the far right, select an appropriate action for each user (sensible defaults have been chosen for you). Then, click \'Process\'. You will have the chance to confirm each action in the next step.',
    'L_MAPMAIN_MULTI' =>    'Or, you can click \'Skip to Next Page\' to skip these users and go to the next page of users.',
    'L_MAP_BEGIN'    =>    'Begin',   
    'L_MAP_NEXTPAGE'    =>    'Next Page',       
    'L_MAP_SKIPNEXT'    =>    'Skip to Next Page',     
        
        
        
    'L_MAP_ERROR_MULTIACCTS' =>   ' ERROR: Integrated to more than one account!',
    'L_MAP_BRK' => 'Romper Integracion',
    'L_MAP_BRK_MULTI' => 'Romper Integraciones',
    'L_MAP_NOT_INTEGRATED' => 'No Integrado',
    'L_MAP_INTEGRATE' => 'Integrar',
    'L_MAP_ALREADYINT' => 'Ya está Integrado',
    'L_MAP_LEAVE_INT' => 'Dejar integrado',
    'L_MAP_CREATEP' => 'Crear User en phpBB',
    'L_MAP_CANTCONNECTP' => 'No se pudo conectar a la base de datos de phpBB',
    'L_MAP_LEAVE_UNINT' => 'Dejar sin integrar',
    'L_MAP_UNINT_FOUND' => 'No integrado (suggested match found)',
    'L_MAP_UNINT_FOUNDBUT' => 'No integrado (match \'%1s\' found, but phpBB user %2s is already integrated to WordPress account ID %3s)',
    'L_MAP_UNINT_NOTFOUND' => 'No integrado (no suggested match found)',
    'L_MAP_ERROR_BLANK' => 'Error: entrada en blanco',
    'L_MAP_DEL_FROM_WP' => 'Borrar de  WP',
    'L_MAP_PROCESS' => 'Procesar',
    'L_MAP_NOUSERS' => 'No fueron encontrados usuarios de WP relevantes &ndash; así que no es necesario usar esta utilidad!',
    
    'L_MAP_CANT_CONNECT' => 'ERROR: No se pudo conectar a WordPress!',
    'L_WP_NO_SETTINGS' => 'No se pudo conectar a los ajustes de WP-United',
    'L_COL_WP_DETAILS' => 'WordPress Detalles',
    'L_COL_MATCHED_DETAILS' => 'Detalles de phpBB sugeridos ',
    'L_USERID' => 'ID del usuario',
    'L_USERNAME' => 'Nombre de usuario',
    'L_NICENAME' => '\'Nick\'',
    'L_NUMPOSTS' => 'Num. de Posts',
    'L_USERNAME' => 'nombre de usuario',
    'L_USERID' => 'User ID',
    'L_MAP_STATUS' => 'Status',
    'L_MAP_ACTION' => 'Action',
    
    
    'L_MAP_ACTIONSINTRO' => 'Your selections have been processed into the following actions:',
    'L_MAP_ACTIONSEXPLAIN1' => 'If any of the above appears to be incorrect, please click your browser \'back\' button and correct the selections. If you are satisfied, press \'Process Actions\' to perform the actions.',
    'L_MAP_NOWTTODO' => 'There are no actions to process. Click your browser \'back\' button. Then, select some actions, or skip to the next page.',
    'L_MAP_ERR_GOBACK' => 'Not all of the actions could be processed. Please click your browser \'back\' button and correct the errors.',
    
    'L_MAP_BREAKWITH' => 'Break integration with phpBB user %s',
    'L_MAP_INTWITH' => 'Integrate with phpBB user %s',
    'L_MAP_BREAKEXISTING' => 'Break existing integration',
    'L_MAP_BREAKMULTI' => 'Break existing integrations',
    'L_MAP_DEL_WP' => 'Delete user from WordPress',
    'L_MAP_CREATE_P' => 'Create user in phpBB',
    'L_MAP_PNOTEXIST' => 'ERROR: This phpBB user does not exist!',
    'L_MAP_ERR_ALREADYINT' => 'ERROR: This phpBB user is already integrated!',
    
    'L_PROCESS_ACTIONS' => 'Process Actions',
    
    'L_MAP_PERFORM_INTRO' => 'The following actions were taken:',
    
    'L_MAP_COULDNT_BREAK' => 'Could not break integration',
    'L_DB_ERROR' => 'Database error',
    'L_MAP_BROKE_SUCCESS' => 'Successfully broke integration for WordPress user ID %s.',
    'L_MAP_CANNOT_BREAK' => 'Error: Cannot break integration as WordPress user not specified!',
    'L_MAP_COULDNT_INT' => 'Could not integrate',
    'L_MAP_INT_SUCCESS' => 'Integrated WordPress user %1s <-> phpBB user %2s',
    'L_MAP_CANNOT_INT' => 'Error: No se pudo integrar usuarios,ID perdida!',
    'L_MAP_WPDEL_SUCCESS' => 'Borrar usuario de Wordpress%s',
    'L_MAP_CANNOT_DEL' => 'ERROR: No se ha podido borrar al usuario de Wordpress,la ID no existe',
    'L_MAP_CANNOT_CREATEP_ID' => 'ERROR: No se puede crear el usuario de phpBB ya que el nombre de usuario o la ID de Wordpress no ha sido dada.',
    'L_MAP_CREATEP_SUCCESS' => 'Creado usuario de phpBB. (NOTE: USUARIO NO INTEGRADO TODAVÍA EN WORDPRESS, USA ESTA UTILIDAD PARA INTEGRARLO.)',
    'L_MAP_CANNOT_CREATEP_NAME' => 'ERROR: Cannot create phpBB user (username could be invalid, or username/e-mail could already exist!)',
    'L_MAP_INVALID_ACTION' => 'ERROR &ndash; invalid action #%s',
    'L_MAP_INVALID_ACTION' => 'ERROR &ndash; empty action #%s',
    'L_MAP_FINISHED' => 'La utilidad deintegración de usuarios ha terminado. Click %1saquí%2s para volver al panel de control de WP-United, o click %3saquí%4s para usar la utilidad de nuevo o inspeccionar los cambios.',
    
    'WP_Reset' => 'Reset WP-United',
    'WP_Reset_Button' => 'Reset',
    'WP_Reset_Explain' => 'Resetting WP-United sets the WP-United Admin Control Panel modules back to their original state &ndash; useful if you have moved them around and want them back. It also sets all the WP-United settings back to their default states, and hides all links to WordPress. WP-United willshow as \'uninstalled\' until you run the Setup Wizard again. WordPress settings, user mappings, and WP-United permissions will remain intact and will NOT be altered.<br /><br /> Most people will NOT need to use this &ndash; only do so if you are sure you want to lose all WP-United settings!',
    'WP_Did_Reset' => 'Reset Successful!',
    'WP_Reset_Confirm' => 'Are you sure you want to reset WP-United?',
    'WP_Reset_Log' => 'Reset WP-United settings to initial state',

    'WP_Uninstall' => 'Desinstalar WP-United',
    'WP_Uninstall_Button' => 'Desinstalar',
    'WP_Uninstall_Explain' => 'Desinstalar WP-United borra CUALQUIER aspecto del mod, desde Wordpress a phpBB, además de las ediciones de los archivos que hiciste para instalar el mod (no necesitas borrar esto). Todos los datos de integración de usuario se perderán -- si los usuarios de phpBB tienen cuentas en Wordpress, estas cuentas continuarán existiendo, pero noe starán integrados en phpBB, y solo podrán ser re-integrados si se re-instala WP-United y se re-integran manualmente con la utilidad de integraciónan.<br /><br /> El desinstalador tratará de contactar con Wordpress, y borrará los ajustes que hizo WP-United, incluyendo todas las acciones personales de los usuarios y los ajustes de sus blogs. <br /><br />La mayoría de la gente no necesita usar esto &ndash; solo hazlo si estás seguro de querer borrar todos los ajustes de WP-UNITED! Deberías hacer un backup de tu base de datos antes de continuar!',
    'WP_Uninstall_Confirm' => 'Estás seguro de querer desinstalar WP-United?',      
    'WP_Uninstall_Log' => 'WP-United fue completamente eliminado',
    
    //new in v0.5.5 -- please translate :-)
	'L_INFO_TO_POST' => 'Info to Post',
	'WP_Debug' => 'Debugging Information',
	'WP_Debug_Explain' => 'If you are having problems with WP-United, and need to ask for help on the wp-united.com forums, please post the debugging information below to help assist with your enquiry. <br /><br />Please also post the content of any error or additional debug information. If you are experiencing problems with usere mapping, turn on debugging in your wp-united/options.php file.<br /><br />NOTE: You may want to obfuscate path information.',
	'DEBUG_SETTINGS_SECTION' => 'WP-United Settings:',
	'DEBUG_PHPBB_SECTION' => 'phpBB Settings:',
	
	//new in v0.6
	'WP_XPost_Title' => 'Allow cross-posting of blog posts to forum?',
	'WP_XPost_Explain' => 'If you enable this option, users will be able to elect to have their blog entry copied to a forum when writing a blog post. To set which forums the user can cross-post to, visit the phpBB forum permissions panel, and enable the cross-posting permission for the users/groups you wish.',
	'WP_XPost_OptTitle' => 'You can set the following option if you integrate logins',	
));

?>

