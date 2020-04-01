<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Exception;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Filesystem;
use Piwik\FrontController;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
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
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\ReportRenderer;
use Piwik\Tests\Framework\XssTesting;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Psr\Container\ContainerInterface;

/**
 * Fixture for UI tests.
 * @property  angularXssLabel
 */
class UITestFixture extends SqlDump
{
    const FIXTURE_LOCATION = '/tests/resources/OmniFixture-dump.sql';

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

        self::resetPluginsInstalledConfig();
        self::updateDatabase();
        self::installAndActivatePlugins($this->getTestEnvironment());
        self::updateDatabase();

        // make sure site has an early enough creation date (for period selector tests)
        Db::get()->update(Common::prefixTable("site"),
            array('ts_created' => '2011-01-01'),
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
        UsersManagerAPI::getInstance()->setUserAccess('oliverqueen', 'view', array(1));

        // another non super user
        UsersManagerAPI::getInstance()->addUser('anotheruser', 'anotheruser', 'someemail@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('anotheruser', 'view', array(1));

        // add xss scheduled report
        APIScheduledReports::getInstance()->addReport(
            $idSite = 1,
            $this->xssTesting->forTwig('scheduledreport'),
            'month',
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['ExampleAPI_xssReportforTwig', 'ExampleAPI_xssReportforAngular'],
            array(ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY)
        );
        APIScheduledReports::getInstance()->addReport(
            $idSite = 1,
            $this->xssTesting->forAngular('scheduledreport'),
            'month',
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['ExampleAPI_xssReportforTwig', 'ExampleAPI_xssReportforAngular'],
            array(ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY)
        );

        $this->addDangerousLinks();
    }

    public function performSetUp($setupEnvironmentOnly = false)
    {
        $this->extraTestEnvVars = array(
            'loadRealTranslations' => 1,
        );
        $this->extraPluginsToLoad = array(
            'CustomDirPlugin'
        );

        parent::performSetUp($setupEnvironmentOnly);

        self::createSuperUser(false);

        $this->createSegments();
        $this->setupDashboards();

        $visitorIdDeterministic = bin2hex(Db::fetchOne(
            "SELECT idvisitor FROM " . Common::prefixTable('log_visit')
            . " WHERE idsite = 2 AND location_latitude IS NOT NULL LIMIT 1"));
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

        $visitProfiles = array(
            array('', 'page-1.html', 'page-2.html', 'page-3.html', ''),
            array('', 'page-3.html', 'page-4.html'),
            array('', 'page-4.html'),
            array('', 'page-1.html', 'page-3.html', 'page-4.html'),
            array('', 'page-4.html', 'page-1.html'),
            array('', 'page-1.html', ''),
            array('page-4.html', ''),
            array('', 'page-2.html', 'page-3.html'),
            array('', 'page-1.html', 'page-2.html'),
            array('', 'page-6.html', 'page-5.html', 'page-4.html', 'page-3.html', 'page-2.html', 'page-1.html', ''),
            array('', 'page-5.html', 'page-3.html', 'page-1.html'),
            array('', 'page-1.html', 'page-2.html', 'page-3.html'),
            array('', 'page-4.html', 'page-3.html'),
            array('', 'page-1.html', ''),
            array('page-6.html', 'page-3.html', ''),
        );

        $ips = array( // ip's chosen for geolocation data
            "72.44.32.12",
            "50.112.3.5",
            "70.117.169.113",
            "73.77.55.45",
            "206.190.75.8",
            "108.211.181.12",
            "174.97.139.63",
            "24.125.31.147",
            "67.51.31.21",
            "156.5.3.1",
            "194.57.91.215",
            "137.82.130.1",
            "113.62.1.1",
            "151.100.101.92",
            "72.44.32.10",
            "95.81.66.139",
        );

        $date = Date::factory('yesterday');
        $t = self::getTracker($idSite = 3, $dateTime = $date->getDatetime(), $defaultInit = true);
        $t->enableBulkTracking();

        foreach ($visitProfiles as $visitCount => $visit) {
            $t->setNewVisitorId();
            $t->setIp($ips[$visitCount]);

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

        $files = array('index.html', 'page-1.html', 'page-2.html', 'page-3.html', 'page-4.html', 'page-5.html', 'page-6.html');

        // copy templates to overlay-test-site-real
        mkdir($realDir);
        foreach ($files as $file) {
            copy(PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site/$file",
                 PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site-real/$file");
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
        return self::getRootUrl() . "tests/resources/overlay-test-site-real/";
    }

    private function addNewSitesForSiteSelector()
    {
        for ($i = 0; $i != 8; ++$i) {
            self::createWebsite("2011-01-01 00:00:00", $ecommerce = 1, $siteName = "Site #$i", $siteUrl = "http://site$i.com");
        }
    }

    /** Creates two dashboards that split the widgets up into different groups. */
    public function setupDashboards()
    {
        $dashboardColumnCount = 3;
        $dashboardCount = 4;

        $layout = array();
        for ($j = 0; $j != $dashboardColumnCount; ++$j) {
            $layout[] = array();
        }

        $dashboards = array();
        for ($i = 0; $i != $dashboardCount; ++$i) {
            $dashboards[] = $layout;
        }

        $oldGet = $_GET;
        $_GET['idSite'] = 1;
        $_GET['token_auth'] = \Piwik\Piwik::getCurrentUserTokenAuth();

        // collect widgets & sort them so widget order is not important
        $allWidgets = Request::processRequest('API.getWidgetMetadata', array(
            'idSite' => 1
        ));

        usort($allWidgets, function ($lhs, $rhs) {
            return strcmp($lhs['uniqueId'], $rhs['uniqueId']);
        });

        $widgetsPerDashboard = ceil(count($allWidgets) / $dashboardCount);

        // group widgets so they will be spread out across 3 dashboards
        $groupedWidgets = array();
        $dashboard = 0;
        foreach ($allWidgets as $widget) {
            if ($widget['uniqueId'] == 'widgetSEOgetRank'
                || $widget['uniqueId'] == 'widgetLivegetVisitorProfilePopup'
                || $widget['uniqueId'] == 'widgetActionsgetPageTitles'
                || $widget['uniqueId'] == 'widgetCoreHomequickLinks'
                || strpos($widget['uniqueId'], 'widgetExample') === 0
            ) {
                continue;
            }

            $widgetEntry = array(
                'uniqueId' => $widget['uniqueId'],
                'parameters' => $widget['parameters']
            );

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
            } else if ($id == 1) {
                $_GET['name'] = $this->xssTesting->forAngular('dashboard name' . $id);
            } else {
                $_GET['name'] = 'dashboard name' . $id;
            }
            $_GET['layout'] = json_encode($layout);
            $_GET['idDashboard'] = $id + 1;
            FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');
        }

        // create empty dashboard
        $dashboard = array(
            array(
                array(
                    'uniqueId' => "widgetVisitsSummarygetEvolutionGraphforceView1viewDataTablegraphEvolution",
                    'parameters' => array(
                        'module' => 'VisitsSummary',
                        'action' => 'getEvolutionGraph',
                        'forceView' => '1',
                        'viewDataTable' => 'graphEvolution'
                    )
                )
            ),
            array(),
            array()
        );

        $_GET['name'] = 'D4';
        $_GET['layout'] = json_encode($dashboard);
        $_GET['idDashboard'] = 5;
        $_GET['idSite'] = 2;
        FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');

        $_GET = $oldGet;
    }

    public function createSegments()
    {
        Db::exec("TRUNCATE TABLE " . Common::prefixTable('segment'));

        $segmentName = $this->xssTesting->forTwig('segment');
        $segmentDefinition = "browserCode==FF";
        APISegmentEditor::getInstance()->add(
            $segmentName, $segmentDefinition, $idSite = 1, $autoArchive = true, $enabledAllUsers = true);

        // create two more segments
        $segmentName = $this->xssTesting->forAngular("From Europe segment");
        APISegmentEditor::getInstance()->add(
            'From Europe ' . $segmentName, "continentCode==eur", $idSite = 1, $autoArchive = false, $enabledAllUsers = true);
        APISegmentEditor::getInstance()->add(
            "Multiple actions", "actions>=2", $idSite = 1, $autoArchive = false, $enabledAllUsers = true);
    }

    public function provideContainerConfig()
    {
        // make sure there's data for the auto suggest test
        API::$_autoSuggestLookBack = floor(Date::today()->getTimestamp() - Date::factory('2012-01-01')->getTimestamp()) / (24 * 60 * 60);

        return [
            'Tests.now' => \DI\decorate(function(){ return Option::get("Tests.forcedNowTimestamp"); }),
            'observers.global' => \DI\add([
                ['Report.addReports', function (&$reports) {
                    $report = new XssReport();
                    $report->initForXss('forTwig');
                    $reports[] = $report;

                    $report = new XssReport();
                    $report->initForXss('forAngular');
                    $reports[] = $report;
                }],
                ['Dimension.addDimensions', function (&$instances) {
                    $instances[] = new XssDimension();
                }],
                ['API.Request.intercept', function (&$result, $finalParameters, $pluginName, $methodName) {
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
                }],
            ]),
            Proxy::class => \DI\get(CustomApiProxy::class),
            'log.handlers' => \DI\decorate(function ($previous, ContainerInterface $c) {
                $previous[] = $c->get(WebNotificationHandler::class);
                return $previous;
            }),
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

        $this->metrics        = array('nb_visits');
        $this->order = 10;

        $action = Common::getRequestVar('actionToWidgetize', false) ?: Common::getRequestVar('action', false);
        if ($action == 'xssReportforTwig') {
            $this->initForXss('forTwig');
        } else if ($action == 'xssReportforAngular') {
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
        $this->id = 'ExampleAPI.xssReport' . $type;
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