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

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Fixtures/ManySitesImportedLogs.php';

/**
 * Imports visits from several log files using the python log importer &
 * adds goals/sites/etc. attempting to create XSS.
 */
class Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts extends Test_Piwik_Fixture_ManySitesImportedLogs
{
    public function setUp()
    {
        parent::setUp();
        
        $this->setupDashboards();
        $this->setupXssSegment();
        $this->addAnnotations();
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
        
        $dashboards = array();
        for ($i = 0; $i != $dashboardCount; ++$i) {
            $layout = array();
            for ($j = 0; $j != $dashboardColumnCount; ++$j) {
                $layout[] = array();
            }
            
            $dashboards[] = $layout;
        }
        
        $oldGet = $_GET;
        $_GET['idSite'] = $this->idSite;
        
        // collect widgets to add to the layout
        $groupedWidgets = array();
        $dashboard = 0;
        foreach (WidgetsList::get() as $category => $widgets) {
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
