<?php

function wp_united_settings_menu() {
	global $phpbbForum;
	
	wp_united_settings_css();
	if ( function_exists('add_submenu_page') ) {
		
		wp_deregister_script( 'jquery' );
		wp_deregister_script( 'jquery-ui-core' );
		
		wp_enqueue_script('jquery', $phpbbForum->url . 'wp-united/js/jquery-wpu-min.js', array(), false, true);
		wp_enqueue_script('jquery-ui', $phpbbForum->url . 'wp-united/js/jqueryui-wpu-min.js', array(), false, true);

		add_submenu_page('plugins.php', "WP-United Settings", "WP-United Settings", 'manage_options','wp-united-settings', 'wp_united_settings');
	}
}

function wp_united_settings_css() {
	global $phpbbForum;
	wp_register_style('wpuSettingsStyles', $phpbbForum->url . 'wp-united/theme/settings.css');
	wp_enqueue_style('wpuSettingsStyles'); 
}

function wp_united_settings() { ?>
	<div class="wrap" id="wp-united-settings">
		<?php screen_icon('options-general'); ?>
		<h2> <?php echo 'WP-United Settings'; ?> </h2>
		
		<p>Introductory text here.</p></p>
		
		<div id="wputabs">
			<ul>
				<li><a href="#wputab-basic">Basic Settings</a></li>
				<li><a href="#wputab-user">User Integration</a></li>
				<li><a href="#wputab-theme">Theme Integration</a></li>
				<li><a href="#wputab-behav">Behaviour Integration</a></li>
				<li><a href="#wputab-blogs">User Blogs</a></li>
			</ul>
			
			<div id="wputab-basic">
				<h3>Path to phpBB3</h3>
				<p>Enter the filesystem path to your phBB installation &ndash; your phpBB config.php should live here</p>
				<input type="text" name="phpbbpath" />
				<button name="phbbautodet" value="Auto-detect">Auto-detect</button>
				<h3>Forum Page</h3>
				<p>Create a WordPress forum page? If you enable this option, WP-United will create a blank page in your WordPress installation, so that 'Forum' links appear in your blog. These links will automatically direct to your forum.</p>
				<input type="checkbox" id="wpuforumpage" /><label for="wpuforumpage">Enable Forum Page</label>		
			</div>
			
			<div id="wputab-user">
				<h3>Integrate logins?</h3>
				<p>If you turn this option on, phpBB will create a WordPress account the first time each phpBB user <strong>with appropriate permissions</strong> visits the blog. If this WordPress install will be non-interactive (e.g., a blog by a single person, a portal page, or an information library with commenting disabled), you may want to turn this option off, as readers may not need accounts. You can also map existing WordPress users to phpBB users, using the mapping tool that will appear after you turn on this option.</p>
				<p>You <strong>must set</strong> the privileges for each user using the WP-United permissions under the phpBB3 Users' and Groups' permissions settings.</p>
				<input type="checkbox" id="wpuloginint" /><label for="wpuloginint">Enable Login Integration?</label>		
				
				<div id="wpusettingsxpost" class="subsettings">
					<h4>Enable cross-posting?</h4>
					<p>If you enable this option, users will be able to elect to have their blog entry copied to a forum when writing a blog post. To set which forums the user can cross-post to, visit the phpBB forum permissions panel, and enable the &quot;can cross-post&quot; permission for the users/groups/forums combinations you need.</p>
					<input type="checkbox" id="wpuxpost" /><label for="wpuxpost">Enable Cross-Posting?</label>		
					
					
					<div id="wpusettingsxpostxtra" class="subsettings">
						<h4>Type of cross-posting?</h4>
						<p>Choose how the post should appear in phpBB. WP-United can post an excerpt, the full post, or give you an option to select when posting each post.</p>
						<input type="radio" name="rad_xpost_type" value="excerpt" id="wpuxpexc"  /><label for="wpuxpexc">Excerpt</label>
						<input type="radio" name="rad_xpost_type" value="fullpost" id="wpuxpfp"  /><label for="wpuxpfp">Full Post</label>
						<input type="radio" name="rad_xpost_type" value="askme" id="wpuxpask"  /><label for="wpuxpask">Ask Me</label>
						
						<h4>phpBB manages comments on crossed posts?</h4>
						<p>Choose this option to have WordPress comments replaced by forum replies for cross-posted blog posts. In addition, comments posted by integrated users via the WordPress comment form will be cross-posted as replies to the forum topic.</p>
						<input type="checkbox" id="wpuxpostcomments" /><label for="wpuxpostcomments">phpBB manages comments</label>		
						
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
				<input type="checkbox" id="wputplint" /><label for="wputplint">Enable Template Integration</label>
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
					
					<p><a href="#" onclick="return tplAdv();">Advanced Settings <span id="wutpladvshow">+</span><span id="wutpladvhide" style="display: none;">-</span></a></p>
					
					<div id="wpusettingstpladv" class="subsettings">
						<h4>Advanced Settings</h4>
						
						<p><strong>Use full page?</strong>
							<a href="#" onclick="alert('Do you want phpBB to simply appear inside your WordPress header and footer, or do you want it to show up in a fully featured WordPress page? Simple header and footer will work best for most WordPress themes – it is faster and less resource-intensive, but cannot display dynamic content on the forum page. However, if you want the WordPress sidebar to show up, or use other WordPress features on the integrated page, you could try \'full page\'. This option could be a little slower.'); return false;">What is this?</a>
						</p>
						<select id="wpuhdrftrspl" name="wpuhdrftrspl">
							<option value="0">-- Simple Header &amp; Footer (recommended) --</option>
							<option value="1">page.php</option>
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
						</div>
						
				
				</div>
			
			
			</div>
			
			<div id="wputab-behav">
				Behaviour Integration
			</div>
			
			<div id="wputab-blogs">
				User Blogs
			</div>
		</div>
		
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php  echo 'Submit' ?>" name="wpusettings-submit" />
	</p>
		
		<script type="text/javascript">
		// <![CDATA[
			jQuery(document).ready(function($) { 
				
				$('#wputabs').tabs();
				
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
		
		
	</div>
<?php }



add_action('admin_menu', 'wp_united_settings_menu');

?>