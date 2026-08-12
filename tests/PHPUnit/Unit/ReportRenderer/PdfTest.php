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
use ReflectionProperty;

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

            // A token that only looks like a scheme is a host and port, so it is completed
            // like any other scheme-less value and then dropped for not naming a domain.
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

        $renderer = (new \ReflectionClass(Pdf::class))->newInstanceWithoutConstructor();
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
     * @param mixed $value
     */
    private function setPrivateProperty(object $renderer, string $name, $value): void
    {
        $property = new ReflectionProperty(Pdf::class, $name);
        $property->setAccessible(true);
        $property->setValue($renderer, $value);
    }
}
