<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Http;

use Piwik\Common;
use Piwik\Config;
use Piwik\Http\SecurityHeaders;

/**
 * @group Core
 */
class SecurityHeadersTest extends \PHPUnit\Framework\TestCase
{
    private $generalConfig;

    public function setUp(): void
    {
        parent::setUp();

        Common::$headersSentInTests = [];

        $this->generalConfig =& Config::getInstance()->General;
        $this->generalConfig['csp_enabled'] = 1;
        $this->generalConfig['csp_report_only'] = 0;
        $this->generalConfig['trusted_hosts'] = [];
        $this->generalConfig['enable_framed_pages'] = 0;
    }

    public function tearDown(): void
    {
        Common::$headersSentInTests = [];
        $this->generalConfig['csp_enabled'] = 1;
        $this->generalConfig['csp_report_only'] = 0;
        $this->generalConfig['trusted_hosts'] = [];
        $this->generalConfig['enable_framed_pages'] = 0;

        parent::tearDown();
    }

    public function testSendForDataResponseSendsDataResponseHeaders()
    {
        SecurityHeaders::sendForDataResponse();

        $policy = trim(Common::$headersSentInTests['Content-Security-Policy']);

        $this->assertSame('nosniff', trim(Common::$headersSentInTests['X-Content-Type-Options']));
        $this->assertSame('deny', trim(Common::$headersSentInTests['X-Frame-Options']));
        $this->assertSame('no-referrer', trim(Common::$headersSentInTests['Referrer-Policy']));
        // no trusted hosts are configured here, so img-src carries nothing beyond its own defaults
        $this->assertStringStartsWith("default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:", $policy);
        $this->assertStringEndsWith("base-uri 'none'; form-action 'none'; frame-ancestors 'none';", $policy);
    }

    public function testSendForDataResponseEnforcesPolicyEvenInReportOnlyMode()
    {
        $this->generalConfig['csp_report_only'] = 1;

        SecurityHeaders::sendForDataResponse();

        $this->assertArrayHasKey('Content-Security-Policy', Common::$headersSentInTests);
        $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', Common::$headersSentInTests);
    }

    /**
     * @dataProvider getTrustedHosts
     */
    public function testSendForDataResponseAllowsImagesFromTheTrustedHosts(array $trustedHosts, string $expectedImgSrc)
    {
        $this->generalConfig['trusted_hosts'] = $trustedHosts;

        SecurityHeaders::sendForDataResponse();

        $this->assertStringContainsString(
            'img-src ' . $expectedImgSrc . ';',
            Common::$headersSentInTests['Content-Security-Policy']
        );
    }

    public function getTrustedHosts(): array
    {
        return [
            'none configured' => [[], "'self' data:"],
            'single host' => [['matomo.example.org'], "'self' data: matomo.example.org"],
            'several hosts' => [
                ['matomo.example.org', 'analytics.example.com'],
                "'self' data: matomo.example.org analytics.example.com",
            ],
            'host with a port' => [['example.org:8080'], "'self' data: example.org:8080"],
            // a host that could carry anything readable as a further directive is left out
            'injected directive' => [['evil.example.org; script-src *'], "'self' data:"],
            'wildcard' => [['*'], "'self' data:"],
            'partly usable' => [['*.example.org', 'matomo.example.org'], "'self' data: matomo.example.org"],
            'ipv6' => [['[2001:db8::1]'], "'self' data: [2001:db8::1]"],
            // a trailing line break would be dropped by the header sink, taking the policy with it
            'trailing line break' => [["matomo.example.org\n", 'analytics.example.com'], "'self' data: analytics.example.com"],
            // a url without a host is read as null, which must not cost the usable entries
            'entry without a host' => [['file:///path', 'matomo.example.org'], "'self' data: matomo.example.org"],
        ];
    }

    public function testSendForDataResponseSendsNoPolicyWhenCspIsDisabled()
    {
        $this->generalConfig['csp_enabled'] = 0;

        SecurityHeaders::sendForDataResponse();

        $this->assertArrayNotHasKey('Content-Security-Policy', Common::$headersSentInTests);
        $this->assertSame('nosniff', trim(Common::$headersSentInTests['X-Content-Type-Options']));
    }

    public function testSendForDataResponseSendsNoFrameRulesWhenEmbeddingIsEnabled()
    {
        $this->generalConfig['enable_framed_pages'] = 1;

        SecurityHeaders::sendForDataResponse();

        $this->assertArrayNotHasKey('X-Frame-Options', Common::$headersSentInTests);
        $this->assertStringNotContainsString(
            'frame-ancestors',
            trim(Common::$headersSentInTests['Content-Security-Policy'] ?? '')
        );
        // the rest of the set is unaffected
        $this->assertSame('nosniff', trim(Common::$headersSentInTests['X-Content-Type-Options'] ?? ''));
        $this->assertStringStartsWith(
            "default-src 'none';",
            trim(Common::$headersSentInTests['Content-Security-Policy'] ?? '')
        );
    }
}
