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
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;
use Piwik\Plugins\Marketplace\Api\Client as ApiClient;
use Piwik\Plugins\Marketplace\Api\Service\Exception as ServiceException;
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
    /**
     * @var TestService
     */
    private $service;

    /**
     * @var ApiClient
     */
    private $api;

    /**
     * @var Tasks
     */
    private $tasks;

    public function setUp(): void
    {
        $this->service = new TestService();
        $this->api = ClientBuilder::build($this->service, new Lazy(new ArrayCache()));
        $this->tasks = $this->buildTasks($this->api, new NullLogger());
    }

    public function testWarmCacheEntriesRefetchesEveryListTheOverviewPageReads()
    {
        $requests = $this->recordRequests();

        $this->tasks->warmCacheEntries();

        $this->assertSame($this->warmedLists(), $requests->getArrayCopy());
    }

    public function testWarmCacheEntriesKeepsRefillingAfterAListCannotBeFetched()
    {
        $requests = new ArrayObject();
        $this->service->setOnFetchCallback(function ($action, $params) use ($requests) {
            $requests[] = [$action, $params['purchase_type']];

            if (count($requests) === 1) {
                throw new ServiceException('The Marketplace could not be reached');
            }
        });

        $this->tasks->warmCacheEntries();

        $this->assertSame($this->warmedLists(), $requests->getArrayCopy());
    }

    public function testWarmCacheEntriesDoesNotFailTheScheduledRunWhenRefillingThrows()
    {
        $api = $this->createMock(ApiClient::class);
        $api->method('refreshOverviewListCaches')
            ->willThrowException(new Exception('The Marketplace could not be reached'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Could not warm the Marketplace cache'));

        $this->buildTasks($api, $logger)->warmCacheEntries();
    }

    public function testClearAllCacheEntriesRefillsTheListsItJustFlushed()
    {
        // nothing warms a search, so it is the entry that shows whether the flush happened at all
        $search = ['SEO', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_FREE];
        $this->api->searchForPlugins(...$search);
        $this->tasks->warmCacheEntries();

        $requests = $this->recordRequests();
        $this->tasks->clearAllCacheEntries();

        // flushing on its own left whoever opened the Marketplace next to pay for every request the
        // page needs, which is the slowest it ever is
        $this->assertSame($this->warmedLists(), $requests->getArrayCopy());

        // what the warming is for: the overview page's own list now costs no request
        $this->api->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
        $this->assertSame($this->warmedLists(), $requests->getArrayCopy());

        // whereas the search, which the flush also emptied and nothing refills, is fetched again
        $this->api->searchForPlugins(...$search);
        $this->assertSame(
            array_merge($this->warmedLists(), [['plugins', PurchaseType::TYPE_FREE]]),
            $requests->getArrayCopy()
        );
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function warmedLists(): array
    {
        return [
            ['plugins', PurchaseType::TYPE_ALL],
            ['plugins', PurchaseType::TYPE_PAID],
            ['themes', PurchaseType::TYPE_ALL],
        ];
    }

    private function buildTasks(ApiClient $api, LoggerInterface $logger): Tasks
    {
        return new Tasks($this->createMock(UpdateCommunication::class), $api, $logger);
    }

    /**
     * Collects the action and purchase type of each list the service is asked for, as it is asked.
     */
    private function recordRequests(): ArrayObject
    {
        $requests = new ArrayObject();

        $this->service->setOnFetchCallback(function ($action, $params) use ($requests) {
            $requests[] = [$action, $params['purchase_type']];
        });

        return $requests;
    }
}
