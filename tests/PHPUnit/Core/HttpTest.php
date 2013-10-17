<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Http;

class HttpTest extends PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider for testFetchRemoteFile
     */
    public function getMethodsToTest()
    {
        return array(
            array('curl'),
            array('fopen'),
            array('socket'),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider getMethodsToTest
     */
    public function testFetchRemoteFile($method)
    {
        $this->assertNotNull(Http::getTransportMethod());
        $version = Http::sendHttpRequestBy($method, 'http://api.piwik.org/1.0/getLatestVersion/', 30);
        $this->assertTrue((boolean)preg_match('/^([0-9.]+)$/', $version));
    }

    /**
     * @group Core
     */
    public function testFetchApiLatestVersion()
    {
        $destinationPath = PIWIK_USER_PATH . '/tmp/latest/LATEST';
        Http::fetchRemoteFile('http://api.piwik.org/1.0/getLatestVersion/', $destinationPath, 3);
        $this->assertFileExists($destinationPath);
        $this->assertGreaterThan(0, filesize($destinationPath));
    }

    /**
     * @group Core
     */
    public function testFetchLatestZip()
    {
        $destinationPath = PIWIK_USER_PATH . '/tmp/latest/latest.zip';
        Http::fetchRemoteFile('http://builds.piwik.org/latest.zip', $destinationPath, 3, 30);
        $this->assertFileExists($destinationPath);
        $this->assertGreaterThan(0, filesize($destinationPath));
    }

    /**
     * @group Core
     * 
     * @dataProvider getMethodsToTest
     */
    public function testCustomByteRange($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            'http://builds.piwik.org/latest.zip',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = array(10, 20),
            $getExtendedInfo = true
        );

        if ($method != 'fopen') {
            $this->assertEquals(206, $result['status']);
            $this->assertTrue(isset($result['headers']['Content-Range']));
            $this->assertEquals('bytes 10-20/', substr($result['headers']['Content-Range'], 0, 12));
            $this->assertEquals('application/zip', $result['headers']['Content-Type']);
        }
    }

    /**
     * @group Core
     * 
     * @dataProvider getMethodsToTest
     */
    public function testHEADOperation($method)
    {
        if ($method == 'fopen') {
            return; // not supported w/ this method
        }

        $result = Http::sendHttpRequestBy(
            $method,
            'http://builds.piwik.org/latest.zip',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'HEAD'
        );

        $this->assertEquals('', $result['data']);
        $this->assertEquals(200, $result['status']);

        $this->assertTrue(isset($result['headers']['Content-Length']), "Content-Length header not set!");
        $this->assertTrue(is_numeric($result['headers']['Content-Length']), "Content-Length header not numeric!");
        $this->assertEquals('application/zip', $result['headers']['Content-Type']);
    }
}
