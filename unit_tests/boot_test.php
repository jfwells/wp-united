<?
define('WPU_TEST_ROOT_PATH', realpath(dirname(dirname(__FILE__))));

class WP_United_WPTest extends PHPUnit_Framework_TestCase {

	public function setup() {
		
	}
	
	
	public function teardown() {
	
		
	}
	
	
	public function testBootWordPress() {
		ob_start();
		require_once(WPU_TEST_ROOT_PATH . '/../../workspace/wordpress/wp-blog-header.php');
		ob_end_clean();
	}
	
	
	

}