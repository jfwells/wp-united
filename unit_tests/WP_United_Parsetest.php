<?
define('WPU_TEST_ROOT_PATH', realpath(dirname(dirname(__FILE__))));

class WP_United_ParseTest extends PHPUnit_Framework_TestCase {

	public function setup() {
		define('IN_PHPBB', true);
		define('ABSPATH', true);
	}
	
	
	public function teardown() {}
	
	
	
	
	
	public function test_phpbb() {
		
		$files = @glob(WPU_TEST_ROOT_PATH . '/phpbb3 MOD/*.php');

		foreach($files as $file) { echo $file;
			require_once($file);
		}
		
	}

	public function test_wp() {
		
		$files = @glob(WPU_TEST_ROOT_PATH . '/WordPress Plugin/*.php');

		foreach($files as $file) { echo $file;
			if(stristr($file, 'wp-united.php') === false) {
				require_once($file);
			}
		}
		
	}

}