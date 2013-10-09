<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Common;
use Piwik\FrontController;
use Piwik\Plugins\Annotations\API as APIAnnotations;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\WidgetsList;
use Piwik\Date;
use Piwik\Db;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Fixtures/ManySitesImportedLogs.php';

/**
 * Imports visits from several log files using the python log importer &
 * adds goals/sites/etc. attempting to create XSS.
 */
class Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts extends Test_Piwik_Fixture_ManySitesImportedLogs
{
    public $now = null;
    public $visitorIdForDeterministicDate = null;

    public function __construct()
    {
        $this->now = Date::factory('now');
    }
    
    public function setUp()
    {
        parent::setUp();

        $this->trackVisitsForRealtimeMap(Date::factory('2012-08-11 11:22:33'), $createSeperateVisitors = false);

        $this->visitorIdForDeterministicDate = bin2hex(Db::fetchOne(
            "SELECT idvisitor FROM " . Common::prefixTable('log_visit')
          . " WHERE idsite = 2 AND location_latitude IS NOT NULL LIMIT 1"));

        $this->setupDashboards();
        $this->setupXssSegment();
        $this->addAnnotations();
        $this->trackVisitsForRealtimeMap($this->now);
    }

    public function setUpWebsitesAndGoals()
    {
        // for conversion testing
        $siteName = self::makeXssContent("site name", $sanitize = true);
        self::createWebsite($this->dateTime, $ecommerce = 1, $siteName);
        APIGoals::getInstance()->addGoal(
            $this->idSite, self::makeXssContent("goal name"), 'url', 'http', 'contains', false, 5);
        
        self::createWebsite($this->dateTime, $ecommerce = 0, $siteName = 'Piwik test two',
            $siteUrl = 'http://example-site-two.com');
    }
    
    /** Creates two dashboards that split the widgets up into different groups. */
    public function setupDashboards()
    {
        $dashboardColumnCount = 3;
        $dashboardCount = 3;
        
        $layout = array();
        for ($j = 0; $j != $dashboardColumnCount; ++$j) {
            $layout[] = array();
        }

        $dashboards = array();
        for ($i = 0; $i != $dashboardCount; ++$i) {
            $dashboards[] = $layout;
        }
        
        $oldGet = $_GET;
        $_GET['idSite'] = $this->idSite;
        
        // collect widgets to add to the layout
        $allWidgets = WidgetsList::get();
        $groupedWidgets = array();
        $dashboard = 0;
        foreach ($allWidgets as $category => $widgets) {
            foreach ($widgets as $widget) {
                if ($widget['uniqueId'] == 'widgetSEOgetRank'
                    || $widget['uniqueId'] == 'widgetReferrersgetKeywordsForPage'
                    || $widget['uniqueId'] == 'widgetLivegetVisitorProfilePopup'
                    || strpos($widget['uniqueId'], 'widgetExample') === 0
                ) {
                    continue;
                }
                
                $dashboard = ($dashboard + 1) % $dashboardCount;
                $groupedWidgets[$dashboard][] = array(
                    'uniqueId' => $widget['uniqueId'],
                    'parameters' => $widget['parameters']
                );
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
            $_GET['name'] = self::makeXssContent('dashboard name' . $id);
            $_GET['layout'] = Common::json_encode($layout);
            $_GET['idDashboard'] = $id + 1;
            FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');
        }

        // create empty dashboard
        $widget = reset($allWidgets[\Piwik\Piwik::translate('UserSettings_VisitorSettings')]);
        $dashboard = array(
            array(
                array(
                    'uniqueId' => $widget['uniqueId'],
                    'parameters' => $widget['parameters']
                )
            ),
            array(),
            array()
        );

        $_GET['name'] = 'D4';
        $_GET['layout'] = Common::json_encode($dashboard);
        $_GET['idDashboard'] = 4;
        $_GET['idSite'] = 2;
        FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');
        
        $_GET = $oldGet;
    }
    
    public function setupXssSegment()
    {
        $segmentName = self::makeXssContent('segment');
        $segmentDefinition = "browserCode==FF";
        APISegmentEditor::getInstance()->add(
            $segmentName, $segmentDefinition, $this->idSite, $autoArchive = true, $enabledAllUsers = true);
    }
    
    public function addAnnotations()
    {
        APIAnnotations::getInstance()->add($this->idSite, '2012-08-09', "Note 1", $starred = 1);
        APIAnnotations::getInstance()->add(
            $this->idSite, '2012-08-08', self::makeXssContent("annotation"), $starred = 0);
        APIAnnotations::getInstance()->add($this->idSite, '2012-08-10', "Note 3", $starred = 1);
    }

    public function trackVisitsForRealtimeMap($date, $createSeperateVisitors = true)
    {
        $dateTime = $date->addHour(-1.25)->getDatetime();
        $idSite = 2;

        $t = self::getTracker($idSite, Date::factory($dateTime)->addHour(-3)->getDatetime(), $defaultInit = true, $useLocal = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->setUrl('http://example.org/index1.htm');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        if ($createSeperateVisitors) {
            $t = self::getTracker($idSite, $dateTime, $defaultInit = true, $useLocal = true);
        } else {
            $t->setForceVisitDateTime($dateTime);
        }

        $t->setTokenAuth(self::getTokenAuth());
        $t->setUrl('http://example.org/index1.htm');
        $t->setCountry('jp');
        $t->setRegion("40");
        $t->setCity('Tokyo');
        $t->setLatitude(35.70);
        $t->setLongitude(139.71);
        self::checkResponse($t->doTrackPageView('incredible title!'));

        if ($createSeperateVisitors) {
            $t = self::getTracker($idSite, Date::factory($dateTime)->addHour(0.5)->getDatetime(), $defaultInit = true, $useLocal = true);
        } else {
            $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.5)->getDatetime());
        }

        $t->setTokenAuth(self::getTokenAuth());
        $t->setUrl('http://example.org/index2.htm');
        $t->setCountry('ca');
        $t->setRegion("QC");
        $t->setCity('Montreal');
        $t->setLatitude(45.52);
        $t->setLongitude(-73.58);
        self::checkResponse($t->doTrackPageView('incredible title!'));

        if ($createSeperateVisitors) {
            $t = self::getTracker($idSite, Date::factory($dateTime)->addHour(1)->getDatetime(), $defaultInit = true, $useLocal = true);
        } else {
            $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDatetime());
        }

        $t->setTokenAuth(self::getTokenAuth());
        $t->setUrl('http://example.org/index3.htm');
        $t->setCountry('br');
        $t->setRegion("27");
        $t->setCity('Sao Paolo');
        $t->setLatitude(-23.55);
        $t->setLongitude(-46.64);
        self::checkResponse($t->doTrackPageView('incredible title!'));
    }
    
    // NOTE: since API_Request does sanitization, API methods do not. when calling them, we must
    // sometimes do sanitization ourselves.
    public static function makeXssContent($type, $sanitize = false)
    {
        $result = "<script>$('body').html('$type XSS!');</script>";
        if ($sanitize) {
            $result = Common::sanitizeInputValue($result);
        }
        return $result;
    }
}
