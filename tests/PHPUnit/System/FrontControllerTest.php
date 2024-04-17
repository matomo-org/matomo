<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 */
class FrontControllerTest extends SystemTestCase
{
    /**
     * @dataProvider malformedUrlsProvider
     */
    public function testMalformedUrlRedirection($url, $redirection)
    {
        $header = $this->getResponseHeader($url);

        if ($redirection) {
            self::assertStringContainsString('Location: ' . Fixture::getRootUrl() . 'tests/PHPUnit/proxy/' . $redirection . "\r\n", $header);
        } else {
            self::assertStringNotContainsString('Location: ', $header);
        }
    }

    public function malformedUrlsProvider()
    {
        return array(
            // Correct url
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
        curl_setopt($ch, CURLOPT_URL, Fixture::getRootUrl() . 'tests/PHPUnit/proxy/' . $url);
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
