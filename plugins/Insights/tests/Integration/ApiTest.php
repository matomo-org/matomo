<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Integration;

use Piwik\API\Request as ApiRequest;
use Piwik\Cache as PiwikCache;
use Piwik\DataTable;
use Piwik\Plugins\Insights\API;
use Piwik\Plugins\Insights\tests\Fixtures\SomeVisitsDifferentPathsOnTwoDays;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Insights
 * @group ApiTest
 * @group Plugins
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var SomeVisitsDifferentPathsOnTwoDays
     */
    public static $fixture = null;

    /**
     * @var API
     */
    private $api;
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        PiwikCache::flushAll();

        Fixture::loadAllTranslations();
        $this->api = API::getInstance();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Fixture::resetTranslations();
    }

    /**
     * '/Mover1' => 2,    +8  // 400%
     * '/Old1' => 9,      -9  // -100%
     * '/Mover2' => 24,   -11 // -50%
     * '/Mover3' => 21,   -1  // -5%
     * '/Old2' => 3       -3  // -100%
     * '/New1',           +5  // 100%
     * '/New2'            +2  // 100%
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
            'orderBy'    => 'absolute',
            'metric'     => 'nb_visits',
            'totalValue' => 50,
            'minChangeMovers' => 1,
            'minIncreaseNew'  => 1,
            'minDecreaseDisappeared' => 1,
            'minGrowthPercentPositive' => 20,
            'minGrowthPercentNegative' => -20,
            'minMoversPercent' => 2,
            'minNewPercent' => 2,
            'minDisappearedPercent' => 2,
            'lastTotalValue' => 59,
            'evolutionTotal' => -15.3,
            'evolutionDifference' => -9,
            'totals' => [
                'nb_visits' => 42,
                'growth_percent' => 0,
                'growth_percent_numeric' => 354.20000000000005,
                'grown' => 0,
                'value_old' => 38,
                'value_new' => 30,
                'difference' => -8,
                'importance' => 38,
                'isDisappeared' => 0,
                'isNew' => 0,
                'isMover' => 0,
                'isMoverAndShaker' => 0,
            ],
            'totalsUnformatted' => [
                'nb_visits' => 42,
                'growth_percent' => 0,
                'growth_percent_numeric' => 354.20000000000005,
                'grown' => 0,
                'value_old' => 38,
                'value_new' => 30,
                'difference' => -8,
                'importance' => 38,
                'isDisappeared' => 0,
                'isNew' => 0,
                'isMover' => 0,
                'isMoverAndShaker' => 0,
            ]
        );

        self::assertIsArray($metadata['report']);
        $this->assertEquals('Actions', $metadata['report']['module']);
        $this->assertEquals('getPageUrls', $metadata['report']['action']);
        unset($metadata['report']);

        $this->assertEqualsWithDelta($expectedMetadata, $metadata, 0.0001);
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
        $insights = $this->requestInsights(array('minImpactPercent' => 0, 'minGrowthPercent' => 1));

        $expectedLabels = array(
            '/Mover1',
            '/New1',
            '/New2',
            '/Mover2',
            '/Old1',
            '/Old2',
            '/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldReturnReturnNothingIfminImpactPercentIsTooHigh()
    {
        $insights = $this->requestInsights(array('minImpactPercent' => 10000, 'minGrowthPercent' => 0));

        $this->assertRows(array(), $insights);
    }

    public function test_getInsights_ShouldReturnReturnNothingIfMinGrowthIsHigh()
    {
        $insights = $this->requestInsights(array('minImpactPercent' => 0, 'minGrowthPercent' => 10000));

        $this->assertRows(array(), $insights);
    }

    public function test_getInsights_ShouldOrderAbsoluteByDefault()
    {
        $insights = $this->requestInsights(array('minImpactPercent' => 0, 'minGrowthPercent' => 0));

        $expectedLabels = array(
            '/Mover1',
            '/New1',
            '/New2',
            '/Mover2',
            '/Old1',
            '/Old2',
            '/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToOrderRelative()
    {
        $insights = $this->requestInsights(array('minImpactPercent' => 0, 'minGrowthPercent' => 0, 'orderBy' => 'relative'));

        $expectedLabels = array(
            '/Mover1',
            '/New1',
            '/New2',
            '/Old1',
            '/Old2',
            '/Mover2',
            '/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToOrderByImportance()
    {
        $insights = $this->requestInsights(array('minImpactPercent' => 0, 'minGrowthPercent' => 0, 'orderBy' => 'importance'));

        $expectedLabels = array(
            '/Mover2',
            '/Old1',
            '/Mover1',
            '/New1',
            '/Old2',
            '/New2',
            '/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldApplyTheLimit()
    {
        $insights = $this->requestInsights(array('limitIncreaser' => 1, 'limitDecreaser' => 1));

        $expectedLabels = array(
            '/Mover1',
            '/Mover2'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToShowOnlyMovers()
    {
        $insights = $this->requestInsights(array('minImpactPercent' => 0, 'minGrowthPercent' => 0, 'filterBy' => 'movers'));

        $expectedLabels = array(
            '/Mover1',
            '/Mover2',
            '/Mover3'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToShowOnlyNew()
    {
        $insights = $this->requestInsights(array('minImpactPercent' => 0, 'minGrowthPercent' => 0, 'filterBy' => 'new'));

        $expectedLabels = array(
            '/New1',
            '/New2'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_getInsights_ShouldBeAbleToShowOnlyDisappeared()
    {
        $insights = $this->requestInsights(array('minImpactPercent' => 0, 'minGrowthPercent' => 0, 'filterBy' => 'disappeared'));

        $expectedLabels = array(
            '/Old1',
            '/Old2'
        );
        $this->assertRows($expectedLabels, $insights);
    }

    public function test_canGenerateInsights()
    {
        $this->assertTrue($this->api->canGenerateInsights('2012-12-12', 'day'));
        $this->assertTrue($this->api->canGenerateInsights('2012-12-12', 'week'));
        $this->assertTrue($this->api->canGenerateInsights('2012-12-12', 'month'));

        $this->assertFalse($this->api->canGenerateInsights('last10', 'day'));
        $this->assertTrue($this->api->canGenerateInsights('2012-11-11,2012-12-12', 'range'));
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

ApiTest::$fixture = new SomeVisitsDifferentPathsOnTwoDays();
