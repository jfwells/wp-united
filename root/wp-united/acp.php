<?php

/**
 * Add menu option for WP-United Settings panel
 */
function wpu_settings_menu() {
	global $phpbbForum;
	
	wpu_settings_css();
	if ( function_exists('add_submenu_page') ) {
		
		wp_deregister_script( 'jquery' );
		wp_deregister_script( 'jquery-ui-core' );
		
		wp_enqueue_script('jquery', $phpbbForum->url . 'wp-united/js/jquery-wpu-min.js', array(), false, true);
		wp_enqueue_script('jquery-ui', $phpbbForum->url . 'wp-united/js/jqueryui-wpu-min.js', array(), false, true);
		wp_enqueue_script('filetree', $phpbbForum->url . 'wp-united/js/filetree.js', array(), false, true);

		add_submenu_page('plugins.php', "WP-United Settings", "WP-United Settings", 'manage_options','wp-united-settings', 'wpu_settings_page');
	}
}

/**
 * Enqueue page CSS associated with WP-United settings panel
 */
function wpu_settings_css() {
	global $phpbbForum;
	wp_register_style('wpuSettingsStyles', $phpbbForum->url . 'wp-united/theme/settings.css');
	wp_enqueue_style('wpuSettingsStyles'); 
}

/**
 * Decide whether to show the settings panel, or process inbound settings
 */
function wpu_settings_page() {  
	?>
		<div class="wrap" id="wp-united-settings">
		<?php screen_icon('options-general'); ?>
		<h2> <?php echo 'WP-United Settings'; ?> </h2>
	<?php
	
	if(isset($_POST['wpusettings-submit'])) {
		// process form
		if(!check_admin_referer( 'wp-united-settings')) {
			wpu_settings_error('Sorry, your submission failed the security check.');
		} else {
			wpu_process_settings();
		}
	} else {
			wpu_show_settings_menu();
	}
	
	?></div> <?php
	
}
	
/**
 * The main WP-United settings panel
 */	
function wpu_show_settings_menu() {	
	
	global $phpbbForum; 
	$settings = wpu_get_settings();
	?>
		
		<p>Introductory text here.</p></p>
		<form name="wpu-settings" id="wpusettings" action="plugins.php?page=wp-united-settings" method="post">
			
			<?php wp_nonce_field('wp-united-settings'); ?>
			
			<div id="wputabs">
				<ul>
					<li><a href="#wputab-basic">Basic Settings</a></li>
					<li><a href="#wputab-user">User Integration</a></li>
					<li><a href="#wputab-theme">Theme Integration</a></li>
					<li><a href="#wputab-behav">Behaviour Integration</a></li>
				<!--	<li><a href="#wputab-blogs">User Blogs</a></li>-->
				</ul>

				<div id="wputab-basic">
					<h3>Path to phpBB3</h3>
					<p>WP-United needs to know where your phpBB is installed on your server. <span id="txtselpath">Find and select your phpBB's config.php below.</span><span id="txtchangepath" style="display: none;">Click &quot;Change Location&quot; to change the stored location.</span></p>
					<div id="phpbbpath" style="height: 200px; border: 1px solid #ffffff; background-color: #bcbcbc; overflow-y: auto;">&nbsp;</div>
					<p>Path selected: <strong id="phpbbpathshow" style="color: red;"><?php echo "Not selected"; ?></strong> <a id="phpbbpathchooser" href="#" onclick="return wpuChangePath();" style="display: none;">Change Location &raquo;</a><a id="wpucancelchange" style="display: none;" href="#" onclick="return wpuCancelChange();">Cancel Change</a></p>
					<input id="wpupathfield" type="hidden" name="wpu-path" value="notset"></input>
					<h3>Forum Page</h3>
					<p>Create a WordPress forum page? If you enable this option, WP-United will create a blank page in your WordPress installation, so that 'Forum' links appear in your blog. These links will automatically direct to your forum.</p>
					<input type="checkbox" id="wpuforumpage" name="wpuforumpage" <? if(!empty($settings['useForumPage'])) { ?>checked="checked"<?php } ?> /><label for="wpuforumpage">Enable Forum Page</label>		
				</div>
				
				<div id="wputab-user">
					<h3>Integrate logins?</h3>
					<p>If you turn this option on, phpBB will create a WordPress account the first time each phpBB user <strong>with appropriate permissions</strong> visits the blog. If this WordPress install will be non-interactive (e.g., a blog by a single person, a portal page, or an information library with commenting disabled), you may want to turn this option off, as readers may not need accounts. You can also map existing WordPress users to phpBB users, using the mapping tool that will appear after you turn on this option.</p>
					<p>You <strong>must set</strong> the privileges for each user using the WP-United permissions under the phpBB3 Users' and Groups' permissions settings.</p>
					<input type="checkbox" id="wpuloginint" name="wpuloginint" <? if(!empty($settings['integrateLogin'])) { ?>checked="checked"<?php } ?> /><label for="wpuloginint">Enable Login Integration?</label>		
					
					<div id="wpusettingsxpost" class="subsettings">
						<h4>Enable cross-posting?</h4>
						<p>If you enable this option, users will be able to elect to have their blog entry copied to a forum when writing a blog post. To set which forums the user can cross-post to, visit the phpBB forum permissions panel, and enable the &quot;can cross-post&quot; permission for the users/groups/forums combinations you need.</p>
						<input type="checkbox" id="wpuxpost" name="wpuxpost" <? if(!empty($settings['xposting'])) { ?>checked="checked"<?php } ?> /><label for="wpuxpost">Enable Cross-Posting?</label>		
						
						
						<div id="wpusettingsxpostxtra" class="subsettings">
							<h4>Type of cross-posting?</h4>
							<p>Choose how the post should appear in phpBB. WP-United can post an excerpt, the full post, or give you an option to select when posting each post.</p>
							<input type="radio" name="rad_xpost_type" value="excerpt" id="wpuxpexc"  <? if($settings['xposttype'] == 'excerpt') { ?>checked="checked"<?php } ?>  /><label for="wpuxpexc">Excerpt</label>
							<input type="radio" name="rad_xpost_type" value="fullpost" id="wpuxpfp" <? if($settings['xposttype'] == 'fullpost') { ?>checked="checked"<?php } ?>  /><label for="wpuxpfp">Full Post</label>
							<input type="radio" name="rad_xpost_type" value="askme" id="wpuxpask" <? if($settings['xposttype'] == 'askme') { ?>checked="checked"<?php } ?>  /><label for="wpuxpask">Ask Me</label>
							
							<h4>phpBB manages comments on crossed posts?</h4>
							<p>Choose this option to have WordPress comments replaced by forum replies for cross-posted blog posts. In addition, comments posted by integrated users via the WordPress comment form will be cross-posted as replies to the forum topic.</p>
							<input type="checkbox" name="wpuxpostcomments" id="wpuxpostcomments" <? if(!empty($settings['xpostautolink'])) { ?>checked="checked"<?php } ?> /><label for="wpuxpostcomments">phpBB manages comments</label>		
							
							<h4>Force all blog posts to be cross-posted?</h4>
							<p>Setting this option will force all blog posts to be cross-posted to a specific forum. You can select the forum here. Note that users must have the &quot;can cross-post&quot; WP-United permission under phpBB Forum Permissions, or the cross-posting will not take place.</p>
							<select id="wpuxpostforce" name="wpuxpostforce">
								<option value="0">-- Disabled --</option>
							</select>
						</div>				
					</div>
				</div>		
				
				<div id="wputab-theme">
					<h3>Integrate templates?</h3>
					<p>WP-United can integrate your phpBB &amp; WordPress templates.</p>
					<input type="checkbox" id="wputplint" name="wputplint" /><label for="wputplint">Enable Template Integration</label>
					<div id="wpusettingstpl" class="subsettings">
						<h4>Integration Mode</h4>
						<p>Do you want WordPress to appear inside your phpBB template, or phpBB to appear inside your WordPress template?</p>
						<input type="radio" name="rad_tpl" value="fwd" id="wputplfwd"  /><label for="wputplfwd">WordPress inside phpBB</label>
						<input type="radio" name="rad_tpl" value="rev" id="wputplrev"  /><label for="wputplrev">phpBB inside WordPress</label>
					
						<h4>Automatic CSS Integration</h4>
						
						<p>WP-United can automatically fix CSS conflicts between your phpBB and WordPress templates. Set the slider to "maximum compatibility" to fix most problems. If you prefer to fix CSS conflicts by hand, or if the automatic changes cause problems, try reducing the level.</p>
						
						<div style="padding: 0 100px;">
							<p style="height: 11px;"><span style="float: left;">Off</span><span style="float: right;">Maximum Compatibility (Recommended)</span></p>
							<div id="wpucssmlvl"></div>
							<div style="background-color: #343434;" id="cssmdesc"><p><strong>Current Level: <span id="cssmlvltitle">xxx</span></strong><br /></p><p id="cssmlvldesc">xxx</p></div>
						</div>
						<input type="hidden" id="wpucssmlvlfield" name="wpucssmlevel" value="notset"></input>
						<p><a href="#" onclick="return tplAdv();">Advanced Settings <span id="wutpladvshow">+</span><span id="wutpladvhide" style="display: none;">-</span></a></p>
						
						<div id="wpusettingstpladv" class="subsettings">
							<h4>Advanced Settings</h4>
							
							<p><strong>Use full page?</strong>
								<a href="#" onclick="alert('Do you want phpBB to simply appear inside your WordPress header and footer, or do you want it to show up in a fully featured WordPress page? Simple header and footer will work best for most WordPress themes – it is faster and less resource-intensive, but cannot display dynamic content on the forum page. However, if you want the WordPress sidebar to show up, or use other WordPress features on the integrated page, you could try \'full page\'. This option could be a little slower.'); return false;">What is this?</a>
							</p>
							<select id="wpuhdrftrspl" name="wpuhdrftrspl">
								<option value="0">-- Simple Header &amp; Footer (recommended) --</option>
								<?php
									$files = scandir(TEMPLATEPATH);
									print_r($files); 
									if(sizeof($files)) {
										foreach($files as $file) {
											if(stristr($file, '.php')) {
												echo '<option value="' . $file . '">Full Page: ' . $file . '</option>';
											}
										}
									}
								?>
							</select>
							
							<p><strong>Padding around phpBB</strong>
								<a href="#" onclick="alert('phpBB is inserted on the WordPress page inside a DIV. Here you can set the padding of that DIV. This is useful because otherwise the phpBB content may not line up properly on the page. The defaults here are good for most WordPress templates. If you would prefer set this yourself, just leave these boxes blank (not \'0\'), and style the \'phpbbforum\' DIV in your stylesheet.'); return false;">What is this?</a>
							</p>
								<table>
									<tr>
										<td>
											<label for="wpupadtop">Top:</label><br />
										</td>
										<td>
											<input type="text" maxlength="3" style="width: 30px;" id="wpupadtop" name="wpupadtop" value="6" />px<br />
										</td>
									</tr>
									<tr>
										<td>
											<label for="wpupadright">Right:</label><br />
										</td>
										<td>
											<input type="text" maxlength="3" style="width: 30px;" id="wpupadright" name="wpupadright" value="12" />px<br />
										</td>
									</tr>
									<tr>
										<td>
											<label for="wpupadbtm">Bottom:</label><br />
										</td>
										<td>
											<input type="text" maxlength="3" style="width: 30px;" id="wpupadbtm" name="wpupadbtm" value="6" />px<br />
										</td>
									</tr>
									<tr>
										<td>
											<label for="wpupadleft">Left:</label><br />
										</td>
										<td>
											<input type="text" maxlength="3" style="width: 30px;" id="wpupadleft" name="wpupadleft" value="12" />px<br />
										</td>
									</tr>
									</table>
								<p><a href="#" onclick="return false;">Reset to defaults</a></p>
								
								<p>
									<input type="checkbox" id="wpudtd" name="wpudtd" /> <label for="wpudtd"><Strong>Use Different Document Type Declaration?</Strong></label>
									<a href="#" onclick="alert('The Document Type Declaration, or DTD, is provided at the top of all web pages to let the browser know what type of markup language is being used. phpBB3\'s prosilver uses an XHTML 1.0 Strict DTD by default. Most WordPress templates, however, use an XHTML 1 transitional DTD. In most cases, this doesn\'t matter -- however, If you want to use WordPress\' DTD on pages where WordPress is inside phpBB, then you can turn this option on. This should prevent browsers from going into quirks mode, and will ensure that even more WordPress templates display as designed.'); return false;">What is this?</a>
								</p>
							</div>
					</div>
				</div>
				
				<div id="wputab-behav">
				
					<h3>Use phpBB Word Censor?</h3>
					<p>Turn this option on if you want WordPress posts to be passed through the phpBB word censor.</p>
					<input type="checkbox" id="wpucensor" name="wpucensor" /><label for="wpucensor">Enable word censoring in WordPress</label>
					
					<h3>Use phpBB smilies?</h3>
					<p>Turn this option on if you want to use phpBB smilies in WordPress comments and posts.</p>
					<input type="checkbox" id="wpucensor" name="wpusmilies" /><label for="wpusmilies">Enable phpBB smilies in WordPress</label>	
					
					<h3>Make Blogs Private?</h3>
					<p>If you turn this on, users will have to be logged in to VIEW blogs. This is not recommended for most set-ups, as WordPress will lose search engine visibility.</p>
					<input type="checkbox" id="wpucensor" name="wpusmilies" /><label for="wpusmilies">Make blogs private</label>							
					
				</div>
				
				<!--<div id="wputab-blogs">
					User Blogs - options being revamped
				</div>-->
			</div>
			
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php  echo 'Submit' ?>" name="wpusettings-submit" />
		</p>
	</form>
		
		<script type="text/javascript">
		// <![CDATA[
			jQuery(document).ready(function($) { 

				$('#wputabs').tabs();				
				
			
				$('#phpbbpath').fileTree({ 
					root: '/',
					script: '<?php echo $phpbbForum->url . 'wp-united/js/filetree.php'; ?>',
					multiFolder: false,
					loadMessage: "Loading..."
				}, function(file) {
					var parts = file.split('/');
					if ((parts.length) > 1) {
						file = parts.pop();
					}
					if(file=='config.php') {
						var pth = parts.join('/') + '/';
						$("#phpbbpathshow").html(pth).css('color', 'green');
						$("#wpupathfield").val(pth);
						$('#phpbbpath').hide('slide');
						$('#txtchangepath').show();
						$('#txtselpath').hide();
						$('#wpucancelchange').hide();
						$('#phpbbpathchooser').show('slide');
					}
				});
				
			<?php if(isset($settings['phpbb_path'])) { ?>
					$('#phpbbpath').hide();
					$('#phpbbpathchooser').show();
					$("#phpbbpathshow").html('<?php echo $settings['phpbb_path']; ?>').css('color', 'green');
					$("#wpupathfield").val('<?php echo $settings['phpbb_path']; ?>');
					$('#txtchangepath').show();
					$('#txtselpath').hide();
					
			<?php } ?>
				
				
				if($('#wpuxpost')[0].value) $('#wpusettingsxpostxtra').show();
				if($('#wpuloginint')[0].value) $('#wpusettingsxpost').show();
				if($('#wputplint')[0].value) $('#wpusettingstpl').show();
				
				$('#wpuloginint').change(function() {
						$('#wpusettingsxpost').toggle("slide", "slow");
				});
				$('#wpuxpost').change(function() {
						$('#wpusettingsxpostxtra').toggle("slide", "slow");
				});
				$('#wputplint').change(function() {
						$('#wpusettingstpl').toggle("slide", "slow");
				});	
				
				setCSSMLevel(2);
				$("#wpucssmlvl").slider({
					value: 2,
					min: 0,
					max: 2,
					step: 1,
					change: function(event, ui) {
						setCSSMLevel(ui.value);
					}
				});
				
							
			});
			
			function wpuChangePath() {
				$('#phpbbpath').show('slide');
				$('#phpbbpathchooser').hide('slide');
				$('#txtchangepath').hide();
				$('#txtselpath').show();
				$('#wpucancelchange').show();
				return false;
			}
			
			function wpuCancelChange() {
				$('#phpbbpath').hide('slide');
				$('#phpbbpathchooser').show('slide');
				$('#txtchangepath').show();
				$('#txtselpath').hide();
				$('#wpucancelchange').hide();				
				return false;
			}
			
			function setCSSMLevel(level) {
				var lvl, desc;
				if(level == 0) {
					lvl = "Off";
					desc = "All automatic CSS integration is disabled";
				} else if(level == 1) {
					lvl = "Medium";
					desc = "CSS Magic is enabled, Template Voodoo is disabled: <ul><li>Styles are reset to stop outer styles applying to the inner part of the page.</li><li>Inner CSS is made more specific so it does affect the outer portion of the page.</li><li>Some HTML IDs and class names may be duplicated.</li></ul>";
				} else if(level == 2) {
					lvl = "Full";
					desc = "CSS Magic and Template Voodoo are enabled:<ul><li>Styles are reset to stop outer styles applying to the inner part of the page.</li><li>Inner CSS is made more specific so it does affect the outer portion of the page.</li><li>HTML IDs and class names that are duplicated in the inner and outer parts of the page are fixed.</li></ul>";							
				}
				$("#wpucssmlvlfield").val(level);
				$("#cssmlvltitle").html(lvl);
				$("#cssmlvldesc").html(desc);
				$("#cssmdesc").effect("highlight");
			}
			
			function tplAdv() {
				//var type = ($('#xxxx')[0].value) ? 'W' : 'P';
				$('#wpusettingstpladv').toggle('slide');
				$('#wutpladvshow').toggle()
				$('#wutpladvhide').toggle();
				return false;
			}
		
		// ]]>
		</script>
		

<?php }


/**
 * Process settings
 */
function wpu_process_settings() {
	
	$data = array();
	
	/**
	 * First process path to phpBB
	 */
	
	if(!isset($_POST['wpu-path'])) {
		wpu_settings_error('ERROR: You must specify a valid path for phpBB\'s config.php');
		return;
	}
	$wpuPath = (string)$_POST['wpu-path'];
	$wpuPath = str_replace('http:', '', $wpuPath);
	$wpuPath = add_trailing_slash($wpuPath);
	if(!file_exists($wpuPath))  {
		wpu_settings_error('ERROR: The path you selected for phpBB\'s config.php is not valid');
		return;
	}
	if(!file_exists($wpuPath . 'config.php'))  {
		wpu_settings_error('ERROR: phpBB\'s config.php could not be found at the location you chose');
		return;
	}
		
	$data['phpbb_path'] = $wpuPath;
	
	/**
	 * Process 'use forum page'
	 */
	$data['useForumPage'] = isset($_POST['wpuforumpage']) ? 1 : 0;
	
	
	
	/** 
	 * Process login integration settings
	 */
	$data['integrateLogin'] = isset($_POST['wpuloginint']) ? 1 : 0;
	
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
	
	 $tplint = isset($_POST['wputplint']) ? 1 : 0;
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
		
		if($simpleHeader != 0) {
			$simpleHeader = (string)$_POST['wpuhdrftrspl'];
			if(!empty($simpleHeader)) {
				if (file_exists(add_trailing_slash(TEMPLATEPATH) . $simpleHeader))  {
					$data['wpSimpleHdr'] = 0;
					$data['wpPageName'] = $simpleHeader;
				} else {
					wpu_settings_error('ERROR: You chose a full page template file that does not exist!');
					return;
				}
			} 
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
		'phpbbCensor' 	=> (isset($_POST['phpbbCensor'])) ? 1 : 0,
		'phpbbSmilies' 	=> (isset($_POST['phpbbSmilies'])) ? 1 : 0,
		'mustLogin' 		=> (isset($_POST['mustLogin'])) ? 1 : 0
	));
	
	update_option('wpu-settings', $data);
	
	
}

/**
 * Retrieve stored WP-United settings or set defaults
 */
function wpu_get_settings() {
	
	$settings = get_option('wpu-settings');
	print_r($settings);
	
	return $settings;
	
}



function wpu_settings_error($text) {
	
	echo '<p style="color: red;">' . $text . '</p>';
	wpu_show_settings_menu();
	
}


add_action('admin_menu', 'wpu_settings_menu');

?>