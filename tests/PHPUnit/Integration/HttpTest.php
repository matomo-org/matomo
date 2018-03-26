<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Http;
use Piwik\Tests\Framework\Fixture;

/**
 * @group Core
 * @group HttpTest
 */
class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider for testFetchRemoteFile
     */
    public function getMethodsToTest()
    {
        return array(
            'curl' => array('curl'),
            'fopen' => array('fopen'),
            'socket' => array('socket'),
        );
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testFetchRemoteFile($method)
    {
        $this->assertNotNull(Http::getTransportMethod());
        $result = Http::sendHttpRequestBy($method, Fixture::getRootUrl() . 'piwik.js', 30);
        $this->assertTrue(strpos($result, 'Piwik') !== false);
    }

    public function testFetchApiLatestVersion()
    {
        $destinationPath = PIWIK_USER_PATH . '/tmp/latest/LATEST';
        Http::fetchRemoteFile(Fixture::getRootUrl(), $destinationPath, 3);
        $this->assertFileExists($destinationPath);
        $this->assertGreaterThan(0, filesize($destinationPath));
    }

    public function testFetchLatestZip()
    {
        $destinationPath = PIWIK_USER_PATH . '/tmp/latest/latest.zip';
        Http::fetchRemoteFile(Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/fixture.zip', $destinationPath, 3, 30);
        $this->assertFileExists($destinationPath);
        $this->assertGreaterThan(0, filesize($destinationPath));
    }

    public function testBuildQuery()
    {
        $this->assertEquals('', Http::buildQuery(array()));
        $this->assertEquals('test=foo', Http::buildQuery(array('test' => 'foo')));
        $this->assertEquals('test=foo&bar=baz', Http::buildQuery(array('test' => 'foo', 'bar' => 'baz')));
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testCustomByteRange($method)
    {
        if ($method == 'fopen') {
            return; // not supported w/ this method
        }

        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . '/piwik.js',
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

        $this->assertEquals(206, $result['status']);
        $this->assertTrue(isset($result['headers']['Content-Range']));
        $this->assertEquals('bytes 10-20/', substr($result['headers']['Content-Range'], 0, 12));
        $this->assertTrue(in_array($result['headers']['Content-Type'], array('application/x-javascript', 'application/javascript')));
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHEADOperation($method)
    {
        if ($method == 'fopen') {
            return; // not supported w/ this method
        }

        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/fixture.zip',
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
        $this->assertTrue(in_array($result['headers']['Content-Type'], array('application/zip', 'application/x-zip-compressed')));
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpAuthentication($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/HttpAuthentication.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'GET',
            $httpUsername = 'test',
            $httpPassword = 'test'
        );

        $this->assertEquals('Authentication successful', $result['data']);
        $this->assertEquals(200, $result['status']);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpAuthenticationInvalid($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/HttpAuthentication.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'GET',
            $httpUsername = '',
            $httpPassword = ''
        );

        $this->assertEquals(401, $result['status']);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpPost_ViaString($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/Post.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = '',
            $httpPassword = '',
            'abc12=43&abfec=abcdef'
        );

        $this->assertEquals('{"abc12":"43","abfec":"abcdef","method":"post"}', $result);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpPost_ViaArray($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/Post.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = '',
            $httpPassword = '',
            array('adf2' => '44', 'afc23' => 'ab12')
        );

        $this->assertEquals('{"adf2":"44","afc23":"ab12","method":"post"}', $result);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpCustomHeaders($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/AdditionalHeaders.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = '',
            $httpPassword = '',
            array(),
            array('CustomHeader: customdata')
        );

        $this->assertEquals('customdata', $result);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpsWorksWithValidCertificate($method)
    {
        $result = Http::sendHttpRequestBy($method, 'https://builds.matomo.org/LATEST', 10);

        $this->assertStringMatchesFormat('%d.%d.%d', $result);
    }

    /**
     * erroe message can be:
     *      curl_exec: server certificate verification failed. CAfile: /home/travis/build/piwik/piwik/core/DataFiles/cacert.pem CRLfile: none. Hostname requested was: self-signed.badssl.com
     * or
     *      curl_exec: SSL certificate problem: self signed certificate. Hostname requested was: self-signed.badssl.com
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /curl_exec: .*certificate.* /
     */
    public function testCurlHttpsFailsWithInvalidCertificate()
    {
        // use a domain from https://badssl.com/
        Http::sendHttpRequestBy('curl', 'https://self-signed.badssl.com/', 10);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage failed to open stream
     */
    public function testFopenHttpsFailsWithInvalidCertificate()
    {
        // use a domain from https://badssl.com/
        Http::sendHttpRequestBy('fopen', 'https://self-signed.badssl.com/', 10);
    }

    public function testSocketHttpsWorksWithValidCertificate()
    {
        $result = Http::sendHttpRequestBy('socket', 'https://piwik.org/', 10);
        $this->assertNotEmpty($result);
    }
}
