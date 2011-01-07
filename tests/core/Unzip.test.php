<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_Piwik_Unzip extends UnitTestCase
{
	public function test_relativePath()
	{
		clearstatcache();
		$extractDir = PIWIK_USER_PATH . '/tmp/latest/';
		$test = 'relative';
		$filename = dirname(__FILE__) . '/Unzip/'.$test.'.zip';

		if(class_exists('ZipArchive', false))
		{
			$unzip = Piwik_Unzip::factory('ZipArchive', $filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual(count($res), 1);
			$this->assertTrue(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
			unlink($extractDir . $test . '.txt');

			$unzip = new Piwik_Unzip_ZipArchive($filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual(count($res), 1);
			$this->assertTrue(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
			unlink($extractDir . $test . '.txt');
		}

		$unzip = Piwik_Unzip::factory('PclZip', $filename);
		$res = $unzip->extract($extractDir);
		$this->assertEqual(count($res), 1);
		$this->assertTrue(file_exists($extractDir . $test . '.txt'));
		$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
		$this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
		unlink($extractDir . $test . '.txt');

		$unzip = new Piwik_Unzip_PclZip($filename);
		$res = $unzip->extract($extractDir);
		$this->assertEqual(count($res), 1);
		$this->assertTrue(file_exists($extractDir . $test . '.txt'));
		$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
		$this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
		unlink($extractDir . $test . '.txt');
	}

	public function test_relativePathAttack()
	{
		clearstatcache();
		$extractDir = PIWIK_USER_PATH . '/tmp/latest/';
		$test = 'zaatt';
		$filename = dirname(__FILE__) . '/Unzip/'.$test.'.zip';

		if(class_exists('ZipArchive', false))
		{
			$unzip = new Piwik_Unzip_ZipArchive($filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual($res, 0);
			$this->assertFalse(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists($extractDir . '../' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../../' . $test . '.txt'));
		}

		$unzip = new Piwik_Unzip_PclZip($filename);
		$res = $unzip->extract($extractDir);
		$this->assertEqual($res, 0);
		$this->assertFalse(file_exists($extractDir . $test . '.txt'));
		$this->assertFalse(file_exists($extractDir . '../' . $test . '.txt'));
		$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
		$this->assertFalse(file_exists(dirname(__FILE__) . '/../' . $test . '.txt'));
		$this->assertFalse(file_exists(dirname(__FILE__) . '/../../' . $test . '.txt'));
	}

	public function test_absolutePathAttack()
	{
		clearstatcache();
		$extractDir = PIWIK_USER_PATH . '/tmp/latest/';
		$test = 'zaabs';
		$filename = dirname(__FILE__) . '/Unzip/'.$test.'.zip';

		if(class_exists('ZipArchive', false))
		{
			$unzip = new Piwik_Unzip_ZipArchive($filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual($res, 0);
			$this->assertFalse(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
		}

		$unzip = new Piwik_Unzip_PclZip($filename);
		$res = $unzip->extract($extractDir);
		$this->assertEqual($res, 0);
		$this->assertFalse(file_exists($extractDir . $test . '.txt'));
		$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
	}
}
