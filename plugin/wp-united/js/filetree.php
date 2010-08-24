<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

if(stristr($_POST['dir'], '..')) {
	die();
}

$docRoot =  (isset($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF']) ) );
$docRoot = @realpath($docRoot); 
$docRoot = ($docRoot[strlen($docRoot)-1] == "/" ) ? $docRoot : $docRoot . "/";

$fileLoc = $_POST['dir'];

if(stristr($fileLoc, $docRoot) === false) {
	$fileLoc = $docRoot . urldecode($fileLoc);
	$fileLoc = str_replace('//', '/', $fileLoc);
}

if( file_exists($docRoot) ) {
	$files = scandir($fileLoc);
	natcasesort($files);
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($fileLoc. $file) && $file != '.' && $file != '..' && is_dir($fileLoc . $file) ) {
				echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($fileLoc . $file) . "/\">" . htmlentities($file) . "</a></li>";
			}
		}
		// All files
		foreach( $files as $file ) {
			if( file_exists($fileLoc . $file) && $file != '.' && $file != '..' && !is_dir($fileLoc . $file) ) {
				$ext = preg_replace('/^.*\./', '', $file);
				echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($fileLoc . $file) . "\">" . htmlentities($file) . "</a></li>";
			}
		}
		echo "</ul>";	
	}
}

?>