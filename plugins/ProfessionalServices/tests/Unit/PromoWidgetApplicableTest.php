<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugin\Manager;
use Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable;
use Piwik\ProfessionalServices\Advertising;

class PromoWidgetApplicableTest extends TestCase
{
    /**
     * @dataProvider checkDataProvider
     */
    public function test_check_shouldReturnBool(bool $adsForProfessionalServicesEnabled, bool $pluginActivated, bool $expected): void
    {
        $advertising = $this->createMock(Advertising::class);
        $advertising->method('areAdsForProfessionalServicesEnabled')->willReturn($adsForProfessionalServicesEnabled);

        $manager = $this->createMock(Manager::class);
        $manager->method('isPluginActivated')->with('MyPlugin')->willReturn($pluginActivated);

        $sut = new PromoWidgetApplicable($advertising, $manager);
        $this->assertEquals($expected, $sut->check('MyPlugin'));
    }

    protected function checkDataProvider(): \Generator
    {
        yield [true, true, false];
        yield [false, true, false];
        yield [true, false, true];
        yield [false, false, false];
    }
}
