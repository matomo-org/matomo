<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\System;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Tests\Fixtures\TwoSitesEcommerceOrderWithItems;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Tests API methods after ecommerce orders are tracked.
 *
 * @group EcommerceOrderWithItemsTest
 * @group Plugins
 */
class EcommerceOrderWithItemsTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite   = self::$fixture->idSite;
        $idSite2  = self::$fixture->idSite2;
        $dateTime = self::$fixture->dateTime;

        $dayApi = [
            'VisitsSummary.get',
            'VisitTime',
            'CustomVariables.getCustomVariables',
            'Live.getLastVisitsDetails',
            'UserCountry',
            'API.getProcessedReport',
            'Goals.get',
            'Goals.getConversions',
            'Goals.getItemsSku',
            'Goals.getItemsName',
            'Goals.getItemsCategory',
        ];

        $goalWeekApi = ['Goals.get', 'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory'];

        $goalItemApi = ['Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory'];

        $processedReportApi = ['API.getProcessedReport'];

        $apiWithSegments = [
            'Goals.getItemsSku',
            'Goals.getItemsName',
            'Goals.getItemsCategory',
        ];

        // Normal standard goal
        $apiWithSegments_visitConvertedGoal = array_merge($apiWithSegments, ['Goals.get', 'VisitsSummary.get']);
        return array_merge(
            [
                // Segment: This will match the first visit of the fixture only
                [
                    $apiWithSegments,
                    [
                        'idSite'                 => $idSite,
                        'date'                   => $dateTime,
                        'periods'                => ['day', 'week'],
                        'otherRequestParameters' => ['_leavePiwikCoreVariables' => 1],
                        'segment'                => 'pageUrl=@example.org%2Findex.htm',
                        'testSuffix'             => '_SegmentPageUrlContains',
                    ],
                ],

                // Goals.get for Ecommerce, with Page Title segment
                [
                    'Goals.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day', 'week'],
                        'idGoal'     => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                        'segment'    => 'pageTitle==Looking%20at%20product%20page',
                        'testSuffix' => '_EcommerceOrderGoal_SegmentPageUrlContains',
                    ],
                ],

                // Segment: This will match the first visit of the fixture only
                [
                    $apiWithSegments,
                    [
                        'idSite'                 => $idSite,
                        'date'                   => $dateTime,
                        'periods'                => ['day', 'week'],
                        'otherRequestParameters' => ['_leavePiwikCoreVariables' => 1],
                        'segment'                => 'countryCode==fr',
                        'testSuffix'             => '_SegmentCountryIsFr',
                    ],
                ],

                // day tests
                [
                    $dayApi,
                    [
                        'idSite'                 => $idSite,
                        'date'                   => $dateTime,
                        'periods'                => ['day'],
                        'otherRequestParameters' => ['_leavePiwikCoreVariables' => 1],
                    ],
                ],

                // goals API week tests
                [$goalWeekApi, ['idSite' => $idSite, 'date' => $dateTime, 'periods' => ['week']]],

                // abandoned carts tests
                [
                    $goalItemApi,
                    [
                        'idSite'                 => $idSite,
                        'date'                   => $dateTime,
                        'periods'                => ['day', 'week'],
                        'testSuffix'             => '_AbandonedCarts',
                        'otherRequestParameters' => [
                            'abandonedCarts' => 1,
                        ],
                    ],
                ],

                // multiple periods tests
                [
                    $goalItemApi,
                    [
                        'idSite'       => $idSite,
                        'date'         => $dateTime,
                        'periods'      => ['day'],
                        'setDateLastN' => true,
                        'testSuffix'   => 'multipleDates',
                    ],
                ],

                // multiple periods & multiple websites tests
                [
                    $goalItemApi,
                    [
                        'idSite'       => sprintf("%u,%u", $idSite, $idSite2),
                        'date'         => $dateTime,
                        'periods'      => ['day'],
                        'setDateLastN' => true,
                        'testSuffix'   => 'multipleDates_andMultipleWebsites',
                    ],
                ],

                // test metadata products
                [
                    $processedReportApi,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'apiModule'  => 'Goals',
                        'apiAction'  => 'getItemsSku',
                        'testSuffix' => '_Metadata_ItemsSku',
                    ],
                ],
                [
                    $processedReportApi,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'apiModule'  => 'Goals',
                        'apiAction'  => 'getItemsCategory',
                        'testSuffix' => '_Metadata_ItemsCategory',
                    ],
                ],

                // test metadata Goals.get for Ecommerce orders & Carts
                [
                    $processedReportApi,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'apiModule'  => 'Goals',
                        'apiAction'  => 'get',
                        'idGoal'     => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                        'testSuffix' => '_Metadata_Goals.Get_Order',
                    ],
                ],
                [
                    $processedReportApi,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'apiModule'  => 'Goals',
                        'apiAction'  => 'get',
                        'idGoal'     => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART,
                        'testSuffix' => '_Metadata_Goals.Get_AbandonedCart',
                    ],
                ],

                // normal standard goal test
                [
                    $processedReportApi,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'apiModule'  => 'Goals',
                        'apiAction'  => 'get',
                        'idGoal'     => self::$fixture->idGoalStandard,
                        'testSuffix' => '_Metadata_Goals.Get_NormalGoal',
                    ],
                ],

                // non-existant goal test
                [
                    $processedReportApi,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'apiModule'  => 'Goals',
                        'apiAction'  => 'get',
                        'idGoal'     => 'FAKE IDGOAL',
                        'testSuffix' => '_Metadata_Goals.Get_NotExistingGoal',
                    ],
                ],

                // While we're at it, test for a standard Metadata report with zero entries
                [
                    $processedReportApi,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'apiModule'  => 'VisitTime',
                        'apiAction'  => 'getVisitInformationPerServerTime',
                        'testSuffix' => '_Metadata_VisitTime.getVisitInformationPerServerTime',
                    ],
                ],

                // Standard non metadata Goals.get
                // test Goals.get with idGoal=ecommerceOrder and ecommerceAbandonedCart
                [
                    'Goals.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day', 'week'],
                        'idGoal'     => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART,
                        'testSuffix' => '_GoalAbandonedCart',
                    ],
                ],
                [
                    'Goals.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day', 'week'],
                        'idGoal'     => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                        'testSuffix' => '_GoalOrder',
                    ],
                ],
                [
                    'Goals.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day', 'week'],
                        'idGoal'     => 1,
                        'testSuffix' => '_GoalMatchTitle',
                    ],
                ],
                [
                    'Goals.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day', 'week'],
                        'idGoal'     => '',
                        'testSuffix' => '_GoalOverall',
                    ],
                ],

                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'segment'    => 'visitEcommerceStatus==none',
                        'testSuffix' => '_SegmentNoEcommerce',
                    ],
                ],
                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'testSuffix' => '_SegmentOrderedSomething',
                        'segment'    => 'visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart',
                    ],
                ],
                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'testSuffix' => '_SegmentAbandonedCart',
                        'segment'    => 'visitEcommerceStatus==abandonedCart,visitEcommerceStatus==orderedThenAbandonedCart',
                    ],
                ],

                // test segment visitConvertedGoalId
                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day', 'week'],
                        'testSuffix' => '_SegmentConvertedGoalId1',
                        'segment'    => "visitConvertedGoalId==" . self::$fixture->idGoalStandard,
                    ],
                ],
                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'testSuffix' => '_SegmentDidNotConvertGoalId1',
                        'segment'    => "visitConvertedGoalId!=" . self::$fixture->idGoalStandard,
                    ],
                ],

                // test segment visitorType
                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['week'],
                        'segment'    => 'visitorType==new',
                        'testSuffix' => '_SegmentNewVisitors',
                    ],
                ],
                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['week'],
                        'segment'    => 'visitorType==returning',
                        'testSuffix' => '_SegmentReturningVisitors',
                    ],
                ],
                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['week'],
                        'segment'    => 'visitorType==returningCustomer',
                        'testSuffix' => '_SegmentReturningCustomers',
                    ],
                ],

                // test segment visitConvertedGoalId with Ecommerce APIs
                [
                    $apiWithSegments_visitConvertedGoal,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['week'],
                        'segment'    => 'visitConvertedGoalId==1;visitConvertedGoalId!=2',
                        'testSuffix' => '_SegmentVisitHasConvertedGoal',
                    ],
                ],

                // Different segment will yield same result, so we keep same testSuffix
                [
                    $apiWithSegments_visitConvertedGoal,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['week'],
                        'segment'    => 'visitConvertedGoalId==1;visitConvertedGoalId!=2;countryCode!=xx;deviceType!=tv',
                        'testSuffix' => '_SegmentVisitHasConvertedGoal',
                    ],
                ],

                // testing a segment on log_conversion matching no visit
                [
                    $apiWithSegments_visitConvertedGoal,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['week'],
                        'segment'    => 'visitConvertedGoalId==666',
                        'testSuffix' => '_SegmentNoVisit_HaveConvertedNonExistingGoal',
                    ],
                ],

                // test segment visitEcommerceStatus and visitConvertedGoalId
                [
                    $apiWithSegments_visitConvertedGoal,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['week'],
                        'segment'    => 'visitEcommerceStatus!=ordered;visitConvertedGoalId==1',
                        'testSuffix' => '_SegmentVisitHasNotOrderedAndConvertedGoal',
                    ],
                ],

                // test segment pageTitle
                [
                    'VisitsSummary.get',
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => ['day'],
                        'segment'    => 'pageTitle==incredible title!',
                        'testSuffix' => '_SegmentPageTitleMatch',
                    ],
                ],

                // test Live! output is OK also for the visit that just bought something (other visits leave an abandoned cart)
                [
                    'Live.getLastVisitsDetails',
                    [
                        'idSite'     => $idSite,
                        'date'       => Date::factory($dateTime)->addHour(30.65)->getDatetime(),
                        'periods'    => ['day'],
                        'testSuffix' => '_LiveEcommerceStatusOrdered',
                    ],
                ],

                // test API.get method
                [
                    'API.get',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => $dateTime,
                        'periods'                => ['day', 'week'],
                        'otherRequestParameters' => [
                            'columns' => 'nb_pageviews,nb_visits,avg_time_on_site,nb_visits_converted',
                        ],
                        'testSuffix'             => '_API_get',
                    ],
                ],

                // Website2
                [
                    $goalWeekApi,
                    [
                        'idSite'     => $idSite2,
                        'date'       => $dateTime,
                        'periods'    => ['week'],
                        'testSuffix' => '_Website2',
                    ],
                ],

                // see https://github.com/piwik/piwik/issues/7851 make sure avg_order_revenue is calculated correct
                // even if only this column is given
                [
                    'Goals.get',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => $dateTime,
                        'periods'                => ['week'],
                        'idGoal'                 => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                        'otherRequestParameters' => [
                            'columns' => 'avg_order_revenue',
                        ],
                        'testSuffix'             => '_AvgOrderRevenue',
                    ],
                ],

                // product category segment
                [
                    array_merge(['VisitsSummary.get'], $goalItemApi),
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => 'week',
                        'testSuffix' => '_productCategorySegment',
                        'segment'    => 'productCategory==Tools',
                    ],
                ],

                [
                    array_merge(['VisitsSummary.get'], $goalItemApi),
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => 'week',
                        'testSuffix' => '_productNameSegment',
                        'segment'    => 'productName=@' . urlencode(urlencode('bought day after')),
                    ],
                ],

                [
                    array_merge(['VisitsSummary.get'], $goalItemApi),
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => 'week',
                        'testSuffix' => '_productSkuSegment',
                        'segment'    => 'productSku==' . urlencode(urlencode('SKU VERY nice indeed')),
                    ],
                ],

                [
                    $goalItemApi,
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => 'week',
                        'testSuffix' => '_productSkuSegmentSorted',
                        'otherRequestParameters' => [
                            'filter_sort_column' => 'nb_visits',
                        ],
                        'segment'    => 'productSku==' . urlencode(urlencode('SKU VERY nice indeed')),
                    ],
                ],

                // deleted sku will be deleted
                [
                    array_merge(['VisitsSummary.get'], $goalItemApi),
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => 'week',
                        'testSuffix' => '_productSkuSegmentDeleted',
                        'segment'    => 'productSku==' . urlencode(urlencode('SKU WILL BE DELETED')),
                    ],
                ],

                [
                    array_merge(['VisitsSummary.get'], $goalItemApi),
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => 'week',
                        'testSuffix' => '_productPrice',
                        'segment'    => 'productPrice>500',
                    ],
                ],
                [
                    ['Live.getLastVisitsDetails', 'Goals.get'],
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => 'day',
                        'testSuffix' => '_SegmentRevenueOrder',
                        'segment'    => 'revenueOrder>500',
                    ],
                ],
                [
                    ['Live.getLastVisitsDetails', 'Goals.get'],
                    [
                        'idSite'     => $idSite,
                        'date'       => $dateTime,
                        'periods'    => 'day',
                        'testSuffix' => '_SegmentCartRevenueOrder',
                        'segment'    => 'revenueAbandonedCart>100',
                    ],
                ],


            ],
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
