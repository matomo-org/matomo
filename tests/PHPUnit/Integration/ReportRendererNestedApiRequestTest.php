<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Piwik\API\Request;
use Piwik\ReportRenderer;
use Piwik\ReportRenderer\Pdf;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * A report may only be streamed to the browser by the top-level request. A report generated as a
 * nested API sub-request must be returned to the calling request instead.
 *
 * @group Core
 * @group ReportRenderer
 */
class ReportRendererNestedApiRequestTest extends IntegrationTestCase
{
    private const REFUSED_MESSAGE = 'A report can only be sent to the browser by the top-level request.';

    public function tearDown(): void
    {
        $this->setNestedApiInvocationCount(0);
        parent::tearDown();
    }

    public function testInlineToBrowserIsRefusedInsideNestedApiRequest()
    {
        $this->setNestedApiInvocationCount(2);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::REFUSED_MESSAGE);

        $this->invokeReportRendererStatic('inlineToBrowser', ['text/html', 'report body']);
    }

    public function testSendToBrowserIsRefusedInsideNestedApiRequest()
    {
        $this->setNestedApiInvocationCount(2);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::REFUSED_MESSAGE);

        $this->invokeReportRendererStatic('sendToBrowser', ['report', 'html', 'text/html', 'report body']);
    }

    public function testPdfSendToBrowserInlineIsRefusedInsideNestedApiRequest()
    {
        $this->setNestedApiInvocationCount(2);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::REFUSED_MESSAGE);

        (new Pdf())->sendToBrowserInline('report');
    }

    public function testPdfSendToBrowserDownloadIsRefusedInsideNestedApiRequest()
    {
        $this->setNestedApiInvocationCount(2);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::REFUSED_MESSAGE);

        (new Pdf())->sendToBrowserDownload('report');
    }

    public function testInlineToBrowserIsAllowedForTheRootApiRequest()
    {
        $this->setNestedApiInvocationCount(1);

        $this->expectOutputString('report body');
        $this->invokeReportRendererStatic('inlineToBrowser', ['text/html', 'report body']);
    }

    private function invokeReportRendererStatic(string $method, array $args): void
    {
        $reflectionMethod = new ReflectionMethod(ReportRenderer::class, $method);
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invokeArgs(null, $args);
    }

    private function setNestedApiInvocationCount(int $count): void
    {
        $reflection = new ReflectionClass(Request::class);
        $reflectionProperty = $reflection->getProperty('nestedApiInvocationCount');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, $count);
    }
}
