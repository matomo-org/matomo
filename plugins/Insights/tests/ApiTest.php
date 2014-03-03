<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Plugins\Insights\API;
use Piwik\API\Request as ApiRequest;

/**
 * @group Insights
 * @group ApiTest
 * @group Database
 * @group Plugins
 */
class ApiTest extends \IntegrationTestCase
{
    /**
     * @var \Test_Piwik_Fixture_SomeVisitsDifferentPathsOnTwoDays
     */
    public static $fixture = null;

    /**
     * @var API
     */
    private $api;
    private $idSite;

    public function setUp()
    {
        parent::setUp();

        $this->api = API::getInstance();
    }

    /**
    '/category/Mover1' => 2,    +8  // 400%
    '/category/Old1' => 9,      -9  // -100%
    '/Mover2' => 24,            -11 // -50%
    '/category/Mover3' => 21,   -1  // -5%
    '/Old2' => 3                -3  // -100%
    New1,                       +5  // 100%
    New2                        +2  // 100%
     */
    public function test_getInsights_ShouldReturnCorrectMetadata()
    {
        $insights = $this->requestInsights(array());
        $metadata = $insights->getAllTableMetadata();

        $expectedMetadata = array(
            'reportName' => 'Page URLs',
            'metricName' => 'Unique Pageviews',
            'date'       => self::$fixture->date1,
            'lastDate'   => self::$fixture->date2,
            'period'     => 'day',
            'totalValue' => 50,
            'minVisits'  => 1
        );

        $this->assertInternalType('array', $metadata['report']);
        $this->assertEquals('Actions', $metadata['report']['module']);
        $this->assertEquals('getPageUrls', $metadata['report']['action']);
        unset($metadata['report']);
        unset($metadata['totals']);

        $this->assertEquals($expectedMetadata, $metadata);
    }

    public function test_getInsights_ShouldGoBackInPastDependingOnComparedToParameter()
    {
        $insights = $this->requestInsights(array('comparedToXPeriods' => 3));

        $metadata = $insights->getAllTableMetadata();

        $this->assertEquals('2010-12-11', $metadata['lastDate']);
    }

    public function test_getInsights_ShouldGoBackInPastDependingOnPeriod()
    {
        $insights = $this->requestInsights(array('period' => 'month'));

        $metadata = $insights->getAllTableMetadata();

        $this->assertEquals('2010-11-14', $metadata['lastDate']);
    }

    public function test_getInsights_ShouldReturnAllRowsIfMinValuesArelow()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 0, 'minGrowthPercent' => 1));

        $expectedLabels = array(
            'category/Mover1',
            'category/New1',
            'New2',
            'Mover2',
            'category/Old1',
            'Old2',
            'category/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldReturnReturnNothingIfMinVisitsPercentIsTooHigh()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 10000, 'minGrowthPercent' => 0));

        $this->assertRows(array(), $insights);
    }

    public function test_getInsights_ShouldReturnReturnNothingIfMinGrowthIsHigh()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 0, 'minGrowthPercent' => 10000));

        $this->assertRows(array(), $insights);
    }

    public function test_getInsights_ShouldOrderAbsoluteByDefault()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 0, 'minGrowthPercent' => 0));

        $expectedLabels = array(
            'category/Mover1',
            'category/New1',
            'New2',
            'Mover2',
            'category/Old1',
            'Old2',
            'category/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToOrderRelative()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 0, 'minGrowthPercent' => 0, 'orderBy' => 'relative'));

        $expectedLabels = array(
            'category/Mover1',
            'category/New1',
            'New2',
            'category/Old1',
            'Old2',
            'Mover2',
            'category/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToOrderByImportance()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 0, 'minGrowthPercent' => 0, 'orderBy' => 'importance'));

        $expectedLabels = array(
            'Mover2',
            'category/Old1',
            'category/Mover1',
            'category/New1',
            'Old2',
            'New2',
            'category/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldApplyTheLimit()
    {
        $insights = $this->requestInsights(array('limitIncreaser' => 1, 'limitDecreaser' => 1));

        $expectedLabels = array(
            'category/Mover1',
            'Mover2'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToShowOnlyMovers()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 0, 'minGrowthPercent' => 0, 'filterBy' => 'movers'));

        $expectedLabels = array(
            'category/Mover1',
            'Mover2',
            'category/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToShowOnlyNew()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 0, 'minGrowthPercent' => 0, 'filterBy' => 'new'));

        $expectedLabels = array(
            'category/New1',
            'New2'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToShowOnlyDisappeared()
    {
        $insights = $this->requestInsights(array('minVisitsPercent' => 0, 'minGrowthPercent' => 0, 'filterBy' => 'disappeared'));

        $expectedLabels = array(
            'category/Old1',
            'Old2'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    private function requestInsights($requestParams)
    {
        $params = array(
            'method' => 'Insights.getInsights',
            'idSite' => self::$fixture->idSite,
            'date'   => self::$fixture->date1,
            'period' => 'day',
            'format' => 'original',
            'reportUniqueId' => 'Actions_getPageUrls',
        );

        if (!empty($requestParams)) {
            foreach ($requestParams as $key => $value) {
                $params[$key] = $value;
            }
        }

        $request = new ApiRequest($params);
        return $request->process();
    }

    private function assertRows($expectedLabels, DataTable $dataTable)
    {
        $this->assertEquals($expectedLabels, $dataTable->getColumn('label'));
        $this->assertEquals(count($expectedLabels), $dataTable->getRowsCount());
    }
}

ApiTest::$fixture = new \Test_Piwik_Fixture_SomeVisitsDifferentPathsOnTwoDays();