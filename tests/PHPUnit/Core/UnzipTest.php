<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
class UnzipTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group Unzip
     */
    public function testRelativePath()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $test = 'relative';
        $filename = dirname(__FILE__) . '/Unzip/'.$test.'.zip';

        if(class_exists('ZipArchive', false))
        {
            $unzip = Piwik_Unzip::factory('ZipArchive', $filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(count($res), 1);
            $this->assertTrue(file_exists($extractDir . $test . '.txt'));
            $this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
            $this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
            unlink($extractDir . $test . '.txt');

            $unzip = new Piwik_Unzip_ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(count($res), 1);
            $this->assertTrue(file_exists($extractDir . $test . '.txt'));
            $this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
            $this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
            unlink($extractDir . $test . '.txt');
        }

        $unzip = Piwik_Unzip::factory('PclZip', $filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(count($res), 1);
        $this->assertTrue(file_exists($extractDir . $test . '.txt'));
        $this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
        $this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
        unlink($extractDir . $test . '.txt');

        $unzip = new Piwik_Unzip_PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(count($res), 1);
        $this->assertTrue(file_exists($extractDir . $test . '.txt'));
        $this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
        $this->assertFalse(file_exists(dirname(__FILE__) . '/../../tests/' . $test . '.txt'));
        unlink($extractDir . $test . '.txt');
    }

    /**
     * @group Core
     * @group Unzip
     */
    public function testRelativePathAttack()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $test = 'zaatt';
        $filename = dirname(__FILE__) . '/Unzip/'.$test.'.zip';

        if(class_exists('ZipArchive', false))
        {
            $unzip = new Piwik_Unzip_ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals($res, 0);
            $this->assertFalse(file_exists($extractDir . $test . '.txt'));
            $this->assertFalse(file_exists($extractDir . '../' . $test . '.txt'));
            $this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
            $this->assertFalse(file_exists(dirname(__FILE__) . '/../' . $test . '.txt'));
            $this->assertFalse(file_exists(dirname(__FILE__) . '/../../' . $test . '.txt'));
        }

        $unzip = new Piwik_Unzip_PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals($res, 0);
        $this->assertFalse(file_exists($extractDir . $test . '.txt'));
        $this->assertFalse(file_exists($extractDir . '../' . $test . '.txt'));
        $this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
        $this->assertFalse(file_exists(dirname(__FILE__) . '/../' . $test . '.txt'));
        $this->assertFalse(file_exists(dirname(__FILE__) . '/../../' . $test . '.txt'));
    }

    /**
     * @group Core
     * @group Unzip
     */
    public function testAbsolutePathAttack()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $test = 'zaabs';
        $filename = dirname(__FILE__) . '/Unzip/'.$test.'.zip';

        if(class_exists('ZipArchive', false))
        {
            $unzip = new Piwik_Unzip_ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals($res, 0);
            $this->assertFalse(file_exists($extractDir . $test . '.txt'));
            $this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
        }

        $unzip = new Piwik_Unzip_PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals($res, 0);
        $this->assertFalse(file_exists($extractDir . $test . '.txt'));
        $this->assertFalse(file_exists(dirname(__FILE__) . '/' . $test . '.txt'));
    }
}
