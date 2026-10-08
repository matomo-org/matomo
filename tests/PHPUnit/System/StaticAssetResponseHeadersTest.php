<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Container\StaticContainer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 * @group StaticAssetResponseHeadersTest
 */
class StaticAssetResponseHeadersTest extends SystemTestCase
{
    private string $cookieFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->cookieFile = (string) tempnam(StaticContainer::get('path.tmp'), 'testStaticAssetResponseHeaders');
    }

    public function tearDown(): void
    {
        @unlink($this->cookieFile);
        parent::tearDown();
    }

    /**
     * @dataProvider getAssetRequests
     */
    public function testServedAssetResponseDoesNotSetCookiesForAnonymousUser(array $params, string $expectedCacheControl)
    {
        $headers = $this->requestHeaders($params);

        $this->assertAssetResponseHasNoCookies($headers, $expectedCacheControl);
    }

    /**
     * @dataProvider getAssetRequests
     */
    public function testServedAssetResponseDoesNotSetCookiesForLoggedInUser(array $params, string $expectedCacheControl)
    {
        $this->logIn();

        $pageHeaders = $this->requestHeaders(['module' => 'CoreHome', 'action' => 'index', 'idSite' => 1, 'period' => 'day', 'date' => 'yesterday']);
        $this->assertMatchesRegularExpression('/^Set-Cookie: MATOMO_SESSID=.*expires=/mi', $pageHeaders);

        $headers = $this->requestHeaders($params);

        $this->assertAssetResponseHasNoCookies($headers, $expectedCacheControl);
    }

    public function getAssetRequests(): iterable
    {
        yield 'merged stylesheet' => [['module' => 'Proxy', 'action' => 'getCss'], 'public'];
        yield 'merged core javascript' => [['module' => 'Proxy', 'action' => 'getCoreJs'], 'public'];
        yield 'merged non core javascript' => [['module' => 'Proxy', 'action' => 'getNonCoreJs'], 'public'];
        yield 'updater stylesheet' => [['module' => 'CoreUpdater', 'action' => 'getUpdaterCss'], 'max-age='];
        yield 'updater javascript' => [['module' => 'CoreUpdater', 'action' => 'getUpdaterJs'], 'max-age='];
        yield 'installation stylesheet' => [['module' => 'Installation', 'action' => 'getInstallationCss'], 'max-age='];
        yield 'installation javascript' => [['module' => 'Installation', 'action' => 'getInstallationJs'], 'max-age='];
    }

    private function assertAssetResponseHasNoCookies(string $headers, string $expectedCacheControl): void
    {
        $this->assertMatchesRegularExpression('/^HTTP\/[\d.]+ 200/', $headers);
        $this->assertMatchesRegularExpression('/^Cache-Control: ' . preg_quote($expectedCacheControl, '/') . '/mi', $headers);
        $this->assertDoesNotMatchRegularExpression('/^Set-Cookie:/mi', $headers);
    }

    private function logIn(): void
    {
        $loginForm = $this->request(['module' => 'Login'], false);
        preg_match('/id="login_form_nonce" value="([a-z0-9]+)"/i', $loginForm, $matches);
        $this->assertNotEmpty($matches[1] ?? '', 'Login form nonce not found');

        $this->request(['module' => 'Login'], false, [
            'form_login' => Fixture::ADMIN_USER_LOGIN,
            'form_password' => Fixture::ADMIN_USER_PASSWORD,
            'form_nonce' => $matches[1],
        ]);
    }

    private function requestHeaders(array $params): string
    {
        return $this->request($params, true);
    }

    private function request(array $params, bool $headersOnly, ?array $postData = null): string
    {
        $ch = curl_init(Fixture::getTestRootUrl() . 'index.php?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);

        if ($headersOnly) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        if ($postData !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        $response = (string) curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        return $headersOnly ? substr($response, 0, $headerSize) : $response;
    }
}
