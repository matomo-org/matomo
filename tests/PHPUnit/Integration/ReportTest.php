<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\API\Proxy;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Report;
use Piwik\Plugins\ExampleReport\Reports\GetExampleReport;
use Piwik\Plugins\Actions\Columns\ExitPageUrl;
use Piwik\Piwik;
use Piwik\Metrics;
use Piwik\Plugins\ExampleTracker\Columns\ExampleDimension;
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

        $this->name = 'My Custom Report Name';
        $this->order  = 20;
        $this->module = 'TestPlugin';
        $this->action = 'getBasicReport';
        $this->categoryId = 'Goals_Goals';
        $this->actionToLoadSubTables = 'invalidReport';
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
        $this->processedMetrics = array('conversion_rate', 'bounce_rate');
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
        $this->unloadAllPlugins();
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

    public function test_shouldDetectTheModuleOfTheReportAutomatically()
    {
        $this->assertEquals('ExampleReport', $this->exampleReport->getModule());
    }

    public function test_shouldDetectTheActionOfTheReportAutomatiacally()
    {
        $this->assertEquals('getExampleReport', $this->exampleReport->getAction());
    }

    public function test_getName_shouldReturnTheNameOfTheReport()
    {
        $this->assertEquals('My Custom Report Name', $this->basicReport->getName());
    }

    public function test_isEnabled_shouldBeEnabledByDefault()
    {
        $this->assertTrue($this->basicReport->isEnabled());
    }

    public function test_checkIsEnabled_shouldThrowAnExceptionIfReportIsNotEnabled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionReportNotEnabled');

        $this->disabledReport->checkIsEnabled();
    }

    public function test_getCategory_shouldReturnTranslatedCategory()
    {
        Fixture::loadAllTranslations();
        $this->assertEquals('Goals_Goals', $this->advancedReport->getCategoryId());
        Fixture::resetTranslations();
    }

    public function test_getMetrics_shouldUseDefaultMetrics()
    {
        $this->assertEquals(Metrics::getDefaultMetrics(), $this->basicReport->getMetrics());
    }

    public function test_getMetrics_shouldReturnEmptyArray_IfNoMetricsDefined()
    {
        $this->advancedReport->set('metrics', array());
        $this->assertEquals(array(), $this->advancedReport->getMetrics());
    }

    public function test_getMetrics_shouldFindTranslationsForMetricsAndReturnOnlyTheOnesDefinedInSameOrder()
    {
        $expected = array(
            'nb_visits'  => 'General_ColumnNbVisits',
            'nb_actions' => 'General_ColumnNbActions'
        );
        $this->assertEquals($expected, $this->advancedReport->getMetrics());
    }

    public function test_getProcessedMetrics_shouldReturnConfiguredValue_IfNotAnArrayGivenToPreventDefaultMetrics()
    {
        $this->advancedReport->set('processedMetrics', false);
        $this->assertEquals(false, $this->advancedReport->getProcessedMetrics());
    }

    public function test_getProcessedMetrics_shouldReturnEmptyArray_IfNoMetricsDefined()
    {
        $this->advancedReport->set('processedMetrics', array());
        $this->assertEquals(array(), $this->advancedReport->getProcessedMetrics());
    }

    public function test_getProcessedMetrics_reportShouldUseDefaultProcessedMetrics()
    {
        $this->assertEquals(Metrics::getDefaultProcessedMetrics(), $this->basicReport->getProcessedMetrics());
    }

    public function test_getProcessedMetrics_shouldFindTranslationsForMetricsAndReturnOnlyTheOnesDefinedInSameOrder()
    {
        $expected = array(
            'conversion_rate' => 'General_ColumnConversionRate',
            'bounce_rate'     => 'General_ColumnBounceRate'
        );
        $this->assertEquals($expected, $this->advancedReport->getProcessedMetrics());
    }

    public function test_hasGoalMetrics_shouldBeDisabledByDefault()
    {
        $this->assertFalse($this->advancedReport->hasGoalMetrics());
    }

    public function test_hasGoalMetrics_shouldReturnGoalMetricsProperty()
    {
        $this->advancedReport->set('hasGoalMetrics', true);
        $this->assertTrue($this->advancedReport->hasGoalMetrics());
    }

    public function test_configureReportMetadata_shouldNotAddAReportIfReportIsDisabled()
    {
        $reports = array();
        $this->disabledReport->configureReportMetadata($reports, array());
        $this->assertEquals(array(), $reports);
    }

    public function test_configureReportMetadata_shouldAddAReportIfReportIsEnabled()
    {
        $reports = array();
        $this->basicReport->configureReportMetadata($reports, array());
        $this->assertCount(1, $reports);
    }

    public function test_configureReportMetadata_shouldBuiltStructureAndIncludeOnlyFieldsThatAreSet()
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
            )
        ), $reports);
    }

    public function test_configureReportMetadata_shouldBuiltStructureAllFieldsSet()
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
                ),
                'actionToLoadSubTables' => 'GetBasicReport',
                'constantRowsCount' => true,
                'order' => '20',
                'subcategory' => 'Actions_SubmenuPageTitles',
                'metricTypes' => [
                    'nb_actions' => 'number',
                    'nb_visits' => 'number',
                    'conversion_rate' => 'percent',
                    'bounce_rate' => 'percent',
                ],
            )
        ), $reports);
    }

    public function test_factory_shouldCreateReport_WhenActionNameUsed()
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

    public function test_getId_ShouldReturnOnlyReturnModuleAndActionWhenNoParametersSet()
    {
        $report = new GetExampleReport();
        $this->assertEquals('ExampleReport.getExampleReport', $report->getId());
    }

    public function test_getId_ShouldReturnIncludeParamsIfSet()
    {
        $this->assertEquals('TestPlugin.getAdvancedReport_idGoal--1', $this->advancedReport->getId());
    }

    public function test_getSubtableDimension_ShouldReturnNullIfNoSubtableActionExists()
    {
        $report = new GetExampleReport();
        $this->assertNull($report->getSubtableDimension());
    }

    public function test_getSubtableDimension_ShouldReturnNullIfSubtableActionIsInvalid()
    {
        $report = new GetBasicReport();
        $this->assertNull($report->getSubtableDimension());
    }

    public function test_getSubtableDimension_ShouldReturnCorrectDimensionIfSubtableActionIsDefinedAndCorrect()
    {
        PluginManager::getInstance()->loadPlugins(array('Referrers'));

        $report = ReportsProvider::factory('Referrers', 'getSearchEngines');
        $subtableDimension = $report->getSubtableDimension();

        $this->assertNotNull($subtableDimension);
        $this->assertInstanceOf("Piwik\\Plugins\\Referrers\\Columns\\Keyword", $subtableDimension);
    }

    public function test_fetch_ShouldUseCorrectApiUrl()
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

    public function test_fetchSubtable_ShouldUseCorrectApiUrl()
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

    public function test_getForDimension_ShouldReturnCorrectInstanceTypeIfAssociatedReportExists()
    {
        PluginManager::getInstance()->loadPlugins(array('Referrers'));

        $report = Report::getForDimension(new Keyword());
        $this->assertInstanceOf("Piwik\\Plugins\\Referrers\\Reports\\GetKeywords", $report);
    }

    public function test_getForDimension_ShouldReturnNullIfNoReportExistsForDimension()
    {
        $this->loadExampleReportPlugin();
        $this->loadMorePlugins();

        $report = Report::getForDimension(new ExampleDimension());
        $this->assertNull($report);
    }

    public function test_getForDimension_ShouldReturnNullIfReportPluginNotLoaded()
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
