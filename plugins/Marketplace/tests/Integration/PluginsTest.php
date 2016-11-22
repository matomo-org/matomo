<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Plugins\Marketplace\API;
use Piwik\Plugins\Marketplace\Consumer;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\Plugins\Marketplace\Plugins;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Client;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Tests\Framework\Mock\ProfessionalServices\Advertising;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugin;

/**
 * @group Marketplace
 * @group PluginsTest
 * @group Plugins
 */
class PluginsTest extends IntegrationTestCase
{
    /**
     * @var Plugins
     */
    private $plugins;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Service
     */
    private $consumerService;

    public function setUp()
    {
        parent::setUp();

        API::unsetInstance();

        $this->service = new Service();
        $this->consumerService = new Service();

        $this->plugins = new Plugins(
            Client::build($this->service),
            new Consumer(Client::build($this->consumerService)),
            new Advertising()
        );
    }

    public function test_getAllAvailablePluginNames_noPluginsFound()
    {
        $pluginNames = $this->plugins->getAllAvailablePluginNames();
        $this->assertSame(array(), $pluginNames);
    }

    public function test_getAllAvailablePluginNames()
    {
        $this->service->returnFixture(array(
            'v2.0_themes.json', 'v2.0_plugins.json'
        ));
        $pluginNames = $this->plugins->getAllAvailablePluginNames();
        $expected = array (
            'AnotherBlackTheme',
            'Barometer',
            'Counter',
            'CustomAlerts',
            'CustomOptOut',
            'FeedAnnotation',
            'IPv6Usage',
            'LiveTab',
            'LoginHttpAuth',
            'page2images-visual-link',
            'PaidPlugin1',
            'ReferrersManager',
            'SecurityInfo',
            'TasksTimetable',
            'TreemapVisualization',
        );
        foreach ($expected as $name) {
            $this->assertContains($name, $pluginNames);
        }
    }

    public function test_getAvailablePluginNames_noPluginsFound()
    {
        $pluginNames = $this->plugins->getAvailablePluginNames($themesOnly = true);
        $this->assertSame(array(), $pluginNames);

        $pluginNames = $this->plugins->getAvailablePluginNames($themesOnly = false);
        $this->assertSame(array(), $pluginNames);
    }

    public function test_getAvailablePluginNames_shouldReturnPluginNames()
    {
        $this->service->returnFixture('v2.0_themes.json');
        $pluginNames = $this->plugins->getAvailablePluginNames($themesOnly = true);
        $this->assertSame(array(
            'AnotherBlackTheme',
            'Darkness',
            'Proteus_Bold',
            'Terrano',
            'CoffeeCup',
            'Vale',
            'ModernBlue',
            'ModernGreen'), $pluginNames);

        $this->service->returnFixture('v2.0_plugins.json');
        $pluginNames = $this->plugins->getAvailablePluginNames($themesOnly = false);
        $this->assertSame($this->getExpectedPluginNames(), $pluginNames);
    }

    public function test_getAvailablePluginNames_shouldCallCorrectApi()
    {
        $this->plugins->getAvailablePluginNames($themesOnly = true);
        $this->assertSame('themes', $this->service->action);

        $this->plugins->getAvailablePluginNames($themesOnly = false);
        $this->assertSame('plugins', $this->service->action);
    }

    public function test_getPluginInfo_noSuchPluginExists()
    {
        $plugin = $this->plugins->getPluginInfo('fooBarBaz');
        $this->assertSame(array(), $plugin);
    }

    public function test_getPluginInfo_notInstalledPlugin_shouldEnrichPluginInformation()
    {
        $this->service->returnFixture('v2.0_plugins_Barometer_info.json');
        $plugin = $this->plugins->getPluginInfo('Barometer');

        unset($plugin['versions']);

        $expected = array (
            'name' => 'Barometer',
            'displayName' => 'Barometer',
            'owner' => 'halfdan',
            'description' => 'Live Plugin that shows the current number of visitors on the page.',
            'homepage' => 'http://github.com/halfdan/piwik-barometer-plugin',
            'createdDateTime' => '2014-12-23 00:38:20',
            'donate' =>
                array (
                    'flattr' => 'https://flattr.com/profile/test1',
                    'bitcoin' => NULL,
                ),
            'support' =>
                array (
                        array (
                            'name' => 'Documentation',
                            'key' => 'docs',
                            'value' => 'https://barometer.org/docs/',
                            'type' => 'url',
                        ),
                        array (
                            'name' => 'Wiki',
                            'key' => 'wiki',
                            'value' => 'https://github.com/barometer/piwik/wiki',
                            'type' => 'url',
                        ),
                        array (
                            'name' => 'Forum',
                            'key' => 'forum',
                            'value' => 'https://baromter.forum.org',
                            'type' => 'url',
                        ),
                        array (
                            'name' => 'Email',
                            'key' => 'email',
                            'value' => 'barometer@example.com',
                            'type' => 'email',
                        ),
                        array (
                            'name' => 'IRC',
                            'key' => 'irc',
                            'value' => 'irc://freenode/baromter',
                            'type' => 'text',
                        ),
                        array (
                            'name' => 'Issues / Bugs',
                            'key' => 'issues',
                            'value' => 'https://github.com/barometer/issues',
                            'type' => 'url',
                        ),
                        array (
                            'name' => 'Source',
                            'key' => 'source',
                            'value' => 'https://github.com/barometer/piwik/',
                            'type' => 'url',
                        ),
                        array (
                            'name' => 'RSS',
                            'key' => 'rss',
                            'value' => 'https://barometer.org/feed/',
                            'type' => 'url',
                        ),
                ),
            'isTheme' => false,
            'keywords' => array ('barometer','live',),
            'basePrice' => 0,
            'authors' =>
                array (array (
                    'name' => 'Fabian Becker',
                    'email' => 'test8@example.com',
                    'homepage' => 'http://geekproject.eu',
                ),),
            'repositoryUrl' => 'https://github.com/halfdan/piwik-barometer-plugin',
            'lastUpdated' => 'Intl_4or41Intl_Time_AMt_357Intl_Time_AMt_S12ort',
            'latestVersion' => '0.5.0',
            'numDownloads' => 0,
            'screenshots' =>
                array (
                    'https://plugins.piwik.org/Barometer/images/0.5.0/piwik-barometer-01.png',
                    'https://plugins.piwik.org/Barometer/images/0.5.0/piwik-barometer-02.png',
                ),
            'previews' =>
                array (array (
                    'type' => 'demo',
                    'provider' => 'link',
                    'url' => 'https://demo.piwik.org',
                ),),
            'activity' =>
                array (
                    'numCommits' => '31',
                    'numContributors' => '3',
                    'lastCommitDate' => NULL,
                ),
            'featured' => false,
            'isFree' => true,
            'isPaid' => false,
            'isCustomPlugin' => false,
            'shop' => NULL,
            'isDownloadable' => true,
            'consumer' => array ('license' => NULL,),
            'isInstalled' => false,
            'isActivated' => false,
            'isInvalid' => true,
            'canBeUpdated' => false,
            'hasExceededLicense' => false,
            'missingRequirements' =>array ( ),
            'isMissingLicense' => false
        );
        $this->assertEquals($expected, $plugin);
    }

    public function test_getPluginInfo_notInstalledPlugin_shouldCallCorrectService()
    {
        $this->plugins->getPluginInfo('Barometer');
        $this->assertSame('plugins/Barometer/info', $this->service->action);
    }

    public function test_searchPlugins_WithSearchAndNoPluginsFound_shouldCallCorrectApi()
    {
        $this->service->returnFixture('v2.0_plugins-query-nomatchforthisquery.json');
        $this->plugins->setPluginsHavingUpdateCache(array());
        $plugins = $this->plugins->searchPlugins($query = 'nomatchforthisquery', $sort = Sort::DEFAULT_SORT, $themesOnly = false);

        $this->assertSame(array(), $plugins);
        $this->assertSame('plugins', $this->service->action);

        $params = array(
            'keywords' => '',
            'purchase_type' => '',
            'query' => 'nomatchforthisquery',
            'sort' => Sort::DEFAULT_SORT,
            'release_channel' => 'latest_stable',
            'prefer_stable' => 1,
            'piwik' => '2.16.3',
            'php' => '7.0.1',
            'mysql' => '5.7.1',
            'num_users' => 5,
            'num_websites' => 21,
        );
        $this->assertSame($params, $this->service->params);
    }

    public function test_searchThemes_ShouldCallCorrectApi()
    {
        $this->service->returnFixture('v2.0_themes.json');
        $this->plugins->setPluginsHavingUpdateCache(array());
        $plugins = $this->plugins->searchPlugins($query = '', $sort = Sort::DEFAULT_SORT, $themesOnly = true);

        $this->assertCount(8, $plugins);
        $this->assertSame('AnotherBlackTheme', $plugins[0]['name']);
        $this->assertSame('themes', $this->service->action);

        $params = array(
            'keywords' => '',
            'purchase_type' => '',
            'query' => '',
            'sort' => Sort::DEFAULT_SORT,
            'release_channel' => 'latest_stable',
            'prefer_stable' => 1,
            'piwik' => '2.16.3',
            'php' => '7.0.1',
            'mysql' => '5.7.1',
            'num_users' => 5,
            'num_websites' => 21,
        );
        $this->assertSame($params, $this->service->params);
    }

    public function test_searchPlugins_manyPluginsFound_shouldEnrichAll()
    {
        $this->service->returnFixture('v2.0_plugins.json');
        $plugins = $this->plugins->searchPlugins($query = '', $sort = Sort::DEFAULT_SORT, $themesOnly = false);

        $this->assertCount(54, $plugins);
        $names = array_map(function ($plugin) {
            return $plugin['name'];
        }, $plugins);
        $this->assertSame($this->getExpectedPluginNames(), $names);

        foreach ($plugins as $plugin) {
            $name = $plugin['name'];
            $this->assertFalse($plugin['isTheme']);
            $this->assertNotEmpty($plugin['homepage']);

            $piwikProCampaign = 'pk_campaign=App_ProfessionalServices&pk_medium=Marketplace&pk_source=Piwik_App';

            if ($name === 'SecurityInfo') {
                $this->assertTrue($plugin['isFree']);
                $this->assertFalse($plugin['isPaid']);
                $this->assertTrue(in_array($plugin['isInstalled'], array(true, false), true));
                $this->assertFalse($plugin['isInvalid']);
                $this->assertTrue(isset($plugin['canBeUpdated']));
                $this->assertSame(array(), $plugin['missingRequirements']);
                $this->assertSame(Plugin\Manager::getInstance()->isPluginActivated('SecurityInfo'), $plugin['isActivated']);
            } elseif ($name === 'SimplePageBuilder') {
                // should add campaign parameters if Piwik PRO plugin
                $this->assertSame('https://github.com/PiwikPRO/SimplePageBuilder?' . $piwikProCampaign . '&pk_content=SimplePageBuilder', $plugin['homepage']);
            }

            if ($plugin['owner'] === 'PiwikPRO') {
                $this->assertContains($piwikProCampaign, $plugin['homepage']);
            } else {
                $this->assertNotContains($piwikProCampaign, $plugin['homepage']);
            }
        }
    }
    
    public function test_getAllPaidPlugins_shouldFetchOnlyPaidPlugins()
    {
        $this->plugins->getAllPaidPlugins();
        $this->assertSame('plugins', $this->service->action);
        $this->assertSame(PurchaseType::TYPE_PAID, $this->service->params['purchase_type']);
        $this->assertSame('', $this->service->params['query']);
    }

    public function test_getAllFreePlugins_shouldFetchOnlyFreePlugins()
    {
        $this->plugins->getAllFreePlugins();
        $this->assertSame('plugins', $this->service->action);
        $this->assertSame(PurchaseType::TYPE_FREE, $this->service->params['purchase_type']);
        $this->assertSame('', $this->service->params['query']);
    }

    public function test_getAllPlugins_shouldFetchFreeAndPaidPlugins()
    {
        $this->plugins->getAllPlugins();
        $this->assertSame('plugins', $this->service->action);
        $this->assertSame(PurchaseType::TYPE_ALL, $this->service->params['purchase_type']);
        $this->assertSame('', $this->service->params['query']);
    }

    public function test_getAllThemes_shouldFetchFreeAndPaidThemes()
    {
        $this->plugins->getAllThemes();
        $this->assertSame('themes', $this->service->action);
        $this->assertSame(PurchaseType::TYPE_ALL, $this->service->params['purchase_type']);
        $this->assertSame('', $this->service->params['query']);
    }

    public function test_getPluginsHavingUpdate_shouldReturnEnrichedPluginUpdatesForPluginsFoundOnTheMarketplace()
    {
        $this->service->returnFixture(array(
            'v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'v2.0_plugins_TreemapVisualization_info.json'
        ));
        $apis = array();
        $this->service->setOnFetchCallback(function ($action, $params) use (&$apis) {
            $apis[] = $action;
        });

        $updates = $this->plugins->getPluginsHavingUpdate();
        $pluginManager = Plugin\Manager::getInstance();
        $pluginName = 'TreemapVisualization';

        $this->assertCount(1, $updates);
        $plugin = $updates[0];
        $this->assertSame($pluginName, $plugin['name']);
        $this->assertSame($pluginManager->getLoadedPlugin($pluginName)->getVersion(), $plugin['currentVersion']);
        $this->assertSame($pluginManager->isPluginActivated($pluginName), $plugin['isActivated']);
        $this->assertSame(array(), $plugin['missingRequirements']);
         $this->assertSame('https://github.com/piwik/plugin-TreemapVisualization/commits/1.0.1', $plugin['repositoryChangelogUrl']);

        $expectedApiCalls = array(
            'plugins/checkUpdates',
            'plugins/AnonymousPiwikUsageMeasurement/info',
            'plugins/CustomAlerts/info',
            'plugins/CustomDimensions/info',
            'plugins/LogViewer/info',
            'plugins/QueuedTracking/info',
            'plugins/SecurityInfo/info',
            'plugins/TasksTimetable/info',
            'plugins/TreemapVisualization/info'
        );
        $this->assertSame($expectedApiCalls, $apis);
    }

    private function getExpectedPluginNames()
    {
        return array (
            'AdminNotification',
            'AdvancedCampaignReporting',
            'AnonymousPiwikUsageMeasurement',
            'ApiGetWithSitesInfo',
            'Bandwidth',
            'Barometer',
            'Chat',
            'ClickHeat',
            'Counter',
            'CustomAlerts',
            'CustomDimensions',
            'CustomOptOut',
            'CustomTrackerJs',
            'ExcludeByDDNS',
            'FeedAnnotation',
            'FlagCounter',
            'FreeMobileMessaging',
            'GoogleAuthenticator',
            'GrabGravatar',
            'InterSites',
            'IntranetGeoIP',
            'Ip2Hostname',
            'IP2Location',
            'IPv6Usage',
            'kDebug',
            'LdapConnection',
            'LdapVisitorInfo',
            'LiveTab',
            'LoginHttpAuth',
            'LoginRevokable',
            'LogViewer',
            'page2images-visual-link',
            'PaidPlugin1',
            'PerformanceInfo',
            'PerformanceMonitor',
            'PlatformsReport',
            'QueuedTracking',
            'ReferrersManager',
            'RerUserDates',
            'SecurityInfo',
            'ServerMonitor',
            'ShibbolethLogin',
            'ShortcodeTracker',
            'SimplePageBuilder',
            'SimpleSysMon',
            'SiteMigration',
            'SnoopyBehavioralScoring',
            'TasksTimetable',
            'TopPagesByActions',
            'TrackingCodeCustomizer',
            'TreemapVisualization',
            'UptimeRobotMonitor',
            'VisitorAvatar',
            'WebsiteGroups'
        );
    }
}
