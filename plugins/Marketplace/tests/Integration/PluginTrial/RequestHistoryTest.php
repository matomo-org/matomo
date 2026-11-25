<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

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

    public function testGetRequestsReturnsOnlyThatPluginsRequestsNewestFirst(): void
    {
        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('OtherPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('PremiumPlugin', 'bob', strtotime('2026-10-03 10:00:00'));

        self::assertSame([
            ['plugin_name' => 'PremiumPlugin', 'login' => 'bob', 'ts_requested' => '2026-10-03 10:00:00'],
            ['plugin_name' => 'PremiumPlugin', 'login' => 'alice', 'ts_requested' => '2026-10-01 10:00:00'],
        ], $this->history->getRequests('PremiumPlugin'));
    }

    public function testHasRequestedCountsOnlyThatLoginAndPlugin(): void
    {
        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        $this->history->add('OtherPlugin', 'bob', strtotime('2026-10-01 10:00:00'));

        self::assertTrue($this->history->hasRequested('PremiumPlugin', 'alice'));
        self::assertFalse($this->history->hasRequested('PremiumPlugin', 'bob'));
        self::assertFalse($this->history->hasRequested('OtherPlugin', 'alice'));
    }

    public function testHasRequestedSeesRequestsAddedAfterAnEarlierCheck(): void
    {
        self::assertFalse($this->history->hasRequested('PremiumPlugin', 'alice'));

        $this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00'));
        self::assertTrue($this->history->hasRequested('PremiumPlugin', 'alice'));

        $this->history->anonymizeLogin('alice');
        self::assertFalse($this->history->hasRequested('PremiumPlugin', 'alice'));
    }

    public function testEachLoginIsRecordedOncePerPlugin(): void
    {
        self::assertTrue($this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-01 10:00:00')));
        self::assertFalse($this->history->add('PremiumPlugin', 'alice', strtotime('2026-10-02 10:00:00')));
        // a later request that only the option holds, as Matomo before the update can store
        $this->history->addIfMissing('PremiumPlugin', 'alice', strtotime('2026-10-03 10:00:00'));

        self::assertSame(
            ['2026-10-01 10:00:00'],
            array_column($this->history->getRequests('PremiumPlugin'), 'ts_requested')
        );
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
