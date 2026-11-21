<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\PrivacyManager\LogDataPurger;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * An unused actions purge that stops early (SQLite) runs again on the next scheduled run, without delaying or
 * repeating the old logs purge.
 *
 * @group PrivacyManager
 * @group Plugins
 */
class UnusedLogActionsPurgeScheduleTest extends IntegrationTestCase
{
    private const DAY = 86400;

    /**
     * @var array<int, array{bool, bool}> the unused actions and old logs flags of each purge
     */
    private $purges = [];

    /**
     * @var bool[] what each purge returns
     */
    private $results = [];

    public function setUp(): void
    {
        parent::setUp();

        PrivacyManager::savePurgeDataSettings(['delete_logs_enable' => 1, 'delete_logs_older_than' => 180]);
        Option::set(PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL, 1);

        $purger = $this->getMockBuilder(LogDataPurger::class)->disableOriginalConstructor()->onlyMethods(['purgeData'])->getMock();
        $purger->method('purgeData')->willReturnCallback(function ($olderThan, $deleteUnusedLogActions, $deleteOldLogs = true) {
            $this->purges[] = [$deleteUnusedLogActions, $deleteOldLogs];
            return array_shift($this->results) ?? true;
        });
        StaticContainer::getContainer()->set(LogDataPurger::class, $purger);
    }

    public function testAnIncompletePurgeKeepsItsIntervalOpen(): void
    {
        $actionsRun = $this->daysAgo(31);
        Option::set(PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS, $this->daysAgo(8));
        Option::set(PrivacyManager::OPTION_LAST_DELETE_UNUSED_LOG_ACTIONS, $actionsRun);
        $this->results = [false];

        $this->assertTrue((new PrivacyManager())->deleteLogData());

        $this->assertSame([[true, true]], $this->purges);
        $this->assertEquals($actionsRun, Option::get(PrivacyManager::OPTION_LAST_DELETE_UNUSED_LOG_ACTIONS));
        $this->assertEquals(1, Option::get(PrivacyManager::OPTION_UNUSED_LOG_ACTIONS_PURGE_INCOMPLETE));
        $this->assertEquals($this->today(), Option::get(PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS));
    }

    public function testAnIncompletePurgeRunsAgainAloneTheNextDay(): void
    {
        $actionsRun = $this->daysAgo(32);
        Option::set(PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS, $this->daysAgo(1));
        Option::set(PrivacyManager::OPTION_LAST_DELETE_UNUSED_LOG_ACTIONS, $actionsRun);
        Option::set(PrivacyManager::OPTION_UNUSED_LOG_ACTIONS_PURGE_INCOMPLETE, 1);
        $this->results = [false];

        $this->assertTrue((new PrivacyManager())->deleteLogData());

        $this->assertSame([[true, false]], $this->purges, 'the old logs purge is not due');
        $this->assertEquals($this->daysAgo(1), Option::get(PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS));
        $this->assertEquals($actionsRun, Option::get(PrivacyManager::OPTION_LAST_DELETE_UNUSED_LOG_ACTIONS));
        $this->assertEquals(2, Option::get(PrivacyManager::OPTION_UNUSED_LOG_ACTIONS_PURGE_INCOMPLETE));
    }

    public function testAPendingRetryDoesNotDelayADueOldLogsPurgeAndCompletingClearsIt(): void
    {
        Option::set(PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS, $this->daysAgo(7));
        Option::set(PrivacyManager::OPTION_LAST_DELETE_UNUSED_LOG_ACTIONS, $this->daysAgo(37));
        Option::set(PrivacyManager::OPTION_UNUSED_LOG_ACTIONS_PURGE_INCOMPLETE, 6);
        $this->results = [true];

        $this->assertTrue((new PrivacyManager())->deleteLogData());

        $this->assertSame([[true, true]], $this->purges);
        $this->assertEquals($this->today(), Option::get(PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS));
        $this->assertEquals($this->today(), Option::get(PrivacyManager::OPTION_LAST_DELETE_UNUSED_LOG_ACTIONS));
        $this->assertFalse(Option::get(PrivacyManager::OPTION_UNUSED_LOG_ACTIONS_PURGE_INCOMPLETE));
    }

    public function testNothingRunsWhenNothingIsDueOrPending(): void
    {
        Option::set(PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS, $this->daysAgo(1));
        Option::set(PrivacyManager::OPTION_LAST_DELETE_UNUSED_LOG_ACTIONS, $this->daysAgo(40));

        $this->assertFalse((new PrivacyManager())->deleteLogData());

        $this->assertSame([], $this->purges);
    }

    private function today(): int
    {
        return Date::factory('today')->getTimestamp();
    }

    private function daysAgo(int $days): int
    {
        return $this->today() - $days * self::DAY;
    }
}
