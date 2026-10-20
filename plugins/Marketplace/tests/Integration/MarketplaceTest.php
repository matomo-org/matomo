<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Matomo\Cache\Backend\ArrayCache;
use Matomo\Cache\Lazy;
use Piwik\Access;
use Piwik\Cache;
use Piwik\CliMulti\CliPhp;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\BackgroundWarmer;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Request\AuthenticationToken;
use Piwik\Scheduler\Scheduler;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group MarketplaceTest
 * @group Plugins
 */
class MarketplaceTest extends IntegrationTestCase
{
    private $cacheKey = 'Marketplace_ExpiredPlugins';

    public function testCheckForUpdatesClearsTheInvalidLicensesCache(): void
    {
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $this->createMock(BackgroundWarmer::class));
        Cache::getEagerCache()->save($this->cacheKey, ['exceeded' => ['FooPlugin']]);

        (new Marketplace())->checkForUpdates();

        self::assertFalse(Cache::getEagerCache()->contains($this->cacheKey));
    }

    public function testCheckForUpdatesKeepsTheListsAndRefreshesThemAtOnce(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::never())->method('clearAllCacheEntries');
        $client->expects(self::once())->method('clearCacheEntriesExceptOverviewLists');
        $client->method('getOverviewListsAge')->willReturn(Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS);
        StaticContainer::getContainer()->set(Client::class, $client);

        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->method('keepSpawnHoldsThrough')->willReturnCallback(function (callable $flush) {
            $flush();
        });
        $warmer->expects(self::once())->method('refreshNow')->with(Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS)->willReturn(true);
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        (new Marketplace())->checkForUpdates();
    }

    public function testASecondCheckForUpdatesRightAfterTheFirstStartsNoSecondRefresh(): void
    {
        $cache = new Lazy(new ArrayCache());
        $client = $this->createMock(Client::class);
        $client->method('clearCacheEntriesExceptOverviewLists')->willReturnCallback([$cache, 'flushAll']);
        $client->method('getOverviewListsAge')->willReturn(null);
        StaticContainer::getContainer()->set(Client::class, $client);

        $warmer = $this->getMockBuilder(BackgroundWarmer::class)
            ->setConstructorArgs([$cache, StaticContainer::get(CliPhp::class), StaticContainer::get(Scheduler::class), new NullLogger()])
            ->onlyMethods(['canSpawn', 'execute'])
            ->getMock();
        $warmer->method('canSpawn')->willReturn(true);
        $warmer->expects(self::once())->method('execute');
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        (new Marketplace())->checkForUpdates();
        (new Marketplace())->checkForUpdates();
    }

    public function testCheckForUpdatesDoesNothingWithoutTheUsersToken(): void
    {
        $token = $this->createMock(AuthenticationToken::class);
        $token->method('getAuthToken')->willReturn('not-the-users-token');
        StaticContainer::getContainer()->set(AuthenticationToken::class, $token);

        $client = $this->createMock(Client::class);
        $client->expects(self::never())->method('clearCacheEntriesExceptOverviewLists');
        StaticContainer::getContainer()->set(Client::class, $client);

        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::never())->method('refreshNow');
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        (new Marketplace())->checkForUpdates();
    }

    public function testCheckForUpdatesDoesNothingWithoutAdminAccess(): void
    {
        Access::getInstance()->setSuperUserAccess(false);

        $client = $this->createMock(Client::class);
        $client->expects(self::never())->method('clearCacheEntriesExceptOverviewLists');
        StaticContainer::getContainer()->set(Client::class, $client);

        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::never())->method('refreshNow');
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        (new Marketplace())->checkForUpdates();
    }

    public function testCheckForUpdatesStartsNoRefreshWhileTheListsAreFresh(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('getOverviewListsAge')->willReturn(Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS - 1);
        StaticContainer::getContainer()->set(Client::class, $client);

        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::never())->method('refreshNow');
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        (new Marketplace())->checkForUpdates();
    }

    public function testAFailedWarmDoesNotInterruptTheUpdateCheck(): void
    {
        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::once())->method('refreshNow')->willThrowException(new \Error('Cannot spawn'));
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);

        Cache::getEagerCache()->save($this->cacheKey, ['exceeded' => ['FooPlugin']]);

        (new Marketplace())->checkForUpdates();

        self::assertFalse(Cache::getEagerCache()->contains($this->cacheKey));
    }

    public function testFinishingTheInstallationWarmsTheListsAtOnce(): void
    {
        // posting the event itself would also run other plugins' installation handlers
        self::assertSame('warmCacheAfterInstallation', (new Marketplace())->registerEvents()['Installation.defaultSettingsForm.submit']);

        $calls = [];
        $environment = $this->createMock(Environment::class);
        $environment->expects(self::once())->method('getWebPhpVersion')->willReturnCallback(function () use (&$calls) {
            $calls[] = 'getWebPhpVersion';

            return '8.4.0';
        });
        StaticContainer::getContainer()->set(Environment::class, $environment);

        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::once())->method('refreshAfterInstallation')->willReturnCallback(function () use (&$calls) {
            $calls[] = 'refreshAfterInstallation';
        });
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        (new Marketplace())->warmCacheAfterInstallation();

        self::assertSame(['getWebPhpVersion', 'refreshAfterInstallation'], $calls);
    }

    public function testAFailedWarmDoesNotInterruptTheInstallation(): void
    {
        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::once())->method('refreshAfterInstallation')->willThrowException(new \Error('Cannot spawn'));
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        (new Marketplace())->warmCacheAfterInstallation();
    }

    public function testAnUpdateWarmsTheListsOnceTheRequestEnds(): void
    {
        // posting the event itself would also run other plugins' update handlers
        self::assertSame('warmCacheAfterUpdate', (new Marketplace())->registerEvents()['CoreUpdater.update.end']);

        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::never())->method('refreshAfterUpdate');
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        $deferred = new \ArrayObject();
        $this->buildDeferringPlugin($deferred)->warmCacheAfterUpdate();
        self::assertCount(1, $deferred);

        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::once())->method('refreshAfterUpdate')->with(Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS);
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        $deferred[0]();
    }

    public function testTheUpdatesTheInstallerRunsDoNotWarmTheLists(): void
    {
        Config::getInstance()->General['installation_in_progress'] = 1;

        $deferred = new \ArrayObject();
        $this->buildDeferringPlugin($deferred)->warmCacheAfterUpdate();

        self::assertCount(0, $deferred);
    }

    public function testAFailedWarmDoesNotInterruptTheUpdate(): void
    {
        $warmer = $this->createMock(BackgroundWarmer::class);
        $warmer->expects(self::once())->method('refreshAfterUpdate')->willThrowException(new \Error('Cannot spawn'));
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $warmer);

        $logger = $this->createMock(LoggerInterface::class);
        // the updater can run in the browser, where the screen writer would show it as a notification
        $logger->expects(self::once())->method('warning')
            ->with(self::anything(), self::callback(function (array $context): bool {
                return true === ($context['ignoreInScreenWriter'] ?? false);
            }));
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);

        $deferred = new \ArrayObject();
        $this->buildDeferringPlugin($deferred)->warmCacheAfterUpdate();

        // by shutdown the container may be gone, so the failure must not need to look anything up
        StaticContainer::getContainer()->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $deferred[0]();
    }

    private function buildDeferringPlugin(\ArrayObject $deferred): Marketplace
    {
        return new class ($deferred) extends Marketplace {
            private $deferred;

            public function __construct(\ArrayObject $deferred)
            {
                parent::__construct('Marketplace');
                $this->deferred = $deferred;
            }

            protected function deferToEndOfRequest(callable $callback): void
            {
                $this->deferred[] = $callback;
            }
        };
    }
}
