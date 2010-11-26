<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_Piwik_Unzip extends UnitTestCase
{
	public function test_relativePath()
	{
		if(defined('ZIPARCHIVE::ER_OK'))
		{
			$test = 'relative';
			$filename = dirname(__FILE__) . '/' . $test . '.zip';
			$zip = new ZipArchive;
			$zip->open($filename, ZIPARCHIVE::OVERWRITE);
			$zip->addFromString($test .'.txt', 'test');
			$zip->close();
			$this->assertTrue(file_exists($filename));

			$extractDir = PIWIK_USER_PATH . '/tmp/latest/';

			$unzip = Piwik_Unzip::getDefaultUnzip($filename);
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

			$unzip = new Piwik_Unzip_PclZip($filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual(count($res), 1);
			$this->assertTrue(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
			unlink($extractDir . $test . '.txt');

			unlink($filename);
		}
	}

	public function test_relativePathAttack()
	{
		clearstatcache();
		if(defined('ZIPARCHIVE::ER_OK'))
		{
			$test = 'zaatt';
			$filename = dirname(__FILE__) . '/' . $test . '.zip';
			$zip = new ZipArchive;
			$zip->open($filename, ZIPARCHIVE::OVERWRITE);
			$zip->addFromString('../' . $test .'.txt', 'test');
			$zip->close();
			$this->assertTrue(file_exists($filename));

			$extractDir = PIWIK_USER_PATH . '/tmp/latest/';

			$unzip = new Piwik_Unzip_ZipArchive($filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual($res, 0);
			$this->assertFalse(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists($extractDir . '../' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../../' . $test . '.txt'));

			$unzip = new Piwik_Unzip_PclZip($filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual($res, 0);
			$this->assertFalse(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists($extractDir . '../' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../' . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/../../' . $test . '.txt'));

			unlink($filename);
		}
	}

	public function test_absolutePathAttack()
	{
		clearstatcache();
		if(defined('ZIPARCHIVE::ER_OK'))
		{
			$test = 'zaabs';
			$filename = dirname(__FILE__) . '/' . $test . '.zip';
			$zip = new ZipArchive;
			$zip->open($filename, ZIPARCHIVE::OVERWRITE);
			$zip->addFromString(dirname(__FILE__) . '/' . $test .'.txt', 'test');
			$zip->close();
			$this->assertTrue(file_exists($filename));

			$extractDir = PIWIK_USER_PATH . '/tmp/latest/';

			$unzip = new Piwik_Unzip_ZipArchive($filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual($res, 0);
			$this->assertFalse(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));

			$unzip = new Piwik_Unzip_PclZip($filename);
			$res = $unzip->extract($extractDir);
			$this->assertEqual($res, 0);
			$this->assertFalse(file_exists($extractDir . $test . '.txt'));
			$this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));

			unlink($filename);
		}
	}
}
