<?
define('WPU_TEST_ROOT_PATH', realpath(dirname(dirname(__FILE__))));

echo(WPU_TEST_ROOT_PATH);

class WP_United_SettingsTest extends PHPUnit_Framework_TestCase {

	private $settings;

	public function setup() {
		define('IN_PHPBB', true);
		require_once(WPU_TEST_ROOT_PATH . '/WordPress Plugin/wp-united/base-classes.php');
	}
	public function teardown() {}
	
	public function testSettingsObjectCreate() {
		
		$this->settings = WP_United_Settings::Create();
		
		$this->assertTrue(is_object($this->settings));
	}


}