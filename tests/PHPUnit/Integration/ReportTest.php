<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\API\Proxy;
use Piwik\Columns\Dimension;
use Piwik\Container\StaticContainer;
use Piwik\DataTable\Row;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;
use Piwik\Plugins\DevicesDetection\Columns\BrowserName;
use Piwik\Plugins\ExampleReport\Reports\GetExampleReport;
use Piwik\Plugins\Actions\Columns\ExitPageUrl;
use Piwik\Piwik;
use Piwik\Metrics;
use Piwik\Plugins\ExampleTracker\Columns\ExampleDimension;
use Piwik\Plugins\Goals\Columns\Metrics\RevenuePerVisit;
use Piwik\Plugins\Referrers\Columns\Keyword;
use Piwik\Plugin\ReportsProvider;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetsList;

class GetBasicReport extends Report
{
    protected function init()
    {
        parent::init();

        $this->dimension = new BrowserName();
        $this->name = 'My Custom Report Name';
        $this->order  = 20;
        $this->module = 'TestPlugin';
        $this->action = 'getBasicReport';
        $this->categoryId = 'Goals_Goals';
        $this->actionToLoadSubTables = 'invalidReport';
    }
}

class AdvancedProcessedMetric extends ProcessedMetric
{
    public function getName()
    {
        return 'advancedmetric';
    }

    public function getTranslatedName()
    {
        return 'MyPlugin_AdvancedMetric';
    }

    public function compute(Row $row)
    {
        // unimplemented (not required for test)
    }

    public function getDependentMetrics()
    {
        return [];
    }

    public function getExtraMetricSemanticTypes(): array
    {
        return [
            'intermediate_value' => Dimension::TYPE_NUMBER,
        ];
    }

    public function getExtraMetricAggregationTypes(): array
    {
        return [
            'intermediate_value' => Metric::AGGREGATION_TYPE_SUM,
        ];
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }
}

class GetAdvancedReport extends GetBasicReport
{
    protected function init()
    {
        parent::init();

        $this->action      = 'getAdvancedReport';
        $this->subcategoryId = 'Actions_SubmenuPageTitles';
        $this->documentation = Piwik::translate('ExampleReportDocumentation');
        $this->dimension   = new ExitPageUrl();
        $this->metrics     = array('nb_actions', 'nb_visits');
        $this->processedMetrics = [
            'conversion_rate',
            'bounce_rate',
            new RevenuePerVisit(),
            new AdvancedProcessedMetric(),
        ];
        $this->parameters = array('idGoal' => 1);
        $this->isSubtableReport = true;
        $this->actionToLoadSubTables = 'GetBasicReport';
        $this->constantRowsCount = true;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widget = $factory->createWidget()->setName('Actions_WidgetPageTitlesFollowingSearch');
        $widgetsList->addWidgetConfig($widget);
    }

    public function set($param, $value)
    {
        $this->$param = $value;
    }
}

class GetDisabledReport extends GetBasicReport
{
    public function isEnabled()
    {
        return false;
    }
}

/**
 * @group Core
 */
class ReportTest extends IntegrationTestCase
{
    /**
     * @var Report
     */
    private $exampleReport;

    /**
     * @var GetDisabledReport
     */
    private $disabledReport;

    /**
     * @var GetBasicReport
     */
    private $basicReport;

    /**
     * @var GetAdvancedReport
     */
    private $advancedReport;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');
        $_GET['idSite'] = 1;

        $this->exampleReport  = new GetExampleReport();
        $this->disabledReport = new GetDisabledReport();
        $this->basicReport    = new GetBasicReport();
        $this->advancedReport = new GetAdvancedReport();
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        parent::tearDown();
    }

    public function testShouldDetectTheModuleOfTheReportAutomatically()
    {
        $this->assertEquals('ExampleReport', $this->exampleReport->getModule());
    }

    public function testShouldDetectTheActionOfTheReportAutomatiacally()
    {
        $this->assertEquals('getExampleReport', $this->exampleReport->getAction());
    }

    public function testGetNameShouldReturnTheNameOfTheReport()
    {
        $this->assertEquals('My Custom Report Name', $this->basicReport->getName());
    }

    public function testIsEnabledShouldBeEnabledByDefault()
    {
        $this->assertTrue($this->basicReport->isEnabled());
    }

    public function testCheckIsEnabledShouldThrowAnExceptionIfReportIsNotEnabled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionReportNotEnabled');

        $this->disabledReport->checkIsEnabled();
    }

    public function testGetCategoryShouldReturnTranslatedCategory()
    {
        Fixture::loadAllTranslations();
        $this->assertEquals('Goals_Goals', $this->advancedReport->getCategoryId());
        Fixture::resetTranslations();
    }

    public function testGetMetricsShouldUseDefaultMetrics()
    {
        $this->assertEquals(Metrics::getDefaultMetrics(), $this->basicReport->getMetrics());
    }

    public function testGetMetricsShouldReturnEmptyArrayIfNoMetricsDefined()
    {
        $this->advancedReport->set('metrics', array());
        $this->assertEquals(array(), $this->advancedReport->getMetrics());
    }

    public function testGetMetricsShouldFindTranslationsForMetricsAndReturnOnlyTheOnesDefinedInSameOrder()
    {
        $expected = array(
            'nb_visits'  => 'General_ColumnNbVisits',
            'nb_actions' => 'General_ColumnNbActions'
        );
        $this->assertEquals($expected, $this->advancedReport->getMetrics());
    }

    public function testGetProcessedMetricsShouldReturnConfiguredValueIfNotAnArrayGivenToPreventDefaultMetrics()
    {
        $this->advancedReport->set('processedMetrics', false);
        $this->assertEquals(false, $this->advancedReport->getProcessedMetrics());
    }

    public function testGetProcessedMetricsShouldReturnEmptyArrayIfNoMetricsDefined()
    {
        $this->advancedReport->set('processedMetrics', array());
        $this->assertEquals(array(), $this->advancedReport->getProcessedMetrics());
    }

    public function testGetProcessedMetricsReportShouldUseDefaultProcessedMetrics()
    {
        $this->assertEquals(Metrics::getDefaultProcessedMetrics(), $this->basicReport->getProcessedMetrics());
    }

    public function testGetProcessedMetricsShouldFindTranslationsForMetricsAndReturnOnlyTheOnesDefinedInSameOrder()
    {
        $expected = array(
            'conversion_rate' => 'General_ColumnConversionRate',
            'bounce_rate'     => 'General_ColumnBounceRate',
            'revenue_per_visit' => 'General_ColumnValuePerVisit',
            'advancedmetric' => 'MyPlugin_AdvancedMetric',
        );
        $this->assertEquals($expected, $this->advancedReport->getProcessedMetrics());
    }

    public function testHasGoalMetricsShouldBeDisabledByDefault()
    {
        $this->assertFalse($this->advancedReport->hasGoalMetrics());
    }

    public function testHasGoalMetricsShouldReturnGoalMetricsProperty()
    {
        $this->advancedReport->set('hasGoalMetrics', true);
        $this->assertTrue($this->advancedReport->hasGoalMetrics());
    }

    public function testConfigureReportMetadataShouldNotAddAReportIfReportIsDisabled()
    {
        $reports = array();
        $this->disabledReport->configureReportMetadata($reports, array());
        $this->assertEquals(array(), $reports);
    }

    public function testConfigureReportMetadataShouldAddAReportIfReportIsEnabled()
    {
        $reports = array();
        $this->basicReport->configureReportMetadata($reports, array());
        $this->assertCount(1, $reports);
    }

    public function testConfigureReportMetadataShouldBuiltStructureAndIncludeOnlyFieldsThatAreSet()
    {
        $reports = array();
        $this->basicReport->configureReportMetadata($reports, array());
        $this->assertEquals(array(
            array(
                'category' => 'Goals_Goals',
                'name' => 'My Custom Report Name',
                'module' => 'TestPlugin',
                'action' => 'getBasicReport',
                'metrics' => array(
                    'nb_visits' => 'General_ColumnNbVisits',
                    'nb_uniq_visitors' => 'General_ColumnNbUniqVisitors',
                    'nb_actions' => 'General_ColumnNbActions',
                    'nb_users' => 'General_ColumnNbUsers'
                ),
                'metricsDocumentation' => array(
                    'nb_visits' => 'General_ColumnNbVisitsDocumentation',
                    'nb_uniq_visitors' => 'General_ColumnNbUniqVisitorsDocumentation',
                    'nb_actions' => 'General_ColumnNbActionsDocumentation',
                    'nb_users' => 'General_ColumnNbUsersDocumentation',
                    'nb_actions_per_visit' => 'General_ColumnActionsPerVisitDocumentation',
                    'avg_time_on_site' => 'General_ColumnAvgTimeOnSiteDocumentation',
                    'bounce_rate' => 'General_ColumnBounceRateDocumentation',
                    'conversion_rate' => 'General_ColumnConversionRateDocumentation',
                ),
                'processedMetrics' => array(
                    'nb_actions_per_visit' => 'General_ColumnActionsPerVisit',
                    'avg_time_on_site' => 'General_ColumnAvgTimeOnSite',
                    'bounce_rate' => 'General_ColumnBounceRate',
                    'conversion_rate' => 'General_ColumnConversionRate',
                ),
                'actionToLoadSubTables' => 'invalidReport',
                'order' => 20,
                'subcategory' => null,
                'metricTypes' => [
                    'nb_visits' => 'number',
                    'nb_uniq_visitors' => 'number',
                    'nb_actions' => 'number',
                    'nb_users' => 'number',
                    'nb_actions_per_visit' => 'number',
                    'avg_time_on_site' => 'duration_s',
                    'bounce_rate' => 'percent',
                    'conversion_rate' => 'percent',
                ],
                'processedMetricFormulas' => [],
                'temporaryMetricAggregationTypes' => [],
                'temporaryMetricSemanticTypes' => [],
                'metricAggregationTypes' => [
                    'nb_visits' => 'sum',
                    'nb_actions' => 'sum',
                ],
                'dimension' => 'DevicesDetection_ColumnBrowser',
            ),
        ), $reports);
    }

    public function testConfigureReportMetadataShouldBuiltStructureAllFieldsSet()
    {
        $reports = array();
        $this->advancedReport->configureReportMetadata($reports, array());
        $this->assertEquals(array(
            array(
                'category' => 'Goals_Goals',
                'name' => 'My Custom Report Name',
                'module' => 'TestPlugin',
                'action' => 'getAdvancedReport',
                'parameters' => array(
                    'idGoal' => 1
                ),
                'dimension' => 'Actions_ColumnExitPageURL',
                'documentation' => 'ExampleReportDocumentation',
                'isSubtableReport' => true,
                'metrics' => array(
                    'nb_actions' => 'General_ColumnNbActions',
                    'nb_visits' => 'General_ColumnNbVisits'
                ),
                'metricsDocumentation' => array(
                    'nb_actions' => 'General_ColumnNbActionsDocumentation',
                    'nb_visits' => 'General_ColumnNbVisitsDocumentation',
                    'conversion_rate' => 'General_ColumnConversionRateDocumentation',
                    'bounce_rate' => 'General_ColumnBounceRateDocumentation',
                ),
                'processedMetrics' => array(
                    'conversion_rate' => 'General_ColumnConversionRate',
                    'bounce_rate' => 'General_ColumnBounceRate',
                    'revenue_per_visit' => 'General_ColumnValuePerVisit',
                    'advancedmetric' => 'MyPlugin_AdvancedMetric',
                ),
                'actionToLoadSubTables' => 'GetBasicReport',
                'constantRowsCount' => true,
                'order' => 20,
                'subcategory' => 'Actions_SubmenuPageTitles',
                'metricTypes' => [
                    'nb_actions' => 'number',
                    'nb_visits' => 'number',
                    'conversion_rate' => 'percent',
                    'bounce_rate' => 'percent',
                    'revenue_per_visit' => 'money',
                    'advancedmetric' => 'percent',
                ],
                'processedMetricFormulas' => [
                    'revenue_per_visit' => '$revenue / ($nb_visits != 0 ? $nb_visits : $nb_conversions)',
                ],
                'temporaryMetricAggregationTypes' => [
                    'intermediate_value' => 'sum',
                ],
                'temporaryMetricSemanticTypes' => [
                    'intermediate_value' => 'number',
                ],
                'metricAggregationTypes' => [
                    'nb_visits' => 'sum',
                    'nb_actions' => 'sum',
                ],
            ),
        ), $reports);
    }

    public function testFactoryShouldCreateReportWhenActionNameUsed()
    {
        $this->loadExampleReportPlugin();

        $module = 'ExampleReport';
        $action = 'getExampleReport';

        $report = ReportsProvider::factory($module, $action);

        $this->assertInstanceOf('Piwik\Plugins\ExampleReport\Reports\GetExampleReport', $report);
        $this->assertEquals($module, $report->getModule());
        $this->assertEquals($action, $report->getAction());

        // action ucfirst should work as well
        $report = ReportsProvider::factory($module, ucfirst($action));

        $this->assertInstanceOf('Piwik\Plugins\ExampleReport\Reports\GetExampleReport', $report);
        $this->assertEquals($module, $report->getModule());
        $this->assertEquals($action, $report->getAction());
    }

    public function testGetIdShouldReturnOnlyReturnModuleAndActionWhenNoParametersSet()
    {
        $report = new GetExampleReport();
        $this->assertEquals('ExampleReport.getExampleReport', $report->getId());
    }

    public function testGetIdShouldReturnIncludeParamsIfSet()
    {
        $this->assertEquals('TestPlugin.getAdvancedReport_idGoal--1', $this->advancedReport->getId());
    }

    public function testGetSubtableDimensionShouldReturnNullIfNoSubtableActionExists()
    {
        $report = new GetExampleReport();
        $this->assertNull($report->getSubtableDimension());
    }

    public function testGetSubtableDimensionShouldReturnNullIfSubtableActionIsInvalid()
    {
        $report = new GetBasicReport();
        $this->assertNull($report->getSubtableDimension());
    }

    public function testGetSubtableDimensionShouldReturnCorrectDimensionIfSubtableActionIsDefinedAndCorrect()
    {
        PluginManager::getInstance()->loadPlugins(array('Referrers'));

        $report = ReportsProvider::factory('Referrers', 'getSearchEngines');
        $subtableDimension = $report->getSubtableDimension();

        $this->assertNotNull($subtableDimension);
        $this->assertInstanceOf("Piwik\\Plugins\\Referrers\\Columns\\Keyword", $subtableDimension);
    }

    public function testFetchShouldUseCorrectApiUrl()
    {
        PluginManager::getInstance()->loadPlugins(array('API', 'ExampleReport'));

        $proxyMock = $this->getMockBuilder('stdClass')->addMethods(array('call', '__construct'))->getMock();
        $proxyMock->expects($this->once())->method('call')->with(
            '\\Piwik\\Plugins\\ExampleReport\\API',
            'getExampleReport',
            array(
                'idSite' => 1,
                'date' => '2012-01-02',
                'format' => 'original',
                'module' => 'API',
                'method' => 'ExampleReport.getExampleReport',
                'format_metrics' => 'bc',
                'serialize' => '0',
                'compare' => '0',
            )
        )->willReturn("result");
        StaticContainer::getContainer()->set(Proxy::class, $proxyMock);

        $report = new GetExampleReport();
        $result = $report->fetch(array('idSite' => 1, 'date' => '2012-01-02'));
        $this->assertEquals("result", $result);
    }

    public function testFetchSubtableShouldUseCorrectApiUrl()
    {
        PluginManager::getInstance()->loadPlugins(array('API', 'Referrers'));

        $proxyMock = $this->getMockBuilder('stdClass')->addMethods(array('call', '__construct'))->getMock();
        $proxyMock->expects($this->once())->method('call')->with(
            '\\Piwik\\Plugins\\Referrers\\API',
            'getSearchEnginesFromKeywordId',
            array(
                'idSubtable' => 23,
                'idSite' => 1,
                'date' => '2012-01-02',
                'format' => 'original',
                'module' => 'API',
                'method' => 'Referrers.getSearchEnginesFromKeywordId',
                'format_metrics' => 'bc',
                'serialize' => '0',
                'compare' => '0',
            )
        )->willReturn("result");
        StaticContainer::getContainer()->set(Proxy::class, $proxyMock);

        $report = new \Piwik\Plugins\Referrers\Reports\GetKeywords();
        $result = $report->fetchSubtable(23, array('idSite' => 1, 'date' => '2012-01-02'));
        $this->assertEquals("result", $result);
    }

    public function testGetForDimensionShouldReturnCorrectInstanceTypeIfAssociatedReportExists()
    {
        PluginManager::getInstance()->loadPlugins(array('Referrers'));

        $report = Report::getForDimension(new Keyword());
        $this->assertInstanceOf("Piwik\\Plugins\\Referrers\\Reports\\GetKeywords", $report);
    }

    public function testGetForDimensionShouldReturnNullIfNoReportExistsForDimension()
    {
        $this->loadExampleReportPlugin();
        $this->loadMorePlugins();

        $report = Report::getForDimension(new ExampleDimension());
        $this->assertNull($report);
    }

    public function testGetForDimensionShouldReturnNullIfReportPluginNotLoaded()
    {
        PluginManager::getInstance()->loadPlugins(array());

        $report = Report::getForDimension(new Keyword());
        $this->assertNull($report);
    }

    private function loadExampleReportPlugin()
    {
        PluginManager::getInstance()->loadPlugins(array('ExampleReport'));
    }

    private function loadMorePlugins()
    {
        PluginManager::getInstance()->loadPlugins(array('Actions', 'DevicesDetection', 'CoreVisualizations', 'API', 'Morpheus'));
    }

    private function unloadAllPlugins()
    {
        PluginManager::getInstance()->unloadPlugins();
    }
}
