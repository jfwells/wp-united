<?php
/** 
*
* WP-United [English]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.0 2009/05/18 John Wells (Jhong) Exp $
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
	'ACP_WPU_INDEX_TITLE'	=> 'WP-United',
	    
	'WPU_Default_Version' => 'v0.7.0-RC1',
	'WP_Version_Text' => 'WP-United %s',

	'WP_Title' => 'WP-United Settings',
	'WP_Intro1' => 'Welcome to WP-United Release Candidate.',
	'WP_Intro2' => 'The settings below need to be set correctly in order for the Mod to work properly.',
	'WP_Settings' => 'Basic Settings',
	'WP_UriTitle' => 'Base WordPress URL',
	'WP_UriExplain' => 'This is the full URL to the base install of WordPress. This is the URL that WordPress would have if it were not integrated into phpBB. For example, http://www.example.com/wordpress/',
	'WP_UriProb' => 'I couldn\'t find the WordPress install here. Please make sure this field contains the full external URL to your WordPress install.',
	'WP_PathTitle' => 'Filesystem Path to WordPress',
	'WP_PathExplain' => 'This is the full filesystem path to WordPress. If you leave this blank, we will try to automatically detect it. If automatic detection doesn\'t work, you may have to type in the full filesystem path to your wordpress install. This could be something like /home/public/www/wordpress -- but every host will be different.',
	'WP_PathProb' => 'I couldn\'t find the WordPress install here, or automatically detect the correct path. Please type the correct filesystem path in here.',
	'WP_Submit' => 'Submit',
	'WP_Login_Title' => 'Let phpBB automatically handle WordPress Logins',
	'WP_Login_Explain' => 'If you turn this option on, phpBB will create a WordPress account the first time each phpBB user visits the integrated page. If this WordPress install will be non-interactive (e.g., a blog by a single person, a portal page, or an information library with commenting disabled), you may want to turn this option off, as readers may not need accounts. You can also map existing WordPress users to phpBB users, using the mapping tool that will appear after you turn on this option.<br /> You can set the privileges for each user using the WP-United permissions under the phpBB3 permissions system.',
	'WP_Yes' => 'Yes',
	'WP_No' => 'No',
	'WP_Footer' => 'WP-United is freely developed by John Wells, www.wp-united.com . If you find this useful, please donate!',

	'WPU_Must_Upgrade_Title' => 'Update Detected',
	'WPU_Must_Upgrade_Explain1' => 'You have updated WP-United. You must run the %sUpgrade Tool%s before continuing.<br /><br />Then you can come back here and run the Setup Wizard.',
	'WPU_Must_Upgrade_Explain2' => '[Note: If you have never turned on user integration, you can %sclick here%s to ignore this prompt.]',
	'WPU_Not_Installed' => '(Not Installed)',


	'WPU_URL_Not_Provided' => 'ERROR: No WordPress URL was provided! The filepath can often be automatically detected, but you MUST provide the URL to the WordPress install. Please try again.',
	'WPU_URL_Diff_Host' => 'WARNING: The Base WordPress URL you entered appears to be on a different domain name than phpBB. Your WordPress install should be accessed through the same host as your phpBB install. Automatic file path detection will not be able to take place.',
	'WPU_OK' => 'OK',
	'WPU_URL_No_Exist' => 'WARNING: The Base WordPress URL you entered does not appear to exist (it returned a 404 error)! Please check that you have typed it correctly, and that you have installed WordPress.',
	'WPU_Cant_Autodet' => 'ERROR: The path cannot be autodetected as the URL you provided appears to be on a different host. Please correct the URL or provide the file path to WordPress.<br />',
	'WPU_Path_Autodet' => 'Trying to automatically detect the file path... ',
	'WPU_Autodet_Error' => 'ERROR: The path to your WordPress install could not be auto-detected. Please type it manually.<br />',
	'WPU_Pathfind_Warning' => 'WARNING: A working WordPress install could not be found (Searching for: %s). Either the path has been typed or auto-detected incorrectly, or WordPress has not been installed. Please check the provided file path and try again.',
	
	'WPU_PathIs' => "File path => ",
	'WPU_Checking_URL' => 'Checking WordPress URL: ',
	'WPU_Process_Settings' => 'Processing settings...',
	'WPU_Checking_URL' => 'Checking that the URL exists... ',
	'WPU_Checking_WPExists' => 'Checking that a WordPress installation exists here... ',
	'WPU_Conn_Installing' => 'Installing WP-United Connection... ',


	'WP_AllOK' => 'All OK. Your settings have been saved.',
	'WP_Saved_Caution' => 'Your settings have been saved, but they will probably not work until you go back and correct the above errors.',
	'WP_Errors_NoSave' => 'Errors were encountered and your settings could not be saved. Please go back and correct the problems above.',
	'WP_DBErr_Retrieve' => 'Could not access WordPress integration table in the database. Please ensure that you ran the SQL query included when installing the mod.',
	'WP_DBErr_Write' => 'Could not insert new values into the database. Please ensure that you ran the SQL query included when installing the mod.',
	'WP_Config_GoBack' => 'Click %sHere%s to go back to the WP-United Main Admin Page.',
	'WP_Perm_Title' => 'PhpBB &lt;-&gt; WordPress Permissions Mapping (requires JavaScript)',

	'WP_Perm_Explain1' => 'If phpBB is handling WordPress logins, you can map the WordPress User Roles to phpBB permissions and/or groups here. Be careful not to grant regular posters permissions that are too high!',
	'WP_Perm_Explain2' => 'On the left are phpBB permission-based groups (such as Regular users, Moderators and Administrators) and phpBB user-defined groups. The boxes on the right represent WordPress permissions.',
	'WP_Perm_Explain3' => 'Click on a phpBB group on the left, and then select a box on the right corresponding to the WordPress permissions you want to grant to every member of that group. Then click the right-hand arrow to assign it. groups left in the left-hand box will not be integrated. For a proper overview of WordPress 2 permissions, check out the <a href=>"http://codex.wordpress.org/Roles_and_Capabilities">WordPress Codex</a>.',
	'WP_Perm_Explain4' => 'CAUTION -- PLEASE ENSURE YOU UNDERSTAND THIS: When permissions conflict, higer permissions override lower permissions. For example, if a member belongs to more than one group, he will get the highest role available from the two groups. Similarly, if a user is a Moderator,  he will get the role assigned above for \'Moderator\' if that role is higher than the role assigned to a group of which he is also a member.',

	'WP_Login_Head' => 'Login and Permissions Integration Settings',

	'WP_User_Title' => 'Registered PhpBB Users',

	'WP_User_Explain' => 'Set the user role that normal PhpBB users should have in WordPress.',
	'WP_Mod_Title' => 'PhpBB Moderators',

	'WP_Mod_Explain' => 'Set the user role that PhpBB moderators should have in WordPress.',

	'WP_Admin_Title' => 'PhpBB Administrators',

	'WP_Admin_Explain' => 'Set the user role that normal PhpBB admins should have in WordPress. NOTE: The default WordPress user named \'admin\', will not be integrated and will retain full admin rights.',

	'WP_Advanced_Head' => 'Display Settings',
	'WP_DTD_Title' => 'Use Different Document Type Declaration?',
	'WP_DTD_Explain' => 'The Document Type Declaration, or DTD, is provided at the top of all web pages to let the browser know what type of markup language is being used.<br /><br />phpBB3\'s prosilver uses an XHTML 1.0 Strict DTD by default. Most WordPress templates, however, use an XHTML 1 transitional DTD.<br /><br />In most cases, this doesn\'t matter -- however, If you want to use WordPress\' DTD on pages where WordPress is inside phpBB, then you can turn this option on. This should prevent browsers from going into &quot;quirks mode&quot;, and will ensure that even more WordPress templates display as designed.',
	'WP_Role_None' => 'none',
	'WP_Role_Subscriber' => 'Subscriber',
	'WP_Role_Contributor' => 'Contributor',
	'WP_Role_Author' => 'Author',
	'WP_Role_Editor' => 'Editor',
	'WP_Role_Administrator' => 'Administrator',

	//Main Page
	'WP_Main_Title' => 'Welcome to WP-United BETA',
	'WP_Main_Intro' => 'From here you can set up WP-United, and choose the settings you need to make it work the way you want.',
	'WP_Main_IntroFirst' => 'You have not yet set up the mod. We recommend you run the Setup Wizard, below.',
	'WP_Main_IntroAdd' => 'You have already set up the mod. You can fine-tune the options by clicking on the "Settings" button, below.',
	'WP_Recommended' => 'Recommended',
	'WP_Wizard_Title' => 'Setup Wizard',
	'WP_Detailed_Title' => 'Settings',
	'WP_Wizard_Explain' => 'This wizard will guide you through the initial set-up of WP-United again. Note that you must ruin it through to the last page.',
	'WP_Wizard_ExplainFirst' => 'This wizard will guide you through the settings for WP-United. As you have not yet set up the Mod, you should run this first, all the way through.',
	'WP_Detailed_ExplainFirst' => 'Here you can review all the settings associated with WP-United. As you have not yet set up the Mod, you should run the wizard, above, first, unless advised otherwise.',
	'WP_Detailed' => 'Here you can review all the settings associated with the WP-United.',
	'WP_Detailed_Explain' => 'This contains all the settings on a single page.',
	'WP_SubmitWiz' => 'Setup Wizard',
	'WP_SubmitDet' => 'Settings',
	'WP_MapLink_Title' => 'User Integration Mapping Tool',
	'WP_MapLink' => 'Go',
	'WP_MapLink_Explain' => 'User integration is turned on. To review the status of WordPress users, click the button to access the User Integration Mapping Tool',

	'WP_Support' => 'Help Support WP-United',
	'WP_Support_Explain' => 'WP-United is free software, and we hope you find it useful. If you do, please support us by making a donation here! Any amount, however small, is much appreciated. Thank you!<br /><br />The PayPal link will take you to a donation page for our PayPal business account, \'Pet Pirates\'',



	//Wizard
	'WP_Wizard_H1' => 'WP-United Setup Wizard',
	'WP_Wizard_Step' => 'Step %s of %s.',
	'WP_Wizard_Next' => 'Continue to step %s -&gt;',
	'WP_Wizard_Back' => '&lt;- Back to step %s',
	'WP_Wizard_BackStart' => '&lt;- Back to start',
	'wizErr_invalid_URL' => "The URL to WordPress you typed appears to be invalid. (It should be a full URL, not relative). ",
	'wizErr_invalid_Blog_URL' => "The URL to the integration you typed appears to be invalid. (It should be a full URL, not relative). ",
	'wizErr_invalid_Path' =>  "The path you typed appears to be invalid. ",

	//Wizard Step 1
	'WP_Wizard_Step1_Title' => 'WordPress Installation',
	'WP_Wizard_Step1_Explain1' => 'Please ensure you have installed WordPress.',
	'WP_Wizard_Step1_Explain2' => 'You can install it anywhere on the same web space as your phpBB installation. It does not have to be inside your phpBB directory, but could be if you wanted it to.',
	'WP_Wizard_Step1_Explain2b' => 'The provided file, blog.php, will be used to access your integrated WordPress pages. You may rename this file and put it wherever you like (for example, you might rename it to index.php and put it in your site root). If you rename/move it, you must open the file and ener the path to phpBB where indicated.',
	'WP_Wizard_Step1_Explain3' => 'WordPress requires a MySQL database. If you need to, you could use your existing phpBB database. If you do, you must ensure that the WordPress tables use a different prefix than your phpBB tables.',
	'WP_Wizard_Step1_Explain4' => 'If you are planning to integrate a pre-existing WordPress installation, please review the usernames of existing WordPress users. Once WP-United is set up, you can use the provided mapping tool to bring over existing WordPress users to phpBB, if needed.',
	'WP_Wizard_Step1_Explain5' => 'Once you have installed WordPress, Please click "Next" to continue. If you want to close this wizard, you can restart it from the same location later.',


	//Step Wizard 1B
	'WP_Wizard_Step1b_Title' => 'WordPress Installation Location',
	'WP_Wizard_Step1b_Explain1' => 'Here we need a few details so we can find where you installed WordPress',

	'WP_Wizard_Step1b_TH1' => 'WordPress URL',
	'WP_Wizard_URI_Explain' => 'Please type the full URL to the base install of WordPress. This is the URL that WordPress would have if it were not integrated into phpBB. For example, http://www.example.com/wordpress/',
	'WP_Wizard_URI_Test_Title' => 'Test URL',
	'WP_Wizard_URI_Test_Explain' => 'Click the button on the right to test that the URL you have entered is accessible.',
	'WP_URI_Test' => 'Test URL',

	'WP_Wizard_Step1b_TH2' => 'WordPress Installation Path',
	'WP_Wizard_Path_Explain1' => 'Here we need the full filesystem path to WordPress. This could be something like /home/public/www/wordpress -- but every host will be different.',
	'WP_Wizard_Path_Explain2' => 'This wizard can try to automatically detect the path for you, so you don\'t have to type it. If the test fails, or if you prefer to type it yourself, you can enter it manually.',
	'WP_Path_Test' => 'Detect Path',
	'WP_Wizard_Path_Explain3' => ' or enter it manually here: ',

	'WP_Wizard_URI_Error' => "ERROR! You must enter a valid URL!",
	'WP_Wizard_Path_Error' => "ERROR! You must enter a valid path!",

	'WPWiz_BlogURI_TH' => 'New Integration Address',
	'WPWiz_BlogURI_Title' => 'The address you will use to access WordPress from now on',
	'WPWiz_BlogURI_Explain1' => 'By default, blog.php, in your phpBB root folder, will be used to access your WordPress pages from now on.',
	'WPWiz_BlogURI_Explain2' => 'However, if you want to change this, please move and/or rename blog.php to the name location of your choice. For example, you could rename it to index.php, and put it in your site root.',
	'WPWiz_BlogURI_Explain3' => 'You will then need to open the file and provide the path to your phpBB root folder where indicated.',
	'WPWiz_BlogURI_Explain4' => 'If you want to move it to your WordPress root folder as index.php, feel free to rename the existing index.php to index-old.php -- it is no longer needed.',
	'WPWiz_BlogURI_Explain5' => 'Please do this <strong>now</strong>, and then come back and provide the address where you want to access your WordPress pages.',


	'WP_No_JavaScript' => 'NOTICE: This page has some enhancements that you cannot see because your browser is old, or JavaScript is disabled.',
	'WP_AJAX_DataError' => 'ERROR: Could not understand the response issued by the server!',

	//Wizard Step 2
	'WP_Wizard_Step2_Title' => 'Integration Settings',
	'WP_Wizard_Step2_Explain' => 'In this step, we will set the way users log into WordPress',

	'WPWiz_IntLogin_title' => 'The following options must be set if you choose to integrate logins...',


	//Wizard Step 3
	'WP_Wizard_Step3_Title' => 'Display &amp; Behaviour Settings',
	'WP_Wizard_Display_Title' => 'Display Settings',
	'WP_Wizard_Behave_Title' => 'Behaviour Settings',
	'WP_Wizard_Step3_Explain' => 'We need to set the way WordPress and phpBB look and behave.',
	'WPWiz_Inside_Title' => 'Integrate phpBB &amp; WordPress Templates?',
	'WPWiz_Template_Forward' => 'WordPress inside phpBB',
	'WPWiz_Template_Reverse' => 'phpBB inside WordPress',
	'WPWiz_Template_None' => 'No template integration',
	'WPWiz_Inside_Explain1' => "WP-United can integrate your phpBB &amp; WordPress templates.",
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





	//Wizard Step 4
	'WP_Wizard_Step4_Title' => '&quot;To Blog or Not To Blog?&quot;',
	'WP_Wizard_Step4_Explain' => 'You have selected to integrate logins between phpBB and WordPress. If your intention is to allow your community members to create their own blogs, you can fine-tune the settings below.',
	'WPWiz_OwnBlogs_Title' => 'Give users their own blogs?',
	'WPWiz_OwnBlogs_Explain1' => 'If you turn this option on, each community member with an access level of "author" or above, can create their own blog. They will be able to choose the title, description, and (optionally), the appearance of their blog.',
	'WPWiz_OwnBlogs_Explain2' => 'With this option on, you will probably want to make some simple changes to your WordPress templates. A few example templates should have been provided for you in the &quot;/contrib&quot; folder when you downloaded this mod. If you want to make use of these, please copy them over to your WordPress &quot;wp-content/themes/&quot; folder now.',
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
	'L_MAP_TITLE' 	=> 	'WP-United User Integration Manager',
	'L_MAP_INTRO1' 	=>	'This tool allows you to map WordPress users to specific phpBB users.',
	'L_MAP_INTRO2'	=>	'What This Tool Will Do:',
	'L_MAP_INTRO3'	=>	'The script will read through and list out each of your WordPress users. If they are not integrated, the tool will try to find a phpBB user with a matching username, on the assumption that you will probably want to integrate these users.',
	'L_MAP_INTRO4' 	=> 	'You will then be given the choice to integrate to this user, or type in the name of a different phpBB user. Alternatively, you can leave this WordPress user unintegrated. You also have the option to delete the user from WordPress, or create a new corresponding user in phpBB.',
	'L_MAP_INTRO5' 	=> 	'If the user is already mapped to a phpBB user, you will be given the option to break the integration, or leave it alone.',
	'L_MAP_INTRO6' 	=> 	'NOTE: Before running this tool, you MUST back up your WordPress database (and your phpBB database).',
	'L_MAP_INTRO7' 	=> 	'Click &quot;Begin&quot; to get started.',
	'L_COL_WP_DETAILS'	=>	'WordPress Details',
	'L_COL_MATCHED_DETAILS'	=>	'Matched/suggested phpBB Details',
	'L_USERID'	=>	'User ID',
	'L_USERNAME'	=>	'Username',
	'L_NICENAME'	=>	'\'Nicename\'',
	'L_NUMPOSTS'	=>	'No. of Posts',
	'L_MAP_STATUS'	=>	'Status',
	'L_MAP_ACTION'	=>	'Action',

	'L_MAPMAIN_1'		=>	'On the left, your WordPress users are listed. On the right, the status of each user (integrated or not integrated) is shown. If the user is already integrated, the phpBB user will be shown in the middle. If the user is not integrated, but a suggested match is found, the match will be shown. If it is not right, you can type in a different username. If no match is found, you can leave them unintegrated, or create a user in phpBB (the default).',
	'L_MAPMAIN_2'		=>	'On the far right, select an appropriate action for each user (sensible defaults have been chosen for you). Then, click \'Process\'. You will have the chance to confirm each action in the next step.',
	'L_MAPMAIN_MULTI' => 	'Or, you can click \'Skip to Next Page\' to skip these users and go to the next page of users.',
	'L_MAP_BEGIN' 	=> 	'Begin',   
	'L_MAP_NEXTPAGE' 	=> 	'Next Page',       
	'L_MAP_SKIPNEXT' 	=> 	'Skip to Next Page',      
	    
	    
	    
	'L_MAP_ERROR_MULTIACCTS' =>   ' ERROR: Integrated to more than one account!',
	'L_MAP_BRK' => 'Break Integration',
 	'L_MAP_BRK_MULTI' => 'Break Integrations',
	'L_MAP_NOT_INTEGRATED' => 'Not Integrated',
	'L_MAP_INTEGRATE' => 'Integrate',
	'L_MAP_ALREADYINT' => 'Already Integrated',
	'L_MAP_LEAVE_INT' => 'Leave integrated',
	'L_MAP_CREATEP' => 'Create User in phpBB',
	'L_MAP_CANTCONNECTP' => 'Cannot connect to phpBB database',
	
    'L_MAP_LEAVE_UNINT' => 'Leave Unintegrated',
    'L_MAP_UNINT_FOUND' => 'Not integrated (suggested match found)',
    'L_MAP_UNINT_FOUNDBUT' => 'Not integrated (match \'%1s\' found, but phpBB user %2s is already integrated to WordPress account ID %3s)',
    'L_MAP_UNINT_NOTFOUND' => 'Not integrated (no suggested match found)', 
    'L_MAP_ERROR_BLANK' => 'Error: blank entry',
    'L_MAP_DEL_FROM_WP' => 'Delete from WP',
    'L_MAP_PROCESS' => 'Process',
    'L_MAP_NOUSERS' => 'No relevant WordPress users found &ndash; so there\'s no point in running this tool!',
    
    'L_MAP_CANT_CONNECT' => 'ERROR: Could not connect to WordPress!',
    'L_WP_NO_SETTINGS' => 'Could not connect to WP-United Settings',
    'L_COL_WP_DETAILS' => 'WordPress Details',
    'L_COL_MATCHED_DETAILS' => 'Matched/suggested phpBB Details',
    'L_USERID' => 'User ID',
	'L_USERNAME' => 'Username',
	'L_NICENAME' => '\'Nicename\'',
	'L_NUMPOSTS' => 'No. of Posts',
	'L_USERNAME' => 'username',
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
	'L_MAP_CANNOT_INT' => 'Error: Cannot integrate users, missing ID!',
	'L_MAP_WPDEL_SUCCESS' => 'Deleted WordPress User %s',
	'L_MAP_CANNOT_DEL' => 'ERROR: Cannot delete WordPress user, missing ID',
	'L_MAP_CANNOT_CREATEP_ID' => 'ERROR: Cannot create phpBB user as username or WordPress ID not given',
	'L_MAP_CREATEP_SUCCESS' => 'Created phpBB user %s. (NOTE: USER NOT YET INTEGRATED TO WORDPRESS COUNTERPART, RUN THIS TOOL AGAIN TO INTEGRATE THEM.)',
	'L_MAP_CANNOT_CREATEP_NAME' => 'ERROR: Cannot create phpBB user (username could be invalid, or username/e-mail could already exist!)',
	'L_MAP_INVALID_ACTION' => 'ERROR &ndash; invalid action #%s',
	'L_MAP_INVALID_ACTION' => 'ERROR &ndash; empty action #%s',
	'L_MAP_FINISHED' => 'The User Mapping Tool has finished. Click %1shere%2s to go back to the WP-United ACP main page, or click %3shere%4s to run the tool again or to inspect the changes.',
	
	'WP_Reset' => 'Reset WP-United',
	'WP_Reset_Button' => 'Reset',
	'WP_Reset_Explain' => 'Resetting WP-United sets the WP-United Admin Control Panel modules back to their original state &ndash; useful if you have moved them around and want them back. It also sets all the WP-United settings back to their default states, and hides all links to WordPress. WP-United willshow as \'uninstalled\' until you run the Setup Wizard again. WordPress settings, user mappings, and WP-United permissions will remain intact and will NOT be altered.<br /><br /> Most people will NOT need to use this &ndash; only do so if you are sure you want to lose all WP-United settings!',
	'WP_Did_Reset' => 'Reset Successful!',
	'WP_Reset_Confirm' => 'Are you sure you want to reset WP-United?',
	'WP_Reset_Log' => 'Reset WP-United settings to initial state',

	'WP_Uninstall' => 'Uninstall WP-United',
	'WP_Uninstall_Button' => 'Uninstall',
	'WP_Uninstall_Explain' => 'Uninstalling WP-United removes ALL aspect of the mod, from WordPress and phpBB, apart from the file edits and copies that were performed when you installed it (you do not need to remove these). All user mapping data will be lost -- if your phpBB users have accounts in WordPress, those accounts will continue to exist, but they will not be mapped to phpBB, and can only be re-mapped if you re-install WP-United and re-map manually with the user mapping tool.<br /><br /> The uninstaller will try to contact WordPress, and remove any WP-United settings, including all WP-United per-user options such as blog settings. <br /><br />Most people will NOT need to use this &ndash; only do so if you are sure you want to lose ALL WP-United settings! You should back up your database before continuing!',
	'WP_Uninstall_Confirm' => 'Are you sure you want to uninstall WP-United?',		
	'WP_Uninstall_Log' => 'Completely removed WP-United',
	
	//new in v0.5.5
	'L_INFO_TO_POST' => 'Info to Post',
	'WP_Debug' => 'Debugging Information',
	'WP_Debug_Explain' => 'If you are having problems with WP-United, and need to ask for help on the wp-united.com forums, please post the debugging information below to help assist with your enquiry. <br /><br />Please also post the content of any error or additional debug information. If you are experiencing problems with usere mapping, turn on debugging in your wp-united/options.php file.<br /><br />NOTE: You may want to obfuscate path information.',
	'DEBUG_SETTINGS_SECTION' => 'WP-United Settings:',
	'DEBUG_PHPBB_SECTION' => 'phpBB Settings:',
	
	//new in v0.6
	'WP_XPost_Title' => 'Allow cross-posting of blog posts to forum?',
	'WP_XPost_Explain' => 'If you enable this option, users will be able to elect to have their blog entry copied to a forum when writing a blog post. To set which forums the user can cross-post to, visit the phpBB forum permissions panel, and enable the cross-posting permission for the users/groups you wish.',
	'WP_XPost_OptTitle' => 'You can set the following option if you integrate logins',
	
	//New in v0.7.0 
	'WPWiz_Fix_Header_Title' => 'Remove phpBB header?',
	'WPWiz_Fix_Header_Explain1' => 'If you turn this option on, the phpBB header will be removed from the integrated page. It will work with the Prosilver & subSilver2 themes and most derivatives. If you use a very customised template, or want to edit the templates yourself, you can leave this option off. Otherwise, leave it on for a quick-and-easy no-hassle integration of phpBB into WordPress.',
	'WPWiz_Fix_Header_Explain2' => 'WP-United will try to automatically position the phpBB Quick Search box in the WordPress header. If it does not appear, or you want to put it somewhere else, add the tag &lt;!--PHPBB_SEARCH--&gt; to your WordPress template, and it will automatically appear there.',
	'WPWiz_Fix' => 'Remove',
	'WPWiz_No_Fix' => 'Don\'t alter',
	'WPWiz_Page_Explain2' => 'Just type in the name of the template (e.g. \'index.php\', \'single.php\' or \'archive.php\') here. If the file can\'t be found WP-United will default to using page.php (for the default WordPress theme) or index.php (for Classic-style themes).',
	'WPWiz_WPSimple_Explain3' => 'However, if you want the WordPress sidebar to show up, or use other WordPress features on the integrated page, you could try \'full page\'. This option could be a little slow, and will require a few modifications to phpBB styles to work. It works best with full-page WordPress themes, such as the Classic theme.',	
	'WPWiz_phpBB_Smilies_Title' => 'Use phpBB smilies in Wordpress?',
	'WPWiz_phpBB_Smilies_Explain' => 'Turn this option on if you want to use phpBB smilies in WordPress comments and posts.',	
	
	'WP_Wizard_Connection_Fail' => 'Error installing WP-United Connection!',
	'WPWizard_Connection_Fail_Explain1' => 'The WP-United Connection could not be installed. This is probably due to one of the following being set incorrectly: (a) invalid path to WordPress, (b) invalid script path set for phpBB in board config. Please correct these and try again.',
	'WPWizard_Connection_Fail_Explain2' => 'The file [phpbb]/wp-united/wpu-plugin.php could not be copied to your WordPress plugins folder. Please copy it there now, or make your WordPress plugins folder writeable. After copying, you should have a copy of wpu-plugin.php in [phpbb]/wp-united and in your WordPress plugins folder. When done, refresh this page or run the Wizard again.',
	'WPU_Conn_InstallError' => 'ERROR: The WP-United Plugin was not found in your wordpress plugins folder. Please copy the file wp-united/wpu-plugin.php there now, and then try again!<br />',
	'WPU_Cache_Unwritable' => 'WARNING: The folder [phpbb]/wp-united/cache is unwritable. For best performance, you should make this folder writable before proceeding',
	'WPU_Install_Exists' => 'WARNING: The file wpu-install.php exists in your phpBB root folder. After running this file, you MUST delete it in order to continue.',
	'Map_Items_PerPage' => 'Items to show per page',
	'Map_Change_PerPage' => 'Change',
	'Map_Quick_Actions' => 'Quick select',
	'Map_Delete_All_Unintegrated' => 'Delete all unintegrated',
	'Map_Break_All' => 'Break all integrations',
	'Map_Reset_Default' => 'Reset selections',
	'DEBUG_SERVER_SETTINGS' => 'Server settings:',
	
	//New in v0.7.1
	'WPWiz_Autolink_Sectitle' => 'You can set the following option if you allow cross-posting',
	'WPWiz_XPost_Autolink_Title' => 'Autolink comments for cross-posted posts',
	'WPWiz_XPost_Autolink_Explain' => 'Choose this option to automatically disable WordPress comments for cross-posted posts, and display a link to the post on the forum instead.',	
	

));

?>
