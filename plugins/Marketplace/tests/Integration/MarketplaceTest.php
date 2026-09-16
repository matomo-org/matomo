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
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group MarketplaceTest
 * @group Plugins
 */
class MarketplaceTest extends IntegrationTestCase
{
    private $cacheKey = 'Marketplace_ExpiredPlugins';

    public function setUp(): void
    {
        parent::setUp();

        SpyCacheWarmer::$warmSoonCalls = 0;
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

    public function testFinishingAnUpdateWarmsTheMarketplaceCache(): void
    {
        Piwik::postEvent('CoreUpdater.update.end');

        self::assertSame(1, SpyCacheWarmer::$warmSoonCalls);
    }

    public function testFinishingAnUpdateDoesNotWarmWhileAnInstallationIsStillInProgress(): void
    {
        // an installation runs the updater before the first site and user exist, and both counts
        // are part of every Marketplace cache key, so warming there fills entries nothing reads
        Config::getInstance()->General['installation_in_progress'] = 1;

        Piwik::postEvent('CoreUpdater.update.end');

        self::assertSame(0, SpyCacheWarmer::$warmSoonCalls);
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
            CacheWarmer::class => new SpyCacheWarmer(),
        ];
    }
}

/**
 * Counts the calls the plugin's event handlers make, without warming anything.
 */
class SpyCacheWarmer extends CacheWarmer
{
    public static $warmSoonCalls = 0;

    public function __construct()
    {
    }

    public function warmSoon(): void
    {
        self::$warmSoonCalls++;
    }
}
