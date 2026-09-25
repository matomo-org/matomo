<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PremiumBundle;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PremiumEntitlements;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BusinessBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\EnterpriseBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\PremiumBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TeamBundleTrigger;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class PremiumBundleTriggerTest extends TestCase
{
    /**
     * @dataProvider getUserRangeCases
     */
    public function testEachBundleTakesItsOwnUserRange(string $class, int $numUsers, bool $expectedToFire): void
    {
        $trigger = $this->makeTrigger($class, $numUsers, $products = 3, $tierHeld = 0);

        $this->assertSame($expectedToFire, $trigger->evaluate(1)->isTriggered());
    }

    /**
     * @return array<string, array{class-string<PremiumBundleTrigger>, int, bool}>
     */
    public function getUserRangeCases(): array
    {
        return [
            'team at one user' => [TeamBundleTrigger::class, 1, true],
            'team at its ceiling' => [TeamBundleTrigger::class, 4, true],
            'team just above its ceiling' => [TeamBundleTrigger::class, 5, false],
            'business just below its floor' => [BusinessBundleTrigger::class, 4, false],
            'business at its floor' => [BusinessBundleTrigger::class, 5, true],
            'business at its ceiling' => [BusinessBundleTrigger::class, 20, true],
            'business just above its ceiling' => [BusinessBundleTrigger::class, 21, false],
            'enterprise just below its floor' => [EnterpriseBundleTrigger::class, 20, false],
            'enterprise at its floor' => [EnterpriseBundleTrigger::class, 21, true],
            'enterprise at its ceiling' => [EnterpriseBundleTrigger::class, 50, true],
            // Above the largest range no bundle is pitched at all.
            'enterprise just above its ceiling' => [EnterpriseBundleTrigger::class, 51, false],
        ];
    }

    /**
     * @dataProvider getProductCountCases
     */
    public function testTheProductFloorApplies(int $numProducts, bool $expectedToFire): void
    {
        $trigger = $this->makeTrigger(TeamBundleTrigger::class, 3, $numProducts, 0);

        $this->assertSame($expectedToFire, $trigger->evaluate(1)->isTriggered());
    }

    /**
     * @return array<string, array{int, bool}>
     */
    public function getProductCountCases(): array
    {
        return [
            'just below the floor' => [2, false],
            'at the floor' => [3, true],
            'above the floor' => [9, true],
            'none' => [0, false],
        ];
    }

    /**
     * @dataProvider getBundleHeldCases
     */
    public function testABundleIsNotPitchedToSomeoneWhoAlreadyHasItOrMore(
        string $class,
        int $numUsers,
        int $tierHeld,
        bool $expectedToFire
    ): void {
        $trigger = $this->makeTrigger($class, $numUsers, 3, $tierHeld);

        $this->assertSame($expectedToFire, $trigger->evaluate(1)->isTriggered());
    }

    /**
     * @return array<string, array{class-string<PremiumBundleTrigger>, int, int, bool}>
     */
    public function getBundleHeldCases(): array
    {
        $team = PremiumBundle::getTier(PremiumBundle::TEAM);
        $business = PremiumBundle::getTier(PremiumBundle::BUSINESS);
        $enterprise = PremiumBundle::getTier(PremiumBundle::ENTERPRISE);

        return [
            'team promoted, nothing held' => [TeamBundleTrigger::class, 3, 0, true],
            'team promoted, team held' => [TeamBundleTrigger::class, 3, $team, false],
            'team promoted, business held' => [TeamBundleTrigger::class, 3, $business, false],
            'team promoted, enterprise held' => [TeamBundleTrigger::class, 3, $enterprise, false],
            // A smaller bundle does not cover a larger one, so the larger is still worth pitching.
            'business promoted, team held' => [BusinessBundleTrigger::class, 10, $team, true],
            'business promoted, business held' => [BusinessBundleTrigger::class, 10, $business, false],
            'business promoted, enterprise held' => [BusinessBundleTrigger::class, 10, $enterprise, false],
            'enterprise promoted, business held' => [EnterpriseBundleTrigger::class, 30, $business, true],
            'enterprise promoted, enterprise held' => [EnterpriseBundleTrigger::class, 30, $enterprise, false],
        ];
    }

    /**
     * Not knowing what the consumer holds is not the same as knowing they hold nothing.
     * The one thing these promotions must not do is pitch a bundle already bought.
     */
    public function testNothingIsPitchedWhenTheMarketplaceCannotBeReached(): void
    {
        $entitlements = $this->createMock(PremiumEntitlements::class);
        $entitlements->method('isBundleOffered')->willReturn(true);
        $entitlements->method('getHighestBundleTierHeld')->willReturn(null);
        $entitlements->method('countPremiumProducts')->willReturn(null);

        $environment = $this->createMock(Environment::class);
        $environment->method('getNumUsers')->willReturn(3);

        $trigger = new TeamBundleTrigger($entitlements, $environment);

        $this->assertFalse($trigger->evaluate(1)->isTriggered());
    }

    /**
     * The Team bundle is only sold to newer accounts, so a legacy account that meets every
     * other condition must still not be pitched it.
     */
    public function testABundleTheMarketplaceDoesNotOfferIsNeverPitched(): void
    {
        $entitlements = $this->createMock(PremiumEntitlements::class);
        $entitlements->method('isBundleOffered')->willReturn(false);
        $entitlements->method('getHighestBundleTierHeld')->willReturn(0);
        $entitlements->method('countPremiumProducts')->willReturn(9);

        $environment = $this->createMock(Environment::class);
        $environment->method('getNumUsers')->willReturn(3);

        $this->assertFalse((new TeamBundleTrigger($entitlements, $environment))->evaluate(1)->isTriggered());
    }

    public function testTheProductCountIsReportedForTheCopy(): void
    {
        $result = $this->makeTrigger(TeamBundleTrigger::class, 4, 7, 0)->evaluate(1);

        $this->assertTrue($result->isTriggered());
        $this->assertSame(['count' => 7, 'numUsers' => 4], $result->getContext());
    }

    /**
     * @param class-string<PremiumBundleTrigger> $class
     */
    private function makeTrigger(string $class, int $numUsers, int $numProducts, int $tierHeld): PremiumBundleTrigger
    {
        $entitlements = $this->createMock(PremiumEntitlements::class);
        $entitlements->method('isBundleOffered')->willReturn(true);
        $entitlements->method('getHighestBundleTierHeld')->willReturn($tierHeld);
        $entitlements->method('countPremiumProducts')->willReturn($numProducts);

        $environment = $this->createMock(Environment::class);
        $environment->method('getNumUsers')->willReturn($numUsers);

        return new $class($entitlements, $environment);
    }
}
