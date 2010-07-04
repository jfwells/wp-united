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
function wpu_settings_menu() {  
	global $wpuUrl, $wpSettings;
	
	if (!current_user_can('manage_options'))  {
		return;
	}
	
	if (!function_exists('add_submenu_page')) {
		return;
	}	
	
	if(isset($_POST['wpusettings-transmit'])) {
		if(check_ajax_referer( 'wp-united-transmit')) {		
			wpu_transmit_settings();
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
				wpu_map_show_data();
				die();
			}
		}
	}	
	
	
	
	wp_register_style('wpuSettingsStyles', $wpuUrl . 'theme/settings.css');
	wp_enqueue_style('wpuSettingsStyles'); 
		
	if(isset($_GET['page'])) {
		if(in_array($_GET['page'], array('wp-united-settings', 'wp-united-setup', 'wpu-user-mapper'))) {

			wp_deregister_script( 'jquery' );
			wp_deregister_script( 'jquery-ui-core' );				
			wp_deregister_script( 'jquery-color' );				
			
			wp_enqueue_script('jquery-bleedingedge', $wpuUrl . 'js/jquery-wpu-min.js', array(), false, false);
			wp_enqueue_script('jqueryui-bleedingedge', $wpuUrl . 'js/jqueryui-wpu-min.js', array('jquery-bleedingedge'), false, false);
			wp_enqueue_script('filetree', $wpuUrl . 'js/filetree.js', array('jquery-bleedingedge'), false, false);				
			wp_enqueue_script('colorbox', $wpuUrl . 'js/colorbox-min.js', array('jquery-bleedingedge'), false, false);				
			wp_enqueue_script('wpu-settings', $wpuUrl . 'js/settings.js', array('jquery-bleedingedge', 'jqueryui-bleedingedge'), false, false);				
		}
		if(in_array($_GET['page'], array('wp-united-settings', 'wp-united-setup', 'wpu-user-mapper', 'wpu-advanced-options', 'wp-united-support'))) {
			wp_register_style('wpuSettingsStyles', $wpuUrl . 'theme/settings.css');
			wp_enqueue_style('wpuSettingsStyles');
		}
	}	
		
	$top = add_menu_page('WP-United ', 'WP-United', 'manage_options', 'wp-united-setup', 'wpu_setup_menu', $wpuUrl . 'images/tiny.gif', 2 );
	add_submenu_page('wp-united-setup', 'WP-United Setup', 'Setup / Status', 'manage_options','wp-united-setup');
		
		
	$status = (isset($wpSettings['status'])) ? $wpSettings['status'] : 0;
	
	// only show other menu items if WP-United is set up
	if($status == 2) {
		add_submenu_page('wp-united-setup', 'WP-United Settings', 'Settings', 'manage_options','wp-united-settings', 'wpu_settings_page');

			if(isset($wpSettings['integrateLogin'])) {
				if($wpSettings['integrateLogin']) {
					add_submenu_page('wp-united-setup', 'WP-United User Mapping', 'User Mapping', 'manage_options','wpu-user-mapper', 'wpu_user_mapper');
				}
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
	global $wpuUrl; 
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
	global $wpuUrl;
	?>
	<div class="wrap" id="wp-united-setup">
		<img id="panellogo" src="<?php echo $wpuUrl ?>/images/settings/seclogo.jpg" />
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

function wpu_setup_menu() {
	global $wpuUrl, $wpuPath; 
	$settings = wpu_get_settings();
	
	?>
		<div class="wrap" id="wp-united-setup">
		<img id="panellogo" src="<?php echo $wpuUrl ?>/images/settings/seclogo.jpg" />
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('WP-United Setup / Status'); ?> </h2>
		<p><?php _e('WP-United needs to connect to phpBB in order to work. On this screen you can set up or disable the connection.') ?></p>

		<div id="wputransmit"><p><strong>Communicating with phpBB...</strong><br />Please Wait</p><img src="<?php echo $wpuUrl ?>/images/settings/wpuldg.gif" /></div>

	<?php
	
	$msg = '';
	if(isset($_GET['msg'])) {
		if($_GET['msg'] == 'fail') {
			$msg = (string)stripslashes($_GET['msgerr']);
			$msg = base64_decode(str_replace(array('[pls]', '[eq]'), array('+', '='), $msg));
		}
	}
				
	$status = (isset($settings['status'])) ? $settings['status'] : 0;
	
	$buttonDisplay = 'display: block;';
	
	switch($status) {
		case 2:
			$statusText = __('OK');
			$statusColour = "updated allok";
			$statusDesc =  __('WP-United is connected and working.');
			$buttonDisplay = 'display: none;';
			break;
		case 1:
			$statusText = __('Connected, but not ready or disabled due to errors');
			$statusColour = "updated highlight allok";
			$statusDesc = sprintf(__('WP-United is connected but your phpBB forum is either producing errors, or is not set up properly. You need to modify your board. %1$sClick here%2$s to download the modification package. You can apply it using %3$sAutoMod%4$s (recommended), or manually by reading the install.xml file and following %5$sthese instructions%6$s. When done, click &quot;Connect&quot; to try again.'), '<a href=\"#\">', '</a>', '<a href=\"http://www.phpbb.com/mods/automod/\">', '</a>', '<a href=\"http://www.phpbb.com/mods/installing/\">', '</a>') .  '<br /><br />' . __('You can\'t change any other settings until the problem is fixed.');
			break;
		case 0:
		default:
			$statusText = __('Not Connected');
			$statusColour = "error";
			$statusDesc = _('WP-United is not connected yet. Select your forum location below and then click &quot;Connect&quot;') . '<br /><br />' . __('You can\'t change any other settings until WP-United is connected.');
			$buttonDisplay = (isset($settings['phpbb_path'])) ? 'display: block;' : 'display: none;';
	}
	
	if(!is_writable($wpuPath . 'cache/')) {
		echo '<div id="cacheerr" class="error highlight"><p>ERROR: Your cache folder (' . $wpuPath . 'cache/) is not writable by the web server. You must make this folder writable for WP-United to work properly!</p></div>';
	}

	if( defined('WPU_CANNOT_OVERRIDE') ) {
		echo '<div id="pluggableerror" class="error highlight"><p>WARNING: Another plugin is overriding WordPress login. WP-United user integration is unavailable.</p></div>';
	}
	if( defined('DEBUG') || defined('DEBUG_EXTRA') ) {
		echo '<div id="debugerror" class="error highlight"><p>WARNING: phpBB Debug is set. To prevent notices from showing due to switching between phpBB and WordPress, delete or comment out the two DEBUG lines from your phpBB\'s config.php. If this is a live site, debug MUST be disabled.</p></div>';
	}
	
			
	echo "<div id=\"wpustatus\" class=\"$statusColour\"><p><strong>" . sprintf(__('Current Status: %s'), $statusText) . '</strong>';
	if($status == 2) {
		echo '<button style="float: right;margin-bottom: 6px;" class="button-secondary" onclick="return wpu_manual_disable(\'wp-united-setup\');">Disable</button>';
	}
	echo "<br /><br />$statusDesc";
	if(!empty($msg)) {
		echo '<br /><br /><strong>' . __('The server returned the following information:') . "</strong><br />$msg";
	}
	echo '</p></div>';
		
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
		var transmitNonce = '<?php echo wp_create_nonce ('wp-united-transmit'); ?>';
		var disableNonce = '<?php echo wp_create_nonce ('wp-united-disable'); ?>';
		var blankPageMsg = '<?php _e('Blank page received: check your error log.'); ?>';
		var phpbbPath = '<?php echo (isset($settings['phpbb_path'])) ? $settings['phpbb_path'] : ''; ?>';		
		var treeScript =  '<?php echo $wpuUrl . 'js/filetree.php'; ?>';
		jQuery(document).ready(function($) { 
			createFileTree();
			<?php if(isset($settings['phpbb_path'])) { ?> 
				setPath('setup');
			<?php } ?>			
		});
	// ]]>
	</script>

		
<?php

}

function wpu_user_mapper() { 
	global $wpuUrl; ?>
	<div class="wrap" id="wp-united-setup">
		<img id="panellogo" src="<?php echo $wpuUrl ?>/images/settings/seclogo.jpg" />
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('WP-United User Integration Mapping'); ?> </h2>
		<p><?php _e('Integrated users have an account both in WordPress and phpBB. These accounts are mapped together. Managing user integration between phpBB and WordPress has two aspects:'); ?></p>
		<ul>
			<li><?php echo '<strong>' . __('User Permissions') . ':</strong> ' . __('Setting up permissions so that users can be automatically given mapped accounts'); ?></li>
			<li><?php echo '<strong>' . __('User Mapping') . ':</strong> ' . __('Manually setting up the linkage between user accounts that already existed in both phPBB and WordPress before you installed WP-United, or manually changing linkages.'); ?></li>
		</ul>
		<p><?php _e('Select a tab below to get started.'); ?></p>
		<div id="wputabs">
			<ul>
				<li><a href="#wpumaptab-perms">User Permissions</a></li>
				<li><a href="#wpumaptab-map">User Mapping</a></li>
			</ul>

			<div id="wpumaptab-perms">
			
				<p><?php _e('Users are integrated if they have WP-United permissions in phpBB. This way, you can choose to integrate only some of your users. (For example, you could allow only phpBB administrators to write posts, or only a specific subset of members to be able to post comments.'); ?></p>
				<p><strong><?php _e('Notes:'); ?></strong></p>
				<ul class="forcebullets">
					<li><?php _e('If a user or group has multiple different WP-United permissions, the highest level shall prevail (i.e. if you set them as an author and an editor, they will be an editor)'); ?></li>
					<li><?php _e('phpBB permissions have three states: <em>Yes</em>, <em>No</em> and <em>Never</em>. A <em>Never</em> setting for a user ensures that they will <strong>never</strong> get that permission. For example, if a user is a member of the <em>Registered Users</em> group which has Subscriber permissions set to <em>Yes</em>, and the <em>Newly Registered Users</em> group, which has Subscriber permissions set to <em>Never</em>, they will not be able to integrate as a subscriber.'); ?></li>
					<li><?php _e('Permissions assigned to individual users are not shown &ndash; however you can include/exclude specific users using the phpBB permissions system.'); ?></li>
					<li><?php _e('phpBB founder users automatically have all permissions, so they will always integrate with full permissions. For everyone else, you will need to add permissions using the phpBB permissions system.'); ?></li>
				</ul>
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
							'total_members' 	=> 	$row['count'],
							'url'							=>	$phpbbForum->url . append_sid('adm/index.php?i=permissions&amp;mode=setting_group_global&amp;group_id[0]=' . $row['group_id'], false, true, $GLOBALS['user']->session_id)
						);
						if($groupData[$row['group_id']]['type'] == __('User-Defined')) {
							$numUserDefined++;
						}
					}
					
					$db->sql_freeresult($result);
					
					$perms = wpu_permissions_list();
					$permSettings = wpu_assess_perms(); 
			
					foreach ($groupTypes as $type) { ?>
						<h4><?php echo "$type Groups"; ?></h4>
						<?php if(($type == __('Built-In')) || ($numUserDefined > 0)) { ?>
							<p><?php printf(__('Use the quick links below to change the permissions in a popup panel, or visit the %1$sphpBB ACP%2$s for more options.'), '<a href="' . append_sid($phpbbForum->url .  'adm/index.php', false, true, $GLOBALS['user']->session_id)  .'">', '</a>'); ?></p>
							<table class="widefat fixed">
								<?php foreach(array('thead', 'tfoot') as $tblHead) { ?>
									<<?php echo $tblHead; ?>>
										<tr class="thead">
											<th scope="col"><?php _e('Group Name'); ?></th>
											<th scope="col"><?php _e('No. of members'); ?></th>
											<th scope="col"><?php _e('WP-United Permissions'); ?></th>
										</tr>
									</<?php echo $tblHead; ?>>
								<?php } ?>
								<tbody>
								<?php
								$it = 0;
								
								foreach ($groupData as $group_id => $row) {
									if($row['type'] == $type) {
										
										$class = ($it == 0) ? ' class="alternate" ' : '';
										
										?>
										<tr <?php echo $class; ?>>
											<td><p><?php echo $row['name']; ?><br />
											
											<small><a class="wpuacppopup" href="<?php echo $row['url']; ?>" title = "<?php _e('This will open in a popup panel'); ?>"><?php _e('Edit Group Permissions'); ?></a></small></p></td>
											<td><?php echo $row['total_members']; ?></td>
											<td><?php 
												if(isset($permSettings[$row['name']])) {
													// search from bottom-up in the standard wp-united permissions
													$nevers =$yes =  array();
													foreach($perms as $perm => $permText)  {
														foreach($permSettings[$row['name']] as $permSetting) {
															if($permSetting['perm'] == $perm) {
																if($permSetting['setting'] == ACL_NEVER) {
																	$nevers[] = array(
																		'perm' 		=>	$permText,
																		'rolename' 	=>	$permSetting['rolename'],
																		'roleurl'		=>	$phpbbForum->url . append_sid('adm/index.php?i=permission_roles&amp;mode=' . $permSetting['roletype'] . '_roles&amp;action=edit&amp;role_id=' . $permSetting['roleid'], false, true, $GLOBALS['user']->session_id)
																	);
																} elseif($permSetting['setting'] == ACL_YES) {
																	$yes = array(
																		'perm' 		=>	$permText,
																		'rolename' 	=>	$permSetting['rolename'],
																		'roleurl'		=>	$phpbbForum->url . append_sid('adm/index.php?i=permission_roles&amp;mode=' . $permSetting['roletype'] . '_roles&amp;action=edit&amp;role_id=' . $permSetting['roleid'], false, true, $GLOBALS['user']->session_id)
																	);
																}
															}
														}
													}
													foreach($nevers as $never) {
														$roleText = (!empty($never['rolename'])) ? '<br /><small>' . sprintf(__('Set by role: %1$s %2$sEdit Role%3$s'), $never['rolename'], '<a href="' . $never['roleurl'] . '" class="wpuacppopup"  title = "This will open in a popup panel">', '</a>') . '</small>' : '';
														echo '<p class="wpupermnever">' . sprintf(__('Can NEVER integrate as a WordPress %s'), __($never['perm'])) . $roleText . '</p>';
													}
													
													if(sizeof($yes)) {
														$roleText = (!empty($yes['rolename'])) ? '<br /><small>' . sprintf(__('Set by role: %1$s %2$sEdit Role%3$s'), $yes['rolename'], '<a href="' . $yes['roleurl'] . '" class="wpuacppopup"  title = "This will open in a popup panel">', '</a>') . '</small>' : '';
														echo '<p class="wpupermyes">' . sprintf(__('Can integrate as a WordPress %s'), __($yes['perm'])) . $roleText . '</p>';
													}
																					
																	
												} else {
													echo '<p style="font-weight: bold; text-align: center;">' . __('No WP-United permissions set') . '</p>';
												}
												?>
											</td>
										</tr>
										<?php
										$it = ($it == 0) ? 1 : 0;
									}
								} ?>
								</tbody>
							</table>
						<?php  } else {
							echo '<p>' . sprintf(__('No %s groups to show'), $type) . '</p>';
						}
					}
					$phpbbForum->background();
				?>
			</div>
			<div id="wpumaptab-map">
				<p>The user mapping tool is under construction.</p>
				<form name="wpumapdisp" id="wpumapdisp">
					<fieldset>
						<label for="wpumapside">Show: </label>
						<select id="wpumapside" name="wpumapside">
							<option value="wp">WordPress users</option>
							<option value="phpbb">phpBB users</option>
						</select> |
						<label for="wpunumshow">Number to show: </label>
						<select id="wpunumshow" name="wpunumshow">
							<option value="0">Dynamic</option>
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="250">250</option>
							<option value="500">500</option>
							<option value="1000">1000</option>
						</select> |			
						<label for="wputypeshow">Type to show: </label>
						<select id="wputypeshow" name="wputypeshow">
							<option value="all">All</option>
							<option value="int">All Integrated</option>
							<option value="unint">All Unintegrated</option>
							<option value="posts">All With Posts</option>
							<option value="noposts">All Without Posts</option>
						</select> 					
					</fieldset>
				</form>
				<div id="wpumapscreen">
					<p>Loading...</p>
					<img src="<?php echo $wpuUrl ?>/images/settings/wpuldg.gif" />
				</div>
			</div>
		</div>
	</div>
	<div id="wpu-reload" title="Message" style="display: none;">
		<p id="wpu-desc">&nbsp;</p><img src="<?php echo $wpuUrl ?>/images/settings/wpuldg.gif" />
	</div>
	<script type="text/javascript">
	// <![CDATA[
		var acpPopupTitle = '<?php _e('phpBB Administration Panel. After saving your settings, close this window to return to WP-United.'); ?>';
		
		var mapNonce = '<?php echo wp_create_nonce ('wp-united-map'); ?>';
		jQuery(document).ready(function($) {
			$('#wputabs').tabs();
			setupAcpPopups();
			
			$("#wpumapdisp select").bind('change', function() {
				wpuShowMapper();
			});
			wpuShowMapper();
		});	
		
		function wpuShowMapper() {
			$('#wpumapscreen').html('<p>Loading</p><img src="<?php echo $wpuUrl ?>/images/settings/wpuldg.gif" />');
						
			$(document).ajaxError(function(e, xhr, settings, exception) {
	
				if(exception == undefined) {
					var exception = 'Server ' + xhr.status + ' error. Please check your server logs for more information.';
				}
				$('#wpumapscreen').html(errMsg = settings.url + ' returned: ' + exception);
			});
			
			
			
			$.post('admin.php?page=wpu-user-mapper', 'wpumapload=1&_ajax_nonce=' + mapNonce, function(response) {
				$('#wpumapscreen').html(response);
			});
		}
		
		
		
	// ]]>
	</script>
		
<?php
}

function wpu_map_show_data() {
	global $wpdb, $phpbbForum;
	
	$sql = "SELECT count(*) AS total
			FROM {$wpdb->users}";
		
	$countEntries = $wpdb->get_results($sql);
	$numWpResults = $countEntries[0]->total;
	
	$first = 0;
	$last = 1000;
	
	$sql = "SELECT ID
			FROM {$wpdb->users}
			ORDER BY user_login
			LIMIT $first, $last";

	$results = $wpdb->get_results($sql);
	
	foreach ((array) $results as $item => $result) {
		
		// TODO in wp3 should be get_user_meta
		$user = new WP_User($result->ID);
		
		?>
		<div class="wpuwpuser" id="wpuuser<?php echo $result->ID; ?>">
			<p class="wpuwplogin"><a href="#"><?php echo $user->user_login; ?></a></p>
			<?php echo get_avatar($user->ID, 50); ?>
			
			<div style="float: left;">
				<p class="wpuwpdetails"><strong>Display name:</strong> <?php echo $user->display_name; ?></p>
				<p class="wpuwpdetails"><strong>E-mail:</strong> <?php echo $user->user_email; ?></p>
				<p class="wpuwpdetails"><strong>Website:</strong> <?php echo !empty($user->user_url) ? $result->user_url : 'n/a' ; ?></p>
				<p class="wpuwpdetails"><strong><?php (sizeof($user->roles) > 1) ? _e('Roles:') : _e('Role:'); ?></strong> <?php 
					foreach($user->roles as $roleItem => $role) {
						echo $role;
						if($roleItem < (sizeof($roles) - 1)) {
							echo ', ';
						}
					} ?></p>
				<p class="wpuwpdetails"><strong>Posts:</strong> <?php /* @TODO in wp3: this is count_user_posts() */ echo (string)get_usernumposts($result->ID); ?> / 
				<strong>Comments:</strong> <?php 
					$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = %d ", $result->ID));
					echo (string)$count;
				
				 ?></p>
				<p class="wpuwpdetails"><strong>Integration status:</strong> <?php 
					$colour = 'red';
					$status = 'Not integrated';
					if ( (isset($user->phpbb_userid)) && (!empty($user->phpbb_userid)) ) {
						
						$phpbbForum->foreground();
						
						/**
						 * @ TODO Check that user is integrated from phpBB side
						 * 
						 */
						
						$phpbbForum->background();
						
						$colour = 'green';
						$status = 'Integrated';
						
					}
					echo '<span style="color: ' . $colour . '">' . $status . '</span>';
						
				
				
				?>
			</div>
			<br />
		</div>
		<?php 
		
		if($item < sizeof($results) - 1) echo  '<hr />'; 
			
	}
	
	
}
	
/**
 * The main WP-United settings panel
 */	
function wpu_settings_page() {	
	
	global $phpbbForum, $wpuUrl; 
	$settings = wpu_get_settings();
	
	?>
	
	<div class="wrap" id="wp-united-setup">
		<img id="panellogo" src="<?php echo $wpuUrl ?>/images/settings/seclogo.jpg" />
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('WP-United Settings'); ?> </h2>
	
			<div id="wputransmit"><p><strong>Sending settings to phpBB...</strong><br />Please Wait</p><img src="<?php echo $wpuUrl ?>/images/settings/wpuldg.gif" /></div>
			
			<?php
				if(isset($_GET['msg'])) {
					if($_GET['msg'] == 'success') {
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
			
			if( defined('WPU_CANNOT_OVERRIDE') ) {
				echo '<div id="pluggableerror" class="error highlight"><p>WARNING: Another plugin is overriding WordPress login. WP-United user integration is unavailable.</p></div>';
			}
			if( defined('DEBUG') || defined('DEBUG_EXTRA') ) {
				echo '<div id="debugerror" class="error highlight"><p>WARNING: phpBB Debug is set. To prevent notices from showing due to switching between phpBB and WordPress, delete or comment out the two DEBUG lines from your phpBB\'s config.php. If this is a live site, debug MUST be disabled.</p></div>';
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
						<input type="checkbox" id="wpuforumpage" name="wpuforumpage" <?php if(!empty($settings['useForumPage'])) { ?>checked="checked"<?php } ?> /><label for="wpuforumpage">Enable Forum Page</label>		
					</div>
					<?php if(!defined('WPU_CANNOT_OVERRIDE')) { ?>
						<div id="wputab-user">
							<h3>Integrate logins?</h3>
							<p>If you turn this option on, phpBB will create a WordPress account the first time each phpBB user <strong>with appropriate permissions</strong> visits the blog. If this WordPress install will be non-interactive (e.g., a blog by a single person, a portal page, or an information library with commenting disabled), you may want to turn this option off, as readers may not need accounts. You can also map existing WordPress users to phpBB users, using the mapping tool that will appear after you turn on this option.</p>
							<p>You <strong>must set</strong> the privileges for each user using the WP-United permissions under the phpBB3 Users' and Groups' permissions settings.</p>
							<input type="checkbox" id="wpuloginint" name="wpuloginint" <?php if(!empty($settings['integrateLogin'])) { ?>checked="checked"<?php } ?> /><label for="wpuloginint">Enable Login Integration?</label>		
							
							<div id="wpusettingsxpost" class="subsettings">
								<h4>Enable cross-posting?</h4>
								<p>If you enable this option, users will be able to elect to have their blog entry copied to a forum when writing a blog post. To set which forums the user can cross-post to, visit the phpBB forum permissions panel, and enable the &quot;can cross-post&quot; permission for the users/groups/forums combinations you need.</p>
								<input type="checkbox" id="wpuxpost" name="wpuxpost" <?php if(!empty($settings['xposting'])) { ?>checked="checked"<?php } ?> /><label for="wpuxpost">Enable Cross-Posting?</label>		
								
								
								<div id="wpusettingsxpostxtra" class="subsettings">
									<h4>Type of cross-posting?</h4>
									<p>Choose how the post should appear in phpBB. WP-United can post an excerpt, the full post, or give you an option to select when posting each post.</p>
									<input type="radio" name="rad_xpost_type" value="excerpt" id="wpuxpexc"  <?php if($settings['xposttype'] == 'excerpt') { ?>checked="checked"<?php } ?>  /><label for="wpuxpexc">Excerpt</label>
									<input type="radio" name="rad_xpost_type" value="fullpost" id="wpuxpfp" <?php if($settings['xposttype'] == 'fullpost') { ?>checked="checked"<?php } ?>  /><label for="wpuxpfp">Full Post</label>
									<input type="radio" name="rad_xpost_type" value="askme" id="wpuxpask" <?php if($settings['xposttype'] == 'askme') { ?>checked="checked"<?php } ?>  /><label for="wpuxpask">Ask Me</label>
									
									<h4>phpBB manages comments on crossed posts?</h4>
									<p>Choose this option to have WordPress comments replaced by forum replies for cross-posted blog posts. In addition, comments posted by integrated users via the WordPress comment form will be cross-posted as replies to the forum topic.</p>
									<input type="checkbox" name="wpuxpostcomments" id="wpuxpostcomments" <?php if(!empty($settings['xpostautolink'])) { ?>checked="checked"<?php } ?> /><label for="wpuxpostcomments">phpBB manages comments</label>		
									
									<h4>Force all blog posts to be cross-posted?</h4>
									<p>Setting this option will force all blog posts to be cross-posted to a specific forum. You can select the forum here. Note that users must have the &quot;can cross-post&quot; WP-United permission under phpBB Forum Permissions, or the cross-posting will not take place.</p>
									<select id="wpuxpostforce" name="wpuxpostforce">
										<option value="0" <?php if($wpSettings['xpostforce'] == 0) { echo ' selected="selected" '; } ?>>-- Disabled --</option>
										
										<?php
										if(defined('IN_PHPBB')) { 
											global $phpbbForum, $db;
											$fStateChanged = $phpbbForum->foreground();
											$sql = 'SELECT forum_id, forum_name FROM ' . FORUMS_TABLE . ' WHERE ' .
												'forum_type = ' . FORUM_POST;
											if ($result = $db->sql_query($sql)) {
												while ( $row = $db->sql_fetchrow($result) ) {
													echo '<option value="' . $row['forum_id'] . '"';
													if($wpSettings['xpostforce'] == (int)$row['forum_id']) {
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
						<input type="checkbox" id="wputplint" name="wputplint" <?php if($settings['showHdrFtr'] != 'NONE') { ?>checked="checked" <?php } ?> /><label for="wputplint">Enable Theme Integration</label>
						<div id="wpusettingstpl" class="subsettings">
							<h4>Integration Mode</h4>
							<p>Do you want WordPress to appear inside your phpBB template, or phpBB to appear inside your WordPress template?</p>
							
							<input type="radio" name="rad_tpl" value="rev" id="wputplrev"  <?php if($settings['showHdrFtr'] != 'FWD') { ?>checked="checked" <?php } ?> /><label for="wputplrev">phpBB inside WordPress</label>
							<input type="radio" name="rad_tpl" value="fwd" id="wputplfwd" <?php if($settings['showHdrFtr'] == 'FWD') { ?>checked="checked" <?php } ?>  /><label for="wputplfwd">WordPress inside phpBB</label>
							
						
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
									
									<option value="0"<?php if($settings['wpSimpleHdr'] == 1) { echo ' selected="selected" '; } ?>>-- Simple Header &amp; Footer (recommended) --</option>
									<?php
										$files = scandir(TEMPLATEPATH);
										if(sizeof($files)) {
											foreach($files as $file) {
												// no stripos for ph4 compatibility
												if(strpos(strtolower($file), '.php') == (strlen($file) - 4)) {
													echo '<option value="' . $file . '"';
													if( ($settings['wpPageName'] == $file) && ($settings['wpSimpleHdr'] == 0) ) {
														echo ' selected="selected" ';
													}
													echo '>Full Page: ' . $file . '</option>';
												}
											}
										}
									?>
								</select>
								
								<p><strong>Padding around phpBB</strong>
								<?php $padding = explode('-', $settings['phpbbPadding']); ?>
								
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
										<input type="checkbox" id="wpudtd" name="wpudtd" <?php if(!empty($settings['dtdSwitch'])) { echo ' checked="checked" '; } ?>/> <label for="wpudtd"><Strong>Use Different Document Type Declaration?</Strong></label>
										<a class="wpuwhatis" href="#" title="The Document Type Declaration, or DTD, is provided at the top of all web pages to let the browser know what type of markup language is being used. phpBB3's prosilver uses an XHTML 1.0 Strict DTD by default. Most WordPress templates, however, use an XHTML 1 transitional  or XHTML 5 DTD. In most cases, this doesn't matter -- however, If you want to use WordPress' DTD on pages where WordPress is inside phpBB, then you can turn this option on. This should prevent browsers from going into quirks mode, and will ensure that even more WordPress templates display as designed.">What is this?</a>
									</p>
								</div>
						</div>
					</div>
					
					<div id="wputab-behav">
					
						<h3>Use phpBB Word Censor?</h3>
						<p>Turn this option on if you want WordPress posts to be passed through the phpBB word censor.</p>
						<input type="checkbox" id="wpucensor" name="wpucensor" <?php if(!empty($settings['phpbbCensor'])) { echo ' checked="checked" '; } ?>/><label for="wpucensor">Enable word censoring in WordPress</label>
						
						<h3>Use phpBB smilies?</h3>
						<p>Turn this option on if you want to use phpBB smilies in WordPress comments and posts.</p>
						<input type="checkbox" id="wpusmilies" name="wpusmilies" <?php if(!empty($settings['phpbbSmilies'])) { echo ' checked="checked" '; } ?>/><label for="wpusmilies">Use phpBB smilies in WordPress</label>	
						
						<h3>Make Blogs Private?</h3>
						<p>If you turn this on, users will have to be logged in to VIEW blogs. This is not recommended for most set-ups, as WordPress will lose search engine visibility.</p>
						<input type="checkbox" id="wpuprivate" name="wpuprivate" <?php if(!empty($settings['mustLogin'])) { echo ' checked="checked" '; } ?> /><label for="wpuprivate">Make blogs private</label>							
						
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
			var phpbbPath = '<?php echo (isset($settings['phpbb_path'])) ? $settings['phpbb_path'] : ''; ?>';		
			var treeScript =  '<?php echo $wpuUrl . 'js/filetree.php'; ?>';
			<?php 
					$cssmVal = 0;
					if(!empty($settings['cssMagic'])){
						$cssmVal++;
					}
					if(!empty($settings['templateVoodoo'])){
						$cssmVal++;
					}
			?>
			var cssmVal = '<?php echo $cssmVal; ?>';

			jQuery(document).ready(function($) { 
				
				setupSettingsPage();
				<?php if(isset($settings['phpbb_path'])) { ?> 
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
	global $wpuUrl, $wpuPath, $wpSettings, $wpdb;

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
	if(!file_exists($wpuPath))  {
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
	
	$data = array_merge((array)$wpSettings, $data);

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
			$data['xposting'] = isset($_POST['wpuxpost']) ? 1 : 0;
			
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
				$data['xpostforce'] =( isset($_POST['wpuxpostforce'])) ? (int) $_POST['wpuxpostforce'] : 0;
			} else {
				//cross-posting disabled, set to default
				$data = array_merge($data, array(
					'xposttype' 					=> 'excerpt',
					'wpuxpostcomments'	=> 0,
					'xpostforce' 				=> 0
				));
			}
		} else {
			// logins not integrated, set to default
			$data = array_merge($data, array(
				'xposting' 					=> 0,
				'xposttype' 					=> 'excerpt',
				'wpuxpostcomments'	=> 0,
				'xpostforce' 				=> 0
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
			'mustLogin' 		=> (isset($_POST['wpuprivate'])) ? 1 : 0
		));
		
	}

	$data = array_merge($data, array(
		'wpUri' => add_trailing_slash(get_option('home')),
		'wpPath' => ABSPATH,
		'wpPluginPath' => ABSPATH.'wp-content/plugins/' . plugin_basename('wp-united') . '/',
		'wpPluginUrl' => $wpuUrl,
		'enabled' => 'enabled',
		'status' => 2
	));


	update_option('wpu-settings', $data);

}


/**
 * Transmit settings to phpBB
 */
function wpu_transmit_settings() {
	global $phpbbForum, $phpbb_root_path, $phpEx, $wpuPath, $wpSettings;
	
	//if WPU was disabled, we need to initialise phpBB first
	// phpbbForum is already inited, however -- we just need to load
	if ( !defined('IN_PHPBB') ) {
		$phpbb_root_path = $wpSettings['phpbb_path'];
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		define('WPU_PHPBB_IS_EMBEDDED', TRUE);
		$phpbbForum->load($phpbb_root_path);
		wpu_set_status();
	}
	
	$settings = get_option('wpu-settings');
	if($phpbbForum->synchronise_settings($settings)) {
			die('OK');
	} else die('NO');
	
}

/**
 * Retrieve stored WP-United settings or set defaults
 */
function wpu_get_settings() {
	global $wpSettings;
	$settings = (array)get_option('wpu-settings');
	
	$defaults = array(
	'status' => 0,
	'wpUri' => '' ,
	'wpPath' => '', 
	'integrateLogin' => 0, 
	'showHdrFtr' => 'NONE',
	'wpSimpleHdr' => 1,
	'dtdSwitch' => 0,
	//'installLevel' => 0,
	'usersOwnBlogs' => 0,
	//'buttonsProfile' => 0,
	//'buttonsPost' => 0,
	//'allowStyleSwitch' => 0,
	//'useBlogHome' => 0,
	//'blogListHead' => $user->lang['WPWiz_BlogListHead_Default'],
	//'blogIntro' => $user->lang['WPWiz_blogIntro_Default'],
	'blogsPerPage' => 6,
	'blUseCSS' => 1,
	'phpbbCensor' => 1,
	//'wpuVersion' => $user->lang['WPU_Not_Installed'],
	'wpPageName' => 'page.php',
	'phpbbPadding' =>  '6-12-6-12',
	'mustLogin' => 0,
	//'upgradeRun' => 0,
	'xposting' => 0,
	'phpbbSmilies' => 0,
	'xpostautolink' => 0,
	'xpostforce' => -1,
	'xposttype' => 'EXCERPT',	
	'cssMagic' => 1,
	'templateVoodoo' => 1,
	//'pluginFixes' => 0,
	'useForumPage' => 1
	
);
	$settings = array_merge($defaults, $settings);
	
	if(isset($wpSettings)) {
		$settings = array_merge($settings, $wpSettings);
	}
	
	return $settings;
	
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


add_action('admin_menu', 'wpu_settings_menu');

?>