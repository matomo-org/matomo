<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Date;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 */
class RequestHistoryTest extends IntegrationTestCase
{
    private RequestHistory $history;

    public function setUp(): void
    {
        parent::setUp();

        $this->history = new RequestHistory();
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testGetRequestsReturnsOnlyThatPluginsRequestsNewestFirst(): void
    {
        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('OtherPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('PremiumPlugin', 'bob', strtotime('2026-10-03 10:00:00'));

        self::assertSame([
            ['plugin_name' => 'PremiumPlugin', 'login' => 'bob', 'ts_requested' => '2026-10-03 10:00:00', 'ts_fulfilled' => null, 'ts_expired' => null],
            ['plugin_name' => 'PremiumPlugin', 'login' => 'alice', 'ts_requested' => '2026-10-01 10:00:00', 'ts_fulfilled' => null, 'ts_expired' => null],
        ], $this->history->getRequests('PremiumPlugin'));
    }

    public function testEndingARequestEndsEveryOpenRequestOfThatPluginOnlyOnce(): void
    {
        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-09-01 10:00:00'));
        Date::$now = strtotime('2026-09-29 12:00:00');
        $this->history->markExpired('PremiumPlugin');

        $this->history->add('PremiumPlugin', 'carol', strtotime('2026-10-01 10:00:01'));
        $this->history->add('PremiumPlugin', 'bob', strtotime('2026-10-01 10:00:00'));
        $this->history->add('OtherPlugin', 'bob', strtotime('2026-10-01 10:00:00'));

        Date::$now = strtotime('2026-10-05 12:00:00');
        $this->history->markFulfilled('PremiumPlugin');
        Date::$now = strtotime('2026-10-06 12:00:00');
        $this->history->markFulfilled('PremiumPlugin');
        $this->history->markExpired('PremiumPlugin');

        // newest first: carol, bob, alice
        $premium = $this->history->getRequests('PremiumPlugin');
        self::assertSame(['2026-10-05 12:00:00', '2026-10-05 12:00:00', null], array_column($premium, 'ts_fulfilled'));
        self::assertSame([null, null, '2026-09-29 12:00:00'], array_column($premium, 'ts_expired'));

        $other = $this->history->getRequests('OtherPlugin');
        self::assertSame([null], array_column($other, 'ts_fulfilled'));
        self::assertSame([null], array_column($other, 'ts_expired'));
    }

    public function testDeletingUserKeepsTheirRequestsWithoutTheirLogin(): void
    {
        Fixture::createSuperUser();
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('bob', 'secret-password-2', 'bob@example.com');

        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('PremiumPlugin', 'bob', strtotime('2026-10-01 10:00:00'));

        UsersManagerAPI::getInstance()->deleteUser('alice');

        self::assertSame(
            ['bob', null],
            array_column($this->history->getRequests('PremiumPlugin'), 'login')
        );
    }
}
