<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\System\Api;

use Piwik\Cache;
use Piwik\Http;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
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
        $plugins = $this->client->searchForPlugins($keywords = '', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_ALL);

        $this->assertGreaterThan(15, count($plugins));

        foreach ($plugins as $plugin) {
            $this->assertNotEmpty($plugin['name']);
            $this->assertFalse($plugin['isTheme']);
        }
    }

    public function testSearchForPluginsOnlyFree()
    {
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
        $plugins = $this->client->searchForPlugins($keywords = 'login', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_ALL);

        $this->assertGreaterThanOrEqual(1, count($plugins));
        $this->assertLessThan(30, count($plugins));

        foreach ($plugins as $plugin) {
            self::assertContains($keywords, $plugin['keywords']);
        }
    }

    public function testSearchForThemesRequestAll()
    {
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
        $url = $this->client->getDownloadUrl('SecurityInfo');

        $start = $this->domain . '/api/2.0/plugins/SecurityInfo/download/';
        $end   = '?coreVersion=' . Version::VERSION;

        $this->assertStringStartsWith($start, $url);
        $this->assertStringEndsWith($end, $url);

        $version = str_replace(array($start, $end), '', $url);

        $this->assertNotEmpty($version);
        $this->assertRegExp('/\d+\.\d+\.\d+/', $version);
    }

    public function testGetDownloadUrlMissingLicense()
    {
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
            'num_websites' => $this->environment->getNumWebsites()
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
            'num_websites' => $this->environment->getNumWebsites());
        $id = 'marketplace.api.2.0.plugins.' . md5(Http::buildQuery($params));

        $cache = $this->getCache();
        $cache->save($id, array('plugins' => array(array('name' => 'foobar'))));

        $result = $this->client->searchForPlugins($keywords = 'login', $query = '', $sort = '', $purchaseType = PurchaseType::TYPE_ALL);

        $this->assertSame(array(array('name' => 'foobar')), $result);
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
        $this->assertSame(array('plugins', 'release_channel', 'prefer_stable', 'piwik', 'php', 'mysql', 'num_users', 'num_websites'), array_keys($service->params));

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
