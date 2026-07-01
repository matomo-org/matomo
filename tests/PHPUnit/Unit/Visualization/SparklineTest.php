<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Visualization;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreVisualizations\FeatureFlags\SparklinesRedesign;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Visualization\Sparkline;

require_once PIWIK_INCLUDE_PATH . '/core/Visualization/Sparkline.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreHome/tests/resources/sparkline/SparklineDouble.php';

/**
 * @group Sparkline
 */
class SparklineTest extends \PHPUnit\Framework\TestCase
{
    private $oldGet;
    private $oldFeatureFlagManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oldGet = $_GET;
        $container = StaticContainer::getContainer();
        $this->oldFeatureFlagManager = $container->has(FeatureFlagManager::class)
            ? $container->get(FeatureFlagManager::class)
            : null;
    }

    protected function tearDown(): void
    {
        $_GET = $this->oldGet;

        StaticContainer::getContainer()->set(
            FeatureFlagManager::class,
            $this->oldFeatureFlagManager ?? $this->createFeatureFlagManager(false)
        );

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

    public function testGetLineThicknessUsesRedesignThicknessWhenFeatureFlagIsEnabled(): void
    {
        StaticContainer::getContainer()->set(
            FeatureFlagManager::class,
            $this->createFeatureFlagManager(true)
        );

        $sparkline = new Sparkline();

        $method = new \ReflectionMethod(Sparkline::class, 'getLineThickness');
        $method->setAccessible(true);

        $this->assertSame(Sparkline::REDESIGN_LINE_THICKNESS, $method->invoke($sparkline));
    }

    public function testGetLineThicknessUsesDefaultThicknessWhenFeatureFlagIsDisabled(): void
    {
        StaticContainer::getContainer()->set(
            FeatureFlagManager::class,
            $this->createFeatureFlagManager(false)
        );

        $sparkline = new Sparkline();

        $method = new \ReflectionMethod(Sparkline::class, 'getLineThickness');
        $method->setAccessible(true);

        $this->assertSame(Sparkline::DEFAULT_LINE_THICKNESS, $method->invoke($sparkline));
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

    private function createFeatureFlagManager(bool $isEnabled): FeatureFlagManager
    {
        $featureFlagManager = $this->createMock(FeatureFlagManager::class);
        $featureFlagManager->method('isFeatureActive')
            ->with(SparklinesRedesign::class)
            ->willReturn($isEnabled);

        return $featureFlagManager;
    }
}
