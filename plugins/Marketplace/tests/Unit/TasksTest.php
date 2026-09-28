<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Unit;

use ArrayObject;
use Exception;
use Matomo\Cache\Backend\ArrayCache;
use Matomo\Cache\Lazy;
use PHPUnit\Framework\MockObject\MockObject;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;
use Piwik\Plugins\Marketplace\Api\Client as ApiClient;
use Piwik\Plugins\Marketplace\BackgroundWarmer;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\Plugins\Marketplace\Tasks;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Client as ClientBuilder;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service as TestService;
use Piwik\Plugins\Marketplace\UpdateCommunication;

/**
 * @group Plugins
 * @group Marketplace
 * @group TasksTest
 */
class TasksTest extends \PHPUnit\Framework\TestCase
{
    private const NOW = 1790000000;

    /**
     * @var TestService
     */
    private $service;

    /**
     * @var ApiClient
     */
    private $api;

    /**
     * @var BackgroundWarmer&MockObject
     */
    private $warmer;

    /**
     * @var Tasks
     */
    private $tasks;

    public function setUp(): void
    {
        Date::$now = self::NOW;

        $this->service = new TestService();
        $this->api = ClientBuilder::build($this->service, new Lazy(new ArrayCache()));
        $this->warmer = $this->createMock(BackgroundWarmer::class);
        $this->api->setBackgroundWarmer($this->warmer);
        $this->tasks = $this->buildTasks($this->api, new NullLogger());
    }

    public function tearDown(): void
    {
        Date::$now = null;
    }

    public function testWarmCacheEntriesSchedulesARefillWithoutRequestingAnythingItself()
    {
        $requests = $this->recordRequests();

        $this->warmer->expects($this->once())
            ->method('refreshPeriodically')
            ->with(ApiClient::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS)
            ->willReturn(true);

        $this->tasks->warmCacheEntries();

        $this->assertSame([], $requests->getArrayCopy());
    }

    public function testWarmCacheEntriesLeavesListsAloneUntilTheyReachThePeriodicAge()
    {
        $this->api->refreshOverviewListCaches();
        Date::$now = self::NOW + ApiClient::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS - 1;

        $this->warmer->expects($this->never())->method('refreshPeriodically');

        $this->tasks->warmCacheEntries();
    }

    public function testWarmCacheEntriesSchedulesARefillOnceTheListsReachThePeriodicAge()
    {
        $this->api->refreshOverviewListCaches();
        Date::$now = self::NOW + ApiClient::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS;

        $this->warmer->expects($this->once())->method('refreshPeriodically')->willReturn(true);
        $requests = $this->recordRequests();

        $this->tasks->warmCacheEntries();

        $this->assertSame([], $requests->getArrayCopy());
    }

    public function testWarmCacheEntriesRefillsTheListsItselfWhereNoBackgroundRefillCanBeSpawned()
    {
        $this->api->refreshOverviewListCaches();
        Date::$now = self::NOW + ApiClient::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS;
        $requests = $this->recordRequests();

        $this->warmer->method('refreshPeriodically')->willReturn(false);

        $this->tasks->warmCacheEntries();

        $this->assertNotEmpty($requests);
        $this->assertSame(0, $this->api->getOverviewListsAge());
    }

    public function testWarmCacheEntriesRefillsTheListsItselfOnceABackgroundRefillEvidentlyFailed()
    {
        $this->api->refreshOverviewListCaches();
        Date::$now = self::NOW + ApiClient::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS;
        $requests = $this->recordRequests();

        $this->warmer->method('claimFailedDelayedRefresh')->willReturn(true);
        $this->warmer->expects($this->never())->method('refreshPeriodically');

        $this->tasks->warmCacheEntries();

        $this->assertNotEmpty($requests);
        $this->assertSame(0, $this->api->getOverviewListsAge());
    }

    public function testWarmCacheEntriesDoesNotFailTheScheduledRunWhenCheckingThrows()
    {
        $api = $this->createMock(ApiClient::class);
        $api->method('getOverviewListsAge')
            ->willThrowException(new Exception('The cache could not be read'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Could not warm the Marketplace cache'));

        $this->buildTasks($api, $logger)->warmCacheEntries();
    }

    public function testWarmCacheEntriesDoesNotFailTheScheduledRunOnAnError()
    {
        $api = $this->createMock(ApiClient::class);
        $api->method('getOverviewListsAge')
            ->willThrowException(new \TypeError('Cannot access offset of type string on string'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $this->buildTasks($api, $logger)->warmCacheEntries();
    }

    public function testTheDailyFlushKeepsTheOverviewListsAndClearsTheRest()
    {
        $this->api->refreshOverviewListCaches();
        $this->api->getPluginInfo('AnyPlugin');
        Date::$now = self::NOW + 100;
        $requests = $this->recordRequests();

        $this->warmer->expects($this->never())->method('refreshPeriodically');

        $this->tasks->clearAllCacheEntries();

        $this->assertSame([], $requests->getArrayCopy());
        $this->assertSame(100, $this->api->getOverviewListsAge());

        $this->api->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
        $this->api->getPluginInfo('AnyPlugin');
        $this->assertSame([['plugins/AnyPlugin/info', null]], $requests->getArrayCopy());
    }

    public function testTheDailyFlushDropsAnOverviewListThatHasOutlivedItsTimeout()
    {
        $this->api->refreshOverviewListCaches();
        Date::$now = self::NOW + ApiClient::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS;

        $this->tasks->clearAllCacheEntries();

        $this->assertNull($this->api->getOverviewListsAge());
    }

    private function buildTasks(ApiClient $api, LoggerInterface $logger): Tasks
    {
        return new Tasks($this->createMock(UpdateCommunication::class), $api, $this->warmer, $logger);
    }

    /**
     * Collects the action and purchase type of each list the service is asked for, as it is asked.
     */
    private function recordRequests(): ArrayObject
    {
        $requests = new ArrayObject();

        $this->service->setOnFetchCallback(function ($action, $params) use ($requests) {
            $requests[] = [$action, $params['purchase_type'] ?? null];
        });

        return $requests;
    }
}
