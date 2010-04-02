<?php

function wp_united_settings_menu() {
	wp_united_settings_css();
	if ( function_exists('add_submenu_page') ) {
		wp_enqueue_script( 'jquery-ui-core');
		wp_enqueue_script( 'jquery-ui-tabs');
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
				
				<div id="wpusetingsxpost" style="display: none; margin-left: 80px;">
					<hr />
					<h4>Enable cross-posting?</h4>
					<p>If you enable this option, users will be able to elect to have their blog entry copied to a forum when writing a blog post. To set which forums the user can cross-post to, visit the phpBB forum permissions panel, and enable the &quot;can cross-post&quot; permission for the users/groups/forums combinations you need.</p>
					<input type="checkbox" id="wpuxpost" /><label for="wpuxpost">Enable Cross-Posting?</label>		
					
					
					<div id="wpusetingsxpostxtra" style="display: none; margin-left: 80px;">
						<hr />
						<h4>Type of cross-posting?</h4>
						<p>Choose how the post should appear in phpBB. WP-United can post an excerpt, the full post, or give you an option to select when posting each post.</p>
						<input type="radio" id="xpostExcerpt" name="rad_xpost_type" value="excerpt" {S_WPXPOSTTYPE_EXCERPT} />{L_WP_EXCERPT} 
						<input type="radio" name="rad_xpost_type" value="fullpost" {S_WPXPOSTTYPE_FULLPOST} />{L_WP_FULLPOST} 
						<input type="radio" name="rad_xpost_type" value="askme" {S_WPXPOSTTYPE_ASKME} />{L_WP_ASKME}
						
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
				<p>You can choose to have WordPress appear inside your phpBB header and footer, or have phpBB appear inside your WordPress page, or neither. The options below will vary depending on which you choose.</p>
				<input type="radio" id="insideFWD" name="rad_Inside" value="FWD" onClick="rowState()" {S_WPINP}/>{L_WPINP} 
				<input type="radio" name="rad_Inside" id="insideREV" value="REV" onClick="rowState()" {S_PINWP}/>{L_PINWP} 
				<input type="radio" name="rad_Inside" value="NONE" onClick="rowState()" {S_PW_NONE} />{L_PW_NONE}
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
				if($('#wpuxpost').val()) $('#wpusetingsxpostxtra').show();
				if($('#wpuloginint').val()) $('#wpusetingsxpost').show();
				$('#wpuloginint').change(function() {
						$('#wpusetingsxpost').toggle("slow");
				});
				$('#wpuxpost').change(function() {
						$('#wpusetingsxpostxtra').toggle("slow");
				});				
			});
		
		// ]]>
		</script>
		
		
	</div>
<?php }



add_action('admin_menu', 'wp_united_settings_menu');

?>