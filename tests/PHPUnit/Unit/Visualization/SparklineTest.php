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
     * The sparkline card caps its own width at MAX_WIDTH / 2 (MAX_DISPLAY_WIDTH in
     * useSparklineSlotSize.ts), so a request is never clamped here, which would squash the image.
     *
     * These literals are deliberate: this test cannot read the TypeScript value, so it only
     * catches a change made here. If it fails, update the client constant to match.
     */
    public function testMaxDimensionsMatchTheValuesMirroredClientSide(): void
    {
        $this->assertSame(1600, Sparkline::MAX_WIDTH);
        $this->assertSame(128, Sparkline::MAX_HEIGHT);
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
