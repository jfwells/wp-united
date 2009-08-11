<?php
/** 
*
* WP-United "CSS Magic" template integrator
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
//	------------------------------
// This experimental library attempts to magically fuse templates on the page
// by modifying CSS specicifity on the fly
//
// Inspired by cssParser class. you are welcome to use this class in your own projects, provided this licence is kept intact.
// John Wells, 2009
// --------------------------------
//
/* USAGE NOTES
	
	// instantiate CSS Magic
	$cssMagic = CSS_Magic::getInstance();
	
	// to read a css file. 
	$success = $cssMagic->parseFile('path_to_file');
	
	// or read a css string
	$cssMagic->clear(); // you can read multiple strings and files. They are combined together. To start anew, use this.
	$success = $cssMagic->parseString($cssString);
	
	// $success will return 1 or true on success, or 0 or false on failure.
	
	//Now, make all the CSS we have read in apply only to children of a particular DIV with ID = $id
	$cssMagic->makeSpecificById($id);
	
	// Or, we could use $cssMagic->makeSpecificByClass($class) 
	// Or, both :-) $cssMagic->makeSpecificByIdThenClass($classAndId) 
	
	// Now get the modified CSS. The output is fairly nicely compressed too.
	$fixedCSS = $cssMagic->getCSS();
	
	// alternatively, send the output straight to the browser as a CSS file
	$cssMagic->sendCSS();
	
	// When you're finished,
	$cssMagic->clear();
	
	// Note: CSS Magic doesn't try to validate the CSS coming in. If the inbound CSS in invalid or garbage, 
	// you'll get garbage coming out -- perhaps even worse than before.
	
	// (c) John Wells, 2009
	
*/

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}


class CSS_Magic {
	var $css;
	var $filename;
	var $nestedItems;
	
	//
	//	GET INSTANCE
	//	----------------------
	//	If you want to use this class as a sngleton, invoke via CSS_Magic::getInstance();
	//	
	function getInstance () {
		static $instance;
		if (!isset($instance)) {
			$instance = new CSS_Magic();
        } 
        return $instance;
    }
	
	// 
	// 	CLASS CONSTRUCTOR
	//	------------------------------
	//	
	//	
	function CSS_Magic() {
		$this->clear();
		$this->filename = '';
		$this->nestedItems = array();
	}
	
	function clear() {
		$this->css = array();
		$this->nestedItems = array();
	}

	// 
	// 	PARSE STRING
	//	------------------------------
	//	Parses raw CSS, storing the keys and CSS code. We don't separate or process the keys, we don't need to.
	//	
	function parseString($str) {
		$Instr = $str;
		// Remove comments
		$str = preg_replace("/\/\*(.*)?\*\//Usi", "", $str);
		$str = str_replace("\t", "", $str);
		$str = str_replace("}\\", '[TANTEK]', $str);
		// for now we just leave all nested stylesheets untouched
		preg_match_all('/\@[^\{]*\{[^\{^\}]*(\{[^\{^\}]*\}[^\{^\}]*)*?\}/', $str, $nested);
		$nestIndex = sizeof($this->nestedItems);
		foreach($nested[0] as $nest) {
			if(!empty($nest)) {
				$this->nestedItems[$nestIndex] = $nest;
				$str = str_replace($nest, '[WPU_NESTED] {' . $nestIndex . '}', $str);
				$nestIndex++;
			}
		}
		$parts = explode("}",$str);
		if(count($parts) > 0) {
			foreach($parts as $part) {
				list($keys,$cssCode) = explode("{",$part);
				// store full selector
				if(strlen($keys) > 0) {
					$keys = str_replace("\n", "", $keys);
					$keys = str_replace("\r", "", $keys);
					$keys = str_replace("\\", "", $keys);
					$this->addSelector($keys, trim($cssCode));
				}
			}
		}
		return (count($this->css) > 0);
	}
	// 
	// 	PARSE FILE
	//	------------------------------
	//	Opens and parses the CSS file.
	//	  
	function parseFile($filename, $clear = false) {
		if ($clear) $this->clear();
		$this->filename = $filename;
		if(file_exists($filename)) {
			return $this->parseString(file_get_contents($filename), $clear);
		} else {
			return false;
		}
	}
	// 
	// 	ADD SELECTOR
	//	------------------------------
	//	Stores a full CSS selector in our class. 
	
	function addSelector($keys, $cssCode) {
		$keys = trim($keys);
		$cssCode = trim($cssCode);
		
		while(array_key_exists($keys, $this->css)) {
			$keys = "__ " . $keys;
		}
		
			$this->css[$keys] = $cssCode;

	}
	
	function makeSpecificById($id, $removeBody = false) {
		$this->_makeSpecific("#{$id}", $removeBody);
	}
	function makeSpecificByClass($class, $removeBody = false) {
		$this->_makeSpecific(".{$class}", $removeBody);
	}
	function makeSpecificByIdThenClass($classAndId, $removeBody = false) {
		$this->_makeSpecific("#{$classAndId} .{$classAndId}", $removeBody);
	}
	
	function renameIds($prefix, $IDs) {
		$fixed = array();
		$searchStrings = array();
		$replStrings = array();
		if(sizeof($IDs)) {
			foreach($IDs as $ID) {
				foreach(array(' ', '{', '.', '#', ':') as $suffix) {
					$searchStrings[] = "#{$ID}{$suffix}";
					$replStrings[] = "#{$prefix}{$ID}{$suffix}";
				}
			}
			foreach($this->css as $keyString => $cssCode) {
				$fixed[str_replace($searchStrings, $replStrings, $keyString)] = $cssCode;
			}
			$this->css = $fixed;
		}
		unset($fixed);
	}
	
	function renameClasses($prefix, $classes) {
		$fixed = array();
		$searchStrings = array();
		$replStrings = array();
		if(sizeof($classes)) {
			foreach($classes as $class) {
				foreach(array(' ', '{', '.', '#', ':') as $suffix) {
					$searchStrings[] = '#' . $class . $suffix;
					$replStrings[] = "#{$prefix}{$class}";
				}
			}
			foreach($this->css as $keyString => $cssCode) {
				$fixed[str_replace($searchStrings, $replStrings, $keyString)] = $cssCode;
			}
			$this->css = $fixed;
		}		
		unset($fixed);
	}	
	
	
	// 
	// 	MAKE SPECIFIC
	//	------------------------------
	//	Makes all stored CSS specific to a particular parent ID or class
	//
	//	@param $removeBody: set to true to remove 
	function _makeSpecific($prefix, $removeBody = false) {
		$fixed = array();
		// things that could be delimiting a "body" selector at the beginning of our string.
		$seps = array(' ', '>', '<', '.', '#', ':', '+', '*', '[', ']', '?');
		$index = 0;
		foreach($this->css as $keyString => $cssCode) {
			$keyString = str_replace('__ ', '', $keyString);
			$index++;
			$fixedKeys = array();
			if($keyString != '[WPU_NESTED]') {
				$keys = explode(',', $keyString);
				foreach($keys as $key) {
					$fixedKey = trim($key);
					$foundBody = false;
					// remove references to 'body'
					//$keyElements = preg_split('/[\s<>\.#\:\+\*\[\]\?]/');
					foreach($seps as $sep) {
						$keyElements = explode($sep, $fixedKey);
						$bodyPos = array_search("body", $keyElements);
						if($bodyPos !== false) {
							$keyElements[$bodyPos] = $prefix;
							if(!$removeBody) {
								if(sizeof($keyElements) > 1) { 
									$fixedKey = implode($sep, $keyElements);
								} else {
									$fixedKey = $keyElements[$bodyPos]; 
								}
							
							} 
							$foundBody = true;
						}
					}
					// add prefix selector before each selector
					if(!$foundBody) {
						if($fixedKey[0] != "@") {
							if((strpos($fixedKey, '* html') !== false)) {
								$fixedKey = str_replace('* html', '* html ' . $prefix . ' ', $fixedKey);
							} else {
								$fixedKey = "{$prefix} " . $fixedKey;
							}
							
						}
					
					}
					if(!empty($fixedKey)) {
						$fixedKeys[] = $fixedKey;
					}
				}
				
			} else { // nested
				$fixedKeys = array('[WPU_NESTED]');
			}
			
			// recreate the fixed key
			if(sizeof($fixedKeys)) {
				$fixedKeyString = implode(', ', $fixedKeys);
			
				while(array_key_exists($fixedKeyString, $fixed)) {
					$fixedKeyString = "__ " . $fixedKeyString;
				}	
				$fixed[$fixedKeyString] = $cssCode;

			}
		}

		// done
		$this->css = $fixed;
		unset($fixed);

	}
	
	//
	// REMOVE COMMON ELEMENTS FROM KEYS
	// ---------------------------------
	// Removes common elements from CSS selectors
	// For example, this can be used to undo CSS magic additions
	function removeCommonKeyEl($txt) {
		$newCSS = array();
		foreach($this->css as $keyString => $cssCode) {
			$newKey = trim(str_replace($txt, '', $keyString));
			if(!empty($newKey)) {
				$newCSS[$newKey] = $cssCode;
			}
		}
		$this->css = $newCSS;
		unset($newCSS);
	}
	
	//
	// GET ALL KEY Classes and IDs
	// --------------------
	// Returns all key classes and IDs
	//
	function getKeyClassesAndIDs() {
		$classes = array();
		$ids = array();
		foreach($this->css as $keyString => $cssCode) {
			$keyString = str_replace('__ ', '', $keyString);
			preg_match_all('/\..[^\s^#^>^<^\.^,^:]*/', $keyString, $cls);
			preg_match_all('/#.[^\s^#^>^<^\.^,^:]*/', $keyString, $id);
			
			if(sizeof($cls[0])) {
				$classes = array_merge($classes, $cls[0]);
			}
			if(sizeof($id[0])) {
				$ids = array_merge($ids, $id[0]);
			}			
			
			
		}
		if(sizeof($classes)) {
			$classes = array_unique($classes);
		}
		if(sizeof($ids)) {
			$ids = array_unique($ids);
		}		
		return array('ids' => $ids, 'classes' => $classes);
	}
	
	
	//
	// MODIFY KEYS
	// -----------
	// Takes in two arrays -- one with key elements to find, and one with replacements.
	// Searches and modifies all occurrences in CSS keys. Useful for modifying specific
	// classes and IDs
	function modifyKeys($finds, $replacements) {
		$theFinds = array();
		$theRepl = array();
		// First prepare the find/replace strings
		foreach($finds as $findString) {
			$theFinds[] = '/' . str_replace('.', '\.', $findString) . '([\s#\.<>:]|$)/';
		}
		foreach($replacements as $replString) {
			$theRepl[] = $replString . '\\1';
		}

		$keys = array_keys($this->css);
		$values = array_values($this->css);
		
		$keys = preg_replace($theFinds, $theRepl, $keys);
		$this->css = array_combine($keys, $values);
		
	}
	

	// 
	// 	GET CSS
	//	------------------------------
	//	Outputs all our stored, fixed (hopeffuly!) CSS
	//	
	function getCSS() {
		$response = '';
		foreach($this->css as $keyString => $cssCode) {
			$keyString = str_replace('__ ', '', $keyString);
			$cssCode = str_replace("}\\", '[TANTEK]', $cssCode);
			if($keyString == '[WPU_NESTED]') {
				$response .= $this->nestedItems[(int)$cssCode];
			} else {
				$response .= $keyString . '{' . $cssCode . "}\n\n";
			}
		}
		return $response;
	}
	
	function sendCSS() {
		header("Content-type: text/css");
		echo $this->getCSS();
	
	}
}


?>
