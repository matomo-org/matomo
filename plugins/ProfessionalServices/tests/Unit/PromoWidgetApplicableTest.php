<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugin\Manager;
use Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable;
use Piwik\Plugins\ProfessionalServices\PromoWidgetDismissal;

class PromoWidgetApplicableTest extends TestCase
{
    /**
     * @dataProvider checkDataProvider
     */
    public function test_check_shouldOnlyReturnTrue_IfAdShouldBeShown(bool $adsForProfessionalServicesEnabled, bool $marketplaceEnabled, bool $internetAccessEnabled, bool $pluginActivated, bool $isDismissed, bool $expected): void
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

        $promoWidgetDismissal = $this->createMock(PromoWidgetDismissal::class);
        $promoWidgetDismissal->method('isPromoWidgetDismissedForCurrentUser')
            ->with('Any')
            ->willReturn($isDismissed);

        $sut = new PromoWidgetApplicable($manager, $config, $promoWidgetDismissal);
        $this->assertEquals($expected, $sut->check('MyPlugin', 'Any'));
    }

    public function checkDataProvider(): \Generator
    {
        yield [true, true, true, true, false, false];
        yield [true, true, true, false, false, true];
        yield [true, true, false, true, false, false];
        yield [true, true, false, false, false, false];
        yield [true, false, true, true, false, false];
        yield [true, false, true, false, false, false];
        yield [true, false, false, true, false, false];
        yield [true, false, false, false, false, false];
        yield [false, true, true, true, false, false];
        yield [false, true, true, false, false, false];
        yield [false, true, false, true, false, false];
        yield [false, true, false, false, false, false];
        yield [false, false, true, true, false, false];
        yield [false, false, true, false, false, false];
        yield [false, false, false, true, false, false];
        yield [false, false, false, false, false, false];
        yield [true, true, true, false, true, false];
    }
}
