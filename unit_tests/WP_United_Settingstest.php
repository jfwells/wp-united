<?
define('WPU_TEST_ROOT_PATH', realpath(dirname(dirname(__FILE__))));

class WP_United_SettingsTest extends PHPUnit_Framework_TestCase {

	public function setup() {
		define('IN_PHPBB', true);
		require_once(WPU_TEST_ROOT_PATH . '/WordPress Plugin/wp-united/base-classes.php');
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