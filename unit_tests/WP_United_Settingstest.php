<?



class WP_United_SettingsTest extends PHPUnit_Framework_TestCase {

	private $settings;

	public function setup() {
		define('IN_PHPBB', true);
		require_once('../WordPress Plugin/wp-united/base-classes.php');
	}
	public function teardown() {}
	
	public function testSettingsObjectCreate() {
		
		$this->settings = WP_United_Settings::Create();
		
		$this->assertTrue(is_object($this->settings));
	}


}