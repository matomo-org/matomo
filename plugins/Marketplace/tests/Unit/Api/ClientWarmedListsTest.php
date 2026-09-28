<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Unit\Api;

use Exception;
use Matomo\Cache\Backend\ArrayCache;
use Matomo\Cache\Lazy;
use PHPUnit\Framework\MockObject\MockObject;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Api\Service\Exception as ServiceException;
use Piwik\Plugins\Marketplace\BackgroundWarmer;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Client as ClientBuilder;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service as TestService;

/**
 * @group Plugins
 * @group Marketplace
 * @group ClientWarmedListsTest
 */
class ClientWarmedListsTest extends \PHPUnit\Framework\TestCase
{
    private const NOW = 1790000000;

    /**
     * @var TestService
     */
    private $service;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var BackgroundWarmer&MockObject
     */
    private $warmer;

    /**
     * @var int
     */
    private $requestCount = 0;

    /**
     * @var int
     */
    private $responseCount = 0;

    /**
     * @var Exception|null
     */
    private $nextFailure;

    /**
     * @var string|null Only lists of this purchase type fail, or every one when null.
     */
    private $failingPurchaseType;

    /**
     * @var int Fetches tried, including the ones that failed.
     */
    private $attemptCount = 0;

    /**
     * @var array[] The context of each warning logged.
     */
    private $warnings = [];

    public function setUp(): void
    {
        Date::$now = self::NOW;

        $this->service = new TestService();
        $this->service->setOnFetchCallback(function ($action, $params) {
            $this->attemptCount++;

            if ($this->nextFailure && in_array($this->failingPurchaseType, [null, $params['purchase_type']], true)) {
                throw $this->nextFailure;
            }

            $this->requestCount++;
            $this->responseCount++;

            return ['plugins' => [['name' => 'Fetched' . $this->responseCount]]];
        });

        $logger = $this->createMock(LoggerInterface::class);
        $logger->method('warning')->willReturnCallback(function ($message, array $context = []) {
            $this->warnings[] = $context;
        });

        $this->client = ClientBuilder::build($this->service, new Lazy(new ArrayCache()), $logger);
        $this->warmer = $this->createMock(BackgroundWarmer::class);
        $this->client->setBackgroundWarmer($this->warmer);

        $this->client->refreshOverviewListCaches();
        $this->requestCount = 0;
        $this->attemptCount = 0;
    }

    public function tearDown(): void
    {
        Date::$now = null;
    }

    public function testAFreshListIsServedWithoutRefreshing()
    {
        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS - 1;

        $this->warmer->expects($this->never())->method('refreshNow');

        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(0, $this->requestCount);
    }

    public function testAVisitIsServedAStaleListAndRefreshesItInTheBackground()
    {
        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS;

        $this->warmer->method('isServingVisit')->willReturn(true);
        $this->warmer->expects($this->once())
            ->method('refreshNow')
            ->with(Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS)
            ->willReturn(true);

        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(0, $this->requestCount);
    }

    public function testAStaleListIsNotRefreshedOutsideAVisit()
    {
        // a console command or a scheduler run, which run at the same minutes on every installation
        Date::$now = self::NOW + Client::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS - 1;

        $this->warmer->method('isServingVisit')->willReturn(false);
        $this->warmer->expects($this->never())->method('refreshNow');

        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(0, $this->requestCount);
    }

    public function testWithoutBackgroundProcessesAVisitRefreshesAStaleListInTheRequest()
    {
        $this->warmer->method('isServingVisit')->willReturn(true);
        $this->warmer->method('refreshNow')->willReturn(false);

        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS - 1;
        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(0, $this->requestCount);

        // the three warmed lists took the first three responses
        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS;
        $this->assertListed('Fetched4', $this->readOverviewList());
        $this->assertSame(1, $this->requestCount);
    }

    public function testWithoutBackgroundProcessesAStaleListIsRefetchedInTheRequestOnlyOnceEveryFewMinutes()
    {
        $this->warmer->method('isServingVisit')->willReturn(true);
        $this->warmer->expects($this->exactly(2))->method('refreshNow')->willReturn(false);
        $this->nextFailure = new ServiceException('The Marketplace could not be reached');

        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS;
        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(1, $this->attemptCount);

        Date::$now += 299;
        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(1, $this->attemptCount);

        Date::$now += 1;
        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(2, $this->attemptCount);
    }

    public function testACachedOnlyReadNeverRefreshesInTheRequest()
    {
        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS;

        $this->warmer->method('isServingVisit')->willReturn(true);
        $this->warmer->method('refreshNow')->willReturn(false);

        $this->assertNotNull($this->client->findInCachedOverviewLists('Fetched1'));
        $this->assertSame(0, $this->requestCount);
    }

    public function testAVisitRefreshesAListInTheRequestOnceBackgroundRefreshesHaveEvidentlyFailed()
    {
        $this->warmer->method('isServingVisit')->willReturn(true);
        $this->warmer->method('refreshNow')->willReturn(true);

        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS - 1;
        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(0, $this->requestCount);

        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS;
        $this->assertListed('Fetched4', $this->readOverviewList());
        $this->assertSame(1, $this->requestCount);
    }

    public function testAnOverdueListIsRefetchedInTheRequestOnlyOnceEveryFewMinutes()
    {
        $this->warmer->method('isServingVisit')->willReturn(true);
        $this->warmer->method('refreshNow')->willReturn(true);
        $this->nextFailure = new ServiceException('The Marketplace could not be reached');

        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS;
        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(1, $this->attemptCount);

        Date::$now += 299;
        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(1, $this->attemptCount);

        Date::$now += 1;
        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(2, $this->attemptCount);

        $this->assertWarningsStayOffScreen(4);
    }

    public function testACachedOnlyReadNeverRefreshesAnOverdueListInTheRequest()
    {
        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS;

        $this->warmer->method('isServingVisit')->willReturn(true);
        $this->warmer->method('refreshNow')->willReturn(true);

        $this->assertNotNull($this->client->findInCachedOverviewLists('Fetched1'));
        $this->assertSame(0, $this->requestCount);
    }

    public function testAListIsRefreshedInTheRequestOnlyAfterAPeriodicRefreshShouldHaveLandedAndBeforeItExpires()
    {
        // the hourly check, then the periodic refresh's random delay of up to an hour
        $latestPeriodicRefresh = Client::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS + 3600 + 3599;

        $this->assertGreaterThan($latestPeriodicRefresh, Client::PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS);
        $this->assertLessThan(Client::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS, Client::PLUGIN_LIST_REFRESH_IN_REQUEST_AFTER_SECONDS);
    }

    public function testRefreshingTheListsSaysWhetherEveryOneWasRefreshed()
    {
        $this->assertTrue($this->client->tryRefreshOverviewListCaches());

        $this->nextFailure = new ServiceException('The Marketplace could not be reached');
        $this->failingPurchaseType = PurchaseType::TYPE_PAID;
        $this->assertFalse($this->client->tryRefreshOverviewListCaches());

        $this->assertWarningsStayOffScreen(1);
    }

    /**
     * @dataProvider getRefreshFailures
     */
    public function testAStaleListIsServedWhenRefreshingItInTheRequestFails(Exception $failure)
    {
        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS;

        $this->warmer->method('isServingVisit')->willReturn(true);
        $this->warmer->method('refreshNow')->willReturn(false);
        $this->nextFailure = $failure;

        $this->assertListed('Fetched1', $this->readOverviewList());
    }

    public function getRefreshFailures(): iterable
    {
        yield 'an error from the Marketplace' => [new ServiceException('The Marketplace returned an error')];
        yield 'no connection to it' => [new Exception('Error while connecting to: plugins.matomo.org')];
    }

    public function testAnUnreachableMarketplaceIsReportedAsItIsWhenNothingIsCached()
    {
        $this->client->clearAllCacheEntries();
        $failure = new Exception('Error while connecting to: plugins.matomo.org');
        $this->nextFailure = $failure;

        try {
            $this->readOverviewList();
            $this->fail('Expected the connection failure to be thrown');
        } catch (Exception $e) {
            $this->assertSame($failure, $e);
        }
    }

    public function testAStaleListIsStillServedWhenTheWarmerThrows()
    {
        Date::$now = self::NOW + Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS;

        $this->warmer->method('isServingVisit')->willThrowException(new Exception('No container'));

        $this->assertListed('Fetched1', $this->readOverviewList());
        $this->assertSame(0, $this->requestCount);
        $this->assertWarningsStayOffScreen(1);
    }

    public function testOverviewListsAgeIsThatOfTheOldestList()
    {
        Date::$now = self::NOW + 100;
        $this->client->refreshOverviewListCaches();

        $this->nextFailure = new ServiceException('The Marketplace could not be reached');
        $this->failingPurchaseType = PurchaseType::TYPE_PAID;
        Date::$now = self::NOW + 200;
        $this->client->refreshOverviewListCaches();
        $this->assertSame(100, $this->client->getOverviewListsAge());
    }

    public function testOverviewListsAgeIsNullWhenAnyListIsMissing()
    {
        $this->client->clearAllCacheEntries();
        $this->client->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);

        $this->assertNull($this->client->getOverviewListsAge());
    }

    private function readOverviewList(): array
    {
        return $this->client->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
    }

    /**
     * A warning reaching the screen writer is shown to whoever is browsing as a notification.
     */
    private function assertWarningsStayOffScreen(int $expectedCount): void
    {
        $this->assertCount($expectedCount, $this->warnings);

        foreach ($this->warnings as $context) {
            $this->assertTrue($context['ignoreInScreenWriter'] ?? false);
        }
    }

    private function assertListed(string $name, array $plugins): void
    {
        $this->assertSame([$name], array_column($plugins, 'name'));
    }
}
