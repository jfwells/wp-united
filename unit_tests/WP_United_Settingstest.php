<?
define('WPU_TEST_ROOT_PATH', realpath(dirname(dirname(__FILE__))));
require_once('mockpress/mockpress.php');
require_once(WPU_TEST_ROOT_PATH . '/WordPress Plugin/wp-united/wp-united.php');
define('ABSPATH', true);

class WP_United_SettingsTest extends PHPUnit_Framework_TestCase {

	public function setup() {
		
		
	}
	
	
	public function teardown() {}
	
	
	
	
	
	public function testSettingsObjectCreate() {
		
		//$settings = WP_United_Settings::Create();
		
		//$this->assertTrue(is_object($settings));
		
		//return $settings;
	}


	


}