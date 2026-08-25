<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\System\Api;

use Matomo\Cache\Lazy;
use Piwik\Cache;
use Piwik\Http;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Version;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service as TestService;
use Piwik\Log\NullLogger;

/**
 * @group Plugins
 * @group Marketplace
 * @group ClientTest
 * @group Client
 */
class ClientTest extends SystemTestCase
{
    private $domain = 'http://plugins.piwik.org';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Environment
     */
    private $environment;

    public function setUp(): void
    {
        $releaseChannels = new Plugin\ReleaseChannels(Plugin\Manager::getInstance());
        $this->environment = new Environment($releaseChannels);

        $this->client = $this->buildClient();
        $this->getCache()->flushAll();
    }

    public function testGetPluginInfoExistingPluginOnTheMarketplace()
    {
        $this->markTestSkipped('Skipped until Matomo 6 compatible plugins are published on the Marketplace.');

        $plugin = $this->client->getPluginInfo('SecurityInfo');

        $expectedPluginKeys = array(
            'name',
            'displayName',
            'owner',
            'description',
            'homepage',
            'createdDateTime',
            'donate',
            'support',
            'isTheme',
            'keywords',
            'basePrice',
            'authors',
            'repositoryUrl',
            'lastUpdated',
            'latestVersion',
            'numDownloads',
            'screenshots',
            'coverImage',
            'previews',
            'activity',
            'featured',
            'isFree',
            'isPaid',
            'isBundle',
            'isCustomPlugin',
            'shop',
            'bundle',
            'specialOffer',
            'category',
            'versions',
            'isDownloadable',
            'changelog',
            'consumer');

        $this->assertNotEmpty($plugin);
        $this->assertEquals($expectedPluginKeys, array_keys($plugin));
        $this->assertSame('SecurityInfo', $plugin['name']);
        $this->assertSame('matomo-org', $plugin['owner']);
        $this->assertTrue(is_array($plugin['keywords']));
        $this->assertNotEmpty($plugin['authors']);
        $this->assertGreaterThan(1000, $plugin['numDownloads']);
        $this->assertTrue($plugin['isFree']);
        $this->assertFalse($plugin['isPaid']);
        $this->assertFalse($plugin['isCustomPlugin']);
        $this->assertNotEmpty($plugin['versions']);
        $this->assertNotEmpty($plugin['coverImage']);
        $this->assertNotEmpty($plugin['category']);

        $lastVersion = $plugin['versions'][count($plugin['versions']) - 1];
        $this->assertEquals(
            array('name', 'release', 'requires', 'wordPressCompatible', 'onPremiseCompatible', 'numDownloads', 'license', 'repositoryChangelogUrl', 'readmeHtml', 'download'),
            array_keys($lastVersion)
        );
        $this->assertNotEmpty($lastVersion['download']);
    }

    public function testGetPluginInfoShouldThrowExceptionIfPluginDoesNotExistOnMarketplace()
    {
        $this->expectException(\Piwik\Plugins\Marketplace\Api\Exception::class);
        $this->expectExceptionMessage('Requested plugin does not exist.');

        $this->client->getPluginInfo('NotExistingPlugIn');
    }

    public function testThePluginListIsCachedForLongerThanTheTaskRefillsIt()
    {
        // browsing data, which the Marketplace itself serves with an eight day max-age. It has to
        // outlive the interval Tasks::warmCacheEntries() refills it at or the warming is moot.
        $ttls = $this->recordCacheTimeouts('v2.0_plugins.json', function (Client $client) {
            $client->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
        });

        self::assertSame([Client::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS], $ttls);
    }

    public function testASearchIsNotHeldForTheLongerTimeout()
    {
        // nothing warms a search, so a longer timeout would only make it staler
        $ttls = $this->recordCacheTimeouts('v2.0_plugins.json', function (Client $client) {
            $client->searchForPlugins('', 'some query', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
        });

        self::assertSame([Client::CACHE_TIMEOUT_IN_SECONDS], $ttls);
    }

    public function testThePaidPluginListIsCachedForLongerThanTheTaskRefillsIt()
    {
        // the premium filter is its own cache entry and the task warms it too
        $ttls = $this->recordCacheTimeouts('v2.0_plugins.json', function (Client $client) {
            $client->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_PAID);
        });

        self::assertSame([Client::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS], $ttls);
    }

    public function testAPurchaseTypeNothingWarmsIsNotHeldForTheLongerTimeout()
    {
        // no task refills the free-only list, so it would go stale without ever being warm
        $ttls = $this->recordCacheTimeouts('v2.0_plugins.json', function (Client $client) {
            $client->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_FREE);
        });

        self::assertSame([Client::CACHE_TIMEOUT_IN_SECONDS], $ttls);
    }

    public function testTheThemeListIsCachedForLongerOnlyForThePurchaseTypeTheTaskWarms()
    {
        $warmed = $this->recordCacheTimeouts('v2.0_themes.json', function (Client $client) {
            $client->searchForThemes('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
        });
        $unwarmed = $this->recordCacheTimeouts('v2.0_themes.json', function (Client $client) {
            $client->searchForThemes('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_PAID);
        });

        self::assertSame([Client::PLUGIN_LIST_CACHE_TIMEOUT_IN_SECONDS], $warmed);
        self::assertSame([Client::CACHE_TIMEOUT_IN_SECONDS], $unwarmed);
    }

    public function testANonDefaultSortIsNotHeldForTheLongerTimeout()
    {
        $ttls = $this->recordCacheTimeouts('v2.0_plugins.json', function (Client $client) {
            $client->searchForPlugins('', '', Sort::METHOD_ALPHA, PurchaseType::TYPE_ALL);
        });

        self::assertSame([Client::CACHE_TIMEOUT_IN_SECONDS], $ttls);
    }

    public function testPluginInfoKeepsTheShortCacheTimeout()
    {
        // a plugin's own details back the update rows and the details modal, so they stay fresh
        $ttls = $this->recordCacheTimeouts('v2.0_plugins_TreemapVisualization_info.json', function (Client $client) {
            $client->getPluginInfo('TreemapVisualization');
        });

        self::assertSame([Client::CACHE_TIMEOUT_IN_SECONDS], $ttls);
    }

    /**
     * Runs the given call against an always-empty cache and returns the timeouts it saved with.
     *
     * @return array<int, int>
     */
    private function recordCacheTimeouts(string $fixture, callable $call): array
    {
        $savedTtls = [];

        $cache = $this->createMock(Lazy::class);
        $cache->method('fetch')->willReturn(false);
        $cache->method('save')->willReturnCallback(
            function ($id, $content, $ttl) use (&$savedTtls) {
                $savedTtls[] = $ttl;
                return true;
            }
        );

        $service = new TestService($this->domain);
        $service->returnFixture($fixture);

        $call(new Client($service, $cache, new NullLogger(), $this->environment));

        return $savedTtls;
    }

    public function testConsumerEndpointsAreNotRequestedWithoutALicenseKey()
    {
        $service = new TestService($this->domain);
        $client = $this->buildClient($service);

        $apis = [];
        $service->setOnFetchCallback(function ($action) use (&$apis) {
            $apis[] = $action;
        });

        $this->assertNull($client->getConsumer());
        $this->assertFalse($client->isValidConsumer());

        // consumer answers 403 without a token and consumer/validate can only say false, so asking
        // is latency the majority of installs pay for nothing
        $this->assertSame([], $apis);
    }

    public function testGetConsumerShouldReturnNullAndNotThrowExceptionIfNotAuthorized()
    {
        $this->assertNull($this->client->getConsumer());
    }

    public function testIsValidConsumerShouldReturnFalseAndNotThrowExceptionIfNotAuthorized()
    {
        $this->assertFalse($this->client->isValidConsumer());
    }

    public function testSearchForPluginsRequestAll()
    {
        $this->markTestSkipped('Skipped until Matomo 6 compatible plugins are published on the Marketplace.');

        $plugins = $this->client->searchForPlugins($keywords = '', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_ALL);

        $this->assertGreaterThan(15, count($plugins));

        foreach ($plugins as $plugin) {
            $this->assertNotEmpty($plugin['name']);
            $this->assertFalse($plugin['isTheme']);
        }
    }

    public function testSearchForPluginsOnlyFree()
    {
        $this->markTestSkipped('Skipped until Matomo 6 compatible plugins are published on the Marketplace.');

        $plugins = $this->client->searchForPlugins($keywords = '', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_FREE);

        $this->assertGreaterThan(15, count($plugins));

        foreach ($plugins as $plugin) {
            $this->assertTrue($plugin['isFree']);
            $this->assertFalse($plugin['isPaid']);
            $this->assertFalse($plugin['isTheme']);
        }
    }

    public function testSearchForPluginsOnlyPaid()
    {
        $plugins = $this->client->searchForPlugins($keywords = '', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_PAID);

        $this->assertGreaterThanOrEqual(1, count($plugins));
        $this->assertLessThan(30, count($plugins));

        foreach ($plugins as $plugin) {
            $this->assertFalse($plugin['isFree']);
            $this->assertTrue($plugin['isPaid']);
            $this->assertFalse($plugin['isTheme']);
        }
    }

    public function testSearchForPluginsWithKeyword()
    {
        $this->markTestSkipped('Skipped until Matomo 6 compatible plugins are published on the Marketplace.');

        $plugins = $this->client->searchForPlugins($keywords = 'login', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_ALL);

        $this->assertGreaterThanOrEqual(1, count($plugins));
        $this->assertLessThan(30, count($plugins));

        foreach ($plugins as $plugin) {
            self::assertContains($keywords, $plugin['keywords']);
        }
    }

    public function testSearchForThemesRequestAll()
    {
        $this->markTestSkipped('Skipped until Matomo 6 compatible plugins are published on the Marketplace.');

        $plugins = $this->client->searchForThemes($keywords = '', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_ALL);

        $this->assertGreaterThanOrEqual(1, count($plugins));
        $this->assertLessThan(50, count($plugins));

        foreach ($plugins as $plugin) {
            $this->assertNotEmpty($plugin['name']);
            $this->assertTrue($plugin['isTheme']);
        }
    }

    public function testGetDownloadUrl()
    {
        $this->markTestSkipped('Skipped until Matomo 6 compatible plugins are published on the Marketplace.');

        $url = $this->client->getDownloadUrl('SecurityInfo');

        $start = $this->domain . '/api/2.0/plugins/SecurityInfo/download/';

        $this->assertStringStartsWith($start, $url);
        $this->assertStringContainsString('?coreVersion=' . Version::VERSION, $url);
        $this->assertStringContainsString('&uid=', $url);

        $version = str_replace($start, '', $url);
        $version = substr($version, 0, strpos($version, '?'));

        $this->assertNotEmpty($version);
        $this->assertMatchesRegularExpression('/\d+\.\d+\.\d+/', $version);
    }

    public function testGetDownloadUrlMissingLicense()
    {
        $this->markTestSkipped('Skipped until Matomo 6 compatible plugins are published on the Marketplace.');

        $this->expectException(\Piwik\Plugins\Marketplace\Api\Exception::class);
        $this->expectExceptionMessage('Plugin is not downloadable');

        $this->client->getDownloadUrl('FormAnalytics');
    }

    public function testClientResponseShouldBeCached()
    {
        $params = array(
            'keywords' => 'login',
            'purchase_type' => '',
            'query' => '',
            'sort' => '',
            'release_channel' => 'latest_stable',
            'prefer_stable' => 1,
            'piwik' => Version::VERSION,
            'php' => $this->environment->getPhpVersion(),
            'mysql' => $this->environment->getMySQLVersion(),
            'num_users' => $this->environment->getNumUsers(),
            'num_websites' => $this->environment->getNumWebsites(),
            'uid' => $this->environment->getUniqueId(),
        );
        $id = 'marketplace.api.2.0.plugins.' . md5(Http::buildQuery($params));

        $cache = $this->getCache();
        $this->assertFalse($cache->contains($id));

        $this->client->searchForPlugins($keywords = 'login', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_ALL);

        $this->assertTrue($cache->contains($id));
        $cachedPlugins = $cache->fetch($id);

        self::assertIsArray($cachedPlugins);
        $this->assertNotEmpty($cachedPlugins);
        $this->assertGreaterThan(30, $cachedPlugins);
    }

    public function testCachedClientResponseShouldBeReturned()
    {
        $params = array(
            'keywords' => 'login',
            'purchase_type' => '',
            'query' => '',
            'sort' => '',
            'release_channel' => 'latest_stable',
            'prefer_stable' => 1,
            'piwik' => Version::VERSION,
            'php' => $this->environment->getPhpVersion(),
            'mysql' => $this->environment->getMySQLVersion(),
            'num_users' => $this->environment->getNumUsers(),
            'num_websites' => $this->environment->getNumWebsites(),
            'uid' => $this->environment->getUniqueId());
        $id = 'marketplace.api.2.0.plugins.' . md5(Http::buildQuery($params));

        $cache = $this->getCache();
        $cache->save($id, array('plugins' => array(array('name' => 'foobar'))));

        $result = $this->client->searchForPlugins($keywords = 'login', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_ALL);

        $this->assertSame(array(array('name' => 'foobar')), $result);
    }

    public function testGetUpdateSummariesOfPluginsHavingUpdateIssuesOneRequestOnly()
    {
        $service = new TestService($this->domain);
        $client = $this->buildClient($service);

        $apis = [];
        $service->setOnFetchCallback(function ($action) use (&$apis) {
            $apis[] = $action;
        });

        $client->getUpdateSummariesOfPluginsHavingUpdate([$this->loadPluginForTest('CustomAlerts')]);

        $this->assertSame(['plugins/checkUpdates'], $apis);
    }

    private function loadPluginForTest(string $pluginName): Plugin
    {
        $pluginManager = Plugin\Manager::getInstance();

        if (!$pluginManager->isPluginLoaded($pluginName)) {
            return $pluginManager->loadPlugin($pluginName);
        }

        return $pluginManager->getLoadedPlugin($pluginName);
    }

    public function testGetInfoOfPluginsHavingUpdate()
    {
        $service = new TestService($this->domain);
        $client = $this->buildClient($service);

        $pluginTest = array();
        if (!Plugin\Manager::getInstance()->isPluginLoaded('CustomAlerts')) {
            $pluginTest[] = Plugin\Manager::getInstance()->loadPlugin('CustomAlerts');
        } else {
            $pluginTest[] = Plugin\Manager::getInstance()->getLoadedPlugin('CustomAlerts');
        }

        $client->getInfoOfPluginsHavingUpdate($pluginTest);

        $this->assertSame('plugins/checkUpdates', $service->action);
        $this->assertSame(array('plugins', 'release_channel', 'prefer_stable', 'piwik', 'php', 'mysql', 'num_users', 'num_websites', 'uid'), array_keys($service->params));

        $plugins = $service->params['plugins'];
        self::assertIsString($plugins);
        $this->assertJson($plugins);
        $plugins = json_decode($plugins, true);

        $names = array(
            'CustomAlerts' => true,
        );
        foreach ($plugins['plugins'] as $plugin) {
            $this->assertNotEmpty($plugin['version']);
            unset($names[$plugin['name']]);
        }

        $this->assertEmpty($names);
    }

    private function buildClient($service = null)
    {
        if (!isset($service)) {
            $service = new Service($this->domain);
        }

        return new Client($service, $this->getCache(), new NullLogger(), $this->environment);
    }

    private function getCache()
    {
        return Cache::getLazyCache();
    }
}
