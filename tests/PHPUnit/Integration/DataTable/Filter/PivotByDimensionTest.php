<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Core\DataTable\Filter;

use Piwik\API\Proxy;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Tracker\Cache;
use Piwik\DataTable;
use Piwik\DataTable\Filter\PivotByDimension;
use Piwik\DataTable\Row;
use Piwik\Plugin\Manager as PluginManager;
use Exception;
use Piwik\Translate;

/**
 * @group DataTableTest
 */
class PivotByDimensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The number of segment tables that have been created. Used when injecting API results to make sure each
     * segment table is different.
     *
     * @var int
     */
    private $segmentTableCount;

    /**
     * Segment query params used to fetch intersected tables in PivotByDimension filter. Captured by mock
     * API\Proxy class.
     *
     * @var array
     */
    public $segmentUsedToGetIntersected = array();

    public function setUp()
    {
        parent::setUp();

        Translate::reset();
        Cache::clearCacheGeneral();
        \Piwik\Cache::flushAll();

        $self = $this;

        $proxyMock = $this->getMockBuilder('stdClass')->setMethods(array('call'))->getMock();
        $proxyMock->expects($this->any())->method('call')->willReturnCallback(function ($className, $methodName, $parameters) use ($self) {
            if ($className == "\\Piwik\\Plugins\\UserCountry\\API"
                && $methodName == 'getCity'
            ) {
                $self->segmentUsedToGetIntersected[] = $parameters['segment'];

                return $self->getSegmentTable();
            } else {
                throw new Exception("Unknown API request: $className::$methodName.");
            }
        });
        Proxy::setSingletonInstance($proxyMock);

        $this->segmentTableCount = 0;
    }

    public function tearDown()
    {
        Proxy::unsetInstance();

        parent::tearDown();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unsupported pivot: report 'ExampleReport.ExampleReportName' has no subtable dimension.
     */
    public function test_construction_ShouldFail_WhenReportHasNoSubtableAndSegmentFetchingIsDisabled()
    {
        $this->loadPlugins('ExampleReport', 'UserCountry');

        new PivotByDimension(new DataTable(), "ExampleReport.GetExampleReport", "UserCountry.City", 'nb_visits', $columnLimit = -1, $enableFetchBySegment = false);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unsupported pivot: the subtable dimension for 'Referrers.Referrers_Keywords' does not match the requested pivotBy dimension.
     */
    public function test_construction_ShouldFail_WhenDimensionIsNotSubtableAndSegmentFetchingIsDisabled()
    {
        $this->loadPlugins('Referrers', 'UserCountry');

        new PivotByDimension(new DataTable(), "Referrers.getKeywords", "UserCountry.City", "nb_visits", $columnLimit = -1, $enableFetchBySegment = false);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unsupported pivot: No segment for dimension of report 'Resolution.Resolution_Configurations'
     */
    public function test_construction_ShouldFail_WhenDimensionIsNotSubtableAndSegmentFetchingIsEnabledButThereIsNoSegment()
    {
        $this->loadPlugins('Referrers', 'Resolution');

        new PivotByDimension(new DataTable(), "Resolution.GetConfiguration", "Referrers.Keyword", "nb_visits");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid dimension 'ExampleTracker.InvalidDimension'
     */
    public function test_construction_ShouldFail_WhenDimensionDoesNotExist()
    {
        $this->loadPlugins('ExampleReport', 'ExampleTracker');

        new PivotByDimension(new DataTable(), "ExampleReport.GetExampleReport", "ExampleTracker.InvalidDimension", 'nb_visits');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unsupported pivot: No report for pivot dimension 'ExampleTracker.ExampleDimension'
     */
    public function test_construction_ShouldFail_WhenThereIsNoReportForADimension()
    {
        $this->loadPlugins('ExampleReport', 'ExampleTracker');

        new PivotByDimension(new DataTable(), "ExampleReport.GetExampleReport", "ExampleTracker.ExampleDimension", "nb_visits");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to find report 'ExampleReport.InvalidReport'
     */
    public function test_construction_ShouldFail_WhenSpecifiedReportIsNotValid()
    {
        $this->loadPlugins('ExampleReport', 'Referrers');

        new PivotByDimension(new DataTable(), "ExampleReport.InvalidReport", "Referrers.Keyword", "nb_visits");
    }

    public function test_filter_ReturnsEmptyResult_WhenTableToFilterIsEmpty()
    {
        $this->loadPlugins('Referrers', 'UserCountry', 'CustomVariables');

        $table = new DataTable();

        $pivotFilter = new PivotByDimension($table, "Referrers.getKeywords", "Referrers.SearchEngine", 'nb_visits');
        $pivotFilter->filter($table);

        $this->assertEquals(array(), $table->getRows());
    }

    public function test_filter_CorrectlyCreatesPivotTable_WhenUsingSubtableReport()
    {
        $this->loadPlugins('Referrers', 'UserCountry', 'CustomVariables');

        $table = $this->getTableToFilter(true);

        $pivotFilter = new PivotByDimension($table, "Referrers.getKeywords", "Referrers.SearchEngine", 'nb_actions', $columnLimit = -1, $fetchBySegment = false);
        $pivotFilter->filter($table);

        $expectedRows = array(
            array('label' => 'row 1', 'col 1' => 2, 'col 2' => false, 'col 3' => false, 'col 4' => false),
            array('label' => 'row 2', 'col 1' => 4, 'col 2' => 6, 'col 3' => false, 'col 4' => false),
            array('label' => 'row 3', 'col 1' => false, 'col 2' => 8, 'col 3' => 31, 'col 4' => 33)
        );
        $this->assertTableRowsEquals($expectedRows, $table);
    }

    public function test_filter_CorrectlyCreatesPivotTable_WhenUsingSegment()
    {
        $this->loadPlugins('Referrers', 'UserCountry', 'CustomVariables');

        $table = $this->getTableToFilter(true);

        $pivotFilter = new PivotByDimension($table, "Referrers.getKeywords", "UserCountry.City", 'nb_visits');
        $pivotFilter->filter($table);

        $expectedSegmentParams = array('referrerKeyword==row+1', 'referrerKeyword==row+2', 'referrerKeyword==row+3');
        $this->assertEquals($expectedSegmentParams, $this->segmentUsedToGetIntersected);

        $expectedRows = array(
            array('label' => 'row 1', 'col 0' => 2, 'col 1' => false, 'col 2' => false),
            array('label' => 'row 2', 'col 0' => 2, 'col 1' => 4, 'col 2' => false),
            array('label' => 'row 3', 'col 0' => 2, 'col 1' => 4, 'col 2' => 6)
        );
        $this->assertTableRowsEquals($expectedRows, $table);
    }

    /**
     * @backupGlobals enabled
     */
    public function test_filter_UsesCorrectSegment_WhenPivotingSegmentedReport()
    {
        $this->loadPlugins('Referrers', 'UserCountry', 'CustomVariables');

        $table = $this->getTableToFilter(true);

        $_GET['segment'] = 'asegment==value';

        $pivotFilter = new PivotByDimension($table, "Referrers.getKeywords", "UserCountry.City", 'nb_visits');
        $pivotFilter->filter($table);

        $expectedSegmentParams = array(
            'asegment==value;referrerKeyword==row+1',
            'asegment==value;referrerKeyword==row+2',
            'asegment==value;referrerKeyword==row+3'
        );
        $this->assertEquals($expectedSegmentParams, $this->segmentUsedToGetIntersected);
    }

    public function test_filter_CorrectlyCreatesPivotTable_WhenPivotMetricDoesNotExistInTable()
    {
        $this->loadPlugins('Referrers', 'UserCountry', 'CustomVariables');

        $table = $this->getTableToFilter(true);

        $pivotFilter = new PivotByDimension($table, "Referrers.getKeywords", "Referrers.SearchEngine", 'invalid_metric');
        $pivotFilter->filter($table);

        $expectedRows = array(
            array('label' => 'row 1', 'col 1' => false, 'col 2' => false, 'col 3' => false, 'col 4' => false),
            array('label' => 'row 2', 'col 1' => false, 'col 2' => false, 'col 3' => false, 'col 4' => false),
            array('label' => 'row 3', 'col 1' => false, 'col 2' => false, 'col 3' => false, 'col 4' => false)
        );
        $this->assertTableRowsEquals($expectedRows, $table);
    }

    public function test_filter_CorrectlyCreatesPivotTable_WhenSubtablesHaveNoRows()
    {
        Cache::setCacheGeneral(array(CustomVariables::MAX_NUM_CUSTOMVARS_CACHEKEY => 5));

        $this->loadPlugins('Referrers', 'UserCountry', 'CustomVariables');

        $table = $this->getTableToFilter(false);

        $pivotFilter = new PivotByDimension($table, "CustomVariables.getCustomVariables", "CustomVariables.CustomVariableValue",
            'nb_visits', $fetchBySegment = false);
        $pivotFilter->filter($table);

        $expectedRows = array(
            array('label' => 'row 1'),
            array('label' => 'row 2'),
            array('label' => 'row 3')
        );

        Cache::clearCacheGeneral();
        $this->assertTableRowsEquals($expectedRows, $table);
    }

    public function test_filter_CorrectlyDefaultsPivotByColumn_WhenNoneProvided()
    {
        $this->loadPlugins('Referrers', 'UserCountry', 'CustomVariables');

        $table = $this->getTableToFilter(true);

        $pivotFilter = new PivotByDimension($table, "Referrers.getKeywords", "Referrers.SearchEngine", $column = false, $columnLimit = -1, $fetchBySegment = false);
        $pivotFilter->filter($table);

        $expectedRows = array(
            array('label' => 'row 1', 'col 1' => 1, 'col 2' => false, 'col 3' => false, 'col 4' => false),
            array('label' => 'row 2', 'col 1' => 3, 'col 2' => 5, 'col 3' => false, 'col 4' => false),
            array('label' => 'row 3', 'col 1' => false, 'col 2' => 7, 'col 3' => 9, 'col 4' => 32)
        );
        $this->assertTableRowsEquals($expectedRows, $table);
    }

    public function test_filter_CorrectlyLimitsTheColumnNumber_WhenColumnLimitProvided()
    {
        $this->loadPlugins('Referrers', 'UserCountry', 'CustomVariables');

        $table = $this->getTableToFilter(true);

        $pivotFilter = new PivotByDimension($table, "Referrers.getKeywords", "Referrers.SearchEngine", $column = 'nb_visits', $columnLimit = 3, $fetchBySegment = false);
        $pivotFilter->filter($table);

        $expectedRows = array(
            array('label' => 'row 1', 'col 2' => false, 'col 4' => false, 'Others' => 1),
            array('label' => 'row 2', 'col 2' => 5, 'col 4' => false, 'Others' => 3),
            array('label' => 'row 3', 'col 2' => 7, 'col 4' => 32, 'Others' => 9)
        );
        $this->assertTableRowsEquals($expectedRows, $table);
    }

    private function getTableToFilter($addSubtables = false)
    {
        $row1 = new Row(array(Row::COLUMNS => array(
            'label' => 'row 1',
            'nb_visits' => 10,
            'nb_actions' => 15
        )));
        if ($addSubtables) {
            $row1->setSubtable($this->getRow1Subtable());
        }

        $row2 = new Row(array(Row::COLUMNS => array(
            'label' => 'row 2',
            'nb_visits' => 13,
            'nb_actions' => 18
        )));
        if ($addSubtables) {
            $row2->setSubtable($this->getRow2Subtable());
        }

        $row3 = new Row(array(Row::COLUMNS => array(
            'label' => 'row 3',
            'nb_visits' => 20,
            'nb_actions' => 25
        )));
        if ($addSubtables) {
            $row3->setSubtable($this->getRow3Subtable());
        }

        $table = new DataTable();
        $table->addRowsFromArray(array($row1, $row2, $row3));
        return $table;
    }

    private function getRow1Subtable()
    {
        $table = new DataTable();
        $table->addRowsFromArray(array(
            new Row(array(Row::COLUMNS => array(
                'label' => 'col 1',
                'nb_visits' => 1,
                'nb_actions' => 2
            )))
        ));
        return $table;
    }

    private function getRow2Subtable()
    {
        $table = new DataTable();
        $table->addRowsFromArray(array(
            new Row(array(Row::COLUMNS => array(
                'label' => 'col 1',
                'nb_visits' => 3,
                'nb_actions' => 4
            ))),
            new Row(array(Row::COLUMNS => array(
                'label' => 'col 2',
                'nb_visits' => 5,
                'nb_actions' => 6
            )))
        ));
        return $table;
    }

    private function getRow3Subtable()
    {
        $table = new DataTable();
        $table->addRowsFromArray(array(
            new Row(array(Row::COLUMNS => array(
                'label' => 'col 2',
                'nb_visits' => 7,
                'nb_actions' => 8
            ))),
            new Row(array(Row::COLUMNS => array(
                'label' => 'col 3',
                'nb_visits' => 9,
                'nb_actions' => 31
            ))),
            new Row(array(Row::COLUMNS => array(
                'label' => 'col 4',
                'nb_visits' => 32,
                'nb_actions' => 33
            )))
        ));
        return $table;
    }

    public function getSegmentTable()
    {
        ++$this->segmentTableCount;

        $table = new DataTable();
        for ($i = 0; $i != $this->segmentTableCount; ++$i) {
            $row = new Row(array(Row::COLUMNS => array(
                'label' => 'col ' . $i,
                'nb_visits' => ($i + 1) * 2,
                'nb_actions' => ($i + 1) * 3
            )));
            $table->addRow($row);
        }
        return $table;
    }

    private function assertTableRowsEquals($expectedRows, $table)
    {
        $renderer = new DataTable\Renderer\Php();
        $renderer->setSerialize(false);
        $actualRows = $renderer->render($table);

        $this->assertEquals($expectedRows, $actualRows);
    }

    private function loadPlugins()
    {
        PluginManager::getInstance()->loadPlugins(func_get_args());
    }
}