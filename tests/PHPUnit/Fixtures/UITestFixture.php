<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Exception;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\FrontController;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\Config as PiwikConfig;

/**
 * Fixture for UI tests.
 */
class UITestFixture extends SqlDump
{
    const FIXTURE_LOCATION = '/tests/resources/OmniFixture-dump.sql';

    public function __construct()
    {
        $this->dumpUrl = PIWIK_INCLUDE_PATH . self::FIXTURE_LOCATION;
        $this->tablesPrefix = '';
    }

    public function setUp()
    {
        parent::setUp();

        self::resetPluginsInstalledConfig();
        self::updateDatabase();
        self::installAndActivatePlugins($this->getTestEnvironment());

        // make sure site has an early enough creation date (for period selector tests)
        Db::get()->update(Common::prefixTable("site"),
            array('ts_created' => '2011-01-01'),
            "idsite = 1"
        );

        // for proper geolocation
        GeoIp2::$geoIPDatabaseDir = 'tests/lib/geoip-files';
        LocationProvider::setCurrentProvider(GeoIp2\Php::ID);
        IPAnonymizer::deactivate();

        $this->addOverlayVisits();
        $this->addNewSitesForSiteSelector();

        DbHelper::createAnonymousUser();
        UsersManagerAPI::getInstance()->setSuperUserAccess('superUserLogin', true);
        SitesManagerAPI::getInstance()->updateSite(1, null, null, true);

        // create non super user
        UsersManagerAPI::getInstance()->addUser('oliverqueen', 'smartypants', 'oli@queenindustries.com');
        UsersManagerAPI::getInstance()->setUserAccess('oliverqueen', 'view', array(1));
    }

    public function performSetUp($setupEnvironmentOnly = false)
    {
        $this->extraTestEnvVars = array(
            'loadRealTranslations' => 1,
        );

        parent::performSetUp($setupEnvironmentOnly);

        $this->createSegments();
        $this->setupDashboards();

        $visitorIdDeterministic = bin2hex(Db::fetchOne(
            "SELECT idvisitor FROM " . Common::prefixTable('log_visit')
            . " WHERE idsite = 2 AND location_latitude IS NOT NULL LIMIT 1"));
        $this->testEnvironment->forcedIdVisitor = $visitorIdDeterministic;

        $this->testEnvironment->overlayUrl = $this->getLocalTestSiteUrl();
        $this->createOverlayTestSite();

        $forcedNowTimestamp = Option::get("Tests.forcedNowTimestamp");
        if ($forcedNowTimestamp == false) {
            throw new Exception("Incorrect fixture setup, Tests.forcedNowTimestamp option does not exist! Run the setup again.");
        }

        $this->testEnvironment->forcedNowTimestamp = $forcedNowTimestamp;
        $this->testEnvironment->save();

        // launch archiving so tests don't run out of time
        print("Archiving in fixture set up...");
        VisitsSummaryAPI::getInstance()->get('all', 'year', '2012-08-09');
        VisitsSummaryAPI::getInstance()->get('all', 'year', '2012-08-09', urlencode(OmniFixture::DEFAULT_SEGMENT));
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
            "20.56.34.67",
            "24.17.88.121",
            "24.12.45.67",
            "24.120.12.5",
            "24.100.12.5",
            "24.110.12.5",
            "24.17.88.122",
            "24.12.45.68",
            "24.17.88.123",
            "24.18.127.34",
            "18.50.45.71",
            "24.20.127.34",
            "24.23.40.34",
            "18.50.45.70",
            "24.50.12.5",
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

    private function createOverlayTestSite()
    {
        $realDir = PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site-real";
        if (is_dir($realDir)) {
            return;
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
            file_put_contents($path, $contents);
        }
    }

    private function getLocalTestSiteUrl()
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
        $_GET['token_auth'] = Piwik::getCurrentUserTokenAuth();

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
                || $widget['uniqueId'] == 'widgetReferrersgetKeywordsForPage'
                || $widget['uniqueId'] == 'widgetLivegetVisitorProfilePopup'
                || $widget['uniqueId'] == 'widgetActionsgetPageTitles'
                || strpos($widget['uniqueId'], 'widgetExample') === 0
            ) {
                continue;
            }

            $widgetEntry = array(
                'uniqueId' => $widget['uniqueId'],
                'parameters' => $widget['parameters']
            );

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
                $_GET['name'] = self::makeXssContent('dashboard name' . $id);
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

        $segmentName = self::makeXssContent('segment');
        $segmentDefinition = "browserCode==FF";
        APISegmentEditor::getInstance()->add(
            $segmentName, $segmentDefinition, $idSite = 1, $autoArchive = true, $enabledAllUsers = true);

        // create two more segments
        APISegmentEditor::getInstance()->add(
            "From Europe", "continentCode==eur", $idSite = 1, $autoArchive = false, $enabledAllUsers = true);
        APISegmentEditor::getInstance()->add(
            "Multiple actions", "actions>=2", $idSite = 1, $autoArchive = false, $enabledAllUsers = true);
    }
}
