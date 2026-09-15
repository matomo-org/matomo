<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Integration\PluginPromotions;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\ProfessionalServices\API;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\UserPromotionState;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The one request-facing entry point the promotions add. It writes per-user state and
 * takes both of its parameters from the request, so who may call it and what it accepts
 * are worth pinning down.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 * @group Plugins
 */
class DismissDashboardPromotionTest extends IntegrationTestCase
{
    private API $api;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2026-01-01 00:00:00');

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'alice';
        FakeAccess::$idSitesView = [1];

        $this->api = API::getInstance();
    }

    public function testItRecordsTheDismissalForTheCurrentUser(): void
    {
        $state = StaticContainer::get(UserPromotionState::class);

        $this->assertFalse($state->isProductInCooldown('CustomReports'));

        $_GET['pluginName'] = 'CustomReports';
        $_GET['triggerName'] = 'segments';

        $this->assertTrue($this->api->dismissDashboardPromotion());

        $this->assertTrue($state->isInGlobalCooldown());
        $this->assertTrue($state->isProductInCooldown('CustomReports'));
    }

    public function testAnonymousUsersMayNotDismiss(): void
    {
        FakeAccess::$identity = 'anonymous';

        $_GET['pluginName'] = 'CustomReports';
        $_GET['triggerName'] = 'segments';

        $this->expectException(\Exception::class);

        $this->api->dismissDashboardPromotion();
    }

    /**
     * The pair has to name a promotion that exists, so a caller cannot write arbitrary
     * plugin names into the user's stored state.
     */
    public function testAnUnknownPromotionIsRejected(): void
    {
        $_GET['pluginName'] = 'NotAPlugin';
        $_GET['triggerName'] = 'segments';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can\'t dismiss unknown plugin promotion NotAPlugin');

        $this->api->dismissDashboardPromotion();
    }

    /**
     * Both halves are checked together: a real plugin paired with a trigger that does not
     * promote it is not a promotion either.
     */
    public function testAKnownPluginWithTheWrongTriggerIsRejected(): void
    {
        $_GET['pluginName'] = 'CustomReports';
        $_GET['triggerName'] = 'bounce_rate';

        $this->expectException(\Exception::class);

        $this->api->dismissDashboardPromotion();
    }

    public function tearDown(): void
    {
        unset($_GET['pluginName'], $_GET['triggerName']);

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
