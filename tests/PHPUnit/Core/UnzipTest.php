<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Unzip\Gzip;
use Piwik\Unzip\PclZip;
use Piwik\Unzip\Tar;
use Piwik\Unzip;
use Piwik\Unzip\ZipArchive;

class UnzipTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     */
    public function testRelativePath()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $test = 'relative';
        $filename = dirname(__FILE__) . '/Unzip/' . $test . '.zip';

        if (class_exists('ZipArchive', false)) {
            $unzip = Unzip::factory('ZipArchive', $filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(1, count($res));
            $this->assertFileExists($extractDir . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/../../tests/' . $test . '.txt');
            unlink($extractDir . $test . '.txt');

            $unzip = new ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(1, count($res));
            $this->assertFileExists($extractDir . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/../../tests/' . $test . '.txt');
            unlink($extractDir . $test . '.txt');
        }

        $unzip = Unzip::factory('PclZip', $filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(1, count($res));
        $this->assertFileExists($extractDir . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/../../tests/' . $test . '.txt');
        unlink($extractDir . $test . '.txt');

        $unzip = new PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(1, count($res));
        $this->assertFileExists($extractDir . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/../../tests/' . $test . '.txt');
        unlink($extractDir . $test . '.txt');
    }

    /**
     * @group Core
     */
    public function testRelativePathAttack()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $test = 'zaatt';
        $filename = dirname(__FILE__) . '/Unzip/' . $test . '.zip';

        if (class_exists('ZipArchive', false)) {
            $unzip = new ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(0, $res);
            $this->assertFileNotExists($extractDir . $test . '.txt');
            $this->assertFileNotExists($extractDir . '../' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/../' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/../../' . $test . '.txt');
        }

        $unzip = new PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(0, $res);
        $this->assertFileNotExists($extractDir . $test . '.txt');
        $this->assertFileNotExists($extractDir . '../' . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/../' . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/../../' . $test . '.txt');
    }

    /**
     * @group Core
     */
    public function testAbsolutePathAttack()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $test = 'zaabs';
        $filename = dirname(__FILE__) . '/Unzip/' . $test . '.zip';

        if (class_exists('ZipArchive', false)) {
            $unzip = new ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(0, $res);
            $this->assertFileNotExists($extractDir . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
        }

        $unzip = new PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(0, $res);
        $this->assertFileNotExists($extractDir . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
    }

    /**
     * @group Core
     */
    public function testUnzipErrorInfo()
    {
        clearstatcache();
        $filename = dirname(__FILE__) . '/Unzip/zaabs.zip';
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';

        $unzip = new ZipArchive($filename);
        $this->assertContains('No error', $unzip->errorInfo());
    }

    /**
     * @group Core
     */
    public function testUnzipEmptyFile()
    {
        clearstatcache();
        $filename = dirname(__FILE__) . '/Unzip/empty.zip';
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';

        $unzip = new ZipArchive($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(0, $res);
    }

    /**
     * @group Core
     */
    public function testUnzipNotExistingFile()
    {
        clearstatcache();
        $filename = dirname(__FILE__) . '/Unzip/NotExisting.zip';

        try {
            $unzip = new ZipArchive($filename);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Exception not raised');
    }

    /**
     * @group Core
     */
    public function testUnzipInvalidFile2()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $filename = dirname(__FILE__) . '/Unzip/NotExisting.zip';

        $unzip = new PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(0, $res);

        $this->assertContains('PCLZIP_ERR_MISSING_FILE', $unzip->errorInfo());
    }

    /**
     * @group Core
     */
    public function testGzipFile()
    {
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $extractFile = $extractDir . 'testgz.txt';
        $filename = dirname(__FILE__) . '/Unzip/test.gz';

        $unzip = new Gzip($filename);
        $res = $unzip->extract($extractFile);
        $this->assertTrue($res);

        $this->assertFileContentsEquals('TESTSTRING', $extractFile);
    }

    /**
     * @group Core
     */
    public function testTarGzFile()
    {
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $filename = dirname(__FILE__) . '/Unzip/test.tar.gz';

        $unzip = new Tar($filename, 'gz');
        $res = $unzip->extract($extractDir);
        $this->assertTrue($res);

        $this->assertFileContentsEquals('TESTDATA', $extractDir . 'tarout1.txt');
        $this->assertFileContentsEquals('MORETESTDATA', $extractDir . 'tardir/tarout2.txt');
    }

    private function assertFileContentsEquals($expectedContent, $path)
    {
        $this->assertTrue(file_exists($path));

        $fd = fopen($path, 'rb');
        $actualContent = fread($fd, filesize($path));
        fclose($fd);

        $this->assertEquals($expectedContent, $actualContent);
    }
}
