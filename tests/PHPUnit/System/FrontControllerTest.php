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
    protected static function configureFixture($fixture)
    {
        $fixture->createSuperUser = true;
    }

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

    public function testApiRequestForANonExistingWebsiteIsAClientError(): void
    {
        [$header, $body] = $this->getResponse(
            'index.php?module=API&method=SitesManager.getSiteFromId&idSite=9999&format=json&token_auth=' . Fixture::getTokenAuth()
        );

        self::assertMatchesRegularExpression('~^HTTP/[\d.]+ 400 ~', $header);
        self::assertStringContainsString('An unexpected website was found in the request', $body);
    }

    /**
     * @dataProvider invalidModuleOrActionUrlsProvider
     */
    public function testInvalidModuleOrActionIsAClientError(string $url): void
    {
        $header = $this->getResponseHeader($url);

        self::assertMatchesRegularExpression('~^HTTP/[\d.]+ 400 ~', $header);
    }

    public function invalidModuleOrActionUrlsProvider(): array
    {
        return [
            ['index.php?module=CoreHome/robots.txt&action=index'],
            ['index.php?module=CoreHome&action[%24eq]=index'],
        ];
    }

    private function getResponseHeader($url)
    {
        return $this->getResponse($url)[0];
    }

    /**
     * @return array{string, string} the response header and body
     */
    private function getResponse(string $url): array
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
        $body = substr($response, $headerSize);

        curl_close($ch);

        return [$header, $body];
    }
}
