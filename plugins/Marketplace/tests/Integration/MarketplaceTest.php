<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\Installation\FormDefaultSettings;
use Piwik\Plugins\Marketplace\CacheWarmer;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Plugins\Marketplace\Tasks;
use Piwik\Scheduler\Scheduler;
use Piwik\Scheduler\Task;
use Piwik\Scheduler\Timetable;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group MarketplaceTest
 * @group Plugins
 */
class MarketplaceTest extends IntegrationTestCase
{
    private const WARM_CACHE_TASK = 'Piwik\\Plugins\\Marketplace\\Tasks.warmCacheEntries';

    private $cacheKey = 'Marketplace_ExpiredPlugins';

    private function taskIsNotDue(): void
    {
        (new Timetable())->rescheduleTaskAndRunTomorrow(Tasks::getWarmCacheEntriesTask());
    }

    /** @var bool|null */
    private $originalCliMode;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalCliMode = Common::$isCliMode;
        FailingScheduler::$failsToMarkDue = false;
        SpyCacheWarmer::$warmSoonCalls = 0;
        SpyCacheWarmer::$builds = 0;
    }

    public function tearDown(): void
    {
        Common::$isCliMode = $this->originalCliMode;
        unset(Config::getInstance()->General['installation_in_progress']);

        parent::tearDown();
    }

    public function testCheckForUpdatesClearsTheInvalidLicensesCache(): void
    {
        Cache::getEagerCache()->save($this->cacheKey, ['exceeded' => ['FooPlugin']]);

        (new Marketplace())->checkForUpdates();

        self::assertFalse(Cache::getEagerCache()->contains($this->cacheKey));
    }

    public function testFinishingAnUpdateFromTheCommandLineOnlyMarksTheWarmTaskDue(): void
    {
        Common::$isCliMode = true;
        $this->taskIsNotDue();

        Piwik::postEvent('CoreUpdater.update.end');

        self::assertTrue((new Timetable())->shouldExecuteTask(self::WARM_CACHE_TASK));

        // an unattended deployment has nobody waiting on a page, and a fleet of them spawning at
        // once would reach the Marketplace together, so each warms on its own next scheduler run
        self::assertSame(0, SpyCacheWarmer::$builds, 'the warmer was constructed inside the updater');
        self::assertSame(0, SpyCacheWarmer::$warmSoonCalls);
    }

    public function testFinishingAnUpdateInABrowserDefersTheWarmToTheEndOfTheRequest(): void
    {
        Common::$isCliMode = false;
        $this->taskIsNotDue();

        $plugin = new DeferringMarketplace();
        $plugin->warmCacheAfterUpdate();

        self::assertTrue((new Timetable())->shouldExecuteTask(self::WARM_CACHE_TASK));

        // the warmer must not be built from inside the updater: doing so moved PrivacyManager's
        // anonymisation settings in NoVisitTest, which is what the deferral is for
        self::assertSame(0, SpyCacheWarmer::$builds, 'the warmer was constructed inside the updater');
        self::assertCount(1, $plugin->deferred);

        call_user_func($plugin->deferred[0]);

        self::assertSame(1, SpyCacheWarmer::$builds);
        self::assertSame(1, SpyCacheWarmer::$warmSoonCalls);
    }

    public function testFinishingAnUpdateDoesNotWarmWhileAnInstallationIsStillInProgress(): void
    {
        // an installation runs the updater before the first site and user exist, and both counts
        // are part of every Marketplace cache key, so warming there fills entries nothing reads
        Config::getInstance()->General['installation_in_progress'] = 1;
        Common::$isCliMode = false;
        $this->taskIsNotDue();

        Piwik::postEvent('CoreUpdater.update.end');

        self::assertFalse((new Timetable())->shouldExecuteTask(self::WARM_CACHE_TASK));
        self::assertSame(0, SpyCacheWarmer::$builds);
    }

    public function testFinishingAnInstallationWarmsTheMarketplaceCache(): void
    {
        Config::getInstance()->General['installation_in_progress'] = 1;

        // carries the settings form the real event does, which this handler has no use for but the
        // other plugins listening to the same event require
        Piwik::postEvent('Installation.defaultSettingsForm.submit', [new FormDefaultSettings()]);

        self::assertSame(1, SpyCacheWarmer::$warmSoonCalls);
    }

    public function testFinishingAnUpdateInABrowserStillDefersTheWarmWhenTheTaskCannotBeMarkedDue(): void
    {
        Common::$isCliMode = false;
        FailingScheduler::$failsToMarkDue = true;
        $this->taskIsNotDue();

        $plugin = new DeferringMarketplace();
        $plugin->warmCacheAfterUpdate();

        // marking the task due is the fallback for the updates that cannot spawn, not a step the
        // spawn waits on, so an instance whose timetable write fails must still get the warm
        self::assertFalse((new Timetable())->shouldExecuteTask(self::WARM_CACHE_TASK));
        self::assertCount(1, $plugin->deferred);
    }

    public function provideContainerConfig()
    {
        return [
            // a factory, not an instance: the regression here is the warmer being *built* inside
            // the updater, and handing over a ready-made object would make that free and invisible
            CacheWarmer::class => \Piwik\DI::factory(static function () {
                return new SpyCacheWarmer();
            }),
            Scheduler::class => \Piwik\DI::autowire(FailingScheduler::class),
        ];
    }
}

/**
 * The real scheduler until a test asks it to fail, which is the only way to reach the update
 * handler's branch where the task cannot be marked due but the warm must still be spawned.
 */
class FailingScheduler extends Scheduler
{
    public static $failsToMarkDue = false;

    public function rescheduleTaskAndRunNow(Task $task)
    {
        if (self::$failsToMarkDue) {
            throw new \RuntimeException('the timetable could not be written');
        }

        parent::rescheduleTaskAndRunNow($task);
    }
}

/**
 * Holds what the update handler defers instead of registering it as a shutdown function, which a
 * test has no way of running or even seeing.
 */
class DeferringMarketplace extends Marketplace
{
    /** @var callable[] */
    public $deferred = [];

    protected function deferToEndOfRequest(callable $callback): void
    {
        $this->deferred[] = $callback;
    }
}

/**
 * Counts the calls the plugin's event handlers make, without warming anything.
 */
class SpyCacheWarmer extends CacheWarmer
{
    public static $warmSoonCalls = 0;

    public static $builds = 0;

    public function __construct()
    {
        self::$builds++;
    }

    public function warmSoon(): void
    {
        self::$warmSoonCalls++;
    }
}
