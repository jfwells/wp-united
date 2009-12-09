<?php
/** 
*
* WP-United ACP panels
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.7.1[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
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
* @package acp
*/
class acp_wp_united {
	var $u_action;
	var $new_config;
	function main($id, $mode) {
		global $db, $user, $auth, $template, $wizShowError, $wizErrorMsg, $inWizard, $numWizardSteps, $showFooter;
		global $wpuAbs, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		$user->add_lang('mods/admin_wp-united');
		require_once($phpbb_root_path . 'wp-united/mod-settings.php');
		require_once($phpbb_root_path . 'wp-united/abstractify.' . $phpEx);
		require_once($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
		
		$wizShowError = FALSE;
		$wizErrorMsg = '';
		
		//bypass any upgrade prompt on the main page
		$ignorePrompt = FALSE;
		if ( isset($_GET['ignore']) ) {
		$ignorePrompt = TRUE;
		}
		
		$inWizard = FALSE;
		$numWizardSteps = 6;
		$showFooter = TRUE;
		
		//
		//	MAIN PROGRAM LOGIC -- FIGURE OUT WHAT WAS CLICKED, AND SHOW APPROPRIATE ACP PAGE
		//	----------------------------------------------------------------------------------------------------------------------------------------
		//
		
		if ( isset($_POST['Submit']) ) {

			$submit = request_var('Submit', ''); 
			// Data has been submitted from the main page, or the save settings page.
			switch ( $submit ) {
			
			case $wpuAbs->lang('WP_SubmitWiz'):
			// The "Launch Setup Wizard" button was clicked.
			$inWizard = TRUE;
			$this->step1_show();
				break;
				
			case $wpuAbs->lang('WP_Submit'):
			// Process data from "configuration-on-a-page".
				$this->settings_process(); 
				break;
				
			case $wpuAbs->lang('WP_SubmitDet'):
				// Launch the "configuration-on-a-page".
				$this->settings_show();
				break;
				
			default:
				$this->mainPage_show();
				break;
			}
		} elseif ( isset($_POST['Next']) ) { 			
			$next = request_var('Next', '');
			// We are in the wizard -- step forward to specified page.
			$toPage = $this->get_page_number($next);
			$inWizard = TRUE; 
			switch ( $toPage ) { 
			case "1": 
				$this->step1b_show();
				break;
			case "2":
				$this->step1b_process();
				break;
			case "3":
				$this->step2_process();
				break;
			case "4":
				$this->step3_process();
				break;
			case "5":
				$this->step4_process();
				break;
			case "6":
				$this->step6_show();
				break;
			default:
				$this->mainPage_show();
				break;
			}
		} elseif ( isset($_POST['Back']) ) {
			$back = request_var('Back', '');
			// We are in the wizard -- step backward to specified page
			$toPage = $this->get_page_number($back);
			$inWizard = TRUE;
			switch ( $toPage ) { 
			case "1":
				$this->step1b_show();
				break;
			case "2":
				$this->step2_show();
				break;
			case "3":
				$this->step3_show();
				break;
			case "4":
				$this->step4_show('BACK');
				break;
			case "5":
				$this->step5_show_frontend();
				break;
			default:
				$this->step1_show();
				break;
			}
		} elseif ( isset($_POST['action']) ) {
			$action = request_var('action', '');
			// Specific processing functions called from the Wizard.
			switch ( $action ) {
			case "uriAjax":
				// Test supplied URI and return data AJAX-style
				$this->testUri(TRUE);
				break;
			case "pathAjax": 
				// Figure out the path to WordPress and return AJAX-style
				$this->findPath(TRUE);
				break;
			case "connAjax":
				// Enable asynchronous enabling of WP-United Connection
				$this->step5_backend();
				break;
			case $wpuAbs->lang('WP_URI_Test'):
				// Test supplied URI and return data normally, since AJAX is unavailable
				$returnMessageUri = $this->testUri(FALSE);
				$this->step1b_show($returnMessageUri);
				break;
			case $wpuAbs->lang('WP_Path_Test'): 
				// Figure out the path to WordPress and return normally, since AJAX is unavailable
				$returnMessagePath = $this->findPath(FALSE);
				$this->step1b_show('', $returnMessagePath);
				break;			
			default;
				$this->mainPage_show();
				break;
			}
		} elseif ( isset($_POST['mapaction']) ) {
			$usermap = request_var('mapaction', '');
			switch ( $usermap ) {
			case $wpuAbs->lang('L_MAP_BEGIN'):
			case $wpuAbs->lang('L_MAP_NEXTPAGE'):      
			case $wpuAbs->lang('L_MAP_SKIPNEXT'):
			case $wpuAbs->lang('Map_Change_PerPage'):
				$this->usermap_main();
				break;
			case $wpuAbs->lang('L_MAP_PROCESS'):
				$this->usermap_process();
				break;
			case $wpuAbs->lang('L_PROCESS_ACTIONS'):
				$this->usermap_perform();
				break;
			default;
				$this->usermap_intro();
				break;					
			}
		} elseif ( !empty($mode) ) {
			//User clicked a phpBB3 module mode link
			switch($mode) {
			case 'detailed':
				$this->settings_show();
				break;
			case 'wizard':
				$this->step1_show();
				break;	
			case 'usermap':
				$this->usermap_intro();
				break;
			case 'permissions':
				$this->permissions_show();
				break;				
			case 'uninstall':
				$this->uninstall_show();
				break;				
			case 'donate':
				$this->donate_show();
				break;			
			case 'reset':
				$this->reset_show();
				break;		
			case 'debug':
				$this->debug_show();
				break;						
			case 'index':
			default;
				$this->mainPage_show();
				break;
			}
		} else {
			//fall back to just showing the main page
			$this->mainPage_show();
		}
	}
	
	function mainPage_show() {
		global $wpuAbs, $phpEx, $ignorePrompt, $phpbb_root_path;
		$this->page_title = 'ACP_WPU_INDEX_TITLE';
		$this->tpl_name = 'acp_wp_united';
		
		//get integration package settings
		$wpSettings = $this->get_settings_set_defaults();

		//The captions and order of the buttons on the page changes depending on if the package has been installed prior
		if ( $wpSettings['installLevel'] < 10 ) { //not yet installed. Recommend wizard.
			$introAdd = $wpuAbs->lang('WP_Main_IntroFirst');
			$button1Title = $wpuAbs->lang('WP_Wizard_Title');
			$button1Explain = $wpuAbs->lang('WP_Wizard_ExplainFirst');
			$button1Value = $wpuAbs->lang('WP_SubmitWiz');
			$button2Title = $wpuAbs->lang('WP_Detailed_Title');
			$button2Explain = $wpuAbs->lang('WP_Detailed_ExplainFirst');
			$button2Value = $wpuAbs->lang('WP_SubmitDet');
			$recommended = $wpuAbs->lang('WP_Recommended');
			$mode1 = 'wizard';
			$mode2 = 'detailed';
		} else { // installed. Show settings page first.
			$introAdd = $wpuAbs->lang('WP_Main_IntroAdd');
			$button1Title = $wpuAbs->lang('WP_Detailed_Title');
			$button1Explain = $wpuAbs->lang('WP_Detailed_Explain');
			$button1Value = $wpuAbs->lang('WP_SubmitDet');
			$button2Title = $wpuAbs->lang('WP_Wizard_Title');
			$button2Explain = $wpuAbs->lang('WP_Wizard_Explain');
			$button2Value = $wpuAbs->lang('WP_SubmitWiz');
			$mode1 = 'detailed';
			$mode2 = 'wizard';
			$recommended = "";
		}
		
		// set the page section to show
		$passBlockVars = array(
		'switch_main_page' => array(),
		);
		
		//If this is an upgrade, and that upgrade requires an install script, throw up a message
		if ($wpSettings['wpuVersion'] != $wpuAbs->lang('WPU_Default_Version')) {
			//clear the phpBB cache
			global $cache;
			$cache->purge();
				
			$ver = $wpuAbs->lang('WPU_Not_Installed');
			if ( ($wpSettings['upgradeRun'] == 1) && ($wpSettings['installLevel'] >= 5) && (!$ignorePrompt) ) {
				$passBlockVars['switch_main_page.switch_upgrade_warning'] = array(
				'L_WP_UPGRADE_TITLE' => $wpuAbs->lang('WPU_Must_Upgrade_Title'),
				'L_WP_UPGRADE_EXPLAIN1' => sprintf($wpuAbs->lang('WPU_Must_Upgrade_Explain1'), '<a href="'. append_sid('wpu_usermap.' .$phpEx) .'" title="upgrade script" style="color: #fff;">', '</a>'),
				'L_WP_UPGRADE_EXPLAIN2' => sprintf($wpuAbs->lang('WPU_Must_Upgrade_Explain2'), '<a href="'. append_sid('admin_wordpress.' .$phpEx .'?ignore=1') .'" title="ignore this prompt" style="color: #fff;">', '</a>')
				);
			}
		} else {
			$ver = $wpSettings['wpuVersion'];
		}

		// pass strings	
		$passVars = array(
			'L_WPMAIN_TITLE' => $wpuAbs->lang('WP_Main_Title'),
			'L_WPMAIN_INTRO' => $wpuAbs->lang('WP_Main_Intro'),
			'L_WPMAIN_INTROADD' => $introAdd,
			'S_WPMAIN_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),
			'L_WPMAIN_TH' => $wpuAbs->lang('WordPress_Integration'),
			'L_WPB1_TITLE' => $button1Title,
			'L_RECOMMENDED' => $recommended,
			'L_WPB1_EXPLAIN' => $button1Explain,
			'L_SUBMITB1' => $button1Value,
			'L_WPB2_TITLE' => $button2Title,
			'L_WPB2_EXPLAIN' => $button2Explain,
			'L_SUBMITB2' => $button2Value,
			'S_MODE_1' => $mode1,
			'S_MODE_2' => $mode2,
			'L_PAYPAL_TITLE' => $wpuAbs->lang('WP_Support'),
			'L_PAYPAL_EXPLAIN' => $wpuAbs->lang('WP_Support_Explain'),
			'L_WP_VERSION' => sprintf($wpuAbs->lang('WP_Version_Text'), $ver)
		);


		//show user mapping link, if package is handling WP logins
		if ( (!empty($wpSettings['integrateLogin'])) && (!empty($wpSettings['wpUri'])) ) {
			$mapLink = append_sid('wpu_usermap.' .$phpEx);
			$passBlockVars['switch_main_page.switch_show_wpmap'] = array(
				'L_MAPLINK_EXPLAIN' => $wpuAbs->lang('WP_MapLink_Explain'),
				'U_MAPLINK' => append_sid("index.$phpEx?i=wp_united"),
				'L_MAPLINK_TITLE' => $wpuAbs->lang('WP_MapLink_Title'),
				'L_MAPLINK' => $wpuAbs->lang('WP_MapLink')
			);
		}
		
		//Check that wp-united/cache is writable
		if(!is_writable(add_trailing_slash($phpbb_root_path) . "wp-united/cache/")) {
			$passBlockVars['switch_main_page.switch_cache_unwritable'] = array(
				'L_CACHE_UNWRITABLE' => $wpuAbs->lang('WPU_Cache_Unwritable')
			);
		}
		
		//Check that wpu-install has been deleted
		if(file_exists(add_trailing_slash($phpbb_root_path) . "wpu-install." . $phpEx)) { 
			$passBlockVars['switch_main_page.switch_install_file_exists'] = array(
				'L_INSTALL_EXISTS' => $wpuAbs->lang('WPU_Install_Exists')
			);
		}
				
		
		//show the page
		$this->showPage($passVars, $passBlockVars);

	}

	// **********************************************************************
	//***												***
	//***				SHOW SETTINGS PAGE			 	***
	//***												***
	//***********************************************************************
	//
	//	Shows a page with all the configuration settings at a glance.
	//
	function settings_show() {
		$this->page_title = 'ACP_WPU_DETAILED';
		$this->tpl_name = 'acp_wp_united';
		global $wpuAbs, $phpEx, $errMsg, $db;

		if ( !empty($errMsg) ) {
			global $wpErrSettings;
			$wpSettings = $wpErrSettings;
		} else {
			//Read settings from db
			$wpSettings = $this->get_settings_set_defaults();
		}
		
		if ( $wpSettings['integrateLogin'] ) {
			$loginEnable = 'checked="checked"';
			$loginDisable = '';
		} else {
			$loginEnable = '';
			$loginDisable = 'checked="checked"';
		}

		if ( $wpuAbs->ver == 'PHPBB2' ) {
			$fullList = ''; $aGroups = ''; $groupList = ''; $fixedList = '';
			$sql = "SELECT group_id, group_name
				FROM " . GROUPS_TABLE . "
				WHERE group_name <> ''
				AND group_single_user <> " . TRUE;
			if(!$result = $db->sql_query($sql)) {
				message_die(GENERAL_ERROR, 'Error getting group information', '', __LINE__, __FILE__, $sql);
			}
			while ( $row = $db->sql_fetchrow($result) ) {
				$aGroups['GID:'.$row['group_id']] = htmlentities($row['group_name']);
			}
			// add in built-in permissions
			$aBuiltIns = array(
			'PHPBB:[ADMIN]' => $wpuAbs->lang('WP_Admin_Title'),
			'PHPBB:[MOD]' => $wpuAbs->lang('WP_Mod_Title'),
			'PHPBB:[USER]' => $wpuAbs->lang('WP_User_Title')
			);
			
			$fullList = array_merge($aGroups, $aBuiltIns);	

			//Parse existing permissions list
			$listElements = array('<S:>', '<C:>', '<A:>', '<E:>', '</:>');
			$listSubs = $listConts = $listAuthors = $listEditors = $listAdmins = ''; 
			$listDestinations = array('listSubs', 'listConts', 'listAuthors', 'listEditors', 'listAdmins');
			for ($i=0;$i<5;$i++) {
				if ( strstr($wpSettings['permList'], $listElements[$i]) ) {
					$list = explode($listElements[$i], $wpSettings['permList']);
					if ( $i < 4 ) {
						if ( strstr($wpSettings['permList'], $listElements[$i+1]) ) {
							$list = explode($listElements[$i+1], $list[1]);
							$gpList = $list[0];
						} else {
							$gpList = $gpList[1];
						}
					} else {
						$gpList = $list[1];
					}
					$gpList = explode('|', $gpList);
					$countElements = 0;
					foreach($gpList as $gp) {
						trim($gp);
						if ( !empty($gp) ) {
							$countElements++;
							if ( array_key_exists($gp,$fullList) ) {
								${$listDestinations[$i]} .= '<option value="'.$gp.'">'.$fullList[$gp]."</option>\n";
								unset($fullList[$gp]);
								$fixedList .= ($countElements > 1)? '|' . $gp : $listElements[$i] . $gp;
							} 
						}
					}
				}
			}
			$fixedList = '[--**WPU-ROLELIST**--]'.$fixedList.'[--**--]';
			
			foreach ( $fullList as $ID => $name ) {
				$groupList .= '<option value="'.$ID.'">'.$name."</option>\n";
			}
		}
		
		

		//set DTD switching radio buttons to initial state
		if ( $wpSettings['dtdSwitch'] ) {
			$dtdSwitchEnable = 'checked="checked"';
			$dtdSwitchDisable = '';
		} else {
			$dtdSwitchEnable = '';
			$dtdSwitchDisable = 'checked="checked"';
		}
		
		//set Show phpBB Header/Footer radio buttons to initial state
		switch($wpSettings['showHdrFtr']) {
		case 'FWD':
			$wInP = 'checked="checked"';
			$pInW = '';
			$pwNone = '';		
			break;
		case 'REV':
			$wInP = '';
			$pInW = 'checked="checked"';
			$pwNone = '';		
			break;
		case 'NONE':
			default;
			$wInP = '';
			$pInW = '';
			$pwNone = 'checked="checked"';		
			break;
		}	
		
		// set CSS Magic and Template Voodoo to initial states
		if($wpSettings['cssMagic']) {
			$cssMOn = 'checked="checked"';
			$cssMOff = '';
		} else {
			$cssMOn = '';
			$cssMOff = 'checked="checked"';
		}
		if($wpSettings['templateVoodoo']) {
			$tvOn = 'checked="checked"';
			$tvOff = '';
		} else {
			$tvOn = '';
			$tvOff = 'checked="checked"';
		}
		// set plugin fixes to initial state
		if($wpSettings['pluginFixes']) {
			$pluginFixesOn = 'checked="checked"';
			$pluginFixesOff = '';
		} else {
			$pluginFixesOn = '';
			$pluginFixesOff = 'checked="checked"';
		}		
		
		//set phpbb css early/late radio buttons
		switch($wpSettings['cssFirst']) {
		case 'P':
			$pFirst = 'checked="checked"';
			$wFirst = '';
			$pNone = '';		
			break;
		case 'W':
			$pFirst = '';
			$wFirst = 'checked="checked"';
			$pNone = '';		
			break;
		case 'NONE':
			$pFirst = '';
			$wFirst = '';
			$pNone = 'checked="checked"';		
			default;
			break;
		}

		//set WP simple hdr ftr radio buttons to initial state
		if ( $wpSettings['wpSimpleHdr'] ) {
			$simpleHeadYes = 'checked="checked"';
			$simpleHeadNo = '';
		} else {
			$simpleHeadYes = '';
			$simpleHeadNo = 'checked="checked"';
		}	
		
		//Set "Remove phpBB header" radio buttons to initial state
		if ( $wpSettings['fixHeader'] ) {
			$fixHeaderYes = 'checked="checked"';
			$fixHeaderNo = '';
		} else {
			$fixHeaderYes = '';
			$fixHeaderNo = 'checked="checked"';
		}
		
		
		//set "users have their own blogs" radio buttons to initial state
		if ( $wpSettings['usersOwnBlogs'] ) {
			$ownEnable = 'checked="checked"';
			$ownDisable = '';
		} else {
			$ownEnable = '';
			$ownDisable = 'checked="checked"';
		}
		
		//set "buttons in profile" radio buttons to initial state
		if ( $wpSettings['buttonsProfile'] ) {
			$btnsProfEnable = 'checked="checked"';
			$btnsProfDisable = '';
		} else {
			$btnsProfEnable = '';
			$btnsProfDisable = 'checked="checked"';
		}	
		
		//set "buttons in posts" radio buttons to initial state
		if ( $wpSettings['buttonsPost'] ) {
			$btnsPostEnable = 'checked="checked"';
			$btnsPostDisable = '';
		} else {
			$btnsPostEnable = '';
			$btnsPostDisable = 'checked="checked"';
		}	
		
		//set "users can switch styles" radio buttons to initial state
		if ( $wpSettings['allowStyleSwitch'] ) {
			$styleSwitchEnable = 'checked="checked"';
			$styleSwitchDisable = '';
		} else {
			$styleSwitchEnable = '';
			$styleSwitchDisable = 'checked="checked"';
		}
		
		//set phpBB Smilies radio buttons to initial state
		if ( $wpSettings['phpbbSmilies'] ) {
			$useSmiliesEnable = 'checked="checked"';
			$useSmiliesDisable = '';
		} else {
			$useSmiliesEnable = '';
			$useSmiliesDisable = 'checked="checked"';
		}	

		//set Word Censor radio buttons to initial state
		if ( $wpSettings['phpbbCensor'] ) {
			$useCensorEnable = 'checked="checked"';
			$useCensorDisable = '';
		} else {
			$useCensorEnable = '';
			$useCensorDisable = 'checked="checked"';
		}	

		//set ONLY LOGGED IN USERS radio buttons to initial state
		if ( $wpSettings['mustLogin'] ) {
			$makePrivateEnable = 'checked="checked"';
			$makePrivateDisable = '';
		} else {
			$makePrivateEnable = '';
			$makePrivateDisable = 'checked="checked"';
		}	

		switch ( $wpSettings['charEncoding'] ) {
		case 'MATCH_WP':
			$matchW = 'checked="checked"';
			$matchP = '';
			$noChange = '';
			break;
		case 'MATCH_PHPBB';
			$matchW = '';
			$matchP = 'checked="checked"';
			$noChange = '';	
			break;
		case 'NO_CHANGE';
			default;
			$matchW = '';
			$matchP = '';
			$noChange = 'checked="checked"';
			break;
		}
		
		//set "create blog home page" radio buttons to initial state
		if ( $wpSettings['useBlogHome'] ) {
			$blogHomeEnable = 'checked="checked"';
			$blogHomeDisable = '';
		} else {
			$blogHomeEnable = '';
			$blogHomeDisable = 'checked="checked"';
		}

		//set "use CSS" radio buttons to initial state
		if ( $wpSettings['blUseCSS'] ) {
			$useCssEnable = 'checked="checked"';
			$useCssDisable = '';
		} else {
			$useCssEnable = '';
			$useCssDisable = 'checked="checked"';
		}	
		
		// set padding text boxes
		if ($wpSettings['phpbbPadding'] == 'NOT_SET') {
			$padding = array('','','','');
		} else {
			$padding = explode('-', $wpSettings['phpbbPadding']);
		}

		if ( $wpSettings['xposting'] ) {
			$xPostEnable = 'checked="checked"';
			$xPostDisable = '';
		} else {
			$xPostEnable = '';
			$xPostDisable = 'checked="checked"';
		}	
			
		if ( $wpSettings['xpostautolink'] ) {
			$xAlEnable = 'checked="checked"';
			$xAlDisable = '';
		} else {
			$xAlEnable = '';
			$xAlDisable = 'checked="checked"';
		}
		
		
		$passVars = array(
			'L_WP_NO_ADV_JS' => $wpuAbs->lang('WP_No_JavaScript'),
			'S_WP_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'S_WPURI' => $wpSettings['wpUri'],
			'S_WPPATH' => $wpSettings['wpPath'],
			'L_WP_TITLE' => $wpuAbs->lang('WP_Title'),
			'L_WP_INTRO1' => $wpuAbs->lang('WP_Intro1'),
			'L_WP_INTRO2' => $wpuAbs->lang('WP_Intro2'),
			'L_WPLOGIN_HEAD' => $wpuAbs->lang('WP_Login_Head'),
			'L_WPLOGIN_TITLE' => $wpuAbs->lang('WP_Login_Title'),
			'L_WPLOGIN_EXPLAIN' => $wpuAbs->lang('WP_Login_Explain'),
			'S_WPLOGIN_ENABLE' => $loginEnable,
			'S_WPLOGIN_DISABLE' => $loginDisable,
			'L_WP_YES' => $wpuAbs->lang('WP_Yes'),
			'L_WP_NO' => $wpuAbs->lang('WP_No'),
			'L_WP_SETTINGS' => $wpuAbs->lang('WP_Settings'),
			'L_WPURI_TITLE' => $wpuAbs->lang('WP_UriTitle'),
			'L_WPURI_EXPLAIN' => $wpuAbs->lang('WP_UriExplain'),
			'L_WPPATH_TITLE' => $wpuAbs->lang('WP_PathTitle'),
			'L_WPPATH_EXPLAIN' => $wpuAbs->lang('WP_PathExplain'),
			'L_WPWIZARD_BLOGURI_TH' => $wpuAbs->lang('WPWiz_BlogURI_TH'),
			'L_WPWIZARD_BLOGURI_TITLE' => $wpuAbs->lang('WPWiz_BlogURI_Title'),
			'L_WPWIZARD_BLOGURI_EXPLAIN1' => $wpuAbs->lang('WPWiz_BlogURI_Explain1'),
			'L_WPWIZARD_BLOGURI_EXPLAIN2' => $wpuAbs->lang('WPWiz_BlogURI_Explain2'),
			'L_WPWIZARD_BLOGURI_EXPLAIN3' => $wpuAbs->lang('WPWiz_BlogURI_Explain3'),
			'L_WPWIZARD_BLOGURI_EXPLAIN4' => $wpuAbs->lang('WPWiz_BlogURI_Explain4'),
			'L_WPWIZARD_BLOGURI_EXPLAIN5' => $wpuAbs->lang('WPWiz_BlogURI_Explain5'),
			'S_BLOGSURI' => $wpSettings['blogsUri'],		
			'L_WPPERMS_TITLE' => $wpuAbs->lang('WP_Perm_Title'),
			'L_WPPERMS_EXPLAIN1' => $wpuAbs->lang('WP_Perm_Explain1'),
			'L_WPPERMS_EXPLAIN2' => $wpuAbs->lang('WP_Perm_Explain2'),
			'L_WPPERMS_EXPLAIN3' => $wpuAbs->lang('WP_Perm_Explain3'),
			'L_WPPERMS_EXPLAIN4' => $wpuAbs->lang('WP_Perm_Explain4'),
			'L_WPLOGIN_OPTTITLE' => $wpuAbs->lang('WPWiz_IntLogin_title'),
			'L_WPXPOST_TITLE' => $wpuAbs->lang('WP_XPost_Title'),
			'L_WPXPOST_EXPLAIN' => $wpuAbs->lang('WP_XPost_Explain'),
			'L_WPXPOST_OPTTITLE' => $wpuAbs->lang('WP_XPost_OptTitle'),			
			'S_WPXPOST_ENABLE' => $xPostEnable,
			'S_WPXPOST_DISABLE' => $xPostDisable,	
			// 0.7.1
			'S_WPXAL_ENABLE' => $xAlEnable,
			'S_WPXAL_DISABLE' => $xAlDisable,
			'L_WPXPOST_AUTOLINKING_TITLE' => $wpuAbs->lang('WPWiz_XPost_Autolink_Title'),				
			'L_WPXPOST_AUTOLINKING_EXPLAIN' => $wpuAbs->lang('WPWiz_XPost_Autolink_Explain'),
			'L_WPXPOSTAL_OPTTITLE' => $wpuAbs->lang('WPWiz_Autolink_Sectitle'),			
			//			
			'L_WPUSER_TITLE' => $wpuAbs->lang('WP_User_Title'),
			'L_WPMOD_TITLE' => $wpuAbs->lang('WP_Mod_Title'),
			'L_WPADMIN_TITLE' => $wpuAbs->lang('WP_Admin_Title'),
			'L_WPADVANCED_HEAD' => $wpuAbs->lang('WP_Advanced_Head'),
			'L_WPINSIDE_TITLE' => $wpuAbs->lang('WPWiz_Inside_Title'),
			'L_WPINSIDE_EXPLAIN1' => $wpuAbs->lang('WPWiz_Inside_Explain1'),
			'L_WPINSIDE_EXPLAIN2' => $wpuAbs->lang('WPWiz_Inside_Explain2'),
			'L_WPINP' => $wpuAbs->lang('WPWiz_Template_Forward'),
			'L_PINWP' => $wpuAbs->lang('WPWiz_Template_Reverse'),
			'L_PW_NONE' => $wpuAbs->lang('WPWiz_Template_None'),
			'L_WPWIZARD_WINP_TITLE' => $wpuAbs->lang('WPWiz_Template_Forward_Title'),
			'L_WPWIZARD_PINW_TITLE' => $wpuAbs->lang('WPWiz_Template_Reverse_Title'),
			'S_WPINP' => $wInP,
			'S_PINWP' => $pInW,
			'S_PW_NONE' => $pwNone,
			
			'L_CSSM_TITLE' => $wpuAbs->lang('WPWiz_CSS_Magic_Title'),
			'L_CSSM_EXPLAIN1' => $wpuAbs->lang('WPWiz_CSS_Magic_Explain1'),
			'L_CSSM_EXPLAIN2' => $wpuAbs->lang('WPWiz_CSS_Magic_Explain2'),
			'S_CSSM_ON' => $cssMOn,
			'S_CSSM_OFF' => $cssMOff,	
			'L_TV_SECTITLE' => $wpuAbs->lang('WPWiz_TemplateVoodoo_SecTitle'),		
			'S_TV_ON' => $tvOn,
			'S_TV_OFF' => $tvOff,
			'L_TV_TITLE' => $wpuAbs->lang('WPWiz_Template_Voodoo_Title'),
			'L_TV_EXPLAIN1' => $wpuAbs->lang('WPWiz_Template_Voodoo_Explain1'),
			'L_TV_EXPLAIN2' => $wpuAbs->lang('WPWiz_Template_Voodoo_Explain2'),			

			
			'L_PLUGIN_FIXES_TITLE' => $wpuAbs->lang('WPWiz_Plugin_Fixes_Title'),
			'L_PLUGIN_FIXES_EXPLAIN1' => $wpuAbs->lang('WPWiz_Plugin_Fixes_Explain1'),
			'L_PLUGIN_FIXES_EXPLAIN2' => $wpuAbs->lang('WPWiz_Plugin_Fixes_Explain2'),
			'S_PLUGIN_FIXES_YES' => $pluginFixesOn,
			'S_PLUGIN_FIXES_NO' => $pluginFixesOff,			
			
			'L_PSTYLES_FIRST_TITLE' => $wpuAbs->lang('WPWiz_PStyles_Early_Title'),
			'L_PSTYLES_FIRST_EXPLAIN1' => $wpuAbs->lang('WPWiz_PStyles_Early_Explain1'),
			'L_PSTYLES_FIRST_EXPLAIN2' => $wpuAbs->lang('WPWiz_PStyles_Early_Explain2'),
			'L_PSTYLES_FIRST_EXPLAIN3' => $wpuAbs->lang('WPWiz_PStyles_Early_Explain3'),
			'L_PSTYLES_FIRST_EXPLAIN4' => $wpuAbs->lang('WPWiz_PStyles_Early_Explain4'),
			'L_PS_NONE' => $wpuAbs->lang('WPWiz_No_PStyles'),
			'S_PS_FIRST' => $pFirst,
			'S_PS_NOTFIRST' => $wFirst,
			'S_PS_NONE' => $pNone,
			'L_WPSIMPLE_TITLE' => $wpuAbs->lang('WPWiz_WPSimple_Title'),
			'L_WPSIMPLE_EXPLAIN1' => $wpuAbs->lang('WPWiz_WPSimple_Explain1'),
			'L_WPSIMPLE_EXPLAIN2' => $wpuAbs->lang('WPWiz_WPSimple_Explain2'),
			'L_WPSIMPLE_EXPLAIN3' => $wpuAbs->lang('WPWiz_WPSimple_Explain3'),		
			'S_WPSIMPLE_YES' => $simpleHeadYes,
			'S_WPSIMPLE_NO' => $simpleHeadNo,

			// New in v0.7.0
			'L_WPFIX_HEADER_TITLE' => $wpuAbs->lang('WPWiz_Fix_Header_Title'),
			'L_WPFIX_HEADER_EXPLAIN1' => $wpuAbs->lang('WPWiz_Fix_Header_Explain1'),
			'L_WPFIX_HEADER_EXPLAIN2' => $wpuAbs->lang('WPWiz_Fix_Header_Explain2'),
			'L_WP_FIX'		=> $wpuAbs->lang('WPWiz_Fix'),
			'L_WP_NOFIX'		=> $wpuAbs->lang('WPWiz_No_Fix'),
			'S_HDR_FIX_ENABLE'	=> $fixHeaderYes,
			'S_HDR_FIX_DISABLE'	=> $fixHeaderNo,						
			
			
			'L_WP_SIMPLE' => $wpuAbs->lang('WPWiz_Simple_Yes'),
			'L_WP_FULLPAGE' => $wpuAbs->lang('WPWiz_Simple_No'),
			'L_WP_PADTITLE' => $wpuAbs->lang('WPWiz_Padding_Title'),
			'L_WP_PADEXPLAIN1' => $wpuAbs->lang('WPWiz_Padding_Explain1'),
			'L_WP_PADEXPLAIN2' => $wpuAbs->lang('WPWiz_Padding_Explain2'),
			'L_WP_PADEXPLAIN3' => $wpuAbs->lang('WPWiz_Padding_Explain3'),
			'L_WP_PIXELS' => $wpuAbs->lang('WPWiz_Pixels'),
			'L_WP_PADTOP' => $wpuAbs->lang('WPWiz_PaddingTop'),
			'L_WP_PADRIGHT' => $wpuAbs->lang('WPWiz_PaddingRight'),
			'L_WP_PADBOTTOM' => $wpuAbs->lang('WPWiz_PaddingBottom'),
			'L_WP_PADLEFT' => $wpuAbs->lang('WPWiz_PaddingLeft'),
			'S_WP_PADTOP' => ($padding[0] != '') ? (int)$padding[0] : '',
			'S_WP_PADRIGHT' => ($padding[1] != '') ? (int)$padding[1] : '',
			'S_WP_PADBOTTOM' => ($padding[2] != '') ? (int)$padding[2] : '',
			'S_WP_PADLEFT' => ($padding[3] != '') ? (int)$padding[3] : '',
			'L_WPPAGE_OPTTITLE' => $wpuAbs->lang('WPWiz_WPPage_OptTitle'),
			'L_WPPAGE_TITLE' => $wpuAbs->lang('WPWiz_Page_Title'),
			'L_WPPAGE_EXPLAIN1' => $wpuAbs->lang('WPWiz_Page_Explain1'),
			'L_WPPAGE_EXPLAIN2' => $wpuAbs->lang('WPWiz_Page_Explain2'),
			'S_WPPAGE' => $wpSettings['wpPageName'],
			'L_WP_YESREC' => $wpuAbs->lang('WP_Yes_Recommend'),
			'L_WP_NOREC' => $wpuAbs->lang('WP_No_Recommend'),		
			'L_WPDTD_TITLE' => $wpuAbs->lang('WP_DTD_Title'),
			'L_WPDTD_EXPLAIN' => $wpuAbs->lang('WP_DTD_Explain'),
			'S_WPDTD_ENABLE' => $dtdSwitchEnable,
			'S_WPDTD_DISABLE' => $dtdSwitchDisable,
			'L_WPROLE_NONE' => $wpuAbs->lang('WP_Role_None'),
			'L_WPROLE_SUBSCRIBER' => $wpuAbs->lang('WP_Role_Subscriber'),
			'L_WPROLE_CONTRIBUTOR' => $wpuAbs->lang('WP_Role_Contributor'),
			'L_WPROLE_AUTHOR' => $wpuAbs->lang('WP_Role_Author'),
			'L_WPROLE_EDITOR' => $wpuAbs->lang('WP_Role_Editor'),
			'L_WPROLE_ADMINISTRATOR' => $wpuAbs->lang('WP_Role_Administrator'),
			
			'L_WPU_GROUPLIST' => $groupList,
			'L_WPU_GROUPLIST_SUBS' => $listSubs,
			'L_WPU_GROUPLIST_CONTS' => $listConts,
			'L_WPU_GROUPLIST_AUTHORS' => $listAuthors,
			'L_WPU_GROUPLIST_EDITORS' => $listEditors,
			'L_WPU_GROUPLIST_ADMINS' => $listAdmins,
			'S_WPU_ROLELIST' => $fixedList,
			'L_WPWIZARD_S4_TITLE' => $wpuAbs->lang('WP_Wizard_Step4_Title'),
			'L_WPOWNBLOGS_TITLE' => $wpuAbs->lang('WPWiz_OwnBlogs_Title'),
			'L_WPOWNBLOGS_EXPLAIN1' => $wpuAbs->lang('WPWiz_OwnBlogs_Explain1'),
			'S_WPOWNBLOGS_ENABLE' => $ownEnable,
			'S_WPOWNBLOGS_DISABLE' => $ownDisable,
			'L_WPBTNSPROF_TITLE' => $wpuAbs->lang('WPWiz_BtnsProf_Title'),
			'L_WPBTNSPROF_EXPLAIN' => $wpuAbs->lang('WPWiz_BtnsProf_Explain'),
			'S_WPBTNSPROF_ENABLE' => $btnsProfEnable,
			'S_WPBTNSPROF_DISABLE' => $btnsProfDisable,
			'L_WPBTNSPOST_TITLE' => $wpuAbs->lang('WPWiz_BtnsPost_Title'),
			'L_WPBTNSPOST_EXPLAIN' => $wpuAbs->lang('WPWiz_BtnsPost_Explain'),
			'S_WPBTNSPOST_ENABLE' => $btnsPostEnable,
			'S_WPBTNSPOST_DISABLE' => $btnsPostDisable,
			'L_WPSTYLESWITCH_TITLE' => $wpuAbs->lang('WPWiz_StyleSwitch_Title'),
			'L_WPSTYLESWITCH_EXPLAIN1' => $wpuAbs->lang('WPWiz_StyleSwitch_Explain1'),
			'L_WPSTYLESWITCH_EXPLAIN2' => $wpuAbs->lang('WPWiz_StyleSwitch_Explain12'),
			'S_WPSTYLESWITCH_ENABLE' => $styleSwitchEnable,
			'S_WPSTYLESWITCH_DISABLE' => $styleSwitchDisable,
			
			'L_WPCHARENC_TITLE' => $wpuAbs->lang('WPWiz_CharEnc_Title'),
			'L_WPCHARENC_EXPLAIN1' =>  $wpuAbs->lang('WPWiz_CharEnc_Explain1'),
			'L_WPCHARENC_EXPLAIN2' =>  $wpuAbs->lang('WPWiz_CharEnc_Explain2'),
			'S_WPCHARENC_MATCHW' => $matchW,
			'S_WPCHARENC_MATCHP' => $matchP,
			'S_WPCHARENC_NOCHANGE' => $noChange,
			'L_WPWIZARD_BEHAVE_TITLE' => $wpuAbs->lang('WP_Wizard_Behave_Title'),
			'L_WPCENSOR_TITLE' => $wpuAbs->lang('WPWiz_Censor_Title'),
			'L_WPCENSOR_EXPLAIN' => $wpuAbs->lang('WPWiz_Censor_Explain'),
			'S_WPCENSOR_ENABLE' => $useCensorEnable,
			'S_WPCENSOR_DISABLE' => $useCensorDisable,
			
			// New in v0.7.0
			'L_PHPBBSMILIES_TITLE' => $wpuAbs->lang('WPWiz_phpBB_Smilies_Title'),
			'L_PHPBBSMILIES_EXPLAIN' => $wpuAbs->lang('WPWiz_phpBB_Smilies_Explain'),
			'S_PHPBBSMILIES_ENABLE' => $useSmiliesEnable,
			'S_PHPBBSMILIES_DISABLE' => $useSmiliesDisable,
			
			'L_WPPRIVATE_TITLE' => $wpuAbs->lang('WPWiz_Private_Title'),
			'L_WPPRIVATE_EXPLAIN' => $wpuAbs->lang('WPWiz_Private_Explain'),
			'S_WPPRIVATE_ENABLE' => $makePrivateEnable,
			'S_WPPRIVATE_DISABLE' => $makePrivateDisable,		
			'L_WP_MATCHW' => $wpuAbs->lang('WPChar_MatchW'),
			'L_WP_MATCHP' => $wpuAbs->lang('WPChar_MatchP'),
			'L_WP_NOCHANGE' => $wpuAbs->lang('WPChar_NoChange'),
			'L_WPOWNBLOGS_OPTTITLE' => $wpuAbs->lang('WP_OwnBlogs_OptTitle'),
			'L_WPBLOGLIST_OPTTITLE' => $wpuAbs->lang('WP_Bloglist_OptTitle'),
			'L_WPBLOGLIST_HEAD' => $wpuAbs->lang('WPWiz_Bloglist_Head'),
			'L_WPBLOGLIST_TITLE' => $wpuAbs->lang('WPWiz_Bloglist_Title'),
			'L_WPBLOGLIST_EXPLAIN1' => $wpuAbs->lang('WPWiz_Bloglist_Explain'),
			'L_WPBLOGLIST_EXPLAIN2' => $wpuAbs->lang('WPWiz_Bloglist_Explain2'),
			'S_BLOGHOME_ENABLE' => $blogHomeEnable,
			'S_BLOGHOME_DISABLE' => $blogHomeDisable,
			
			'L_WPBLOGTITLE_TITLE' => $wpuAbs->lang('WPWiz_BlogListHead_Title'),
			'L_WPBLOGTITLE_EXPLAIN' => $wpuAbs->lang('WPWiz_BlogListHead_Explain'),
			'S_WPBLOGTITLE' => $wpSettings['blogListHead'],
			
			'L_WPBLOGINTRO_TITLE' => $wpuAbs->lang('WPWiz_BlogIntro_Title'),
			'L_WPBLOGINTRO_EXPLAIN1' => $wpuAbs->lang('WPWiz_BlogIntro_Explain'),
			'L_WPBLOGINTRO_EXPLAIN2' => $wpuAbs->lang('WPWiz_BlogIntro_Explain2'),
			'S_WPBLOGINTRO' => $wpSettings['blogIntro'],
			
			'L_WPBLOGSPERPAGE_TITLE' => $wpuAbs->lang('WPWiz_NumBlogList_Title'),
			'L_WPBLOGSPERPAGE_EXPLAIN' => $wpuAbs->lang('WPWiz_NumBlogList_Explain'),
			'S_WPBLOGSPERPAGE' => $wpSettings['blogsPerPage'],			

			'L_WPBL_CSS_TITLE' => $wpuAbs->lang('WPWiz_blCSS_Title'),
			'L_WPUBL_CSS_EXPLAIN1' => $wpuAbs->lang('WPWiz_blCSS_Explain'),
			'L_WPUBL_CSS_EXPLAIN2' => $wpuAbs->lang('WPWiz_blCSS_Explain2'),
			'S_WPUBL_CSS_ENABLE' => $useCssEnable,					
			'S_WPUBL_CSS_DISABLE' => $useCssDisable,
			
			'L_SUBMIT' => $wpuAbs->lang('WP_Submit'),
			'L_WP_FOOTER' => $wpuAbs->lang('WP_Footer')
		);
		
		
		
		
		// set the page section to show
		$passBlockVars = array(
			'switch_settings_page' => array(),
		);
		
		// Should we show an error box at the top of the page?
		if ( !empty($errMsg) ) {
			$passBlockVars['switch_settings_page.switch_wp_error'] = array(
			'WP_ERROR_MSG' => $errMsg
			);
		}

		$this->showPage($passVars, $passBlockVars);
	}
	
	function step1_show() {
		
		//
		//	STEP 1 -- WORDPRESS INSTALLATION INTRO PAGE
		//	-----------------------------------------------------------------------
		//	Not interactive. Just asks tthe user to install WordPress. AKA "Step 0"
		//
		global $wpuAbs, $numWizardSteps, $phpEx;
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$wpuAbs->lang('WP_Wizard_H1');
		// pass strings
		$passVars = array(
			'L_WPWIZARD_H1' => $wpuAbs->lang('WP_Wizard_H1'),
			'L_WPWIZARD_STEP' => sprintf($wpuAbs->lang('WP_Wizard_Step'), 0, $numWizardSteps),
			'L_WPWIZARD_S1_TITLE' => $wpuAbs->lang('WP_Wizard_Step1_Title'),
			'L_WPWIZARD_S1_EXPLAIN1' => $wpuAbs->lang('WP_Wizard_Step1_Explain1'),
			'L_WPWIZARD_S1_EXPLAIN2' => $wpuAbs->lang('WP_Wizard_Step1_Explain2'),
			'L_WPWIZARD_S1_EXPLAIN2B' => $wpuAbs->lang('WP_Wizard_Step1_Explain2b'),
			'L_WPWIZARD_S1_EXPLAIN3' => $wpuAbs->lang('WP_Wizard_Step1_Explain3'),
			'L_WPWIZARD_S1_EXPLAIN4' => $wpuAbs->lang('WP_Wizard_Step1_Explain4'),
			'L_WPWIZARD_S1_EXPLAIN5' => $wpuAbs->lang('WP_Wizard_Step1_Explain5'),
			'S_WPWIZ_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),
			'L_WPNEXT' => sprintf($wpuAbs->lang('WP_Wizard_Next'), 1)
		);

		// set the page section to show
		$passBlockVars = array(
			'switch_wizard_page1' => array(),
		);

		//show the page
		$this->showPage($passVars, $passBlockVars);
	}
	
	//
	//	STEP 1B -- WORDPRESS INSTALLATION SETTINGS
	//	-----------------------------------------------------------------------
	//	Sets the WordPress path & URL to WordPress. User sees as continuation of "Step 1"
	//	
	function step1b_show($uriResult = '', $pathResult = '') {
		global $wpuAbs, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx;
		
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$wpuAbs->lang('WP_Wizard_H1');
		
		//Read settings from db
		$wpSettings = $this->get_settings_set_defaults();
		
		// This page may be being called from the "test uri" or "find path" functions, when AJAX is unavailable.
		// In that case, set these fields to match what the user just inputted.
		if ( !empty($uriResult) ) {
			$wpSettings['wpUri'] = $this->clean_path(request_var('txt_Uri', ''));
			$inpPath = $this->clean_path(request_var('txt_Path', ''));
			if ( !empty($inpPath) ) {
				$wpSettings['wpPath'] = $inpPath;
			}
		}
		if ( !empty($pathResult) ) {
			$wpSettings['wpPath'] = $this->clean_path(request_var('txt_Path', ''));
			$inpUri = $this->clean_path(request_var('txt_Uri', ''));
			if ( !empty($inpUri) ) {
				$wpSettings['wpUri'] = $inpUri;
			}
		}

		$uri = append_sid("index.$phpEx?i=wp_united");				
		// pass strings
		$passVars = array(
			'L_WP_NO_ADV_JS' => $wpuAbs->lang('WP_No_JavaScript'),
			'L_WPWIZARD_H1' => $wpuAbs->lang('WP_Wizard_H1'),
			'L_WPWIZARD_STEP' => sprintf($wpuAbs->lang('WP_Wizard_Step'), 1, $numWizardSteps),
			'L_WPWIZARD_S1B_TITLE' => $wpuAbs->lang('WP_Wizard_Step1b_Title'),
			'L_WPWIZARD_S1B_EXPLAIN1' => $wpuAbs->lang('WP_Wizard_Step1b_Explain1'),
			'S_WPWIZ_ACTION' => $uri,
			'S_WPAJAX_ACTION' => str_replace ('&amp;', '&', $uri),
			'L_WPWIZARD_S1B_TH1' => $wpuAbs->lang('WP_Wizard_Step1b_TH1'),
			'L_WPURI_TITLE' => $wpuAbs->lang('WP_UriTitle'),
			'L_WPWIZURI_EXPLAIN' => $wpuAbs->lang('WP_Wizard_URI_Explain'),
			'S_WPURI' => $wpSettings['wpUri'],
			'L_WPWIZURI_TEST_TITLE' => $wpuAbs->lang('WP_Wizard_URI_Test_Title'),
			'L_WPWIZURI_TEST_EXPLAIN' => $wpuAbs->lang('WP_Wizard_URI_Test_Explain'),
			'L_WPURITEST' => $wpuAbs->lang('WP_URI_Test'),
			'L_WPWIZARD_S1B_TH2' => $wpuAbs->lang('WP_Wizard_Step1b_TH2'),
			'L_WPPATH_TITLE' => $wpuAbs->lang('WP_PathTitle'),
			'L_WPWIZPATH_EXPLAIN1' => $wpuAbs->lang('WP_Wizard_Path_Explain1'),
			'L_WPWIZPATH_EXPLAIN2' => $wpuAbs->lang('WP_Wizard_Path_Explain2'),
			'L_WPPATHTEST' => $wpuAbs->lang('WP_Path_Test'),
			'L_WPWIZPATH_EXPLAIN3' => $wpuAbs->lang('WP_Wizard_Path_Explain3'),
			'S_WPPATH' => $wpSettings['wpPath'],
			'L_WPWIZARD_BLOGURI_TH' => $wpuAbs->lang('WPWiz_BlogURI_TH'),
			'L_WPWIZARD_BLOGURI_TITLE' => $wpuAbs->lang('WPWiz_BlogURI_Title'),
			'L_WPWIZARD_BLOGURI_EXPLAIN1' => $wpuAbs->lang('WPWiz_BlogURI_Explain1'),
			'L_WPWIZARD_BLOGURI_EXPLAIN2' => $wpuAbs->lang('WPWiz_BlogURI_Explain2'),
			'L_WPWIZARD_BLOGURI_EXPLAIN3' => $wpuAbs->lang('WPWiz_BlogURI_Explain3'),
			'L_WPWIZARD_BLOGURI_EXPLAIN4' => $wpuAbs->lang('WPWiz_BlogURI_Explain4'),
			'L_WPWIZARD_BLOGURI_EXPLAIN5' => $wpuAbs->lang('WPWiz_BlogURI_Explain5'),
			'S_BLOGSURI' => $wpSettings['blogsUri'],
			'L_WPWIZARD_S1B_TH3' => $wpuAbs->lang('WP_Wizard_Step1b_TH3'),
			'L_WP_YES' => $wpuAbs->lang('WP_Yes'),
			'L_WP_NO' => $wpuAbs->lang('WP_No'),
			'L_WPBACK' => $wpuAbs->lang('WP_Wizard_BackStart'),
			'L_WPNEXT' => sprintf($wpuAbs->lang('WP_Wizard_Next'), 2),
			'L_WPWIZ_URITESTRESULT_NOAJAX' => $uriResult,
			'L_WPWIZ_PATHTESTRESULT_NOAJAX' => $pathResult
		);
		
		// set the page section to show
		$passBlockVars = array(
			'switch_wizard_page1b' => array(),
		);

		// Should we show an error box at the top of the page?
		if ( !empty($wizShowError) ) {
			$passBlockVars['switch_wizard_page1b.switch_wp_error'] = array(
				'WP_ERROR_MSG' => $wizErrorMsg
			);
		}
		
		//show the page
		$this->showPage($passVars, $passBlockVars); 
	}
	
	
	

	function step2_show() {
		//
		//	STEP 2 -- INTEGRATION SETTINGS
		//	--------------------------------------------------
		//	Sets how login integration is handled
		//
		global $wpuAbs, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx, $db;
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$wpuAbs->lang('WP_Wizard_H1');
		
		//Read settings from db
		$wpSettings = $this->get_settings_set_defaults();
		
		if ( $wpSettings['integrateLogin'] ) {
			$loginEnable = 'checked="checked"';
			$loginDisable = '';
		} else {
			$loginEnable = '';
			$loginDisable = 'checked="checked"';
		}
		
		if ( $wpSettings['xposting'] ) {
			$xPostEnable = 'checked="checked"';
			$xPostDisable = '';
		} else {
			$xPostEnable = '';
			$xPostDisable = 'checked="checked"';
		}		
					
		if ( $wpSettings['xpostautolink'] ) {
			$xAlEnable = 'checked="checked"';
			$xAlDisable = '';
		} else {
			$xAlEnable = '';
			$xAlDisable = 'checked="checked"';
		}


		if ( $wpuAbs->ver == 'PHPBB2' ) {
			// get list of groups
			$fullList = ''; $aGroups = ''; $groupList = ''; $fixedList = '';
			$sql = "SELECT group_id, group_name
				FROM " . GROUPS_TABLE . "
				WHERE group_name <> ''
				AND group_single_user <> " . TRUE;
			if(!$result = $db->sql_query($sql)) {
				message_die(GENERAL_ERROR, 'Error getting group information', '', __LINE__, __FILE__, $sql);
			}
			$numGroups = 0;
			while ( $row = $db->sql_fetchrow($result) ) {
				$numGroups++;
				$aGroups['GID:'.$row['group_id']] = htmlentities($row['group_name']);
			}
			// add in built-in permissions
			$aBuiltIns = array(
				'PHPBB:[ADMIN]' => $wpuAbs->lang('WP_Admin_Title'),
				'PHPBB:[MOD]' => $wpuAbs->lang('WP_Mod_Title'),
				'PHPBB:[USER]' => $wpuAbs->lang('WP_User_Title')
			);
			
			if ( $numGroups ) {
				$fullList = array_merge($aGroups, $aBuiltIns);	
			} else {
				$fullList = $aBuiltIns;
			}

			//Parse existing permissions list
			$listElements = array('<S:>', '<C:>', '<A:>', '<E:>', '</:>');
			$listSubs = $listConts = $listAuthors = $listEditors = $listAdmins = ''; 
			$listDestinations = array('listSubs', 'listConts', 'listAuthors', 'listEditors', 'listAdmins');
			for ($i=0;$i<5;$i++) {
				if ( strstr($wpSettings['permList'], $listElements[$i]) ) {
					$list = explode($listElements[$i], $wpSettings['permList']);
					if ( $i < 4 ) {
						if ( strstr($wpSettings['permList'], $listElements[$i+1]) ) {
							$list = explode($listElements[$i+1], $list[1]);
							$gpList = $list[0];
						} else {
							$gpList = $gpList[1];
						}
					} else {
						$gpList = $list[1];
					}
					$gpList = explode('|', $gpList);
					$countElements = 0;
					foreach($gpList as $gp) {
						trim($gp);
						if ( !empty($gp) ) {
							$countElements++;
							if ( array_key_exists($gp,$fullList) ) {
								${$listDestinations[$i]} .= '<option value="'.$gp.'">'.$fullList[$gp]."</option>\n";
								unset($fullList[$gp]);
								$fixedList .= ($countElements > 1)? '|' . $gp : $listElements[$i] . $gp;
							} 
						}
					}
				}
			}
			$fixedList = '[--**WPU-ROLELIST**--]'.$fixedList.'[--**--]';
			
			foreach ( $fullList as $ID => $name ) {
				$groupList .= '<option value="'.$ID.'">'.$name."</option>\n";
			}
		}
		// pass strings
		$passVars = array(		
			'L_WP_NO_ADV_JS'  => $wpuAbs->lang('WP_No_JavaScript'),
			'L_WPWIZARD_H1' => $wpuAbs->lang('WP_Wizard_H1'),
			'L_WPWIZARD_STEP' => sprintf($wpuAbs->lang('WP_Wizard_Step'), 2, $numWizardSteps),
			'L_WPWIZARD_S2_TITLE' => $wpuAbs->lang('WP_Wizard_Step2_Title'),
			'L_WPWIZARD_S2_EXPLAIN' => $wpuAbs->lang('WP_Wizard_Step2_Explain'),
			'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'L_WPLOGIN_HEAD' => $wpuAbs->lang('WP_Login_Head'),
			'L_WPLOGIN_TITLE' => $wpuAbs->lang('WP_Login_Title'),
			'L_WPLOGIN_EXPLAIN' => $wpuAbs->lang('WP_Login_Explain'),
			'S_WPLOGIN_ENABLE' => $loginEnable,
			'S_WPLOGIN_DISABLE' => $loginDisable,
			'L_WP_YES' => $wpuAbs->lang('WP_Yes'),
			'L_WP_NO' => $wpuAbs->lang('WP_No'),
			'L_WPLOGIN_OPTTITLE' => $wpuAbs->lang('WPWiz_IntLogin_title'),
			'L_WPPERMS_TITLE' => $wpuAbs->lang('WP_Perm_Title'),
			'L_WPPERMS_EXPLAIN1' => $wpuAbs->lang('WP_Perm_Explain1'),
			'L_WPPERMS_EXPLAIN2' => $wpuAbs->lang('WP_Perm_Explain2'),
			'L_WPPERMS_EXPLAIN3' => $wpuAbs->lang('WP_Perm_Explain3'),
			'L_WPPERMS_EXPLAIN4' => $wpuAbs->lang('WP_Perm_Explain4'),
			'L_WPUSER_TITLE' => $wpuAbs->lang('WP_User_Title'),
			'L_WPMOD_TITLE' => $wpuAbs->lang('WP_Mod_Title'),
			'L_WPADMIN_TITLE' => $wpuAbs->lang('WP_Admin_Title'),
			'L_WPADVANCED_HEAD' => $wpuAbs->lang('WP_Advanced_Head'),
			'L_WPROLE_NONE' => $wpuAbs->lang('WP_Role_None'),
			'L_WPROLE_SUBSCRIBER' => $wpuAbs->lang('WP_Role_Subscriber'),
			'L_WPROLE_CONTRIBUTOR' => $wpuAbs->lang('WP_Role_Contributor'),
			'L_WPROLE_AUTHOR' => $wpuAbs->lang('WP_Role_Author'),
			'L_WPROLE_EDITOR' => $wpuAbs->lang('WP_Role_Editor'),
			'L_WPROLE_ADMINISTRATOR' => $wpuAbs->lang('WP_Role_Administrator'),
			'L_WPXPOST_TITLE' => $wpuAbs->lang('WP_XPost_Title'),
			'L_WPXPOST_EXPLAIN' => $wpuAbs->lang('WP_XPost_Explain'),
			'L_WPXPOST_OPTTITLE' => $wpuAbs->lang('WP_XPost_OptTitle'),
			'S_WPXPOST_ENABLE' => $xPostEnable,
			'S_WPXPOST_DISABLE' => $xPostDisable,	
			// 0.71
			'S_WPXAL_ENABLE' => $xAlEnable,
			'S_WPXAL_DISABLE' => $xAlDisable,
			'L_WPXPOST_AUTOLINKING_TITLE' => $wpuAbs->lang('WPWiz_XPost_Autolink_Title'),				
			'L_WPXPOST_AUTOLINKING_EXPLAIN' => $wpuAbs->lang('WPWiz_XPost_Autolink_Explain'),						
			'L_WPXPOSTAL_OPTTITLE' => $wpuAbs->lang('WPWiz_Autolink_Sectitle'),
			//
			'L_WPU_GROUPLIST' => $groupList,
			'L_WPU_GROUPLIST_SUBS' => $listSubs,
			'L_WPU_GROUPLIST_CONTS' => $listConts,
			'L_WPU_GROUPLIST_AUTHORS' => $listAuthors,
			'L_WPU_GROUPLIST_EDITORS' => $listEditors,
			'L_WPU_GROUPLIST_ADMINS' => $listAdmins,
			'S_WPU_ROLELIST' => $fixedList,
			
			'L_WPBACK' => sprintf($wpuAbs->lang('WP_Wizard_Back'), 1),
			'L_WPNEXT' => sprintf($wpuAbs->lang('WP_Wizard_Next'), 3)		
		);
		
		// set the page section to show
		$passBlockVars['switch_wizard_page2'] = array();
		


		// Should we show an error box at the top of the page?
		if ( !empty($wizShowError) ) {
			$passBlockVars['switch_wizard_page2.switch_wp_error'] = array(
				'WP_ERROR_MSG' => $wizErrorMsg
			);
		}
		//show the page
		$this->showPage($passVars, $passBlockVars);

	}


	//
	//	STEP 3 -- DISPLAY & BEHAVIOUR SETTINGS
	//	------------------------------------------------------------
	//	Sets the appearance of the WordPress page.
	//
	function step3_show() {
		global $wpuAbs, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx;
		
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$wpuAbs->lang('WP_Wizard_H1');
		
		//Read settings from db
		$wpSettings = $this->get_settings_set_defaults();

		//set dtd switching radio buttons to initial state
		if ( $wpSettings['dtdSwitch'] ) {
			$dtdSwitchEnable = 'checked="checked"';
			$dtdSwitchDisable = '';
		} else {
			$dtdSwitchEnable = '';
			$dtdSwitchDisable = 'checked="checked"';
		}
		
		//set Show phpBB Header/Footer radio buttons to initial state
		switch($wpSettings['showHdrFtr']) {
		case 'FWD':
			$wInP = 'checked="checked"';
			$pInW = '';
			$pwNone = '';		
			break;
		case 'REV':
			$wInP = '';
			$pInW = 'checked="checked"';
			$pwNone = '';		
			break;
		case 'NONE':
			default;
			$wInP = '';
			$pInW = '';
			$pwNone = 'checked="checked"';		
			break;
		}
		
		// set CSS Magic and Template Voodoo to initial states
		if($wpSettings['cssMagic']) {
			$cssMOn = 'checked="checked"';
			$cssMOff = '';
		} else {
			$cssMOn = '';
			$cssMOff = 'checked="checked"';
		}
		if($wpSettings['templateVoodoo']) {
			$tvOn = 'checked="checked"';
			$tvOff = '';
		} else {
			$tvOn = '';
			$tvOff = 'checked="checked"';
		}
		// set plugin fixes to initial state
		if($wpSettings['pluginFixes']) {
			$pluginFixesOn = 'checked="checked"';
			$pluginFixesOff = '';
		} else {
			$pluginFixesOn = '';
			$pluginFixesOff = 'checked="checked"';
		}	
		
		//set phpbb css early/late radio buttons
		switch($wpSettings['cssFirst']) {
		case 'P':
			$pFirst = 'checked="checked"';
			$wFirst = '';
			$pNone = '';		
			break;
		case 'W':
			$pFirst = '';
			$wFirst = 'checked="checked"';
			$pNone = '';		
			break;
		case 'NONE':
			$pFirst = '';
			$wFirst = '';
			$pNone = 'checked="checked"';		
			default;
			break;
		}

		//set WP simple hdr ftr radio buttons to initial state
		if ( $wpSettings['wpSimpleHdr'] ) {
			$simpleHeadYes = 'checked="checked"';
			$simpleHeadNo = '';
		} else {
			$simpleHeadYes = '';
			$simpleHeadNo = 'checked="checked"';
		}
		
		
		//Set "Remove phpBB header" radio buttons to initial state
		if ( $wpSettings['fixHeader'] ) {
			$fixHeaderYes = 'checked="checked"';
			$fixHeaderNo = '';
		} else {
			$fixHeaderYes = '';
			$fixHeaderNo = 'checked="checked"';
		}
		
		//set phpBB Smilies radio buttons to initial state
		if ( $wpSettings['phpbbSmilies'] ) {
			$useSmiliesEnable = 'checked="checked"';
			$useSmiliesDisable = '';
		} else {
			$useSmiliesEnable = '';
			$useSmiliesDisable = 'checked="checked"';
		}			
		
		
		//set Word Censor radio buttons to initial state
		if ( $wpSettings['phpbbCensor'] ) {
			$useCensorEnable = 'checked="checked"';
			$useCensorDisable = '';
		} else {
			$useCensorEnable = '';
			$useCensorDisable = 'checked="checked"';
		}	
		
		//set ONLY LOGGED IN USERS radio buttons to initial state
		if ( $wpSettings['mustLogin'] ) {
			$makePrivateEnable = 'checked="checked"';
			$makePrivateDisable = '';
		} else {
			$makePrivateEnable = '';
			$makePrivateDisable = 'checked="checked"';
		}	

		//set character encoding radio buttons to initial state
		switch ( $wpSettings['charEncoding'] ) {
		case 'MATCH_WP':
			$matchW = 'checked="checked"';
			$matchP = '';
			$noChange = '';
			break;
		case 'MATCH_PHPBB';
			$matchW = '';
			$matchP = 'checked="checked"';
			$noChange = '';	
			break;
		case 'NO_CHANGE';
			default;
			$matchW = '';
			$matchP = '';
			$noChange = 'checked="checked"';
			break;
		}	
		
		if ($wpSettings['phpbbPadding'] == 'NOT_SET') {
			$padding = array('','','','');
		} else {
			$padding = explode('-', $wpSettings['phpbbPadding']);
		}
		
		// pass strings
		$passVars = array(	
			'L_WP_NO_ADV_JS'  => $wpuAbs->lang('WP_No_JavaScript'),
			'L_WPWIZARD_H1' => $wpuAbs->lang('WP_Wizard_H1'),
			'L_WPWIZARD_STEP' => sprintf($wpuAbs->lang('WP_Wizard_Step'), 3, $numWizardSteps),
			'L_WPWIZARD_S3_TITLE' => $wpuAbs->lang('WP_Wizard_Step3_Title'),
			'L_WPWIZARD_DISPLAY_TITLE' => $wpuAbs->lang('WP_Wizard_Display_Title'),
			'L_WPWIZARD_BEHAVE_TITLE' => $wpuAbs->lang('WP_Wizard_Behave_Title'),
			'L_WPWIZARD_S3_EXPLAIN' => $wpuAbs->lang('WP_Wizard_Step3_Explain'),
			'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'L_WPINSIDE_TITLE' => $wpuAbs->lang('WPWiz_Inside_Title'),
			'L_WPINSIDE_EXPLAIN1' => $wpuAbs->lang('WPWiz_Inside_Explain1'),
			'L_WPINSIDE_EXPLAIN2' => $wpuAbs->lang('WPWiz_Inside_Explain2'),
			'L_WPINP' => $wpuAbs->lang('WPWiz_Template_Forward'),
			'L_PINWP' => $wpuAbs->lang('WPWiz_Template_Reverse'),
			'L_PW_NONE' => $wpuAbs->lang('WPWiz_Template_None'),
			'L_WPWIZARD_WINP_TITLE' => $wpuAbs->lang('WPWiz_Template_Forward_Title'),
			'L_WPWIZARD_PINW_TITLE' => $wpuAbs->lang('WPWiz_Template_Reverse_Title'),
			'S_WPINP' => $wInP,
			'S_PINWP' => $pInW,
			'S_PW_NONE' => $pwNone,
			
			'L_CSSM_TITLE' => $wpuAbs->lang('WPWiz_CSS_Magic_Title'),
			'L_CSSM_EXPLAIN1' => $wpuAbs->lang('WPWiz_CSS_Magic_Explain1'),
			'L_CSSM_EXPLAIN2' => $wpuAbs->lang('WPWiz_CSS_Magic_Explain2'),
			'S_CSSM_ON' => $cssMOn,
			'S_CSSM_OFF' => $cssMOff,	
			'L_TV_SECTITLE' => $wpuAbs->lang('WPWiz_TemplateVoodoo_SecTitle'),		
			'S_TV_ON' => $tvOn,
			'S_TV_OFF' => $tvOff,
			'L_TV_TITLE' => $wpuAbs->lang('WPWiz_Template_Voodoo_Title'),
			'L_TV_EXPLAIN1' => $wpuAbs->lang('WPWiz_Template_Voodoo_Explain1'),
			'L_TV_EXPLAIN2' => $wpuAbs->lang('WPWiz_Template_Voodoo_Explain2'),
			
			'L_PLUGIN_FIXES_TITLE' => $wpuAbs->lang('WPWiz_Plugin_Fixes_Title'),
			'L_PLUGIN_FIXES_EXPLAIN1' => $wpuAbs->lang('WPWiz_Plugin_Fixes_Explain1'),
			'L_PLUGIN_FIXES_EXPLAIN2' => $wpuAbs->lang('WPWiz_Plugin_Fixes_Explain2'),
			'S_PLUGIN_FIXES_YES' => $pluginFixesOn,
			'S_PLUGIN_FIXES_NO' => $pluginFixesOff,			

			'L_PSTYLES_FIRST_TITLE' => $wpuAbs->lang('WPWiz_PStyles_Early_Title'),
			'L_PSTYLES_FIRST_EXPLAIN1' => $wpuAbs->lang('WPWiz_PStyles_Early_Explain1'),
			'L_PSTYLES_FIRST_EXPLAIN2' => $wpuAbs->lang('WPWiz_PStyles_Early_Explain2'),
			'L_PSTYLES_FIRST_EXPLAIN3' => $wpuAbs->lang('WPWiz_PStyles_Early_Explain3'),
			'L_PSTYLES_FIRST_EXPLAIN4' => $wpuAbs->lang('WPWiz_PStyles_Early_Explain4'),
			'L_PS_NONE' => $wpuAbs->lang('WPWiz_No_PStyles'),
			'S_PS_FIRST' => $pFirst,
			'S_PS_NOTFIRST' => $wFirst,
			'S_PS_NONE' => $pNone,
			'L_WPSIMPLE_TITLE' => $wpuAbs->lang('WPWiz_WPSimple_Title'),
			'L_WPSIMPLE_EXPLAIN1' => $wpuAbs->lang('WPWiz_WPSimple_Explain1'),
			'L_WPSIMPLE_EXPLAIN2' => $wpuAbs->lang('WPWiz_WPSimple_Explain2'),
			'L_WPSIMPLE_EXPLAIN3' => $wpuAbs->lang('WPWiz_WPSimple_Explain3'),		
			'S_WPSIMPLE_YES' => $simpleHeadYes,
			'S_WPSIMPLE_NO' => $simpleHeadNo,
			'L_WP_SIMPLE' => $wpuAbs->lang('WPWiz_Simple_Yes'),
			'L_WP_FULLPAGE' => $wpuAbs->lang('WPWiz_Simple_No'),
			
			// New in v0.7.0
			'L_WPFIX_HEADER_TITLE' => $wpuAbs->lang('WPWiz_Fix_Header_Title'),
			'L_WPFIX_HEADER_EXPLAIN1' => $wpuAbs->lang('WPWiz_Fix_Header_Explain1'),
			'L_WPFIX_HEADER_EXPLAIN2' => $wpuAbs->lang('WPWiz_Fix_Header_Explain2'),
			'L_WP_FIX'		=> $wpuAbs->lang('WPWiz_Fix'),
			'L_WP_NOFIX'		=> $wpuAbs->lang('WPWiz_No_Fix'),
			'S_HDR_FIX_ENABLE'	=> $fixHeaderYes,
			'S_HDR_FIX_DISABLE'	=> $fixHeaderNo,	
			
			'L_WP_PADTITLE' => $wpuAbs->lang('WPWiz_Padding_Title'),
			'L_WP_PADEXPLAIN1' => $wpuAbs->lang('WPWiz_Padding_Explain1'),
			'L_WP_PADEXPLAIN2' => $wpuAbs->lang('WPWiz_Padding_Explain2'),
			'L_WP_PADEXPLAIN3' => $wpuAbs->lang('WPWiz_Padding_Explain3'),
			'L_WP_PIXELS' => $wpuAbs->lang('WPWiz_Pixels'),
			'L_WP_PADTOP' => $wpuAbs->lang('WPWiz_PaddingTop'),
			'L_WP_PADRIGHT' => $wpuAbs->lang('WPWiz_PaddingRight'),
			'L_WP_PADBOTTOM' => $wpuAbs->lang('WPWiz_PaddingBottom'),
			'L_WP_PADLEFT' => $wpuAbs->lang('WPWiz_PaddingLeft'),
			'S_WP_PADTOP' => ($padding[0] != '') ? (int)$padding[0] : '',
			'S_WP_PADRIGHT' => ($padding[1] != '') ? (int)$padding[1] : '',
			'S_WP_PADBOTTOM' => ($padding[2] != '') ? (int)$padding[2] : '',
			'S_WP_PADLEFT' => ($padding[3] != '') ? (int)$padding[3] : '',

			'L_WPPAGE_OPTTITLE' => $wpuAbs->lang('WPWiz_WPPage_OptTitle'),
			'L_WPPAGE_TITLE' => $wpuAbs->lang('WPWiz_Page_Title'),
			'L_WPPAGE_EXPLAIN1' => $wpuAbs->lang('WPWiz_Page_Explain1'),
			'L_WPPAGE_EXPLAIN2' => $wpuAbs->lang('WPWiz_Page_Explain2'),
			'S_WPPAGE' => $wpSettings['wpPageName'],
			
			'L_WP_YES' => $wpuAbs->lang('WP_Yes'),
			'L_WP_YESREC' => $wpuAbs->lang('WP_Yes_Recommend'),
			'L_WP_NO' => $wpuAbs->lang('WP_No'),
			'L_WP_NOREC' => $wpuAbs->lang('WP_No_Recommend'),
			'L_WP_MATCHW' => $wpuAbs->lang('WPChar_MatchW'),
			'L_WP_MATCHP' => $wpuAbs->lang('WPChar_MatchP'),
			'L_WP_NOCHANGE' => $wpuAbs->lang('WPChar_NoChange'),
			'L_WPDTD_TITLE' => $wpuAbs->lang('WP_DTD_Title'),
			'L_WPDTD_EXPLAIN' => $wpuAbs->lang('WP_DTD_Explain'),
			'S_WPDTD_ENABLE' => $dtdSwitchEnable,
			'S_WPDTD_DISABLE' => $dtdSwitchDisable,
			'L_WPCHARENC_TITLE' => $wpuAbs->lang('WPWiz_CharEnc_Title'),
			'L_WPCHARENC_EXPLAIN1' =>  $wpuAbs->lang('WPWiz_CharEnc_Explain1'),
			'L_WPCHARENC_EXPLAIN2' =>  $wpuAbs->lang('WPWiz_CharEnc_Explain2'),
			'S_WPCHARENC_MATCHW' => $matchW,
			'S_WPCHARENC_MATCHP' => $matchP,
			'S_WPCHARENC_NOCHANGE' => $noChange,
			'L_WPCENSOR_TITLE' => $wpuAbs->lang('WPWiz_Censor_Title'),
			'L_WPCENSOR_EXPLAIN' => $wpuAbs->lang('WPWiz_Censor_Explain'),
			'S_WPCENSOR_ENABLE' => $useCensorEnable,
			'S_WPCENSOR_DISABLE' => $useCensorDisable,
			
			// New in v0.7.0
			'L_PHPBBSMILIES_TITLE' => $wpuAbs->lang('WPWiz_phpBB_Smilies_Title'),
			'L_PHPBBSMILIES_EXPLAIN' => $wpuAbs->lang('WPWiz_phpBB_Smilies_Explain'),
			'S_PHPBBSMILIES_ENABLE' => $useSmiliesEnable,
			'S_PHPBBSMILIES_DISABLE' => $useSmiliesDisable,
			
			'L_WPPRIVATE_TITLE' => $wpuAbs->lang('WPWiz_Private_Title'),
			'L_WPPRIVATE_EXPLAIN' => $wpuAbs->lang('WPWiz_Private_Explain'),
			'S_WPPRIVATE_ENABLE' => $makePrivateEnable,
			'S_WPPRIVATE_DISABLE' => $makePrivateDisable,		
			'L_WPBACK' => sprintf($wpuAbs->lang('WP_Wizard_Back'), 2),
			'L_WPNEXT' => sprintf($wpuAbs->lang('WP_Wizard_Next'), 4)
		);
		
		// set the page section to show
		$passBlockVars = array(
		'switch_wizard_page3' => array(),
		);

		// Should we show an error box at the top of the page?
		if ( !empty($wizShowError) ) {
			$passBlockVars['switch_wizard_page3.switch_wp_error'] = array(
				'WP_ERROR_MSG' => $wizErrorMsg
			);
		}
		
		//show the page
		$this->showPage($passVars, $passBlockVars);
		
	}

	//
	//	STEP 4 -- "TO BLOG OR NOT TO BLOG?"
	//	------------------------------------------------------
	//	If the user wants logins to be integrated, then we can offer them a whole host of options
	//	controlling how members use/see their blogs.
	//	
	function step4_show($dir='FWD') {
		global $wpuAbs, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx;
		
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$wpuAbs->lang('WP_Wizard_H1');


		//Read settings from db
		$wpSettings = $this->get_settings_set_defaults();

		if ( $wpSettings['integrateLogin'] ) {
			
			//set "users have their own blogs" radio buttons to initial state
			if ( $wpSettings['usersOwnBlogs'] ) {
				$ownEnable = 'checked="checked"';
				$ownDisable = '';
			} else {
				$ownEnable = '';
				$ownDisable = 'checked="checked"';
			}
			
			//set "buttons in profile" radio buttons to initial state
			if ( $wpSettings['buttonsProfile'] ) {
				$btnsProfEnable = 'checked="checked"';
				$btnsProfDisable = '';
			} else {
				$btnsProfEnable = '';
				$btnsProfDisable = 'checked="checked"';
			}	
			
			//set "buttons in posts" radio buttons to initial state
			if ( $wpSettings['buttonsPost'] ) {
				$btnsPostEnable = 'checked="checked"';
				$btnsPostDisable = '';
			} else {
				$btnsPostEnable = '';
				$btnsPostDisable = 'checked="checked"';
			}	
			
			//set "users can switch styles" radio buttons to initial state
			if ( $wpSettings['allowStyleSwitch'] ) {
				$styleSwitchEnable = 'checked="checked"';
				$styleSwitchDisable = '';
			} else {
				$styleSwitchEnable = '';
				$styleSwitchDisable = 'checked="checked"';
			}	
			
			//set "create blog home page" radio buttons to initial state
			if ( $wpSettings['useBlogHome'] ) {
				$blogHomeEnable = 'checked="checked"';
				$blogHomeDisable = '';
			} else {
				$blogHomeEnable = '';
				$blogHomeDisable = 'checked="checked"';
			}

			//set "use CSS" radio buttons to initial state
			if ( $wpSettings['blUseCSS'] ) {
				$useCssEnable = 'checked="checked"';
				$useCssDisable = '';
			} else {
				$useCssEnable = '';
				$useCssDisable = 'checked="checked"';
			}		
			
			// pass strings
			$passVars = array(	
				'L_WP_NO_ADV_JS'  => $wpuAbs->lang('WP_No_JavaScript'),
				'L_WPWIZARD_H1' => $wpuAbs->lang('WP_Wizard_H1'),
				'L_WPWIZARD_STEP' => sprintf($wpuAbs->lang('WP_Wizard_Step'), 4, $numWizardSteps),
				'L_WPWIZARD_S4_TITLE' => $wpuAbs->lang('WP_Wizard_Step4_Title'),
				'L_WPWIZARD_S4_EXPLAIN' => $wpuAbs->lang('WP_Wizard_Step4_Explain'),
				'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
				'L_WPOWNBLOGS_TITLE' => $wpuAbs->lang('WPWiz_OwnBlogs_Title'),
				'L_WPOWNBLOGS_EXPLAIN1' => $wpuAbs->lang('WPWiz_OwnBlogs_Explain1'),
				'S_WPOWNBLOGS_ENABLE' => $ownEnable,
				'S_WPOWNBLOGS_DISABLE' => $ownDisable,
				'L_WPBTNSPROF_TITLE' => $wpuAbs->lang('WPWiz_BtnsProf_Title'),
				'L_WPBTNSPROF_EXPLAIN' => $wpuAbs->lang('WPWiz_BtnsProf_Explain'),
				'S_WPBTNSPROF_ENABLE' => $btnsProfEnable,
				'S_WPBTNSPROF_DISABLE' => $btnsProfDisable,
				'L_WPBTNSPOST_TITLE' => $wpuAbs->lang('WPWiz_BtnsPost_Title'),
				'L_WPBTNSPOST_EXPLAIN' => $wpuAbs->lang('WPWiz_BtnsPost_Explain'),
				'S_WPBTNSPOST_ENABLE' => $btnsPostEnable,
				'S_WPBTNSPOST_DISABLE' => $btnsPostDisable,
				'L_WPSTYLESWITCH_TITLE' => $wpuAbs->lang('WPWiz_StyleSwitch_Title'),
				'L_WPSTYLESWITCH_EXPLAIN1' => $wpuAbs->lang('WPWiz_StyleSwitch_Explain1'),
				'L_WPSTYLESWITCH_EXPLAIN2' => $wpuAbs->lang('WPWiz_StyleSwitch_Explain12'),
				'S_WPSTYLESWITCH_ENABLE' => $styleSwitchEnable,
				'S_WPSTYLESWITCH_DISABLE' => $styleSwitchDisable,
				'L_WPOWNBLOGS_OPTTITLE' => $wpuAbs->lang('WP_OwnBlogs_OptTitle'),
				'L_WPBLOGLIST_OPTTITLE' => $wpuAbs->lang('WP_Bloglist_OptTitle'),			
				'L_WPBLOGLIST_HEAD' => $wpuAbs->lang('WPWiz_Bloglist_Head'),
				'L_WPBLOGLIST_TITLE' => $wpuAbs->lang('WPWiz_Bloglist_Title'),
				'L_WPBLOGLIST_EXPLAIN1' => $wpuAbs->lang('WPWiz_Bloglist_Explain'),
				'L_WPBLOGLIST_EXPLAIN2' => $wpuAbs->lang('WPWiz_Bloglist_Explain2'),
				'S_BLOGHOME_ENABLE' => $blogHomeEnable,
				'S_BLOGHOME_DISABLE' => $blogHomeDisable,
				
				'L_WPBLOGTITLE_TITLE' => $wpuAbs->lang('WPWiz_BlogListHead_Title'),
				'L_WPBLOGTITLE_EXPLAIN' => $wpuAbs->lang('WPWiz_BlogListHead_Explain'),
				'S_WPBLOGTITLE' => $wpSettings['blogListHead'],
				
				'L_WPBLOGINTRO_TITLE' => $wpuAbs->lang('WPWiz_BlogIntro_Title'),
				'L_WPBLOGINTRO_EXPLAIN1' => $wpuAbs->lang('WPWiz_BlogIntro_Explain'),
				'L_WPBLOGINTRO_EXPLAIN2' => $wpuAbs->lang('WPWiz_BlogIntro_Explain2'),
				'S_WPBLOGINTRO' => $wpSettings['blogIntro'],
				
				'L_WPBLOGSPERPAGE_TITLE' => $wpuAbs->lang('WPWiz_NumBlogList_Title'),
				'L_WPBLOGSPERPAGE_EXPLAIN' => $wpuAbs->lang('WPWiz_NumBlogList_Explain'),
				'S_WPBLOGSPERPAGE' => $wpSettings['blogsPerPage'],
				
				'L_WPBL_CSS_TITLE' => $wpuAbs->lang('WPWiz_blCSS_Title'),
				'L_WPUBL_CSS_EXPLAIN1' => $wpuAbs->lang('WPWiz_blCSS_Explain'),
				'L_WPUBL_CSS_EXPLAIN2' => $wpuAbs->lang('WPWiz_blCSS_Explain2'),
				'S_WPUBL_CSS_ENABLE' => $useCssEnable,					
				'S_WPUBL_CSS_DISABLE' => $useCssDisable,					
				
				'L_WP_YES' => $wpuAbs->lang('WP_Yes'),
				'L_WP_NO' => $wpuAbs->lang('WP_No'),
				'L_WPBACK' => sprintf($wpuAbs->lang('WP_Wizard_Back'), 3),
				'L_WPNEXT' => sprintf($wpuAbs->lang('WP_Wizard_Next'), 5)
			);
			
			// set the page section to show
			$passBlockVars = array(
			'switch_wizard_page4' => array(),
			);

			// Should we show an error box at the top of the page?
			if ( !empty($wizShowError) ) {
				$passBlockVars['switch_wizard_page4.switch_wp_error'] = array(
					'WP_ERROR_MSG' => $wizErrorMsg
				);
			}
			
			//show the page
			$this->showPage($passVars, $passBlockVars);
		} else {
			//Login is not integrated, we can skip past this wizard page.
			if ( $dir == 'FWD' ) {
				$this->step5_show_frontend();
			} else {
				$this->step3_show();
			}
		}
	}
	
	//
	//	STEP 5 -- WP-UNITED CONNECTION
	//	-----------------------------------------------
	//	Auto-installs a plugin to WordPress that handles elements of the integration from the WP side transparently.
	//	This (invisible) plugin, AKA the "WP-United Connection", resides in our phpBB fileset - so users don't have to install anything.
	//
	function step5_show_frontend() {
		global $wpuAbs, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx;
		
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$wpuAbs->lang('WP_Wizard_H1');
		
		// pass strings
		$passVars = array(	
			'L_WP_NO_ADV_JS'  => $wpuAbs->lang('WP_No_JavaScript'),
			'L_WPWIZARD_H1' => $wpuAbs->lang('WP_Wizard_H1'),
			'L_WPWIZARD_STEP' => sprintf($wpuAbs->lang('WP_Wizard_Step'), 5, $numWizardSteps),
			'S_WPAJAX_ACTION' => str_replace ('&amp;', '&', $uri),
			'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'L_WPBACK' => sprintf($wpuAbs->lang('WP_Wizard_Back'), 4),
			'L_WPNEXT' => sprintf($wpuAbs->lang('WP_Wizard_Next'), 6)
		);

		// set the page section to show
		$passBlockVars = array(
			'switch_wizard_page5' => array(),
		);
		
		
		//show the page
		$this->showPage($passVars, $passBlockVars);
	}
	
	function step5_backend() {
		//Read settings from db and make global
		global $wpuAbs, $wpSettings, $wpu_debug;
		$wpu_debug = '';
		$wpSettings = get_integration_settings();

		$connError = $this->install_wpuConnection();
		
		$xmlData['info'] = ' ';

		if (empty($connError)) {
			$data['installLevel'] = 10;
			//set the version to the db at this stage
			$data['wpuVersion'] = $wpuAbs->lang('WPU_Default_Version');
			if ( !(set_integration_settings($data)) ) {
				$xmlData['result'] = "NOSAVE";
				$xmlData['message'] = $wpuAbs->lang('WPWIZ_DB_ERR_CONN_OK');
			} else {
				$xmlData['result'] = "OK";
				$xmlData['message'] = $wpuAbs->lang('WP-United Installed Successfully');
			}
		} else {
				$xmlData['result'] = "FAIL";
				$xmlData['message'] = "<![CDATA[$wpu_debug]]>";
		}
		
		$this->send_ajax($xmlData, 'installconn');
		
	}
	
	function step5_errorhandler() {
	
	}
		
		
		
		
	//
	//	STEP 6 -- FINISH PAGE
	//	---------------------------------
	//	Congratulates the user for completing the wizard, and encourages them to donate.
	//
	function step6_show() {
		global $wpuAbs, $numWizardSteps, $phpEx;	
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$wpuAbs->lang('WP_Wizard_H1');
		
		//get integration package settings
		$wpSettings = $this->get_settings_set_defaults();
		
		
		// pass strings
		$passVars = array(
			'L_WPWIZARD_H1' => $wpuAbs->lang('WP_Wizard_H1'),
			'L_WPWIZARD_STEP' => sprintf($wpuAbs->lang('WP_Wizard_Step'), 6, $numWizardSteps),
			'L_WPWIZARD_COMPLETE_TITLE' => $wpuAbs->lang('WP_Wizard_Complete_Title'),
			'L_WPWIZARD_COMPLETE_EXPLAIN0' => $wpuAbs->lang('WP_Wizard_Complete_Explain0'),
			'L_WPWIZARD_COMPLETE_EXPLAIN1' => sprintf($wpuAbs->lang('WP_Wizard_Complete_Explain1'), '<a href="' . $this->add_trailing_slash($wpSettings['wpUri']) . 'wp-admin' . '" target="_blank">', '</a>'),
			'L_WPWIZARD_COMPLETE_EXPLAIN2' => $wpuAbs->lang('WP_Wizard_Complete_Explain2'),
			'L_WPWIZARD_COMPLETE_EXPLAIN3' => $wpuAbs->lang('WP_Wizard_Complete_Explain3'),
			'L_WPWIZARD_COMPLETE_EXPLAIN4' => sprintf($wpuAbs->lang('WP_Config_GoBack'), "<a href=\"" . append_sid("index.$phpEx?i=wp_united") . "\">", "</a>") ,
			'S_WPWIZ_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),
			'L_WPBACK' => sprintf($wpuAbs->lang('WP_Wizard_Back'), 5),
		);

		// set the page section to show
		$passBlockVars = array(
			'switch_wizard_page6' => array(),
		);

		//show the page
		$this->showPage($passVars, $passBlockVars);	
		
	}				


	//
	//	STEP 1B -- PROCESS WORDPRESS INSTALLATION SETTINGS
	//	------------------------------------------------------------------------------------
	//	Saves the URL, file path & database sharing settings to the config table.
	//	Performs some, albeit limited validation.
	//
	function step1b_process() {
		global $wpuAbs, $phpEx, $wizShowError, $wizErrorMsg;
		// Process form data
		$saveSettings = TRUE;
		$data['wpUri'] = $this->clean_path(request_var('txt_Uri', ''));
		$data['wpPath'] = $this->clean_path(request_var('txt_Path', ''));
		$data['blogsUri'] = $this->clean_path(request_var('txt_BlogsUri', ''));
		
		if (($data['wpUri'] == "") || (strlen($data['wpUri']) < 3)) {
			$wizShowError = TRUE;
			$wizErrorMsg = $wpuAbs->lang('wizErr_invalid_URL');
			$saveSettings = FALSE;
		} else {
			//Add http:// and trailing slash if needed
			$data['wpUri'] = $this->add_trailing_slash($this->add_http($data['wpUri']));
		}
		if (($data['wpPath'] == "") || (strlen($data['wpPath']) < 3)) {
			$wizShowError = TRUE;
			$wizErrorMsg .= $wpuAbs->lang('wizErr_invalid_Path'); 
			$saveSettings = FALSE;
		} else {
			$data['wpPath'] = $this->add_trailing_slash($data['wpPath']);
		}

		if (($data['blogsUri'] == "") || (strlen($data['blogsUri']) < 3)) {
			$wizShowError = TRUE;
			$wizErrorMsg = $wpuAbs->lang('wizErr_invalid_Blog_URL');
			$saveSettings = FALSE;
		} else {
			//Add http:// if needed
			$data['blogsUri'] = $this->add_http($data['blogsUri']);
		}
		
		if ($saveSettings) {
			//Save settings to db
			if ( !(set_integration_settings($data)) ) {
				$wizShowError = TRUE;
				$wizErrorMsg .= "The settings could not be saved in the database. ";
			} else {
				$this->step2_show();
			}
		} 
		
		if ( !empty($wizShowError) ) {
			$wizErrorMsg .= "Your settings were not saved.";
			$this->step1b_show();
		}

	}
	
	
	//
	//	STEP 2 -- PROCESS INTEGRATION SETTINGS
	//	---------------------------------------------------------------
	//	Saves the login integration settings to the config table.
	//
	function step2_process() {
		global $wpuAbs, $phpEx, $wizShowError, $wizErrorMsg;
		// Process form data
		$radWpLogin = (int) request_var('rad_Login', '');

		if ( $radWpLogin ) {
		
			$data['xposting'] = (int) request_var('rad_xPost', '');
			$data['xpostautolink'] = (int) request_var('rad_xpost_al', '');
		
			$data['integrateLogin'] = 1;
			if ($wpuAbs->ver == 'PHPBB2') {				
				$permsList = $value = str_replace("\'", "''", request_var('rolesOutput', ''));
				//process permissions list
				if ( (!(strstr($permsList, '[--**WPU-ROLELIST**--]' === FALSE))) && (!(strstr($permsList, '[--**--]' === FALSE))) ) {
					$data['permList'] = str_replace('[--**--]', '', str_replace('[--**WPU-ROLELIST**--]', '', $permsList)); 
				} else {
					//malformed
					$wizShowError = TRUE;
					$wizErrorMsg .= "The permissions were not selected correctly. Please ensure you have JavaScript enabled in your browser.";
				}
			} else {

				// Add phpBB3 modules
				if ($tab = $this->module_exists('ACP_WP_UNITED')) {
					if ($cat = $this->module_exists('ACP_WPU_CATMANAGE', $tab)) {
						$modData = array(
							'module_basename'	=> 'wp_united',
							'module_mode'		=> 'usermap', 
							'module_auth'		=> 'acl_a_wpu_manage', 
							'module_enabled'	=> 1,
							'module_display'	=> 1, 
							'parent_id'			=> $cat,
							'module_langname'	=> 'ACP_WPU_USERMAP', 
							'module_class'		=>'acp'
						);
						$this->add_acp_module($modData);
						$modData = array(
							'module_basename'	=> 'wp_united',
							'module_mode'		=> 'permissions', 
							'module_auth'		=> 'acl_a_wpu_manage', 
							'module_enabled'	=> 1,
							'module_display'	=> 1, 
							'parent_id'			=> $cat,
							'module_langname'	=> 'ACP_WPU_PERMISSIONS', 
							'module_class'		=>'acp'
						);
						$this->add_acp_module($modData);						
					}
				}
			}

		} else { 
			if ($wpuAbs->ver == 'PHPBB3') {
				// remove modules
				if ($tab = $this->module_exists('ACP_WP_UNITED')) {
					if ($cat = $this->module_exists('ACP_WPU_CATMANAGE', $tab)) {
						$modData = array(
							'module_basename'	=> 'wp_united',
							'module_mode'		=> 'usermap', 
							'module_auth'		=> 'acl_a_wpu_manage', 
							'module_enabled'	=> 0,
							'module_display'	=> 0, 
							'parent_id'			=> $cat,
							'module_langname'	=> 'ACP_WPU_USERMAP', 
							'module_class'		=>'acp'
						);
						$this->add_acp_module($modData);
						$modData = array(
							'module_basename'	=> 'wp_united',
							'module_mode'		=> 'permissions', 
							'module_auth'		=> 'acl_a_wpu_manage', 
							'module_enabled'	=> 0,
							'module_display'	=> 0, 
							'parent_id'			=> $cat,
							'module_langname'	=> 'ACP_WPU_PERMISSIONS', 
							'module_class'		=>'acp'
						);
						$this->add_acp_module($modData);						
						
						
					}
				}	
			}
			$data['xposting'] = 0;
			$data['xpostautolink'] = 0;
			$data['integrateLogin'] = 0;
			$data['permList'] = '';
			
			$data['usersOwnBlogs'] = 0;
			$data['buttonsProfile'] = 0;
			$data['buttonsPost'] =  0;
			$data['allowStyleSwitch'] = 0;
			
			$data['blUseCSS'] =  0;
			$data['useBlogHome'] = 0;

		}	
		
		//Save settings to db
		if ( !(set_integration_settings($data)) ) {
			$wizShowError = TRUE;
			$wizErrorMsg .= "The settings could not be saved in the database. ";
		} 	 
		
		if ( !empty($wizShowError) ) {
			$wizErrorMsg .= "Your settings were not saved.";
			$this->step2_show();
		} else {
			$this->step3_show();
		}
	}


	//
	//	STEP 3 -- PROCESS DISPLAY & BEHAVIOUR SETTINGS
	//	----------------------------------------------------------------------------
	//	Saves the display settings to the config table.
	//
	function step3_process() {

		global $wpuAbs, $phpEx, $wizShowError, $wizErrorMsg;
		
		$data['dtdSwitch'] = (int) request_var('rad_DTD', 0);
		$data['showHdrFtr'] = request_var('rad_Inside', '');
		$data['cssMagic'] = (int) request_var('rad_cssm', 1);
		$data['templateVoodoo'] = (int) request_var('rad_tv', 1);
		$data['fixHeader'] = (int) request_var('rad_fixhdr', 1);
		$data['wpSimpleHdr'] = (int) request_var('rad_Simple', 0);
		$data['wpPageName'] = request_var('txt_wpPage', '');
		$data['cssFirst'] = (int) request_var('rad_pFirst', 0);
		$data['phpbbCensor'] = (int) request_var('rad_Censor', 0);
		$data['pluginFixes'] = (int) request_var('rad_Plugins', 0);
		$data['phpbbSmilies'] = (int) request_var('rad_Smilies', 0);
		$data['mustLogin'] = (int) request_var('rad_Private', 0);
		$data['charEncoding'] =  request_var('rad_CharEnc', '');
		

		$padT = request_var('txt_padT', 20);
		$padR = request_var('txt_padR', 20);
		$padB = request_var('txt_padB', 20);
		$padL = request_var('txt_padL', 20);
		if ( ($padT == '') && ($padR == '') && ($padB == '') && ($padL == '') ) {
			$data['phpbbPadding'] = 'NOT_SET';
		} else {
			$data['phpbbPadding'] = (int)$padT . '-' . (int)$padR . '-' . (int)$padB . '-' . (int)$padL;
		}
		
		switch ( $data['charEncoding'] ) {
		case 'W':
			$data['charEncoding'] = 'MATCH_WP';
			break;
		case 'P';
			$data['charEncoding'] = 'MATCH_PHPBB';
			break;
		case 'N';
			default;
			$data['charEncoding'] = 'NO_CHANGE';
			break;
		}	

		switch ( $data['showHdrFtr'] ) {
		case 'FWD':
			$data['showHdrFtr'] = 'FWD';
			break;
		case 'REV';
			$data['showHdrFtr'] = 'REV';
			break;
		case 'N';
			default;
			$data['showHdrFtr'] = 'NONE';
			$data['cssMagic'] = 0;
			$data['templateVoodoo'] = 0;
			break;
		}
		if(!$data['cssMagic']) {
			$data['templateVoodoo'] = 0;
		}

		switch ( $data['cssFirst'] ) {
		case '1':
			$data['cssFirst'] = 'P';
			break;
		case '2';
			$data['cssFirst'] = 'W';
			break;
		case '0';
			default;
			$data['cssFirst'] = 'NONE';
			break;
		}		
		
		//Save settings to db
		if ( !(set_integration_settings($data)) ) {
			$wizShowError = TRUE;
			$wizErrorMsg .= "The settings could not be saved in the database. ";
		} else {
			$this->step4_show('FWD');
		}
		
		
		if ( !empty($wizShowError) ) {
			$wizErrorMsg .= "Your settings were not saved.";
			$this->step3_show();
		}

	}


	//
	//	STEP 4 -- PROCESS BLOG SETTINGS
	//	-------------------------------------------------------
	//	Saves the Blog settings to the config table.
	//
	function step4_process() {

		global $wpuAbs, $phpEx, $wizShowError, $wizErrorMsg;
		
		$ownBlogs = (int) request_var('rad_ownBlogs', 0);
		$btnsProf = (int) request_var('rad_Prof', 0);
		$btnsPost = (int) request_var('rad_Post', 0);
		$swStyles = (int) request_var('rad_Styles', 0);
		
		$blogsListing = (int) request_var('rad_useList', 0);
		$data['blogListHead'] = request_var('txt_ListTitle', '');
		$data['blogIntro'] = request_var('txt_Intro', '');
		$data['blogsPerPage'] = (int) request_var('txt_BlogsPerPg', 0);
		$useCSS = (int) request_var('rad_useCSS', 0);
		
		$data['usersOwnBlogs'] = ( $ownBlogs == 1 ) ? 1 : 0;

		$data['blogsPerPage'] = ( $data['blogsPerPage'] < 0 ) ? 1 : $data['blogsPerPage'];

		
		if ( !empty($data['usersOwnBlogs']) ) {
			$data['blUseCSS'] = ( $useCSS == 1 ) ? 1 : 0;
			$data['useBlogHome'] = ( $blogsListing == 1 ) ? 1 : 0;
			$data['buttonsProfile'] = ( $btnsProf == 1 ) ? 1 : 0;
			$data['buttonsPost'] = ( $btnsPost == 1 ) ? 1 : 0;
			$data['allowStyleSwitch'] = ( $swStyles == 1 ) ? 1 : 0;
		} else {
			$data['blUseCSS'] =  0;
			$data['useBlogHome'] = 0;
			$data['buttonsProfile'] = 0;
			$data['buttonsPost'] =  0;
			$data['allowStyleSwitch'] = 0;
		}
		
		//Save settings to db
		if ( !(set_integration_settings($data)) ) {
			$wizShowError = TRUE;
			$wizErrorMsg .= "The settings could not be saved in the database. ";
			$wizErrorMsg .= "Your settings were not saved.";
			$this->step4_show('FWD');
		} else {
			$this->step5_show_frontend();
		}
	} 





	// **********************************************************************
	//***												***
	//***				PROCESS SETTINGS DATA			 	***
	//***												***
	//***********************************************************************
	//
	//	Processes and validates the data provided by settings page.
	//

	function settings_process() {
		
		global $wpuAbs, $phpEx, $wpu_debug;
		$this->page_title = 'ACP_WPU_INDEX_TITLE';
		$this->tpl_name = 'acp_wp_united';
		
		// Process form data
		$allOK = FALSE; $saveSettings = FALSE; $autoDetect = TRUE;
		$data['wpUri'] = $this->clean_path(request_var('txt_Uri', ''));
		$data['wpPath'] = $this->clean_path(request_var('txt_Path', ''));
		$data['blogsUri'] = $this->clean_path(request_var('txt_BlogsUri', ''));	
		$data['dtdSwitch'] = (int) request_var('rad_DTD', 0);
		$data['showHdrFtr'] = request_var('rad_Inside', '');
		$data['cssMagic'] = (int) request_var('rad_cssm', 1);
		$data['templateVoodoo'] = (int) request_var('rad_tv', 1);
		$data['fixHeader'] = (int) request_var('rad_fixhdr', 1);
		$data['wpSimpleHdr'] = (int) request_var('rad_Simple', 1);
		$data['cssFirst'] = (int) request_var('rad_pFirst', 1);	
		$data['phpbbCensor'] = (int) request_var('rad_Censor', 1);
		$data['pluginFixes'] = (int) request_var('rad_Plugins', 0);
		$data['phpbbSmilies'] = (int) request_var('rad_Smilies', 1);
		$data['mustLogin'] = (int) request_var('rad_Private', 0);
		$data['charEncoding'] =  request_var('rad_CharEnc', '');
		$permsList = $value = str_replace("\'", "''", (request_var('rolesOutput', '')));
		
		$data['wpPageName'] = request_var('txt_wpPage', 'page.php');
		$padT = (int) request_var('txt_padT', 20);
		$padR = (int) request_var('txt_padR', 20);
		$padB = (int) request_var('txt_padB', 20);
		$padL = (int) request_var('txt_padL', 20);
		if ( ($padT == '') && ($padR == '') && ($padB == '') && ($padL == '') ) {
			$data['phpbbPadding'] = 'NOT_SET';
		} else {
			$data['phpbbPadding'] = (int)$padT . '-' . (int)$padR . '-' . (int)$padB . '-' . (int)$padL;
		}

		switch ( $data['showHdrFtr'] ) {
		case 'FWD':
			$data['showHdrFtr'] = 'FWD';
			break;
		case 'REV';
			$data['showHdrFtr'] = 'REV';
			break;
		case 'N';
			default;
			$data['showHdrFtr'] = 'NONE';
			$data['cssMagic'] = 0;
			$data['templateVoodoo'] = 0;
			break;
		}
		if(!$data['cssMagic']) {
			$data['templateVoodoo'] = 0;
		}

		switch ( $data['cssFirst'] ) {
		case '1':
			$data['cssFirst'] = 'P';
			break;
		case '2';
			$data['cssFirst'] = 'W';
			break;
		case '0';
			default;
			$data['cssFirst'] = 'NONE';
			break;
		}			
		switch ( $data['charEncoding'] ) {
		case 'W':
			$data['charEncoding'] = 'MATCH_WP';
			break;
		case 'P';
			$data['charEncoding'] = 'MATCH_PHPBB';
			break;
		case 'N';
			default;
			$data['charEncoding'] = 'NO_CHANGE';
			break;
		}	

		//set the version to the db at this stage
		if ( !isset($data['wpuVersion']) ) {
			$data['wpuVersion'] = $wpuAbs->lang('WPU_Default_Version');
		} else {
			$data['wpuVersion'] = ($data['wpuVersion'] == $wpuAbs->lang('WPU_Not_Installed')) ? $wpuAbs->lang('WPU_Default_Version') : $data['wpuVersion'];
		}
		
		
		
		$radWpLogin = (int) request_var('rad_Login', 0);
		
		$ownBlogs = (int) request_var('rad_ownBlogs', 0);
		$btnsProf = (int) request_var('rad_Prof', 0);
		$btnsPost = (int) request_var('rad_Post', 0);
		$swStyles = (int) request_var('rad_Styles', 0);

		$blogsListing = (int) request_var('rad_useList', 0);
		$data['blogListHead'] = request_var('txt_ListTitle', '');
		$data['blogIntro'] = request_var('txt_Intro', '');
		$data['blogsPerPage'] = (int) request_var('txt_BlogsPerPg', 10);
		$useCSS = (int) request_var('rad_useCSS', 1);	
		
		$data['blogsPerPage'] = ( $data['blogsPerPage'] < 0 ) ? 1 : $data['blogsPerPage'];
		
		if ( $radWpLogin ) {
			$data['integrateLogin'] = 1;
			$data['xposting'] = (int) request_var('rad_xPost', '');
			$data['xpostautolink'] = (int) request_var('rad_xpost_al', '');
			
			if ($wpuAbs->ver == 'PHPBB2') {
				//process permissions list
				if ( (!(strstr($permsList, '[--**WPU-ROLELIST**--]' === FALSE))) && (!(strstr($permsList, '[--**--]' === FALSE))) ) {
					$data['permList'] = str_replace('[--**--]', '', str_replace('[--**WPU-ROLELIST**--]', '', $permsList)); 
				} else {
					//malformed
					$procError = TRUE;
					$msgError .= "The permissions were not selected correctly. Please ensure you have JavaScript enabled in your browser.";
				}
			} else {
				// Add phpBB3 modules
				if ($tab = $this->module_exists('ACP_WP_UNITED')) {
					if ($cat = $this->module_exists('ACP_WPU_CATMANAGE', $tab)) {
						$modData = array(
							'module_basename'	=> 'wp_united',
							'module_mode'		=> 'usermap', 
							'module_auth'		=> 'acl_a_wpu_manage', 
							'module_enabled'	=> 1,
							'module_display'	=> 1, 
							'parent_id'			=> $cat,
							'module_langname'	=> 'ACP_WPU_USERMAP', 
							'module_class'		=>'acp'
						);
						$this->add_acp_module($modData);
						$modData = array(
							'module_basename'	=> 'wp_united',
							'module_mode'		=> 'permissions', 
							'module_auth'		=> 'acl_a_wpu_manage', 
							'module_enabled'	=> 1,
							'module_display'	=> 1, 
							'parent_id'			=> $cat,
							'module_langname'	=> 'ACP_WPU_PERMISSIONS', 
							'module_class'		=>'acp'
						);
						$this->add_acp_module($modData);							
						
					}
				}
			}
			
			$data['usersOwnBlogs'] = ( $ownBlogs == 1 ) ? 1 : 0;
			if ( !empty($data['usersOwnBlogs']) ) {
				$data['blUseCSS'] = ( $useCSS == 1 ) ? 1 : 0;
				$data['useBlogHome'] = ( $blogsListing == 1 ) ? 1 : 0;
				$data['buttonsProfile'] = ( $btnsProf == 1 ) ? 1 : 0;
				$data['buttonsPost'] = ( $btnsPost == 1 ) ? 1 : 0;
				$data['allowStyleSwitch'] = ( $swStyles == 1 ) ? 1 : 0;
			} else {
				$data['blUseCSS'] =  0;
				$data['useBlogHome'] = 0;
				$data['buttonsProfile'] = 0;
				$data['buttonsPost'] =  0;
				$data['allowStyleSwitch'] = 0;
			}
		} else {
			$data['blUseCSS'] =  0;
			$data['useBlogHome'] = 0;
			$data['integrateLogin'] = 0;
			$data['permList'] = '';
			$data['usersOwnBlogs'] = 0;
			$data['buttonsProfile'] = 0;
			$data['buttonsPost'] =  0;
			$data['allowStyleSwitch'] = 0;
			$data['xposting'] = 0;
			$data['xpostautolink'] = 0;

			if ($wpuAbs->ver == 'PHPBB3') {
				// remove module
				if ($tab = $this->module_exists('ACP_WP_UNITED')) {
					if ($cat = $this->module_exists('ACP_WPU_CATMANAGE', $tab)) {
						$modData = array(
							'module_basename'	=> 'wp_united',
							'module_mode'		=> 'usermap', 
							'module_auth'		=> 'acl_a_wpu_manage', 
							'module_enabled'	=> 0,
							'module_display'	=> 0, 
							'parent_id'			=> $cat,
							'module_langname'	=> 'ACP_WPU_USERMAP', 
							'module_class'		=>'acp'
						);
						$this->add_acp_module($modData);
						$modData = array(
							'module_basename'	=> 'wp_united',
							'module_mode'		=> 'permissions', 
							'module_auth'		=> 'acl_a_wpu_manage', 
							'module_enabled'	=> 0,
							'module_display'	=> 0, 
							'parent_id'			=> $cat,
							'module_langname'	=> 'ACP_WPU_PERMISSIONS', 
							'module_class'		=>'acp'
						);
						$this->add_acp_module($modData);							
					}
				}	
			}
		}				
		$procError = FALSE; 
		if (($data['wpUri'] == "") || (strlen($data['wpUri']) < 3)) {
			$procError = TRUE;
			$msgError .= $wpuAbs->lang('WPU_URL_Not_Provided');
		} else {
			// Clean up the URI 
			//Add http:// and trailing slash if needed
			$data['wpUri'] = $this->add_http($data['wpUri']);
			$data['wpUri'] = $this->add_trailing_slash($data['wpUri']);


			//Check the URI is on the same host
			$hostName = "http://".$_SERVER['HTTP_HOST'];
			If (strpos($data['wpUri'], $hostName) === FALSE) {
				$urlCheckColour = "orange";
				$urlCheckResult = $wpuAbs->lang('WPU_URL_Diff_Host');
				$autoDetect = FALSE;
			} else {
				$urlCheckColour = "green";
				$urlCheckResult = $wpuAbs->lang('WPU_OK');
			}	
			//test that the URL at that location actually exists

			$uriExists = $this->uri_exists($data['wpUri']);
			if ( $uriExists ){
				$urlExistColour = "green";
				$urlExistResult = $wpuAbs->lang('WPU_OK');
			} else {
				if ($uriExists === FALSE) {
					$urlExistColour = "orange";
					$urlExistResult = $wpuAbs->lang('WPU_URL_No_Exist');
				} else{
					$urlExistColour = "orange";
					$urlExistResult = $wpuAbs->lang('WP_cURL_Not_Avail');
				}
			} 

			if ($data['wpPath'] == "") {
				if (!$autoDetect) {
					$procError = TRUE;
					$msgError .= $wpuAbs->lang('WPU_Cant_Autodet');
				} else {
					$pathDet = $wpuAbs->lang('WPU_Path_Autodet');
					//figure out the filesystem path
					$data['wpPath'] = $this->detect_path_from_uri($data['wpUri']);
				}
			} else {
				$pathDet = $wpuAbs->lang('WPU_PathIs');
			}
			if (!$data['wpPath']) {
				if ($autoDetect) {
					$procError = TRUE;
					$msgError .= $wpuAbs->lang('WPU_Autodet_Error');
				}
			} else {	
				$pathDetColour = "green";
				$pathDetResult = $data['wpPath'];
				
				//Check that location to see if WordPress files exist there
				if ( !$this->wordpress_exists($data['wpPath']) ) {
					$wpExistColour = "orange";
					$wpExistResult = sprintf($wpuAbs->lang['WPU_Pathfind_Warning'], $this->add_trailing_slash($data['wpPath']) . 'wp-settings.php');
					$msgError .= sprintf($wpuAbs->lang['WPU_Pathfind_Warning'] . "<br />", $this->add_trailing_slash($data['wpPath']) . 'wp-settings.php');
				} else {
					$wpExistColour = "green";
					$wpExistResult = $wpuAbs->lang('WPU_OK');
				}
				global $wpSettings;
				$wpSettings = $data; 
				$connError = $this->install_wpuConnection();
				if ( $connError ) {
					$procError = TRUE;
					$msgError .= $wpuAbs->lang('WP_Wizard_Connection_Fail');
					$msgError .= $wpu_debug;
				} else {
					$allOK = TRUE;
					$wpConnColour = "green";
					$wpConnResult = $wpuAbs->lang('WPU_OK');
				}
			}
		}
		
		if (($data['blogsUri'] == "") || (strlen($data['blogsUri']) < 3)) {
			$procError = TRUE;
			$msgError .= $wpuAbs->lang('wizErr_invalid_Blog_URL');
		} else {
			//Add http:// if needed
			$data['blogsUri'] = $this->add_http($data['blogsUri']);
		}
		
		
		
		if ( !$procError ) {
			//THIS MARKS THE END OF THE SETUP. Allow access to application.
			$data['installLevel'] = 10;
			//set the version to the db at this stage
			$data['wpuVersion'] = $wpuAbs->lang('WPU_Default_Version');		
			
			//Save settings to db
			if ( !(set_integration_settings($data)) ) {
				$procError = TRUE;
				$msgError .= $wpuAbs->lang('WP_DBErr_Write');
			}
			
			if ((!$allOK) || ($uriNotFound)) {
				$saveColour = "orange";
				$saveResult = $wpuAbs->lang('WP_Saved_Caution');
			} else {
				$saveColour = "green";
				$saveResult = $wpuAbs->lang('WP_AllOK');
			}
		} 
		
		
		
		if ( $procError ) { 
			if ( empty($msgError) ) {
				$msgError .= $wpuAbs->lang('WP_Errors_NoSave');
			}
			global $wpErrSettings, $errMsg;
			$errMsg = $msgError;
			$wpErrSettings = $data;
			$this->settings_show();
		} else {
			// pass strings
			$passVars = array(	
				'L_WP_TITLE' => $wpuAbs->lang('WP_Title'),
				'L_WPSETTINGS_PROCESS' => $wpuAbs->lang('WPU_Process_Settings'),
				'L_WP_URL' => $wpuAbs->lang('WPU_Checking_URL'),
				'L_WP_URLCOLOUR' => $urlCheckColour,
				'L_WP_URLRESULT' => $urlCheckResult,
				'L_WP_URLEXIST' => $wpuAbs->lang('WPU_Checking_URL'),
				'L_WP_URLEXISTCOLOUR' => $urlExistColour,
				'L_WP_URLEXISTRESULT' => $urlExistResult,
				'L_WP_PATHDET' => $pathDet,
				'L_WP_PATHDETCOLOUR' => $pathDetColour,
				'L_WP_PATHDETRESULT' => $pathDetResult,
				'L_WP_WPEXIST' => $wpuAbs->lang('WPU_Checking_WPExists'),
				'L_WP_WPEXISTCOLOUR' => $wpExistColour,
				'L_WP_WPEXISTRESULT' => $wpExistResult,
				'L_WP_WPCONN' => $wpuAbs->lang('WPU_Conn_Installing'),
				'L_WP_WPCONNCOLOUR' => $wpConnColour,
				'L_WP_WPCONNRESULT' => $wpConnResult,
				'L_WP_WPSAVECOLOR' => $saveColour,
				'L_WP_WPSAVERESULT' => $saveResult,
				'L_WPSETTINGS_RESULTMSG' => sprintf($wpuAbs->lang('WP_Config_GoBack'), "<a href=\"" . append_sid("index.$phpEx?i=wp_united") . "\">", "</a>") ,
			);
			

			// set the page section to show
			$passBlockVars = array(
				'switch_settings_process_page' => array(),
			);
			$this->showPage($passVars, $passBlockVars);
			
		}
	}
	// **********************************************************************
	//***																	***
	//***				DONATE PAGE				 	***
	//***																	***
	//***********************************************************************
	//
	//	Temporary - right now it's a copy of the main page link, we'll add a donation bocx in the future.
	//
	function donate_show() {
		global $wpuAbs, $phpEx, $ignorePrompt;
		$this->page_title = 'ACP_WPU_INDEX_TITLE';
		$this->tpl_name = 'acp_wp_united';


			// set the page section to show
		$passBlockVars = array(
		'switch_donate' => array(),
		);
		
	
		// pass strings	
		$passVars = array(
			'L_WPMAIN_TITLE' => $wpuAbs->lang('WP_Main_Title'),
			'L_WPMAIN_INTRO' => $wpuAbs->lang('WP_Main_Intro'),
			'L_WPMAIN_INTROADD' => $introAdd,
			'L_PAYPAL_TITLE' => $wpuAbs->lang('WP_Support'),
			'L_PAYPAL_EXPLAIN' => $wpuAbs->lang('WP_Support_Explain'),
		);

		//show the page
		$this->showPage($passVars, $passBlockVars);

	}
	
	// **********************************************************************
	//***																	***
	//***				UNINSTALL WP-UNITED				 	***
	//***																	***
	//***********************************************************************
	//
	//	Resets WP-United back to freshly installed state
	//
	function uninstall_show() {
		global $phpbb_root_path, $phpEx, $wpuAbs, $phpEx, $cache, $auth, $db, $wpSettings;
		$this->page_title = 'ACP_WPU_INDEX_TITLE';
		$this->tpl_name = 'acp_wp_united';
		
		$wpSettings = get_integration_settings();
		
		$do_uninstall = request_var('uninstallaction', '');
			
		// pass strings	
		$passVars = array(
			'L_WPMAIN_TITLE' => $wpuAbs->lang('WP_Main_Title'),
			'L_UNINSTALL_TITLE' => $wpuAbs->lang('WP_Uninstall'),
			'L_WP_UNINSTALL' => $wpuAbs->lang('WP_Uninstall_Button'),
			'L_UNINSTALL_EXPLAIN' => $wpuAbs->lang('WP_Uninstall_Explain'),
			'S_WPWIZ_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),			
		);
		
		if ($do_uninstall == $wpuAbs->lang('WP_Uninstall_Button')) {	
			if (confirm_box(true)) {		
				
				$wp_id_list = array();
				$sql = " SELECT user_wpuint_id
								FROM " . USERS_TABLE . "
								WHERE user_wpuint_id > 0";
				if ($result = $db->sql_query($sql)) {
					$wp_id_list =  $db->sql_fetchrow($result);
				}
				$db->sql_freeresult($result);			

				if (count($wp_id_list)) {				
					//Set up the WordPress Integration
					global $wpUtdInt, $wpuCache;
					require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);
					$wpuCache = WPU_Cache::getInstance();

					require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
					$wpUtdInt = WPU_Integration::getInstance();
					$connError = FALSE;
					if ($wpUtdInt->can_connect_to_wp()) {
						//Enter Integration
						$wpUtdInt->enter_wp_integration();
						eval($wpUtdInt->exec());  
						
						// reset the Blog Uri
						if (!empty($wpSettings['wpUri'])) {
							update_option('home', $wpSettings['wpUri']);
						}
						// Delete blogs home page, if set
						$post_ID = get_option('wpu_set_frontpage');
						if ( !empty($post_ID) ) {
							update_option('wpu_set_frontpage', '');
							update_option('page_on_front', '');
							update_option('posts_on_front', ''); 
							update_option('show_on_front', 'posts');
							wp_delete_post($post_ID);
						}
						//delete the options we set
						delete_option('wputd_connection');
						delete_option('wpu_set_frontpage');
						
						// Remove the WP-United Connection
						$current = get_settings('active_plugins'); 
						$arrPlugins = array();
						if ( is_array($current) ) {
							foreach ($current as $wpPlugin) {
								$isWPUConn = strpos($wpPlugin, 'wpu-plugin');
								if ( $isWPUConn === FALSE ) {
									$arrPlugins[] = $wpPlugin;
								}
							}
							sort($arrPlugins);
							update_option('active_plugins', $arrPlugins);							
						}
						// delete all WP-United user-specific settings
						global $wpdb;
						$wpdb->query("DELETE FROM " . $wpdb->usermeta . " 
									WHERE meta_key IN ('phpbb_userid', 'phpbb_userLogin', 'WPU_MyTemplate', 'WPU_MyStylesheet', 'blog_title', 'blog_tagline', 'wpu_last_post', 'wpu_allowavatar', 'wpu_avatar_type', 'wpu_avatar', 'wpu_avatar_width', 'wpu_avatar_height', 'wpu_my_cats')");
						
						$wpUtdInt->exit_wp_integration();
						$wpUtdInt = null; unset ($wpUtdInt);
					}
				}				

				//drop mappng data
				if  (array_key_exists('user_wpuint_id', $wpuAbs->userdata()) ) {
 					$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
								DROP user_wpuint_id';
				}
				$db->sql_query($sql);
				if  (array_key_exists('user_wpublog_id', $wpuAbs->userdata()) ) {
 					$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
								DROP user_wpublog_id';
				}				
				$db->sql_query($sql);
				
				//delete modules
				$errors = $this->remove_modules();

				$killPerms = array('f_wpu_xpost', 'u_wpu_subscriber','u_wpu_contributor','u_wpu_author','m_wpu_editor','a_wpu_administrator', 'a_wpu_manage');

				$perm_ids = $this->get_acl_option_ids($killPerms);

				//unassign permissions from roles
				$sql = 	"DELETE FROM " . ACL_ROLES_DATA_TABLE . "
								WHERE " . $db->sql_in_set('auth_option_id', $perm_ids);
				$db->sql_query($sql);

				//unassign permissions from groups
				$sql = 	"DELETE FROM " . ACL_GROUPS_TABLE . "
								WHERE " . $db->sql_in_set('auth_option_id', $perm_ids);
				$db->sql_query($sql);

				//unassign permissions from users
				$sql = 	"DELETE FROM " . ACL_USERS_TABLE . "
								WHERE " . $db->sql_in_set('auth_option_id', $perm_ids);
				$db->sql_query($sql);

				// remove permissions
				$sql = 	"DELETE FROM " . ACL_OPTIONS_TABLE . "
								WHERE " . $db->sql_in_set('auth_option', $killPerms);
				$db->sql_query($sql);
				
				$db->sql_freeresult();
				$cache->destroy('acl_options');
				$auth->acl_clear_prefetch();				
		
				//now reset settings
				clear_integration_settings();				
				$cache->destroy('_modules_');
				$cache->destroy('_sql_', MODULES_TABLE);
				$cache->purge();	
				add_log('admin', 'WP_UNINSTALLED', $wpuAbs->lang('WP_Uninstall_Log'));				
				redirect(append_sid("index.$phpEx"));
			} else {
				confirm_box(false,$wpuAbs->lang('WP_Uninstall_Confirm'), build_hidden_fields(array(
						'i'			=> 'wp_united',
						'mode'		=> 'uninstall',
						'uninstallaction' => $wpuAbs->lang('WP_Uninstall_Button'),
					)));
			}
		}
		
		// set the page section to show
		$passBlockVars = array(
		'switch_uninstall' => array(),
		);

		//show the page
		$this->showPage($passVars, $passBlockVars);		
	}
	// **********************************************************************
	//***																	***
	//***				SHOW DEBUG INFO						 	***
	//***																	***
	//***********************************************************************
	//
	//	Resets WP-United back to freshly installed state
	//
	function debug_show() {
		global $phpbb_root_path, $phpEx, $wpuAbs, $phpEx, $db;
		$this->page_title = 'ACP_WPU_INDEX_TITLE';
		$this->tpl_name = 'acp_wp_united';	
		
		$result = $db->sql_query("select version() as ve");
		if ($res = $db->sql_fetchrow($result)) {
			$myVersion = $res['ve'];
		} else {
			$myVersion = "Unable to detect. Not mySql?";
		}
		
		$curl_exists = (function_exists('curl_exec')) ? "Yes" : "No";
		
		
		$wpSettings = get_integration_settings(TRUE);
		$debug_info = '<strong style="text-decoration: underline;">[b][u]' . $wpuAbs->lang('DEBUG_SETTINGS_SECTION') . '[/u][/b]</strong><br /><br />';
		foreach ($wpSettings as $setting_name => $setting_value) {
			$debug_info .= '[b]<strong>: ' . $setting_name . ':</strong>[/b] ' . $setting_value . "<br />\n";
		}
		
		$debug_info .= '<br /><strong style="text-decoration: underline;">[b][u]' . $wpuAbs->lang('DEBUG_PHPBB_SECTION') . '[/u][/b]</strong><br /><br />';
		$phpbb_info = array('server_name', 'server_protocol', 'server_port', 'script_path', 'force_server_vars', 'cookie_name', 'cookie_domain', 'cookie_path', 'cookie_secure');
		foreach ($phpbb_info as $config_name) {
			$debug_info .= '[b]<strong>' . $config_name . ':</strong>[/b] ' . $wpuAbs->config($config_name) . "<br />\n";
		}
		
		$debug_info .= '<br /><strong style="text-decoration: underline;">[b][u]' . $wpuAbs->lang('DEBUG_SERVER_SETTINGS') .  '[/u][/b]</strong><br /><br />';
		$debug_info .= '[b]<strong>PHP version:</strong>[/b] ' . phpversion() . "<br />\n";
		$debug_info .= '[b]<strong>MySQL version:</strong>[/b] ' . $myVersion . "<br />\n";
		$debug_info .= '[b]<strong>cURL available:</strong>[/b] ' . $curl_exists . "<br />\n";
		
		// pass strings	
		$passVars = array(
			'L_WPMAIN_TITLE' => $wpuAbs->lang('WP_Main_Title'),
			'L_DEBUG_TITLE' => $wpuAbs->lang('WP_Debug'),
			'L_DEBUG_EXPLAIN' => $wpuAbs->lang('WP_Debug_Explain'),
			'INFO_TO_POST' => $wpuAbs->lang('L_INFO_TO_POST'),
			'DEBUG_INFO' => $debug_info,
		);
		
		// set the page section to show
		$passBlockVars = array(
			'switch_debug' => array(),
		);

		//show the page
		$this->showPage($passVars, $passBlockVars);
	
	
	}
	
	// **********************************************************************
	//***																	***
	//***				RESET WP-UNITED						 	***
	//***																	***
	//***********************************************************************
	//
	//	Resets WP-United back to freshly installed state
	//
	function reset_show() {
		global $phpbb_root_path, $phpEx, $wpuAbs, $phpEx, $cache;
		$this->page_title = 'ACP_WPU_INDEX_TITLE';
		$this->tpl_name = 'acp_wp_united';

		$do_reset = request_var('resetaction', '');
		$did_reset = request_var('didreset', '');
		
		// pass strings	
		$passVars = array(
			'L_WPMAIN_TITLE' => $wpuAbs->lang('WP_Main_Title'),
			'L_RESET_TITLE' => $wpuAbs->lang('WP_Reset'),
			'L_WP_RESET' => $wpuAbs->lang('WP_Reset_Button'),
			'L_RESET_EXPLAIN' => $wpuAbs->lang('WP_Reset_Explain'),
			'S_WPWIZ_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),			
		);
		
		if($did_reset) {
			$passVars['DID_RESET'] = $wpuAbs->lang('WP_Did_Reset');
		}
		
		if ($do_reset == $wpuAbs->lang('WP_Reset_Button')) {
			if (confirm_box(true)) {	
			
				// reset modules...
				//delete them first
				$errors = $this->remove_modules();
				
				//re-create modules
				$modData = array(
					'module_basename'	=> '', 
					'module_mode'		=> '',
					'module_auth'		=> '', 
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> 0,
					'module_langname'	=> 'ACP_WP_UNITED', 
					'module_class'		=>'acp',
				);
				$tabId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> '', 
					'module_mode'		=> '', 
					'module_auth'		=> '', 
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> $tabId,
					'module_langname'	=> 'ACP_WPU_CATMAIN', 
					'module_class'		=>'acp',
				);
				$catMainId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> '', 
					'module_mode'		=> '', 
					'module_auth'		=> '', 
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> $tabId,
					'module_langname'	=> 'ACP_WPU_CATSETUP', 
					'module_class'		=>'acp',
				);
				$catSetupId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> '', 
					'module_mode'		=> '',
					'module_auth'		=> '', 
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> $tabId,
					'module_langname'	=> 'ACP_WPU_CATMANAGE',
					'module_class'		=>'acp',
				);
				$catManageId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> '', 
					'module_mode'		=> '',
					'module_auth'		=> '', 
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> $tabId,
					'module_langname'	=> 'ACP_WPU_CATSUPPORT', 
					'module_class'		=>'acp',
				);
				$catSupportId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> '', 
					'module_mode'		=> '', 
					'module_auth'		=> '',
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> $tabId,
					'module_langname'	=> 'ACP_WPU_CATOTHER', 
					'module_class'		=>'acp',
				);
				$catOtherId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> 'wp_united',
					'module_mode'		=> 'index', 
					'module_auth'		=> 'acl_a_wpu_manage',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> $catMainId,
					'module_langname'	=> 'ACP_WPU_MAINTITLE',
					'module_class'		=>'acp',
				);
				$catMainPageId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> 'wp_united',
					'module_mode'		=> 'wizard', 
					'module_auth'		=> 'acl_a_wpu_manage',
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> $catSetupId,
					'module_langname'	=> 'ACP_WPU_WIZARD', 
					'module_class'		=>'acp',
				);
				$catSetupWizId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> 'wp_united', 
					'module_mode'		=> 'detailed', 
					'module_auth'		=> 'acl_a_wpu_manage',
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> $catSetupId,
					'module_langname'	=> 'ACP_WPU_DETAILED', 
					'module_class'		=>'acp',
				);
				$catDetailedId = $this->add_acp_module($modData);
				$modData = array(
					'module_basename'	=> 'wp_united',
					'module_mode'		=> 'usermap', 
					'module_auth'		=> 'acl_a_wpu_manage', 
					'module_enabled'	=> 0,
					'module_display'	=> 0, 
					'parent_id'			=> $catManageId,
					'module_langname'	=> 'ACP_WPU_USERMAP', 
					'module_class'		=>'acp'
				);
				$someid = $this->add_acp_module($modData);
				$modData = array(
					'module_basename'	=> 'wp_united',
					'module_mode'		=> 'donate', 
					'module_auth'		=> 'acl_a_wpu_manage', 
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> $catSupportId,
					'module_langname'	=> 'ACP_WPU_DONATE', 
					'module_class'		=>'acp',
				);
				$catDetailedId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> 'wp_united', 
					'module_mode'		=> 'uninstall',
					'module_auth'		=> 'acl_a_wpu_manage',
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> $catOtherId,
					'module_langname'	=> 'ACP_WPU_UNINSTALL', 
					'module_class'		=>'acp',
				);
				$catDetailedId = $this->add_acp_module($modData);

				$modData = array(
					'module_basename'	=> 'wp_united', 
					'module_mode'		=> 'reset',
					'module_auth'		=> 'acl_a_wpu_manage', 
					'module_enabled'	=> 1,
					'module_display'	=> 1, 
					'parent_id'			=> $catOtherId,
					'module_langname'	=> 'ACP_WPU_RESET', 
					'module_class'		=>'acp',
				);
				$resetId= $this->add_acp_module($modData);
				
				$modData = array(
					'module_basename'	=> 'wp_united',
					'module_mode'		=> 'debug',
					'module_auth'		=> 'acl_a_wpu_manage', 
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> $catOtherId,
					'module_langname'	=> 'ACP_WPU_DEBUG', 
					'module_class'		=>'acp',
				);
				$resetId= $this->add_acp_module($modData);
				
				
				
				
				//now reset settings
				clear_integration_settings();
				$cache->destroy('_modules_');
				$cache->destroy('_sql_', MODULES_TABLE);
				$cache->purge();
				add_log('admin','ACP_WPU_RESET', $wpuAbs->lang('WP_Reset_Log'));	
				//the module IDs have changed -- we redirect so that the "WP-United" tab on the page is the new one.
				redirect(append_sid("index.$phpEx?mode=reset&didreset=true&i=$resetId"));		
			} else {
				confirm_box(false,$wpuAbs->lang('WP_Reset_Confirm'), build_hidden_fields(array(
						'i'			=> 'wp_united',
						'mode'		=> 'reset',
						'resetaction' => $wpuAbs->lang('WP_Reset_Button'),
					)));
			}
		}

		// set the page section to show
		$passBlockVars = array(
			'switch_reset' => array(),
		);

		//show the page
		$this->showPage($passVars, $passBlockVars);
	}
	//
	//	PERMISSIONS SIDEBAR LINK
	//	---------------------------------------------------------------------------------------------------
	//	Adding a module doesn't cross-link to the right tab. So we just redirect instead
	//	
	function permissions_show() {
		redirect(append_sid("index.php?i=permissions&mode=intro"));
	}


	
	//
	//	GET CONFIGURATION SETTINGS FROM DATABASE, OR SET DEFAULTS
	//	---------------------------------------------------------------------------------------------------
	//	Gets the configuration settings from the integration table, and returns them in $wpSettings.
	//	Sets initial values to sensible deafaults if they haven't been set yet -- useful for populating form fields with default values.
	//
	function get_settings_set_defaults() {
		
		//This has been superseded by defaults in mod_settings.
		return get_integration_settings(TRUE);

	}








	//
	//	TEST THE PROVIDED URL
	//	-----------------------------------
	//  	Tests that the supplied URL exists and is on the same domain.
	//	Return the result in XML if browser supports AJAX. 
	//
	function testUri($ajax = TRUE) {
		global $wpuAbs;
		
		$hostName = "http://".$_SERVER['HTTP_HOST'];
		
		$wpUri = $this->clean_path(request_var('txt_Uri', ''));
		$wpUri = $this->add_http($wpUri);
		$wpUri = $this->add_trailing_slash($wpUri);
		$uriExists = $this->uri_exists($wpUri);
		$sameHost = strpos($wpUri, $hostName);
		
		if ( !empty($uriExists) ) {
			If ( $sameHost === FALSE) {
				$testSuccess = "WARNING";
				$returnMessage = $wpuAbs->lang('WP_URI_OK_Diff_Host');
			} else {
				$testSuccess = "SUCCESS";
				$returnMessage = $wpuAbs->lang('WP_URI_Found');
			}
		} elseif ( $uriExists === 0 ) {
			$testSuccess = "ERROR";
			$returnMessage = $wpuAbs->lang('WP_cURL_Not_Avail');
		} elseif ( $sameHost === FALSE) {
			$testSuccess = "ERROR";
			$returnMessage = $wpuAbs->lang('WP_URI_No_Diff_Host');
		} else {
			$testSuccess = "ERROR";
			$returnMessage = $wpuAbs->lang('WP_URI_Not_Found');
		}
		
		
		if ( $ajax ) {
			$xmlData['result'] = $testSuccess;
			$xmlData['message'] = $returnMessage;
			$xmlData['info'] = $wpUri;

			$this->send_ajax($xmlData, 'testuri');
		} 

		return $returnMessage;
	}

	//
	//	PATH "AUTO-DISCOVERY"
	//	-----------------------------------
	//	Figures out the filesystem path to WordPress and tests to see if WordPress files exist there.
	//	Return the result in XML if browser supports AJAX. 
	//
	function findPath($ajax = TRUE) {
		
		global $wpuAbs;
		
		$hostName = "http://".$_SERVER['HTTP_HOST'];
		$wpUri = $this->clean_path(request_var('txt_Uri', ''));
		$wpPath = $this->clean_path(request_var('txt_Path', ''));
		$manuallyTyped = FALSE; $returnMessage = '';
		
		if ( empty($wpPath) ) { //the path field is empty, autodetect
			$sameHost = strpos($wpUri, $hostName);
			if ($sameHost === FALSE ) {
				$testSuccess = 'ERROR';
				$returnMessage = $wpuAbs->lang('WP_PathTest_Diff_Host');
			} else {

				$wpPath = $this->detect_path_from_uri($wpUri);
			}
		} else {
			$manuallyTyped = true;
		}
		
		if ( !$returnMessage ) {
			if ( empty($wpPath) ) { // path is still empty, after autodetect
				$testSuccess = 'ERROR';
				if ( strlen($wpUri) <= 3 ) {  //because the URI is invalid on non-existent
					$returnMessage = $wpuAbs->lang('WP_PathTest_Invalid_URL');
				} else { // Due to an unknown error
					$returnMessage = $wpuAbs->lang('WP_PathTest_Not_Detected');
				}
			} else {
				if ( $this->wordpress_exists($wpPath) ) { //Found WordPress at that path
					$testSuccess = 'SUCCESS';
					$returnMessage = sprintf($wpuAbs->lang('WP_PathTest_Success'), $wpPath);
				} else { // Found path (or path was manualy typed), but no WordPress there!
					$testSuccess = 'WARNING';
					if ( $manuallyTyped ) {
						$returnMessage = $wpuAbs->lang('WP_PathTest_TestOnly_NotFound');
					} else {
						$returnMessage = sprintf($wpuAbs->lang('WP_PathTest_GuessedOnly'), $wpPath);
					}
				}
			}
		}
		if ( $ajax ) {
			$xmlData['result'] = $testSuccess;
			$xmlData['message'] = $returnMessage;
			$xmlData['info'] = ( empty($wpPath) ) ? 'null' : $wpPath;

			$this->send_ajax($xmlData, 'testpath');
		}
		
		
		return $returnMessage;

	}













	//
	//	INSTALL WP-UNITED CONNECTION
	//	-----------------------------------
	//	Sets and installs the auto-plugin, "WP-United Connection"
	//
	function install_wpuConnection() {
		define ('WPU_SET', 1);
		global $wpuAbs, $wpuCache, $wpSettings, $phpEx, $phpbb_root_path, $board_config, $wpu_debug, $wpUtdInt;
		require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);		
		$wpuCache = WPU_Cache::getInstance();

		//Set up the WordPress Integration
		require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
		$wpUtdInt = WPU_Integration::getInstance();
		$connError = FALSE;
		if ($wpUtdInt->can_connect_to_wp()) {
			//Enter Integration
			$wpUtdInt->enter_wp_integration();
			//ob_start(); //ignore phpBB notices about WP code
			eval($wpUtdInt->exec());  
			//ob_end_clean();
			//Figure out the filepath  to phpBB
			//$thisPath = $this->add_trailing_slash($this->clean_path(realpath(dirname(__FILE__))));
			
			// Old (above) path did not work on symlinked WP-united installs. Below finds path instead to 
			// adm
			$thisPath = $this->add_trailing_slash($this->clean_path(realpath(getcwd())));
			$wpu_debug .= 'DEBUG (to post if you need help):<br />';
			$wpu_debug .= 'Current Path ' . $thisPath . '<br />';
			
			$thisPath =  explode("/", $thisPath );
			array_pop($thisPath); array_pop($thisPath); //array_pop($thisPath); 
			//get the filepath to WordPress
			$wpLoc = explode ("/", $this->add_trailing_slash($this->clean_path(realpath($wpSettings['wpPath']))));
			
			$wpu_debug .= 'Path to WP: ' . $wpSettings['wpPath'] . ' <br />Realpath to WP: ' . $this->add_trailing_slash($this->clean_path(realpath($wpSettings['wpPath']))) . '<br />';
			
			//ditch common parent dirs from the paths
			$pathsComputed = FALSE;
			while ( (($thisPath[0] == $wpLoc[0]) || ((DIRECTORY_SEPARATOR == '\\') && (strtolower($thisPath[0]) == strtolower($wpLoc[0])))) && (count($thisPath) > 0) && (count($wpLoc) > 0) )  {
				array_shift($thisPath);
				array_shift($wpLoc);
			}
			
			//The location of the WP-United Connection files
			$toPlugin = ( count($thisPath) > 0 ) ? implode("/", $thisPath)."/wp-united/" : "wp-united/";
			
			$wpu_debug .= 'Path to WPUtd Conn. Files: ' . $toPlugin . '<br />';
			
			//The location of phpBB root dir
			$toP = ( count($thisPath) > 0 ) ? implode("/", $thisPath)."/" : "";
			
			$wpu_debug .= 'Calc. path to phpBB: ' . $toP . '<br />';
			$wpu_debug .= 'ABSPATH: ' . ABSPATH . '<br />';
			//Count back the number of Dirs from the WP install. We intentially add one extra, as we will be using this from the <wordpress>/admin dir.
			$stepsToW = count($wpLoc);
			$adminFromW = '';
			for ($cti = 1; $cti <= $stepsToW; $cti++) {
				$adminFromW .= "../";
			}
			// Plugins are activated from the <wordpress>/wp-content/plugins dir - i.e. one level deeper...
			$fromW = $adminFromW . "../";
			
			//WP 2.3 and onwards doesn't allow directory traversal, so we need to ask user to copy the file instead
			$wpPluginDir = $this->add_trailing_slash($this->clean_path(realpath($wpSettings['wpPath']))) . "wp-content/plugins/";
			if (file_exists($wpPluginDir)) {
				// we got the plugin directory correct, copy file over
				$copySuccess = false;
				if(!@copy($phpbb_root_path . "/wp-united/wpu-plugin.php", $wpPluginDir . "wpu-plugin.php")) {
					// Copy failed
				} 
				if (file_exists($wpPluginDir . "wpu-plugin.php")) {
					// Check to see that WPU-Plugin is the correct version
					
					$correctVerFile = file_get_contents($phpbb_root_path . "/wp-united/wpu-plugin.php");
					$found = preg_match('/\|\|WPU-PLUGIN-VERSION=[0-9\.]*\|\|/', $correctVerFile,  $correctVer);
					unset($correctVerFile);
					if ($found) {
						$testVerFile = file_get_contents($wpPluginDir . "wpu-plugin.php");
						$test = preg_match('/\|\|WPU-PLUGIN-VERSION=[0-9\.]*\|\|/', $testVerFile,  $testVer);
						unset($testVerFile);
						if ($test) {
							if ( ($testVer[0] == $correctVer[0]) && (!empty($testVer[0])) ) {
								$copySuccess = true;
							}
						}
					}
				}
		
				
			}

			if(!$copySuccess) { 
				// CORRECT WPU-PLUGIN IS NOT IN PLUGIN DIRECTORY -- FAIL
				
				$connError = TRUE;
				$wpu_debug = "<br />" . $wpuAbs->lang('WPWizard_Connection_Fail_Explain2');
				
			} else {
			
				// We build up the connection settings for WordPress
				$pluginPath = "wpu-plugin." . $phpEx;
			
			
				//$pluginPath = $fromW.$toPlugin. "wpu-plugin." . $phpEx;
			
				$wpu_debug .= 'Final Calculated Path: ' . $pluginPath . '<br />'; 
			
				$WPU_Connection['full_path_to_plugin'] = $pluginPath;
				//And the path we'll use to access the phpBB root from the WordPress admin dir is:
				$WPU_Connection['path_to_phpbb'] = $adminFromW . $toP;
				$wpu_debug .= 'Path back to phpBB: ' . $WPU_Connection['path_to_phpbb'] . '<br />';
				// We will also want to access our WP-United Connection as a relative URL
				$WPU_Connection['path_to_plugin'] = $this->add_trailing_slash($board_config['script_path']) . "wp-united/wpu-plugin." . $phpEx;
				// We'll also want to have the full scriptPath in wp-admin for playing with URLs
				$server = $wpuAbs->config('server_protocol') . $this->add_trailing_slash($wpuAbs->config('server_name'));
				$scriptPath = $this->add_trailing_slash($wpuAbs->config('script_path'));
				$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
				$scriptPath = $server . $scriptPath;
				$WPU_Connection['phpbb_url'] = $scriptPath;
				
				//and...
				$WPU_Connection['path_to_wp'] = $wpSettings['wpPath'];
				$WPU_Connection['logins_integrated'] = $wpSettings['integrateLogin'];
				$WPU_Connection['styles'] = $wpSettings['allowStyleSwitch'];
				$WPU_Connection['blogs'] = $wpSettings['usersOwnBlogs'];
				$WPU_Connection['wpu_enable_xpost'] = $wpSettings['xposting'];
				$WPU_Connection['autolink_xpost'] = $wpSettings['xpostautolink'];
				//Set Connection settings
				update_option('wputd_connection', $WPU_Connection);
				$server = $this->add_http($this->add_trailing_slash($wpuAbs->config('server_name')));
				$scriptPath = $this->add_trailing_slash($wpuAbs->config('script_path'));
				$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
				$blogUri = $wpSettings['blogsUri']; 
			
				//Set up WordPress the way we want
				update_option('home', $blogUri);
				global $wpdb;
				//Set up the blog front page
				$post_ID = get_option('wpu_set_frontpage');
				if ( $wpSettings['useBlogHome'] ) {
					if ( !empty($post_ID) ) {
						$wpdb->query(
						"UPDATE IGNORE $wpdb->posts SET
							post_author = '0',
							post_date = '".current_time('mysql')."',
							post_date_gmt = '".current_time('mysql',1)."',
							post_content = '<!--wp-united-home-->',
							post_content_filtered = '',
							post_title = '{$wpSettings['blogListHead']}',
							post_excerpt = '',
							post_status = 'publish',
							post_type = 'page',
							comment_status = 'closed',
							ping_status = 'closed',
							post_password = '',
							post_name = 'blogs-home',
							to_ping = '',
							pinged = '',
							post_modified = '".current_time('mysql')."',
							post_modified_gmt = '".current_time('mysql',1)."',
							post_parent = '0',
							menu_order = '0'
							WHERE ID = $post_ID"
						);
					} else {
						$wpdb->query(
						"INSERT IGNORE INTO $wpdb->posts
								(post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, post_type, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_mime_type)
							VALUES
								('0', '".current_time('mysql')."', '".current_time('mysql',1)."', '<!--wp-united-home-->', '', '{$wpSettings['blogListHead']}', '', 'publish', 'page', 'closed', 'closed', '', 'blogs-home', '', '', '".current_time('mysql')."', '".current_time('mysql',1)."', '0', '0', '')"
						);
						$post_ID = $wpdb->insert_id;		
					}
				
					update_option('wpu_set_frontpage', $post_ID);
					update_option('page_on_front', $post_ID);
					update_option('posts_on_front', ''); 
					update_option('show_on_front', 'page');
				} else {
					if ( !empty($post_ID) ) {
						update_option('wpu_set_frontpage', '');
						update_option('page_on_front', '');
						update_option('posts_on_front', ''); 
						update_option('show_on_front', 'posts');
						wp_delete_post($post_ID);
					}
				}	
				if ($wpuAbs->ver == 'PHPBB2') {
					//Set up character encoding
					//If we change WP encoding, back it up so it can be restored later.
					$backup = get_option('wpu_blog_charset_backup');
					if ( $wpSettings['charEncoding'] == 'MATCH_PHPBB' ) {
						if ( empty($backup) ) {
							update_option('wpu_blog_charset_backup', get_option('blog_charset'));
						}
						update_option('blog_charset', $wpuAbs->lang('ENCODING'));
					} else {
						if ( !empty($backup) ) {
							update_option('blog_charset', $backup);
							update_option('wpu_blog_charset_backup', '');
						}
					}
				}
			
				//Activate the Connection
				if ( file_exists(ABSPATH . 'wp-content/plugins/' . $pluginPath) ) {
					$current = get_settings('active_plugins'); 
					//remove any existing entries to prevent duplicates
					$arrPlugins = array();
					if ( is_array($current) ) {
						foreach ($current as $wpPlugin) {
							$isWPUConn = strpos($wpPlugin, 'wpu-plugin');
							if ( $isWPUConn === FALSE ) {
								$arrPlugins[] = $wpPlugin;
							}
						}
					} elseif ( !empty($current) ) {
						$arrPlugins[] = $current;
					}
					$arrPlugins[] = $pluginPath;
					sort($arrPlugins);
					update_option('active_plugins', $arrPlugins);
				} else {
					$connError = TRUE; 
					$wpu_debug = "<br />" . $wpuAbs->lang('WPWizard_Connection_Fail_Explain2');
				}
			} // end if copy success
			
		} else { // can't connect to WP
			$connError = TRUE;
			$debugPath = $this->add_trailing_slash($this->clean_path(realpath(dirname(__FILE__))));
			$wpu_debug .= "<br />Can't connect to WordPress<br />";
			$wpu_debug .= $wpuAbs->lang('WPWizard_Connection_Fail_Explain1');
			$wpu_debug .=  'DEBUG (to post if you need help):<br />Current Path: ' . $debugPath . ' <br />';
			$wpu_debug .= 'Path To WP: ' . $wpSettings['wpPath'] . '<br />';
			
		}
		$wpUtdInt->exit_wp_integration();
		$wpUtdInt = null; unset ($wpUtdInt);
		return $connError;
	}



	//
	//	USERMAP_INTRO
	//	------------------------
	//	Shows the introductory usermap page
	//
	function usermap_intro() {
		global $wpuAbs, $phpEx;
		$this->page_title = 'L_MAP_TITLE';
		$this->tpl_name = 'acp_wp_united';

		//Get integration settings
		$wpSettings = get_integration_settings();
		if ( ($wpSettings == FALSE)	|| ($wpSettings['wpPath'] == '') ) {
			$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_DBErr_Gen'), $wpuAbs->lang('L_WP_NO_SETTINGS'), __LINE__, __FILE__, $sql);
		}
		
		$passVars = array(	
			'L_MAP_TITLE'  =>	$wpuAbs->lang('L_MAP_TITLE'),
			'L_MAP_INTRO1' => $wpuAbs->lang('L_MAP_INTRO1'),
			'L_MAP_INTRO2' => $wpuAbs->lang('L_MAP_INTRO2'),
			'L_MAP_INTRO3' => $wpuAbs->lang('L_MAP_INTRO3'),
			'L_MAP_INTRO4' => $wpuAbs->lang('L_MAP_INTRO4'),
			'L_MAP_INTRO5' => $wpuAbs->lang('L_MAP_INTRO5'),
			'L_MAP_INTRO6' => $wpuAbs->lang('L_MAP_INTRO6'),
			'L_MAP_INTRO7' => $wpuAbs->lang('L_MAP_INTRO7'),
			'L_MAP_BEGIN' => $wpuAbs->lang('L_MAP_BEGIN'),
			'S_WPMAP_ACTION' => append_sid("index.$phpEx?i=wp_united")
		);
		
		// set the page section to show
		$passBlockVars = array(
			'switch_usermap_intro' => array(),
		);
		$this->showPage($passVars, $passBlockVars);
	}
	
	//
	//	USERMAP_MAIN
	//	------------------------
	//	The main usermap page that lists all the names
	//
	function usermap_main() {	
		global $wpuAbs, $phpEx, $phpbb_root_path, $wpSettings, $db, $template;
		// NUMBER OF RESULTS PER PAGE -- COULD ADJUST THIS FOR LARGE USERBASES
		
		$numPerPage = $numResults = (int)request_var('wpumapperpage', 50);
		
		//Get integration settings
		$wpSettings = get_integration_settings();
		if ( ($wpSettings == FALSE)	|| ($wpSettings['wpPath'] == '') ) {
			$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_DBErr_Gen'), $wpuAbs->lang('L_WP_NO_SETTINGS'), __LINE__, __FILE__, $sql);
		}			
		
		$this->page_title = 'L_MAP_TITLE';
		$this->tpl_name = 'acp_wp_united';
		
		// set the page section to show
		$template->assign_block_vars('switch_usermap_main', array());        

		// Eventually this will be in a dropdown.
		$action = request_var('mapaction', '');
		if($action == $wpuAbs->lang('Map_Change_PerPage')) {
			$wpStart = (int)request_var('oldstart', 0);
		} else {
			$wpStart = (int)request_var('start', 0);
		}
		
		$thisEnd = $nextStart = $wpStart + $numResults;
		
		// Enter WordPress and pull user data
		global $wpdb, $wpUtdInt, $wpuCache;
		require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);
		$wpuCache = WPU_Cache::getInstance();
		require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
		$wpUtdInt = WPU_Integration::getInstance(get_defined_vars());
		define('USE_THEMES', FALSE);
		if ($wpUtdInt->can_connect_to_wp()) {
			$wpUtdInt->enter_wp_integration();
			eval($wpUtdInt->exec()); 

			$sql = "SELECT count(*) AS total
				FROM {$wpdb->users} 
				WHERE {$wpdb->users}.user_login <> 'admin'";
			
			$countEntries = $wpdb->get_results($sql);
			$numWpResults = $countEntries[0]->total;
			//$thisEnd = ($thisEnd > $numWpResults) ? $numWpResults : $thisEnd
			$numPages = ceil($numWpResults / $numResults);
			$curPage = ceil(($wpStart/$numResults) + 1);
			$sql = "SELECT ID, user_login, user_nicename 
				FROM {$wpdb->users}
				WHERE user_login<>'admin'
				ORDER BY user_login
				LIMIT $wpStart, $thisEnd";
			//execute sql
			$results = $wpdb->get_results($sql);

			if ( count($results) > 0 ) {
				//output table with results
				if ( $numPages > 1 ) {
					$template->assign_block_vars('switch_usermap_main.switch_multipage', array());
				}
				$itn = 0; $x=2;

				foreach ((array) $results as $result) {
					$optCre = '';
					$posts = get_usernumposts($result->ID);
					//TODO: show number of comments
					if ( empty($result->ID) ) {
						$wpuAbs->err_msg(GENERAL_ERROR, 'No WordPress ID!', 'No ID error!', __LINE__, __FILE__, $sql);
					}
					$phpBBMappedName = get_usermeta($result->ID, 'phpbb_userLogin');
					if ( empty($phpBBMappedName) ) {
						$phpBBMappedName = $result->user_login;
					}
					$wpUtdInt->switch_db('TO_P');
					$pUsername = '';
					$pID = '';
					$class = '';
					$pStatus = $wpuAbs->lang('L_MAP_NOT_INTEGRATED');
					$intText = $wpuAbs->lang('L_MAP_INTEGRATE');
					$selInt = ''; $selBrk = ''; $selDel = '';
					$alreadyID = ''; $alreadyUN = ''; $mustBrk = 'FALSE';
					
					//First let's see if they are already integrated
					$sql = 	"SELECT username, user_id FROM " . USERS_TABLE .
					" WHERE user_wpuint_id = '{$result->ID}'";
					if ( $pFirstResults = $db->sql_query($sql) ) {	
						$numResults = 0; $pRes = '';
						if ($pResNew = $db->sql_fetchrow($pFirstResults)) {
							//We found an integration ID...
							$ctr = 1;
							while ( $pResNew ) {
								$pID .= ($numResults > 1) ? ', ' . $pResNew['user_id'] :  $pResNew['user_id'];
								$pUsername .= ($numResults > 1) ? ', ' . $pResNew['username'] :  $pResNew['username'];
								$pRes = $pResNew;
								$pResNew = $db->sql_fetchrow($pFirstResults);
								$numResults++;
							}
							if ($numResults > 1) {
								$pStatus = $wpuAbs->lang('L_MAP_ERROR_MULTIACCTS');
								$breakOrLeave = $wpuAbs->lang('L_MAP_BRK_MULTI');
								$selBrk = 'selected="selected"';
								$mustBrk = 'TRUE';
								$class = "mustbrk";
							} else {
								$pStatus = $wpuAbs->lang('L_MAP_ALREADYINT'); 
								$breakOrLeave = $wpuAbs->lang('L_MAP_BRK');
								$selInt = 'selected="selected"';
								$intText = $wpuAbs->lang('L_MAP_LEAVE_INT');
								$alreadyID = $pRes['user_id'];
								$alreadyUN = $pRes['username'];
								$class = "alreadyint";			
							}
						} else {
							//No Integration ID... so let's search for a match
							
							//User may want to create a phpBB user
							$optCre = '<option value="Cre">'. $wpuAbs->lang('L_MAP_CREATEP') .'</option>'; 
							
							if ( !empty($phpBBMappedName) ) {
								$sql = 	"SELECT username, user_id, user_wpuint_id FROM " . USERS_TABLE .
								" WHERE username = '" . $phpBBMappedName . "'
										LIMIT 1";
								if (!$pResults = $db->sql_query($sql)) {
									$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_DBErr_Retrieve'), $wpuAbs->lang('L_MAP_CANTCONNECTP'), __LINE__, __FILE__, $sql);
								}
								if ($pResults = $db->sql_fetchrow($pResults))  {
									//OK, so we found a username match... but show only if they're not already integrated to another acct.
									if ( empty($pResults['user_wpuint_id']) ) {
										if ( (!empty($pResults['username'])) && (!empty($pResults['user_id'])) ) {
											$pUsername = $pResults['username'];
											$pID = $pResults['user_id'];
											$breakOrLeave = $wpuAbs->lang('L_MAP_LEAVE_UNINT');
											$pStatus = $wpuAbs->lang('L_MAP_UNINT_FOUND');
											$selInt = 'selected="selected"';
											$class = 'unintfound';
										}
									} else {
										$breakOrLeave = $wpuAbs->lang('L_MAP_LEAVE_UNINT');
										$selBrk = 'selected="selected"';
										$pStatus = sprintf($wpuAbs->lang('L_MAP_UNINT_FOUNDBUT'), $pResults['username'], $pResults['username'], $pResults['user_wpuint_id']);
										$class = 'unintfoundbut';
									}	
								} else {
									// Offer to create the user
									$optCre = '<option value="Cre" selected="selected">'. $wpuAbs->lang('L_MAP_CREATEP') .'</option>'; 
									$pStatus = $wpuAbs->lang('L_MAP_UNINT_NOTFOUND'); 										
									$pUsername = $phpBBMappedName;
									$breakOrLeave = $wpuAbs->lang('L_MAP_LEAVE_UNINT');
									$class = 'unintnotfound';
									/*
									$breakOrLeave = $wpuAbs->lang('L_MAP_LEAVE_UNINT');
									$selBrk = 'selected="selected"';
									$pStatus = $wpuAbs->lang('L_MAP_UNINT_NOTFOUND'); ; */
								}	
							}
						}	
					} else {
						$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_DBErr_Retrieve'), $wpuAbs->lang('WP_DBErr_Retrieve'), __LINE__, __FILE__, $sql);
					}
					if ( empty($phpBBMappedName) ) {
						$breakOrLeave = $wpuAbs->lang('L_MAP_LEAVE_UNINT');
						$selDel = 'selected="selected"';
						$pStatus = $wpuAbs->lang('L_MAP_ERROR_BLANK');
						$class = "maperror";
					}
					$wpUtdInt->switch_db('TO_W');
					$bg = ($mustBrk == 'FALSE' ) ? 'none' : 'red';		
					$x = ( $x == 1 ) ? 2 : 1;

					$template->assign_block_vars('switch_usermap_main.maplist_row', array(
						'CLASS' => $class,
						'EVERY_OTHER' => $x,				
						'BGCOLOUR' => $bg,
						'ROW_NUM' => $itn,
						'WP_ID' => $result->ID,
						'WP_LOGIN' => $result->user_login,
						'WP_NICENAME' => $result->user_nicename,
						'WP_NUMPOSTS' => $posts,
						'ALREADY_USERNAME' => $alreadyUN,
						'P_USERNAME' => $pUsername,
						'ALREADY_ID' => $alreadyID,
						'P_ID' => $pID,
						'P_STATUS' => $pStatus,
						'S_INTEGRATED_SELECTED' => $selInt,
						'S_BREAK_SELECTED' => $selBrk,
						'S_DEL_SELECTED' => $selDel,
						'L_SEL_INTEGRATE' => $intText,
						'L_SEL_BREAK_OR_LEAVE' => $breakOrLeave,
						'L_MAP_DEL_FROM_WP' => $wpuAbs->lang('L_MAP_DEL_FROM_WP'),
						'S_MUST_BREAK' => $mustBrk,
						'S_OPT_CREATE' => $optCre,
					
					));
					
					$itn++;
				}
				if ( $thisEnd < $numWpResults ) {
					$template->assign_block_vars('switch_usermap_main.next_page_data', array(
						'L_MAP_SKIPNEXT' => $wpuAbs->lang('L_MAP_SKIPNEXT'),
					));
				} 

			} else {
				$template->assign_block_vars('switch_usermap_main.switch_no_results', array(
					'L_MAP_NOUSERS' => $wpuAbs->lang('L_MAP_NOUSERS'),
				));
			}
		} else {
			die($wpuAbs->lang('L_MAP_CANT_CONNECT'));
		}
		
		$passVars = array(	
			'L_MAP_TITLE'  =>	$wpuAbs->lang('L_MAP_TITLE'),
			'S_WPMAP_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'S_NEXTSTART' => $nextStart,
			'S_OLDSTART' => $wpStart,
			'S_TOTAL_ITN' => $itn - 1,
			'L_MAP_PROCESS' => $wpuAbs->lang('L_MAP_PROCESS'),
			'L_MAPMAIN_1' => $wpuAbs->lang('L_MAPMAIN_1'),
			'L_MAPMAIN_2' => $wpuAbs->lang('L_MAPMAIN_2'),
			'L_COL_WP_DETAILS' => $wpuAbs->lang('L_COL_WP_DETAILS'),
			'L_COL_MATCHED_DETAILS' => $wpuAbs->lang('L_COL_MATCHED_DETAILS'),
			'L_USERID' => $wpuAbs->lang('L_USERID'),
			'L_USERNAME' => $wpuAbs->lang('L_USERNAME'),
			'L_NICENAME' => $wpuAbs->lang('L_NICENAME'),
			'L_NUMPOSTS' => $wpuAbs->lang('L_NUMPOSTS'),
			'L_USERNAME' => $wpuAbs->lang('L_USERNAME'),
			'L_USERID' => $wpuAbs->lang('L_USERID'),
			'L_MAP_STATUS' => $wpuAbs->lang('L_MAP_STATUS'),
			'L_MAP_ACTION' => $wpuAbs->lang('L_MAP_ACTION'),
			'L_MAPMAIN_MULTI' => $wpuAbs->lang('L_MAPMAIN_MULTI'),
			
			'L_MAP_ITEMS_PERPAGE' => $wpuAbs->lang('Map_Items_PerPage'),
			'L_MAP_CHANGE_PERPAGE' => $wpuAbs->lang('Map_Change_PerPage'),
			'L_MAP_QUICK_ACTIONS' => $wpuAbs->lang('Map_Quick_Actions'),
			'L_MAP_DELETE_ALL_UNINTEGRATED' => $wpuAbs->lang('Map_Delete_All_Unintegrated'),
			'L_MAP_BREAK_ALL' => $wpuAbs->lang('Map_Break_All'),
			'L_MAP_RESET_DEFAULT' => $wpuAbs->lang('Map_Reset_Default'),
			

		);
		
		for($i=50;$i<=500;$i=$i+50) {
			$passVars['S_NUMPERPAGE_' . $i] = ($i == $numPerPage) ? 'selected = "selected"' : '';
		}
		
		$this->showPage($passVars, 0);
		
	}
	
	
	//
	//	USERMAP_PROCESS
	//	------------------------
	//	Process the users' selections into an actionable list
	//
	function usermap_process() {	
		global $wpuAbs, $phpEx, $phpbb_root_path, $wpSettings, $db, $template;
		
		$this->page_title = 'L_MAP_TITLE';
		$this->tpl_name = 'acp_wp_united';
		
		// set the page section to show
		$template->assign_block_vars('switch_usermap_process', array());      	

		$lastRow =  (int) request_var('numrows', 0);
		$nextStart = (int) request_var('start', 0);
		$numPerPage = (int)request_var('wpumapperpage', 50);
		$paged = (int) request_var('paged', 0);
		
		for ( $procRow = 0; $procRow <= $lastRow; $procRow++ ) { 
			$wpID =  (int) request_var('wpID' . $procRow, 0);
			$wpUN =  request_var('wpUN' . $procRow, '');
			$typedName =  request_var('pName' . $procRow, '');
			$action =  request_var('selAction' . $procRow, '');
			$alreadyUN =  request_var('alreadyUN' . $procRow, '');
			$alreadyID =  (int) request_var('alreadyID' . $procRow, 0);
			$mustBreak = request_var('mustBrk' . $procRow, '');
			if ($action == 'Int') {
				if ( (!empty($alreadyUN)) && (!empty($alreadyID)) ) {
					if ( ($alreadyUN == $typedName) ) {
						$action = "NO_CHANGE";
					} else {
						$action = "BREAK_THEN_INTEGRATE";
						$actionList[] = array(
						'action' => 'break',
						'wpID' => $wpID,
						'wpUN' => $wpUN,
						'text' => sprintf($wpuAbs->lang('L_MAP_BREAKWITH'), $alreadyUN)
						);
						$actionList[] = array(
						'action' => 'integrate',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'typed' => $typedName,
						'text' => sprintf($wpuAbs->lang('L_MAP_INTWITH'), $typedName)
						);
					}
				} else {
					if ( $mustBreak == 'TRUE' ) {
						$action = "SORT_OUT_MULTIPLES";
						$actionList[] = array(
						'action' => 'break',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'text' => $wpuAbs->lang('L_MAP_BREAKEXISTING')
						);
						$actionList[] = array(
						'action' => 'integrate',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'typed' => $typedName,
						'text' => sprintf($wpuAbs->lang('L_MAP_INTWITH'), $typedName)
						);
					} else {
						$action = "INTEGRATE_NEW";
						$actionList[] = array(
						'action' => 'integrate',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'typed' => $typedName,
						'text' => sprintf($wpuAbs->lang('L_MAP_INTWITH'), $typedName)
						);
					}
				}	
			}
			if ($action == 'Brk') {	
				if ( (empty($alreadyUN)) && (empty($alreadyID)) ) {
					//Break only if there is an error - otherwise leave unintegrated
					if ( $mustBreak == 'TRUE' ) {
						$action = "BREAK";
						$actionList[] = array(
						'action' => 'break',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'text' => $wpuAbs->lang('L_MAP_BREAKMULTI')
						);
					} else {
						$action = "NO_CHANGE";
					}
				} else {
					$action = 'BREAK';
					$actionList[] = array(
					'action' => 'break',
					'wpID' => $wpID,
					'wpUN' => $wpUN,							
					'text' => sprintf($wpuAbs->lang('L_MAP_BREAKWITH'), $alreadyUN)
					);
				}
			}
			if ($action == 'Del') {
				if ( (empty($alreadyUN)) && (empty($alreadyID)) ) {
					$action = 'DELETE';
					$actionList[] = array(
					'action' => 'delete',
					'wpID' =>$wpID,
					'wpUN' => $wpUN,							
					'text' => $wpuAbs->lang('L_MAP_DEL_WP')
					);
				} else {
					$action = 'BREAK_THEN_DELETE';
					$actionList[] = array(
					'action' => 'break',
					'wpID' => $wpID,
					'wpUN' => $wpUN,							
					'text' => sprintf($wpuAbs->lang('L_MAP_BREAKWITH'), $alreadyUN)							
					);
					$actionList[] = array(
					'action' => 'delete',
					'wpID' =>$wpID,
					'wpUN' => $wpUN,							
					'text' => $wpuAbs->lang('L_MAP_DEL_WP')							
					);
				}
			}
			
			if ($action == 'Cre') {
				$action = 'CREATE_PHPBB';
				$actionList[] = array(
				'action' => 'createP',
				'wpID' =>$wpID,
				'wpUN' => $wpUN,
				'typed' => $typedName,
				'text' => $wpuAbs->lang('L_MAP_CREATE_P')
				);
			}
		}
		if ( isset($actionList) ) { 
			$intro_para = $wpuAbs->lang('L_MAP_ACTIONSINTRO');
			$ctr = 0;
			$error = FALSE;
			foreach ((array)$actionList as $doThis) { 
				$col = 'green';  $errText = '';
				if ($doThis['action'] == 'integrate') { 
					//Check that phpBB user exists and is not already integrated
					$sql = 	"SELECT user_wpuint_id, user_id FROM " . USERS_TABLE .
					" WHERE username = '" . $db->sql_escape($doThis['typed']) . "'";
					if (!$pCheck = $db->sql_query($sql)) {
						$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_DBErr_Retrieve'), $wpuAbs->lang('L_MAP_CANTCONNECTP'), __LINE__, __FILE__, $sql);
					}
					if (!($pCheckResults = $db->sql_fetchrow($pCheck)))  {
						$error = TRUE;
						$col = 'red';
						$errText =  ' ' . $wpuAbs->lang('L_MAP_PNOTEXIST');							
					} else {
						if ( !empty($pCheckResults['user_wpuint_id']) ) {
							if (   !(($pCheckResults['user_wpuint_id'] == $doThis['wpID']) && empty($doThis['alreadyID']) && empty($doThis['alreadyUN'])) ) {
								$error = TRUE;
								$col = 'red';
								$errText =   ' ' . $wpuAbs->lang('L_MAP_ERR_ALREADYINT');
							}
						} else {
							$pID = $pCheckResults['user_id'];
						}
					}
				} 
				$template->assign_block_vars('switch_usermap_process.actionlist_row', array(
					'S_ACTION_COLOUR' => $col,	
					'ACTION_USERNAME' => $doThis['wpUN'],
					'ACTION_DESC' => $doThis['text'],
					'ERROR_TEXT' => $errText,
					'LINE_COUNTER' => $ctr,
					'MAP_ACTION' => $doThis['action'],
					'MAP_WP_ID' => $doThis['wpID'],
					'MAP_TYPEDNAME' => $doThis['typed'],
					'MAP_P_ID' => $pID,
					'MAP_P_USERNAME' => $doThis['pUN'],
				));
		
				$ctr++;
			}
			if (!$error) { 
				$close_para = $wpuAbs->lang('L_MAP_ACTIONSEXPLAIN1');
				$template->assign_block_vars('switch_usermap_process.switch_doactions', array(
					'L_PROCESS_ACTIONS' => $wpuAbs->lang('L_PROCESS_ACTIONS'),
					'S_WPMAP_ACTION' => append_sid("index.$phpEx?i=wp_united"),
					'NUM_ROWS' => $ctr - 1,
				));
			} else {
				$close_para = $wpuAbs->lang('L_MAP_ERR_GOBACK'); 
			}
		} else {
			$intro_para = $wpuAbs->lang('L_MAP_NOWTTODO');
		} 
		
		$passVars = array(	
			'L_MAP_TITLE'  =>	$wpuAbs->lang('L_MAP_TITLE'),
			'L_MAP_ACTINTRO' => $intro_para,
			'L_MAP_ACT_CLOSEPARA' => $close_para,
			'S_NEXTSTART' => $nextStart,
			'S_NUMPERPAGE' => $numPerPage,
		);
		$this->showPage($passVars,0);	
	}
	
	//
	//	USERMAP_PERFORM
	//	------------------------
	//	Process each of the actions in the list.
	//
	function usermap_perform() {	
		global $wpuAbs, $phpEx, $phpbb_root_path, $wpSettings, $db, $template;
		
		$this->page_title = 'L_MAP_TITLE';
		$this->tpl_name = 'acp_wp_united';

		// set the page section to show
		$template->assign_block_vars('switch_usermap_perform', array()); 	
			
		//Get integration settings
		$wpSettings = get_integration_settings();
		if ( ($wpSettings == FALSE)	|| ($wpSettings['wpPath'] == '') ) {
			$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('WP_DBErr_Gen'), $wpuAbs->lang('L_WP_NO_SETTINGS'), __LINE__, __FILE__, $sql);
		}		
		
		$lastAction= (int) request_var('numrows', 0);
		$nextStart = (int) request_var('start', 0);
		$numPerPage = (int)request_var('wpumapperpage', 50);
		$paged = (int) request_var('paged', 0);

		// Enter WordPress and pull user data
		global $wpdb, $wpUtdInt, $wpuCache;
		require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);
		$wpuCache = WPU_Cache::getInstance();
		require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
		$wpUtdInt = WPU_Integration::getInstance();
		define('USE_THEMES', FALSE);
		if ($wpUtdInt->can_connect_to_wp()) {
			$wpUtdInt->enter_wp_integration();
			eval($wpUtdInt->exec()); 
			$wpUtdInt->switch_db('TO_P');
			if (file_exists($wpSettings['wpPath'] .'wp-admin/includes/user.php')) {  //WP >= 2.3
				require_once($wpSettings['wpPath'] .'wp-admin/includes/user.php');
			} else {
				require_once($wpSettings['wpPath'] .'wp-admin/admin-db.php'); //WP < 2.3
			}
			require_once($phpbb_root_path . 'wp-united/wpu-actions.' . $phpEx);
			
			
			for ( $procAction = 0; $procAction <= $lastAction; $procAction++ ) {
				$status_text = '';
				$actionName = request_var('actname'.$procAction, '') ;
				if ( !empty($actionName) ) {
					$wpID = (int) request_var('wpID'.$procAction, 0);				
					$pID = (int)request_var('pID'.$procAction, 0);		
					$typedName = request_var('typedName'.$procAction, '');	
					switch ($actionName) {
						case 'break':
							if ( !empty($wpID) ) {
								$sql = 'UPDATE ' . USERS_TABLE .
									" SET user_wpuint_id = NULL 
									WHERE user_wpuint_id = $wpID";
								if (!$pDel = $db->sql_query($sql)) {
									$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('L_MAP_COULDNT_BREAK'), $wpuAbs->lang('L_DB_ERROR'), __LINE__, __FILE__, $sql);
								}									
								$status_text = '<li>'. sprintf($wpuAbs->lang('L_MAP_BROKE_SUCCESS'), $wpID) . '</li>';
							} else {
								$status_text = '<li>' . $wpuAbs->lang('L_MAP_CANNOT_BREAK') . '</li>';
							}
						break;
						case 'integrate':
							if ( (!empty($wpID)) && (!empty($pID))  ) {
								$sql = 'UPDATE ' . USERS_TABLE .
									" SET user_wpuint_id = $wpID 
									WHERE user_id = $pID";
								if (!$pInt = $db->sql_query($sql)) {
									$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('L_MAP_COULDNT_INT'), $wpuAbs->lang('L_DB_ERROR'), __LINE__, __FILE__, $sql);
								}
								// Sync profiles
								$sql = 	"SELECT *
												FROM " . USERS_TABLE . " 
												WHERE user_id = $pID";
								if (!$pUserData = $db->sql_query($sql)) {
									$wpuAbs->err_msg(GENERAL_ERROR, $wpuAbs->lang('L_MAP_COULDNT_INT'), $wpuAbs->lang('L_DB_ERROR'), __LINE__, __FILE__, $sql);
								}
								$data = $db->sql_fetchrow($pUserData);
								$db->sql_freeresult($pUserData);
								$password = $data['user_password'];
								if(substr($password, 0, 3) == '$H$') {
									$password = substr_replace($password, '$P$', 0, 3);
								}
								$wpu_newDetails = array(
									'user_id' 		=>  	$pID,
									'username' 		=>  	(isset($data['username'])) ? $data['username'] : '',
									'user_email' 		=> 	(isset($data['user_email'])) ? $data['user_email'] : '',
									'user_password' 	=> 	(isset($password)) ? $password : '',
									'user_aim'		=> 	(isset($data['user_aim'])) ? $data['user_aim'] : '',
									'user_yim'		=> 	(isset($data['user_yim'])) ? $data['user_yim'] : '',
									'user_jabber'		=> 	(isset($data['user_jabber'])) ? $data['user_jabber'] : '',
									'user_website'		=> 	(isset($data['user_website'])) ? $data['user_website'] : '',							
									'user_avatar' 			=> 	(isset($data['user_avatar'])) ? $data['user_avatar'] : '',
									'user_avatar_type'		=> 	(isset($data['user_avatar_type'])) ? $data['user_avatar_type'] : '',
									'user_avatar_width'		=> 	(isset($data['user_avatar_width'])) ? $data['user_avatar_width'] : '',
									'user_avatar_height'		=> 	(isset($data['user_avatar_height'])) ? $data['user_avatar_height'] : ''							
								);
								$wpUtdInt->switch_db('TO_W');
								$wpUsrData = get_userdata($wpID);
								$wpUpdateData = $wpUtdInt->check_details_consistency($wpUsrData, $wpu_newDetails);
	
								$wpUtdInt->switch_db('TO_P');
								$status_text = '<li>' . sprintf($wpuAbs->lang('L_MAP_INT_SUCCESS'), $wpID, $pID) . '</li>';	
							} else {
								$status_text = '<li>' . $wpuAbs->lang('L_MAP_CANNOT_INT') . '</li>';
							}							
						break;
						case 'delete':
							$wpUtdInt->switch_db('TO_W');
							if ( !empty($wpID) ) {
								wp_delete_user($wpID, $reassign = '0');
								$status_text = '<li>' . sprintf($wpuAbs->lang('L_MAP_WPDEL_SUCCESS'), $wpID) . '</li>';
								$nextStart = $nextStart - 1;
							} else {
								$status_text = '<li>' . $wpuAbs->lang('L_MAP_CANNOT_DEL') . '</li>';
							}
							$wpUtdInt->switch_db('TO_P');
						break;
						case 'createP':
							if (!$wpID || !$typedName) {
								$status_text = '<li>' . $wpuAbs->lang('L_MAP_CANNOT_CREATEP_ID') . '</li>';
							} else {
								$wpUtdInt->switch_db('TO_W');
								$wpUsr = get_userdata($wpID);
								$wpUtdInt->switch_db('TO_P');
								$password = $wpUsr->user_pass;
								if(substr($password, 0, 3) == '$P$') {
									$password = substr_replace($password, '$H$', 0, 3);
								}								
								if ($wpuAbs->insert_user($typedName, $password, $wpUsr->user_email , $wpID)) {
									$status_text = '<li>'. sprintf($wpuAbs->lang('L_MAP_CREATEP_SUCCESS'), $typedName) . '</li>';
								} else {
									$status_text = '<li>' . $wpuAbs->lang('L_MAP_CANNOT_CREATEP_NAME') . '</li>';
								}
							}
						break;
						default;
							$wpuAbs->err_msg(sprintf($wpuAbs->lang('L_MAP_INVALID_ACTION'), $procAction));
						break;
					}
				} else {
					$wpuAbs->err_msg(sprintf($wpuAbs->lang('L_MAP_EMPTY_ACTION'), $procAction)); 
				}
				$template->assign_block_vars('switch_usermap_perform.performlist_row', array(
					'LIST_ITEM' => $status_text
				));
			
			}
			
		} else {
			die($wpuAbs->lang('L_MAP_CANT_CONNECT'));
		}
		
		if (!empty($paged)) {
			$template->assign_block_vars('switch_usermap_perform.switch_paged', array(
				'L_MAP_NEXTPAGE' => $wpuAbs->lang('L_MAP_NEXTPAGE')
			));
		} else {
			$template->assign_block_vars('switch_usermap_perform.switch_unpaged', array(
				'L_MAP_FINISHED' => sprintf($wpuAbs->lang('L_MAP_FINISHED'), '<a href="' . append_sid("index.$phpEx?i=wp_united&amp;mode=index") . '">', '</a>', '<a href="' . append_sid("index.$phpEx?i=wp_united&amp;mode=usermap") . '">', '</a>' )
			));
		}
		
		$passVars = array(	
			'L_MAP_TITLE'  =>	$wpuAbs->lang('L_MAP_TITLE'),
			'S_WPMAP_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'L_MAP_PERFORM_INTRO' => $wpuAbs->lang('L_MAP_PERFORM_INTRO'),
			'S_NEXTSTART' => $nextStart,
			'S_NUMPERPAGE' => $numPerPage,
		);		
		$this->showPage($passVars, 0);			
	}
	
	
	
	

	
	// 
	// 	SHOW THE PAGE
	//	------------------------
	//	Assigns vars & block vars, and displays the page
	//
	function showPage($assignVars, $assignBlockVars) {

		global $template, $showFooter; 
		
		// assign template variables
		if ( !($assignVars === 0)) {
			$template->assign_vars($assignVars);
		}
		if ( !($assignBlockVars === 0)) {
			foreach ($assignBlockVars as $arrayName => $arrayOfVars) {
				$template->assign_block_vars($arrayName, $arrayOfVars);
			}
		}
		if ( $showFooter ) {
			$template->assign_block_vars('switch_show_footer', array());
		}
	}    
	
	//
	//	ADD TRAILING SLASH 
	//	--------------------------------
	//	Adds a traling slash to a string if one is not already present.
	//
	function add_trailing_slash($path) {
		return ( $path[strlen($path)-1] == "/" ) ? $path : $path . "/";
	}

	//
	//	ADD HTTP:// 
	//	-----------------
	//	Adds http:// to the URL if it is not already present
	//	TODO: Check if https:// is already present, too!!
	//
	function add_http($path) {
		return ( strpos($path, "http://") === FALSE ) ? "http://" . $path : $path;
	}
	
	function clean_path($value) {
		$value = trim($value);
		$value = str_replace('\\', '/', $value);
		$value = (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
		return $value;
	}
	//
	//	GET PAGE NUMBER
	//	--------------------------
	//	grabs the number from a string.
	//	Could be done very simply but we cannot guarantee where the numbers will occur, due to language differences.
	//
	function get_page_number($string) {

		preg_match('{(\d+)}', $string, $pResult); 
		return $pResult[1];
		
	}

	//
	//	TEST IF SUPPLIED PAGE EXISTS
	//	---------------------------------------------
	//	Uses cURL to see if the page exists. 
	//	Returns TRUE unless page times out or returns HTTP 404.
	//
	function uri_exists($wpUri) {

		//Test to see if cURL is available.
		//$curlAvail = @curl_version();
		if ( !function_exists('curl_exec') ) {
			return 0;
		}
		
		$testPage = curl_init(); 
		$cTimeout = 10;
		// Just get the HTTP code
		curl_setopt($testPage, CURLOPT_URL, $wpUri);
		curl_setopt($testPage, CURLOPT_HEADER, true);
		curl_setopt($testPage, CURLOPT_NOBODY, true);
		curl_setopt($testPage, CURLOPT_CONNECTTIMEOUT, $cTimeout);
		curl_setopt($testPage, CURLOPT_RETURNTRANSFER, true);
		$testData = curl_exec($testPage);
		curl_close($testPage);
		preg_match_all("/HTTP\/1\.[1|0]\s(\d{3})/",$testData,$matches);
		$code = end($matches[1]);
		
		if ( (!empty($testData)) && ($code!=404) ) {
			return TRUE;
		} else {
			return  FALSE;
		}	
	}	


	//
	//	FIGURE OUT FILESYSTEM PATH OF WORDPRESS
	//	-------------------------------------------------------------------
	//	Gets the filepath & URI of the current script, and then compares it to a provided WordPress URI 
	//	to calculate the difference. Works on Unix-style & Windows servers.
	//
	function detect_path_from_uri($wpUri) {

		$hostName = "http://".$_SERVER['HTTP_HOST'];
		
		$slash = strstr( PHP_OS, "WIN") ? "\\" :  "/"; 	// this sets the sytem / or \ 
		$docRoot = $_SERVER['DOCUMENT_ROOT'];
		$AtheFile = explode ("/", $wpUri);
		$theFileName = array_pop($AtheFile);
		$AwimpyPathWWW = explode ("/", $hostName);
		$AtheFilePath = array_values (array_diff ($AtheFile, $AwimpyPathWWW));
		if($AtheFilePath){
			$theFilePath = $slash.implode($slash, $AtheFilePath).$slash.$theFileName;
		} else {
			$theFilePath = implode($slash, $AtheFilePath).$slash.$theFileName;
		}
		$wpPath = $docRoot . $theFilePath;
		$wpPath = str_replace($slash.$slash, $slash, $wpPath); // removes pointless double-slashes
		$wpPath = $this->clean_path($wpPath); //reverses slashes for Windows servers.
		return $wpPath;
	}


	//
	//	CHECK TO SEE IF WORDPRESS INSTALL EXISTS
	//	--------------------------------------------------------------------
	//	Simple func that tries to access wp-settings.php (a core WordPress file) locally.
	//
	function wordpress_exists($wpPath) {
		$wpPath = $this->add_trailing_slash($wpPath);
		$wpPath = str_replace('http://', '', $wpPath); // urs sometimes return true on php 5.. this makes sure they don't.
		$pathToWpSettings = $wpPath . "wp-settings.php";
		return (file_exists($pathToWpSettings));
	}


	//
	//	SEND "AJAX" XML
	//	-----------------------------------
	//	Returns an XML message
	//	Used by the wizard for asynchronous communication with the server
	//
	function send_ajax ($xmlData, $title="results") {
		
		global $template, $showHeader, $showFooter; //$showPage;
		$this->tpl_name = 'acp_wp_united';
		// set the test title attribute of the main xml tag
		$template->assign_vars(array(
			'L_WP_XML_TESTNAME' => $title,
			'L_WP_XML_DECL' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		));
		
		// set the section of the template and put the XML data in a loop
		$template->assign_block_vars('switch_xml_response', array());

		//write out the XML data to be parsed in a loop
		foreach ($xmlData as $tagName => $tagValue) {
			$template->assign_block_vars('switch_xml_response.xml_data', array(
				'WP_XML_TAGNAME' => $tagName,
				'WP_XML_TAGVALUE' => $tagValue
			));
		}
		
		//use xml doctye defined in template
		$showHeader = FALSE;
		$showFooter = FALSE;
		define('HEADER_INC', FALSE);
		// set header. Need no-cache headers too -- they are set by phpBB.
		header('Content-Type: application/xml'); 
		header('Cache-Control: private, no-cache="set-cookie"');
		header('Expires: 0');
		header('Pragma: no-cache');

		//$showPage = FALSE;
	}

	//
	//	ADD ACP MODULE
	//	-----------------------------------
	//	Auto-creates an ACP module
	//
	function add_acp_module(&$module_data) {
		global $cache;

		$acp_modules = new acp_modules();
		$acp_modules->module_class = $module_data['module_class'];

		$mod_id = $this->module_exists($module_data['module_langname'], $module_data['parent_id']);

		if ( !empty($mod_id) ) {
			$module_data['module_id'] = $mod_id;
		}

		// Adjust auth row if not category
		if ($module_data['module_basename'] && $module_data['module_mode']) {
			$fileinfo = $acp_modules->get_module_infos($module_data['module_basename']);
			$module_data['module_auth'] = $fileinfo[$module_data['module_basename']]['modes'][$module_data['module_mode']]['auth'];
		}

		$errors = $acp_modules->update_module_data($module_data, TRUE);
		if (!sizeof($errors)) {
			$acp_modules->remove_cache_file();
		} else {
			trigger_error('Could not add module!<br />' . implode('<br />', $errors));
		}

		$cache->destroy('_modules_');
		$cache->destroy('_sql_', MODULES_TABLE);

		return $module_data['module_id'];
	}
	//
	//	MODULE EXISTS?
	//	-----------------------------------
	//	Returns module ID if module exists
	//
	function module_exists($modName, $parent = 0) {
		global $db;
		$sql = "SELECT module_id FROM " . MODULES_TABLE . "
			WHERE parent_id = $parent
			AND module_langname = '$modName'";
		if (!$result = $db->sql_query($sql)) {
			trigger_error('Could not access modules table');
		}
		//there could be a duplicate module, but screw it
		if ( $row = $db->sql_fetchrow($result) ) {
			if ( !empty($row['module_id']) ) {
				return $row['module_id'];
			}
		}
		return false;
	}

	//
	//	REMOVE MODULES
	//	-----------------------------------
	//	Removes WP-United ACP modules. Note: Only removes them if they're in the expected place in the tree.
	//
	function remove_modules() {

		$modules_to_delete = array(
			 'ACP_WPU_CATOTHER' => array('ACP_WPU_UNINSTALL', 'ACP_WPU_RESET', 'ACP_WPU_DEBUG'),
			 'ACP_WPU_CATSUPPORT' =>array( 'ACP_WPU_DONATE'),
			 'ACP_WPU_CATMANAGE' => array('ACP_WPU_USERMAP', 'ACP_WPU_PERMISSIONS'), 
			 'ACP_WPU_CATSETUP' => array('ACP_WPU_DETAILED', 'ACP_WPU_WIZARD'),
			 'ACP_WPU_CATMAIN' => array('ACP_WPU_MAINTITLE')
		);
		$cats = array('ACP_WPU_CATOTHER', 'ACP_WPU_CATSUPPORT', 'ACP_WPU_CATMANAGE', 'ACP_WPU_CATSETUP', 'ACP_WPU_CATMAIN');
		$acp_modules = new acp_modules();
		$acp_modules->module_class = 'acp';
		$mainTab = $this->module_exists('ACP_WP_UNITED' , 0);
		if ($mainTab) {
			//remove modules
			foreach ($modules_to_delete as $cat => $modules) { 
				if ($parent = $this->module_exists($cat, $mainTab)) { 
					foreach ($modules as $module) {
						if ($id = $this->module_exists($module, $parent)) { 
							$errors .= $acp_modules->delete_module($id); 
						}
					}
					//remove cat
					$errors .= $acp_modules->delete_module($parent);
				}
			}
			//remove main tab
			$acp_modules->delete_module($mainTab); 
		}
		$acp_modules->remove_cache_file();
		return $errors;
	}

	//
	//	GET ACL OPTION IDS
	//	-----------------------------------
	//	Based on fn by Poyntesm Poyntesm, http://www.phpbb.com/community/viewtopic.php?f=71&t=545415&p=3026305
	//
	function get_acl_option_ids($auth_options) {
	   global $db;

	   $data = array();
	   $sql = "SELECT auth_option_id
	      FROM " . ACL_OPTIONS_TABLE . "
	      WHERE " . $db->sql_in_set('auth_option', $auth_options) . "
	      GROUP BY auth_option_id";
	   $result = $db->sql_query($sql);
	   while ($row = $db->sql_fetchrow($result))  {
	      $data[] = $row['auth_option_id'];
	   }
	   $db->sql_freeresult($result);

	   return $data;
	}

}

?>
