<?php
/** 
*
* WP-United [Svenska]
*
* @package WP-United
* @version $Id: v0.8.4RC2 2010/01/14 John Wells (Jhong) Exp $
* @copyright (c) 2006-2010 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
* @translation by Oz
*/


/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(

	//Moved out from install.xml
	'BLOG' 					=>	'WordPress Blogg',
	'VISIT_BLOG'			=>	'Besök Användarens Blogg',
	'Function_Duplicate' 	=>	'En dubblett funktionsnamnet har upptäckts. Detta beror förmodligen på ett kraftigt moddad forum. Besök www.wp-united.com för att rapportera felet.',
	'WPU_Credit' 			=>	'Integration av %sWP-United%s',

));

?>