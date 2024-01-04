<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class JsProxyTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2014-01-01 02:03:04');
    }

    public function testMatomoJs()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getStaticSrvUrl() . '/js/');
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $fullResponse = curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        $this->assertEquals(200, $responseInfo["http_code"], 'Ok response');

        $piwik_js = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . '/matomo.js');
        $this->assertEquals($piwik_js, $fullResponse, 'script content');
    }

    public function testPiwik_WhiteLabelledJs_HasNoComment()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getStaticSrvUrl() . '/js/tracker.php');
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $fullResponse = curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        $this->assertEquals(200, $responseInfo["http_code"], 'Ok response');

        $piwikJs = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . '/matomo.js');
        $piwikNoCommentJs = substr($piwikJs, strpos($piwikJs, "*/\n") + 3);
        $this->assertEquals($piwikNoCommentJs, trim($fullResponse), 'script content (if comment shows, $byteStart value in /js/tracker.php)');
    }

    public function testPiwikPhp()
    {
        $curlHandle = curl_init();
        $url = $this->getStaticSrvUrl() . '/js/?idsite=1';
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $fullResponse = curl_exec($curlHandle);
        $responseInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        $this->assertEquals(200, $responseInfo["http_code"], var_export($responseInfo, true) . $fullResponse);
        $expected = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        $processed = base64_encode($fullResponse);

        $this->assertEquals(
            $expected,
            $processed,
            'checking for image content' . "\n\n\n\nRaw content: \n\n\n" . $fullResponse
        );
    }

    /**
     * Helper methods
     */
    private function getStaticSrvUrl()
    {
        return Fixture::getRootUrl();
    }
}
