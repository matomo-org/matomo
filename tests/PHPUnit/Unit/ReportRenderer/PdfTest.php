<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\ReportRenderer;

use Piwik\ReportRenderer\Pdf;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @group Core
 */
class PdfTest extends TestCase
{
    /**
     * @return false|string
     */
    private function getRenderableLabelLinkUrl(string $url)
    {
        // Exercise the label link target in isolation, without building a TCPDF document.
        $renderer = (new \ReflectionClass(Pdf::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(Pdf::class, 'getRenderableLabelLinkUrl');
        $method->setAccessible(true);

        return $method->invoke($renderer, $url);
    }

    public function getTestDataForGetRenderableLabelLinkUrl(): array
    {
        return [
            // Values already carrying a web or contact scheme are linked as they are.
            ['http://example.com/report', 'http://example.com/report'],
            ['https://example.com/report', 'https://example.com/report'],
            ['https://example.com/quarterly-report.pdf', 'https://example.com/quarterly-report.pdf'],
            ['mailto:someone@example.com', 'mailto:someone@example.com'],
            ['tel:+123456789', 'tel:+123456789'],
            ['sms:+123456789', 'sms:+123456789'],
            ['callto:someone', 'callto:someone'],

            // Scheme-less values are completed, so bare domain rows stay clickable.
            ['example.com/report', 'https://example.com/report'],
            ['example.com/quarterly-report.pdf', 'https://example.com/quarterly-report.pdf'],
            ['example.com', 'https://example.com'],
            ['example.com:8080/report', 'https://example.com:8080/report'],

            // A token that only looks like a scheme is completed, not trusted as one.
            ['http:8080/report', false],

            // Other schemes are not linkable, they render as plain label text.
            ['ftp://example.com/quarterly-report.pdf', false],
            ['webcal://example.com/calendar.ics', false],

            // Values that cannot form a usable link render as plain label text.
            ['//example.com/report', false],
            ['/relative/path', false],
            ['', false],
        ];
    }

    /**
     * @dataProvider getTestDataForGetRenderableLabelLinkUrl
     * @param false|string $expected
     */
    public function testGetRenderableLabelLinkUrl(string $url, $expected): void
    {
        self::assertSame($expected, $this->getRenderableLabelLinkUrl($url));
    }
}
