<?php
/** 
*
* WP-United Settings Panels
*
* @package WP-United
* @version $Id: v0.9.0RC3 2010/07/01 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
*
*	The plugin settings panels.
* 
*/

/**
 * Add menu options for WP-United Settings panel
 */
 add_action('admin_menu', 'wpu_settings_menu');
function wpu_settings_menu() {  
	global $wpUnited;
	
	if (!current_user_can('manage_options'))  {
		return;
	}
	
	if (!function_exists('add_submenu_page')) {
		return;
	}	
	
	if(isset($_POST['wpusettings-transmit'])) { 
		if(check_ajax_referer( 'wp-united-transmit')) {		

			$wpUnited->transmit_settings();
			die();
		}
	}	
	
	if(isset($_GET['page'])) {
		if($_GET['page'] == 'wpu_acp') {
			global $phpbbForum;
			wp_redirect(append_sid($phpbbForum->url .  'adm/index.php', false, true, $GLOBALS['user']->session_id), 302);
			die();
		}
		if($_GET['page'] == 'wpu-user-mapper') {
			if( isset($_POST['wpumapload']) && check_ajax_referer('wp-united-map') ) {
				// Send user mapper html data
				wpu_map_show_data();
				die();
			}
			if(isset($_GET['term']) && check_ajax_referer('wp-united-usersearch')) {
				// send JSON back for autocomplete
				
				$pkg = ($_GET['pkg'] == 'phpbb') ? 'phpbb' : 'wp';
				$term = request_var('term', '');

				require($wpUnited->get_plugin_path() . 'user-mapper.php');
				require($wpUnited->get_plugin_path() . 'mapped-users.php');
				
				$userMapper = new WPU_User_Mapper("leftSide={$pkg}&numToShow=10&numStart=0&showOnlyInt=0
					&showOnlyUnInt=0&showOnlyPosts=0&showOnlyNoPosts=0", 0, $term);
				
				$userMapper->send_json();
				die();
			}
			if( isset($_POST['wpumapaction']) && check_ajax_referer('wp-united-mapaction') ) {
				// Send user mapper html data
				
				wpu_process_mapaction();
				die();
			}
			if( isset($_POST['wpusetperms']) && check_ajax_referer('wp-united-mapaction') ) {
				// Send user mapper html data
				
				wpu_process_perms();
				die();
			}
						
		}
	}	
	
	
	
	wp_register_style('wpuSettingsStyles', $wpUnited->get_plugin_url() . 'theme/settings.css');
	wp_enqueue_style('wpuSettingsStyles'); 
		
	if(isset($_GET['page'])) {
		if(in_array($_GET['page'], array('wp-united-settings', 'wp-united-setup', 'wpu-user-mapper'))) {

			// Deregister wordpress scripts where we want our stuff in front
			$scriptsToDereg = array('jquery', 'jquery-ui-core');//, 'jquery-color');
			foreach($scriptsToDereg as $script) {
				if(wp_script_is($script, 'registered')) {
					wp_deregister_script($script);
				}
			}
			wp_enqueue_script('jquery', $wpUnited->get_plugin_url() . 'js/jquery-wpu-min.js', array(), false, false);
			wp_enqueue_script('jquery-ui-core', $wpUnited->get_plugin_url() . 'js/jqueryui-wpu-min.js', array('jquery'), false, false);
			wp_enqueue_script('filetree', $wpUnited->get_plugin_url() . 'js/filetree.js', array('jquery'), false, false);				
			wp_enqueue_script('colorbox', $wpUnited->get_plugin_url() . 'js/colorbox-min.js', array('jquery'), false, false);				
			wp_enqueue_script('splitter', $wpUnited->get_plugin_url() . 'js/splitter-min.js', array('jquery'), false, false);				
			wp_enqueue_script('jsplumb', $wpUnited->get_plugin_url() . 'js/jsplumb-wpu-min.js', array('jquery', 'jquery-ui-core'), false, false);				
			wp_enqueue_script('wpu-settings', $wpUnited->get_plugin_url() . 'js/settings.js', array('jquery', 'jquery-ui-core'), false, false);				
		}
		if(in_array($_GET['page'], array('wp-united-settings', 'wp-united-setup', 'wpu-user-mapper', 'wpu-advanced-options', 'wp-united-support'))) {
			wp_register_style('wpuSettingsStyles', $wpUnited->get_plugin_url() . 'theme/settings.css');
			wp_enqueue_style('wpuSettingsStyles');
		}
	}	
		
	$top = add_menu_page('WP-United ', 'WP-United', 'manage_options', 'wp-united-setup', 'wpu_setup_menu', $wpUnited->get_plugin_url() . 'images/tiny.gif', 2 );
	add_submenu_page('wp-united-setup', 'WP-United Setup', 'Setup / Status', 'manage_options','wp-united-setup');
		
		
	// only show other menu items if WP-United is set up
	if($wpUnited->get_last_run() == 'working' && $wpUnited->is_enabled()) {
		add_submenu_page('wp-united-setup', 'WP-United Settings', 'Settings', 'manage_options','wp-united-settings', 'wpu_settings_page');

			if($wpUnited->get_setting('integrateLogin')) {
					add_submenu_page('wp-united-setup', 'WP-United User Mapping', 'User Mapping', 'manage_options','wpu-user-mapper', 'wpu_user_mapper');
			}
		add_submenu_page('wp-united-setup', 'WP-United Advanced Options', 'Advanced Options', 'manage_options','wpu-advanced-options', 'wpu_advanced_options');
		add_submenu_page('wp-united-setup', 'Visit phpBB ACP', 'Visit phpBB ACP', 'manage_options', 'wpu_acp', 'wpu_acp');
	}
	
	add_submenu_page('wp-united-setup', 'Please Help Support WP-United!', 'Support WP-United', 'manage_options','wp-united-support', 'wpu_support');
	
}

/** 
 * Just a stub for the menu to redirect to the phpBB ACP
 * We redirect before this is invoked.
 */
function wpu_acp() {
	
}

/**
 * Decide whether to show the advanced options, or save them
 */
function wpu_advanced_options() {

	?>
		<div class="wrap" id="wp-united-setup">
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('WP-United Advanced Options<br />[UNDER CONSTRUCTION - USE SETTINGS PAGE FOR NOW]'); ?> </h2>
		<p><?php _e('Here you can set advanced options that control WP-United by editing the options.php file. You should not normally have to edit these.') ?></p>
		<p><?php _e('Note that these options could be overwritten during a WP-United upgrade.') ?></p>
	<?php
	if(isset($_POST['wpuadvanced-submit'])) {
		// process form
		if(check_admin_referer( 'wp-united-advanced')) {
			wpu_process_advanced_options();
		}
	} else {
			wpu_show_advanced_options();
	}
	?></div> <?php
}

function wpu_support() {
	global $wpUnited;
	?>
	<div class="wrap" id="wp-united-setup">
		<img id="panellogo" src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/seclogo.jpg" />
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('Please Help Support WP-United'); ?> </h2>
		<p><?php _e('WP-United is free software, and we hope you find it useful. If you do, please support us by making a donation here! Any amount, however small, is much appreciated. Thank you!');  ?></p>
		<p><?php _e('The PayPal link will take you to a donation page for our PayPal business account, \'Pet Pirates\'');  ?></p>
		
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="GSBRNNH7REY8Y">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		</form>

			
			
		<h3><?php echo 'Other ways to support the WP-United project'; ?></h3>
		<p><?php _e('If you cannot donate, please consider helping support the WP-United project in another way. For example, you culd help:');  ?></p>
		<ul>
			<li><?php _e('Contributing to the WP-United documentation');  ?></li>
			<li><?php _e('Providing a translation');  ?></li>
			<li><?php _e('Recommending WP-United or our paid installation services');  ?></li>
			<li><?php _e('Link back to www.wp-united.com, or post about WP-United on your blog.');  ?></li>
		</ul>
		
		<p><?php printf(__('For more information, please visit the %1$sWP-United forums%2$s.'), '<a href="http://www.wp-united.com/index.php">', '</a>'); ?></p>

	</div>
	
	<?php
}

/**
 * If settings have been changed and Template Voodoo is active, we reload the page twice in a hidden iFrame in order to reset the styles.
 */
function wpu_reload_preview() {
	global $wpUnited, $phpbbForum;

	$previewUrl = '';
	if ($wpUnited->get_setting('showHdrFtr') == 'FWD') {
		$previewUrl = get_site_url();
	} else if($wpUnited->get_setting('showHdrFtr') == 'REV')  {
		if(is_object($phpbbForum) && !empty($phpbbForum->url)) {
			$previewUrl = $phpbbForum->url;
		}
	}
	if(empty($previewUrl)) {
		return '';
	} 
	?>
	<p id="wpulastprocessing"><?php _e('Performing final processing... Please wait...'); ?></p>
	<iframe id="wpupreviewreload" onload="wpuIncPrevCtr();" src="<?php echo $previewUrl . '?wpurnd=' . rand(100000,999999); ?>" style="float: left;width:1px;height:1px;border: 0;" border="0"></iframe>
	<script type="text/javascript">
	// <![CDATA[ 
		var ctr = 0;
		function wpuIncPrevCtr() {
			if(ctr < 2) {
				ctr++;
				$('#wpulastprocessing').show();
				// in case the site has frame breakout code that tries to redirect this parent page.
				window.onbeforeUnload = function(e) { 
					return '<?php _e('Please stay on this page for a few more moments until processing is complete. Stay on this page?'); ?>';
				};
				try {
					document.getElementById('wpupreviewreload').contentWindow.location.reload(true);
				} catch(e) {}
			} else {
				window.onbeforeunload = null;
				try {
					$('#wpulastprocessing, #wpupreviewreload').hide('slow');
				} catch(e) {}
			}
		}
	// ]]>
	</script>
	<?php
}




function wpu_setup_menu() {
	global $wpUnited;
	
	?>
		<div class="wrap" id="wp-united-setup">
		<img id="panellogo" src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/seclogo.jpg" />
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('WP-United Setup / Status'); ?> </h2>
		<p><?php _e('WP-United needs to connect to phpBB in order to work. On this screen you can set up or disable the connection.') ?></p>

		<div id="wputransmit"><p><strong>Communicating with phpBB...</strong><br />Please Wait</p><img src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/wpuldg.gif" /></div>

	<?php
	
	$needPreview = false;
	$msg = '';
	if(isset($_GET['msg'])) {
		if($_GET['msg'] == 'fail') {
			$msg = (string)stripslashes($_GET['msgerr']);
			$msg = base64_decode(str_replace(array('[pls]', '[eq]'), array('+', '='), $msg));
		} else {
			// $msg is succcess, do preview reloads to init Template Voodoo:
			$needPreview = true;
		}
	}
		
	$buttonDisplay = 'display: block;';
	
	if(!$wpUnited->is_enabled() && ($wpUnited->get_last_run() == 'working')) {
			$statusText = __('Disabled');
			$statusColour = "error";
			$statusDesc = _('WP-United is disabled. Select your forum location below and then click &quot;Connect&quot;') . '<br /><br />' . __('You can\'t change any other settings until WP-United is connected.');
			$buttonDisplay = 'display: block;';		
	} else {
	
		switch($wpUnited->get_last_run()) {
			case 'working':
				$statusText = __('OK');
				$statusColour = "updated allok";
				$statusDesc =  __('WP-United is connected and working.');
				$buttonDisplay = 'display: none;';
				break;
			case 'connected':
				$statusText = __('Connected, but not ready or disabled due to errors');
				$statusColour = "updated highlight allok";
				$statusDesc = sprintf(__('WP-United is connected but your phpBB forum is either producing errors, or is not set up properly. You need to modify your board. %1$sClick here%2$s to download the modification package. You can apply it using %3$sAutoMod%4$s (recommended), or manually by reading the install.xml file and following %5$sthese instructions%6$s. When done, click &quot;Connect&quot; to try again.'), '<a href=\"#\">', '</a>', '<a href=\"http://www.phpbb.com/mods/automod/\">', '</a>', '<a href=\"http://www.phpbb.com/mods/installing/\">', '</a>') .  '<br /><br />' . __('You can\'t change any other settings until the problem is fixed.');
				break;
			default:
				$statusText = __('Not Connected');
				$statusColour = "error";
				$statusDesc = _('WP-United is not connected yet. Select your forum location below and then click &quot;Connect&quot;') . '<br /><br />' . __('You can\'t change any other settings until WP-United is connected.');
				$buttonDisplay = (!$wpUnited->is_enabled()) ? 'display: block;' : 'display: none;';
		}
	}
	
	wpu_panel_warnings();
		
	echo "<div id=\"wpustatus\" class=\"$statusColour\"><p><strong>" . sprintf(__('Current Status: %s'), $statusText) . '</strong>';
	if($wpUnited->get_last_run() == 'working' && $wpUnited->is_enabled()) {
		echo '<button style="float: right;margin-bottom: 6px;" class="button-secondary" onclick="return wpu_manual_disable(\'wp-united-setup\');">Disable</button>';
	}
	echo "<br /><br />$statusDesc";
	if(!empty($msg)) {
		echo '<br /><br /><strong>' . __('The server returned the following information:') . "</strong><br />$msg";
	}
	echo '</p></div>';
	
	if($needPreview) {
		wpu_reload_preview();
	} 
	
	?>
	<h3><?php _e('phpBB Location') ?></h3>
	<form name="wpu-setup" id="wpusetup" method="post" onsubmit="return wpu_transmit('wp-united-setup', this.id);">
		<?php wp_nonce_field('wp-united-setup');  ?>
		
		<p><?php _e('WP-United needs to know where phpBB is installed on your server.'); ?> <span id="txtselpath">Find and select your phpBB's config.php below.</span><span id="txtchangepath" style="display: none;">Click &quot;Change Location&quot; to change the stored location.</span></p>
		<div id="phpbbpath">&nbsp;</div>
		<p>Path selected: <strong id="phpbbpathshow" style="color: red;"><?php echo "Not selected"; ?></strong> <a id="phpbbpathchooser" href="#" onclick="return wpuChangePath();" style="display: none;">Change Location &raquo;</a><a id="wpucancelchange" style="display: none;" href="#" onclick="return wpuCancelChange();">Cancel Change</a></p>
		<input id="wpupathfield" type="hidden" name="wpu-path" value="notset"></input>
	
		<p class="submit">
			<input type="submit" style="<?php echo $buttonDisplay; ?>"; class="button-primary" value="<?php  _e('Connect') ?>" name="wpusetup-submit" id="wpusetup-submit" />
		</p>
	</form>
	</div>
	

	
	<script type="text/javascript">
	// <![CDATA[
		var transmitMessage;
		var filetreeNonce = '<?php echo wp_create_nonce ('wp-united-filetree'); ?>';
		var transmitNonce = '<?php echo wp_create_nonce ('wp-united-transmit'); ?>';
		var disableNonce = '<?php echo wp_create_nonce ('wp-united-disable'); ?>';
		var blankPageMsg = '<?php _e('Blank page received: check your error log.'); ?>';
		var phpbbPath = '<?php echo ($wpUnited->get_setting('phpbb_path')) ? $wpUnited->get_setting('phpbb_path') : ''; ?>';		
		var treeScript =  '<?php echo 'admin.php?page=wp-united-setup'; ?>';
		jQuery(document).ready(function($) { 
			createFileTree();
			<?php if($wpUnited->get_setting('phpbb_path')) { ?> 
				setPath('setup');
			<?php } ?>			
		});
	// ]]>
	</script>

		
<?php

}

function wpu_panel_warnings() {
	global $wpUnited, $phpbbForum;
	
	if(!is_writable($wpUnited->get_plugin_path() . 'cache/')) {
		echo '<div id="cacheerr" class="error highlight"><p>ERROR: Your cache folder (' . $wpUnited->get_plugin_path() . 'cache/) is not writable by the web server. You must make this folder writable for WP-United to work properly!</p></div>';
	}

	if( defined('WPU_CANNOT_OVERRIDE') ) {
		echo '<div id="pluggableerror" class="error highlight"><p>' . __('WARNING: Another plugin is overriding WordPress login. WP-United user integration is unavailable.') . '</p></div>';
	}
	if( defined('DEBUG') || defined('DEBUG_EXTRA') ) {
		echo '<div id="debugerror" class="error highlight"><p>' . __('WARNING: phpBB Debug is set. To prevent notices from showing due to switching between phpBB and WordPress, delete or comment out the two DEBUG lines from your phpBB\'s config.php. If this is a live site, debug MUST be disabled.') . '</p></div>';
	}
	
	if($wpUnited->is_enabled() && $wpUnited->get_setting('integrateLogins') && defined('COOKIE_DOMAIN') && ($phpbbForum->get_cookie_domain() != COOKIE_DOMAIN)) {
		echo '<div id="debugerror" class="error highlight"><p>' . __('WARNING: phpBB and WordPress cookie domains do not match! For user integration to work properly, please edit the cookie domain in phpBB or set the WordPress COOKIE_DOMAIN so that both phpBB &amp; WordPress can set cookies for each other.') . '</p></div>';
	}

}

function wpu_user_mapper() { 
	global $wpUnited; ?>
	<div class="wrap" id="wp-united-setup">
	
		<img id="panellogo" src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/seclogo.jpg" />
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('WP-United User Integration Mapping'); ?> </h2>
		<p><?php _e('Integrated users have an account both in WordPress and phpBB. These accounts are mapped together. Managing user integration between phpBB and WordPress has two aspects:'); ?></p>
		<ul>
			<li><?php echo '<strong>' . __('User Permissions') . ':</strong> ' . __('Setting up permissions so that users can be automatically given mapped accounts'); ?></li>
			<li><?php echo '<strong>' . __('User Mapping') . ':</strong> ' . __('Manually setting up and checking the linkage between user accounts in phPBB and WordPress.'); ?></li>
		</ul>
		<p><?php _e('Select a tab below to get started.'); ?></p>
		<div id="wputabs">
			<ul>
				<li><a href="#wpumaptab-perms">User Permissions</a></li>
				<li><a href="#wpumaptab-map">User Mapping</a></li>
			</ul>

			<div id="wpumaptab-perms">
			
				<p><?php _e('Users are integrated if they have WP-United permissions in phpBB. This way, you have control over which users and groups get automatically integrated. This page provides an easy way to assign permissions.'); ?></p>
				
				<a class="wpuwhatis" href="#" onclick="return $('#wpupermmore').toggle();">More info &raquo;</a>
				<div id="wpupermmore" style="display: none;">
				<p><strong><?php _e('Notes:'); ?></strong></p>
				<ul class="forcebullets">
					<li><?php _e('If a user has an account in phpBB but not in WordPress, and they (or a group they belong to) has WP-United permissins, a WordPress account will be created fore them automatically.'); ?></li>
					<li><?php _e('If a user has an account in WordPress but not phpBB, and the default New Users group (indicated below) has WP-United permissions, a phpBB account will be automatically created for them.'); ?></li>
					<li><?php _e('If a user or group has multiple different WP-United permissions, the highest level shall prevail (i.e. if you set them as an author and an editor, they will be an editor)'); ?></li>
					<li><?php _e('phpBB permissions have three states: <em>Yes</em>, <em>No</em> and <em>Never</em>. A <em>Never</em> setting for a user ensures that they will <strong>never</strong> get that permission. For example, if a user is a member of the <em>Registered Users</em> group which has Subscriber permissions set to <em>Yes</em>, and the <em>Newly Registered Users</em> group, which has Subscriber permissions set to <em>Never</em>, they will not be able to integrate as a subscriber.'); ?></li>
					<li><?php _e('Permissions assigned to individual users are not shown here &ndash; however you can include/exclude specific users using the phpBB permissions system.'); ?></li>
					<li><?php _e('phpBB founder users automatically have all permissions, so they will always integrate with full permissions. For everyone else, you will need to add permissions using the phpBB permissions system.'); ?></li>
				</ul>
				</div>
				<p><?php _e(' Connect a phpBB group on the left to an appropriate WordPress role by dragging the blue dots. Remember that phpBB users can belong to more than one group, so connect the red squares if you want to ensure a mapping <em>never</em> happens.When happy, click &quot;Apply&quot;'); ?></p>
				<?php
					global $phpbbForum, $db;
					$phpbbForum->foreground();
					
					$groupTypes = array(__('Built-In'), __('User-Defined'));
					$numUserDefined = 0;
						
					// Get all the groups, and associated info
					$sqlArr = array(
						'SELECT'			=>	'COUNT(ug.user_id) AS count, g.group_id, g.group_type, g.group_name',
						
						'FROM'			=>	array(
							GROUPS_TABLE	=>	'g',
						),
						
						'LEFT_JOIN'		=>	array(
							array(
								'FROM'	=>	array(USER_GROUP_TABLE	=>	'ug'),
								'ON'			=>	'g.group_id = ug.group_id'
							)
						),
						
						'GROUP_BY'	=>	'g.group_id',
						
						'ORDER_BY'	=> 'g.group_type DESC, g.group_name ASC'
					);

					$sql = $db->sql_build_query('SELECT',$sqlArr);
					$result = $db->sql_query($sql);
	
					$groupData = array();
					while ($row = $db->sql_fetchrow($result)) {
						$groupData[$row['group_id']] = array(
							'type' 						=> 	($row['group_type'] == GROUP_SPECIAL) ? __('Built-In') : __('User-Defined'),
							'name'						=>	(!empty($phpbbForum->lang['G_' . $row['group_name']]))? $phpbbForum->lang['G_' . $row['group_name']] : $row['group_name'],
							'db_name'					=>	$row['group_name'],
							'total_members' 			=> 	$row['count'],
							'url'						=>	$phpbbForum->url . append_sid('adm/index.php?i=permissions&amp;mode=setting_group_global&amp;group_id[0]=' . $row['group_id'], false, true, $GLOBALS['user']->session_id)
						);

						if($groupData[$row['group_id']]['type'] == __('User-Defined')) {
							$numUserDefined++;
						}
					}
					
					$db->sql_freeresult($result);
				?>	
					
				<table class="widefat fixed">
					<?php foreach(array('thead', 'tfoot') as $tblHead) { ?>
						<<?php echo $tblHead; ?>>
						<tr class="thead">
							<th scope="col"><?php _e('phpBB Group'); ?></th>
							<th scope="col" style="text-align: right;"><?php _e('WordPress Role'); ?></th>
						</tr>
						</<?php echo $tblHead; ?>>
					<?php } ?>
					<tbody><tr><td colspan="2">
						<div id="wpuplumbcanvas" class="wpuplumbcanvas" id="wpuplumb<?php echo $typeId; ?>">
							<?php
							$perms = wpu_permissions_list();
							$newUserGroups = $phpbbForum->get_newuser_group();
							$linkages = array();
							$neverLinkages = array();
							$elsL = array();
							$elsR = array();
							$typeId = 0;
				
							foreach ($groupTypes as $type) { 
								$typeId++;
								if(($type == __('Built-In')) || ($numUserDefined > 0)) {

									$effectivePerms = wpu_assess_perms('', false, false); //wpu_get_wp_role_for_group();
									$nevers = wpu_assess_perms('', false, true);
									$linkages[$typeId] = array();
									$neverLinkages[$typeId] = array();
									$elsL[$typeId] = array();
									$elsR[$typeId] = array();
									?><div class="wpuplumbleft"><?php
										foreach ($groupData as $group_id => $row) {
											if($row['type'] == $type) {
												$blockIdL = "wpuperml-{$typeId}-{$row['db_name']}";
												$elsL[$typeId][] = $blockIdL;
												?><div class="wpuplumbgroupl ui-widget-header ui-corner-all" id="<?php echo $blockIdL; ?>">
													<p><strong><?php echo $row['name'];?></strong> <?php if(in_array($row['db_name'], $newUserGroups)) echo ' <span style="color: red;">*</span>'; ?>
													<?php echo '<br /><small><strong>' . __('No. of members: ') . '</strong>' . $row['total_members']; ?><br />
													<?php echo '<strong>' . __('Group type: ') . '</strong>' . $type; ?></small></p>
													<?php 
														if(isset($effectivePerms[$row['name']])) {
															foreach($effectivePerms[$row['name']] as $permItem) {
																$linkages[$typeId][$blockIdL] = "wpupermr-{$permItem}";
															}
														} 
														if(isset($nevers[$row['name']])) {
															foreach($nevers[$row['name']] as $neverItem) {
																$neverLinkages[$typeId][$blockIdL] = "wpupermr-{$neverItem}";
															}
														} 
													?> 
												</div> <?php
											}
										} 
									?></div><?php
								}
							} 
							$phpbbForum->background();
							?>
							<div class="wpuplumbright">
									
								<?php foreach($perms as $permSetting => $wpName) {
									$blockIdR = "wpupermr-{$permSetting}";
									$elsR[$typeId][] = $blockIdR;  ?>
									<div class="wpuplumbgroupr ui-widget-header ui-corner-all" id="<?php echo $blockIdR; ?>">
										<strong><?php echo 'WordPress ' . $wpName; ?></strong>
									</div>
								<?php } ?>
							</div>
							<br style="clear: both;" />
						</div>

					</td></tr></tbody>
				</table>
				<small><em><span style="color: red;">* </span><?php _e('Default new user group for new phpBB users'); ?></em></small>
				<div id="wpupermactions">
					<button class="wpuprocess" onclick="return wpuApplyPerms();"><?php _e('Apply'); ?></button>
					<button class="wpuclear" onclick="return wpuClearPerms();"><?php _e('Reset'); ?></button>
				</div>
				

				<script type="text/javascript"> // <[CDATA[
					function initPlumbing() {
						<?php 
							foreach($elsL as $typeId => $els) {
								foreach($els as $el) { 
									$var = 'plumb' . strtolower(str_replace(array('-', '_'), '', $el));		?>
									var <?php echo $var; ?> = jsPlumb.addEndpoint($('#<?php echo $el; ?>'), {anchor: [1,0.25,1,0], maxConnections: 1, isSource: true}, wpuEndPoint);
									var <?php echo "n$var"; ?> = jsPlumb.addEndpoint($('#<?php echo $el; ?>'), {anchor: [1,0.75,1,0], maxConnections: 1, isSource: true}, wpuNeverEndPoint);
								<?php }
							}
							
							foreach($elsR as $typeId => $els) {
								foreach($els as $el) { 
									$var = 'plumb' . strtolower(str_replace(array('-', '_'), '', $el));		?>
									var <?php echo $var; ?> = jsPlumb.addEndpoint($('#<?php echo $el; ?>'), {anchor: [0,0.25,-1,0], maxConnections: 10, isTarget: true},  wpuEndPoint);
									var <?php echo "n$var"; ?> = jsPlumb.addEndpoint($('#<?php echo $el; ?>'), {anchor: [0,0.75,-1,0], maxConnections: 10, isTarget: true},  wpuNeverEndPoint);
								<?php }
							}
							
							foreach($linkages as $typeId => $linkage) {
								foreach($linkage as $linkL => $linkR) {
									$varL = 'plumb' . strtolower(str_replace(array('-', '_'), '', $linkL));	
									$varR = 'plumb' . strtolower(str_replace(array('-', '_'), '', $linkR));	?>		
									
									jsPlumb.connect({
										source: <?php echo $varL; ?>,
										target: <?php echo $varR; ?>
									});
								<?php }
							}						
							foreach($neverLinkages as $typeId => $linkage) {
								foreach($linkage as $linkL => $linkR) {
									$varL = 'plumb' . strtolower(str_replace(array('-', '_'), '', $linkL));	
									$varR = 'plumb' . strtolower(str_replace(array('-', '_'), '', $linkR));	?>		
									
									jsPlumb.connect({
										source: <?php echo "n$varL"; ?>,
										target: <?php echo "n$varR"; ?>
									});
								<?php }
							}
						?>							
					}
				// ]]>
				</script>				
				
				
			</div>
			<div id="wpumaptab-map">
				<p><?php _e('All your WordPress or phpBB users are shown on the left below, together with their integration status. On the right, you can see their corresponding integrated user, or &ndash; if they are not integrated &ndash; some suggestions for users they could integrate to.'); ?></p>
				<p><?php _e('Choose the actions you wish to take, and then click &quot;Process Actions&quot; in the pop-up panel to apply them..'); ?></p>
				<div class="ui-widget-header ui-corner-all wpumaptoolbar">
					<form name="wpumapdisp" id="wpumapdisp">
						<fieldset>
							<label for="wpumapside"><?php _e('Show on left: '); ?></label>
							<select id="wpumapside" name="wpumapside">
								<option value="wp"><?php _e('WordPress users'); ?></option>
								<option value="phpbb"><?php _e('phpBB users'); ?></option>
							</select> 
							<label for="wpunumshow"><?php _e('Number to show: '); ?></label>
							<select id="wpunumshow" name="wpunumshow">
								<option value="1">1</option>
								<option value="5">5</option>
								<option value="10" selected="selected">10</option>
								<option value="20">20</option>
								<option value="50">50</option>
								<!--<option value="100">100</option>
								<option value="250">250</option>
								<option value="500">500</option>
								<option value="1000">1000</option>-->
							</select> 	
							<label for="wputypeshow"><?php _e('Show: '); ?></label>
							<select id="wputypeshow" name="wputypeshow">
								<option value="all"><?php _e('All'); ?></option>
								<option value="int"><?php _e('All Integrated'); ?></option>
								<option value="unint"><?php _e('All Unintegrated'); ?></option>
								<option value="posts"><?php _e('All With Posts'); ?></option>
								<option value="noposts"><?php _e('All Without Posts'); ?></option>
							</select>
							<input type="hidden" name="wpufirstitem" id="wpufirstitem" value="0" />			
						</fieldset>
					</form>
					<div id="wpumappaginate1" class="wpumappaginate">
					</div>
				</div>

				<div id="wpumapcontainer">
					<div id="wpumapscreen">
						<div class="wpuloading">
							<p><?php _e('Loading...'); ?></p>
							<img src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/wpuldg.gif" />
						</div>
					</div>
					<div id="wpumappanel" class="ui-widget">
						<h3 class="ui-widget-header ui-corner-all"><?php _e('Actions to process'); ?></h3>
						<ul id="wpupanelactionlist">
						</ul>
						<div id="wpupanelactions">
							<small>
								<button class="wpuprocess" onclick="return wpuProcess();"><?php _e('Process actions'); ?></button>
								<button class="wpuclear" onclick="return wpuMapClearAll();"><?php _e('Clear all'); ?></button>
							</small>
						</div>
					</div>
				</div>
				<div class="ui-widget-header ui-corner-all wpumaptoolbar">
					<div id="wpumappaginate2" class="wpumappaginate">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="wpuoffscreen">
	</div>
	<div id="wpu-reload" title="Message" style="display: none;">
		<p id="wpu-desc">&nbsp;</p><img id="wpuldgimg" src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/wpuldg.gif" />
	</div>
	<script type="text/javascript">
	// <![CDATA[
		var mapNonce = '<?php echo wp_create_nonce ('wp-united-map'); ?>';
		var autofillNonce = '<?php echo wp_create_nonce ('wp-united-usersearch'); ?>';
		var firstMapActionNonce = '<?php echo wp_create_nonce ('wp-united-mapaction'); ?>';
		
		var imgLdg						= '<?php echo $wpUnited->get_plugin_url() ?>images/settings/wpuldg.gif';
		var currWpUser				= '<?php echo $GLOBALS['current_user']->ID; ?>';
		var currPhpbbUser			= '<?php echo $phpbbForum->get_userdata('user_id'); ?>';

		var wpText 					=	'<?php _e('WordPress'); ?>';
		var phpbbText 				= '<?php _e('phpBB'); ?>';
		var mapEditTitle 			= '<?php _e('Editing user. When you are finished, close this screen.'); ?>';
		var mapProfileTitle 		= '<?php _e('Viewing user profile. When you are finished, close this screen.'); ?>';
		var actionBreak 			=	'<?php _e('Break integration'); ?>';
		var actionBreakDets 		=	'<?php _e('between %1$s and %2$s'); ?>';
		var actionDelBoth 			=	'<?php _e('Delete '); ?>';
		var actionDelBothDets 	=	'<?php _e('%1$s from %2$s and %3$s from %4$s'); ?>';
		var actionDel 				=	'<?php _e('Delete '); ?>';
		var actionDelDets 			=	'<?php _e('%1$s from %2$s'); ?>';
		var actionCreate			=	'<?php _e('Create '); ?>';
		var actionCreateDets 	=	'<?php _e('integrated counterpart for %1$s in %2$s'); ?>';
		var actionIntegrate		=	'<?php _e('Integrate '); ?>';
		var actionIntegrateDets =	'<?php _e('%1$s user %2$s to %3$s user %4$s'); ?>';
		
		var acpPopupTitle = '<?php _e('phpBB Administration Panel. After saving your settings, close this window to return to WP-United.'); ?>';
		
		jQuery(document).ready(function($) {
			setupUserMapperPage();
			
		});			
	
	// ]]>
	</script>
		
<?php
}



function wpu_process_perms() {
	global $phpbbForum;
	
	$conns = stripslashes(base64_decode(str_replace(array('[pls]', '[eq]'), array('+', '='), (string)$_POST['wpusetperms'])));
	$conns = explode(',', $conns);
	$nevers = stripslashes(base64_decode(str_replace(array('[pls]', '[eq]'), array('+', '='), (string)$_POST['wpusetnevers'])));
	$nevers = explode(',', $nevers);	
	$permsList = array_keys(wpu_permissions_list());
	
	$phpbbForum->clear_group_permissions();

	foreach($conns as $conn) {
		list($phpbbGroup, $wpuPermName) = explode('=', $conn);
		if(in_array($wpuPermName, $permsList)) {
			wpu_set_phpbb_group_permissions($phpbbGroup, $wpuPermName);
		}
	}
	foreach($nevers as $never) {
		list($phpbbGroup, $wpuPermName) = explode('=', $never);
		if(in_array($wpuPermName, $permsList)) {
			wpu_set_phpbb_group_permissions($phpbbGroup, $wpuPermName, ACL_NEVER);
		}
	}

	die('OK');
}	



function wpu_map_show_data() {
	global $wpUnited, $wpdb, $phpbbForum, $db, $user;
	
	$type = (isset($_POST['wpumapside']) && $_POST['wpumapside'] == 'phpbb' ) ? 'phpbb' : 'wp';
	$first = (isset($_POST['wpufirstitem'])) ? (int) $_POST['wpufirstitem'] : 0;
	$num = (isset($_POST['wpunumshow'])) ? (int) $_POST['wpunumshow'] : 50;
	
	$showOnlyInt = ((isset($_POST['wputypeshow'])) && ($_POST['wputypeshow'] == 'int')) ? 1 : 0;
	$showOnlyUnInt = ((isset($_POST['wputypeshow'])) && ($_POST['wputypeshow'] == 'unint')) ? 1 : 0;
	$showOnlyPosts = ((isset($_POST['wputypeshow'])) && ($_POST['wputypeshow'] == 'posts')) ? 1 : 0;
	$showOnlyNoPosts = ((isset($_POST['wputypeshow'])) && ($_POST['wputypeshow'] == 'noposts')) ? 1 : 0;
	
	require($wpUnited->get_plugin_path() . 'user-mapper.php');
	require($wpUnited->get_plugin_path() . 'mapped-users.php');
	
	$userMapper = new WPU_User_Mapper("leftSide={$type}&numToShow={$num}&numStart={$first}&showOnlyInt={$showOnlyInt}
		&showOnlyUnInt={$showOnlyUnInt}&showOnlyPosts={$showOnlyPosts}&showOnlyNoPosts={$showOnlyNoPosts}");

	$alt = '';
	
	wpu_ajax_header();
	
	echo '<wpumapper>';
	
	$fStateChanged = $phpbbForum->foreground();
	$pagination = generate_pagination('#', $userMapper->num_users(), $num, $first, true);
	$pagination = str_replace('<a ', '<a onclick="return wpuMapPaginate(this);"', $pagination);
	$phpbbForum->background($fStateChanged);
	
	$total = $userMapper->num_users();
	$to = (($first + $num) > $total) ? $total : ($first + $num);
	$package = ($type == 'phpbb') ? __('phpBB') : __('WordPress');
	$packageUsers = ($total > 1) ? sprintf(__('%s users'), $package) :  sprintf(__('%s user'), $package);

	echo '<pagination><![CDATA[<p><em class="wpumapcount">' . sprintf(__('Showing %1$d to %2$d of %3$d %4$s.'), ($first + 1), $to, $total, $packageUsers) . ' </em>' . $pagination . '</p>]]></pagination>';
	
	echo '<mapcontent><![CDATA[';
	
	$haveUnintegratedUsers = false;
	$haveIntegratedUsers = false;
	
	if($total == 0) {
		echo '<em id="wpumaptable">' . __('There are no users to show that match your criteria') . '</em>';
	} else {
		?><table id="wpumaptable"><?php
		foreach($userMapper->users as $userID => $user) { 
			?>
			<tr class="wpumaprow<?php echo $alt; ?>"  id="wpuuser<?php echo $userID ?>">
				<td> 
					<?php echo $user; ?>
				</td><td>
				<?php if(!$user->is_integrated()) { 
					$haveUnintegratedUsers = true; ?>
				
					<div class="wpuintegnot ui-widget-header ui-corner-all">
						<p>Status: <?php _e('Not Integrated'); ?></p>
						<p class="wpubuttonset">
							<?php echo $user->create_action(); ?>
							<?php echo $user->del_action(); ?>
						</p>
					</div>
					</td><td>
					<div class="wpumapsugg">
					<p class="wpuintto"><?php _e('Integrate to a suggested match'); ?>:</p>
						<div class="wpudetails">
							<?php echo $user->get_suggested_matches(); ?>
						</div>
						<p class="wpuintto"><?php _e('Or, type a name'); ?>:</p>
						<div class="wpuavatartyped" id="wpuavatartyped<?php echo $userID; ?>"></div><input class="wpuusrtyped" id="wpumapsearch-<?php echo $userID; ?>" /> <small class="wpubuttonset"><a href="#" class="wpumapactionlnktyped" onclick="return false;" id="wpumapfrom-<?php echo $userID; ?>"><?php _e('Integrate'); ?></a></small>
					</div>
				<?php } else { 
					$haveIntegratedUsers = true;?>
					<div class="wpuintegok ui-widget-header ui-corner-all">
						<p>Status: Integrated</p>
						<p class="wpubuttonset">
							<?php echo $user->break_action(); ?>
							<?php echo $user->delboth_action(); ?>
						</p>
					</div>
				</td><td>
					<?php echo $user->get_partner(); ?>
				<?php } ?>
				</td>
			</tr>
			<?php 
			$alt = ($alt == '') ? ' wpualt' : '';
		}
		echo '</table>';
	}
	
	echo ']]></mapcontent><bulk><![CDATA[';
	if($total>0) {
		echo '<div id="wpubulk"><select id="wpuquicksel" name="wpuquicksel">
			<option value="0">---- Bulk actions ----</option>';
		if($haveUnintegratedUsers) {
			echo '<option value="del">Delete all unintegrated</option>';
		}
		if($haveIntegratedUsers) {
			echo '<option value="break">Break all integrated</option>';
		}
		if($haveUnintegratedUsers) {
			echo  '<option value="create">Create users for all unintegrated</option>';
		}				
		echo '</select><button id="wpuquickselbtn" onclick="return wpuMapBulkActions();">' . __('Add') . '</button></div>';
	}
	echo ']]></bulk></wpumapper>';
	
}




/**
 * Perform an action requested by the user mapper
 */
function wpu_process_mapaction() {
	global $phpbbForum, $db, $wpdb, $phpbb_root_path, $phpEx;
	
	wpu_ajax_header();
	echo '<wpumapaction>';
	
	$action = (isset($_POST['type'])) ? (string)$_POST['type'] : '';
	$userID = (isset($_POST['userid'])) ? (int)$_POST['userid'] : 0;
	$intUserID = (isset($_POST['intuserid'])) ? (int)$_POST['intuserid'] : 0;
	$package = (isset($_POST['package'])) ? (string)$_POST['package'] : '';
	
	if(
		empty($action) || 
		empty($userID) || 
		empty($package) || 
		(($action == 'delboth') && empty($intUserID)) ||
		(($action == 'break') && empty($intUserID))
	) {
		wpu_map_action_error('Cannot perform action, required details are missing');
	}
	
	require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
	
	
	switch($action) {
		
		case 'del':
			if($package == 'wp') {
				// First break if the user is integrated
				wpu_map_break($userID);
				wp_delete_user($userID, '0');
			} else {
				$fStateChanged = $phpbbForum->foreground();
				user_delete('retain', $userID);
				$phpbbForum->background($fStateChanged);
			}
			echo '<status>OK</status>';
		break;

		case 'delboth':
			$wUserID = ($package == 'wp') ? $userID : $intUserID;
			$pUserID = ($package == 'wp') ? $intUserID : $userID;

			wp_delete_user($wUserID, '0');
			$fStateChanged = $phpbbForum->foreground();
			user_delete('retain', $pUserID);
			$phpbbForum->background($fStateChanged);
			echo '<status>OK</status>';
		break;		
		
		case 'integrate':
			
			$wUserID = ($package == 'wp') ? $userID : $intUserID;
			$pUserID = ($package == 'wp') ? $intUserID : $userID;
		
			if ( (!empty($wUserID)) && (!empty($pUserID))  ) {
				
				$fStateChanged = $phpbbForum->foreground();
				
				$sql = 'UPDATE ' . USERS_TABLE .
					" SET user_wpuint_id = $wUserID 
					WHERE user_id = $pUserID";
				if (!$pInt = $db->sql_query($sql)) {
					die('<status>FAIL</status><details>Database error: Could not integrate</details></wpumapaction>');
				}
				// Sync profiles
				$wpuNewDetails = $phpbbForum->get_userdata('', $pUserID);
				$phpbbForum->background($fStateChanged);
				$wpUsrData = get_userdata($wUserID);
				wpu_sync_profiles($wpUsrData, $wpuNewDetails, 'sync');
				echo '<status>OK</status>';
			}
		break;
		
		case 'break':
			$id = ($package == 'wp') ? $userID : $intUserID;
			wpu_map_break($id);
			echo '<status>OK</status>';
		break;
		
		case 'createin':
		
			// create user in phpBB
			if($package == 'phpbb') {
				$phpbbID = wpu_create_phpbb_user($userID);
					
				if($phpbbID == 0) {
					die('<status>FAIL</status><details>Could not add user to phpBB</details></wpumapaction>');
				} else if($phpbbID == -1) {
					die('<status>FAIL</status><details>A suitable username could not be found in phpBB</details></wpumapaction>');
				}
				wpu_sync_profiles(get_userdata($userID), $phpbbForum->get_userdata('', $phpbbID), 'sync');
				
			} else {

				// create user in WordPress
				$wpuNewDetails = $phpbbForum->get_userdata('', $userID);
				
				require_once( ABSPATH . WPINC . '/registration.php');
				
				if( !$userLevel = wpu_get_user_level() ) {
					die('<status>FAIL</status><details>Cannot create integrated user, as they would have no integration permissions.</details></wpumapaction>');
				}
				$phpbbForum->transition_user();
				
				$newUserID = wpu_create_wp_user($wpuNewDetails['username'], $password, $wpuNewDetails);
						
				if($newUserID) { 
					if($wpUser = get_userdata($newUserID)) { 
						wpu_update_int_id($userID, $wpUser->ID);
						
						wpu_sync_profiles($wpUser, $wpuNewDetails, 'sync');

						wpu_set_role($wpUser->ID, $userLevel);
						$phpbbForum->transition_user($userID, $wpuNewDetails->user_ip);
					}
				} else {
					die('<status>FAIL</status><details>Could not add user to WordPress</details></wpumapaction>');
				}				
				
			}
			
		echo '<status>OK</status>';

		break;
		
	}
	echo '<nonce>' . wp_create_nonce('wp-united-mapaction') . '</nonce>';
	echo '</wpumapaction>';
	die();	
	
}


function wpu_map_break($intID) {
	global $phpbbForum, $db;
	$fStateChanged = $phpbbForum->foreground();
	$sql = 'UPDATE ' . USERS_TABLE . ' 
				 SET user_wpuint_id = NULL 
				WHERE user_wpuint_id = ' . $intID;

	if (!$pDel = $db->sql_query($sql)) {
		wpu_map_action_error('Error when breaking integration');
	}
	$phpbbForum->background($fStateChanged);	
	
	wpu_map_killusermeta($intID);
	
}

function wpu_map_killusermeta($intID) {
	//update usermeta on WP side
	if(function_exists('delete_user_meta')) {
		@delete_user_meta($intID, 'phpbb_userid');
		@delete_user_meta($intID, 'phpbb_userLogin');
	} else {
		@delete_usermeta( $intID, 'phpbb_userid');
		@delete_usermeta( $intID, 'phpbb_userLogin');
	}	
}

function wpu_map_action_error($errDesc) {
		echo '<status>ERROR</status>';
		echo '<details>' . $errDesc . '</details>';
		echo '</wpumapaction>';
		die();
	
}

	
/**
 * The main WP-United settings panel
 */	
function wpu_settings_page() {	
	
	global $phpbbForum, $wpUnited; 

	$needPreview = false;
	?>
	
	<div class="wrap" id="wp-united-setup">
		<img id="panellogo" src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/seclogo.jpg" />
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('WP-United Settings'); ?> </h2>
	
			<div id="wputransmit"><p><strong><?php _e('Sending settings to phpBB...'); ?></strong><br />Please Wait</p><img src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/wpuldg.gif" /></div>
			
			<?php
				if(isset($_GET['msg'])) {
					if($_GET['msg'] == 'success') {
						$needPreview = true;
			?>
			<div id="wpustatus" class="updated"><p><?php _e('Settings applied successfully.'); ?></p></div>
			<?php
				} elseif($_GET['msg'] == 'fail') {
			?>
					<div id="wpustatus" class="error">
						<p><?php _e('An error occurred. The error details are below. Please check your settings or try disabling plugins.'); ?></p>
						<div style="margin-bottom: 8px;" id="wpuerrordets">
							<?php echo (string)stripslashes($_GET['msgerr']); ?>
						</div>
					</div>
			<?php
				}
			}
			
			wpu_panel_warnings();
			
			if($needPreview) {
				wpu_reload_preview();
			} 
			
			
			?>
			<p><?php _e('WP-United is modular; You can enable or disable any of the four major features below: User Integration, Theme Integration, Behaviour Integration and User Blogs.') ?></p>
			<p><?php _e('Visit each of the tabs to select the settings, then hit Submit when done.') ?></p>
					
			<form name="wpu-settings" id="wpusettings" method="post" onsubmit="return wpu_transmit('wp-united-settings', this.id);">
				
				<div id="wputabs">
					<ul>
						<li><a href="#wputab-basic">Basic Settings</a></li>
						<?php if(!defined('WPU_CANNOT_OVERRIDE')) { ?>
							<li><a href="#wputab-user">User Integration</a></li>
						<?php } ?>
						<li><a href="#wputab-theme">Theme Integration</a></li>
						<li><a href="#wputab-behav">Behaviour Integration</a></li>
					<!--	<li><a href="#wputab-blogs">User Blogs</a></li>-->
					</ul>

					<div id="wputab-basic">
						<h3>Path to phpBB3</h3>
						<p>WP-United needs to know where phpBB is installed on your server. You can change the location on the &quot;Setup / Status&quot; page.</p>
					
						<p>Path selected: <strong id="phpbbpathshow" style="color: red;"><?php echo "Not selected"; ?></strong> <a href="admin.php?page=wp-united-setup" id="phpbbpathchange">Change Location &raquo;</a></p>
						<input id="wpupathfield" type="hidden" name="wpu-path" value="notset"></input>
						<h3>Forum Page</h3>
						<p>Create a WordPress forum page? If you enable this option, WP-United will create a blank page in your WordPress installation, so that 'Forum' links appear in your blog. These links will automatically direct to your forum.</p>
						<input type="checkbox" id="wpuforumpage" name="wpuforumpage" <?php if($wpUnited->get_setting('useForumPage')) { ?>checked="checked"<?php } ?> /><label for="wpuforumpage">Enable Forum Page</label>		
					</div>
					<?php if(!defined('WPU_CANNOT_OVERRIDE')) { ?>
						<div id="wputab-user">
							
							<?php
								if($wpUnited->get_setting('integrateLogin')) {
									$setPerms = array_keys(wpu_assess_perms());
									if(sizeof($setPerms)) {
										$integratedGroups = implode(', ', $setPerms);
									} else {
										$integratedGroups = __('None');
									}
									$newUsersCan = (sizeof(wpu_assess_newuser_perms())) ?__('Yes') : __('No: Appropriate permissions in phpBB are not set');
								
									echo '<div id="wpuintegsetupstatus" class="highlight"><h4>' . __('Current status:') . '</h4>';
									echo '<ul><li><strong>' . __('phpBB groups that can automatically integrate: ') . '</strong>' . $integratedGroups . '</li>';
									echo '<li><strong>' . __('New WordPress users can be given phpBB accounts? ') . '</strong>' . $newUsersCan . '</li></ul>';
									echo '<p><small><em>' . __('Users are integrated according to WP-United permissions in phpBB. For more information and to change these, see <a href="admin.php?page=wpu-user-mapper">User Mapping &rarr; User Permissions</a>.') . '</p></small></em></div>';
								}
							?>

							<h3>Integrate logins?</h3>
							<p>This will enable some or all of your users to have a seamless session across both phpBB and WordPress. If they are logged in to one, they will be logged in to the other. Accounts will be created in the respective part of the site as needed. Note that you will need to set permissions in the User Mapper section that will appear once this option is enabled. Otherwise, by default, only the phpBB founder user is integrated.</p>
							

							<input type="checkbox" id="wpuloginint" name="wpuloginint" <?php if($wpUnited->get_setting('integrateLogin')) { ?>checked="checked"<?php } ?> /><label for="wpuloginint">Enable Login Integration?</label>		
							
							<div id="wpusettingsxpost" class="subsettings">
								
								<h4>Sync avatars?</h4>
								<p>Avatars will be synced between phpBB &amp; WordPress. If a user has an avatar in phpBB, it will show in WordPress. If they have a Gravatar, it will show in phpBB.</p>
								<p>Enabling this option requires that the &quot;Allow avatars&quot; and &quot;Remote avatar linking&quot; options is enabled in phpBB, so WP-United will automatically enable those options for you if they are disabled.</p>
								<input type="checkbox" id="wpuavatar" name="wpuavatar" <?php if($wpUnited->get_setting('avatarsync')) { echo ' checked="checked" '; } ?>/><label for="wpusmilies">Sync avatars?</label>	
						
								
								
								<h4>Enable cross-posting?</h4>
								<p>If you enable this option, users will be able to elect to have their blog entry copied to a forum when writing a blog post. To set which forums the user can cross-post to, visit the phpBB forum permissions panel, and enable the &quot;can cross-post&quot; permission for the users/groups/forums combinations you need.</p>
								<input type="checkbox" id="wpuxpost" name="wpuxpost" <?php if($wpUnited->get_setting('xposting')) { ?>checked="checked"<?php } ?> /><label for="wpuxpost">Enable Cross-Posting?</label>		
								
								
								<div id="wpusettingsxpostxtra" class="subsettings">
									<h4>Type of cross-posting?</h4>
									<p>Choose how the post should appear in phpBB. WP-United can post an excerpt, the full post, or give you an option to select when posting each post.</p>
									<input type="radio" name="rad_xpost_type" value="excerpt" id="wpuxpexc"  <?php if($wpUnited->get_setting('xposttype') == 'excerpt') { ?>checked="checked"<?php } ?>  /><label for="wpuxpexc">Excerpt</label>
									<input type="radio" name="rad_xpost_type" value="fullpost" id="wpuxpfp" <?php if($wpUnited->get_setting('xposttype') == 'fullpost') { ?>checked="checked"<?php } ?>  /><label for="wpuxpfp">Full Post</label>
									<input type="radio" name="rad_xpost_type" value="askme" id="wpuxpask" <?php if($wpUnited->get_setting('xposttype') == 'askme') { ?>checked="checked"<?php } ?>  /><label for="wpuxpask">Ask Me</label>
									
									<h4>phpBB manages comments on crossed posts?</h4>
									<p>Choose this option to have WordPress comments replaced by forum replies for cross-posted blog posts. In addition, comments posted by integrated users via the WordPress comment form will be cross-posted as replies to the forum topic.</p>
									<input type="checkbox" name="wpuxpostcomments" id="wpuxpostcomments" <?php if($wpUnited->get_setting('xpostautolink')) { ?>checked="checked"<?php } ?> /><label for="wpuxpostcomments">phpBB manages comments</label>		
									
									<h4>Force all blog posts to be cross-posted?</h4>
									<p>Setting this option will force all blog posts to be cross-posted to a specific forum. You can select the forum here. Note that users must have the &quot;can cross-post&quot; WP-United permission under phpBB Forum Permissions, or the cross-posting will not take place.</p>
									<select id="wpuxpostforce" name="wpuxpostforce">
										<option value="-1" <?php if($wpUnited->get_setting('xpostforce') == -1) { echo ' selected="selected" '; } ?>>-- Disabled --</option>
										
										<?php
										if(defined('IN_PHPBB')) { 
											global $phpbbForum, $db;
											$fStateChanged = $phpbbForum->foreground();
											$sql = 'SELECT forum_id, forum_name FROM ' . FORUMS_TABLE . ' WHERE ' .
												'forum_type = ' . FORUM_POST;
											if ($result = $db->sql_query($sql)) {
												while ( $row = $db->sql_fetchrow($result) ) {
													echo '<option value="' . $row['forum_id'] . '"';
													if($wpUnited->get_setting('xpostforce') == (int)$row['forum_id']) {
														 echo ' selected="selected" ';
													}
													echo '>' . $row['forum_name'] . '</option>';
												}
											}
											$phpbbForum->restore_state($fStateChanged);
										}
									?>								
										
									</select>
								</div>				
							</div>
						</div>	
					<?php } ?>	
					
					<div id="wputab-theme">
						<h3>Integrate themes?</h3>
						<p>WP-United can integrate your phpBB &amp; WordPress templates.</p>
						<input type="checkbox" id="wputplint" name="wputplint" <?php if($wpUnited->get_setting('showHdrFtr') != 'NONE') { ?>checked="checked" <?php } ?> /><label for="wputplint">Enable Theme Integration</label>
						<div id="wpusettingstpl" class="subsettings">
							<h4>Integration Mode</h4>
							<p>Do you want WordPress to appear inside your phpBB template, or phpBB to appear inside your WordPress template?</p>
							
							<input type="radio" name="rad_tpl" value="rev" id="wputplrev"  <?php if($wpUnited->get_setting('showHdrFtr') != 'FWD') { ?>checked="checked" <?php } ?> /><label for="wputplrev">phpBB inside WordPress</label>
							<input type="radio" name="rad_tpl" value="fwd" id="wputplfwd" <?php if($wpUnited->get_setting('showHdrFtr') == 'FWD') { ?>checked="checked" <?php } ?>  /><label for="wputplfwd">WordPress inside phpBB</label>
							
						
							<h4>Automatic CSS Integration</h4>
							
							<p>WP-United can automatically fix CSS conflicts between your phpBB and WordPress templates. Set the slider to "maximum compatibility" to fix most problems. If you prefer to fix CSS conflicts by hand, or if the automatic changes cause problems, try reducing the level.</p>
							
							<div style="padding: 0 100px;">
								<p style="height: 11px;"><span style="float: left;">Off</span><span style="float: right;">Maximum Compatibility (Recommended)</span></p>
								<div id="wpucssmlvl"></div>
								<div id="cssmdesc"><p><strong>Current Level: <span id="cssmlvltitle">xxx</span></strong><br /></p><p id="cssmlvldesc">xxx</p></div>
							</div>
							<input type="hidden" id="wpucssmlvlfield" name="wpucssmlevel" value="notset"></input>
							<p><a id="wputpladvancedstgs" href="#" onclick="return tplAdv();"><span id="wutpladvshow">Show Advanced Settings &raquo;</span><span id="wutpladvhide" style="display: none;">&laquo; Hide Advanced Settings</span></a></p>
							
							<div id="wpusettingstpladv" class="subsettings">
								<h4>Advanced Settings</h4>
								<p><strong>Use full page?</strong>
									<a class="wpuwhatis" href="#" title="Do you want phpBB to simply appear inside your WordPress header and footer, or do you want it to show up in a fully featured WordPress page? Simple header and footer will work best for most WordPress themes – it is faster and less resource-intensive, but cannot display dynamic content on the forum page. However, if you want the WordPress sidebar to show up, or use other WordPress features on the integrated page, you could try 'full page'. This option could be a little slower.">What is this?</a>
								</p>
								<select id="wpuhdrftrspl" name="wpuhdrftrspl">
									
									<option value="0"<?php if($wpUnited->get_setting('wpSimpleHdr') == 1) { echo ' selected="selected" '; } ?>>-- Simple Header &amp; Footer (recommended) --</option>
									<?php
										$files = scandir(TEMPLATEPATH);
										if(sizeof($files)) {
											foreach($files as $file) {
												// no stripos for ph4 compatibility
												if(strpos(strtolower($file), '.php') == (strlen($file) - 4)) {
													echo '<option value="' . $file . '"';
													if( ($wpUnited->get_setting('wpPageName') == $file) && ($wpUnited->get_setting('wpSimpleHdr') == 0) ) {
														echo ' selected="selected" ';
													}
													echo '>Full Page: ' . $file . '</option>';
												}
											}
										}
									?>
								</select>
								
								<p><strong>Padding around phpBB</strong>
								<?php $padding = explode('-', $wpUnited->get_setting('phpbbPadding')); ?>
								
									<a class="wpuwhatis" href="#" title="phpBB is inserted on the WordPress page inside a DIV. Here you can set the padding of that DIV. This is useful because otherwise the phpBB content may not line up properly on the page. The defaults here are good for most WordPress templates. If you would prefer set this yourself, just leave these boxes blank (not '0'), and style the 'phpbbforum' DIV in your stylesheet.">What is this?</a>
								</p>
									<table>
										<tr>
											<td>
												<label for="wpupadtop">Top:</label><br />
											</td>
											<td>
												<input type="text" onkeypress="checkPadding(event)" maxlength="3" style="width: 30px;" id="wpupadtop" name="wpupadtop" value="<?php echo $padding[0]; ?>" />px<br />
											</td>
										</tr>
										<tr>
											<td>
												<label for="wpupadright">Right:</label><br />
											</td>
											<td>
												<input type="text" onkeypress="checkPadding(event)" maxlength="3" style="width: 30px;" id="wpupadright" name="wpupadright" value="<?php echo $padding[1]; ?>" />px<br />
											</td>
										</tr>
										<tr>
											<td>
												<label for="wpupadbtm">Bottom:</label><br />
											</td>
											<td>
												<input type="text" onkeypress="checkPadding(event)" maxlength="3" style="width: 30px;" id="wpupadbtm" name="wpupadbtm" value="<?php echo $padding[2]; ?>" />px<br />
											</td>
										</tr>
										<tr>
											<td>
												<label for="wpupadleft">Left:</label><br />
											</td>
											<td>
												<input type="text" onkeypress="checkPadding(event)" maxlength="3" style="width: 30px;" id="wpupadleft" name="wpupadleft" value="<?php echo $padding[3]; ?>" />px<br />
											</td>
										</tr>
										</table>
									<p><a href="#" onclick="return false;">Reset to defaults</a></p>
									
									<p>
										<input type="checkbox" id="wpudtd" name="wpudtd" <?php if($wpUnited->get_setting('dtdSwitch')) { echo ' checked="checked" '; } ?>/> <label for="wpudtd"><Strong>Use Different Document Type Declaration?</Strong></label>
										<a class="wpuwhatis" href="#" title="The Document Type Declaration, or DTD, is provided at the top of all web pages to let the browser know what type of markup language is being used. phpBB3's prosilver uses an XHTML 1.0 Strict DTD by default. Most WordPress templates, however, use an XHTML 1 transitional  or XHTML 5 DTD. In most cases, this doesn't matter -- however, If you want to use WordPress' DTD on pages where WordPress is inside phpBB, then you can turn this option on. This should prevent browsers from going into quirks mode, and will ensure that even more WordPress templates display as designed.">What is this?</a>
									</p>
								</div>
						</div>
					</div>
					
					<div id="wputab-behav">

						<h3>Use phpBB Word Censor?</h3>
						<p>Turn this option on if you want WordPress posts to be passed through the phpBB word censor.</p>
						<input type="checkbox" id="wpucensor" name="wpucensor" <?php if($wpUnited->get_setting('phpbbCensor')) { echo ' checked="checked" '; } ?>/><label for="wpucensor">Enable word censoring in WordPress</label>
						
						<h3>Use phpBB smilies?</h3>
						<p>Turn this option on if you want to use phpBB smilies in WordPress comments and posts.</p>
						<input type="checkbox" id="wpusmilies" name="wpusmilies" <?php if($wpUnited->get_setting('phpbbSmilies')) { echo ' checked="checked" '; } ?>/><label for="wpusmilies">Use phpBB smilies in WordPress</label>	
						
					</div>
					
					<!--<div id="wputab-blogs">
						User Blogs - options being revamped
					</div>-->
				</div>
				
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Submit') ?>" name="wpusettings-submit" />
			</p>
		</form>
		
		<div id="wpu-dialog" title="Message" style="display: none;">
			<p id="wpu-desc">&nbsp;</p>
		</div>
		
	</div>
	
		<script type="text/javascript">
		// <![CDATA[
			var transmitMessage;
			var transmitNonce = '<?php echo wp_create_nonce ('wp-united-transmit'); ?>';
			var disableNonce = '<?php echo wp_create_nonce ('wp-united-disable'); ?>';
			var blankPageMsg = '<?php _e('Blank page received: check your error log.'); ?>';
			var phpbbPath = '<?php echo ($wpUnited->get_setting('phpbb_path')) ? $wpUnited->get_setting('phpbb_path') : ''; ?>';		
			var treeScript =  '<?php echo $wpUnited->get_plugin_url() . 'js/filetree.php'; ?>';
			<?php 
					$cssmVal = 0;
					if($wpUnited->get_setting('cssMagic')){
						$cssmVal++;
					}
					if($wpUnited->get_setting('templateVoodoo')){
						$cssmVal++;
					}
			?>
			var cssmVal = '<?php echo $cssmVal; ?>';

			jQuery(document).ready(function($) { 
				
				setupSettingsPage();
				<?php if($wpUnited->get_setting('phpbb_path')) { ?> 
					setPath('settings');
				<?php } ?>	
					setupHelpButtons();
					settingsFormSetup();
		
			});
		// ]]>
		</script>	

<?php }




/**
 * Process settings
 */
function wpu_process_settings() {
	global $wpUnited, $wpdb; 

	$type = 'setup';
	if(isset($_GET['page'])) {
		if($_GET['page'] == 'wp-united-settings') {
			$type = 'settings';
		}
	}
	
	$data = array();

	/**
	 * First process path to phpBB
	 */
	if(!isset($_POST['wpu-path'])) {
		die('[ERROR] ERROR: You must specify a valid path for phpBB\'s config.php');
	}
	$wpuPhpbbPath = (string)$_POST['wpu-path'];
	$wpuPhpbbPath = str_replace('http:', '', $wpuPhpbbPath);
	$wpuPhpbbPath = add_trailing_slash($wpuPhpbbPath);
	if(!file_exists($wpUnited->get_plugin_path()))  {
		die('[ERROR] ERROR:The path you selected for phpBB\'s config.php is not valid');
		return;
	}
	if(!file_exists($wpuPhpbbPath . 'config.php'))  {
		die('[ERROR] ERROR: phpBB\'s config.php could not be found at the location you chose');
		return;
	}
	if($type=='setup') {
		$data['phpbb_path'] = $wpuPhpbbPath;
	}
	
	$wpUnited->update_settings($data);

	if($type == 'settings') {
		/**
		 * Process 'use forum page'
		 */
		$data['useForumPage'] = isset($_POST['wpuforumpage']) ? 1 : 0;
		
		$forum_page_ID = get_option('wpu_set_forum');
		if ( !empty($data['useForumPage']) ) {
			$content = '<!--wp-united-phpbb-forum-->';
			$title = __('Forum');
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
		
		/** 
		 * Process login integration settings
		 */
		$data['integrateLogin'] = (isset($_POST['wpuloginint']) && (!defined('WPU_CANNOT_OVERRIDE')) ) ? 1 : 0;
		
		if($data['integrateLogin']) {
			
			
			$data['xposting'] =   (isset($_POST['wpuxpost'])) ? 1 : 0;
			$data['avatarsync'] = (isset($_POST['wpuavatar'])) ? 1 : 0;
			
			if($data['xposting'] ) { 
				
				$xpostType = (!isset($_POST['rad_xpost_type'])) ? 'excerpt' : $_POST['rad_xpost_type'];
				if($xpostType == 'askme') {
					$data['xposttype'] ='askme';
				} else if($xpostType == 'fullpost') {
					$data['xposttype'] ='fullpost';
				} else {
					$data['xposttype'] ='excerpt';
				}
				
				$data['xpostautolink'] =(isset($_POST['wpuxpostcomments'])) ? 1 : 0;
				$data['xpostforce'] =( isset($_POST['wpuxpostforce'])) ? (int) $_POST['wpuxpostforce'] : -1;
			} else {
				//cross-posting disabled, set to default
				$data = array_merge($data, array(
					'xposttype' 		=> 'excerpt',
					'wpuxpostcomments'	=> 0,
					'xpostforce' 		=> -1
				));
			}
		} else {
			// logins not integrated, set to default
			$data = array_merge($data, array(
				'xposting' 				=> 0,
				'xposttype' 			=> 'excerpt',
				'wpuxpostcomments'		=> 0,
				'xpostforce' 			=> -1,
				'avatarsync'			=> 0
			));
		}
			
			
		/**
		 * Process 'theme integration' settings
		 */
		
		 $tplInt = isset($_POST['wputplint']) ? 1 : 0;

		if($tplInt) {
			$tplDir = isset($_POST['rad_tpl']) ? (string) $_POST['rad_tpl'] : 'fwd';
			
			if($tplDir == 'rev') {
				$data['showHdrFtr'] = 'REV';
			} else {
				$data['showHdrFtr'] = 'FWD';
			}
			
			$cssmLevel = isset($_POST['wpucssmlevel']) ? (int) $_POST['wpucssmlevel'] : 2;
			switch($cssmLevel) {
				case 0:
					$data['cssMagic'] = 0;
					$data['templateVoodoo'] = 0;
					break;
				case 1:
					$data['cssMagic'] = 1;
					$data['templateVoodoo'] = 0;
					break;
				default:
					$data['cssMagic'] = 1;
					$data['templateVoodoo'] = 1;	
			}
			
			$simpleHeader = (isset($_POST['wpuhdrftrspl'])) ?  $_POST['wpuhdrftrspl'] : 0;
			
			// set defaults
			$data['wpSimpleHdr'] = 1;
			$data['wpPageName'] = 'page.php';	

			if(!empty($simpleHeader)) {
				// we would check for existence of the file, but TEMPLATEPATH isn't initialised here yet.
				$data['wpSimpleHdr'] = 0;
				$data['wpPageName'] = $simpleHeader;
			} 
			
			$padT = isset($_POST['wpupadtop']) ? $_POST['wpupadtop'] : '';
			$padR = isset($_POST['wpupadright']) ? $_POST['wpupadright'] : '';
			$padB = isset($_POST['wpupadbtm']) ? $_POST['wpupadbtm'] : '';
			$padL = isset($_POST['wpupadleft']) ? $_POST['wpupadleft'] : '';

			if ( ($padT == '') && ($padR == '') && ($padB == '') && ($padL == '') ) {
				$data['phpbbPadding'] = 'NOT_SET';
			} else {
				$data['phpbbPadding'] = (int)$padT . '-' . (int)$padR . '-' . (int)$padB . '-' . (int)$padL;
			}
			
			$data['dtdSwitch'] =(isset($_POST['wpudtd'])) ? 1 : 0;
			
		} else {
			$data = array_merge($data, array(
				'showHdrFtr' 			=> 'NONE',
				'cssMagic' 				=> 0,
				'templateVoodoo' 	=> 0,
				'wpSimpleHdr' 		=> 1,
				'wppageName' 		=> 'page.php',
				'phpbbPadding' 		=>  '6-12-6-12',
				'dtdSwitch' 				=> 0
			));
		}
		
		/**
		 * Process 'behaviour' settings
		 */
		$data = array_merge($data, array(
			'phpbbCensor' 	=> (isset($_POST['wpucensor'])) ? 1 : 0,
			'phpbbSmilies' 	=> (isset($_POST['wpusmilies'])) ? 1 : 0,
			//'mustLogin' 		=> (isset($_POST['wpuprivate'])) ? 1 : 0
		));
		
	}

	
	$wpUnited->update_settings($data);
}


function wpu_panel_error($type, $text) {
	
	echo '<div id="message" class="error"><p>' . $text . '</p></div>';
	if($type=='settings') {
		wpu_settings_page();
	} else {
		wpu_show_setup_menu();
	}
	
}


function wpu_show_advanced_options() {	
	
	global $phpbbForum; 
	?>

		<form name="wpu-advoptions" id="wpuoptions" action="admin.php?page=wp-united-advanced" method="post">
		
		_EDITOR_HERE_
		
		<?php wp_nonce_field('wp-united-advanced'); ?>
		
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php  _e('Save') ?>" name="wpuadvanced-submit" />
		</p>
		
		</form>
		
	<?php

}

function wpu_process_advanced_options() {	
	echo "SAVED";
	wpu_show_advanced_options();
}

function wpu_ajax_header() {
	header('Content-Type: application/xml'); 
	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');
	
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
}


function wpu_filetree() {
	if(stristr($_POST['filetree'], '..')) {
		die();
	}

	$docRoot =  (isset($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF']) );
	$docRoot = @realpath($docRoot); 
	$docRoot = str_replace( '\\', '/', $docRoot);
	$docRoot = ($docRoot[strlen($docRoot)-1] == '/') ? $docRoot : $docRoot . '/';

	$fileLoc = str_replace( '\\', '/', urldecode($_POST['filetree']));

	if(stristr($fileLoc, $docRoot) === false) {
		$fileLoc = $docRoot . $fileLoc;
		$fileLoc = str_replace('//', '/', $fileLoc);
	}

	if( file_exists($fileLoc) ) {
		$files = scandir($fileLoc);
		natcasesort($files);
		if( count($files) > 2 ) { /* The 2 accounts for . and .. */
			echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
			// All dirs
			foreach( $files as $file ) {
				if( file_exists($fileLoc. $file) && $file != '.' && $file != '..' && is_dir($fileLoc . $file) ) {
					echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($fileLoc . $file) . "/\">" . htmlentities($file) . "</a></li>";
				}
			}
			// All files
			foreach( $files as $file ) {
				if( file_exists($fileLoc . $file) && $file != '.' && $file != '..' && !is_dir($fileLoc . $file) ) {
					$ext = preg_replace('/^.*\./', '', $file);
					echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($fileLoc . $file) . "\">" . htmlentities($file) . "</a></li>";
				}
			}
			echo "</ul>";	
		}
	}
	die();
	
}




?>