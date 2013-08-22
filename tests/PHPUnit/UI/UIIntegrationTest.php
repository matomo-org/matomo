<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Tests UI code by grabbing screenshots of webpages and comparing with expected files.
 * 
 * Uses slimerjs or phantomjs.
 * 
 * TODO:
 * - allow instrumentation javascript to be injected before screenshot is taken (so we can, say,
 *   take a screenshot of column documentation)
 */
class Test_Piwik_Integration_UIIntegrationTest extends UITest
{
    public static $fixture = null; // initialized below class definition    
    
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
        // compare processed w/ expected
        $this->compareScreenshot($name, $urlQuery);
    }
}

Test_Piwik_Integration_UIIntegrationTest::$fixture = new Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts();