<?php
/** 
*
* WP-United WordPress template loader replacement
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
//
// Loads up the WP template. loose copy of WP's template-loader function, modified to work with functions exported to phpBB.
//
//

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

global $wpuNoHead, $wpSettings, $wp_version;
$wpuNoHead = false;
//
//	Need to branch to provide for WP 2.1 and maintain backwards compat.
//	------------------------------------------------------------------------------------------
//
//

if ( defined('WPU_REVERSE_INTEGRATION') ) {
	global $pfHead, $pfContent, $wpSettings, $phpEx, $wpuOutputPreStr, $wpuOutputPostStr;
	$padding = '';
	if ($wpSettings['phpbbPadding'] != 'NOT_SET') {
		$pad = explode('-', $wpSettings['phpbbPadding']);
		$padding = 'padding: ' . (int)$pad[0] . 'px ' .(int)$pad[1] . 'px ' .(int)$pad[2] . 'px ' .(int)$pad[3] . 'px;';
	}

	if(defined('USE_CSS_MAGIC') && USE_CSS_MAGIC) {
		$wpuOutputPreStr = '<div id="wpucssmagic" style="' . $padding . 'margin: 0; background-color: ' . CSS_MAGIC_BGCOLOUR . '; font-size: ' . CSS_MAGIC_FONTSIZE .';"><div class="wpucssmagic"><div class="' . $bodyClass . '" ' . $bodyDetails . '>';
		$wpuOutputPostStr = '</div></div></div>';
		
		if(defined('USE_TEMPLATE_VOODOO') && USE_TEMPLATE_VOODOO) {
			/*  Here we detect all classes and IDs in the phpBB document, and store 
			   their names and occurrences. Later, we compare and rename them if they also exist in WordPress,
			   and then store that info so that the stylesheet fixer part of template voodoo can modify the 
			   appropriate CSS  */
			include("wpu-template-voodoo.php");
			$tVoodoo = Template_Voodoo::getInstance();
			$tVoodoo->loadTemplate($pfContent);
		}
	} else {
		$wpuOutputPreStr = '<div style="'. $padding .' margin: 0px;" class="' . $bodyClass . '" ' . $bodyDetails . '>';
		$wpuOutputPostStr = '</div>';
	}
	$copy = "\n\n<!--\n phpBB <-> WordPress integration by John Wells, (c) 2006-2009 www.wp-united.com \n-->\n\n";
	
	if ( !empty($wpSettings['wpSimpleHdr']) ) {
		//
		//	Simple header and footer
		//
		if ( !defined('WPU_USE_CACHE') ) {
			//
			// Need to rebuld the cache
			//
			//Uncomment the below line to see if your cache is working or being rebuilt every time
			//echo "DEBUG: Cache Build";
			$doCache = FALSE;
			$theme = array_pop(explode('/', TEMPLATEPATH)); 
			if ( (defined('WPU_CACHE_ENABLED')) && (WPU_CACHE_ENABLED) ) {
				$doCache = TRUE;
				$fnTemp = $phpbb_root_path . 'wp-united/cache/temp_' . floor(round(0, 9999)) . 'cache';
				$fnDest = $phpbb_root_path . "wp-united/cache/$theme.wpucache";
				$hTempFile = @fopen($fnTemp, 'w+');
			}
			
			//prevent WP 404 error
			query_posts('showposts=1');
			
			ob_start();
			get_header();
			$hdrContent = ob_get_contents();
			ob_end_clean();
			
			if(defined('USE_TEMPLATE_VOODOO') && USE_TEMPLATE_VOODOO) {
				$tVoodoo->checkTemplate($hdrContent);
			}
			
			if ( !empty($wpSettings['fixHeader']) && !DISABLE_HEADER_FIX ) {
				if ( ($pHeadRemSuccess) && (!empty($srchBox)) ) {
					// User can add tag to their template if they like
					$hdrContent = preg_replace('/<!--PHPBB_SEARCH-->/', "<div style=\"padding:0 20px 0 0\" >$srchBox</div></div></div><hr />", $hdrContent, 1, $addedSrch);
					// If no tag we try ourselves.
					if(empty($addedSrch)) {
						$hdrContent = preg_replace('/<\/div>[\s]*?<\/div>[\s]*?<hr \/>/', "<div class=\"wpucssmagic\" style=\"padding:0 20px 0 0\" >$srchBox</div></div></div><hr />", $hdrContent, 1);
					}
				}
			}
			
			if ( $wpSettings['cssFirst'] == 'P' ) {
				$hdrContent = str_replace('</title>', '</title>' . "\n\n" . '[--PHPBB*HEAD--]', $hdrContent);
			}
			
			if ( $doCache ) {
				@fwrite($hTempFile, $hdrContent . '[--PHPBB*CONTENT--]');
			}
			
			$hdrContent= preg_replace("/<title>[^<]+?<\/title>/", "<title>{$GLOBALS['wpu_page_title']}</title>", $hdrContent);
			
			
			ob_start();
			get_footer();
			$ftrContent = ob_get_contents();
			ob_end_clean();
			
			$tvFileHash = false;
			if(defined('USE_TEMPLATE_VOODOO') && USE_TEMPLATE_VOODOO) {
				$tVoodoo->checkTemplate($ftrContent);
				$tvFileHash = $tVoodoo->storeResult($theme, $user->theme['theme_name']);
				$pfContent = $tVoodoo->fixTemplate($pfContent);
			}
			
			//Modify stylesheets to use CSS Magic
			if(defined('USE_CSS_MAGIC') && USE_CSS_MAGIC) {
				$pfHead = wpu_modify_stylesheet_links($pfHead, $tvFileHash);
			}
			
			
			echo str_replace('[--PHPBB*HEAD--]', $pfHead, $hdrContent);
			unset ($hdrContent, $pfHead);
			
			
			echo $wpuOutputPreStr . $pfContent . $wpuOutputPostStr; unset ($pfContent);
			echo $ftrContent;
			
			
			if ( $doCache ) {
				@fwrite($hTempFile, $ftrContent);
				@fclose($hTempFile);
				@copy($fnTemp, $fnDest);
				@unlink($fnTemp);
			} 
			unset($ftrContent);
			
		} else {
			//
			// Just pull the header and footer from the cache
			//
			global $wpu_cacheLoc;
			$page_content = @file_get_contents($wpu_cacheLoc);
			$page_content = str_replace('[--PHPBB*HEAD--]',$pfHead, $page_content);
			$retWpInc = str_replace('[--PHPBB*CONTENT--]', $wpuOutputPreStr . $pfContent . $wpuOutputPostStr, $page_content);
			unset($page_content, $pfContent);
		}
	} else {
		//
		//	Full WP page
		//
		query_posts('showposts=1');
		define('PHPBB_CONTENT_ONLY', TRUE);

		
		ob_start();
		
		
		$wpTemplateFile = TEMPLATEPATH . '/' . strip_tags($wpSettings['wpPageName']);
		if ( !file_exists($wpTemplateFile) ) {
			$wpTemplateFile = TEMPLATEPATH . "/page.php";
			// Fall back to index.php for Classic template
			if(!file_exists($wpTemplateFile)) {
				$wpTemplateFile = TEMPLATEPATH . "/index.php";
			}
		}
		include($wpTemplateFile);
		
		$wContent = ob_get_contents();
		ob_end_clean();
		
		$tvFileHash = false;
		if(defined('USE_TEMPLATE_VOODOO') && USE_TEMPLATE_VOODOO) {
			$tVoodoo->checkTemplate($wContent);
			$tvFileHash = $tVoodoo->storeResult($theme, $user->theme['theme_name']);
			$pfContent = $tVoodoo->fixTemplate($pfContent);
		}
		//Modify stylesheets to use CSS Magic
		if(defined('USE_CSS_MAGIC') && USE_CSS_MAGIC) {
			$pfHead = wpu_modify_stylesheet_links($pfHead, $tvFileHash);
		}		
		
		
		
		
		if ( ($wpSettings['cssFirst'] == 'P') ) {
			$wContent = str_replace('</title>', '</title>' . "\n\n" . $pfHead, $wContent);
		}
			
		if ( !empty($wpSettings['fixHeader']) && !DISABLE_HEADER_FIX ) {
			if ( ($pHeadRemSuccess) && (!empty($srchBox)) ) {
				// User can add tag to their template if they like
				$wContent = preg_replace('/<!--PHPBB_SEARCH-->/', "<div class=\"wpucssmagic\" style=\"padding:0 20px 0 0\" >$srchBox</div></div></div><hr />", $wContent, 1, $addedSrch);
				// If no tag we try ourselves.
				if(empty($addedSrch)) {
					$wContent = preg_replace('/<\/div>[\s]*?<\/div>[\s]*?<hr \/>/', "<div style=\"padding:0 20px 0 0\" >$srchBox</div></div></div><hr />", $wContent, 1);
				}
				
			}
		}
		$wContent= preg_replace("/<title>(.*)[^<>]<\/title>/", "<title>{$GLOBALS['wpu_page_title']}</title>", $wContent);
		$wContent = str_replace('[--PHPBB*HEAD--]',$pfHead, $wContent);
			
		echo $wContent; unset($wContent, $pfContent);
	}

} else {

if ( ((float) $wp_version) >= 2.1 ) {

	//WP 2.1 && 2.2 BRANCH
	if ( defined('WP_USE_THEMES') && constant('WP_USE_THEMES') ) {
		do_action('template_redirect');
		if ( is_robots() ) {
			$wpuNoHead = true;
			do_action('do_robots');
		} else if ( is_feed() ) {
			$wpuNoHead = true;
			do_feed();
		} else if ( is_trackback() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-trackback.php');
		} else if ( is_404() && $wp_template = get_404_template() ) {
			include($wp_template);
		} else if ( is_search() && $wp_template = get_search_template() ) {
			include($wp_template);
		} else if ( is_home() && $wp_template = get_home_template() ) {
			include($wp_template); 
		} else if ( is_attachment() && $wp_template = get_attachment_template() ) {
			include($wp_template);
		} else if ( is_single() && $wp_template = get_single_template() ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include($wp_template);
		} else if ( is_page() && $wp_template = get_page_template() ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include($wp_template);
		} else if ( is_category() && $wp_template = get_category_template()) {
			include($wp_template);
		} else if ( is_author() && (!empty($wpSettings['usersOwnBlogs'])) && ($wp_template = get_home_template()) ) {
			include($wp_template);
		} else if ( is_author() && $wp_template = get_author_template() ) {	
			include($wp_template);		
		} else if ( is_date() && $wp_template = get_date_template() ) {
			include($wp_template);
		} else if ( is_archive() && $wp_template = get_archive_template() ) {
			include($wp_template);
		} else if ( is_comments_popup() && $wp_template = get_comments_popup_template() ) {
			include($wp_template);
		} else if ( is_paged() && $wp_template = get_paged_template() ) {
			include($wp_template);
		} else if ( file_exists(TEMPLATEPATH . "/index.php") ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include(TEMPLATEPATH . "/index.php");
		}
	} else {
		// Process feeds and trackbacks even if not using themes.
		if ( is_robots() ) {
			$wpuNoHead = true;
			do_action('do_robots');
		} else if ( is_feed() ) {
			$wpuNoHead = true;
			do_feed();
		} else if ( is_trackback() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-trackback.php');
		}
	}

	
} else {

	// WP 2.0x BRANCH!
	if ( defined('WP_USE_THEMES') && constant('WP_USE_THEMES') ) {
		do_action('template_redirect');
		if ( is_feed() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-feed.php');
		} else if ( is_trackback() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-trackback.php');
		} else if ( is_404() && $wp_template = get_404_template() ) {
			include($wp_template);
		} else if ( is_search() && $wp_template = get_search_template() ) {
			include($wp_template);
		} else if ( is_home() && $wp_template = get_home_template() ) {
			include($wp_template);
		} else if ( is_attachment() && $wp_template = get_attachment_template() ) {
			include($wp_template);
		} else if ( is_single() && $wp_template = get_single_template() ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include($wp_template);
		} else if ( is_page() && $wp_template = get_page_template() ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include($wp_template);
		} else if ( is_category() && $wp_template = get_category_template()) {
			include($wp_template);
		} else if ( is_author() && (!empty($wpSettings['usersOwnBlogs'])) && ($wp_template = get_home_template()) ) {
			include($wp_template);
		} else if ( is_author() && $wp_template = get_author_template() ) {	
			include($wp_template);
		} else if ( is_date() && $wp_template = get_date_template() ) {
			include($wp_template);
		} else if ( is_archive() && $wp_template = get_archive_template() ) {
			include($wp_template);
		} else if ( is_comments_popup() && $wp_template = get_comments_popup_template() ) {
			include($wp_template);
		} else if ( is_paged() && $wp_template = get_paged_template() ) {
			include($wp_template);
		} else if ( file_exists(TEMPLATEPATH . "/index.php") ) {
			if ( is_attachment() )
				add_filter('the_content', 'prepend_attachment');
			include(TEMPLATEPATH . "/index.php");
		}
	
	} else {
		// Process feeds and trackbacks even if not using themes.
		if ( is_feed() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-feed.php');
		} else if ( is_trackback() ) {
			$wpuNoHead = true;
			include(ABSPATH . '/wp-trackback.php');
		}
	}
}



}


 
?>
