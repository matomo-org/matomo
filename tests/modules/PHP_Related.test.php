<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '../..');
}
require_once PATH_TEST_TO_ROOT ."/tests/config_test.php";


class test_staticAttr
{
	static public $a = 'testa';
	public $b = 'testb';
}

class test_magicMethodStaticAttr
{
	static $test = "test";
	
	function __get($name)
	{
		print("reading static attr ; __get called");
		return 1;
	}
}
		
class Test_PHP_Related extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
	}
	
	public function tearDown()
	{
	}
	
	/**
	 * test reading static attribute of a variable class
	 */
	public function test_staticAttr()
	{
		// use this trick to read the static attribute of the class
		// $class::$methodsNotToPublish doesn't work
		$vars = get_class_vars("test_staticAttr");
		
		$this->assertEqual( $vars['a'], 'testa' );
	}
	/**
	 * __get is not called when reading a static attribute from a class... snif 
	 */
	public function test_magicMethodStaticAttr()
	{
		$val = test_magicMethodStaticAttr::$test;
		
		$this->assertEqual( $val, "test" );
	}
	
}