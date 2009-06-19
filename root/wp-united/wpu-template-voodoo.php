<?php
/** 
*
* WP-United "Template Voodoo" template integrator
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
// This class is designed to modify an integrated template to remove duplicate classes and IDs,
// replace them with something unique, and then store the resulting information so that the relevant styleshet
// can be modified
//
// It works together with CSS Magic to ensure fully-working template integrations. CSS Magic increases the
// specicifity of style selectors, while template voodoo irons out remaining conflicts.
//
// John Wells, 2009

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}


class Template_Voodoo {
	var $idNames;
	var $classNames;
	var $idDupes;
	var $classsDupes;
	var $loaded;
	
	//
	//	GET INSTANCE
	//	----------------------
	//	Makes class a Singleton.
	//	
	function getInstance () {
		static $instance;
		if (!isset($instance)) {
			$instance = new Template_Voodoo();
        } 
        return $instance;
    }
	
	// 
	// 	CLASS CONSTRUCTOR
	//	------------------------------
	//	
	//	
	function Template_Voodoo() {
		$this->clear();
	}
	
	function clear() {
		$this->idNames = array();
		$this->classNames = array();
		$this->idDupes = array();
		$this->classDupes = array();
		$this->loaded = false;
	}
	
	/* Loads the template to be integrated, and stores a list of all the IDs and classes, and where they
	  occur. */
	function loadTemplate($content) {
		// Detect all IDs
		preg_match_all("/(id\s*=\s*\"[^\s]*\")|(id\s*=\s*\'[^\s]*\')/i", $content, $vdIDs);
		foreach ($vdIDs[0] as $vdID) {
			$idName = strtolower(trim(str_ireplace(array("id", "=", "'", '"', " "), "", $vdID)));
			$this->idNames[$idName] = trim($vdID);
		}
		// Detect all classes
		preg_match_all("/(class\s*=\s*\"[^\"]*\")|(class\s*=\s*\'[^\']*\')/i", $content, $vdClasses);
		foreach ($vdClasses[0] as $vdClass) {
			$classNames = strtolower(trim(str_ireplace(array("class", "=", "'", '"'), "", $vdClass)));
			$className = explode(" ", $classNames);
			foreach($className as $cN) {
				if(!empty($cN)) {
					// store the occurrence code for each occurrence
					if(array_key_exists($cN, $this->classNames)) {
						$this->classNames[$cN] = array_merge($this->classNames[$cN], array(trim($vdClass)));
						$this->classNames[$cN] = array_unique($this->classNames[$cN]);
					} else {
						$this->classNames[$cN] = array(trim($vdClass));
					}
				}
			}	
		}
		$this->loaded = true;
	}
	/* Checks a portion of the containing template to see if it already defines some of our classes / IDs
	This can be called multiple times -- it continues to fill idDupes and classDupes each time */
	function checkTemplate($content) {
		if($this->loaded) {
			// Detect all IDs
			preg_match_all("/(id\s*=\s*\"\s*[^\s]+\s*\")|(id\s*=\s*\'\s*[^\s]+\s*\')/i", $content, $vdIDs);
			$dupes = array();
			foreach ($vdIDs[0] as $vdID) {
				$idName = strtolower(trim(str_ireplace(array("id", "=", "'", '"', " "), "", $vdID)));
				foreach($this->idNames as $key => $val) {
					if($idName == $key) {
						if(!in_array($idName, $this->idDupes)) {
							$this->idDupes[] = $idName;
						}
					}
				}
			}
			// Detect all classes
			preg_match_all("/(class\s*=\s*\"\s*[^\"]+\s*\")|(class\s*=\s*\'\s*[^\']+\s*\')/i", $content, $vdClasses);
			foreach ($vdClasses[0] as $vdClass) {
				$classNames = strtolower(trim(str_ireplace(array("class", "=", "'", '"'), "", $vdClass)));
				$className = explode(" ", $classNames);
				foreach($className as $cN) {
					if(!empty($cN)) {
						$cName = trim($cN);
						foreach($this->classNames as $key => $val) {
							if($cN == $key) {
								if(!in_array($cN, $this->classDupes)) {
									$this->classDupes[] = $cN;
								}
							}
						}					
					}
				}	
			}
			return true;
		}
		return false;		
	}
	// Stores only duplicated IDs / Classes
	function storeResult($wpTemplate, $phpbbTemplate) {
		if($this->loaded) {
			global $phpbb_root_path, $wpuAbs;
			$vdData = serialize(array($this->idDupes, $this->classDupes));
			$fnTemp = $phpbb_root_path . 'wp-united/cache/temp_tvoodoo' . floor(round(0, 9999)) . 'cache';
			// Get template & theme name here & check age
			$fileHash = base64_encode("{$wpuAbs->wpu_ver}-$wpTemplate-$phpbbTemplate");
			$fnDest = $phpbb_root_path . "wp-united/cache/tvoodoo-" . $fileHash . ".tv";
			$hTempFile = fopen($fnTemp, 'w+');		
			@fwrite($hTempFile, $vdData);
			@fclose($hTempFile);
			@copy($fnTemp, $fnDest);
			@unlink($fnTemp);			
			return (file_exists($fnDest)) ? $fileHash : false;
		}
		return false;
	}
	// Stores all IDs / Classes
	function storeAll($wpTemplate, $phpbbTemplate) {
		if($this->loaded) {
			global $phpbb_root_path, $wpuAbs;
			$vdData = serialize(array(array_keys($this->idNames), array_keys($this->classNames)));
			$fnTemp = $phpbb_root_path . 'wp-united/cache/temp_tvoodoo' . floor(round(0, 9999)) . 'cache';
			// Get template & theme name here & check age
			$fileHash = base64_encode("{$wpuAbs->wpu_ver}-$wpTemplate-$phpbbTemplate-all");
			$fnDest = $phpbb_root_path . "wp-united/cache/tvoodoo-" . $fileHash . ".tv";
			$hTempFile = fopen($fnTemp, 'w+');		
			@fwrite($hTempFile, $vdData);
			@fclose($hTempFile);
			@copy($fnTemp, $fnDest);
			@unlink($fnTemp);			
			return (file_exists($fnDest)) ? $fileHash : false;
		}
		return false;
	}	
	function getStoredResult() {
		if($this->loaded) {
			return true;
		}
		return false;	
	
	}
	
	function fixTemplate($content) {
		if($this->loaded) {
			if(sizeof($this->idDupes)) {
				foreach($this->idDupes as $idName) {
					//Have to use preg_replace, as we're not sure of the pattern for IDs
					$content = preg_replace("/(id\s*=\s*\"\s*{$idName}\s*\")|(id\s*=\s*\'\s*{$idName}\s*\')/i", "id=\"wpu{$idName}\"", $content);
				}
			}
			if(sizeof($this->classDupes)) {
				foreach($this->classDupes as $className) {
					foreach($this->classNames[$className] as $docString) {
						$replStr = str_replace($className, "wpu{$className}", $docString);
						$content = str_replace($docString, $replStr, $content);
					}
				}
			}			
		}
		return $content;		
	}
	
	
}
//str_ireplace for PHP4
if(!function_exists('str_ireplace')) {
	function str_ireplace($Needle, $Replacement, $Haystack){
	   $i = 0;
	   while($Pos = strpos(strtolower($Haystack), $Needle, $i)){
	       $Haystack = substr($Haystack, 0, $Pos).$Replacement.substr($Haystack, $Pos+strlen($Needle));
	       $i = $Pos+strlen($Replacement);
	   }
	   return $Haystack;
	}
}

