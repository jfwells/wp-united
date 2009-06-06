<?php
/** 
*
* WP-United WordPress template loader replacement
*
* @package WP-United
* @version $Id: wp-united.php,v0.9.5[phpBB2]/v 0.7.0[phpBB3] 2009/05/18 John Wells (Jhong) Exp $
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
	global $pfHead, $pfContent, $wpSettings, $phpEx, $phpbb_preString, $phpbb_postString;
	$padding = '';
	if ($wpSettings['phpbbPadding'] != 'NOT_SET') {
		$pad = explode('-', $wpSettings['phpbbPadding']);
		$padding = 'padding: ' . (int)$pad[0] . 'px ' .(int)$pad[1] . 'px ' .(int)$pad[2] . 'px ' .(int)$pad[3] . 'px;';
	}

	if(defined('USE_CSS_MAGIC') && USE_CSS_MAGIC) {
		$phpbb_preString = '<div id="wpucssmagic" style="' . $padding . 'margin: 0; background-color: ' . CSS_MAGIC_BGCOLOUR . '; font-size: ' . CSS_MAGIC_FONTSIZE .';"><div class="wpucssmagic">';
		$phpbb_postString = '</div></div>';
		
		if(defined('USE_TEMPLATE_VOODOO') && USE_TEMPLATE_VOODOO) {
			$phpbb_postString .= '<script type="text/javascript">
				// <[CDATA[
				var clses, cls;
				var vdDiv = document.getElementById("wpucssmagic");
				var vdDivChildren = vdDiv.getElementsByTagName("*");
				for(var i=0;i<vdDivChildren.length;i++) { 
					if(vdDivChildren[i].id != "") {
						vdDivChildren[i].id = "wpu" + vdDivChildren[i].id;
					
					}
					if((vdDivChildren[i].className != "") && (vdDivChildren[i].className != "wpucssmagic"))  {
						clses= vdDivChildren[i].className.split(" ");
						for(cls in clses) {
							clses[cls] = "wpu" + clses[cls]
						}
						vdDivChildren[i].className = clses.join(" ");
					}		
				}
				// ]]>
			</script>';
		}
	} else {
		$phpbb_preString = '<div style="'. $padding .' margin: 0px;">';
		$phpbb_postString = '</div>';
	}
	$copy = "\n\n<!--\n phpBB <-> WordPress integration by John Wells, (c) 2006-2007 www.wp-united.com \n-->\n\n";
	
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
			if ( (defined('WPU_CACHE_ENABLED')) && (WPU_CACHE_ENABLED) ) {
				$doCache = TRUE;
				$fnTemp = $phpbb_root_path . 'wp-united/cache/temp_' . floor(round(0, 9999)) . 'cache';
				$theme = array_pop(explode('/', TEMPLATEPATH)); 
				$fnDest = $phpbb_root_path . "wp-united/cache/$theme.wpucache";
				$hTempFile = @fopen($fnTemp, 'w+');
			}
			ob_start();
			get_header();
			$hdrContent = ob_get_contents();
			ob_end_clean();
			
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
			
			$hdrContent= preg_replace("/<title>(.*)[^<>]<\/title>/", "<title>{$GLOBALS['wpu_page_title']}</title>", $hdrContent);
			
			//Modify stylesheets to use CSS Magic
			if(defined('USE_CSS_MAGIC') && USE_CSS_MAGIC) {
				$pfHead = wpu_modify_stylesheet_links($pfHead);
			}
	 
			echo str_replace('[--PHPBB*HEAD--]', $pfHead, $hdrContent);
			unset ($hdrContent, $pfHead);
			echo $phpbb_preString . $pfContent . $phpbb_postString; unset ($pfContent);
			ob_start();
			get_footer();
			if ( $doCache ) {
				@fwrite($hTempFile, ob_get_contents());
				@fclose($hTempFile);
				@copy($fnTemp, $fnDest);
				@unlink($fnTemp);
			} 
			ob_flush();
		} else {
			//
			// Just pull the header and footer from the cache
			//
			global $wpu_cacheLoc;
			$page_content = @file_get_contents($wpu_cacheLoc);
			$page_content = str_replace('[--PHPBB*HEAD--]',$pfHead, $page_content);
			$retWpInc = str_replace('[--PHPBB*CONTENT--]', $phpbb_preString . $pfContent . $phpbb_postString, $page_content);
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

//
// Modify links in header to stylesheets to use CSS Magic instead
//
function wpu_modify_stylesheet_links($headerInfo) {

	preg_match_all('/<link rel=.*?\.css.*?\/>/', $headerInfo, $matches);

	if(is_array($matches[0])) {
		foreach($matches[0] as $match) {
			// extract css location
			$cssLoc = '';
			$els = explode('"', $match);
			foreach($els as $el) {
				if(stristr($el, ".css") !== false) {
					$cssLoc = $el;
				}
			}
			if($cssLoc) {
				// TODO: translate the URL to a local path :-)
				if( file_exists($cssLoc) && (stristr($cssLoc, "http:") === false) ) {
					$newLoc = "wp-united/wpu-style-fixer.php?style=" . urlencode(htmlentities($cssLoc));
					$headerInfo = str_replace($cssLoc, $newLoc, $headerInfo);
				}
			}
		}
	}
	return $headerInfo;
}
 
?>
