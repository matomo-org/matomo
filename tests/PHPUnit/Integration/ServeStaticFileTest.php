<?php

/**
 * This php file is used to unit test Piwik::serverStaticFile()
 * Unit tests for this method should normally be located in /tests/core/Piwik.test.php
 * To make a comprehensive test suit for Piwik::serverStaticFile() (ie. being able to test combinations of request
 * headers, being able to test response headers and so on) we need to simulate static file requests in a controlled
 * environment
 * The php code which simulates requests using Piwik::serverStaticFile() is provided in the same file (ie. this one)
 * as the unit testing code for Piwik::serverStaticFile()
 * This decision has a structural impact on the usual unit test file structure
 * serverStaticFile.test.php has been created to avoid making too many modifications to /tests/core/Piwik.test.php
 */

namespace Piwik\Tests\Integration;

// This is Piwik logo, the static file used in this test suit

use Exception;
use Piwik\ProxyHttp;
use Piwik\SettingsServer;
use Piwik\Tests\Framework\Fixture;

define("TEST_FILE_LOCATION", realpath(dirname(__FILE__) . "/../../resources/lipsum.txt"));
define("TEST_FILE_CONTENT_TYPE", "text/plain");

// Defines http request parameters
define("FILE_MODE_REQUEST_VAR", "fileMode");
define("SRV_MODE_REQUEST_VAR", "serverMode");
define("ZLIB_OUTPUT_REQUEST_VAR", "zlibOutput");

/**
 * These constants define the mode in which this php file is used :
 * - for unit testing Piwik::serverStaticFile() or
 * - as a static file server
 */
define("STATIC_SERVER_MODE", "staticServerMode");
define("UNIT_TEST_MODE", "unitTestMode");

// These constants define which action will be performed by the static server.
define("NULL_FILE_SRV_MODE", "nullFile");
define("GHOST_FILE_SRV_MODE", "ghostFile");
define("TEST_FILE_SRV_MODE", "testFile");
define("PARTIAL_TEST_FILE_SRV_MODE", "partialTestFile");
define("WHOLE_TEST_FILE_WITH_RANGE_SRV_MODE", "wholeTestFileWithRange");

define("PARTIAL_BYTE_START", 1204);
define("PARTIAL_BYTE_END", 14724);

// If the static file server has not been requested, the standard unit test case class is defined
class ServeStaticFileTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        if (!chmod(TEST_FILE_LOCATION, 0644)) {
            throw new Exception("Could not chmod 0644 " . TEST_FILE_LOCATION);
        }
    }

    /**
     * Test that php compression isn't enabled ... otherwise, lots of tests will fail
     *
     * @group Core
     */
    public function testPhpOutputCompression()
    {
        $this->assertFalse(ProxyHttp::isPhpOutputCompressed());
    }

    /**
     * Checks that "HTTP/1.0 404 Not Found" is returned when Piwik::serverStaticFile is called with a null file
     *
     * @group Core
     */
    public function testNullFile()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getNullFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        $this->assertEquals($responseInfo["http_code"], 404);
    }

    /**
     * Checks that "HTTP/1.0 404 Not Found" is returned when Piwik::serverStaticFile is called with a non existing file
     *
     *
     * @group Core
     */
    public function testGhostFile()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getGhostFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        $this->assertEquals($responseInfo["http_code"], 404);
    }

    /**
     * Checks that "HTTP/1.0 505 Internal server error" is returned when Piwik::serverStaticFile is called with a
     * non-readable file
     *
     * @group Core
     */
    public function testNonReadableFile()
    {
        /**
         * This test would fail on a windows environment because it is not possible to remove reading rights on a
         * windows file using PHP.
         */
        if (SettingsServer::isWindows()) {
            return;
        }

        // Setting mode so the testing file is non-readable
        chmod(TEST_FILE_LOCATION, 0200);

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url = $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        // Restoring file mode
        chmod(TEST_FILE_LOCATION, 0644);

        $this->assertEquals($responseInfo["http_code"], 500);
    }

    /**
     * Context :
     *  - First access to test file
     *  - zlib.output_compression = 0
     *  - no compression
     * Expected :
     *  - file is send back without compression
     *  - cache control headers are correctly set
     *
     * @group Core
     */
    public function testFirstAccessNoCompression()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_HEADER, true);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $fullResponse = curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        // Tests returned code equals 200
        $this->assertEquals(200, $responseInfo["http_code"]);

        // Tests content type
        self::assertStringContainsString(TEST_FILE_CONTENT_TYPE, $responseInfo["content_type"]);

        // Tests no compression has been applied
        $this->assertNull($this->getContentEncodingValue($fullResponse));

        // Tests returned size
        $this->assertEquals(filesize(TEST_FILE_LOCATION), $responseInfo["size_download"]);

        // Tests if returned modified date is correctly set
        $this->assertEquals(
            gmdate('D, d M Y H:i:s', filemtime(TEST_FILE_LOCATION)) . ' GMT',
            $this->getLastModifiedValue($fullResponse)
        );

        // Tests if cache control headers are correctly set
        $this->assertEquals("public, must-revalidate", $this->getCacheControlValue($fullResponse));
        $pragma = $this->getPragma($fullResponse);
        $this->assertTrue($pragma == null || $pragma == 'Pragma:');
        $expires = $this->getExpires($fullResponse);
        $this->assertTrue(strtotime($expires) > time() + 99 * 86400);
    }

    /**
     * Context :
     *  - Second access to test file
     *  - If-Modified-Since set to test file modification date
     * Expected :
     *  - "HTTP/1.1 304 Not Modified" sent back to client
     *
     * @group Core
     */
    public function testSecondAccessNoCompression()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_TIMECONDITION, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEVALUE, filemtime(TEST_FILE_LOCATION));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        $this->assertEquals($responseInfo["http_code"], 304);
    }

    /**
     * Context :
     *  - Second access to test file
     *  - If-Modified-Since set to test file modification date minus 1 second
     * Expected :
     *  - http return code 200 sent back to client
     *
     * @group Core
     */
    public function testSecondAccessNoCompressionExpiredFile()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_TIMECONDITION, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEVALUE, filemtime(TEST_FILE_LOCATION) - 1);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        $this->assertEquals($responseInfo["http_code"], 200);
    }

    /**
     * Context :
     *  - First access to file
     *  - zlib output compression is on
     * Expected :
     *  - the response has to be readable, it tests the proxy doesn't compress the file when compression
     *      is enabled in php.
     *
     * @group Core
     */
    public function testResponseReadableWithPhpCompression()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->setZlibOutputRequest(($this->getTestFileSrvModeUrl())));

        // The "" parameter sets all compatible content encodings
        curl_setopt($curlHandle, CURLOPT_ENCODING, "");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $fullResponse = curl_exec($curlHandle);
        curl_close($curlHandle);

        // Tests response content, it has to be equal to the test file. If not equal, double compression occurred
        $this->assertEquals($fullResponse, file_get_contents(TEST_FILE_LOCATION));
    }

    /**
     * Context :
     *  - First access to file
     *  - Content-Encoding: deflate
     * Expected :
     *  - the response has to be readable
     *  - the compression method used must be gzdeflate and not gzcompression to be IE compatible
     *
     * @group Core
     */
    public function testDeflateCompression()
    {
        $this->removeCompressedFiles();

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $fullResponse = curl_exec($curlHandle);
        curl_close($curlHandle);

        // Tests response content, it has to be equal to the test file
        $this->assertEquals($fullResponse, file_get_contents(TEST_FILE_LOCATION));

        // Tests deflate compression has been used
        $deflateFileLocation = $this->getCompressedFileLocation() . ".deflate";
        $this->assertFileExists($deflateFileLocation);

        // Tests gzdeflate has been used for IE compatibility
        $this->assertEquals(gzinflate(file_get_contents($deflateFileLocation)), file_get_contents(TEST_FILE_LOCATION));

        $this->removeCompressedFiles();
    }

    /**
     * Context :
     *  - First access to file
     *  - Content-Encoding: gzip
     * Expected :
     *  - the response has to be readable
     *  - the compression method used is gzip
     *
     * @group Core
     */
    public function testGzipCompression()
    {
        $this->removeCompressedFiles();

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_ENCODING, "gzip");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $fullResponse = curl_exec($curlHandle);
        curl_close($curlHandle);

        // Tests response content, it has to be equal to the test file
        $this->assertEquals($fullResponse, file_get_contents(TEST_FILE_LOCATION));

        // Tests gzip compression has been used
        $gzipFileLocation = $this->getCompressedFileLocation() . ".gz";
        $this->assertFileExists($gzipFileLocation);

        $this->removeCompressedFiles();
    }

    /**
     * Context :
     *  - First access to file
     *  - Content-Encoding: deflate
     *  - Repeat twice
     * Expected :
     *  - the compressed file cache mechanism works file, ie. the .deflate file is not generated twice
     *
     * @group Core
     */
    public function testCompressionCache()
    {
        $this->removeCompressedFiles();
        $deflateFileLocation = $this->getCompressedFileLocation() . ".deflate";

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        curl_close($curlHandle);

        $firstAccessModificationTime = filemtime($deflateFileLocation);

        // Requests made to the static file have to be executed at different times for the test to be valid.
        sleep(1);

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        curl_close($curlHandle);

        // Tests the .deflate file has not been generated twice
        clearstatcache();
        $this->assertEquals($firstAccessModificationTime, filemtime($deflateFileLocation));

        $this->removeCompressedFiles();
    }

    /**
     * Context :
     *  - First access to file
     *  - Content-Encoding: deflate
     *  - Repeat twice, in between: update the modification date of the test file
     * Expected :
     *  - the test file has been updated, the cached compressed file should be regenerated
     *
     * @group Core
     */
    public function testCompressionCacheInvalidation()
    {
        $this->removeCompressedFiles();
        $deflateFileLocation = $this->getCompressedFileLocation() . ".deflate";

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        curl_close($curlHandle);

        $firstAccessModificationTime = filemtime($deflateFileLocation);

        // Requests made to the static file have to be executed at different times for the test to be valid.
        sleep(1);

        // We update the test file modification date
        touch(TEST_FILE_LOCATION);

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlHandle);
        curl_close($curlHandle);

        clearstatcache();
        $this->assertNotEquals($firstAccessModificationTime, filemtime($deflateFileLocation));

        $this->removeCompressedFiles();
    }

    /**
     * @group Core
     */
    public function testPartialFileServeNoCompression()
    {
        $this->removeCompressedFiles();

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getPartialTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $partialResponse = curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        clearstatcache();

        // check no compressed files created
        $this->assertFalse(file_exists($this->getCompressedFileLocation() . ".deflate"));
        $this->assertFalse(file_exists($this->getCompressedFileLocation() . ".gz"));

        // check $partialResponse
        $this->assertEquals(PARTIAL_BYTE_END - PARTIAL_BYTE_START, $responseInfo["size_download"]);

        $expectedPartialContents = substr(
            file_get_contents(TEST_FILE_LOCATION),
            PARTIAL_BYTE_START,
            PARTIAL_BYTE_END - PARTIAL_BYTE_START
        );
        $this->assertEquals($expectedPartialContents, $partialResponse);
    }

    /**
     * @group Core
     * @group TestToExec
     */
    public function testPartialFileServeWithCompression()
    {
        $this->removeCompressedFiles();

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getPartialTestFileSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
        $partialResponse = curl_exec($curlHandle);
        curl_getinfo($curlHandle);
        curl_close($curlHandle);

        clearstatcache();

        // check the correct compressed file is created
        $this->assertFileExists($this->getCompressedFileLocation() . '.' . PARTIAL_BYTE_START . '.' . PARTIAL_BYTE_END . ".deflate");
        $this->assertFileNotExists($this->getCompressedFileLocation() . ".gz");

        // check $partialResponse
        $expectedPartialContents = substr(
            file_get_contents(TEST_FILE_LOCATION),
            PARTIAL_BYTE_START,
            PARTIAL_BYTE_END - PARTIAL_BYTE_START
        );
        $this->assertEquals($expectedPartialContents, $partialResponse);

        $this->removeCompressedFiles();
    }

    /**
     * @group Core
     */
    public function testWholeFileServeWithByteRange()
    {
        $this->removeCompressedFiles();

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getWholeTestFileWithRangeSrvModeUrl());
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
        $fullResponse = curl_exec($curlHandle);
        curl_getinfo($curlHandle);
        curl_close($curlHandle);

        clearstatcache();

        // check the correct compressed file is created
        $this->assertFileExists($this->getCompressedFileLocation() . ".deflate");
        $this->assertFileNotExists($this->getCompressedFileLocation() . ".gz");

        // check $fullResponse
        $this->assertEquals(file_get_contents(TEST_FILE_LOCATION), $fullResponse);

        $this->removeCompressedFiles();
    }

    /**
     * Helper methods
     */
    private function getStaticSrvUrl()
    {
        $url = Fixture::getRootUrl();
        $url .= '/tests/resources/';

        return $url . "staticFileServer.php?" . FILE_MODE_REQUEST_VAR . "=" . STATIC_SERVER_MODE .
            "&" . SRV_MODE_REQUEST_VAR . "=";
    }

    private function getNullFileSrvModeUrl()
    {
        return $this->getStaticSrvUrl() . NULL_FILE_SRV_MODE;
    }

    private function getGhostFileSrvModeUrl()
    {
        return $this->getStaticSrvUrl() . GHOST_FILE_SRV_MODE;
    }

    private function getTestFileSrvModeUrl()
    {
        return $this->getStaticSrvUrl() . TEST_FILE_SRV_MODE;
    }

    private function getPartialTestFileSrvModeUrl()
    {
        return $this->getStaticSrvUrl() . PARTIAL_TEST_FILE_SRV_MODE;
    }

    private function getWholeTestFileWithRangeSrvModeUrl()
    {
        return $this->getStaticSrvUrl() . WHOLE_TEST_FILE_WITH_RANGE_SRV_MODE;
    }

    private function setZlibOutputRequest($url)
    {
        return $url . "&" . ZLIB_OUTPUT_REQUEST_VAR . "=1";
    }

    private function getContentEncodingValue($fullResponse)
    {
        preg_match_all('/Content-Encoding:[\s*]([[:print:]]*)/', $fullResponse, $matches);

        if (isset($matches[1][0])) {
            return $matches[1][0];
        }

        return null;
    }

    private function getCacheControlValue($fullResponse)
    {
        preg_match_all('/Cache-Control:[\s*]([[:print:]]*)/', $fullResponse, $matches);

        if (isset($matches[1][0])) {
            return $matches[1][0];
        }

        return null;
    }

    private function getPragma($fullResponse)
    {
        preg_match_all('/(Pragma:[[:print:]]*)/', $fullResponse, $matches);

        if (isset($matches[1][0])) {
            return trim($matches[1][0]);
        }

        return null;
    }

    private function getExpires($fullResponse)
    {
        preg_match_all('/Expires: ([[:print:]]*)/', $fullResponse, $matches);

        if (isset($matches[1][0])) {
            return trim($matches[1][0]);
        }

        return null;
    }

    private function getLastModifiedValue($fullResponse)
    {
        preg_match_all('/Last-Modified:[\s*]([[:print:]]*)/', $fullResponse, $matches);

        if (isset($matches[1][0])) {
            return $matches[1][0];
        }

        return null;
    }

    private function getCompressedFileLocation()
    {
        return \Piwik\AssetManager::getInstance()->getAssetDirectory() . '/' . basename(TEST_FILE_LOCATION);
    }

    private function removeCompressedFiles()
    {
        @unlink($this->getCompressedFileLocation() . ".deflate");
        @unlink($this->getCompressedFileLocation() . ".gz");
    }
}
