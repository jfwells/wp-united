<?
define('WPU_TEST_ROOT_PATH', realpath(dirname(dirname(__FILE__))));

require_once(WPU_TEST_ROOT_PATH . '/../../workspace/wordpress/wp-load.php');

class WP_United_WPTest extends PHPUnit_Framework_TestCase {

	public function setup() {
		
	}
	
	
	public function teardown() {
	
		
	}
	
	
	public function testBootWordPress() {
		ob_start();
		wp();
		require_once( ABSPATH . WPINC . '/template-loader.php' );
		ob_end_clean();
	}
	
	
	

}