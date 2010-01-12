<?php
/** 
*
* WP-United ACP panels
*
* @package WP-United
* @version $Id: wp-united.php,v 0.8.0 2009/12/10 John Wells (Jhong) Exp $
* @copyright (c) 2006-2009 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_wp_united {
	var $u_action;
	var $new_config;
	function main($id, $mode) {
		global $db, $user, $auth, $template, $wizShowError, $wizErrorMsg, $inWizard, $numWizardSteps, $showFooter;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		$user->add_lang('mods/admin_wp-united');
		require_once($phpbb_root_path . 'wp-united/mod-settings.' . $phpEx);
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
		
		/**
		 * MAIN PROGRAM LOGIC
		 * FIGURE OUT WHAT WAS CLICKED, AND SHOW APPROPRIATE ACP PAGE
		 */
		if ( isset($_POST['Submit']) ) {

			$submit = request_var('Submit', ''); 
			// Data has been submitted from the main page, or the save settings page.
			switch ( $submit ) {
			
			case $user->lang['WP_SubmitWiz']:
			// The "Launch Setup Wizard" button was clicked.
			$inWizard = TRUE;
			$this->step1_show();
				break;
				
			case $user->lang['SUBMIT']:
			// Process data from "configuration-on-a-page".
				$this->settings_process(); 
				break;
				
			case $user->lang['WP_SUBMITDET']:
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
			case $user->lang['WP_URI_Test']:
				// Test supplied URI and return data normally, since AJAX is unavailable
				$returnMessageUri = $this->testUri(FALSE);
				$this->step1b_show($returnMessageUri);
				break;
			case $user->lang['WP_Path_Test']: 
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
			case $user->lang['MAP_BEGIN'];
			case $user->lang['MAP_NEXTPAGE'];    
			case $user->lang['MAP_SKIPNEXT'];
			case $user->lang['MAP_CHANGE_PERPAGE'];
				$this->usermap_main();
				break;
			case $user->lang['MAP_PROCESS']:
				$this->usermap_process();
				break;
			case $user->lang['PROCESS_ACTIONS']:
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
		global $template, $user, $phpEx, $ignorePrompt, $phpbb_root_path;
		$this->page_title = $user->lang['ACP_WPU_INDEX_TITLE'];
		$this->tpl_name = 'acp_wp_united';
		
		//get integration package settings
		$wpSettings = $this->get_settings_set_defaults();

		//The captions and order of the buttons on the page changes depending on if the package has been installed prior
		if ( $wpSettings['installLevel'] < 10 ) { //not yet installed. Recommend wizard.
			$introAdd = $user->lang['WP_Main_IntroFirst'];
			$button1Title = $user->lang['WP_Wizard_Title'];
			$button1Explain = $user->lang['WP_Wizard_ExplainFirst'];
			$button1Value = $user->lang['WP_SubmitWiz'];
			$button2Title = $user->lang['WP_Detailed_Title'];
			$button2Explain = $user->lang['WP_Detailed_ExplainFirst'];
			$button2Value = $user->lang['WP_SUBMITDET'];
			$recommended = $user->lang['WP_Recommended'];
			$mode1 = 'wizard';
			$mode2 = 'detailed';
		} else { // installed. Show settings page first.
			$introAdd = $user->lang['WP_Main_IntroAdd'];
			$button1Title = $user->lang['WP_Detailed_Title'];
			$button1Explain = $user->lang['WP_Detailed_Explain'];
			$button1Value = $user->lang['WP_SUBMITDET'];
			$button2Title = $user->lang['WP_Wizard_Title'];
			$button2Explain = $user->lang['WP_Wizard_Explain'];
			$button2Value = $user->lang['WP_SubmitWiz'];
			$mode1 = 'detailed';
			$mode2 = 'wizard';
			$recommended = "";
		}
		
		// set the page section to show
		$template->assign_block_vars('switch_main_page', array());
		
		//If this is an upgrade, and that upgrade requires an install script, throw up a message
		if ($wpSettings['wpuVersion'] != $user->lang['WPU_Default_Version']) {
			//clear the phpBB cache
			global $cache;
			$cache->purge();
				
			$ver = $user->lang['WPU_Not_Installed'];
			if ( ($wpSettings['upgradeRun'] == 1) && ($wpSettings['installLevel'] >= 5) && (!$ignorePrompt) ) {
				$template->assign_block_vars('switch_main_page.switch_upgrade_warning', array(
				'L_WP_UPGRADE_TITLE' => $user->lang['WPU_Must_Upgrade_Title'],
				'L_WP_UPGRADE_EXPLAIN1' => sprintf($user->lang['WPU_Must_Upgrade_Explain1'], '<a href="'. append_sid('wpu_usermap.' .$phpEx) .'" title="upgrade script" style="color: #fff;">', '</a>'),
				'L_WP_UPGRADE_EXPLAIN2' => sprintf($user->lang['WPU_Must_Upgrade_Explain2'], '<a href="'. append_sid('admin_wordpress.' .$phpEx .'?ignore=1') .'" title="ignore this prompt" style="color: #fff;">', '</a>')
				));
			}
		} else {
			$ver = $wpSettings['wpuVersion'];
		}

		// pass strings	
		$template->assign_vars(array(
			'L_WPMAIN_INTROADD' => $introAdd,
			'S_WPMAIN_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),
			'L_WPB1_TITLE' => $button1Title,
			'L_RECOMMENDED' => $recommended,
			'L_WPB1_EXPLAIN' => $button1Explain,
			'L_SUBMITB1' => $button1Value,
			'L_WPB2_TITLE' => $button2Title,
			'L_WPB2_EXPLAIN' => $button2Explain,
			'L_SUBMITB2' => $button2Value,
			'S_MODE_1' => $mode1,
			'S_MODE_2' => $mode2,
			'L_WP_VERSION' => sprintf($user->lang['WP_Version_Text'], $ver)
		));


		//show user mapping link, if package is handling WP logins
		if ( (!empty($wpSettings['integrateLogin'])) && (!empty($wpSettings['wpUri'])) ) {
			$mapLink = append_sid('wpu_usermap.' .$phpEx);
			$template->assign_block_vars('switch_main_page.switch_show_wpmap', array(
				'L_MAPLINK_EXPLAIN' => $user->lang['WP_MapLink_Explain'],
				'U_MAPLINK' => append_sid("index.$phpEx?i=wp_united"),
				'L_MAPLINK_TITLE' => $user->lang['WP_MapLink_Title'],
				'L_MAPLINK' => $user->lang['WP_MapLink']
			));
		}
		
		//Check that wp-united/cache is writable
		if(!is_writable($this->add_trailing_slash($phpbb_root_path) . "wp-united/cache/")) {
			$template->assign_block_vars('switch_main_page.switch_cache_unwritable', array(
				'L_CACHE_UNWRITABLE' => $user->lang['WPU_Cache_Unwritable']
			));
		}
		
		//Check that wpu-install has been deleted
		if(file_exists($this->add_trailing_slash($phpbb_root_path) . "wpu-install." . $phpEx)) { 
			$template->assign_block_vars('switch_main_page.switch_install_file_exists', array(
				'L_INSTALL_EXISTS' => $user->lang['WPU_Install_Exists']
			));
		}
				
		
		//show the page
		$this->showPage();

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
		global $template, $user, $phpEx, $errMsg, $db;
		$this->page_title = $user->lang['ACP_WPU_DETAILED'];
		$this->tpl_name = 'acp_wp_united';
		

		$wpSettings = ( !empty($errMsg) ) ? $wpErrSettings : $this->get_settings_set_defaults();

		// set padding text boxes
		$padding = ($wpSettings['phpbbPadding'] == 'NOT_SET') ? array('','','','') : explode('-', $wpSettings['phpbbPadding']);
		
				
		// set the page section to show
		$template->assign_block_vars('switch_settings_page', array());
		
		//Prepare forum list for forced cross-posting		
		$template->assign_block_vars('switch_settings_page.xpost_forumlist', array(
			'S_XPOST_VALUE' => -1,
			'S_XPOST_SEL' => ($wpSettings['xpostforce'] == -1) ? 'selected="selected"' : '',
			'S_XPOST_DESC' => "----{$user->lang['WPU_DISABLED']}----"
		));		
		$sql = 'SELECT forum_id, forum_name FROM ' . FORUMS_TABLE . ' WHERE ' .
			'forum_type = ' . FORUM_POST;
		if ($result = $db->sql_query($sql)) {
			while ( $row = $db->sql_fetchrow($result) ) {
				$template->assign_block_vars('switch_settings_page.xpost_forumlist', array(
					'S_XPOST_VALUE' => $row['forum_id'],
					'S_XPOST_SEL' => ($wpSettings['xpostforce'] == (int)$row['forum_id']) ? 'selected="selected"' : '',
					'S_XPOST_DESC' => $row['forum_name']
				));
			}
		}
		
		$template->assign_vars(array(
			'S_WP_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'S_WPURI' => $wpSettings['wpUri'],
			'S_WPPATH' => $wpSettings['wpPath'],
			'S_USEFORUMPG_ENABLE' => ($wpSettings['useForumPage']) ? 'checked="checked"' : '',
			'S_USEFORUMPG_DISABLE' => ($wpSettings['useForumPage']) ? '': 'checked="checked"',
			'S_WPLOGIN_ENABLE' =>  ( $wpSettings['integrateLogin'] ) ? 'checked="checked"' : '',
			'S_WPLOGIN_DISABLE' => ( $wpSettings['integrateLogin'] ) ? '' : 'checked="checked"',
			'S_BLOGSURI' => $wpSettings['blogsUri'],		
			'S_WPXPOST_ENABLE' => ( $wpSettings['xposting'] ) ? 'checked="checked"' : '',
			'S_WPXPOST_DISABLE' => ( $wpSettings['xposting'] ) ? '' : 'checked="checked"',
			'S_WPXAL_ENABLE' => ( $wpSettings['xpostautolink'] ) ? 'checked="checked"' : '',
			'S_WPXAL_DISABLE' => ( $wpSettings['xpostautolink'] ) ? '' : 'checked="checked"',
			'S_WPINP' => ($wpSettings['showHdrFtr'] == 'FWD') ? 'checked="checked"' : '',
			'S_PINWP' => ($wpSettings['showHdrFtr'] == 'REV') ? 'checked="checked"' : '',
			'S_PW_NONE' => ($wpSettings['showHdrFtr'] == 'NONE') ? 'checked="checked"' : '',
			'S_CSSM_ON' => ( $wpSettings['cssMagic'] ) ? 'checked="checked"' : '',
			'S_CSSM_OFF' => ( $wpSettings['cssMagic'] ) ? '' : 'checked="checked"',	
			'S_TV_ON' =>  ( $wpSettings['templateVoodoo'] ) ? 'checked="checked"' : '',
			'S_TV_OFF' => ( $wpSettings['templateVoodoo'] ) ? '' : 'checked="checked"',
			'S_PLUGIN_FIXES_YES' => ( $wpSettings['pluginFixes'] ) ? 'checked="checked"' : '',
			'S_PLUGIN_FIXES_NO' =>  ( $wpSettings['pluginFixes'] ) ? '' : 'checked="checked"',	
			'S_WPSIMPLE_YES' => ( $wpSettings['wpSimpleHdr'] ) ? 'checked="checked"' : '',
			'S_WPSIMPLE_NO' =>  ( $wpSettings['wpSimpleHdr'] ) ? '' : 'checked="checked"',					
			'S_WP_PADTOP' => ($padding[0] != '') ? (int)$padding[0] : '',
			'S_WP_PADRIGHT' => ($padding[1] != '') ? (int)$padding[1] : '',
			'S_WP_PADBOTTOM' => ($padding[2] != '') ? (int)$padding[2] : '',
			'S_WP_PADLEFT' => ($padding[3] != '') ? (int)$padding[3] : '',
			'S_WPPAGE' => $wpSettings['wpPageName'],
			'S_WPDTD_ENABLE' => ( $wpSettings['dtdSwitch'] ) ? 'checked="checked"' : '',
			'S_WPDTD_DISABLE' => ( $wpSettings['dtdSwitch'] ) ? '' : 'checked="checked"',
			'S_WPOWNBLOGS_ENABLE' => ( $wpSettings['usersOwnBlogs'] ) ? 'checked="checked"' : '',
			'S_WPOWNBLOGS_DISABLE' => ( $wpSettings['usersOwnBlogs'] ) ? '' : 'checked="checked"',
			'S_WPBTNSPROF_ENABLE' => ( $wpSettings['buttonsProfile'] ) ? 'checked="checked"' : '',
			'S_WPBTNSPROF_DISABLE' => ( $wpSettings['buttonsProfile'] ) ? '' : 'checked="checked"',
			'S_WPBTNSPOST_ENABLE' => ( $wpSettings['buttonsPost'] ) ? 'checked="checked"' : '',
			'S_WPBTNSPOST_DISABLE' => ( $wpSettings['buttonsPost'] ) ? '' : 'checked="checked"',
			'S_WPSTYLESWITCH_ENABLE' => ( $wpSettings['allowStyleSwitch'] ) ? 'checked="checked"' : '',
			'S_WPSTYLESWITCH_DISABLE' => ( $wpSettings['allowStyleSwitch'] ) ? '' : 'checked="checked"',
			'S_WPCENSOR_ENABLE' => ( $wpSettings['phpbbCensor'] ) ? 'checked="checked"' : '',
			'S_WPCENSOR_DISABLE' => ( $wpSettings['phpbbCensor'] ) ? '' : 'checked="checked"',
			'S_PHPBBSMILIES_ENABLE' => ( $wpSettings['phpbbSmilies'] ) ? 'checked="checked"' : '',
			'S_PHPBBSMILIES_DISABLE' => ( $wpSettings['phpbbSmilies'] ) ? '' : 'checked="checked"',
			'S_WPPRIVATE_ENABLE' => ( $wpSettings['mustLogin'] ) ? 'checked="checked"' : '',
			'S_WPPRIVATE_DISABLE' => ( $wpSettings['mustLogin'] ) ? '' : 'checked="checked"',		
			'S_BLOGHOME_ENABLE' => ( $wpSettings['useBlogHome'] ) ? 'checked="checked"' : '',
			'S_BLOGHOME_DISABLE' => ( $wpSettings['useBlogHome'] ) ? '' : 'checked="checked"',
			'S_WPBLOGTITLE' => $wpSettings['blogListHead'],
			'S_WPBLOGINTRO' => $wpSettings['blogIntro'],
			'S_WPBLOGSPERPAGE' => $wpSettings['blogsPerPage'],			
			'S_WPUBL_CSS_ENABLE' => ( $wpSettings['blUseCSS'] ) ? 'checked="checked"' : '',					
			'S_WPUBL_CSS_DISABLE' => ( $wpSettings['blUseCSS'] ) ? '' : 'checked="checked"',
		));

		
		// Should we show an error box at the top of the page?
		if ( !empty($errMsg) ) {
			$template->assign_block_vars('switch_settings_page.switch_wp_error', array(
			'WP_ERROR_MSG' => $errMsg
			));
		}

		$this->showPage();
	}
	
	function step1_show() {
		
		//
		//	STEP 1 -- WORDPRESS INSTALLATION INTRO PAGE
		//	-----------------------------------------------------------------------
		//	Not interactive. Just asks tthe user to install WordPress. AKA "Step 0"
		//
		global $template, $user, $numWizardSteps, $phpEx;
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$user->lang['WPWIZARD_H1'];
		// pass strings
		$template->assign_vars(array(
			'L_WPWIZARD_STEP' => sprintf($user->lang['WP_Wizard_Step'], 0, $numWizardSteps),
			'S_WPWIZ_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),
			'L_WPNEXT' => sprintf($user->lang['WP_Wizard_Next'], 1)
		));

		// set the page section to show
		$template->assign_block_vars('switch_wizard_page1', array());

		//show the page
		$this->showPage();
	}
	
	//
	//	STEP 1B -- WORDPRESS INSTALLATION SETTINGS
	//	-----------------------------------------------------------------------
	//	Sets the WordPress path & URL to WordPress. User sees as continuation of "Step 1"
	//	
	function step1b_show($uriResult = '', $pathResult = '') {
		global $user, $template, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx;
		
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$user->lang['WPWIZARD_H1'];
		
		//Read settings from db
		$wpSettings = $this->get_settings_set_defaults();
		
		// This page may be being called from the "test uri" or "find path" functions, when AJAX is unavailable.
		// In that case, set these fields to match what the user just inputed.
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

		// pass strings
		$template->assign_vars(array(
			'L_WPWIZARD_STEP' => sprintf($user->lang['WP_Wizard_Step'], 1, $numWizardSteps),
			'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'S_WPAJAX_ACTION' => str_replace ('&amp;', '&', append_sid("index.$phpEx?i=wp_united")),
			'S_WPURI' => $wpSettings['wpUri'],
			'S_WPPATH' => $wpSettings['wpPath'],
			'S_BLOGSURI' => $wpSettings['blogsUri'],
			'S_USEFORUMPG_ENABLE' => ($wpSettings['useForumPage']) ? 'checked="checked"' : '',
			'S_USEFORUMPG_DISABLE' => ($wpSettings['useForumPage']) ? '': 'checked="checked"',
			'L_WPNEXT' => sprintf($user->lang['WP_Wizard_Next'], 2),
			'L_WPWIZ_URITESTRESULT_NOAJAX' => $uriResult,
			'L_WPWIZ_PATHTESTRESULT_NOAJAX' => $pathResult
		));
		
		// set the page section to show
		$template->assign_block_vars('switch_wizard_page1b',  array());

		// Should we show an error box at the top of the page?
		if ( !empty($wizShowError) ) {
			$template->assign_block_vars('switch_wizard_page1b.switch_wp_error', array(
				'WP_ERROR_MSG' => $wizErrorMsg
			));
		}
		
		//show the page
		$this->showPage(); 
	}
	

	function step2_show() {
		//
		//	STEP 2 -- INTEGRATION SETTINGS
		//	--------------------------------------------------
		//	Sets how login integration is handled
		//
		global $user, $template, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx, $db;
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$user->lang['WPWIZARD_H1'];
		
		//Read settings from db
		$wpSettings = $this->get_settings_set_defaults();
		
		
		// set the page section to show
		$template->assign_block_vars('switch_wizard_page2', array());

		//Prepare forum list for forced cross-posting		
		$template->assign_block_vars('switch_wizard_page2.xpost_forumlist', array(
			'S_XPOST_VALUE' => -1,
			'S_XPOST_SEL' => ($wpSettings['xpostforce'] == -1) ? 'selected="selected"' : '',
			'S_XPOST_DESC' => "----{$user->lang['WPU_DISABLED']}----"
		));		
		$sql = 'SELECT forum_id, forum_name FROM ' . FORUMS_TABLE . ' WHERE ' .
			'forum_type = ' . FORUM_POST;
		if ($result = $db->sql_query($sql)) {
			while ( $row = $db->sql_fetchrow($result) ) {
				$template->assign_block_vars('switch_wizard_page2.xpost_forumlist', array(
					'S_XPOST_VALUE' => $row['forum_id'],
					'S_XPOST_SEL' => ($wpSettings['xpostforce'] == (int)$row['forum_id']) ? 'selected="selected"' : '',
					'S_XPOST_DESC' => $row['forum_name']
				));
			}
		}
		

		// pass strings
		$template->assign_vars(array(		
			'L_WPWIZARD_STEP' => sprintf($user->lang['WP_Wizard_Step'], 2, $numWizardSteps),
			'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'S_WPLOGIN_ENABLE' => ( $wpSettings['integrateLogin'] ) ? 'checked="checked"' : '',
			'S_WPLOGIN_DISABLE' => ( $wpSettings['integrateLogin'] ) ? '' : 'checked="checked"',
			'S_WPXPOST_ENABLE' => ( $wpSettings['xposting'] ) ? 'checked="checked"' : '',
			'S_WPXPOST_DISABLE' => ( $wpSettings['xposting'] ) ? '' : 'checked="checked"' ,
			'S_WPXAL_ENABLE' => ( $wpSettings['xpostautolink'] ) ? 'checked="checked"' : '',
			'S_WPXAL_DISABLE' => ( $wpSettings['xpostautolink'] ) ? '' : 'checked="checked"' ,
			'L_WPBACK' => sprintf($user->lang['WP_Wizard_Back'], 1),
			'L_WPNEXT' => sprintf($user->lang['WP_Wizard_Next'], 3)		
		));
		
		

		// Should we show an error box at the top of the page?
		if ( !empty($wizShowError) ) {
			$template->assign_block_vars('switch_wizard_page2.switch_wp_error', array(
				'WP_ERROR_MSG' => $wizErrorMsg
			));
		}
		//show the page
		$this->showPage();

	}


	//
	//	STEP 3 -- DISPLAY & BEHAVIOUR SETTINGS
	//	------------------------------------------------------------
	//	Sets the appearance of the WordPress page.
	//
	function step3_show() {
		global $user, $template, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx;
		
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$user->lang['WPWIZARD_H1'];
		
		//Read settings from db
		$wpSettings = $this->get_settings_set_defaults();

		$padding = ($wpSettings['phpbbPadding'] == 'NOT_SET') ? array('','','','') : explode('-', $wpSettings['phpbbPadding']);
		
		// pass strings
		$template->assign_vars(array(	
			'L_WPWIZARD_STEP' => sprintf($user->lang['WP_Wizard_Step'], 3, $numWizardSteps),
			'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'S_WPINP' =>  ( $wpSettings['showHdrFtr'] == 'FWD' ) ? 'checked="checked"' : '',
			'S_PINWP' =>  ( $wpSettings['showHdrFtr']  == 'REV') ? 'checked="checked"' : '',
			'S_PW_NONE' => ( $wpSettings['showHdrFtr']  == 'NONE') ? 'checked="checked"' : '',
			'S_CSSM_ON' => ( $wpSettings['cssMagic'] ) ? 'checked="checked"' : '',
			'S_CSSM_OFF' => ( $wpSettings['cssMagic'] ) ? '' : 'checked="checked"',	
			'S_TV_ON' =>  ( $wpSettings['templateVoodoo'] ) ? 'checked="checked"' : '',
			'S_TV_OFF' => ( $wpSettings['templateVoodoo'] ) ? '' : 'checked="checked"',
			'S_PLUGIN_FIXES_YES' => ( $wpSettings['pluginFixes'] ) ? 'checked="checked"' : '',
			'S_PLUGIN_FIXES_NO' =>  ( $wpSettings['pluginFixes'] ) ? '' : 'checked="checked"',
			'S_WPSIMPLE_YES' => ( $wpSettings['wpSimpleHdr'] ) ? 'checked="checked"' : '',
			'S_WPSIMPLE_NO' =>  ( $wpSettings['wpSimpleHdr'] ) ? '' : 'checked="checked"',
			'S_WP_PADTOP' => ($padding[0] != '') ? (int)$padding[0] : '',
			'S_WP_PADRIGHT' => ($padding[1] != '') ? (int)$padding[1] : '',
			'S_WP_PADBOTTOM' => ($padding[2] != '') ? (int)$padding[2] : '',
			'S_WP_PADLEFT' => ($padding[3] != '') ? (int)$padding[3] : '',
			'S_WPPAGE' => $wpSettings['wpPageName'],
			'S_WPDTD_ENABLE' => ( $wpSettings['dtdSwitch'] ) ? 'checked="checked"' : '',
			'S_WPDTD_DISABLE' => ( $wpSettings['dtdSwitch'] ) ? '' :  'checked="checked"',
			'S_WPCENSOR_ENABLE' => ( $wpSettings['phpbbCensor'] ) ? 'checked="checked"' : '',
			'S_WPCENSOR_DISABLE' => ( $wpSettings['phpbbCensor'] ) ? '' : 'checked="checked"',
			'S_PHPBBSMILIES_ENABLE' => ( $wpSettings['phpbbSmilies'] ) ? 'checked="checked"' : '',
			'S_PHPBBSMILIES_DISABLE' => ( $wpSettings['phpbbSmilies'] ) ? '' : 'checked="checked"',
			'S_WPPRIVATE_ENABLE' => ( $wpSettings['mustLogin'] ) ? 'checked="checked"' : '',
			'S_WPPRIVATE_DISABLE' => ( $wpSettings['mustLogin'] ) ? '' : 'checked="checked"',			
			'L_WPBACK' => sprintf($user->lang['WP_Wizard_Back'], 2),
			'L_WPNEXT' => sprintf($user->lang['WP_Wizard_Next'], 4)
		));
		
		// set the page section to show
		$template->assign_block_vars('switch_wizard_page3', array());

		// Should we show an error box at the top of the page?
		if ( !empty($wizShowError) ) {
			$template->assign_block_vars('switch_wizard_page3.switch_wp_error', array(
				'WP_ERROR_MSG' => $wizErrorMsg
			));
		}
		
		//show the page
		$this->showPage();
		
	}

	//
	//	STEP 4 -- "TO BLOG OR NOT TO BLOG?"
	//	------------------------------------------------------
	//	If the user wants logins to be integrated, then we can offer them a whole host of options
	//	controlling how members use/see their blogs.
	//	
	function step4_show($dir='FWD') {
		global $user, $template, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx;
		
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$user->lang['WPWIZARD_H1'];


		//Read settings from db
		$wpSettings = $this->get_settings_set_defaults();

		if ( $wpSettings['integrateLogin'] ) {
			
			// pass strings
			$template->assign_vars(array(	
				'L_WPWIZARD_STEP' => sprintf($user->lang['WP_Wizard_Step'], 4, $numWizardSteps),
				'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
				'S_WPOWNBLOGS_ENABLE' => ( $wpSettings['usersOwnBlogs'] ) ? 'checked="checked"' : '',
				'S_WPOWNBLOGS_DISABLE' => ( $wpSettings['usersOwnBlogs'] ) ? '' : 'checked="checked"',
				'S_WPBTNSPROF_ENABLE' => ( $wpSettings['buttonsProfile'] ) ? 'checked="checked"' : '',
				'S_WPBTNSPROF_DISABLE' => ( $wpSettings['buttonsProfile'] ) ? '' : 'checked="checked"',
				'S_WPBTNSPOST_ENABLE' => ( $wpSettings['buttonsPost'] ) ? 'checked="checked"' : '',
				'S_WPBTNSPOST_DISABLE' => ( $wpSettings['buttonsPost'] ) ? '' : 'checked="checked"',
				'S_WPSTYLESWITCH_ENABLE' => ( $wpSettings['allowStyleSwitch'] ) ? 'checked="checked"' : '',
				'S_WPSTYLESWITCH_DISABLE' => ( $wpSettings['allowStyleSwitch'] ) ? '' : 'checked="checked"',
				'S_BLOGHOME_ENABLE' => ( $wpSettings['useBlogHome'] ) ? 'checked="checked"' : '',
				'S_BLOGHOME_DISABLE' => ( $wpSettings['useBlogHome'] ) ? '' : 'checked="checked"',
				'S_WPBLOGTITLE' => $wpSettings['blogListHead'],
				'S_WPBLOGINTRO' => $wpSettings['blogIntro'],
				'S_WPBLOGSPERPAGE' => $wpSettings['blogsPerPage'],
				'S_WPUBL_CSS_ENABLE' => ( $wpSettings['blUseCSS'] ) ? 'checked="checked"' : '',					
				'S_WPUBL_CSS_DISABLE' => ( $wpSettings['blUseCSS'] ) ? '' : 'checked="checked"',			
				'L_WPBACK' => sprintf($user->lang['WP_Wizard_Back'], 3),
				'L_WPNEXT' => sprintf($user->lang['WP_Wizard_Next'], 5)
			));
			
			// set the page section to show
			$template->assign_block_vars('switch_wizard_page4',array());

			// Should we show an error box at the top of the page?
			if ( !empty($wizShowError) ) {
				$template->assign_block_vars('switch_wizard_page4.switch_wp_error', array(
					'WP_ERROR_MSG' => $wizErrorMsg
				));
			}
			
			//show the page
			$this->showPage();
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
		global $user, $template, $numWizardSteps, $wizShowError, $wizErrorMsg, $phpEx;
		
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$user->lang['WPWIZARD_H1'];
		
		$wpSettings = get_integration_settings();
		
		// pass strings
		$template->assign_vars(array(	
			'L_WPWIZARD_STEP' => sprintf($user->lang['WP_Wizard_Step'], 5, $numWizardSteps),
			'U_WPAJAX_ACTION' => str_replace ('&amp;', '&', append_sid("index.$phpEx?i=wp_united")),
			'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'S_PLUGIN_STATE' => (!empty($wpSettings['pluginFixes'])) ? 'ENABLED' : 'DISABLED',
			'L_WPBACK' => sprintf($user->lang['WP_Wizard_Back'], 4),
			'L_WPNEXT' => sprintf($user->lang['WP_Wizard_Next'], 6)
		));

		// set the page section to show
		$template->assign_block_vars('switch_wizard_page5', array());
		
		
		//show the page
		$this->showPage();
	}
	
	function step5_backend() {
		global $user, $wpSettings, $wpu_debug;
		$wpu_debug = '';
		
		set_error_handler(array($this, 'step5_errorhandler'));
		
		$data['pluginFixes'] = (request_var('plug', 'DISABLED') == 'ENABLED') ? 1 : 0;
		set_integration_settings($data);

		$wpSettings = get_integration_settings();

		$connError = $this->install_wpuConnection();
		
		$xmlData['info'] = ' ';

		if (empty($connError)) {
			$data['installLevel'] = 10;
			//set the version to the db at this stage
			$data['wpuVersion'] = $user->lang['WPU_Default_Version']; 
			if ( !(set_integration_settings($data)) ) { 
				$xmlData['result'] = "NOSAVE";
				$xmlData['message'] = $user->lang['WPWIZ_DB_ERR_CONN_OK'];
			} else {
				$xmlData['result'] = "OK";  
				$xmlData['message'] = $user->lang['WPWIZ_INSTALLED_OK'];
			}
		} else {
				$xmlData['result'] = "FAIL";
				$xmlData['message'] = "<![CDATA[$wpu_debug]]>";
		}
		$xmlData['pluginstate'] = (!empty($wpSettings['pluginFixes'])) ? 'ENABLED' : 'DISABLED';
		$this->send_ajax($xmlData, 'installconn');
		
	}
	
	function step5_errorhandler($errNo, $errStr, $errFile, $errLine) {
		global $user;
		switch ($errNo) {
			case E_ERROR:
			case E_USER_ERROR:
				echo sprintf($user->lang['WPWIZ_CAUGHT_ERROR'], $errNo, $errFile, $errLine, $errStr). '<br />';
				break;
			default:
				return false;
        }
		
	}
		
	//
	//	STEP 6 -- FINISH PAGE
	//	---------------------------------
	//	Congratulates the user for completing the wizard, and encourages them to donate.
	//
	function step6_show() {
		global $user, $template, $numWizardSteps, $phpEx;	
		$this->tpl_name = 'acp_wp_united';		
		$this->page_title =$user->lang['WPWIZARD_H1'];
		
		//get integration package settings
		$wpSettings = $this->get_settings_set_defaults();
		
		
		// pass strings
		$template->assign_vars(array(
			'L_WPWIZARD_STEP' => sprintf($user->lang['WP_Wizard_Step'], 6, $numWizardSteps),
			'L_WPWIZARD_COMPLETE_EXPLAIN4' => sprintf($user->lang['WP_Config_GoBack'], "<a href=\"" . append_sid("index.$phpEx?i=wp_united") . "\">", "</a>") ,
			'S_WPWIZ_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),
			'L_WPBACK' => sprintf($user->lang['WP_Wizard_Back'], 5),
		));

		// set the page section to show
		$template->assign_block_vars('switch_wizard_page6', array());

		//show the page
		$this->showPage();	
		
	}				


	//
	//	STEP 1B -- PROCESS WORDPRESS INSTALLATION SETTINGS
	//	------------------------------------------------------------------------------------
	//	Saves the URL, file path & database sharing settings to the config table.
	//	Performs some, albeit limited validation.
	//
	function step1b_process() {
		global $user, $phpEx, $wizShowError, $wizErrorMsg;
		// Process form data
		$saveSettings = TRUE;
		$data['wpUri'] = $this->clean_path(request_var('txt_Uri', ''));
		$data['wpPath'] = $this->clean_path(request_var('txt_Path', ''));
		$data['blogsUri'] = $this->clean_path(request_var('txt_BlogsUri', ''));
		$data['useForumPage'] = (int) request_var('rad_ForumPg', 1);
		
		if (($data['wpUri'] == "") || (strlen($data['wpUri']) < 3)) {
			$wizShowError = TRUE;
			$wizErrorMsg = $user->lang['wizErr_invalid_URL'];
			$saveSettings = FALSE;
		} else {
			//Add http:// and trailing slash if needed
			$data['wpUri'] = $this->add_trailing_slash($this->add_http($data['wpUri']));
		}
		if (($data['wpPath'] == "") || (strlen($data['wpPath']) < 3)) {
			$wizShowError = TRUE;
			$wizErrorMsg .= $user->lang['wizErr_invalid_Path']; 
			$saveSettings = FALSE;
		} else {
			$data['wpPath'] = $this->add_trailing_slash($data['wpPath']);
		}

		if (($data['blogsUri'] == "") || (strlen($data['blogsUri']) < 3)) {
			$wizShowError = TRUE;
			$wizErrorMsg = $user->lang['wizErr_invalid_Blog_URL'];
			$saveSettings = FALSE;
		} else {
			//Add http:// if needed
			$data['blogsUri'] = $this->add_http($data['blogsUri']);
		}
		
		if ($saveSettings) {
			//Save settings to db
			if ( !(set_integration_settings($data)) ) {
				$wizShowError = TRUE;
				$wizErrorMsg .= $user->lang['WPU_DATABASE_SAVE_ERR'];
			} else {
				$this->step2_show();
			}
		} 
		
		if ( !empty($wizShowError) ) {
			$wizErrorMsg .= $user->lang['WPU_NOT_SAVED'];
			$this->step1b_show();
		}

	}
	
	
	//
	//	STEP 2 -- PROCESS INTEGRATION SETTINGS
	//	---------------------------------------------------------------
	//	Saves the login integration settings to the config table.
	//
	function step2_process() {
		global $user, $phpEx, $wizShowError, $wizErrorMsg;
		// Process form data
		$radWpLogin = (int) request_var('rad_Login', '');

		if ( $radWpLogin ) {
			$data['xposting'] = (int) request_var('rad_xPost', 0);
			$data['xpostautolink'] = (int) request_var('rad_xpost_al', 0);
			$data['xpostforce'] = (int) request_var('al_xpost_must', -1);
			$data['integrateLogin'] = 1;

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
		} else { 
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
			$wizErrorMsg .= $user->lang['WPU_DATABASE_SAVE_ERR'];
		} 	 
		
		if ( !empty($wizShowError) ) {
			$wizErrorMsg .= $user->lang['WPU_NOT_SAVED'];
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

		global $user, $phpEx, $wizShowError, $wizErrorMsg;
		
		$data['dtdSwitch'] = (int) request_var('rad_DTD', 0);
		$data['showHdrFtr'] = request_var('rad_Inside', '');
		$data['cssMagic'] = (int) request_var('rad_cssm', 1);
		$data['templateVoodoo'] = (int) request_var('rad_tv', 1);
		$data['wpSimpleHdr'] = (int) request_var('rad_Simple', 0);
		$data['wpPageName'] = request_var('txt_wpPage', '');
		$data['phpbbCensor'] = (int) request_var('rad_Censor', 0);
		$data['pluginFixes'] = (int) request_var('rad_Plugins', 0);
		$data['phpbbSmilies'] = (int) request_var('rad_Smilies', 0);
		$data['mustLogin'] = (int) request_var('rad_Private', 0);
		

		$padT = request_var('txt_padT', 20);
		$padR = request_var('txt_padR', 20);
		$padB = request_var('txt_padB', 20);
		$padL = request_var('txt_padL', 20);
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
			$data['templateVoodoo'] = 1;
			break;
		}
		if(!$data['cssMagic']) {
			$data['templateVoodoo'] = 1;
		}

		//Save settings to db
		if ( !(set_integration_settings($data)) ) {
			$wizShowError = TRUE;
			$wizErrorMsg .= $user->lang['WPU_DATABASE_SAVE_ERR'];
		} else {
			$this->step4_show('FWD');
		}
		
		
		if ( !empty($wizShowError) ) {
			$wizErrorMsg .= $user->lang['WPU_NOT_SAVED'];
			$this->step3_show();
		}

	}


	//
	//	STEP 4 -- PROCESS BLOG SETTINGS
	//	-------------------------------------------------------
	//	Saves the Blog settings to the config table.
	//
	function step4_process() {

		global $user, $phpEx, $wizShowError, $wizErrorMsg;
		
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
			$wizErrorMsg .= $user->lang['WPU_DATABASE_SAVE_ERR'];
			$wizErrorMsg .= $user->lang['WPU_NOT_SAVED'];
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
		
		global $user, $phpEx, $wpu_debug, $template;
		$this->page_title = $user->lang['ACP_WPU_INDEX_TITLE'];
		$this->tpl_name = 'acp_wp_united';
		
		// Process form data
		$allOK = FALSE; $saveSettings = FALSE; $autoDetect = TRUE;
		$data['wpUri'] = $this->clean_path(request_var('txt_Uri', ''));
		$data['wpPath'] = $this->clean_path(request_var('txt_Path', ''));
		$data['blogsUri'] = $this->clean_path(request_var('txt_BlogsUri', ''));	
		$data['useForumPage'] = (int) request_var('rad_ForumPg', 1);
		$data['dtdSwitch'] = (int) request_var('rad_DTD', 0);
		$data['showHdrFtr'] = request_var('rad_Inside', '');
		$data['cssMagic'] = (int) request_var('rad_cssm', 1);
		$data['templateVoodoo'] = (int) request_var('rad_tv', 1);
		$data['wpSimpleHdr'] = (int) request_var('rad_Simple', 1);
		$data['phpbbCensor'] = (int) request_var('rad_Censor', 1);
		$data['pluginFixes'] = (int) request_var('rad_Plugins', 0);
		$data['phpbbSmilies'] = (int) request_var('rad_Smilies', 1);
		$data['mustLogin'] = (int) request_var('rad_Private', 0);
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
			$data['templateVoodoo'] = 1;
			break;
		}
		if(!$data['cssMagic']) {
			$data['templateVoodoo'] = 1;
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
			$data['xposting'] = (int) request_var('rad_xPost', 0);
			$data['xpostautolink'] = (int) request_var('rad_xpost_al', 0);
			$data['xpostforce'] = (int) request_var('al_xpost_must', -1);
			
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
		$procError = FALSE; 
		if (($data['wpUri'] == "") || (strlen($data['wpUri']) < 3)) {
			$procError = TRUE;
			$msgError .= $user->lang['WPU_URL_Not_Provided'];
		} else {
			// Clean up the URI 
			//Add http:// and trailing slash if needed
			$data['wpUri'] = $this->add_http($data['wpUri']);
			$data['wpUri'] = $this->add_trailing_slash($data['wpUri']);


			//Check the URI is on the same host
			$hostName = "http://".$_SERVER['HTTP_HOST'];
			If (strpos($data['wpUri'], $hostName) === FALSE) {
				$urlCheckColour = "orange";
				$urlCheckResult = $user->lang['WPU_URL_Diff_Host'];
				$autoDetect = FALSE;
			} else {
				$urlCheckColour = "green";
				$urlCheckResult = $user->lang['WPU_OK'];
			}	
			//test that the URL at that location actually exists

			$uriExists = $this->uri_exists($data['wpUri']);
			if ( $uriExists ){
				$urlExistColour = "green";
				$urlExistResult = $user->lang['WPU_OK'];
			} else {
				if ($uriExists === FALSE) {
					$urlExistColour = "orange";
					$urlExistResult = $user->lang['WPU_URL_No_Exist'];
				} else{
					$urlExistColour = "orange";
					$urlExistResult = $user->lang['WP_cURL_Not_Avail'];
				}
			} 

			if ($data['wpPath'] == "") {
				if (!$autoDetect) {
					$procError = TRUE;
					$msgError .= $user->lang['WPU_Cant_Autodet'];
				} else {
					$pathDet = $user->lang['WPU_Path_Autodet'];
					//figure out the filesystem path
					$data['wpPath'] = $this->detect_path_from_uri($data['wpUri']);
				}
			} else {
				$pathDet = $user->lang['WPU_PathIs'];
			}
			if (!$data['wpPath']) {
				if ($autoDetect) {
					$procError = TRUE;
					$msgError .= $user->lang['WPU_Autodet_Error'];
				}
			} else {	
				$pathDetColour = "green";
				$pathDetResult = $data['wpPath'];
				
				//Check that location to see if WordPress files exist there
				if ( !$this->wordpress_exists($data['wpPath']) ) {
					$wpExistColour = "orange";
					$wpExistResult = sprintf($user->lang['WPU_Pathfind_Warning'], $this->add_trailing_slash($data['wpPath']) . 'wp-settings.php');
					$msgError .= sprintf($user->lang['WPU_Pathfind_Warning'] . "<br />", $this->add_trailing_slash($data['wpPath']) . 'wp-settings.php');
				} else {
					$wpExistColour = "green";
					$wpExistResult = $user->lang['WPU_OK'];
				}
				global $wpSettings;
				$wpSettings = $data; 
			}
		}
		
		if (($data['blogsUri'] == "") || (strlen($data['blogsUri']) < 3)) {
			$procError = TRUE;
			$msgError .= $user->lang['wizErr_invalid_Blog_URL'];
		} else {
			//Add http:// if needed
			$data['blogsUri'] = $this->add_http($data['blogsUri']);
		}
		
		if ( !$procError ) {
			//THIS MARKS THE END OF THE SETUP. Allow access to application.
		/*	$data['installLevel'] = 10;
			//set the version to the db at this stage
			$data['wpuVersion'] = $user->lang['WPU_Default_Version'];		*/
			
			//Save settings to db
			if ( !(set_integration_settings($data)) ) {
				$procError = TRUE;
				$msgError .= $user->lang['WP_DBErr_Write'];
			}
			
			if ((!$allOK) || ($uriNotFound)) {
				$saveColour = "orange";
				$saveResult = $user->lang['WP_Saved_Caution'];
			} else {
				$saveColour = "green";
				$saveResult = $user->lang['WP_AllOK'];
			}
		} 
		
		
		
		if ( $procError ) { 
			if ( empty($msgError) ) {
				$msgError .= $user->lang['WP_Errors_NoSave'];
			}
			global $wpErrSettings, $errMsg;
			$errMsg = $msgError;
			$wpErrSettings = $data;
			$this->settings_show();
		} else {
			// pass strings
			$template->assign_vars(array(	
				'L_WP_URLCOLOUR' => $urlCheckColour,
				'L_WP_URLRESULT' => $urlCheckResult,
				'L_WP_URLEXISTCOLOUR' => $urlExistColour,
				'L_WP_URLEXISTRESULT' => $urlExistResult,
				'L_WP_PATHDET' => $pathDet,
				'L_WP_PATHDETCOLOUR' => $pathDetColour,
				'L_WP_PATHDETRESULT' => $pathDetResult,
				'L_WP_WPEXISTCOLOUR' => $wpExistColour,
				'L_WP_WPEXISTRESULT' => $wpExistResult,
				'S_PLUGIN_STATE' => (!empty($wpSettings['pluginFixes'])) ? 'ENABLED' : 'DISABLED',
				'U_WPAJAX_ACTION' => str_replace ('&amp;', '&', append_sid("index.$phpEx?i=wp_united")),
				'S_WPWIZ_ACTION' => append_sid("index.$phpEx?i=wp_united"),
				'L_WPSETTINGS_RESULTMSG' => sprintf($user->lang['WP_Config_GoBack'], "<a href=\"" . append_sid("index.$phpEx?i=wp_united") . "\">", "</a>") ,
			));
			

			// set the page section to show
			$template->assign_block_vars('switch_settings_process_page', array());
			$this->showPage();
			
		}
	}
	// **********************************************************************
	//***																	***
	//***				DONATE PAGE				 	***
	//***																	***
	//***********************************************************************

	function donate_show() {
		global $user, $phpEx, $ignorePrompt;
		$this->page_title = $user->lang['ACP_WPU_INDEX_TITLE'];
		$this->tpl_name = 'acp_wp_united';

		// set the page section to show
		$template->assign_block_vars('switch_donate', array());

		//show the page
		$this->showPage();

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
		global $phpbb_root_path, $template, $phpEx, $user, $phpEx, $cache, $auth, $db, $wpSettings;
		$this->page_title = $user->lang['ACP_WPU_INDEX_TITLE'];
		$this->tpl_name = 'acp_wp_united';
		
		$wpSettings = get_integration_settings();
		
		$do_uninstall = request_var('uninstallaction', '');
			
		// pass strings	
		$template->assign_vars(array(
			'S_WPWIZ_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),			
		));
		
		if ($do_uninstall == $user->lang['WP_UNINSTALL']) {	
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
				if  (array_key_exists('user_wpuint_id', $user->data()) ) {
 					$sql = 'ALTER TABLE ' . USERS_TABLE . ' 
								DROP user_wpuint_id';
				}
				$db->sql_query($sql);
				if  (array_key_exists('user_wpublog_id', $user->data()) ) {
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
				add_log('admin', 'WP_UNINSTALLED', $user->lang['WP_UNINSTALL_LOG']);				
				redirect(append_sid("index.$phpEx"));
			} else {
				confirm_box(false,$user->lang['WP_UNINSTALL_CONFIRM'], build_hidden_fields(array(
						'i'			=> 'wp_united',
						'mode'		=> 'uninstall',
						'uninstallaction' => $user->lang['WP_UNINSTALL'],
					)));
			}
		}
		
		// set the page section to show
		$template->assign_block_vars('switch_uninstall', array());

		//show the page
		$this->showPage();		
	}
	// **********************************************************************
	//***																	***
	//***				SHOW DEBUG INFO						 	***
	//***																	***
	//***********************************************************************
	//
	//	The debug info is for posting online -- it should be in English, so many strings don't need to be
	// i18n'ed.
	//
	function debug_show() {
		global $phpbb_root_path, $template, $phpEx, $user, $config, $phpEx, $db;
		$this->page_title = $user->lang['ACP_WPU_INDEX_TITLE'];
		$this->tpl_name = 'acp_wp_united';	
		
		$result = $db->sql_query("select version() as ve");
		if ($res = $db->sql_fetchrow($result)) {
			$myVersion = $res['ve'];
		} else {
			$myVersion = "Unable to detect. Not mySql?";
		}
		
		$curl_exists = (function_exists('curl_exec')) ? "Yes" : "No";
		
		
		$wpSettings = get_integration_settings(TRUE);
		$debug_info = '<strong style="text-decoration: underline;">[b][u]' . $user->lang['DEBUG_SETTINGS_SECTION'] . '[/u][/b]</strong><br /><br />';
		foreach ($wpSettings as $setting_name => $setting_value) {
			$debug_info .= '[b]<strong>: ' . $setting_name . ':</strong>[/b] ' . $setting_value . "<br />\n";
		}
		
		$debug_info .= '<br /><strong style="text-decoration: underline;">[b][u]' . $user->lang['DEBUG_PHPBB_SECTION'] . '[/u][/b]</strong><br /><br />';
		$phpbb_info = array('server_name', 'server_protocol', 'server_port', 'script_path', 'force_server_vars', 'cookie_name', 'cookie_domain', 'cookie_path', 'cookie_secure');
		foreach ($phpbb_info as $config_name) {
			$debug_info .= '[b]<strong>' . $config_name . ':</strong>[/b] ' . $config[$config_name] . "<br />\n";
		}
		
		$debug_info .= '<br /><strong style="text-decoration: underline;">[b][u]' . $user->lang['DEBUG_SERVER_SETTINGS'] .  '[/u][/b]</strong><br /><br />';
		$debug_info .= '[b]<strong>PHP version:</strong>[/b] ' . phpversion() . "<br />\n";
		$debug_info .= '[b]<strong>MySQL version:</strong>[/b] ' . $myVersion . "<br />\n";
		$debug_info .= '[b]<strong>cURL available:</strong>[/b] ' . $curl_exists . "<br />\n";
		
		// pass strings	
		$template->assign_vars(array(
			'DEBUG_INFO' => $debug_info,
		));
		
		// set the page section to show
		$template->assign_block_vars('switch_debug', array());

		//show the page
		$this->showPage();
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
		global $phpbb_root_path, $user, $template, $phpEx, $cache;
		$this->page_title = $user->lang['ACP_WPU_INDEX_TITLE'];
		$this->tpl_name = 'acp_wp_united';

		$do_reset = request_var('resetaction', '');
		$did_reset = request_var('didreset', '');
		
		// add additional language strings from non-admin file
		$user->add_lang('mods/wp-united');
		
		// pass strings	
		$template->assign_vars(array(
			'S_WPWIZ_ACTION' =>  append_sid("index.$phpEx?i=wp_united"),			
		));
		
		if($did_reset) {
			$template->assign_var('DID_RESET', $user->lang['WP_DID_RESET']);
		}

		if ($do_reset == $user->lang['WP_RESET']) {
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
				$usermapId = $this->add_acp_module($modData);
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
				$debugId= $this->add_acp_module($modData);
				
				
				//now reset settings
				clear_integration_settings();
				$cache->destroy('_modules_');
				$cache->destroy('_sql_', MODULES_TABLE);
				$cache->purge();
				add_log('admin','ACP_WPU_RESET', $user->lang['WP_RESET_LOG']);	
				//the module IDs have changed -- we redirect so that the "WP-United" tab on the page is the new one.
				redirect(append_sid("index.$phpEx?mode=reset&didreset=true&i=$resetId"));		
			} else {
				confirm_box(false,$user->lang['WP_RESET_CONFIRM'], build_hidden_fields(array(
						'i'			=> 'wp_united',
						'mode'		=> 'reset',
						'resetaction' => $user->lang['WP_RESET'],
					)));
			}
		}

		// set the page section to show
		$template->assign_block_vars('switch_reset', array());

		//show the page
		$this->showPage();
	}
	/**
	 * Permissions sidebar link
	 * Adding a module doesn't cross-link to the right tab. So we just redirect instead
	 */
	function permissions_show() {
		global $phpEx;
		redirect(append_sid("index.{$phpEx}?i=permissions&mode=intro"));
	}


	
	/**
	 * Gets the configuration settings from the integration table, and returns them in $wpSettings.
	 * Sets initial values to sensible deafaults if they haven't been set yet -- useful for populating form fields with default values.
	 * This has been superseded by defaults in mod_settings.
	 */
	function get_settings_set_defaults() {
		return get_integration_settings(TRUE);
	}


	/**
	 * Tests that the supplied URL exists and is on the same domain.
	 * Return the result in XML if browser supports AJAX. 
	 */
	function testUri($ajax = TRUE) {
		global $user;
		
		$hostName = "http://".$_SERVER['HTTP_HOST'];
		
		$wpUri = $this->clean_path(request_var('txt_Uri', ''));
		$wpUri = $this->add_http($wpUri);
		$wpUri = $this->add_trailing_slash($wpUri);
		$uriExists = $this->uri_exists($wpUri);
		$sameHost = strpos($wpUri, $hostName);
		
		if ( !empty($uriExists) ) {
			If ( $sameHost === FALSE) {
				$testSuccess = "WARNING";
				$returnMessage = $user->lang['WP_URI_OK_Diff_Host'];
			} else {
				$testSuccess = "SUCCESS";
				$returnMessage = $user->lang['WP_URI_Found'];
			}
		} elseif ( $uriExists === 0 ) {
			$testSuccess = "ERROR";
			$returnMessage = $user->lang['WP_cURL_Not_Avail'];
		} elseif ( $sameHost === FALSE) {
			$testSuccess = "ERROR";
			$returnMessage = $user->lang['WP_URI_No_Diff_Host'];
		} else {
			$testSuccess = "ERROR";
			$returnMessage = $user->lang['WP_URI_Not_Found'];
		}
		
		
		if ( $ajax ) {
			$xmlData['result'] = $testSuccess;
			$xmlData['message'] = $returnMessage;
			$xmlData['info'] = $wpUri;

			$this->send_ajax($xmlData, 'testuri');
		} 

		return $returnMessage;
	}

	/** Path "auto-discovery"
	 * Figures out the filesystem path to WordPress and tests to see if WordPress files exist there.
	 * Return the result in XML if browser supports AJAX.
	 */
	function findPath($ajax = TRUE) {
		
		global $user;
		
		$hostName = "http://".$_SERVER['HTTP_HOST'];
		$wpUri = $this->clean_path(request_var('txt_Uri', ''));
		$wpPath = $this->clean_path(request_var('txt_Path', ''));
		$manuallyTyped = FALSE; $returnMessage = '';
		
		if ( empty($wpPath) ) { //the path field is empty, autodetect
			$sameHost = strpos($wpUri, $hostName);
			if ($sameHost === FALSE ) {
				$testSuccess = 'ERROR';
				$returnMessage = $user->lang['WP_PathTest_Diff_Host'];
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
					$returnMessage = $user->lang['WP_PathTest_Invalid_URL'];
				} else { // Due to an unknown error
					$returnMessage = $user->lang['WP_PathTest_Not_Detected'];
				}
			} else {
				if ( $this->wordpress_exists($wpPath) ) { //Found WordPress at that path
					$testSuccess = 'SUCCESS';
					$returnMessage = sprintf($user->lang['WP_PathTest_Success'], $wpPath);
				} else { // Found path (or path was manualy typed), but no WordPress there!
					$testSuccess = 'WARNING';
					if ( $manuallyTyped ) {
						$returnMessage = $user->lang['WP_PathTest_TestOnly_NotFound'];
					} else {
						$returnMessage = sprintf($user->lang['WP_PathTest_GuessedOnly'], $wpPath);
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



	/**
	 * Install WP-United Connection
	 * Sets and installs the auto-plugin, "WP-United Connection"
	 */
	function install_wpuConnection() {
		define ('WPU_SET', 1);
		global $phpbbForum, $user, $wpuCache, $wpSettings, $phpEx, $phpbb_root_path, $config, $wpu_debug, $wpUtdInt;
		require_once($phpbb_root_path . 'wp-united/cache.' . $phpEx);		
		$wpuCache = WPU_Cache::getInstance();
		$wpuCache->purge();

		//Set up the WordPress Integration
		require_once($phpbb_root_path . 'wp-united/wp-integration-class.' . $phpEx);
		$wpUtdInt = WPU_Integration::getInstance();
		$connError = FALSE;
		if ($wpUtdInt->can_connect_to_wp()) {
			//Enter Integration
			$wpUtdInt->enter_wp_integration();
			eval($wpUtdInt->exec());  

			// Find path  to adm
			$thisPath = $this->add_trailing_slash($this->clean_path(realpath(getcwd())));
			$wpu_debug .= 'DEBUG (to post if you need help):<br />';
			$wpu_debug .= 'Current Path ' . $thisPath . '<br />';
			
			$thisPath =  explode("/", $thisPath );
			array_pop($thisPath); array_pop($thisPath);
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
			$copySuccess = false;
			if (file_exists($wpPluginDir)) {
				// we got the plugin directory correct, copy file over
				$copySuccess = false;
				if(!@copy($phpbb_root_path . "/wp-united/wpu-plugin.{$phpEx}", $wpPluginDir . "wpu-plugin.{$phpEx}")) {
					// Copy failed
				} 
				if (file_exists($wpPluginDir . "wpu-plugin.{$phpEx}")) {
					// Check to see that WPU-Plugin is the correct version
					
					$localFileTime = @filemtime("{$phpbb_root_path}wp-united/wpu-plugin.{$phpEx}");
					$installedFileTime =@filemtime("{$wpPluginDir}wpu-plugin.{$phpEx}");
					$copySuccess = ($localFileTime <= $installedFileTime);
				}
			}

			if(!$copySuccess) { 
				// CORRECT WPU-PLUGIN IS NOT IN PLUGIN DIRECTORY -- FAIL
				
				$connError = TRUE;
				$wpu_debug = "<br />" . $user->lang['WPWizard_Connection_Fail_Explain2'];
				
			} else {
			
				// We build up the connection settings for WordPress
				$pluginPath = "wpu-plugin." . $phpEx;

				//$pluginPath = $fromW.$toPlugin. "wpu-plugin." . $phpEx;
			
				$wpu_debug .= 'Final Calculated Path: ' . $pluginPath . '<br />'; 
			
				//And the path we'll use to access the phpBB root from the WordPress admin dir is:
				$WPU_Connection['path_to_phpbb'] = $this->add_trailing_slash(realpath($phpbb_root_path));
				$wpu_debug .= 'Path back to phpBB: ' . $WPU_Connection['path_to_phpbb'] . '<br />';
				
				// We'll also want to have the full scriptPath in wp-admin for playing with URLs
				$server = $config['server_protocol'] . $this->add_trailing_slash($config['server_name']);
				$scriptPath = $this->add_trailing_slash($config['script_path']);
				$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
				$scriptPath = $server . $scriptPath;
				$WPU_Connection['phpbb_url'] = $scriptPath;

				//Set Connection settings
				update_option('wputd_connection', $WPU_Connection);
				$server = $this->add_http($this->add_trailing_slash($config['server_name']));
				$scriptPath = $this->add_trailing_slash($config['script_path']);
				$scriptPath = ( $scriptPath[0] == "/" ) ? substr($scriptPath, 1) : $scriptPath;
				$blogUri = $wpSettings['blogsUri']; 
			
				//Set up WordPress the way we want
				update_option('home', $blogUri);
				global $wpdb, $phpbbForum;
				
				// Set up the reverse-integrated forum page
				$forum_page_ID = get_option('wpu_set_forum');
				if ( !empty($wpSettings['useForumPage']) ) {
					$content = '<!--wp-united-phpbb-forum-->';
					$title = $phpbbForum->lang['FORUM'];
					if ( !empty($forum_page_ID) ) {
						$wpdb->query( 
							"UPDATE IGNORE $wpdb->posts SET
							post_author = '0',
							post_date = '".current_time('mysql')."',
							post_date_gmt = '".current_time('mysql',1)."',
							post_content = '$content',
							post_content_filtered = '',
							post_title = '$title',
							post_excerpt = '',
							post_status = 'publish',
							post_type = 'page',
							comment_status = 'closed',
							ping_status = 'closed',
							post_password = '',
							post_name = 'forum',
							to_ping = '',
							pinged = '',
							post_modified = '".current_time('mysql')."',
							post_modified_gmt = '".current_time('mysql',1)."',
							post_parent = '0',
							menu_order = '0'
							WHERE ID = $forum_page_ID"
						);
					} else {
						$wpdb->query(
						"INSERT IGNORE INTO $wpdb->posts
								(post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, post_type, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_mime_type)
							VALUES
								('0', '".current_time('mysql')."', '".current_time('mysql',1)."', '{$content}', '', '{$title}', '', 'publish', 'page', 'closed', 'closed', '', 'forum', '', '', '".current_time('mysql')."', '".current_time('mysql',1)."', '0', '0', '')"
						);
						$forum_page_ID = $wpdb->insert_id;		
					}		
					update_option('wpu_set_forum', $forum_page_ID);			
					
				} else {
					if ( !empty($forum_page_ID) ) {
						update_option('wpu_set_forum', '');
						@wp_delete_post($forum_page_ID);
					}					
				}
				
				
				
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
					$wpu_debug = "<br />" . $user->lang['WPWizard_Connection_Fail_Explain2'];
				}
			} // end if copy success
			
		} else { // can't connect to WP
			$connError = TRUE;
			$debugPath = $this->add_trailing_slash($this->clean_path(realpath(dirname(__FILE__))));
			$wpu_debug .= "<br />" . $user->lang['NO_CONNECT_WP_GEN'] . "<br />";
			$wpu_debug .= $user->lang['WPWizard_Connection_Fail_Explain1'];
			$wpu_debug .=  'DEBUG (to post if you need help):<br />Current Path: ' . $debugPath . ' <br />';
			$wpu_debug .= 'Path To WP: ' . $wpSettings['wpPath'] . '<br />';
			
		}
		$wpUtdInt->exit_wp_integration();
		$wpUtdInt = null; unset ($wpUtdInt);
		
		$wpuCache->purge();
		
		return $connError;
	}



	/**	
	 * Shows the introductory user mapping page
	 */
	function usermap_intro() {
		global $user, $template, $phpEx;
		$this->page_title = $user->lang['MAP_TITLE'];
		$this->tpl_name = 'acp_wp_united';

		//Get integration settings
		$wpSettings = get_integration_settings();
		if ( ($wpSettings == FALSE)	|| ($wpSettings['wpPath'] == '') ) {
			trigger_error($user->lang['WP_DBErr_Gen'] . " " . $user->lang['WP_NO_SETTINGS'], __LINE__, __FILE__, $sql);
		}
		
		$template->assign_vars(array(	
			'S_WPMAP_ACTION' => append_sid("index.$phpEx?i=wp_united")
		));
		
		// set the page section to show
		$template->assign_block_vars('switch_usermap_intro', array());
		$this->showPage();
	}
	
	/**
	 * The main user mapping page that lists all the names
	 */
	function usermap_main() {	
		global $user, $phpEx, $phpbb_root_path, $wpSettings, $db, $template, $phpbbForum;
		// NUMBER OF RESULTS PER PAGE -- COULD ADJUST THIS FOR LARGE USERBASES
		
		$numPerPage = $numResults = (int)request_var('wpumapperpage', 50);
		
		//Get integration settings
		$wpSettings = get_integration_settings();
		if ( ($wpSettings == FALSE)	|| ($wpSettings['wpPath'] == '') ) {
			trigger_error($user->lang['WP_DBErr_Gen'] . '<br />' . $user->lang['WP_NO_SETTINGS']);
		}			
		
		$this->page_title = $user->lang['MAP_TITLE'];
		$this->tpl_name = 'acp_wp_united';
		
		// set the page section to show
		$template->assign_block_vars('switch_usermap_main', array());        

		// Eventually this will be in a dropdown.
		$action = request_var('mapaction', '');
		if($action == $user->lang['MAP_CHANGE_PERPAGE']) {
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
						trigger_error($user->lang['NO_WP_ID'] . '<br />' . $user->lang['NO_WP_ID_ERR']);
					}
					$phpBBMappedName = get_usermeta($result->ID, 'phpbb_userLogin');
					if ( empty($phpBBMappedName) ) {
						$phpBBMappedName = $result->user_login;
					}
					$phpbbForum->enter();
					$pUsername = '';
					$pID = '';
					$class = '';
					$pStatus = $user->lang['MAP_NOT_INTEGRATED'];
					$intText = $user->lang['MAP_INTEGRATE'];
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
								$pStatus = $user->lang['MAP_ERROR_MULTIACCTS'];
								$breakOrLeave = $user->lang['MAP_BRK_MULTI'];
								$selBrk = 'selected="selected"';
								$mustBrk = 'TRUE';
								$class = "mustbrk";
							} else {
								$pStatus = $user->lang['MAP_ALREADYINT']; 
								$breakOrLeave = $user->lang['MAP_BRK'];
								$selInt = 'selected="selected"';
								$intText = $user->lang['MAP_LEAVE_INT'];
								$alreadyID = $pRes['user_id'];
								$alreadyUN = $pRes['username'];
								$class = "alreadyint";			
							}
						} else {
							//No Integration ID... so let's search for a match
							
							//User may want to create a phpBB user
							$optCre = '<option value="Cre">'. $user->lang['MAP_CREATEP'] .'</option>'; 
							
							if ( !empty($phpBBMappedName) ) {
								$sql = 	"SELECT username, user_id, user_wpuint_id FROM " . USERS_TABLE .
								" WHERE username = '" . $phpBBMappedName . "'
										LIMIT 1";
								if (!$pResults = $db->sql_query($sql)) {
									trigger_error($user->lang['WP_DBErr_Retrieve']  . '<br />' . $user->lang['MAP_CANTCONNECTP']);
								}
								if ($pResults = $db->sql_fetchrow($pResults))  {
									//OK, so we found a username match... but show only if they're not already integrated to another acct.
									if ( empty($pResults['user_wpuint_id']) ) {
										if ( (!empty($pResults['username'])) && (!empty($pResults['user_id'])) ) {
											$pUsername = $pResults['username'];
											$pID = $pResults['user_id'];
											$breakOrLeave = $user->lang['MAP_LEAVE_UNINT'];
											$pStatus = $user->lang['MAP_UNINT_FOUND'];
											$selInt = 'selected="selected"';
											$class = 'unintfound';
										}
									} else {
										$breakOrLeave = $user->lang['MAP_LEAVE_UNINT'];
										$selBrk = 'selected="selected"';
										$pStatus = sprintf($user->lang['MAP_UNINT_FOUNDBUT'], $pResults['username'], $pResults['username'], $pResults['user_wpuint_id']);
										$class = 'unintfoundbut';
									}	
								} else {
									// Offer to create the user
									$optCre = '<option value="Cre" selected="selected">'. $user->lang['MAP_CREATEP'] .'</option>'; 
									$pStatus = $user->lang['MAP_UNINT_NOTFOUND']; 										
									$pUsername = $phpBBMappedName;
									$breakOrLeave = $user->lang['MAP_LEAVE_UNINT'];
									$class = 'unintnotfound';
									/*
									$breakOrLeave = $user->lang['MAP_LEAVE_UNINT'];
									$selBrk = 'selected="selected"';
									$pStatus = $user->lang['MAP_UNINT_NOTFOUND'] ; */
								}	
							}
						}	
					} else {
						trigger_error($user->lang['WP_DBErr_Retrieve'] . '<br />' . $user->lang['WP_DBErr_Retrieve']);
					}
					if ( empty($phpBBMappedName) ) {
						$breakOrLeave = $user->lang['MAP_LEAVE_UNINT'];
						$selDel = 'selected="selected"';
						$pStatus = $user->lang['MAP_ERROR_BLANK'];
						$class = "maperror";
					}
					$wpUtdInt->exit_wp_integration();
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
						'S_MUST_BREAK' => $mustBrk,
						'S_OPT_CREATE' => $optCre,
					));
					
					$itn++;
				}
				if ( $thisEnd < $numWpResults ) {
					$template->assign_block_vars('switch_usermap_main.next_page_data', array(
						'MAP_SKIPNEXT' => $user->lang['MAP_SKIPNEXT'],
					));
				} 

			} else {
				$template->assign_block_vars('switch_usermap_main.switch_no_results', array(
					'MAP_NOUSERS' => $user->lang['MAP_NOUSERS'],
				));
			}
		} else {
			die($user->lang['MAP_CANT_CONNECT']);
		}
		
		$template->assign_vars(array(	
			'S_WPMAP_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'S_NEXTSTART' => $nextStart,
			'S_OLDSTART' => $wpStart,
			'S_TOTAL_ITN' => $itn - 1,
		));
		
		for($i=50;$i<=500;$i=$i+50) {
			$template->assign_var('S_NUMPERPAGE_' . $i, ($i == $numPerPage) ? 'selected = "selected"' : '');
		}
		
		$this->showPage();
		
	}
	
	
	/**
	 * Process the users' selections into an actionable list
	 */
	function usermap_process() {	
		global $user, $phpEx, $phpbb_root_path, $wpSettings, $db, $template;
		
		$this->page_title = $user->lang['MAP_TITLE'];
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
						'text' => sprintf($user->lang['MAP_BREAKWITH'], $alreadyUN)
						);
						$actionList[] = array(
						'action' => 'integrate',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'typed' => $typedName,
						'text' => sprintf($user->lang['MAP_INTWITH'], $typedName)
						);
					}
				} else {
					if ( $mustBreak == 'TRUE' ) {
						$action = "SORT_OUT_MULTIPLES";
						$actionList[] = array(
						'action' => 'break',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'text' => $user->lang['MAP_BREAKEXISTING']
						);
						$actionList[] = array(
						'action' => 'integrate',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'typed' => $typedName,
						'text' => sprintf($user->lang['MAP_INTWITH'], $typedName)
						);
					} else {
						$action = "INTEGRATE_NEW";
						$actionList[] = array(
						'action' => 'integrate',
						'wpID' => $wpID,
						'wpUN' => $wpUN,								
						'typed' => $typedName,
						'text' => sprintf($user->lang['MAP_INTWITH'], $typedName)
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
						'text' => $user->lang['MAP_BREAKMULTI']
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
					'text' => sprintf($user->lang['MAP_BREAKWITH'], $alreadyUN)
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
					'text' => $user->lang['MAP_DEL_WP']
					);
				} else {
					$action = 'BREAK_THEN_DELETE';
					$actionList[] = array(
					'action' => 'break',
					'wpID' => $wpID,
					'wpUN' => $wpUN,							
					'text' => sprintf($user->lang['MAP_BREAKWITH'], $alreadyUN)							
					);
					$actionList[] = array(
					'action' => 'delete',
					'wpID' =>$wpID,
					'wpUN' => $wpUN,							
					'text' => $user->lang['MAP_DEL_WP']					
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
				'text' => $user->lang['MAP_CREATE_P']
				);
			}
		}
		if ( isset($actionList) ) { 
			$intro_para = $user->lang['MAP_ACTIONSINTRO'];
			$ctr = 0;
			$error = FALSE;
			foreach ((array)$actionList as $doThis) { 
				$col = 'green';  $errText = '';
				if ($doThis['action'] == 'integrate') { 
					//Check that phpBB user exists and is not already integrated
					$sql = 	"SELECT user_wpuint_id, user_id FROM " . USERS_TABLE .
					" WHERE username = '" . $db->sql_escape($doThis['typed']) . "'";
					if (!$pCheck = $db->sql_query($sql)) {
						trigger_error($user->lang['WP_DBErr_Retrieve'] . '<br />' . $user->lang['MAP_CANTCONNECTP']);
					}
					if (!($pCheckResults = $db->sql_fetchrow($pCheck)))  {
						$error = TRUE;
						$col = 'red';
						$errText =  ' ' . $user->lang['MAP_PNOTEXIST'];							
					} else {
						if ( !empty($pCheckResults['user_wpuint_id']) ) {
							if (   !(($pCheckResults['user_wpuint_id'] == $doThis['wpID']) && empty($doThis['alreadyID']) && empty($doThis['alreadyUN'])) ) {
								$error = TRUE;
								$col = 'red';
								$errText =   ' ' . $user->lang['MAP_ERR_ALREADYINT'];
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
				$close_para = $user->lang['MAP_ACTIONSEXPLAIN1'];
				$template->assign_block_vars('switch_usermap_process.switch_doactions', array(
					'S_WPMAP_ACTION' => append_sid("index.$phpEx?i=wp_united"),
					'NUM_ROWS' => $ctr - 1,
				));
			} else {
				$close_para = $user->lang['MAP_ERR_GOBACK']; 
			}
		} else {
			$intro_para = $user->lang['MAP_NOWTTODO'];
		} 
		
		$template->assign_vars(array(	
			'L_MAP_ACTINTRO' => $intro_para,
			'L_MAP_ACT_CLOSEPARA' => $close_para,
			'S_NEXTSTART' => $nextStart,
			'S_NUMPERPAGE' => $numPerPage,
		));
		$this->showPage();	
	}
	
	/**
	 * Process each of the actions in the list.
	 */
	function usermap_perform() {	
		global $user, $phpEx, $phpbb_root_path, $wpSettings, $db, $template, $phpbbForum;
		require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		$this->page_title = $user->lang['MAP_TITLE'];
		$this->tpl_name = 'acp_wp_united';

		// set the page section to show
		$template->assign_block_vars('switch_usermap_perform', array()); 	
			
		//Get integration settings
		$wpSettings = get_integration_settings();
		if ( ($wpSettings == FALSE)	|| ($wpSettings['wpPath'] == '') ) {
			trigger_error($user->lang['WP_DBErr_Gen'] . '<br />' . $user->lang['WP_NO_SETTINGS']);
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
		if (!$wpUtdInt->can_connect_to_wp()) {
			die($user->lang['MAP_CANT_CONNECT']);
		}
		$wpUtdInt->enter_wp_integration();
		eval($wpUtdInt->exec()); 
		
		if (file_exists($wpSettings['wpPath'] .'wp-admin/includes/user.php')) {  //WP >= 2.3
			require_once($wpSettings['wpPath'] .'wp-admin/includes/user.php');
		} else {
			require_once($wpSettings['wpPath'] .'wp-admin/admin-db.php'); //WP < 2.3
		}
		require_once($phpbb_root_path . 'wp-united/wpu-actions.' . $phpEx);
		
		$phpbbForum->enter();
		$status = array();
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
								trigger_error($user->lang['MAP_COULDNT_BREAK'] . '<br />' . $user->lang['DB_ERROR']);
							}									
							$status[] = '<li>'. sprintf($user->lang['MAP_BROKE_SUCCESS'], $wpID) . '</li>';
						} else {
							$status[] = '<li>' . $user->lang['MAP_CANNOT_BREAK'] . '</li>';
						}
					break;
					case 'integrate':
						if ( (!empty($wpID)) && (!empty($pID))  ) {
							$sql = 'UPDATE ' . USERS_TABLE .
								" SET user_wpuint_id = $wpID 
								WHERE user_id = $pID";
							if (!$pInt = $db->sql_query($sql)) {
								trigger_error($user->lang['MAP_COULDNT_INT'] . '<br />' . $user->lang['DB_ERROR']);
							}
							// Sync profiles
							$sql = 	"SELECT *
											FROM " . USERS_TABLE . " 
											WHERE user_id = $pID";
							if (!$pUserData = $db->sql_query($sql)) {
								trigger_error($user->lang['MAP_COULDNT_INT'] . '<br />' . $user->lang['DB_ERROR']);
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
							$phpbbForum->leave();
							$wpUsrData = get_userdata($wpID);
							$wpUpdateData = $wpUtdInt->check_details_consistency($wpUsrData, $wpu_newDetails);
							$phpbbForum->enter();
							
							$status[] = '<li>' . sprintf($user->lang['MAP_INT_SUCCESS'], $wpID, $pID) . '</li>';	
						} else {
							$status[] = '<li>' . $user->lang['MAP_CANNOT_INT'] . '</li>';
						}							
					break;
					case 'delete':
						$phpbbForum->leave();
						if ( !empty($wpID) ) {
							wp_delete_user($wpID, $reassign = '0');
							$status[] = '<li>' . sprintf($user->lang['MAP_WPDEL_SUCCESS'], $wpID) . '</li>';
							$nextStart = $nextStart - 1;
						} else {
							$status[] = '<li>' . $user->lang['MAP_CANNOT_DEL'] . '</li>';
						}
						$phpbbForum->enter();
					break;
					case 'createP':
						if (!$wpID || !$typedName) {
							$status[] = '<li>' . $user->lang['MAP_CANNOT_CREATEP_ID'] . '</li>';
						} else {
							$phpbbForum->leave();
							$wpUsr = get_userdata($wpID);
							$phpbbForum->enter();
							$password = $wpUsr->user_pass;
							if(substr($password, 0, 3) == '$P$') {
								$password = substr_replace($password, '$H$', 0, 3);
							}
							
							if ( validate_username($username) === FALSE ) {
								$userToAdd = array(
									'username' => $typedName,
									'user_password' => $password,
									'user_email' => $wpUsr->user_email,
									'user_type' => USER_NORMAL,
									'group_id' => 2  //add to registered users group		
								);
								if (user_add($userToAdd)) {
									$status[] = '<li>'. sprintf($user->lang['MAP_CREATEP_SUCCESS'], $typedName) . '</li>';
								} else {
									$status[] = '<li>' . $user->lang['MAP_CANNOT_CREATEP_NAME'] . '</li>';
								}
							}		
						}
					break;
					default;
						trigger_error($user->lang['MAP_INVALID_ACTION'] . '<br />' . $procAction);
					break;
				}
			} else {
				trigger_error($user->lang['MAP_EMPTY_ACTION'] . '<br />' . $procAction);
			}
		}

		$wpUtdInt->exit_wp_integration();
		
		foreach($status as $statusText) {
			$template->assign_block_vars('switch_usermap_perform.performlist_row', array(
				'LIST_ITEM' => $statusText
			));
		}
		
		if (!empty($paged)) {
			$template->assign_block_vars('switch_usermap_perform.switch_paged', array(
				'L_MAP_NEXTPAGE' => $user->lang['MAP_NEXTPAGE']
			));
		} else {
			$template->assign_block_vars('switch_usermap_perform.switch_unpaged', array(
				'L_MAP_FINISHED' => sprintf($user->lang['MAP_FINISHED'], '<a href="' . append_sid("index.$phpEx?i=wp_united&amp;mode=index") . '">', '</a>', '<a href="' . append_sid("index.$phpEx?i=wp_united&amp;mode=usermap") . '">', '</a>' )
			));
		}
		
		$template->assign_vars(array(	
			'L_MAP_TITLE'  =>	$user->lang['MAP_TITLE'],
			'S_WPMAP_ACTION' => append_sid("index.$phpEx?i=wp_united"),
			'L_MAP_PERFORM_INTRO' => $user->lang['MAP_PERFORM_INTRO'],
			'S_NEXTSTART' => $nextStart,
			'S_NUMPERPAGE' => $numPerPage,
		));		
		$this->showPage();			
	}
	

	/**
	 * Show the Page
	 * Assigns vars & block vars, and displays the page
	 */
	function showPage() {

		global $template, $showFooter; 
		
		if ( $showFooter ) {
			$template->assign_block_vars('switch_show_footer', array());
		}
	}    
	
	/**
	 * Adds a traling slash to a string if one is not already present.
	 */
	function add_trailing_slash($path) {
		return ( $path[strlen($path)-1] == "/" ) ? $path : $path . "/";
	}

	/**
	 * Adds http:// to the URL if it is not already present
	 */
	function add_http($path) {
		if ( (strpos($path, "http://") === FALSE) && (strpos($path, "https://") === FALSE) ) {
			return "http://" . $path;
		}
		return $path;
	}
	
	/**
	 * Clean and standardise a provided file path
	 */
	function clean_path($value) {
		$value = trim($value);
		$value = str_replace('\\', '/', $value);
		$value = (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
		return $value;
	}
	
	/**
	 * Grabs the number from a string.
	 * Could be done very simply but we cannot guarantee where the numbers will occur, due to language differences.
	 */
	function get_page_number($string) {
		preg_match('{(\d+)}', $string, $pResult); 
		return $pResult[1];
	}

	/**
	 * Uses cURL to see if the page exists.
	 * @return bool true unless page times out or returns HTTP 404. 
	 * Returns integer zero if cURL is unavailable
	 */
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


	/**
	 * Gets the filepath & URI of the current script, and then compares it to a provided WordPress URI 
	 * to calculate the difference. Works on Unix-style & Windows servers.
	 */
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


	/**
	 * Simple func that tries to access wp-settings.php (a core WordPress file) locally.
	 */
	function wordpress_exists($wpPath) {
		$wpPath = $this->add_trailing_slash($wpPath);
		$wpPath = str_replace('http://', '', $wpPath); // urls sometimes return true on php 5.. this makes sure they don't.
		$pathToWpSettings = $wpPath . "wp-settings.php";
		return (file_exists($pathToWpSettings));
	}


	/**
	 * Returns an XML message
	 * Used by the wizard for asynchronous communication with the server
	 */
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

	/**
	 * Add ACP Module
	 * Auto-creates an ACP module
	 */
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
	
	
	/**
	 * Returns module ID if module exists
	 */
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

	/**
	 * Removes WP-United ACP modules. Note: Only removes them if they're in the expected place in the tree.
	 */
	function remove_modules() {
		global $user;

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

	/**
	 * Get ACL option IDs
	 * Original author Poyntesm, http://www.phpbb.com/community/viewtopic.php?f=71&t=545415&p=3026305
	 */
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