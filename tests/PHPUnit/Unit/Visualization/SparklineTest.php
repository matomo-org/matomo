<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Visualization;

use Piwik\Visualization\Sparkline;

require_once PIWIK_INCLUDE_PATH . '/core/Visualization/Sparkline.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreHome/tests/resources/sparkline/SparklineDouble.php';

/**
 * @group Sparkline
 */
class SparklineTest extends \PHPUnit\Framework\TestCase
{
    private $oldGet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oldGet = $_GET;
    }

    protected function tearDown(): void
    {
        $_GET = $this->oldGet;

        parent::tearDown();
    }

    public function testSetSparklineColorsTreatsWhiteColorsCaseInsensitively(): void
    {
        $_GET['colors'] = json_encode([
            'backgroundColor' => '#FFFFFF',
            'lineColor' => '#123456',
            'fillColor' => '#FFFFFF',
            'minPointColor' => '#FFFFFF',
            'maxPointColor' => '#FFFFFF',
            'lastPointColor' => '#FFFFFF',
        ]);

        $sparkline = new Sparkline();
        $double = new \Piwik\Plugins\CoreHome\tests\resources\sparkline\SparklineDouble();

        $method = new \ReflectionMethod(Sparkline::class, 'setSparklineColors');
        $method->setAccessible(true);
        $method->invoke($sparkline, $double, 0);

        $this->assertTrue($double->backgroundDeactivated);
        $this->assertTrue($double->fillDeactivated);
        $this->assertSame([], $double->points);
        $this->assertSame([['#123456', null]], $double->lineColors);
    }

    public function testSetWidthClampsToMaxWidth(): void
    {
        $sparkline = new Sparkline();
        $sparkline->setWidth(Sparkline::MAX_WIDTH + 5000);

        $this->assertSame(Sparkline::MAX_WIDTH, $sparkline->getWidth());
    }

    public function testSetHeightClampsToMaxHeight(): void
    {
        $sparkline = new Sparkline();
        $sparkline->setHeight(Sparkline::MAX_HEIGHT + 5000);

        $this->assertSame(Sparkline::MAX_HEIGHT, $sparkline->getHeight());
    }

    /**
     * Sparkline cards cap their own size client-side so a request never reaches the clamps above,
     * which would squash the image. Read the client constants rather than restating them: the
     * dangerous edit is raising one of those past what the server allows, and a test that only
     * knows the PHP side cannot catch it.
     */
    public function testMaxDimensionsMatchTheValuesMirroredClientSide(): void
    {
        $source = $this->getSparklineSlotSizeSource();

        // The image is requested at twice the size it is displayed at, for hi-DPI screens.
        $this->assertSame(Sparkline::MAX_WIDTH, 2 * $this->readClientConstant($source, 'MAX_DISPLAY_WIDTH'));
        $this->assertSame(Sparkline::MAX_HEIGHT, 2 * $this->readClientConstant($source, 'MAX_DISPLAY_HEIGHT'));
    }

    private function getSparklineSlotSizeSource(): string
    {
        $path = PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/vue/src/Sparklines/useSparklineSlotSize.ts';
        $this->assertFileExists($path);

        return (string) file_get_contents($path);
    }

    private function readClientConstant(string $source, string $name): int
    {
        $found = preg_match('/export const ' . $name . ' = (\\d+);/', $source, $matches);
        $this->assertSame(1, $found, "$name is not declared in useSparklineSlotSize.ts");

        return (int) $matches[1];
    }

    /**
     * @dataProvider getInvalidDimensions
     */
    public function testSetWidthIgnoresInvalidValues($invalidValue): void
    {
        $sparkline = new Sparkline();
        $sparkline->setWidth($invalidValue);

        $this->assertSame(Sparkline::DEFAULT_WIDTH, $sparkline->getWidth());
    }

    /**
     * @dataProvider getInvalidDimensions
     */
    public function testSetHeightIgnoresInvalidValues($invalidValue): void
    {
        $sparkline = new Sparkline();
        $sparkline->setHeight($invalidValue);

        $this->assertSame(Sparkline::DEFAULT_HEIGHT, $sparkline->getHeight());
    }

    public function getInvalidDimensions(): array
    {
        return [
            'zero' => [0],
            'negative' => [-100],
            'non-numeric' => ['abc'],
        ];
    }
}
