<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Ecommerce\tests\System;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoSitesEcommerceOrderWithItems;

/**
 * Tests API methods after ecommerce orders are tracked.
 *
 * @group EcommerceOrderWithItemsTest
 * @group Plugins
 */
class EcommerceOrderWithItemsTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function testImagesIncludedInTests()
    {
        $this->alertWhenImagesExcludedFromTests();
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $idSite2 = self::$fixture->idSite2;
        $dateTime = self::$fixture->dateTime;

        $dayApi = array('VisitsSummary.get', 'VisitTime', 'CustomVariables.getCustomVariables',
                        'Live.getLastVisitsDetails', 'UserCountry', 'API.getProcessedReport', 'Goals.get',
                        'Goals.getConversions', 'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory');

        $goalWeekApi = array('Goals.get', 'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory');

        $goalItemApi = array('Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory');

        $processedReportApi = array('API.getProcessedReport');

        $apiWithSegments = array(
            'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory'
        );

        // Normal standard goal
        $apiWithSegments_visitConvertedGoal = array_merge($apiWithSegments , array('Goals.get', 'VisitsSummary.get'));
        return array_merge(array(

                // Segment: This will match the first visit of the fixture only
                array(
                    $apiWithSegments,
                    array(
                        'idSite' => $idSite,
                        'date' => $dateTime,
                        'periods' => array('day', 'week'),
                        'otherRequestParameters' => array('_leavePiwikCoreVariables' => 1),
                        'segment' => 'pageUrl=@example.org%2Findex.htm',
                        'testSuffix' => '_SegmentPageUrlContains'
                    )
                ),

                // Goals.get for Ecommerce, with Page Title segment
                array(
                    'Goals.get',
                    array(
                        'idSite' => $idSite,
                        'date' => $dateTime,
                        'periods' => array('day', 'week'),
                        'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                        'segment' => 'pageTitle==Looking%20at%20product%20page',
                        'testSuffix' => '_EcommerceOrderGoal_SegmentPageUrlContains'
                    )
                ),

                // Segment: This will match the first visit of the fixture only
                array(
                    $apiWithSegments,
                    array(
                        'idSite' => $idSite,
                        'date' => $dateTime,
                        'periods' => array('day', 'week'),
                        'otherRequestParameters' => array('_leavePiwikCoreVariables' => 1),
                        'segment' => 'countryCode==fr',
                        'testSuffix' => '_SegmentCountryIsFr'
                    )
                ),

                // day tests
                array($dayApi, array('idSite' => $idSite, 'date' => $dateTime, 'periods' => array('day'),
                                     'otherRequestParameters' => array('_leavePiwikCoreVariables' => 1))),

                // goals API week tests
                array($goalWeekApi, array('idSite' => $idSite, 'date' => $dateTime, 'periods' => array('week'))),

                // abandoned carts tests
                array($goalItemApi, array('idSite'     => $idSite, 'date' => $dateTime,
                                          'periods'    => array('day', 'week'),
                                          'testSuffix' => '_AbandonedCarts',
                                          'otherRequestParameters' => array(
                                              'abandonedCarts' => 1
                                          ))),

                // multiple periods tests
                array($goalItemApi, array('idSite'       => $idSite, 'date' => $dateTime, 'periods' => array('day'),
                                          'setDateLastN' => true, 'testSuffix' => 'multipleDates')),

                // multiple periods & multiple websites tests
                array($goalItemApi, array('idSite'     => sprintf("%u,%u", $idSite, $idSite2), 'date' => $dateTime,
                                          'periods'    => array('day'), 'setDateLastN' => true,
                                          'testSuffix' => 'multipleDates_andMultipleWebsites')),

                // test metadata products
                array($processedReportApi, array('idSite'    => $idSite, 'date' => $dateTime,
                                                 'periods'   => array('day'), 'apiModule' => 'Goals',
                                                 'apiAction' => 'getItemsSku', 'testSuffix' => '_Metadata_ItemsSku')),
                array($processedReportApi, array('idSite'    => $idSite, 'date' => $dateTime,
                                                 'periods'   => array('day'), 'apiModule' => 'Goals',
                                                 'apiAction' => 'getItemsCategory', 'testSuffix' => '_Metadata_ItemsCategory')),

                // test metadata Goals.get for Ecommerce orders & Carts
                array($processedReportApi, array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('day'), 'apiModule' => 'Goals', 'apiAction' => 'get',
                                                 'idGoal'     => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                                                 'testSuffix' => '_Metadata_Goals.Get_Order')),
                array($processedReportApi, array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('day'), 'apiModule' => 'Goals', 'apiAction' => 'get',
                                                 'idGoal'     => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART,
                                                 'testSuffix' => '_Metadata_Goals.Get_AbandonedCart')),

                // normal standard goal test
                array($processedReportApi, array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('day'), 'apiModule' => 'Goals', 'apiAction' => 'get',
                                                 'idGoal'     => self::$fixture->idGoalStandard,
                                                 'testSuffix' => '_Metadata_Goals.Get_NormalGoal')),

                // non-existant goal test
                array($processedReportApi, array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('day'), 'apiModule' => 'Goals', 'apiAction' => 'get',
                                                 'idGoal'     => 'FAKE IDGOAL',
                                                 'testSuffix' => '_Metadata_Goals.Get_NotExistingGoal')),

                // While we're at it, test for a standard Metadata report with zero entries
                array($processedReportApi, array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('day'), 'apiModule' => 'VisitTime',
                                                 'apiAction'  => 'getVisitInformationPerServerTime',
                                                 'testSuffix' => '_Metadata_VisitTime.getVisitInformationPerServerTime')),

                // Standard non metadata Goals.get
                // test Goals.get with idGoal=ecommerceOrder and ecommerceAbandonedCart
                array('Goals.get', array('idSite'     => $idSite, 'date' => $dateTime,
                                         'periods'    => array('day', 'week'), 'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART,
                                         'testSuffix' => '_GoalAbandonedCart')),
                array('Goals.get', array('idSite'     => $idSite, 'date' => $dateTime,
                                         'periods'    => array('day', 'week'), 'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                                         'testSuffix' => '_GoalOrder')),
                array('Goals.get', array('idSite'  => $idSite, 'date' => $dateTime,
                                         'periods' => array('day', 'week'), 'idGoal' => 1, 'testSuffix' => '_GoalMatchTitle')),
                array('Goals.get', array('idSite'  => $idSite, 'date' => $dateTime,
                                         'periods' => array('day', 'week'), 'idGoal' => '', 'testSuffix' => '_GoalOverall')),

                array('VisitsSummary.get', array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('day'), 'segment' => 'visitEcommerceStatus==none',
                                                 'testSuffix' => '_SegmentNoEcommerce')),
                array('VisitsSummary.get', array('idSite'  => $idSite, 'date' => $dateTime,
                                                 'periods' => array('day'), 'testSuffix' => '_SegmentOrderedSomething',
                                                 'segment' => 'visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart')),
                array('VisitsSummary.get', array('idSite'  => $idSite, 'date' => $dateTime,
                                                 'periods' => array('day'), 'testSuffix' => '_SegmentAbandonedCart',
                                                 'segment' => 'visitEcommerceStatus==abandonedCart,visitEcommerceStatus==orderedThenAbandonedCart')),

                // test segment visitConvertedGoalId
                array('VisitsSummary.get', array('idSite'  => $idSite, 'date' => $dateTime,
                                                 'periods' => array('day', 'week'), 'testSuffix' => '_SegmentConvertedGoalId1',
                                                 'segment' => "visitConvertedGoalId==" . self::$fixture->idGoalStandard)),
                array('VisitsSummary.get', array('idSite'  => $idSite, 'date' => $dateTime,
                                                 'periods' => array('day'), 'testSuffix' => '_SegmentDidNotConvertGoalId1',
                                                 'segment' => "visitConvertedGoalId!=" . self::$fixture->idGoalStandard)),

                // test segment visitorType
                array('VisitsSummary.get', array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('week'), 'segment' => 'visitorType==new',
                                                 'testSuffix' => '_SegmentNewVisitors')),
                array('VisitsSummary.get', array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('week'), 'segment' => 'visitorType==returning',
                                                 'testSuffix' => '_SegmentReturningVisitors')),
                array('VisitsSummary.get', array('idSite'     => $idSite, 'date' => $dateTime,
                                                 'periods'    => array('week'), 'segment' => 'visitorType==returningCustomer',
                                                 'testSuffix' => '_SegmentReturningCustomers')),

                // test segment visitConvertedGoalId with Ecommerce APIs
                array($apiWithSegments_visitConvertedGoal,
                      array(
                          'idSite' => $idSite,
                          'date' => $dateTime,
                          'periods' => array('week'),
                          'segment' => 'visitConvertedGoalId==1;visitConvertedGoalId!=2',
                          'testSuffix' => '_SegmentVisitHasConvertedGoal')),

                // Different segment will yield same result, so we keep same testSuffix
                array($apiWithSegments_visitConvertedGoal,
                      array(
                          'idSite' => $idSite,
                          'date' => $dateTime,
                          'periods' => array('week'),
                          'segment' => 'visitConvertedGoalId==1;visitConvertedGoalId!=2;countryCode!=xx;deviceType!=tv',
                          'testSuffix' => '_SegmentVisitHasConvertedGoal')),

                // testing a segment on log_conversion matching no visit
                array($apiWithSegments_visitConvertedGoal,
                      array(
                          'idSite' => $idSite,
                          'date' => $dateTime,
                          'periods' => array('week'),
                          'segment' => 'visitConvertedGoalId==666',
                          'testSuffix' => '_SegmentNoVisit_HaveConvertedNonExistingGoal')),

                // test segment visitEcommerceStatus and visitConvertedGoalId
                array($apiWithSegments_visitConvertedGoal,
                      array(
                          'idSite' => $idSite,
                          'date' => $dateTime,
                          'periods' => array('week'),
                          'segment' => 'visitEcommerceStatus!=ordered;visitConvertedGoalId==1',
                          'testSuffix' => '_SegmentVisitHasNotOrderedAndConvertedGoal')),

                // test segment pageTitle
                array('VisitsSummary.get', array('idSite'     => $idSite,
                                                 'date' => $dateTime,
                                                 'periods'    => array('day'),
                                                 'segment' => 'pageTitle==incredible title!',
                                                 'testSuffix' => '_SegmentPageTitleMatch')),

                // test Live! output is OK also for the visit that just bought something (other visits leave an abandoned cart)
                array('Live.getLastVisitsDetails', array('idSite'  => $idSite,
                                                         'date'    => Date::factory($dateTime)->addHour(30.65)->getDatetime(),
                                                         'periods' => array('day'), 'testSuffix' => '_LiveEcommerceStatusOrdered')),

                // test API.get method
                array('API.get', array('idSite'                 => $idSite, 'date' => $dateTime, 'periods' => array('day', 'week'),
                                       'otherRequestParameters' => array(
                                           'columns' => 'nb_pageviews,nb_visits,avg_time_on_site,nb_visits_converted'),
                                       'testSuffix'             => '_API_get')),

                // Website2
                array($goalWeekApi, array('idSite'     => $idSite2, 'date' => $dateTime, 'periods' => array('week'),
                                          'testSuffix' => '_Website2')),

                // see https://github.com/piwik/piwik/issues/7851 make sure avg_order_revenue is calculated correct
                // even if only this column is given
                array('Goals.get', array('idSite' => $idSite,
                                         'date' => $dateTime,
                                         'periods' => array('week'),
                                         'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                                         'otherRequestParameters' => array(
                                            'columns' => 'avg_order_revenue'),
                                         'testSuffix' => '_AvgOrderRevenue')),

           ),
            self::getApiForTestingScheduledReports($dateTime, 'week')
        );
    }

    public static function getOutputPrefix()
    {
        return 'ecommerceOrderWithItems';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

EcommerceOrderWithItemsTest::$fixture = new TwoSitesEcommerceOrderWithItems();