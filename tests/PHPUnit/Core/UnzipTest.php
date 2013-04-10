<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        $filename = dirname(__FILE__) . '/Unzip/' . $test . '.zip';

        if (class_exists('ZipArchive', false)) {
            $unzip = Piwik_Unzip::factory('ZipArchive', $filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(1, count($res));
            $this->assertFileExists($extractDir . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/../../tests/' . $test . '.txt');
            unlink($extractDir . $test . '.txt');

            $unzip = new Piwik_Unzip_ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(1, count($res));
            $this->assertFileExists($extractDir . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/../../tests/' . $test . '.txt');
            unlink($extractDir . $test . '.txt');
        }

        $unzip = Piwik_Unzip::factory('PclZip', $filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(1, count($res));
        $this->assertFileExists($extractDir . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/../../tests/' . $test . '.txt');
        unlink($extractDir . $test . '.txt');

        $unzip = new Piwik_Unzip_PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(1, count($res));
        $this->assertFileExists($extractDir . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/../../tests/' . $test . '.txt');
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
        $filename = dirname(__FILE__) . '/Unzip/' . $test . '.zip';

        if (class_exists('ZipArchive', false)) {
            $unzip = new Piwik_Unzip_ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(0, $res);
            $this->assertFileNotExists($extractDir . $test . '.txt');
            $this->assertFileNotExists($extractDir . '../' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/../' . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/../../' . $test . '.txt');
        }

        $unzip = new Piwik_Unzip_PclZip($filename);
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
     * @group Unzip
     */
    public function testAbsolutePathAttack()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $test = 'zaabs';
        $filename = dirname(__FILE__) . '/Unzip/' . $test . '.zip';

        if (class_exists('ZipArchive', false)) {
            $unzip = new Piwik_Unzip_ZipArchive($filename);
            $res = $unzip->extract($extractDir);
            $this->assertEquals(0, $res);
            $this->assertFileNotExists($extractDir . $test . '.txt');
            $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
        }

        $unzip = new Piwik_Unzip_PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(0, $res);
        $this->assertFileNotExists($extractDir . $test . '.txt');
        $this->assertFileNotExists(dirname(__FILE__) . '/' . $test . '.txt');
    }

    /**
     * @group Core
     * @group Unzip
     */
    public function testUnzipErrorInfo()
    {
        clearstatcache();
        $filename = dirname(__FILE__) . '/Unzip/zaabs.zip';
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';

        $unzip = new Piwik_Unzip_ZipArchive($filename);
        $this->assertContains('No error', $unzip->errorInfo());
    }

    /**
     * @group Core
     * @group Unzip
     */
    public function testUnzipEmptyFile()
    {
        clearstatcache();
        $filename = dirname(__FILE__) . '/Unzip/empty.zip';
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';

        $unzip = new Piwik_Unzip_ZipArchive($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(0, $res);
    }

    /**
     * @group Core
     * @group Unzip
     */
    public function testUnzipNotExistingFile()
    {
        clearstatcache();
        $filename = dirname(__FILE__) . '/Unzip/NotExisting.zip';

        try {
            $unzip = new Piwik_Unzip_ZipArchive($filename);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Exception not raised');
    }

    /**
     * @group Core
     * @group Unzip
     */
    public function testUnzipInvalidFile2()
    {
        clearstatcache();
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $filename = dirname(__FILE__) . '/Unzip/NotExisting.zip';

        $unzip = new Piwik_Unzip_PclZip($filename);
        $res = $unzip->extract($extractDir);
        $this->assertEquals(0, $res);

        $this->assertContains('PCLZIP_ERR_MISSING_FILE', $unzip->errorInfo());
    }

    /**
     * @group Core
     * @group Unzip
     */
    public function testGzipFile()
    {
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $extractFile = $extractDir . 'testgz.txt';
        $filename = dirname(__FILE__) . '/Unzip/test.gz';

        $unzip = new Piwik_Unzip_Gzip($filename);
        $res = $unzip->extract($extractFile);
        $this->assertTrue($res);

        $this->assertFileContentsEquals('TESTSTRING', $extractFile);
    }

    /**
     * @group Core
     * @group Unzip
     */
    public function testTarGzFile()
    {
        $extractDir = PIWIK_USER_PATH . '/tmp/latest/';
        $filename = dirname(__FILE__) . '/Unzip/test.tar.gz';

        $unzip = new Piwik_Unzip_Tar($filename, 'gz');
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
