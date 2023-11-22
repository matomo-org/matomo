<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugin\Manager;
use Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable;

class PromoWidgetApplicableTest extends TestCase
{
    /**
     * @dataProvider checkDataProvider
     */
    public function test_check_shouldOnlyReturnTrue_IfAdShouldBeShown(bool $adsForProfessionalServicesEnabled, bool $marketplaceEnabled, bool $internetAccessEnabled, bool $pluginActivated, bool $expected): void
    {
        $manager = $this->createMock(Manager::class);
        $manager->method('isPluginActivated')->willReturnMap(
            [
                ['MyPlugin', $pluginActivated],
                ['Marketplace', $marketplaceEnabled],
            ]
        );
        $config = $this->createMock(Config::class);
        $config->method('__get')
            ->with('General')
            ->willReturn([
                'enable_internet_features' => $internetAccessEnabled,
                'piwik_professional_support_ads_enabled' => $adsForProfessionalServicesEnabled,
            ]);

        $sut = new PromoWidgetApplicable($manager, $config);
        $this->assertEquals($expected, $sut->check('MyPlugin', 'Any'));
    }

    protected function checkDataProvider(): \Generator
    {
        yield [true, true, true, true, false];
        yield [true, true, true, false, true];
        yield [true, true, false, true, false];
        yield [true, true, false, false, false];
        yield [true, false, true, true, false];
        yield [true, false, true, false, false];
        yield [true, false, false, true, false];
        yield [true, false, false, false, false];
        yield [false, true, true, true, false];
        yield [false, true, true, false, false];
        yield [false, true, false, true, false];
        yield [false, true, false, false, false];
        yield [false, false, true, true, false];
        yield [false, false, true, false, false];
        yield [false, false, false, true, false];
        yield [false, false, false, false, false];
    }
}
