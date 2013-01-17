<?
define('WPU_TEST_ROOT_PATH', realpath(dirname(dirname(__FILE__))));

require_once('mockpress/mockpress.php');

class WP_United_WPTest extends PHPUnit_Framework_TestCase {

	public function setup() {
		
	}
	
	
	public function teardown() {
	
		
	}
	
	
	public function testBootWordPress() {
		get_option('test');
	}
	
	
	

}