<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\Installation\FormDefaultSettings;
use Piwik\Plugins\Marketplace\CacheWarmer;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Plugins\Marketplace\Tasks;
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

    public function setUp(): void
    {
        parent::setUp();

        SpyCacheWarmer::$warmSoonCalls = 0;
        SpyCacheWarmer::$builds = 0;
    }

    public function tearDown(): void
    {
        unset(Config::getInstance()->General['installation_in_progress']);

        parent::tearDown();
    }

    public function testCheckForUpdatesClearsTheInvalidLicensesCache(): void
    {
        Cache::getEagerCache()->save($this->cacheKey, ['exceeded' => ['FooPlugin']]);

        (new Marketplace())->checkForUpdates();

        self::assertFalse(Cache::getEagerCache()->contains($this->cacheKey));
    }

    public function testFinishingAnUpdateMarksTheWarmTaskDueWithoutBuildingTheWarmer(): void
    {
        $this->taskIsNotDue();

        Piwik::postEvent('CoreUpdater.update.end');

        self::assertTrue((new Timetable())->shouldExecuteTask(self::WARM_CACHE_TASK));

        // the warmer must not be built from inside the updater: doing so moved PrivacyManager's
        // anonymisation settings in NoVisitTest, so the update path marks the task due instead
        self::assertSame(0, SpyCacheWarmer::$builds, 'the warmer was constructed inside the updater');
        self::assertSame(0, SpyCacheWarmer::$warmSoonCalls);
    }

    public function testFinishingAnUpdateDoesNotWarmWhileAnInstallationIsStillInProgress(): void
    {
        // an installation runs the updater before the first site and user exist, and both counts
        // are part of every Marketplace cache key, so warming there fills entries nothing reads
        Config::getInstance()->General['installation_in_progress'] = 1;
        $this->taskIsNotDue();

        Piwik::postEvent('CoreUpdater.update.end');

        self::assertFalse((new Timetable())->shouldExecuteTask(self::WARM_CACHE_TASK));
    }

    public function testFinishingAnInstallationWarmsTheMarketplaceCache(): void
    {
        Config::getInstance()->General['installation_in_progress'] = 1;

        // carries the settings form the real event does, which this handler has no use for but the
        // other plugins listening to the same event require
        Piwik::postEvent('Installation.defaultSettingsForm.submit', [new FormDefaultSettings()]);

        self::assertSame(1, SpyCacheWarmer::$warmSoonCalls);
    }

    public function provideContainerConfig()
    {
        return [
            // a factory, not an instance: the regression here is the warmer being *built* inside
            // the updater, and handing over a ready-made object would make that free and invisible
            CacheWarmer::class => \Piwik\DI::factory(static function () {
                return new SpyCacheWarmer();
            }),
        ];
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
