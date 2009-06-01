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
	
	//
	//	GET INSTANCE
	//	----------------------
	//	Makes class a Singleton.
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
	}
	
	function clear() {
		$this->css = array();	
	}

	// 
	// 	PARSE STRING
	//	------------------------------
	//	Parses raw CSS, storing the keys and CSS code. We don't separate or process the keys, we don't need to.
	//	
	function parseString($str) {
		
		// Remove comments
		$str = preg_replace("/\/\*(.*)?\*\//Usi", "", $str);
		$str = str_replace("\t", "", $str);
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
	//	Stores a full CSS selector in our class. Combines identical selectors together
	//	if CSS is passed in in the order it should appear on the page, this shouldn't affect specicifity	
	function addSelector($keys, $cssCode) {
		$keys = trim($keys);
		$cssCode = trim($cssCode);
		if(!isset($this->css[$keys])) {
			$this->css[$keys] = $cssCode;
		} else {
			$this->css[$keys] = str_replace(';;', ';', $this->css[$keys] . ';' . $cssCode);
		}
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
	
	function renameIds($prefix) {
		$fixed = array();
		foreach($this->css as $keyString => $cssCode) {
			$fixed[str_replace("#", "#{$prefix}", $keyString)] = $cssCode;
		}
		$this->css = $fixed;
		unset($fixed);
	}
	
	function renameClasses($prefix) {
		$fixed = array();
		foreach($this->css as $keyString => $cssCode) {
			$fixed[str_replace(".", ".{$prefix}", $keyString)] = $cssCode;
		}
		$this->css = $fixed;
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
		$seps = array(' ', '>', '<', '.', '#');
		foreach($this->css as $keyString => $cssCode) {
			$fixedKeys = array();
			$keys = explode(',', $keyString);
			foreach($keys as $key) {
				$fixedKey = trim($key);
				$foundBody = false;
				// remove references to 'body'
				foreach($seps as $sep) {
					$keyElements = explode($sep, $fixedKey);
					if((strtolower($keyElements[0]) == "body") || (strtolower($keyElements[0]) == "body") ) {
						$keyElements[0] = $prefix;
						if(!$removeBody) {
							if(sizeof($keyElements) > 1) { 
								$fixedKey = implode($sep, $keyElements);
							} else {
								$fixedKey = $keyElements[0] . $sep;
							}
							
						} 
						$foundBody = true;
						$fixedKey = '';
					}
				}
				// add #id selector before each selector
				if(!$foundBody) {
					if($fixedKey[0] != "@") {
						$fixedKey = "{$prefix} " . $fixedKey;
					}
					
				}
				if(!empty($fixedKey)) {
					$fixedKeys[] = $fixedKey;
				}
			}
			// recreate the fixed key
			if(sizeof($fixedKeys)) {
				$fixedKeyString = implode(', ', $fixedKeys);
						
				if(!isset($fixed[$fixedKeyString])) {
					$fixed[$fixedKeyString] = $cssCode;
				} else {
					$fixed[$fixedKeyString] = str_replace(';;', ';', $fixed[$fixedKeyString] . ';' . $cssCode);
				}
			}
		}
		// done
		$this->css = $fixed;
		unset($fixed);

	}

	// 
	// 	GET CSS
	//	------------------------------
	//	Outputs all our stored, fixed (hopeffuly!) CSS
	//	
	function getCSS() {
		$response = '';
		foreach($this->css as $keyString => $cssCode) {
			$response .= $keyString . '{' . $cssCode . '}';
		}
		return $response;
	}
	
	function sendCSS() {
		header("Content-type: text/css");
		echo $this->getCSS();
	
	}
}


?>
