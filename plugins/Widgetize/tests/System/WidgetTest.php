<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Widgetize\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Http\ControllerResolver;
use Piwik\Piwik;
use Piwik\Plugins\Goals;
use Piwik\Plugins\Widgetize\tests\Fixtures\WidgetizeFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\WidgetsList;

/**
 * @group Widgetize
 * @group WidgetTest
 * @group Plugins
 */
class WidgetTest extends SystemTestCase
{
    /**
     * @var WidgetizeFixture
     */
    public static $fixture = null; // initialized below class definition

    public function setUp()
    {
        parent::setUp();

        $_GET = array();
        $_GET['idSite'] = self::$fixture->idSite;
        $_GET['period'] = 'year';
        $_GET['date']   = 'today';
    }

    public function tearDown()
    {
        $_GET = array();
        parent::tearDown();
    }

    public function test_AvailableWidgetListIsUpToDate()
    {
        $namesOfWidgetsThatAreAPI = $this->getWidgetNames($this->getWidgetsThatAreAPI());

        Piwik::postEvent('Platform.initialized'); // userCountryMap defines it's Widgets via this event currently

        $currentWidgetNames = array();
        foreach (WidgetsList::get() as $widgets) {
            $currentWidgetNames = array_merge($this->getWidgetNames($widgets), $currentWidgetNames);
        }

        $allWidgetNames = array_merge($namesOfWidgetsThatAreAPI, $currentWidgetNames);
        $regressedWidgetNames = array_diff($allWidgetNames, $currentWidgetNames);

        $this->assertEmpty($regressedWidgetNames, 'The widgets list is no longer up to date. If you added, removed or renamed a widget please update `getAvailableWidgets()` otherwise you will need to fix it. Different names: ' . var_export($regressedWidgetNames, 1));
    }

    private function getWidgetNames($widgets)
    {
        return array_map(function ($widget) {
            return $widget['name'];
        }, $widgets);
    }

    /**
     * @param array $widget
     *
     * @dataProvider availableWidgetsProvider
     */
    public function test_WidgetIsRenderable_ToPreventBreakingTheAPI($widget)
    {
        $params     = $widget['parameters'];
        $parameters = array();

        $resolver   = new ControllerResolver(StaticContainer::getContainer());
        $controller = $resolver->getController($params['module'], $params['action'], $parameters);

        $this->assertNotEmpty($controller, $widget['name'] . ' is not renderable with following params: ' . json_encode($params) . '. This breaks the API, please make sure to keep the URL working');
    }

    public function availableWidgetsProvider()
    {
        $widgets = $this->getWidgetsThatAreAPI();

        $data = array();

        foreach ($widgets as $widget) {
            if (!empty($widget)) {
                $data[] = array($widget);
            }
        }

        return $data;
    }

    /**
     * This is a list of all widgets that we consider API. We need to make sure the widgets will be still renderable
     * etc.
     * @return array
     */
    public function getWidgetsThatAreAPI()
    {
        return array (
                array (
                    'name' => 'Visits by Server Time',
                    'uniqueId' => 'widgetVisitTimegetVisitInformationPerServerTime',
                    'parameters' =>
                        array (
                            'module' => 'VisitTime',
                            'action' => 'getVisitInformationPerServerTime',
                        ),
                ),
                array (
                    'name' => 'Visits by Local Time',
                    'uniqueId' => 'widgetVisitTimegetVisitInformationPerLocalTime',
                    'parameters' =>
                        array (
                            'module' => 'VisitTime',
                            'action' => 'getVisitInformationPerLocalTime',
                        ),
                ),
                array (
                    'name' => 'Visits by Day of Week',
                    'uniqueId' => 'widgetVisitTimegetByDayOfWeek',
                    'parameters' =>
                        array (
                            'module' => 'VisitTime',
                            'action' => 'getByDayOfWeek',
                        ),
                ),
                array (
                    'name' => 'Visits Over Time',
                    'uniqueId' => 'widgetVisitsSummarygetEvolutionGraphcolumnsArray',
                    'parameters' =>
                        array (
                            'module' => 'VisitsSummary',
                            'action' => 'getEvolutionGraph',
                            'columns' =>
                                array (
                                    0 => 'nb_visits',
                                ),
                        ),
                ),
                array (
                    'name' => 'Visits Overview',
                    'uniqueId' => 'widgetVisitsSummarygetSparklines',
                    'parameters' =>
                        array (
                            'module' => 'VisitsSummary',
                            'action' => 'getSparklines',
                        ),
                ),
                array (
                    'name' => 'Visits Overview (with graph)',
                    'uniqueId' => 'widgetVisitsSummaryindex',
                    'parameters' =>
                        array (
                            'module' => 'VisitsSummary',
                            'action' => 'index',
                        ),
                ),
                array (
                    'name' => 'Real-time Map',
                    'uniqueId' => 'widgetUserCountryMaprealtimeMap',
                    'parameters' =>
                        array (
                            'module' => 'UserCountryMap',
                            'action' => 'realtimeMap',
                        ),
                ),
                array (
                    'name' => 'Visitor Log',
                    'uniqueId' => 'widgetLivegetVisitorLogsmall1',
                    'parameters' =>
                        array (
                            'module' => 'Live',
                            'action' => 'getVisitorLog',
                            'small' => 1,
                        ),
                ),
                array (
                    'name' => 'Real Time Visitor Count',
                    'uniqueId' => 'widgetLivegetSimpleLastVisitCount',
                    'parameters' =>
                        array (
                            'module' => 'Live',
                            'action' => 'getSimpleLastVisitCount',
                        ),
                ),
                array (
                    'name' => 'Visitors in Real-time',
                    'uniqueId' => 'widgetLivewidget',
                    'parameters' =>
                        array (
                            'module' => 'Live',
                            'action' => 'widget',
                        ),
                ),
                array (
                    'name' => 'Visitor profile',
                    'uniqueId' => 'widgetLivegetVisitorProfilePopup',
                    'parameters' =>
                        array (
                            'module' => 'Live',
                            'action' => 'getVisitorProfilePopup',
                        ),
                ),
                array (
                    'name' => 'Visitor Map',
                    'uniqueId' => 'widgetUserCountryMapvisitorMap',
                    'parameters' =>
                        array (
                            'module' => 'UserCountryMap',
                            'action' => 'visitorMap',
                        ),
                ),
                array (
                    'name' => 'Visitor Location (Country)',
                    'uniqueId' => 'widgetUserCountrygetCountry',
                    'parameters' =>
                        array (
                            'module' => 'UserCountry',
                            'action' => 'getCountry',
                        ),
                ),
                array (
                    'name' => 'Visitor Location (Continent)',
                    'uniqueId' => 'widgetUserCountrygetContinent',
                    'parameters' =>
                        array (
                            'module' => 'UserCountry',
                            'action' => 'getContinent',
                        ),
                ),
                array (
                    'name' => 'Visitor Location (Region)',
                    'uniqueId' => 'widgetUserCountrygetRegion',
                    'parameters' =>
                        array (
                            'module' => 'UserCountry',
                            'action' => 'getRegion',
                        ),
                ),
                array (
                    'name' => 'Visitor Location (City)',
                    'uniqueId' => 'widgetUserCountrygetCity',
                    'parameters' =>
                        array (
                            'module' => 'UserCountry',
                            'action' => 'getCity',
                        ),
                ),
                array (
                    'name' => 'Custom Variables',
                    'uniqueId' => 'widgetCustomVariablesgetCustomVariables',
                    'parameters' =>
                        array (
                            'module' => 'CustomVariables',
                            'action' => 'getCustomVariables',
                        ),
                ),
                array (
                    'name' => 'Length of Visits',
                    'uniqueId' => 'widgetVisitorInterestgetNumberOfVisitsPerVisitDuration',
                    'parameters' =>
                        array (
                            'module' => 'VisitorInterest',
                            'action' => 'getNumberOfVisitsPerVisitDuration',
                        ),
                ),
                array (
                    'name' => 'Pages per Visit',
                    'uniqueId' => 'widgetVisitorInterestgetNumberOfVisitsPerPage',
                    'parameters' =>
                        array (
                            'module' => 'VisitorInterest',
                            'action' => 'getNumberOfVisitsPerPage',
                        ),
                ),
                array (
                    'name' => 'Visits by Visit Number',
                    'uniqueId' => 'widgetVisitorInterestgetNumberOfVisitsByVisitCount',
                    'parameters' =>
                        array (
                            'module' => 'VisitorInterest',
                            'action' => 'getNumberOfVisitsByVisitCount',
                        ),
                ),
                array (
                    'name' => 'Visits by Days Since Last Visit',
                    'uniqueId' => 'widgetVisitorInterestgetNumberOfVisitsByDaysSinceLast',
                    'parameters' =>
                        array (
                            'module' => 'VisitorInterest',
                            'action' => 'getNumberOfVisitsByDaysSinceLast',
                        ),
                ),
                array (
                    'name' => 'Frequency Overview',
                    'uniqueId' => 'widgetVisitFrequencygetSparklines',
                    'parameters' =>
                        array (
                            'module' => 'VisitFrequency',
                            'action' => 'getSparklines',
                        ),
                ),
                array (
                    'name' => 'Returning Visits Over Time',
                    'uniqueId' => 'widgetVisitFrequencygetEvolutionGraphcolumnsArray',
                    'parameters' =>
                        array (
                            'module' => 'VisitFrequency',
                            'action' => 'getEvolutionGraph',
                            'columns' =>
                                array (
                                    0 => 'nb_visits_returning',
                                ),
                        ),
                ),
                array (
                    'name' => 'Screen Resolution',
                    'uniqueId' => 'widgetResolutiongetResolution',
                    'parameters' =>
                        array (
                            'module' => 'Resolution',
                            'action' => 'getResolution',
                        ),
                ),
                array (
                    'name' => 'Browser Plugins',
                    'uniqueId' => 'widgetDevicePluginsgetPlugin',
                    'parameters' =>
                        array (
                            'module' => 'DevicePlugins',
                            'action' => 'getPlugin',
                        ),
                ),
                array (
                    'name' => 'Visitor Configuration',
                    'uniqueId' => 'widgetResolutiongetConfiguration',
                    'parameters' =>
                        array (
                            'module' => 'Resolution',
                            'action' => 'getConfiguration',
                        ),
                ),
                array (
                    'name' => 'Browser language',
                    'uniqueId' => 'widgetUserLanguagegetLanguage',
                    'parameters' =>
                        array (
                            'module' => 'UserLanguage',
                            'action' => 'getLanguage',
                        ),
                ),
                array (
                    'name' => 'Language code',
                    'uniqueId' => 'widgetUserLanguagegetLanguageCode',
                    'parameters' =>
                        array (
                            'module' => 'UserLanguage',
                            'action' => 'getLanguageCode',
                        ),
                ),
                array (
                    'name' => 'Device type',
                    'uniqueId' => 'widgetDevicesDetectiongetType',
                    'parameters' =>
                        array (
                            'module' => 'DevicesDetection',
                            'action' => 'getType',
                        ),
                ),
                array (
                    'name' => 'Device brand',
                    'uniqueId' => 'widgetDevicesDetectiongetBrand',
                    'parameters' =>
                        array (
                            'module' => 'DevicesDetection',
                            'action' => 'getBrand',
                        ),
                ),
                array (
                    'name' => 'Visitor Browser',
                    'uniqueId' => 'widgetDevicesDetectiongetBrowsers',
                    'parameters' =>
                        array (
                            'module' => 'DevicesDetection',
                            'action' => 'getBrowsers',
                        ),
                ),
                array (
                    'name' => 'Device model',
                    'uniqueId' => 'widgetDevicesDetectiongetModel',
                    'parameters' =>
                        array (
                            'module' => 'DevicesDetection',
                            'action' => 'getModel',
                        ),
                ),
                array (
                    'name' => 'Browser version',
                    'uniqueId' => 'widgetDevicesDetectiongetBrowserVersions',
                    'parameters' =>
                        array (
                            'module' => 'DevicesDetection',
                            'action' => 'getBrowserVersions',
                        ),
                ),
                array (
                    'name' => 'Operating System families',
                    'uniqueId' => 'widgetDevicesDetectiongetOsFamilies',
                    'parameters' =>
                        array (
                            'module' => 'DevicesDetection',
                            'action' => 'getOsFamilies',
                        ),
                ),
                array (
                    'name' => 'Operating System versions',
                    'uniqueId' => 'widgetDevicesDetectiongetOsVersions',
                    'parameters' =>
                        array (
                            'module' => 'DevicesDetection',
                            'action' => 'getOsVersions',
                        ),
                ),
                array (
                    'name' => 'Browser engines',
                    'uniqueId' => 'widgetDevicesDetectiongetBrowserEngines',
                    'parameters' =>
                        array (
                            'module' => 'DevicesDetection',
                            'action' => 'getBrowserEngines',
                        ),
                ),
                array (
                    'name' => 'Pages',
                    'uniqueId' => 'widgetActionsgetPageUrls',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getPageUrls',
                        ),
                ),
                array (
                    'name' => 'Entry Pages',
                    'uniqueId' => 'widgetActionsgetEntryPageUrls',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getEntryPageUrls',
                        ),
                ),
                array (
                    'name' => 'Exit Pages',
                    'uniqueId' => 'widgetActionsgetExitPageUrls',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getExitPageUrls',
                        ),
                ),
                array (
                    'name' => 'Page Titles',
                    'uniqueId' => 'widgetActionsgetPageTitles',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getPageTitles',
                        ),
                ),
                array (
                    'name' => 'Entry Page Titles',
                    'uniqueId' => 'widgetActionsgetEntryPageTitles',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getEntryPageTitles',
                        ),
                ),
                array (
                    'name' => 'Exit Page Titles',
                    'uniqueId' => 'widgetActionsgetExitPageTitles',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getExitPageTitles',
                        ),
                ),
                array (
                    'name' => 'Outlinks',
                    'uniqueId' => 'widgetActionsgetOutlinks',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getOutlinks',
                        ),
                ),
                array (
                    'name' => 'Downloads',
                    'uniqueId' => 'widgetActionsgetDownloads',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getDownloads',
                        ),
                ),
                array (
                    'name' => 'Content Name',
                    'uniqueId' => 'widgetContentsgetContentNames',
                    'parameters' =>
                        array (
                            'module' => 'Contents',
                            'action' => 'getContentNames',
                        ),
                ),
                array (
                    'name' => 'Content Piece',
                    'uniqueId' => 'widgetContentsgetContentPieces',
                    'parameters' =>
                        array (
                            'module' => 'Contents',
                            'action' => 'getContentPieces',
                        ),
                ),
                array (
                    'name' => 'Event Categories',
                    'uniqueId' => 'widgetEventsgetCategorysecondaryDimensioneventAction',
                    'parameters' =>
                        array (
                            'module' => 'Events',
                            'action' => 'getCategory',
                            'secondaryDimension' => 'eventAction',
                        ),
                ),
                array (
                    'name' => 'Event Actions',
                    'uniqueId' => 'widgetEventsgetActionsecondaryDimensioneventName',
                    'parameters' =>
                        array (
                            'module' => 'Events',
                            'action' => 'getAction',
                            'secondaryDimension' => 'eventName',
                        ),
                ),
                array (
                    'name' => 'Event Names',
                    'uniqueId' => 'widgetEventsgetNamesecondaryDimensioneventAction',
                    'parameters' =>
                        array (
                            'module' => 'Events',
                            'action' => 'getName',
                            'secondaryDimension' => 'eventAction',
                        ),
                ),
                array (
                    'name' => 'Site Search Keywords',
                    'uniqueId' => 'widgetActionsgetSiteSearchKeywords',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getSiteSearchKeywords',
                        ),
                ),
                array (
                    'name' => 'Search Keywords with No Results',
                    'uniqueId' => 'widgetActionsgetSiteSearchNoResultKeywords',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getSiteSearchNoResultKeywords',
                        ),
                ),
                array (
                    'name' => 'Search Categories',
                    'uniqueId' => 'widgetActionsgetSiteSearchCategories',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getSiteSearchCategories',
                        ),
                ),
                array (
                    'name' => 'Pages Following a Site Search',
                    'uniqueId' => 'widgetActionsgetPageUrlsFollowingSiteSearch',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getPageUrlsFollowingSiteSearch',
                        ),
                ),
                array (
                    'name' => 'Page Titles Following a Site Search',
                    'uniqueId' => 'widgetActionsgetPageTitlesFollowingSiteSearch',
                    'parameters' =>
                        array (
                            'module' => 'Actions',
                            'action' => 'getPageTitlesFollowingSiteSearch',
                        ),
                ),
                array (
                    'name' => 'Overview',
                    'uniqueId' => 'widgetReferrersgetReferrerType',
                    'parameters' =>
                        array (
                            'module' => 'Referrers',
                            'action' => 'getReferrerType',
                        ),
                ),
                array (
                    'name' => 'All Referrers',
                    'uniqueId' => 'widgetReferrersgetAll',
                    'parameters' =>
                        array (
                            'module' => 'Referrers',
                            'action' => 'getAll',
                        ),
                ),
                array (
                    'name' => 'Keywords',
                    'uniqueId' => 'widgetReferrersgetKeywords',
                    'parameters' =>
                        array (
                            'module' => 'Referrers',
                            'action' => 'getKeywords',
                        ),
                ),
                array (
                    'name' => 'Referrer Websites',
                    'uniqueId' => 'widgetReferrersgetWebsites',
                    'parameters' =>
                        array (
                            'module' => 'Referrers',
                            'action' => 'getWebsites',
                        ),
                ),
                array (
                    'name' => 'Search Engines',
                    'uniqueId' => 'widgetReferrersgetSearchEngines',
                    'parameters' =>
                        array (
                            'module' => 'Referrers',
                            'action' => 'getSearchEngines',
                        ),
                ),
                array (
                    'name' => 'Campaigns',
                    'uniqueId' => 'widgetReferrersgetCampaigns',
                    'parameters' =>
                        array (
                            'module' => 'Referrers',
                            'action' => 'getCampaigns',
                        ),
                ),
                array (
                    'name' => 'List of social networks',
                    'uniqueId' => 'widgetReferrersgetSocials',
                    'parameters' =>
                        array (
                            'module' => 'Referrers',
                            'action' => 'getSocials',
                        ),
                ),
                array (
                    'name' => 'Goals Overview',
                    'uniqueId' => 'widgetGoalswidgetGoalsOverview',
                    'parameters' =>
                        array (
                            'module' => 'Goals',
                            'action' => 'widgetGoalsOverview',
                        ),
                ),
                array (
                    'name' => 'Download Software',
                    'uniqueId' => 'widgetGoalswidgetGoalReportidGoal1',
                    'parameters' =>
                        array (
                            'module' => 'Goals',
                            'action' => 'widgetGoalReport',
                            'idGoal' => '1',
                        ),
                ),
                array (
                    'name' => 'Download Software2',
                    'uniqueId' => 'widgetGoalswidgetGoalReportidGoal2',
                    'parameters' =>
                        array (
                            'module' => 'Goals',
                            'action' => 'widgetGoalReport',
                            'idGoal' => '2',
                        ),
                ),
                array (
                    'name' => 'Opens Contact Form',
                    'uniqueId' => 'widgetGoalswidgetGoalReportidGoal3',
                    'parameters' =>
                        array (
                            'module' => 'Goals',
                            'action' => 'widgetGoalReport',
                            'idGoal' => '3',
                        ),
                ),
                array (
                    'name' => 'Visit Docs',
                    'uniqueId' => 'widgetGoalswidgetGoalReportidGoal4',
                    'parameters' =>
                        array (
                            'module' => 'Goals',
                            'action' => 'widgetGoalReport',
                            'idGoal' => '4',
                        ),
                ),
                array (
                    'name' => 'Product SKU',
                    'uniqueId' => 'widgetGoalsgetItemsSku',
                    'parameters' =>
                        array (
                            'module' => 'Goals',
                            'action' => 'getItemsSku',
                        ),
                ),
                array (
                    'name' => 'Product Name',
                    'uniqueId' => 'widgetGoalsgetItemsName',
                    'parameters' =>
                        array (
                            'module' => 'Goals',
                            'action' => 'getItemsName',
                        ),
                ),
                array (
                    'name' => 'Product Category',
                    'uniqueId' => 'widgetGoalsgetItemsCategory',
                    'parameters' =>
                        array (
                            'module' => 'Goals',
                            'action' => 'getItemsCategory',
                        ),
                ),
                array (
                    'name' => 'Overview',
                    'uniqueId' => 'widgetEcommercewidgetGoalReportidGoalecommerceOrder',
                    'parameters' =>
                        array (
                            'module' => 'Ecommerce',
                            'action' => 'widgetGoalReport',
                            'idGoal' => 'ecommerceOrder',
                        ),
                ),
                array (
                    'name' => 'Ecommerce Log',
                    'uniqueId' => 'widgetEcommercegetEcommerceLog',
                    'parameters' =>
                        array (
                            'module' => 'Ecommerce',
                            'action' => 'getEcommerceLog',
                        ),
                ),
                array (
                    'name' => 'Insights Overview',
                    'uniqueId' => 'widgetInsightsgetInsightsOverview',
                    'parameters' =>
                        array (
                            'module' => 'Insights',
                            'action' => 'getInsightsOverview',
                        ),
                ),
                array (
                    'name' => 'Movers and Shakers',
                    'uniqueId' => 'widgetInsightsgetOverallMoversAndShakers',
                    'parameters' =>
                        array (
                            'module' => 'Insights',
                            'action' => 'getOverallMoversAndShakers',
                        ),
                ),
                array (
                    'name' => 'Top Keywords for Page URL',
                    'uniqueId' => 'widgetReferrersgetKeywordsForPage',
                    'parameters' =>
                        array (
                            'module' => 'Referrers',
                            'action' => 'getKeywordsForPage',
                        ),
                ),
                array (
                    'name' => 'SEO Rankings',
                    'uniqueId' => 'widgetSEOgetRank',
                    'parameters' =>
                        array (
                            'module' => 'SEO',
                            'action' => 'getRank',
                        ),
                ),
                array (
                    'name' => 'Support Piwik!',
                    'uniqueId' => 'widgetCoreHomegetDonateForm',
                    'parameters' =>
                        array (
                            'module' => 'CoreHome',
                            'action' => 'getDonateForm',
                        ),
                ),
                array (
                    'name' => 'Welcome!',
                    'uniqueId' => 'widgetCoreHomegetPromoVideo',
                    'parameters' =>
                        array (
                            'module' => 'CoreHome',
                            'action' => 'getPromoVideo',
                        ),
                ),
                array (
                    'name' => 'Piwik.org Blog',
                    'uniqueId' => 'widgetExampleRssWidgetrssPiwik',
                    'parameters' =>
                        array (
                            'module' => 'ExampleRssWidget',
                            'action' => 'rssPiwik',
                        ),
                ),
                array (
                    'name' => 'Piwik Changelog',
                    'uniqueId' => 'widgetExampleRssWidgetrssChangelog',
                    'parameters' =>
                        array (
                            'module' => 'ExampleRssWidget',
                            'action' => 'rssChangelog',
                        ),
                ),
                array (
                    'name' => 'Piwik PRO Blog',
                    'uniqueId' => 'widgetPiwikProrssPiwikPro',
                    'parameters' =>
                        array (
                            'module' => 'PiwikPro',
                            'action' => 'rssPiwikPro',
                        ),
                ),
                array (
                    'name' => 'Piwik PRO: Advanced Analytics & Services',
                    'uniqueId' => 'widgetPiwikPropromoPiwikProPiwikPro',
                    'parameters' =>
                        array (
                            'module' => 'PiwikPro',
                            'action' => 'promoPiwikPro',
                        ),
                )
        );
    }
}


WidgetTest::$fixture = new WidgetizeFixture();