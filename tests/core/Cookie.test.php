<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

class Test_Piwik_Cookie_Mock_Class {
}

class Test_Piwik_Cookie extends UnitTestCase
{
    public function testUnserializeArray()
    {
		$a = array('value1', 'value2');
		$as = serialize($a);
		$expected = 'a:2:{i:0;s:6:"value1";i:1;s:6:"value2";}';
		$this->assertEqual( $as, $expected );

		$ua = Piwik_Cookie::unserialize_array($as);
		$this->assertTrue( is_array($ua) && count($ua) == 2 && $ua[0] === 'value1' && $ua[1] === 'value2' );

		$a = 'O:31:"Test_Piwik_Cookie_Phantom_Class":0:{}';
		try {
			unserialize($a);
			$this->fail("Expected exception not raised");
		} catch(Exception $expected) {
			echo "test: unserializing an object where class not (yet) defined<br>\n";
		}

		$ua = Piwik_Cookie::unserialize_array($a);
		$this->assertEqual( $a, $ua );

		$a = 'O:28:"Test_Piwik_Cookie_Mock_Class":0:{}';
		try {
			unserialize($a);
			echo "test: unserializing an object where class is defined<br>\n";
		} catch(Exception $unexpected) {
			$this->fail("Unexpected exception raised");
		}

		$ua = Piwik_Cookie::unserialize_array($a);
		$this->assertEqual( $a, $ua );

		$a = 'a:1:{i:0;O:28:"Test_Piwik_Cookie_Mock_Class":0:{}}';
		try {
			unserialize($a);
			echo "test: unserializing nested object where class is defined<br>\n";
		} catch(Exception $unexpected) {
			$this->fail("Unexpected exception raised");
		}

		$ua = Piwik_Cookie::unserialize_array($a);
		$this->assertEqual( $a, $ua );

		$a = 'a:2:{i:0;s:4:"test";i:1;O:28:"Test_Piwik_Cookie_Mock_Class":0:{}}';
		try {
			unserialize($a);
			echo "test: unserializing another nested object where class is defined<br>\n";
		} catch(Exception $unexpected) {
			$this->fail("Unexpected exception raised");
		}

		$ua = Piwik_Cookie::unserialize_array($a);
		$this->assertEqual( $a, $ua );

		$a = 'O:28:"Test_Piwik_Cookie_Mock_Class":1:{s:34:"'."\0".'Test_Piwik_Cookie_Mock_Class'."\0".'name";s:4:"test";}';
		try {
			unserialize($a);
			echo "test: unserializing object with member where class is defined<br>\n";
		} catch(Exception $unexpected) {
			$this->fail("Unexpected exception raised");
		}

		$ua = Piwik_Cookie::unserialize_array($a);
		$this->assertEqual( $a, $ua );

		$a = 'a:1:{s:4:"test";s:1:"'."\0".'";}';
		try {
			unserialize($a);
			echo "test: unserializing with leading null byte<br>\n";
		} catch(Exception $unexpected) {
			$this->fail("Unexpected exception raised");
		}

		$ua = Piwik_Cookie::unserialize_array($a);
		$this->assertEqual( $a, $ua );

		$a = 'a:1:{s:4:"test";s:3:"'."a\0b".'";}';
		try {
			unserialize($a);
			echo "test: unserializing with leading intervening byte<br>\n";
		} catch(Exception $unexpected) {
			$this->fail("Unexpected exception raised");
		}

		$ua = Piwik_Cookie::unserialize_array($a);
		$this->assertEqual( $a, $ua );

		// arrays and objects cannot be used as keys, i.e., generates "Warning: Illegal offset type ..."
		$a = 'a:2:{i:0;a:0:{}O:28:"Test_Piwik_Cookie_Mock_Class":0:{}s:4:"test";';
		$ua = Piwik_Cookie::unserialize_array($a);
		$this->assertEqual( $a, $ua );
    }
}

