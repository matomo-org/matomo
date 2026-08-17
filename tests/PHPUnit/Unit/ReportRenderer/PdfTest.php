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
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

/**
 * @group Core
 */
class PdfTest extends TestCase
{
    private function getRenderableLabelLinkUrl(string $url): ?string
    {
        $method = new ReflectionMethod(Pdf::class, 'getRenderableLabelLinkUrl');
        $method->setAccessible(true);

        return $method->invoke($this->newRenderer(), $url);
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

            // A token that only looks like a scheme is a host and port, so it is completed
            // like any other scheme-less value and then dropped for not naming a domain.
            ['http:8080/report', null],

            // Other schemes are not linkable, they render as plain label text.
            ['ftp://example.com/quarterly-report.pdf', null],
            ['webcal://example.com/calendar.ics', null],

            // Values that cannot form a usable link render as plain label text.
            ['//example.com/report', null],
            ['/relative/path', null],
            ['', null],
        ];
    }

    /**
     * @dataProvider getTestDataForGetRenderableLabelLinkUrl
     */
    public function testGetRenderableLabelLinkUrl(string $url, ?string $expected): void
    {
        self::assertSame($expected, $this->getRenderableLabelLinkUrl($url));
    }

    public function getTestDataForRenderLabelLinkAndLogo(): array
    {
        return [
            ['example.com/report', ['https://example.com/report']],
            ['http://example.com/report', ['http://example.com/report']],
            ['ftp://example.com/quarterly-report.pdf', []],
            [false, []],
        ];
    }

    /**
     * @dataProvider getTestDataForRenderLabelLinkAndLogo
     * @param false|string $url
     * @param string[] $expectedLinks
     */
    public function testRenderLabelLinkAndLogoLinksTheRenderableUrlOnly($url, array $expectedLinks): void
    {
        $tcpdf = new class {
            /**
             * @var string[]
             */
            public $links = [];

            // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
            public function Link($posX, $posY, $width, $height, $link): void
            {
                $this->links[] = $link;
            }

            // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
            public function SetXY($posX, $posY): void
            {
            }
        };

        $renderer = $this->newRenderer();
        $this->setPrivateProperty($renderer, 'TCPDF', $tcpdf);
        $this->setPrivateProperty($renderer, 'labelCellWidth', 80.0);

        $logoWidth = 16.0;
        $logoHeight = 16.0;
        $method = new ReflectionMethod(Pdf::class, 'renderLabelLinkAndLogo');
        $method->setAccessible(true);
        $method->invokeArgs($renderer, [$url, 10.0, 20.0, 6.0, [], false, &$logoWidth, &$logoHeight]);

        self::assertSame($expectedLinks, $tcpdf->links);
    }

    /**
     * A table wider than the page has its last column clipped by TCPDF, which used to happen
     * once a report had around ten metric columns: the metric width was rounded to the nearest
     * millimetre, and half a millimetre per column is enough to push the table off the page.
     *
     * @dataProvider getColumnCounts
     */
    public function testColumnWidthsNeverExceedThePageWidth(int $metricColumns, float $labelWidth)
    {
        $renderer = new Pdf();

        $reflection = new ReflectionObject($renderer);

        $labelCellWidth = $reflection->getProperty('labelCellWidth');
        $labelCellWidth->setAccessible(true);
        $labelCellWidth->setValue($renderer, $labelWidth);

        $fit = $reflection->getMethod('fitColumnWidthsToPage');
        $fit->setAccessible(true);
        $fit->invoke($renderer, $metricColumns);

        $pageWidth = $reflection->getProperty('reportWidthPortrait');
        $pageWidth->setAccessible(true);
        $pageWidth = $pageWidth->getValue($renderer);

        $cellWidth = $reflection->getProperty('cellWidth');
        $cellWidth->setAccessible(true);
        $cellWidth = $cellWidth->getValue($renderer);

        $totalWidth = $reflection->getProperty('totalWidth');
        $totalWidth->setAccessible(true);

        $label = $labelCellWidth->getValue($renderer);

        self::assertGreaterThan(0, $cellWidth, 'every metric column gets some width');
        self::assertGreaterThan(0, $label, 'the label column gets some width');
        self::assertSame(
            (float) $pageWidth,
            (float) ($label + $metricColumns * $cellWidth),
            'the table fills the page exactly'
        );
        self::assertSame((float) $pageWidth, (float) $totalWidth->getValue($renderer));
    }

    public function getColumnCounts(): array
    {
        return [
            'one metric'                       => [1, 136.0],
            'four metrics'                     => [4, 117.0],
            'eight metrics, wide label'        => [8, 80.0],
            // a report with a percentage next to each of its metrics
            'ten metrics, short label'         => [10, 35.0],
            'ten metrics, wide label'          => [10, 80.0],
            'sixteen metrics'                  => [16, 35.0],
        ];
    }

    public function testASingleColumnTableGivesTheWholePageToTheLabel()
    {
        $renderer = new Pdf();

        $reflection = new ReflectionObject($renderer);

        $fit = $reflection->getMethod('fitColumnWidthsToPage');
        $fit->setAccessible(true);
        $fit->invoke($renderer, 0);

        $labelCellWidth = $reflection->getProperty('labelCellWidth');
        $labelCellWidth->setAccessible(true);

        $pageWidth = $reflection->getProperty('reportWidthPortrait');
        $pageWidth->setAccessible(true);

        self::assertSame(
            $pageWidth->getValue($renderer),
            $labelCellWidth->getValue($renderer)
        );
    }

    /**
     * Exercises the label rendering in isolation, without building a TCPDF document.
     */
    private function newRenderer(): Pdf
    {
        return (new ReflectionClass(Pdf::class))->newInstanceWithoutConstructor();
    }

    /**
     * @param mixed $value
     */
    private function setPrivateProperty(Pdf $renderer, string $name, $value): void
    {
        $property = new ReflectionProperty(Pdf::class, $name);
        $property->setAccessible(true);
        $property->setValue($renderer, $value);
    }
}
