<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Piwik;
use Piwik\Access;
use Piwik\AssetManager;
use Piwik\Plugins\VisitsSummary\API;

/**
 * Tests UI code by grabbing screenshots of webpages and comparing with expected files.
 * 
 * Uses slimerjs or phantomjs.
 * 
 * TODO:
 * - allow instrumentation javascript to be injected before screenshot is taken (so we can, say,
 *   take a screenshot of column documentation)
 */
class Test_Piwik_Integration_UIIntegrationTest extends IntegrationTestCase
{
    const IMAGE_TYPE = 'png';
    const CAPTURE_PROGRAM = 'phantomjs';
    
    public static $fixture = null; // initialized below class definition
    
    public static function createAccessInstance()
    {
        Access::setSingletonInstance($access = new Test_Access_OverrideLogin());
        Piwik_PostEvent('FrontController.initAuthenticationObject');
    }
    
    public static function setUpBeforeClass()
    {
        if (!self::isXvfbAvailable()) {
            self::markTestSkipped("xvfb is not available, skipping UI integration tests. (install with 'sudo apt-get install xvfb')");
        } else if (self::CAPTURE_PROGRAM == 'slimerjs'
                   && !self::isSlimerJsAvailable()
        ) {
            self::markTestSkipped("slimerjs is not available, skipping UI integration tests. "
                                . "(install by downloading http://slimerjs.org/download.html)");
        } else if (self::CAPTURE_PROGRAM == 'phantomjs'
                   && !self::isPhantomJsAvailable()
        ) {
            self::markTestSkipped("phantomjs is not available, skipping UI integration tests. "
                                . "(install by downloading http://phantomjs.org/download.html)");
        } else {
            self::markTestSkipped("No way to take screenshots of URLs, skipping UI integration tests. (Enable by installing "
                                . "phantomjs (http://phantomjs.org/download.html) or slimerjs (http://slimerjs.org/download.html).");
        }
        
        parent::setUpBeforeClass();
        
        AssetManager::removeMergedAssets();
        
        // launch archiving so tests don't run out of time
        API::getInstance()->get(self::$fixture->idSite, 'year', '2012-08-09');

        // make sure processed & expected dirs exist
        list($processedDir, $expectedDir) = self::getProcessedAndExpectedDirs();
        if (!is_dir($processedDir)) {
            mkdir($processedDir);
        }
        if (!is_dir($expectedDir)) {
            mkdir($expectedDir);
        }

        // run slimerjs/phantomjs w/ all urls so we only invoke it once
        $urls = array();
        foreach (self::getUrlsForTesting() as $testInfo) {
            list($name, $urlQuery) = $testInfo;

            list($processedScreenshotPath, $expectedScreenshotPath) = self::getProcessedAndExpectedScreenshotPaths($name);
            $urls[] = array($processedScreenshotPath, self::getProxyUrl() . $urlQuery);
        }
        
        echo "Generating screenshots...\n";
        self::runCaptureProgram($urls);
    }
    
    public static function tearDownAfterClass()
    {
        if (file_exists("C:\\nppdf32Log\\debuglog.txt")) { // remove slimerjs oddity
            unlink("C:\\nppdf32Log\\debuglog.txt");
        }

        if (!Zend_Registry::get('db')) {
            Piwik::createDatabaseObject();
        }
        
        parent::tearDownAfterClass();
    }
    
    public function setUp()
    {
        parent::setUp();
        
        if (!Zend_Registry::get('db')) {
            Piwik::createDatabaseObject();
        }
    }
    
    public function tearDown()
    {
        parent::tearDown();
        
        \Zend_Registry::get('db')->closeConnection();
        \Zend_Registry::set('db', false);
    }
    
    public static function getUrlsForTesting()
    {
        $generalParams = 'idSite=1&period=week&date=2012-08-09';
        $evolutionParams = 'idSite=1&period=day&date=2012-08-11&evolution_day_last_n=30';
        $urlBase = 'module=CoreHome&action=index&' . $generalParams;
        $widgetizeParams = "module=Widgetize&action=iframe";
        $segment = urlencode("browserCode==FF");

        return array(
            // dashboard
            array('dashboard1', "?$urlBase#$generalParams&module=Dashboard&action=embeddedIndex&idDashboard=1"),
            array('dashboard2', "?$urlBase#$generalParams&module=Dashboard&action=embeddedIndex&idDashboard=2"),
            array('dashboard3', "?$urlBase#$generalParams&module=Dashboard&action=embeddedIndex&idDashboard=3"),
            
            // visitors pages (except real time map since it displays current time)
            array('visitors_overview', "?$urlBase#$generalParams&module=VisitsSummary&action=index"),
            array('visitors_visitorlog', "?$urlBase#$generalParams&module=Live&action=indexVisitorLog"),
            array('visitors_devices', "?$urlBase#$generalParams&module=DevicesDetection&action=index"),
            array('visitors_locations_provider', "?$urlBase#$generalParams&module=UserCountry&action=index"),
            array('visitors_settings', "?$urlBase#$generalParams&module=UserSettings&action=index"),
            array('visitors_times', "?$urlBase#$generalParams&module=VisitTime&action=index"),
            array('visitors_engagement', "?$urlBase#$generalParams&module=VisitFrequency&action=index"),
            array('visitors_custom_vars', "?$urlBase#$generalParams&module=CustomVariables&action=index"),
            
            // actions pages
            array('actions_pages', "?$urlBase#$generalParams&module=Actions&action=indexPageUrls"),
            array('actions_entry_pages', "?$urlBase#$generalParams&module=Actions&action=indexEntryPageUrls"),
            array('actions_exit_pages', "?$urlBase#$generalParams&module=Actions&action=indexExitPageUrls"),
            array('actions_page_titles', "?$urlBase#$generalParams&module=Actions&action=indexPageTitles"),
            array('actions_site_search', "?$urlBase#$generalParams&module=Actions&action=indexSiteSearch"),
            array('actions_outlinks', "?$urlBase#$generalParams&module=Actions&action=indexOutlinks"),
            array('actions_downloads', "?$urlBase#$generalParams&module=Actions&action=indexDownloads"),

            // referrers pages
            array('referrers_overview', "?$urlBase#$generalParams&module=Referers&action=index"),
            array('referrers_search_engines_keywords',
                  "?$urlBase#$generalParams&module=Referers&action=getSearchEnginesAndKeywords"),
            array('referrers_websites_social', "?$urlBase#$generalParams&module=Referers&action=indexWebsites"),
            array('referrers_campaigns', "?$urlBase#$generalParams&module=Referers&action=indexCampaigns"),
            
            // goals pages
            array('goals_ecommerce',
                  "?$urlBase#$generalParams&module=Goals&action=ecommerceReport&idGoal=ecommerceOrder"),
            array('goals_overview', "?$urlBase#$generalParams&module=Goals&action=index"),
            array('goals_individual_goal', "?$urlBase#$generalParams&module=Goals&action=goalReport&idGoal=1"),
            
            // one page w/ segment
            array('visitors_overview_segment',
                  "?$urlBase#$generalParams&module=VisitsSummary&action=index&segment=$segment"),
            
            // widgetize
            array("widgetize_visitor_log",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=Live&actionToWidgetize=getVisitorLog"),
            array("widgetize_html_table",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=table"),
            array("widgetize_goals_table",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=tableGoals"),
            array("widgetize_goals_table_ecommerce",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=tableGoals&idGoal=ecommerceOrder"),
            array("widgetize_goals_table_single",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=tableGoals&idGoal=1"),
            array("widgetize_goals_table_full",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=tableGoals&idGoal=0"),
            array("widgetize_all_columns_table",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=tableAllColumns"),
            array("widgetize_pie_graph",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=graphPie"),
            array("widgetize_bar_graph",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=graphVerticalBar"),
            array("widgetize_evolution_graph",
                  "?$widgetizeParams&$evolutionParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=graphEvolution"),
            array("widgetize_tag_cloud",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry"
                . "&viewDataTable=cloud"),
            array("widgetize_actions_search",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls"
                . "&filter_column_recursive=label&filter_pattern_recursive=i"),
            array("widgetize_actions_flat",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls"
                . "&flat=1"),
            array("widgetize_actions_excludelowpop",
                  "?$widgetizeParams&$generalParams&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls"
                . "&enable_filter_excludelowpop=1"),
            
            // row evolution
            array("row_evolution_popup",
                  "?$widgetizeParams&moduleToWidgetize=CoreHome&actionToWidgetize=getRowEvolutionPopover"
                . "&apiMethod=UserSettings.getBrowser&label=Chrome&disableLink=1&idSite=1&period=day"
                . "&date=2012-08-11"),
            array("multi_row_evolution_popup",
                  "?$widgetizeParams&moduleToWidgetize=CoreHome&actionToWidgetize=getMultiRowEvolutionPopover"
                . "&label=" . urlencode("Chrome,Firefox") . "&apiMethod=UserSettings.getBrowser&idSite=1&period=day"
                . "&date=2012-08-11&disableLink=1"),
        );
    }

    /**
     * @dataProvider getUrlsForTesting
     * @group        Integration
     * @group        UITests
     */
    public function testUIUrl($name, $urlQuery)
    {
        list($processedScreenshotPath, $expectedScreenshotPath) = self::getProcessedAndExpectedScreenshotPaths($name);
        
        // compare processed w/ expected
        $this->compareScreenshot($name, $expectedScreenshotPath, $processedScreenshotPath, $urlQuery);
    }
    
    private static function runCaptureProgram($urlInfo)
    {
        file_put_contents(PIWIK_INCLUDE_PATH . '/tmp/urls.txt', json_encode($urlInfo));
        $cmd = self::CAPTURE_PROGRAM . " \"" . PIWIK_INCLUDE_PATH . "/tests/resources/screenshot-capture/capture.js\" 2>&1";
        $cmd = 'xvfb-run --server-args="-screen 0, 1024x768x24" ' . $cmd;
        
        exec($cmd, $output, $result);
        $output = implode("\n", $output);
        if ($result !== 0
            || strpos($output, "ERROR") !== false
        ) {
            echo self::CAPTURE_PROGRAM . " failed: " . $output . "\n\ncommand used: $cmd\n";
            throw new Exception("phantomjs failed");
        }
        return $output;
    }
    
    private function compareScreenshot($name, $expectedPath, $processedPath, $urlQuery)
    {
        $processed = file_get_contents($processedPath);
        
        if (!file_exists($expectedPath)) {
            $this->markTestIncomplete("expected screenshot for processed '$processedPath' is missing");
        }
        
        $expected = file_get_contents($expectedPath);
        if ($expected != $processed) {
            echo "\nFail: '$processedPath' for '$urlQuery'\n";
        }
        $this->assertTrue($expected == $processed, "screenshot compare failed for '$processedPath'");
    }
    
    private static function isSlimerJsAvailable()
    {
        return self::isProgramAvailable('slimerjs');
    }

    private static function isPhantomJsAvailable()
    {
        return self::isProgramAvaialble('phantomjs');
    }
    
    private static function isXvfbAvailable()
    {
        return self::isProgramAvailable('xvfb-run');
    }

    private static function isProgramAvailable($name)
    {
        exec($name . ' --help 2>&1', $output, $result);
        return $result === 0 || $result === 1;
    }

    private static function getProcessedAndExpectedScreenshotPaths($name)
    {
        list($processedDir, $expectedDir) = self::getProcessedAndExpectedDirs();

        $processedScreenshotPath = $processedDir . "$name." . self::IMAGE_TYPE;
        $expectedScreenshotPath = $expectedDir . "$name." . self::IMAGE_TYPE;

        return array($processedScreenshotPath, $expectedScreenshotPath);
    }
    
    protected static function getProcessedAndExpectedDirs()
    {
        $path = self::getPathToTestDirectory() . '/../UI';
        return array($path . '/processed-ui-screenshots/', $path . '/expected-ui-screenshots/');
    }
    
    public static function getProxyUrl()
    {
        return Test_Piwik_BaseFixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php';
    }
}

Test_Piwik_Integration_UIIntegrationTest::$fixture = new Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts();