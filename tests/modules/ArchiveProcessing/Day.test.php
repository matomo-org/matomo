<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '../../..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once "../../../tests/config_test.php";
}

require_once "ArchiveProcessing.php";
require_once "ArchiveProcessing/Day.php";

class Test_Piwik_ArchiveProcessing_Day extends UnitTestCase
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
	/*
	public function test_getActionCategoryFromName_normal()
	{
		$name = 'DGgieqho  gea ga ae gae / 3145245 / geq geqa ga eag ae';
		
		$expected = array(
				'DGgieqho  gea ga ae gae',
				'3145245',
				'geq geqa ga eag ae'
				);
		Piwik_ArchiveProcessing_Day::setCategoryDelimiter('/');
		$this->assertEqual( Piwik_ArchiveProcessing_Day::getActionCategoryFromName($name),
							$expected
					);
	}
	
	public function test_getActionCategoryFromName_emptyCat()
	{
		$name = '// 	/	/ /DGgieqho  gea ga ae gae / 314//5245 / geq geqa ga eag ae/    ';
		
		$expected = array(
				'DGgieqho  gea ga ae gae',
				'314',
				'5245',
				'geq geqa ga eag ae'
				);
		Piwik_ArchiveProcessing_Day::setCategoryDelimiter('/');
		$this->assertEqual( Piwik_ArchiveProcessing_Day::getActionCategoryFromName($name),
							$expected
					);
	}
	public function test_getActionCategoryFromName_strangeChar()
	{
		$name = '// 	/	/ /       £$%^&*())(&*&%}{~@:>897864564DGgieqho  gea ga ae gae / 314//5245 / geq geqa ga eag ae/    ';
		
		$expected = array(
				'£$%^&*())(&*&%}{~@:>897864564DGgieqho  gea ga ae gae',
				'314',
				'5245',
				'geq geqa ga eag ae'
				);
		Piwik_ArchiveProcessing_Day::setCategoryDelimiter('/');
		$this->assertEqual( Piwik_ArchiveProcessing_Day::getActionCategoryFromName($name),
							$expected
					);
	}*/
	
}