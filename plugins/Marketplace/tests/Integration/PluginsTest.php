<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Plugins\Marketplace\API;
use Piwik\Plugins\Marketplace\Consumer;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\Plugins\Marketplace\Plugins;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Client;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Tests\Framework\Fixture;
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

    public function setUp(): void
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

    public function testGetAllAvailablePluginNamesNoPluginsFound()
    {
        $pluginNames = $this->plugins->getAllAvailablePluginNames();
        $this->assertSame([], $pluginNames);
    }

    public function testGetAllAvailablePluginNames()
    {
        $this->service->returnFixture([
            'v2.0_themes.json', 'v2.0_plugins.json'
        ]);
        $pluginNames = $this->plugins->getAllAvailablePluginNames();
        $expected =  [
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
        ];
        foreach ($expected as $name) {
            self::assertTrue(in_array($name, $pluginNames));
        }
    }

    public function testGetAvailablePluginNamesNoPluginsFound()
    {
        $pluginNames = $this->plugins->getAvailablePluginNames($themesOnly = true);
        $this->assertSame([], $pluginNames);

        $pluginNames = $this->plugins->getAvailablePluginNames($themesOnly = false);
        $this->assertSame([], $pluginNames);
    }

    public function testGetAvailablePluginNamesShouldReturnPluginNames()
    {
        $this->service->returnFixture('v2.0_themes.json');
        $pluginNames = $this->plugins->getAvailablePluginNames($themesOnly = true);
        $this->assertSame([
            'AnotherBlackTheme',
            'Darkness',
            'Proteus_Bold',
            'Terrano',
            'CoffeeCup',
            'Vale',
            'ModernBlue',
            'ModernGreen'], $pluginNames);

        $this->service->returnFixture('v2.0_plugins.json');
        $pluginNames = $this->plugins->getAvailablePluginNames($themesOnly = false);
        $this->assertSame($this->getExpectedPluginNames(), $pluginNames);
    }

    public function testGetAvailablePluginNamesShouldCallCorrectApi()
    {
        $this->plugins->getAvailablePluginNames($themesOnly = true);
        $this->assertSame('themes', $this->service->action);

        $this->plugins->getAvailablePluginNames($themesOnly = false);
        $this->assertSame('plugins', $this->service->action);
    }

    public function testGetLicenseValidInfoNoSuchPluginExists()
    {
        $plugin = $this->plugins->getPluginInfo('fooBarBaz');
        $this->assertSame([], $plugin);
    }

    public function testGetLicenseValidInfoShouldEnrichLicenseInformation()
    {
        $this->service->returnFixture('v2.0_plugins_Barometer_info.json');
        $plugin = $this->plugins->getLicenseValidInfo('PaidPlugin1');

        unset($plugin['versions']);

        $expected =  [
            'hasExceededLicense' => false,
            'isMissingLicense' => false,
        ];
        $this->assertEquals($expected, $plugin);
    }

    public function testGetLicenseValidInfoMissingLicense()
    {
        $this->service->returnFixture('v2.0_plugins_PaidPlugin1_info.json');
        $plugin = $this->plugins->getLicenseValidInfo('PaidPlugin1');

        unset($plugin['versions']);

        $expected =  [
            'hasExceededLicense' => false,
            'isMissingLicense' => true,
        ];
        $this->assertEquals($expected, $plugin);
    }

    public function testGetLicenseValidInfoValidLicense()
    {
        $this->service->returnFixture('v2.0_consumer-access_token-consumer2_paid1.json');
        $plugin = $this->plugins->getLicenseValidInfo('Barometer');

        unset($plugin['versions']);

        $expected =  [
            'hasExceededLicense' => false,
            'isMissingLicense' => false,
        ];
        $this->assertEquals($expected, $plugin);
    }

    public function testGetLicenseValidInfoNotInstalledPluginShouldCallCorrectService()
    {
        $this->plugins->getLicenseValidInfo('Barometer');
        $this->assertSame('plugins/Barometer/info', $this->service->action);
    }

    public function testGetPluginInfoNoSuchPluginExists()
    {
        $plugin = $this->plugins->getPluginInfo('fooBarBaz');
        $this->assertSame([], $plugin);
    }

    public function testGetPluginInfoNotInstalledPluginShouldEnrichPluginInformation()
    {
        Fixture::loadAllTranslations();

        $this->service->returnFixture('v2.0_plugins_Barometer_info.json');
        $plugin = $this->plugins->getPluginInfo('Barometer');

        unset($plugin['versions']);

        $expected =  [
            'name' => 'Barometer',
            'displayName' => 'Barometer',
            'owner' => 'halfdan',
            'description' => 'Live Plugin that shows the current number of visitors on the page.',
            'homepage' => 'http://github.com/halfdan/piwik-barometer-plugin',
            'createdDateTime' => '2014-12-23 00:38:20',
            'donate' =>
                 [
                    'flattr' => 'https://flattr.com/profile/test1',
                    'bitcoin' => null,
                ],
            'support' =>
                 [
                         [
                            'name' => 'Documentation',
                            'key' => 'docs',
                            'value' => 'https://barometer.org/docs/',
                            'type' => 'url',
                         ],
                         [
                            'name' => 'Wiki',
                            'key' => 'wiki',
                            'value' => 'https://github.com/barometer/piwik/wiki',
                            'type' => 'url',
                         ],
                         [
                            'name' => 'Forum',
                            'key' => 'forum',
                            'value' => 'https://baromter.forum.org',
                            'type' => 'url',
                         ],
                         [
                            'name' => 'Email',
                            'key' => 'email',
                            'value' => 'barometer@example.com',
                            'type' => 'email',
                         ],
                         [
                            'name' => 'IRC',
                            'key' => 'irc',
                            'value' => 'irc://freenode/baromter',
                            'type' => 'text',
                         ],
                         [
                            'name' => 'Issues / Bugs',
                            'key' => 'issues',
                            'value' => 'https://github.com/barometer/issues',
                            'type' => 'url',
                         ],
                         [
                            'name' => 'Source',
                            'key' => 'source',
                            'value' => 'https://github.com/barometer/piwik/',
                            'type' => 'url',
                         ],
                         [
                            'name' => 'RSS',
                            'key' => 'rss',
                            'value' => 'https://barometer.org/feed/',
                            'type' => 'url',
                         ],
                ],
            'isTheme' => false,
            'keywords' =>  ['barometer','live',],
            'basePrice' => 0,
            'authors' =>
                 [ [
                    'name' => 'Fabian Becker',
                    'email' => 'test8@example.com',
                    'homepage' => 'http://geekproject.eu',
                 ],],
            'repositoryUrl' => 'https://github.com/halfdan/piwik-barometer-plugin',
            'lastUpdated' => 'Dec 23, 2014',
            'latestVersion' => '0.5.0',
            'numDownloads' => 0,
            'screenshots' =>
                 [
                    'https://plugins.piwik.org/Barometer/images/0.5.0/piwik-barometer-01.png',
                    'https://plugins.piwik.org/Barometer/images/0.5.0/piwik-barometer-02.png',
                ],
            'coverImage' => 'https://plugins.piwik.org/img/categories/insights.png',
            'previews' =>
                 [ [
                    'type' => 'demo',
                    'provider' => 'link',
                    'url' => 'https://demo.piwik.org',
                 ],],
            'activity' =>
                 [
                    'numCommits' => '31',
                    'numContributors' => '3',
                    'lastCommitDate' => null,
                ],
            'featured' => false,
            'isFree' => true,
            'isPaid' => false,
            'isCustomPlugin' => false,
            'shop' => null,
            'isDownloadable' => true,
            'consumer' =>  ['license' => null,],
            'isInstalled' => false,
            'isActivated' => false,
            'isInvalid' => true,
            'canBeUpdated' => false,
            'hasExceededLicense' => false,
            'missingRequirements' => [],
            'isMissingLicense' => false,
            'changelog' => [
                'url' => 'http://plugins.piwik.org/Barometer/changelog'
            ],
            'canBePurchased' => false,
            'isEligibleForFreeTrial' => false,
            'priceFrom' => null,
            'numDownloadsPretty' => '0',
            'hasDownloadLink' => true,
            'licenseStatus' => '',
            'category' => 'customisation',
        ];
        $this->assertEquals($expected, $plugin);
    }

    public function testGetPluginInfoNotInstalledPluginShouldCallCorrectService()
    {
        $this->plugins->getPluginInfo('Barometer');
        $this->assertSame('plugins/Barometer/info', $this->service->action);
    }

    /**
     * @dataProvider getPluginInfoShouldSetFreeTrialEligibilityTestData
     */
    public function testGetPluginInfoShouldSetFreeTrialEligibility(
        string $pluginName,
        string $fixtureName,
        bool $isEligibleForFreeTrial
    ): void {
        $this->service->returnFixture($fixtureName);

        $plugin = $this->plugins->getPluginInfo($pluginName);

        self::assertArrayHasKey('isEligibleForFreeTrial', $plugin);
        self::assertSame($isEligibleForFreeTrial, $plugin['isEligibleForFreeTrial']);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public function getPluginInfoShouldSetFreeTrialEligibilityTestData(): iterable
    {
        yield 'free plugin' => [
            'Barometer',
            'v2.0_plugins_Barometer_info.json',
            false,
        ];

        yield 'paid plugin, no prior license' => [
            'PaidPlugin1',
            'v2.0_plugins_PaidPlugin1_info.json',
            true,
        ];

        yield 'paid plugin, with prior license' => [
            'PaidPlugin1',
            'v2.0_plugins_PaidPlugin1_info-access_token-consumer3_paid1_custom2.json',
            false,
        ];
    }

    public function testSearchPluginsWithSearchAndNoPluginsFoundShouldCallCorrectApi()
    {
        $this->service->returnFixture('v2.0_plugins-query-nomatchforthisquery.json');
        $this->plugins->setPluginsHavingUpdateCache([]);
        $plugins = $this->plugins->searchPlugins($query = 'nomatchforthisquery', $sort = Sort::DEFAULT_SORT, $themesOnly = false);

        $this->assertSame([], $plugins);
        $this->assertSame('plugins', $this->service->action);

        $params = [
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
        ];
        $this->assertSame($params, $this->service->params);
    }

    public function testSearchThemesShouldCallCorrectApi()
    {
        $this->service->returnFixture('v2.0_themes.json');
        $this->plugins->setPluginsHavingUpdateCache([]);
        $plugins = $this->plugins->searchPlugins($query = '', $sort = Sort::DEFAULT_SORT, $themesOnly = true);

        $this->assertCount(8, $plugins);
        $this->assertSame('AnotherBlackTheme', $plugins[0]['name']);
        $this->assertSame('themes', $this->service->action);

        $params = [
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
        ];
        $this->assertSame($params, $this->service->params);
    }

    public function testSearchPluginsManyPluginsFoundShouldEnrichAll()
    {
        $this->service->returnFixture('v2.0_plugins.json');
        $plugins = $this->plugins->searchPlugins($query = '', $sort = Sort::DEFAULT_SORT, $themesOnly = false);

        $this->assertCount(47, $plugins);
        $names = array_map(function ($plugin) {
            return $plugin['name'];
        }, $plugins);
        $this->assertSame($this->getExpectedPluginNames(), $names);

        foreach ($plugins as $plugin) {
            $name = $plugin['name'];
            $this->assertFalse($plugin['isTheme']);
            $this->assertNotEmpty($plugin['homepage']);

            $piwikProCampaign = 'pk_campaign=App_ProfessionalServices&pk_medium=Marketplace&pk_source=Matomo_App';

            if ($name === 'SecurityInfo') {
                $this->assertTrue($plugin['isFree']);
                $this->assertFalse($plugin['isPaid']);
                $this->assertTrue(in_array($plugin['isInstalled'], [true, false], true));
                $this->assertFalse($plugin['isInvalid']);
                $this->assertTrue(isset($plugin['canBeUpdated']));
                $this->assertSame([], $plugin['missingRequirements']);
                $this->assertSame(Plugin\Manager::getInstance()->isPluginActivated('SecurityInfo'), $plugin['isActivated']);
            } elseif ($name === 'SimplePageBuilder') {
                // should add campaign parameters if Piwik PRO plugin
                $this->assertSame('https://github.com/PiwikPRO/SimplePageBuilder?' . $piwikProCampaign . '&pk_content=SimplePageBuilder', $plugin['homepage']);
            }

            if ($plugin['owner'] === 'PiwikPRO') {
                self::assertStringContainsString($piwikProCampaign, $plugin['homepage']);
            } else {
                self::assertStringNotContainsString($piwikProCampaign, $plugin['homepage']);
            }
        }
    }

    public function testGetAllPaidPluginsShouldFetchOnlyPaidPlugins()
    {
        $this->plugins->getAllPaidPlugins();
        $this->assertSame('plugins', $this->service->action);
        $this->assertSame(PurchaseType::TYPE_PAID, $this->service->params['purchase_type']);
        $this->assertSame('', $this->service->params['query']);
    }

    public function testGetAllFreePluginsShouldFetchOnlyFreePlugins()
    {
        $this->plugins->getAllFreePlugins();
        $this->assertSame('plugins', $this->service->action);
        $this->assertSame(PurchaseType::TYPE_FREE, $this->service->params['purchase_type']);
        $this->assertSame('', $this->service->params['query']);
    }

    public function testGetAllPluginsShouldFetchFreeAndPaidPlugins()
    {
        $this->plugins->getAllPlugins();
        $this->assertSame('plugins', $this->service->action);
        $this->assertSame(PurchaseType::TYPE_ALL, $this->service->params['purchase_type']);
        $this->assertSame('', $this->service->params['query']);
    }

    public function testGetAllThemesShouldFetchFreeAndPaidThemes()
    {
        $this->plugins->getAllThemes();
        $this->assertSame('themes', $this->service->action);
        $this->assertSame(PurchaseType::TYPE_ALL, $this->service->params['purchase_type']);
        $this->assertSame('', $this->service->params['query']);
    }

    public function testGetPluginsHavingUpdateShouldReturnEnrichedPluginUpdatesForPluginsFoundOnTheMarketplace()
    {
        $this->service->returnFixture([
            'v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'emptyObjectResponse.json',
            'v2.0_plugins_TreemapVisualization_info.json'
        ]);
        $apis = [];
        $this->service->setOnFetchCallback(function ($action, $params) use (&$apis) {
            $apis[] = $action;
        });

        $updates = $this->plugins->getPluginsHavingUpdate();
        $pluginManager = Plugin\Manager::getInstance();
        $pluginName = 'TreemapVisualization';

        $this->assertCount(1, $updates);
        $plugin = $updates[$pluginName];
        $this->assertSame($pluginName, $plugin['name']);
        $this->assertSame($pluginManager->getLoadedPlugin($pluginName)->getVersion(), $plugin['currentVersion']);
        $this->assertSame($pluginManager->isPluginActivated($pluginName), $plugin['isActivated']);
        $this->assertSame([], $plugin['missingRequirements']);
        $this->assertSame('https://github.com/piwik/plugin-TreemapVisualization/commits/1.0.1', $plugin['repositoryChangelogUrl']);

        $expectedApiCalls = [
            'plugins/checkUpdates',
            'plugins/AnonymousPiwikUsageMeasurement/info',
            'plugins/CustomAlerts/info',
            'plugins/CustomDimensions/info',
            'plugins/LogViewer/info',
            'plugins/QueuedTracking/info',
            'plugins/SecurityInfo/info',
            'plugins/TasksTimetable/info',
            'plugins/TreemapVisualization/info'
        ];
        $this->assertSame($expectedApiCalls, $apis);
    }

    private function getExpectedPluginNames()
    {
        return  [
            'AdminNotification',
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
            'ExcludeByDDNS',
            'FeedAnnotation',
            'FlagCounter',
            'FreeMobileMessaging',
            'GoogleAuthenticator',
            'GrabGravatar',
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
            'QueuedTracking',
            'ReferrersManager',
            'RerUserDates',
            'SecurityInfo',
            'ServerMonitor',
            'ShibbolethLogin',
            'ShortcodeTracker',
            'SimpleSysMon',
            'SnoopyBehavioralScoring',
            'TasksTimetable',
            'TopPagesByActions',
            'TrackingCodeCustomizer',
            'TreemapVisualization',
            'UptimeRobotMonitor',
            'VisitorAvatar'
        ];
    }
}
