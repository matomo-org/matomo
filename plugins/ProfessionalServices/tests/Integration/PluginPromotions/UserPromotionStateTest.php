<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\UserPromotionState;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class UserPromotionStateTest extends IntegrationTestCase
{
    private UserPromotionState $state;

    public function setUp(): void
    {
        parent::setUp();

        Date::$now = strtotime('2026-08-27 10:00:00 UTC');

        $this->asUser('alice');
        $this->state = new UserPromotionState(StaticContainer::get(UserScopedSettingsAccessManager::class));
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testNothingIsInCooldownForAFreshUser(): void
    {
        $this->assertFalse($this->state->isInGlobalCooldown());
        $this->assertFalse($this->state->isProductInCooldown('CustomReports'));
    }

    public function testDismissingStartsBothCooldowns(): void
    {
        $this->state->dismiss('CustomReports', 'segments');

        $this->assertTrue($this->state->isInGlobalCooldown());
        $this->assertTrue($this->state->isProductInCooldown('CustomReports'));

        // Only the dismissed product gets a product cooldown.
        $this->assertFalse($this->state->isProductInCooldown('Funnels'));
    }

    public function testTheGlobalCooldownEndsAfterSevenDaysButTheProductCooldownRemains(): void
    {
        $this->state->dismiss('CustomReports', 'segments');

        Date::$now = strtotime('2026-09-02 10:00:00 UTC'); // six days later
        $this->assertTrue($this->state->isInGlobalCooldown());

        Date::$now = strtotime('2026-09-04 10:00:00 UTC'); // eight days later
        $this->assertFalse($this->state->isInGlobalCooldown());
        $this->assertTrue($this->state->isProductInCooldown('CustomReports'));
    }

    public function testTheProductCooldownEndsAfterSixMonths(): void
    {
        $this->state->dismiss('CustomReports', 'segments');

        Date::$now = strtotime('2027-02-20 10:00:00 UTC'); // just under six months
        $this->assertTrue($this->state->isProductInCooldown('CustomReports'));

        Date::$now = strtotime('2027-03-01 10:00:00 UTC'); // just over six months
        $this->assertFalse($this->state->isProductInCooldown('CustomReports'));
    }

    /**
     * Triggers are evaluated per website, but a dismissal is not: it silences the
     * promotion everywhere for that user. The state deliberately records no site at all.
     */
    public function testADismissalAppliesToEveryWebsite(): void
    {
        $this->state->dismiss('CustomReports', 'scheduled_reports');

        // Nothing about the state is scoped to a website, so there is nothing that could
        // let the promotion reappear on another one.
        $this->assertTrue($this->state->isProductInCooldown('CustomReports'));
    }

    public function testOtherUsersAreNotAffected(): void
    {
        $this->state->dismiss('CustomReports', 'segments');

        $this->asUser('bob');

        $this->assertFalse($this->state->isInGlobalCooldown());
        $this->assertFalse($this->state->isProductInCooldown('CustomReports'));

        $this->asUser('alice');

        $this->assertTrue($this->state->isInGlobalCooldown());
    }

    /**
     * Displaying a promotion starts no cooldown, so the timestamp it records is purely
     * informational and must not add a write to every single dashboard request.
     */
    public function testRecordShownWritesAtMostOncePerDay(): void
    {
        $stored = [];

        $accessManager = $this->createMock(UserScopedSettingsAccessManager::class);
        $accessManager->method('get')->willReturnCallback(static function () use (&$stored) {
            return $stored;
        });
        $accessManager->expects($this->exactly(2))->method('set')->willReturnCallback(
            static function (string $plugin, string $login, string $key, $value) use (&$stored): void {
                $stored = $value;
            }
        );

        $state = new UserPromotionState($accessManager);

        $state->recordShown('CustomReports', 'segments');

        Date::$now = strtotime('2026-08-27 23:00:00 UTC');
        $state->recordShown('CustomReports', 'segments');

        Date::$now = strtotime('2026-08-28 00:30:00 UTC');
        $state->recordShown('CustomReports', 'segments');
    }

    public function testShowingAPromotionStartsNoCooldown(): void
    {
        $this->state->recordShown('CustomReports', 'segments');

        $this->assertFalse($this->state->isInGlobalCooldown());
        $this->assertFalse($this->state->isProductInCooldown('CustomReports'));
    }

    private function asUser(string $login): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = $login;
        FakeAccess::$idSitesView = [1];
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
