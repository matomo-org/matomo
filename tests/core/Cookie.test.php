<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_Piwik_Cookie_Mock_Class {
}

require_once 'Cookie.php';
class Test_Piwik_Cookie extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
		parent::setUp();
		$_GET = $_POST = array();
	}
	
	public function tearDown()
	{
		parent::tearDown();
	}

	function test_jsonSerialize()
	{
		// @see http://bugs.php.net/38680
		if(PHP_VERSION >= '5.2.0' && PHP_VERSION < '5.2.1')
			return;

		$tests = array(
			'null' => null,
			'bool false' => false,
			'bool true' => true,
			'negative int' => -42,
			'zero' => 0,
			'positive int' => 42,
			'float' => 1.25,
			'empty string' => '',
			'nul in string' => "\0",
			'carriage return in string' => "first line\r\nsecond line",
			'utf7 in string' => 'hello, world',
			'utf8 in string' => '是',
			'empty array' => array(),
			'single element array' => array("test"),
			'associative array' => array("alpha", 2 => "beta"),
			'mixed keys' => array('first' => 'john', 'last' => 'doe', 10 => 'age'),
			'nested arrays' => array('top' => array('middle' => 2, array('bottom'), 'last'), 'the end' => true),
			'array confusion' => array('"', "'", '}', ';', ':'),
		);

		foreach($tests as $id => $testData)
		{
			$this->assertEqual( json_decode(json_encode($testData), $assoc = true), $testData, $id );
		}
	}

	function test_safeSerialize()
	{
		$tests = array(
			'null' => null,
			'bool false' => false,
			'bool true' => true,
			'negative int' => -42,
			'zero' => 0,
			'positive int' => 42,
			'float' => 1.25,
			'empty string' => '',
			'nul in string' => "\0",
			'carriage return in string' => "first line\r\nsecond line",
			'utf7 in string' => 'hello, world',
			'utf8 in string' => '是',
			'empty array' => array(),
			'single element array' => array("test"),
			'associative array' => array("alpha", 2 => "beta"),
			'mixed keys' => array('first' => 'john', 'last' => 'doe', 10 => 'age'),
			'nested arrays' => array('top' => array('middle' => 2, array('bottom'), 'last'), 'the end' => true),
			'array confusion' => array('"', "'", '}', ';', ':'),
		);

		foreach($tests as $id => $testData)
		{
			$this->assertEqual( safe_serialize($testData), serialize($testData), $id );
		}

		foreach($tests as $id => $testData)
		{
			$this->assertEqual( unserialize(safe_serialize($testData)), $testData, $id );
			$this->assertTrue( safe_unserialize(safe_serialize($testData)) === $testData, $id );
			$this->assertTrue( safe_unserialize(serialize($testData)) === $testData, $id );
		}

		/*
		 * serialize() uses its internal maachine representation when floats expressed in E-notation,
		 * which may vary between php versions, OS, and hardware platforms
		 */
		$testData = $tests['exp float'] = -5.0E+142;
		// intentionally disabled; this doesn't work
//		$this->assertEqual( safe_serialize($testData), serialize($testData) );
		$this->assertEqual( unserialize(safe_serialize($testData)), $testData );
		$this->assertTrue( safe_unserialize(safe_serialize($testData)) === $testData) ;
		// workaround: cast floats into strings
		$this->assertTrue( (string)safe_unserialize(serialize($testData)) === (string)$testData );

		$unserialized = array(
			'announcement' => true,
			'source' => array(
				array(
					'filename' => 'php-5.3.3.tar.bz2',
					'name' => 'PHP 5.3.3 (tar.bz2)',
					'md5' => '21ceeeb232813c10283a5ca1b4c87b48',
					'date' => '22 July 2010',
				),
				array(
					'filename' => 'php-5.3.3.tar.gz',
					'name' => 'PHP 5.3.3 (tar.gz)',
					'md5' => '5adf1a537895c2ec933fddd48e78d8a2',
					'date' => '22 July 2010',
				),
			),
			'date' => '22 July 2010',
			'version' => '5.3.3',
		);
		$serialized = 'a:4:{s:12:"announcement";b:1;s:6:"source";a:2:{i:0;a:4:{s:8:"filename";s:17:"php-5.3.3.tar.bz2";s:4:"name";s:19:"PHP 5.3.3 (tar.bz2)";s:3:"md5";s:32:"21ceeeb232813c10283a5ca1b4c87b48";s:4:"date";s:12:"22 July 2010";}i:1;a:4:{s:8:"filename";s:16:"php-5.3.3.tar.gz";s:4:"name";s:18:"PHP 5.3.3 (tar.gz)";s:3:"md5";s:32:"5adf1a537895c2ec933fddd48e78d8a2";s:4:"date";s:12:"22 July 2010";}}s:4:"date";s:12:"22 July 2010";s:7:"version";s:5:"5.3.3";}';

		$this->assertTrue( unserialize($serialized) === $unserialized );
		$this->assertEqual( serialize($unserialized), $serialized );

		$this->assertTrue( safe_unserialize($serialized) === $unserialized );
		$this->assertEqual( safe_serialize($unserialized), $serialized );
		$this->assertTrue( safe_unserialize(safe_serialize($unserialized)) === $unserialized );
		$this->assertEqual( safe_serialize(safe_unserialize($serialized)), $serialized );

		$a = 'O:31:"Test_Piwik_Cookie_Phantom_Class":0:{}';
		$this->assertFalse( safe_unserialize($a), "test: unserializing an object where class not (yet) defined" );

		$a = 'O:28:"Test_Piwik_Cookie_Mock_Class":0:{}';
		$this->assertFalse( safe_unserialize($a), "test: unserializing an object where class is defined" );

		$a = 'a:1:{i:0;O:28:"Test_Piwik_Cookie_Mock_Class":0:{}}';
		$this->assertFalse( safe_unserialize($a), "test: unserializing nested object where class is defined" );

		$a = 'a:2:{i:0;s:4:"test";i:1;O:28:"Test_Piwik_Cookie_Mock_Class":0:{}}';
		$this->assertFalse( safe_unserialize($a), "test: unserializing another nested object where class is defined" );

		$a = 'O:28:"Test_Piwik_Cookie_Mock_Class":1:{s:34:"'."\0".'Test_Piwik_Cookie_Mock_Class'."\0".'name";s:4:"test";}';
		$this->assertFalse( safe_unserialize($a), "test: unserializing object with member where class is defined" );

		// arrays and objects cannot be used as keys, i.e., generates "Warning: Illegal offset type ..."
		$a = 'a:2:{i:0;a:0:{}O:28:"Test_Piwik_Cookie_Mock_Class":0:{}s:4:"test";';
		$this->assertFalse( safe_unserialize($a), "test: unserializing with illegal key" );
	}
}
