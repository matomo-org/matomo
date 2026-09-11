<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugin\Manager;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionEligibility;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class PromotionEligibilityTest extends TestCase
{
    /**
     * @dataProvider getConditions
     */
    public function testAPluginMayOnlyBePromotedWhenEveryConditionHolds(
        bool $adsEnabled,
        bool $marketplaceActivated,
        bool $internetEnabled,
        bool $pluginActivated,
        bool $expected
    ): void {
        $manager = $this->createMock(Manager::class);
        $manager->method('isPluginActivated')->willReturnMap([
            ['MyPlugin', $pluginActivated],
            ['Marketplace', $marketplaceActivated],
        ]);

        $config = $this->createMock(Config::class);
        $config->method('__get')
            ->with('General')
            ->willReturn([
                'piwik_professional_support_ads_enabled' => $adsEnabled,
                'enable_internet_features' => $internetEnabled,
            ]);

        $eligibility = new PromotionEligibility($manager, $config);

        $this->assertSame($expected, $eligibility->isAllowedForPlugin('MyPlugin'));
    }

    /**
     * @return array<string, array{bool, bool, bool, bool, bool}>
     */
    public function getConditions(): array
    {
        return [
            'everything allows it' => [true, true, true, false, true],
            'promotions disabled in the config' => [false, true, true, false, false],
            'marketplace not activated' => [true, false, true, false, false],
            'internet features disabled' => [true, true, false, false, false],
            'plugin already installed' => [true, true, true, true, false],
            'nothing allows it' => [false, false, false, true, false],
        ];
    }
}
