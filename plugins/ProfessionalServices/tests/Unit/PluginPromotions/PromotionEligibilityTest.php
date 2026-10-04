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
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PremiumEntitlements;
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
        bool $pluginInFilesystem,
        ?bool $pluginLicensed,
        bool $expected
    ): void {
        $manager = $this->createMock(Manager::class);
        $manager->method('isPluginActivated')->willReturnMap([
            ['Marketplace', $marketplaceActivated],
        ]);
        $manager->method('isPluginInFilesystem')->willReturnMap([
            ['MyPlugin', $pluginInFilesystem],
        ]);

        $config = $this->createMock(Config::class);
        $config->method('__get')
            ->with('General')
            ->willReturn([
                'piwik_professional_support_ads_enabled' => $adsEnabled,
                'enable_internet_features' => $internetEnabled,
            ]);

        $entitlements = $this->createMock(PremiumEntitlements::class);
        $entitlements->method('isLicensed')->willReturn($pluginLicensed);

        $eligibility = new PromotionEligibility($manager, $config, $entitlements);

        $this->assertSame($expected, $eligibility->isAllowedForPlugin('MyPlugin'));
    }

    /**
     * @return array<string, array{bool, bool, bool, bool, bool|null, bool}>
     */
    public function getConditions(): array
    {
        return [
            'everything allows it' => [true, true, true, false, false, true],
            'promotions disabled in the config' => [false, true, true, false, false, false],
            'marketplace not activated' => [true, false, true, false, false, false],
            'internet features disabled' => [true, true, false, false, false, false],
            // On disk covers both the activated case and the one this missed: a plugin the
            // customer has installed and then switched off.
            'plugin installed' => [true, true, true, true, false, false],
            // Paid for, not downloaded yet.
            'plugin licensed but not installed' => [true, true, true, false, true, false],
            // No answer from the Marketplace is not an answer of "not licensed", but it
            // must not silence the promotion either.
            'license state unknown' => [true, true, true, false, null, true],
            'nothing allows it' => [false, false, false, true, true, false],
        ];
    }
}
