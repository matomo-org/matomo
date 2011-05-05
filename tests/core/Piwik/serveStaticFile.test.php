<?php
/**
 * This php file is used to unit test Piwik::serveStaticFile()
 *
 * Unit tests for this method should normally be located in /tests/core/Piwik.test.php
 * To make a comprehensive test suit for Piwik::serveStaticFile() (ie. being able to test combinations of request
 * headers, being able to test response headers and so on) we need to simulate static file requests in a controlled
 * environment
 * The php code which simulates requests using Piwik::serveStaticFile() is provided in the same file (ie. this one)
 * as the unit testing code for Piwik::serveStaticFile()
 *
 * This decision has a structural impact on the usual unit test file structure
 * serveStaticFile.test.php has been created to avoid making too many modifications to /tests/core/Piwik.test.php
 */

if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', realpath(dirname(__FILE__) . '/../../..'));
}
require_once PIWIK_PATH_TEST_TO_ROOT . '/libs/upgradephp/upgrade.php';

// This is Piwik logo, the static file used in this test suit
define("TEST_FILE_LOCATION", PIWIK_PATH_TEST_TO_ROOT . "/tests/core/Piwik/lipsum.txt");
define("TEST_FILE_CONTENT_TYPE", "text/plain");

// Defines http request parameters
define("FILE_MODE_REQUEST_VAR", "fileMode");
define("SRV_MODE_REQUEST_VAR", "serverMode");
define("ZLIB_OUTPUT_REQUEST_VAR", "zlibOutput");

/**
 * These constants define the mode in which this php file is used :
 * - for unit testing Piwik::serveStaticFile() or
 * - as a static file server
 */
define("STATIC_SERVER_MODE", "staticServerMode");
define("UNIT_TEST_MODE", "unitTestMode");

// These constants define which action will be performed by the static server.
define("NULL_FILE_SRV_MODE", "nullFile");
define("GHOST_FILE_SRV_MODE", "ghostFile");
define("TEST_FILE_SRV_MODE", "testFile");

// Getting the file mode from the request
require_once PIWIK_PATH_TEST_TO_ROOT . "/core/Common.php";
$staticFileServerMode = Piwik_Common::getRequestVar(FILE_MODE_REQUEST_VAR, UNIT_TEST_MODE);

/**
 * If the static file server has been requested, the response sent back to the browser will be the content produced by
 * the execution of Piwik:serveStaticFile(). In this case, unit tests won't be executed
 */
if ( $staticFileServerMode === STATIC_SERVER_MODE )
{
	// Getting the server mode
	$staticFileServerMode = Piwik_Common::getRequestVar(SRV_MODE_REQUEST_VAR, "");

	// Setting zlib output compression as requested
	ini_set('zlib.output_compression', Piwik_Common::getRequestVar(ZLIB_OUTPUT_REQUEST_VAR, '0'));

	if ($staticFileServerMode === "")
	{
		throw new Exception("When this testing file is used as a static file server, the request parameter " .
				SRV_MODE_REQUEST_VAR . " must be provided.");
	}

	define("PIWIK_DOCUMENT_ROOT", PIWIK_PATH_TEST_TO_ROOT);
	define("PIWIK_INCLUDE_PATH", PIWIK_PATH_TEST_TO_ROOT);
	define("PIWIK_USER_PATH", PIWIK_PATH_TEST_TO_ROOT);
	require_once PIWIK_PATH_TEST_TO_ROOT . '/core/Piwik.php';

	switch ($staticFileServerMode)
	{
		// The static file server calls Piwik::serveStaticFile with a null file
		case NULL_FILE_SRV_MODE:

			Piwik::serveStaticFile(null, TEST_FILE_CONTENT_TYPE);
			break;

		// The static file server calls Piwik::serveStaticFile with a non-existing file
		case GHOST_FILE_SRV_MODE:

			Piwik::serveStaticFile(TEST_FILE_LOCATION . ".ghost", TEST_FILE_CONTENT_TYPE);
			break;

		// The static file server calls Piwik::serveStaticFile with the test file
		case TEST_FILE_SRV_MODE:

			Piwik::serveStaticFile(TEST_FILE_LOCATION, TEST_FILE_CONTENT_TYPE);
			break;
	}

	// Stops the execution of the whole file
	exit;
}

if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

// If the static file server has not been requested, the standard unit test case class is defined
class Test_Piwik_serveStaticFile extends UnitTestCase
{
	public function tearDown()
	{
		parent::tearDown();
		chmod(TEST_FILE_LOCATION, 0644);
	}

	/**
	 * Test that php compression isn't enabled ... otherwise, lots of tests will fail
	 */
	public function test_phpOutputCompression()
	{
		$this->assertFalse(Piwik::isPhpOutputCompressed());
	}

	/**
	 * Checks that "HTTP/1.0 404 Not Found" is returned when Piwik::serveStaticFile is called with a null file
	 */
	public function test_nullFile()
	{
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getNullFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_exec($curlHandle);
		$responseInfo = curl_getinfo($curlHandle);
		curl_close($curlHandle);

		$this->assertEqual($responseInfo["http_code"], 404);
	}

	/**
	 * Checks that "HTTP/1.0 404 Not Found" is returned when Piwik::serveStaticFile is called with a non existing file
	 */
	public function test_ghostFile()
	{
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getGhostFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_exec($curlHandle);
		$responseInfo = curl_getinfo($curlHandle);
		curl_close($curlHandle);

		$this->assertEqual($responseInfo["http_code"], 404);
	}

	/**
	 * Checks that "HTTP/1.0 505 Internal server error" is returned when Piwik::serveStaticFile is called with a
	 * non-readable file
	 */
	public function test_nonReadableFile()
	{
		/**
		 * This test would fail on a windows environment because it is not possible to remove reading rights on a
		 * windows file using PHP.
		 */
		if(Piwik_Common::isWindows())
		{
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

		$this->assertEqual($responseInfo["http_code"], 505);
	}

	/**
	 * Context :
	 *  - First access to test file
	 *  - zlib.output_compression = 0
	 *  - no compression
	 * 
	 * Expected :
	 *  - file is send back without compression
	 *  - cache control headers are correctly set
	 */
	public function test_firstAccessNoCompression()
	{
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_HEADER, true);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
		$responseInfo = curl_getinfo($curlHandle);
		curl_close($curlHandle);

		// Tests returned code equals 200
		$this->assertEqual($responseInfo["http_code"], 200);

		// Tests content type
		$this->assertEqual($responseInfo["content_type"], TEST_FILE_CONTENT_TYPE);

		// Tests no compression has been applied
		$this->assertNull($this->getContentEncodingValue($fullResponse));

		// Tests returned size
		$this->assertEqual($responseInfo["size_download"], filesize(TEST_FILE_LOCATION));

		// Tests if returned modified date is correctly set
		$this->assertEqual ($this->getLastModifiedValue($fullResponse),
							gmdate('D, d M Y H:i:s', filemtime(TEST_FILE_LOCATION)) . ' GMT');

		// Tests if cache control headers are correctly set
		$this->assertEqual($this->getCacheControlValue($fullResponse), "public, must-revalidate");
		$pragma = $this->getPragma($fullResponse);
		$this->assertTrue($pragma == null || $pragma == 'Pragma:');
		$expires = $this->getExpires($fullResponse);
		$this->assertTrue(strtotime($expires) > time() + 99*86400);
	}

	/**
	 * Context :
	 *  - Second access to test file
	 *  - If-Modified-Since set to test file modification date
	 *
	 * Expected :
	 *  - "HTTP/1.1 304 Not Modified" sent back to client
	 */
	public function test_secondAccessNoCompression()
	{
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_TIMECONDITION, 1);
		curl_setopt($curlHandle, CURLOPT_TIMEVALUE, filemtime(TEST_FILE_LOCATION));
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_exec($curlHandle);
		$responseInfo = curl_getinfo($curlHandle);
		curl_close($curlHandle);

		$this->assertEqual($responseInfo["http_code"], 304);
	}

	/**
	 * Context :
	 *  - Second access to test file
	 *  - If-Modified-Since set to test file modification date minus 1 second
	 *
	 * Expected :
	 *  - http return code 200 sent back to client
	 */
	public function test_secondAccessNoCompressionExpiredFile()
	{
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_TIMECONDITION, 1);
		curl_setopt($curlHandle, CURLOPT_TIMEVALUE, filemtime(TEST_FILE_LOCATION) - 1);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_exec($curlHandle);
		$responseInfo = curl_getinfo($curlHandle);
		curl_close($curlHandle);

		$this->assertEqual($responseInfo["http_code"], 200);
	}

	/**
	 * Context :
	 *  - First access to file
	 *  - zlib output compression is on
	 *
	 * Expected :
	 *  - the response has to be readable, it tests the proxy doesn't compress the file when compression
	 *	  is enabled in php.
	 */
	public function test_responseReadableWithPhpCompression()
	{
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->setZlibOutputRequest(($this->getTestFileSrvModeUrl())));

		// The "" parameter sets all compatible content encodings
		curl_setopt($curlHandle, CURLOPT_ENCODING, "");
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
		curl_close($curlHandle);

		// Tests response content, it has to be equal to the test file. If not equal, double compression occurred
		$this->assertEqual($fullResponse, file_get_contents(TEST_FILE_LOCATION));
	}

	/**
	 * Context :
	 *  - First access to file
	 *  - Content-Encoding: deflate
	 *
	 * Expected :
	 *  - the response has to be readable
	 *  - the compression method used must be gzdeflate and not gzcompression to be IE compatible
	 */
	public function test_deflateCompression()
	{
		$this->removeCompressedFiles();

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
		curl_close($curlHandle);

		// Tests response content, it has to be equal to the test file
		$this->assertEqual($fullResponse, file_get_contents(TEST_FILE_LOCATION));

		// Tests deflate compression has been used
		$deflateFileLocation = $this->getCompressedFileLocation() . ".deflate";
		$this->assertTrue(file_exists($deflateFileLocation));

		// Tests gzdeflate has been used for IE compatibility
		$this->assertEqual(gzinflate(file_get_contents($deflateFileLocation)), file_get_contents(TEST_FILE_LOCATION));

		$this->removeCompressedFiles();
	}

	/**
	 * Context :
	 *  - First access to file
	 *  - Content-Encoding: gzip
	 *
	 * Expected :
	 *  - the response has to be readable
	 *  - the compression method used is gzip
	 */
	public function test_gzipCompression()
	{
		$this->removeCompressedFiles();

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_ENCODING, "gzip");
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
		curl_close($curlHandle);

		// Tests response content, it has to be equal to the test file
		$this->assertEqual($fullResponse, file_get_contents(TEST_FILE_LOCATION));

		// Tests gzip compression has been used
		$gzipFileLocation = $this->getCompressedFileLocation() . ".gz";
		$this->assertTrue(file_exists($gzipFileLocation));

		$this->removeCompressedFiles();
	}

	/**
	 * Context :
	 *  - First access to file
	 *  - Content-Encoding: deflate
	 *  - Repeat twice
	 *
	 * Expected :
	 *  - the compressed file cache mechanism works file, ie. the .deflate file is not generated twice
	 */
	public function test_compressionCache()
	{
		$this->removeCompressedFiles();
		$deflateFileLocation = $this->getCompressedFileLocation() . ".deflate";

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
		curl_close($curlHandle);

		$firstAccessModificationTime = filemtime($deflateFileLocation);

		// Requests made to the static file have to be executed at different times for the test to be valid.
		sleep(1);

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
		curl_close($curlHandle);

		// Tests the .deflate file has not been generated twice
		clearstatcache();
		$this->assertEqual($firstAccessModificationTime, filemtime($deflateFileLocation));

		$this->removeCompressedFiles();
	}

	/**
	 * Context :
	 *  - First access to file
	 *  - Content-Encoding: deflate
	 *  - Repeat twice, in between: update the modification date of the test file
	 *
	 * Expected :
	 *  - the test file has been updated, the cached compressed file should be regenerated
	 *
	 */
	public function test_compressionCacheInvalidation()
	{
		$this->removeCompressedFiles();
		$deflateFileLocation = $this->getCompressedFileLocation() . ".deflate";

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getTestFileSrvModeUrl());
		curl_setopt($curlHandle, CURLOPT_ENCODING, "deflate");
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
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
		$fullResponse = curl_exec($curlHandle);
		curl_close($curlHandle);

		clearstatcache();
		$this->assertNotEqual($firstAccessModificationTime, filemtime($deflateFileLocation));

		$this->removeCompressedFiles();
	}
	
	/**
	 * Helper methods
	 */
	private function getStaticSrvUrl()
	{
		$path = Piwik_Url::getCurrentScriptPath();
		if(substr($path, -7) == '/tests/')
		{
			$path .= 'core/Piwik/';
		}
		else if(substr($path, -18) != '/tests/core/Piwik/')
		{
			throw new Exception('unsupported test path: ' . $path);
		}

		return
			"http://" . $_SERVER['HTTP_HOST'] .
			$path . "serveStaticFile.test.php?" . FILE_MODE_REQUEST_VAR . "=" . STATIC_SERVER_MODE .
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

	private function setZlibOutputRequest($url)
	{
		return $url . "&" . ZLIB_OUTPUT_REQUEST_VAR . "=1";
	}

	private function getContentEncodingValue($fullResponse)
	{
		preg_match_all('/Content-Encoding:[\s*]([[:print:]]*)/', $fullResponse, $matches);
		
		if (isset($matches[1][0]))
		{
			return $matches[1][0];
		}
		
		return null;
	}

	private function getCacheControlValue($fullResponse)
	{
		preg_match_all('/Cache-Control:[\s*]([[:print:]]*)/', $fullResponse, $matches);

		if (isset($matches[1][0]))
		{
			return $matches[1][0];
		}

		return null;
	}

	private function getPragma($fullResponse)
	{
		preg_match_all('/(Pragma:[[:print:]]*)/', $fullResponse, $matches);

		if (isset($matches[1][0]))
		{
			return trim($matches[1][0]);
		}

		return null;
	}

	private function getExpires($fullResponse)
	{
		preg_match_all('/Expires: ([[:print:]]*)/', $fullResponse, $matches);

		if (isset($matches[1][0]))
		{
			return trim($matches[1][0]);
		}

		return null;
	}

	private function getLastModifiedValue($fullResponse)
	{
		preg_match_all('/Last-Modified:[\s*]([[:print:]]*)/', $fullResponse, $matches);

		if (isset($matches[1][0]))
		{
			return $matches[1][0];
		}

		return null;
	}

	private function getCompressedFileLocation()
	{
		return PIWIK_PATH_TEST_TO_ROOT . Piwik::COMPRESSED_FILE_LOCATION . basename(TEST_FILE_LOCATION);
	}

	private function removeCompressedFiles()
	{
		@unlink ( $this->getCompressedFileLocation() . ".deflate");
		@unlink ( $this->getCompressedFileLocation() . ".gz");
	}
}
