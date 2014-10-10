<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Fixture;

/**
 * @group System
 */
class FrontControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider malformedUrlsProvider
     */
    public function testMalformedUrlRedirection($url, $redirection)
    {
        $header = $this->getResponseHeader($url);

        if ($redirection) {
            $this->assertContains('Location: http://localhost:8000/' . $redirection, $header);
        } else {
            $this->assertNotContains('Location: ', $header);
        }
    }

    public function malformedUrlsProvider()
    {
        return array(
            // Correct urls
            array('', false),
            array('index.php', false),
            array('index.php?module=CoreHome&action=index&idSite=1&period=day&date=yesterday', false),
            // These urls may cause XSS vulnerabilities in old browsers
            array('index.php/.html', 'index.php'),
            array(
                'index.php/.html?module=CoreHome&action=index&idSite=1&period=day&date=yesterday',
                'index.php?module=CoreHome&action=index&idSite=1&period=day&date=yesterday',
            ),
            array(
                'index.php/.html/.html?module=CoreHome&action=index&idSite=1&period=day&date=yesterday',
                'index.php?module=CoreHome&action=index&idSite=1&period=day&date=yesterday',
            ),
        );
    }

    private function getResponseHeader($url)
    {
        if (! function_exists('curl_init')) {
            $this->markTestSkipped('Curl is not installed');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Fixture::getRootUrl() . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);

        curl_close($ch);

        return $header;
    }
}
