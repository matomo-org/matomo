<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\ReportRenderer;
use Piwik\ReportRenderer\Html;
use Piwik\ReportRenderer\Pdf;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use ReflectionMethod;

/**
 * A report streamed to the browser must not be treated as application UI.
 *
 * @group Core
 * @group ReportRenderer
 */
class ReportRendererSecurityHeadersTest extends IntegrationTestCase
{
    private const EXPECTED_POLICY_PREFIX = "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:";

    public function setUp(): void
    {
        parent::setUp();

        Common::$headersSentInTests = [];
    }

    public function tearDown(): void
    {
        Common::$headersSentInTests = [];

        parent::tearDown();
    }

    public function testInlineToBrowserSendsDataResponseHeaders()
    {
        $this->expectOutputString('report body');

        $this->invokeReportRendererStatic('inlineToBrowser', ['text/html', 'report body']);

        $this->assertDataResponseHeadersWereSent();
        $this->assertSame('text/html', trim(Common::$headersSentInTests['Content-Type']));
    }

    public function testSendToBrowserSendsDataResponseHeaders()
    {
        $this->expectOutputString('report body');

        $this->invokeReportRendererStatic('sendToBrowser', ['report', 'html', 'text/html', 'report body']);

        $this->assertDataResponseHeadersWereSent();
    }

    /**
     * The renderer builds the report body from Twig views, and rendering a view sends the headers
     * for a regular page. The data response headers have to win over those.
     */
    public function testHtmlReportInlineOverridesTheHeadersSentWhileRenderingTheReport()
    {
        ob_start();
        try {
            (new Html())->sendToBrowserInline('report');
        } finally {
            ob_end_clean();
        }

        $this->assertDataResponseHeadersWereSent();
    }

    /**
     * TCPDF writes its own response headers, so the data response headers have to be sent first.
     */
    public function testPdfReportInlineSendsDataResponseHeaders()
    {
        ob_start();
        try {
            (new Pdf())->sendToBrowserInline('report');
        } finally {
            ob_end_clean();
        }

        $this->assertDataResponseHeadersWereSent();
    }

    private function assertDataResponseHeadersWereSent(): void
    {
        $policy = trim(Common::$headersSentInTests['Content-Security-Policy'] ?? '');

        $this->assertSame('nosniff', trim(Common::$headersSentInTests['X-Content-Type-Options'] ?? ''));
        $this->assertSame('deny', trim(Common::$headersSentInTests['X-Frame-Options'] ?? ''));
        $this->assertSame('no-referrer', trim(Common::$headersSentInTests['Referrer-Policy'] ?? ''));
        $this->assertStringStartsWith(self::EXPECTED_POLICY_PREFIX, $policy);
        $this->assertStringContainsString("base-uri 'none'; form-action 'none'; frame-ancestors 'none';", $policy);
        $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', Common::$headersSentInTests);
    }

    private function invokeReportRendererStatic(string $method, array $args): void
    {
        $reflectionMethod = new ReflectionMethod(ReportRenderer::class, $method);
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invokeArgs(null, $args);
    }
}
