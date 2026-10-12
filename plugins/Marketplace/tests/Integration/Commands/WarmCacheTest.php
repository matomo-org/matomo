<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Commands;

use PHPUnit\Framework\MockObject\MockObject;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\BackgroundWarmer;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group Plugins
 * @group Marketplace
 * @group WarmCacheTest
 */
class WarmCacheTest extends ConsoleCommandTestCase
{
    /**
     * @var Client&MockObject
     */
    private $client;

    /**
     * @var BackgroundWarmer&MockObject
     */
    private $warmer;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        StaticContainer::getContainer()->set(Client::class, $this->client);

        $this->warmer = $this->createMock(BackgroundWarmer::class);
        StaticContainer::getContainer()->set(BackgroundWarmer::class, $this->warmer);
    }

    public function testEveryRunIsRecordedEvenWhenItHasNothingToDoOrFails(): void
    {
        $this->warmer->expects(self::exactly(2))->method('recordRun');
        $this->client->method('getOverviewListsAge')->willReturnOnConsecutiveCalls(3599, null);
        $this->client->method('tryRefreshOverviewListCaches')->willReturn(false);

        self::assertSame(0, $this->runWarmCache(3600), $this->getCommandDisplayOutputErrorMessage());
        self::assertNotSame(0, $this->runWarmCache(3600));
    }

    public function testMissingListsAreRefreshed(): void
    {
        $this->client->method('getOverviewListsAge')->willReturn(null);
        $this->client->expects(self::once())->method('tryRefreshOverviewListCaches')->willReturn(true);

        self::assertSame(0, $this->runWarmCache(3600), $this->getCommandDisplayOutputErrorMessage());
    }

    public function testListsYoungerThanAskedForAreLeftAlone(): void
    {
        $this->client->method('getOverviewListsAge')->willReturn(3599);
        $this->client->expects(self::never())->method('tryRefreshOverviewListCaches');

        self::assertSame(0, $this->runWarmCache(3600), $this->getCommandDisplayOutputErrorMessage());
    }

    public function testListsAsOldAsAskedForAreRefreshed(): void
    {
        $this->client->method('getOverviewListsAge')->willReturn(3600);
        $this->client->expects(self::once())->method('tryRefreshOverviewListCaches')->willReturn(true);

        self::assertSame(0, $this->runWarmCache(3600), $this->getCommandDisplayOutputErrorMessage());
    }

    public function testFailsWhenAListCouldNotBeRefreshed(): void
    {
        $this->client->method('getOverviewListsAge')->willReturn(null);
        $this->client->method('tryRefreshOverviewListCaches')->willReturn(false);

        self::assertNotSame(0, $this->runWarmCache(0));
        self::assertStringContainsString('could not be refreshed', $this->applicationTester->getDisplay(true));
    }

    public function testADelayedRunFetchesNothingOnceInternetFeaturesAreOff(): void
    {
        // the plugin is not loaded without internet features, so its command is not there to run
        Config::getInstance()->General['enable_internet_features'] = 0;

        $this->client->expects(self::never())->method('tryRefreshOverviewListCaches');

        self::assertNotSame(0, $this->runWarmCache(0));
    }

    private function runWarmCache(int $ifOlderThan): int
    {
        return $this->applicationTester->run([
            'command' => 'marketplace:warm-cache',
            '--if-older-than' => $ifOlderThan,
        ]);
    }
}
