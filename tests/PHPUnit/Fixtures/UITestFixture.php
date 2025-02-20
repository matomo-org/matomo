<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Exception;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\EventDispatcher;
use Piwik\Filesystem;
use Piwik\FrontController;
use Piwik\Option;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Manager;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;
use Piwik\Plugins\API\API;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\Monolog\Handler\WebNotificationHandler;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Plugins\PrivacyManager\SystemSettings;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\ReportRenderer;
use Piwik\Tests\Framework\XssTesting;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Container\Container;
use Piwik\CronArchive\SegmentArchiving;

/**
 * Fixture for UI tests.
 * @property  angularXssLabel
 */
class UITestFixture extends SqlDump
{
    public const FIXTURE_LOCATION = '/tests/resources/OmniFixture-dump.sql';

    /**
     * @var XssTesting
     */
    private $xssTesting;

    private $angularXssLabel;

    private $twigXssLabel;

    public function __construct()
    {
        $this->dumpUrl = PIWIK_INCLUDE_PATH . self::FIXTURE_LOCATION;
        $this->tablesPrefix = '';
        $this->xssTesting = new XssTesting();
    }

    public function setUp(): void
    {
        parent::setUp();

        // We need to disable events for running updates below.
        // Otherwise PHP will run into a segfault when trying to execute updates for plugins.
        EventDispatcher::$_SKIP_EVENTS_IN_TESTS = true;

        // fetch the installed versions of all plugins from options table
        $pluginVersions = Option::getLike('version_%');
        $plugins = [];

        foreach ($pluginVersions as $pluginName => $version) {
            $name = substr($pluginName, 8);
            if (Manager::getInstance()->isValidPluginName($name) && Manager::getInstance()->isPluginInFilesystem($name)) {
                $plugins[] = $name;
            }
        }

        self::resetPluginsInstalledConfig($plugins);

        self::updateDatabase();

        self::installAndActivatePlugins($this->getTestEnvironment());

        self::updateDatabase();

        EventDispatcher::$_SKIP_EVENTS_IN_TESTS = false;

        // make sure site has an early enough creation date (for period selector tests)
        Db::get()->update(
            Common::prefixTable("site"),
            ['ts_created' => '2011-01-01'],
            "idsite = 1"
        );

        // for proper geolocation
        LocationProvider::setCurrentProvider(GeoIp2\Php::ID);
        IPAnonymizer::deactivate();

        self::createSuperUser(false);

        $this->addOverlayVisits();
        $this->addNewSitesForSiteSelector();

        DbHelper::createAnonymousUser();
        $userUpdater = new UserUpdater();
        $userUpdater->setSuperUserAccessWithoutCurrentPassword('superUserLogin', true);
        SitesManagerAPI::getInstance()->updateSite(1, null, null, true);

        // create non super user
        UsersManagerAPI::getInstance()->addUser('oliverqueen', 'smartypants', 'oli@queenindustries.com');
        UsersManagerAPI::getInstance()->setUserAccess('oliverqueen', 'view', [1]);

        // another non super user
        UsersManagerAPI::getInstance()->addUser('anotheruser', 'anotheruser', 'someemail@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('anotheruser', 'view', [1]);

        // add xss scheduled report
        APIScheduledReports::getInstance()->addReport(
            $idSite = 1,
            $this->xssTesting->forTwig('scheduledreport'),
            'month',
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['ExampleAPI_xssReportforTwig', 'ExampleAPI_xssReportforAngular'],
            [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY]
        );
        APIScheduledReports::getInstance()->addReport(
            $idSite = 1,
            $this->xssTesting->forAngular('scheduledreport'),
            'month',
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['ExampleAPI_xssReportforTwig', 'ExampleAPI_xssReportforAngular'],
            [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY]
        );

        $this->addDangerousLinks();

        $model = new \Piwik\Plugins\UsersManager\Model();
        $user  = $model->getUser(self::VIEW_USER_LOGIN);

        if (empty($user)) {
            $model->addUser(self::VIEW_USER_LOGIN, self::VIEW_USER_PASSWORD, 'hello2@example.org', Date::now()->getDatetime());
            $model->addUserAccess(self::VIEW_USER_LOGIN, 'view', [1, 3]);
        } else {
            $model->updateUser(self::VIEW_USER_LOGIN, self::VIEW_USER_PASSWORD, 'hello2@example.org');
        }
        if (!$model->getUserByTokenAuth(self::VIEW_USER_TOKEN)) {
            $model->addTokenAuth(self::VIEW_USER_LOGIN, self::VIEW_USER_TOKEN, 'View user token', Date::now()->getDatetime());
        }
    }

    public function performSetUp($setupEnvironmentOnly = false)
    {
        $this->extraTestEnvVars = [
            'loadRealTranslations' => 1,
        ];
        $this->extraPluginsToLoad = [
            'CustomDirPlugin'
        ];

        parent::performSetUp($setupEnvironmentOnly);

        self::createSuperUser(false);

        $this->createSegments();
        $this->setupDashboards();

        $visitorIdDeterministic = bin2hex(Db::fetchOne(
            "SELECT idvisitor FROM " . Common::prefixTable('log_visit')
            . " WHERE idsite = 2 AND location_latitude IS NOT NULL LIMIT 1"
        ));
        $this->testEnvironment->forcedIdVisitor = $visitorIdDeterministic;

        $this->testEnvironment->overlayUrl = self::getLocalTestSiteUrl();
        self::createOverlayTestSite();

        $forcedNowTimestamp = Option::get("Tests.forcedNowTimestamp");
        if ($forcedNowTimestamp == false) {
            throw new Exception("Incorrect fixture setup, Tests.forcedNowTimestamp option does not exist! Run the setup again.");
        }

        $this->testEnvironment->forcedNowTimestamp = $forcedNowTimestamp;
        $this->testEnvironment->tokenAuth = self::getTokenAuth();
        $this->testEnvironment->save();

        print "Token auth in fixture is {$this->testEnvironment->tokenAuth}\n";

        $this->angularXssLabel = $this->xssTesting->forAngular('datatablerow');
        $this->twigXssLabel = $this->xssTesting->forTwig('datatablerow');
        $this->xssTesting->sanityCheck();

        // launch archiving so tests don't run out of time
        print("Archiving in fixture set up...");
        VisitsSummaryAPI::getInstance()->get('all', 'year', '2012-08-09');
        VisitsSummaryAPI::getInstance()->get('all', 'year', '2012-08-09', urlencode(OmniFixture::DEFAULT_SEGMENT));
        VisitsSummaryAPI::getInstance()->get(3, 'week', 'yesterday'); // for overlay
        print("Done.");
    }

    private function addOverlayVisits()
    {
        $baseUrl = $this->getLocalTestSiteUrl();

        $visitProfiles = [
            ['', 'page-1.html', 'page-2.html', 'page-3.html', ''],
            ['', 'page-3.html', 'page-4.html'],
            ['', 'page-4.html'],
            ['', 'page-1.html', 'page-3.html', 'page-4.html'],
            ['', 'page-4.html', 'page-1.html'],
            ['', 'page-1.html', ''],
            ['page-4.html', ''],
            ['', 'page-2.html', 'page-3.html'],
            ['', 'page-1.html', 'page-2.html'],
            ['', 'page-6.html', 'page-5.html', 'page-4.html', 'page-3.html', 'page-2.html', 'page-1.html', ''],
            ['', 'page-5.html', 'page-3.html', 'page-1.html'],
            ['', 'page-1.html', 'page-2.html', 'page-3.html'],
            ['', 'page-4.html', 'page-3.html'],
            ['', 'page-1.html', ''],
            ['page-6.html', 'page-3.html', ''],
        ];

        // ip's chosen for geolocation data
        $ips = [
            '72.44.32.12', // not mapped, so defaults to France, without details
            '50.112.3.5', // San Jose, California, United States
            '70.117.169.113', // El Paso, Texas, United States
            '73.77.55.45', // Mount Laurel, New Jersey, United States
            '206.190.75.8', // Lake Forest, California, United States
            '108.211.181.12', // San Francisco, California, United States
            '174.97.139.63', // Raleigh, North Carolina, United States
            '24.125.31.147', // Mechanicsville, Virginia, United States
            '67.51.31.21', // Ogden, Utah, United States
            '156.5.3.1', // Englewood Cliffs, New Jersey, United States
            '194.57.91.215', // Besançon, Bourgogne-Franche-Comte, France
            '137.82.130.1', // Vancouver, British Columbia, Canada
            '113.62.1.1', // Lhasa, Tibet, China
            '151.100.101.92', // Rome, Latium, Italy
            '72.44.32.10', // Ashburn, Virginia, United States
            '95.81.66.139', // Tabriz, East Azerbaijan, Iran
        ];

        $userAgents = [
            'Mozilla/5.0 (Linux; Android 4.4.2; Nexus 4 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.136 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; U; Android 2.3.7; fr-fr; HTC Desire Build/GRI40; MildWild CM-8.0 JG Stable) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.76 Safari/537.36',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; GTB6.3; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; OfficeLiveConnector.1.4; OfficeLivePatch.1.3)',
            'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; MDDSJS; rv:11.0) like Gecko',
            'Mozilla/5.0 (Linux; Android 4.1.1; SGPT13 Build/TJDS0170) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Safari/537.36',
            'Mozilla/5.0 (Linux; U; Android 4.3; zh-cn; SM-N9006 Build/JSS15J) AppleWebKit/537.36 (KHTML, like Gecko)Version/4.0 MQQBrowser/5.0 Mobile Safari/537.36',
            'Mozilla/5.0 (X11; U; Linux i686; ru; rv:1.9.0.14) Gecko/2009090216 Ubuntu/9.04 (jaunty) Firefox/3.0.14',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1',
            'Mozilla/5.0 (Linux; U; Android 4.4.2; en-us; SCH-I535 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
            'Mozilla/5.0 (Linux; Android 7.0; SM-G930V Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.125 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 7.0; SM-A310F Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.91 Mobile Safari/537.36 OPR/42.7.2246.114996',
            'Opera/9.80 (J2ME/MIDP; Opera Mini/5.1.21214/28.2725; U; ru) Presto/2.8.119 Version/11.10',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4 (KHTML, like Gecko) FxiOS/7.5b3349 Mobile/14F89 Safari/603.2.4',
            'Mozilla/5.0 (Android 7.0; Mobile; rv:54.0) Gecko/54.0 Firefox/54.0',
            'Mozilla/5.0 (Linux; Android 6.0.1; SM-G920V Build/MMB29K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.98 Mobile Safari/537.36',
        ];

        $date = Date::factory('yesterday');
        $t = self::getTracker(3, $date->getDatetime(), true);
        $t->enableBulkTracking();

        foreach ($visitProfiles as $visitCount => $visit) {
            $t->setNewVisitorId();
            $t->setIp($ips[$visitCount]);
            $t->setUserAgent($userAgents[$visitCount]);

            foreach ($visit as $idx => $action) {
                $t->setForceVisitDateTime($date->addHour($visitCount)->addHour(0.01 * $idx)->getDatetime());

                $url = $baseUrl . $action;
                $t->setUrl($url);

                if ($idx != 0) {
                    $referrerUrl = $baseUrl . $visit[$idx - 1];
                    $t->setUrlReferrer($referrerUrl);
                }

                self::assertTrue($t->doTrackPageView("page title of $action"));
            }
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }

    public static function createOverlayTestSite($idSite = 3)
    {
        $realDir = PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site-real";
        if (is_dir($realDir)) {
            Filesystem::unlinkRecursive($realDir, true);
        }

        $files = [
            'index.html',
            'opt-out.php',
            'user-id-visitor-id.php',
            'page-1.html',
            'page-2.html',
            'page-3.html',
            'page-4.html',
            'page-5.html',
            'page-6.html'
        ];

        // copy templates to overlay-test-site-real
        mkdir($realDir);
        foreach ($files as $file) {
            copy(
                PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site/$file",
                PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site-real/$file"
            );
        }

        // replace URL in copied files
        $url = self::getRootUrl() . 'tests/PHPUnit/proxy/';
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $url = substr($url, strlen($scheme) + 3);

        foreach ($files as $file) {
            $path = PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site-real/$file";

            $contents = file_get_contents($path);
            $contents = str_replace("%trackerBaseUrl%", $url, $contents);
            $contents = str_replace("%idSite%", $idSite, $contents);
            file_put_contents($path, $contents);
        }
    }

    public static function getLocalTestSiteUrl()
    {
        $url = self::getRootUrl() . "tests/resources/overlay-test-site-real/";

        // when running tests on localhost we use 127.0.0.1 as url instead, so we have a different host in the iframe
        // otherwise we would only test on the same host, which causes a lot less issues
        return str_replace('localhost', '127.0.0.1', $url);
    }

    private function addNewSitesForSiteSelector()
    {
        for ($i = 0; $i != 8; ++$i) {
            self::createWebsite(
                "2011-01-01 00:00:00",
                $ecommerce = 1,
                $siteName = "Site #$i",
                $siteUrl = "http://site$i.com",
                1,
                null,
                null,
                null,
                null,
                0,
                implode(',', [$this->xssTesting->forTwig('excludedparameter'),
                $this->xssTesting->forAngular('excludedparameter'),
                'sid'])
            );
        }
    }

    /** Creates two dashboards that split the widgets up into different groups. */
    public function setupDashboards()
    {
        $oldGet = $_GET;

        $_GET['idSite'] = 1;
        $_GET['token_auth'] = \Piwik\Piwik::getCurrentUserTokenAuth();

        // create almost empty dashboard first, as this will be loaded as default quite often
        $dashboard = [
            [
                [
                    'uniqueId' => "widgetVisitsSummarygetEvolutionGraphforceView1viewDataTablegraphEvolution",
                    'parameters' => [
                        'module' => 'VisitsSummary',
                        'action' => 'getEvolutionGraph',
                        'forceView' => '1',
                        'viewDataTable' => 'graphEvolution'
                    ]
                ]
            ],
            [],
            []
        ];

        $_GET['name'] = 'D4';
        $_GET['layout'] = json_encode($dashboard);
        $_GET['idDashboard'] = 1;
        FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');

        $dashboardColumnCount = 3;
        $dashboardCount = 4;

        $layout = [];
        for ($j = 0; $j != $dashboardColumnCount; ++$j) {
            $layout[] = [];
        }

        $dashboards = [];
        for ($i = 0; $i != $dashboardCount; ++$i) {
            $dashboards[] = $layout;
        }

        // collect widgets & sort them so widget order is not important
        $allWidgets = Request::processRequest('API.getWidgetMetadata', [
            'idSite' => 1
        ]);

        usort($allWidgets, function ($lhs, $rhs) {
            return strcmp($lhs['uniqueId'], $rhs['uniqueId']);
        });

        $widgetsPerDashboard = ceil(count($allWidgets) / $dashboardCount);

        // group widgets so they will be spread out across 3 dashboards
        $groupedWidgets = [];
        $dashboard = 0;
        foreach ($allWidgets as $widget) {
            if (
                $widget['uniqueId'] == 'widgetSEOgetRank'
                || $widget['uniqueId'] == 'widgetLivegetVisitorProfilePopup'
                || $widget['uniqueId'] == 'widgetActionsgetPageTitles'
                || $widget['uniqueId'] == 'widgetCoreHomequickLinks'
                || strpos($widget['uniqueId'], 'widgetExample') === 0
            ) {
                continue;
            }

            $widgetEntry = [
                'uniqueId' => $widget['uniqueId'],
                'parameters' => $widget['parameters']
            ];

            // for realtime map, disable some randomness
            if ($widget['uniqueId'] == 'widgetUserCountryMaprealtimeMap') {
                $widgetEntry['parameters']['showDateTime'] = '0';
                $widgetEntry['parameters']['realtimeWindow'] = 'last2';
                $widgetEntry['parameters']['changeVisitAlpha'] = '0';
                $widgetEntry['parameters']['enableAnimation'] = '0';
                $widgetEntry['parameters']['doNotRefreshVisits'] = '1';
                $widgetEntry['parameters']['removeOldVisits'] = '0';
            }

            // dashboard images must have height of less than 4000px to avoid odd discoloration of last line of image
            $widgetEntry['parameters']['filter_limit'] = 5;

            $groupedWidgets[$dashboard][] = $widgetEntry;

            if (count($groupedWidgets[$dashboard]) >= $widgetsPerDashboard) {
                $dashboard = $dashboard + 1;
            }

            // sanity check
            if ($dashboard >= $dashboardCount) {
                throw new Exception("Unexpected error: Incorrect dashboard widget placement logic. Something's wrong w/ the code.");
            }
        }

        // distribute widgets in each dashboard
        $column = 0;
        foreach ($groupedWidgets as $dashboardIndex => $dashboardWidgets) {
            foreach ($dashboardWidgets as $widget) {
                $column = ($column + 1) % $dashboardColumnCount;

                $dashboards[$dashboardIndex][$column][] = $widget;
            }
        }

        foreach ($dashboards as $id => $layout) {
            if ($id == 0) {
                $_GET['name'] = $this->xssTesting->forTwig('dashboard name' . $id);
            } elseif ($id == 1) {
                $_GET['name'] = $this->xssTesting->forAngular('dashboard name' . $id);
            } else {
                $_GET['name'] = 'dashboard name' . $id;
            }
            $_GET['layout'] = json_encode($layout);
            $_GET['idDashboard'] = $id + 2;
            FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');
        }

        $_GET = $oldGet;
    }

    public function createSegments()
    {
        Rules::setBrowserTriggerArchiving(false);
        Db::exec("TRUNCATE TABLE " . Common::prefixTable('segment'));

        $segmentName = $this->xssTesting->forTwig('segment');
        $segmentDefinition = "browserCode==FF";
        APISegmentEditor::getInstance()->add(
            $segmentName,
            $segmentDefinition,
            $idSite = 1,
            $autoArchive = true,
            $enabledAllUsers = true
        );

        // create two more segments
        $segmentName = $this->xssTesting->forAngular("From Europe segment");
        APISegmentEditor::getInstance()->add(
            'From Europe ' . $segmentName,
            "continentCode==eur",
            $idSite = 1,
            $autoArchive = false,
            $enabledAllUsers = true
        );
        APISegmentEditor::getInstance()->add(
            "Multiple actions",
            "actions>=2",
            $idSite = 1,
            $autoArchive = false,
            $enabledAllUsers = true
        );
        Rules::setBrowserTriggerArchiving(true);
    }

    public function provideContainerConfig()
    {
        // make sure there's data for the auto suggest test
        API::$_autoSuggestLookBack = floor(Date::today()->getTimestamp() - Date::factory('2012-01-01')->getTimestamp()) / (24 * 60 * 60);

        return [
            'Tests.now' => \Piwik\DI::decorate(function () {
                return Option::get("Tests.forcedNowTimestamp");
            }),
            'observers.global' => \Piwik\DI::add([
                ['Report.addReports', \Piwik\DI::value(function (&$reports) {
                    $report = new XssReport();
                    $report->initForXss('forTwig');
                    $reports[] = $report;

                    $report = new XssReport();
                    $report->initForXss('forAngular');
                    $reports[] = $report;
                })],
                ['Dimension.addDimensions', \Piwik\DI::value(function (&$instances) {
                    $instances[] = new XssDimension();
                })],
                ['API.Request.intercept', \Piwik\DI::value(function (&$result, $finalParameters, $pluginName, $methodName) {
                    if ($pluginName != 'ExampleAPI' && $methodName != 'xssReportforTwig' && $methodName != 'xssReportforAngular') {
                        return;
                    }

                    if (!empty($_GET['forceError']) || !empty($_POST['forceError'])) {
                        throw new \Exception("forced exception");
                    }

                    $dataTable = new DataTable();
                    $dataTable->addRowFromSimpleArray([
                        'label' => $this->angularXssLabel,
                        'nb_visits' => 10,
                    ]);
                    $dataTable->addRowFromSimpleArray([
                        'label' => $this->twigXssLabel,
                        'nb_visits' => 15,
                    ]);
                    $result = $dataTable;
                })],
            ]),
            Proxy::class => \Piwik\DI::get(CustomApiProxy::class),
            'log.handlers' => \Piwik\DI::decorate(function ($previous, Container $c) {
                $previous[] = $c->get(WebNotificationHandler::class);
                return $previous;
            }),

            SegmentArchiving::class => \Piwik\DI::autowire()
                ->constructorParameter('beginningOfTimeLastNInYears', 15)
        ];
    }

    public function addDangerousLinks()
    {
        $privacyManagerSettings = new SystemSettings();
        $privacyManagerSettings->termsAndConditionUrl->setValue($this->xssTesting->dangerousLink("termsandconditions"));
        $privacyManagerSettings->termsAndConditionUrl->save();
        $privacyManagerSettings->privacyPolicyUrl->setValue($this->xssTesting->dangerousLink("privacypolicyurl"));
        $privacyManagerSettings->privacyPolicyUrl->save();
    }
}

class XssReport extends Report
{
    private $xssType;

    protected function init()
    {
        parent::init();

        $this->metrics        = ['nb_visits'];
        $this->order = 10;

        $action = Common::getRequestVar('actionToWidgetize', false) ?: Common::getRequestVar('action', false);
        if ($action == 'xssReportforTwig') {
            $this->initForXss('forTwig');
        } elseif ($action == 'xssReportforAngular') {
            $this->initForXss('forAngular');
        }
    }

    public function initForXss($type)
    {
        $this->xssType = $type;

        $xssTesting = new XssTesting();
        $this->dimension      = new XssDimension();
        $this->dimension->initForXss($type);
        $this->name           = $xssTesting->$type('reportname');
        $this->documentation  = $xssTesting->$type('reportdoc');
        $this->categoryId = $xssTesting->$type('category');
        $this->subcategoryId = $xssTesting->$type('subcategory');
        $this->processedMetrics = [new XssProcessedMetric($type)];
        $this->module = 'ExampleAPI';
        $this->action = 'xssReport' . $type;
    }
}

class XssDimension extends VisitDimension
{
    public $type = Dimension::TYPE_NUMBER;

    private $xssType;

    public function initForXss($type)
    {
        $xssTesting = new XssTesting();

        $this->xssType = $type;
        $this->nameSingular = $xssTesting->$type('dimensionname');
        $this->columnName = 'xsstestdim';
        $this->category = $xssTesting->$type('category');
    }

    public function getId()
    {
        return 'XssTest.XssDimension.' . $this->xssType;
    }
}

class XssProcessedMetric extends ProcessedMetric
{
    /**
     * @var string
     */
    private $xssType;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $docs;

    public function __construct($type)
    {
        $xssTesting = new XssTesting();

        $this->xssType = $type;
        $this->name = $xssTesting->$type('processedmetricname');
        $this->docs = $xssTesting->$type('processedmetricdocs');
    }

    public function getName()
    {
        return 'xssmetric';
    }

    public function getTranslatedName()
    {
        return $this->name;
    }

    public function getDocumentation()
    {
        return $this->docs;
    }

    public function compute(Row $row)
    {
        return 5;
    }

    public function getDependentMetrics()
    {
        return [];
    }
}

class CustomApiProxy extends Proxy
{
    public function __construct()
    {
        parent::__construct();
        $this->metadataArray['\Piwik\Plugins\ExampleAPI\API']['xssReportforTwig']['parameters'] = [];
        $this->metadataArray['\Piwik\Plugins\ExampleAPI\API']['xssReportforAngular']['parameters'] = [];
    }

    public function isExistingApiAction($pluginName, $apiAction)
    {
        if ($pluginName == 'ExampleAPI' && ($apiAction == 'xssReportforTwig' || $apiAction == 'xssReportforAngular')) {
            return true;
        }
        return parent::isExistingApiAction($pluginName, $apiAction);
    }
}
