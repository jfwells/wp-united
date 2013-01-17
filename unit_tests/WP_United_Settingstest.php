<?
define('WPU_TEST_ROOT_PATH', realpath(dirname(dirname(__FILE__))));
require_once('mockpress/mockpress.php');
require_once(WPU_TEST_ROOT_PATH . '/WordPress Plugin/wp-united/wp-united.php');

class WP_United_SettingsTest extends PHPUnit_Framework_TestCase {

	public function setup() {
		define('ABSPATH', true);
		
	}
	
	
	public function teardown() {}
	
	
	
	
	
	public function testSettingsObjectCreate() {
		
		$settings = WP_United_Settings::Create();
		
		$this->assertTrue(is_object($settings));
		
		return $settings;
	}

	/**
	*	@depends testSettingsObjectCreate
	*/
	public function testSettingsSaveEmpty($settings) {
		
		$emptyData = array();
		$settings->update_settings($emptyData);
	}
	


}