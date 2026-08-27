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

    /**
     * @var Client
     */
    private $marketplaceClient;

    private const TEST_UNIQUE_ID = 'test-unique-id';

    public function setUp(): void
    {
        parent::setUp();

        API::unsetInstance();

        $this->service = new Service();
        $this->consumerService = new Service();
        $this->marketplaceClient = Client::build($this->service);

        $this->plugins = new Plugins(
            $this->marketplaceClient,
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
            'v2.0_themes.json', 'v2.0_plugins.json',
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

    public function testLicenseInformationPrefersTheConsumerOverTheCopyEmbeddedInThePlugin()
    {
        // PaidPlugin1's own entry carries no license, which is why
        // testGetLicenseValidInfoMissingLicense sees it as missing. The consumer response does
        // carry one, and that is the fresher answer: the plugin lists are cached for longer.
        $this->service->returnFixture('v2.0_plugins_PaidPlugin1_info.json');
        $this->consumerService->authenticate('123456789');
        $this->consumerService->returnFixture('v2.0_consumer-access_token-consumer2_paid1.json');

        $plugin = $this->plugins->getLicenseValidInfo('PaidPlugin1');

        $this->assertFalse($plugin['isMissingLicense']);
        $this->assertFalse($plugin['hasExceededLicense']);
    }

    public function testLicenseInformationFallsBackToTheEmbeddedCopyWhenTheConsumerSaysNothing()
    {
        // an instance that cannot reach the Marketplace, or a consumer holding no license for this
        // plugin, has to behave exactly as it did before the consumer lookup was added
        $this->service->returnFixture('v2.0_plugins_PaidPlugin1_info.json');
        $this->consumerService->authenticate('123456789');
        $this->consumerService->returnFixture('v2.0_consumer-access_token-validbutnolicense.json');

        $plugin = $this->plugins->getLicenseValidInfo('PaidPlugin1');

        $this->assertTrue($plugin['isMissingLicense']);
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
                'url' => 'http://plugins.piwik.org/Barometer/changelog',
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
     * The pair below has to be read together: both let the consumer answer, and they differ only in
     * whether the Marketplace suppressed the plugin's trial. Without an answering consumer neither
     * proves anything, because {@link Plugins::getCurrentLicenseFor()} then falls back to the
     * embedded copy and reaches the right verdict for the wrong reason.
     */
    public function testGetPluginInfoOffersATrialWhenTheConsumerHoldsNoLicenceForThePlugin(): void
    {
        $this->letTheConsumerAnswerHoldingNoLicences();
        $this->service->returnFixture('v2.0_plugins_PaidPlugin1_info.json');

        $plugin = $this->plugins->getPluginInfo('PaidPlugin1');

        self::assertTrue($plugin['isEligibleForFreeTrial']);
    }

    public function testGetPluginInfoKeepsTheMarketplacesTrialSuppression(): void
    {
        $this->letTheConsumerAnswerHoldingNoLicences();
        // identical to the fixture above but for consumer.license, which the Marketplace sets to a
        // scalar for BusinessBundle and EnterpriseBundle to keep them out of the free trial flow.
        // The consumer endpoint only ever carries real licence rows, so it cannot express this and
        // must not be allowed to override it into "no licence, so offer a trial".
        $this->service->returnFixture('v2.0_plugins_PaidPlugin1_info-license-suppressed.json');

        $plugin = $this->plugins->getPluginInfo('PaidPlugin1');

        self::assertFalse($plugin['isEligibleForFreeTrial']);
    }

    private function letTheConsumerAnswerHoldingNoLicences(): void
    {
        // authenticated, or Client::getConsumer() short circuits and the licences read as unknown
        $this->consumerService->authenticate('123456789');
        $this->consumerService->returnFixture('v2.0_consumer-access_token-validbutnolicense.json');
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
            'uid' => self::TEST_UNIQUE_ID,
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
            'uid' => self::TEST_UNIQUE_ID,
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

    public function testGetPluginInfoPreferringListServesAListedPluginWithoutItsOwnRequest()
    {
        $this->service->returnFixture([
            'v2.0_plugins.json',
            'v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json',
        ]);

        $this->warmOverviewLists();

        $apis = [];
        $this->service->setOnFetchCallback(function ($action) use (&$apis) {
            $apis[] = $action;
        });

        $plugin = $this->plugins->getPluginInfoPreferringList('TreemapVisualization');

        $this->assertSame('TreemapVisualization', $plugin['name']);
        // the details modal used to pay a round trip to the Marketplace the first time each plugin
        // was opened, for a payload the cached plugin list already holds
        $this->assertNotContains('plugins/TreemapVisualization/info', $apis);
    }

    public function testGetPluginInfoPreferringListFallsBackForAPluginTheListsOmit()
    {
        // CustomReports is in neither list fixture, so the lookup scans the cached plugin list,
        // then the cached theme list, then asks for the plugin directly and enriches just that one
        $this->service->returnFixture([
            'v2.0_plugins.json',
            'v2.0_themes.json',
            'system_v2.0_plugins_CustomReports_info.json',
            'v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json',
        ]);

        $this->warmOverviewLists(true);

        $apis = [];
        $this->service->setOnFetchCallback(function ($action) use (&$apis) {
            $apis[] = $action;
        });

        $plugin = $this->plugins->getPluginInfoPreferringList('CustomReports');

        $this->assertContains('plugins/CustomReports/info', $apis);
        $this->assertSame('CustomReports', $plugin['name']);
    }

    public function testGetPluginInfoPreferringListDoesNotFetchTheListsWhenTheyAreNotCached()
    {
        $this->service->returnFixture([
            'system_v2.0_plugins_CustomReports_info.json',
            'v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json',
        ]);

        $apis = [];
        $this->service->setOnFetchCallback(function ($action) use (&$apis) {
            $apis[] = $action;
        });

        $plugin = $this->plugins->getPluginInfoPreferringList('CustomReports');

        // a cold cache must not turn one plugin lookup into a download of both catalogues, which is
        // far more than the single info request the lookup replaces
        $this->assertNotContains('plugins', $apis);
        $this->assertNotContains('themes', $apis);
        $this->assertContains('plugins/CustomReports/info', $apis);
        $this->assertSame('CustomReports', $plugin['name']);
    }

    /**
     * The overview page fetches these lists before any details modal can be opened, so a test about
     * preferring them has to put them in the cache the same way the page does.
     *
     * The client is rebuilt with a cache that retains responses first. The mock client's default
     * keeps nothing, so the warmed lists would otherwise not be there to be preferred and the
     * lookup would fall back to a per plugin request.
     */
    private function warmOverviewLists(bool $themesToo = false): void
    {
        $this->marketplaceClient = Client::build($this->service, new Lazy(new ArrayCache()));
        $this->plugins = new Plugins(
            $this->marketplaceClient,
            new Consumer(Client::build($this->consumerService)),
            new Advertising()
        );

        $this->marketplaceClient->searchForPlugins('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);

        if ($themesToo) {
            $this->marketplaceClient->searchForThemes('', '', Sort::DEFAULT_SORT, PurchaseType::TYPE_ALL);
        }
    }

    public function testSearchPluginsShouldNotRequestPluginInfoForEveryPluginHavingUpdate()
    {
        $this->service->returnFixture([
            'v2.0_plugins.json',
            'v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json',
        ]);

        $apis = [];
        $this->service->setOnFetchCallback(function ($action) use (&$apis) {
            $apis[] = $action;
        });

        $this->plugins->searchPlugins($query = '', Sort::DEFAULT_SORT, $themesOnly = false);

        // enriching the list must not resolve each updatable plugin's info, which used to cost one
        // extra request per plugin having an update
        $this->assertSame(['plugins', 'plugins/checkUpdates'], $apis);
    }

    public function testSearchPluginsShouldFlagUpdatablePluginsFromTheUpdateSummary()
    {
        $this->service->returnFixture([
            'v2.0_plugins.json',
            'v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json',
        ]);

        $plugins = $this->plugins->searchPlugins($query = '', Sort::DEFAULT_SORT, $themesOnly = false);

        $updatable = [];
        foreach ($plugins as $plugin) {
            if (!empty($plugin['canBeUpdated'])) {
                $updatable[$plugin['name']] = $plugin;
            }
        }

        // every plugin checkUpdates reports is flagged. getPluginsHavingUpdate() additionally drops
        // plugins whose info request comes back empty, but a plugin can only reach this list if it
        // passed the same isCustomPlugin filter the info request applies, so that pass is redundant
        // here and cost one request per updatable plugin
        $this->assertContains('TreemapVisualization', array_keys($updatable));

        $plugin = $updatable['TreemapVisualization'];
        $this->assertSame(
            'https://github.com/piwik/plugin-TreemapVisualization/commits/1.0.1',
            $plugin['repositoryChangelogUrl']
        );
        $this->assertSame(
            Plugin\Manager::getInstance()->getLoadedPlugin('TreemapVisualization')->getVersion(),
            $plugin['currentVersion']
        );
    }

    public function testGetPluginsHavingUpdateStillDropsAPluginWhoseInfoComesBackEmpty()
    {
        // the catalogue lists answer empty, so every reported update falls through to its own info
        // request the way it always did — and those answer empty too, so none may be listed. This is
        // the filter that keeps a custom or delisted plugin out of the plugins admin page and the
        // update notification email; resolving updates from the catalogue must not bypass it.
        $this->service->returnFixture(array_merge(
            ['v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json'],
            array_fill(0, 2, 'emptyObjectResponse.json'),
            array_fill(0, 8, 'emptyObjectResponse.json')
        ));

        $this->assertSame([], $this->plugins->getPluginsHavingUpdate());
    }

    public function testGetPluginsHavingUpdateShouldReturnEnrichedPluginUpdatesForPluginsFoundOnTheMarketplace()
    {
        $this->service->returnFixture([
            'v2.0_plugins.json',
            'v2.0_themes.json',
            'v2.0_plugins_checkUpdates-pluginspluginsnameAnonymousPi.json',
        ]);

        $this->warmOverviewLists(true);

        $apis = [];
        $this->service->setOnFetchCallback(function ($action, $params) use (&$apis) {
            $apis[] = $action;
        });

        $updates = $this->plugins->getPluginsHavingUpdate();
        $pluginManager = Plugin\Manager::getInstance();
        $pluginName = 'TreemapVisualization';

        // every plugin checkUpdates reported is returned. The old fixture forced seven of the eight
        // to come back empty from their own info request, which no longer happens because they are
        // resolved from the catalogue instead.
        $this->assertCount(8, $updates);
        $this->assertArrayHasKey($pluginName, $updates);

        $plugin = $updates[$pluginName];
        $this->assertSame($pluginName, $plugin['name']);
        $this->assertSame($pluginManager->getLoadedPlugin($pluginName)->getVersion(), $plugin['currentVersion']);
        $this->assertSame($pluginManager->isPluginActivated($pluginName), $plugin['isActivated']);
        $this->assertSame([], $plugin['missingRequirements']);
        $this->assertSame('https://github.com/piwik/plugin-TreemapVisualization/commits/1.0.1', $plugin['repositoryChangelogUrl']);

        // the updates are resolved out of the already cached plugin and theme lists, so the only
        // request left is the update check itself. Asking about each plugin in turn used to cost one
        // request per plugin having an update.
        $this->assertSame(['plugins/checkUpdates'], $apis);

        $infoRequests = array_values(array_filter($apis, function ($action) {
            return (bool) preg_match('#^plugins/[^/]+/info$#', $action);
        }));
        $this->assertSame([], $infoRequests);
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
            'VisitorAvatar',
        ];
    }

    public function provideContainerConfig()
    {
        return [
            'dev.forced_plugin_update_result' => null,
        ];
    }
}
