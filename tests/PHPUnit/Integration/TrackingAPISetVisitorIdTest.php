<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\API\Proxy;
use Piwik\Tests\IntegrationTestCase;
use Piwik\Tests\Fixtures\FewVisitsWithSetVisitorId;

/**
 * This test tests that when using &cid=, the visitor ID is enforced
 *
 * @group TrackingAPISetVisitorIdTest
 * @group Integration
 */
class TrackingAPISetVisitorIdTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public function setUp()
    {
        Proxy::getInstance()->setHideIgnoredFunctions(false);
    }

    public function tearDown()
    {
        Proxy::getInstance()->setHideIgnoredFunctions(true);
    }

    public static function getOutputPrefix()
    {
        return "TrackingAPI_SetVisitorId";
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
        return array(
            array('VisitsSummary.get',
                                        array('idSite'     => self::$fixture->idSite,
                                             'date'       => self::$fixture->dateTime,
                                             'periods'    => 'day',
                                             'testSuffix' => '',
            )),

            array('Live.getLastVisitsDetails',
                                        array('idSite'  => self::$fixture->idSite,
                                                     'date'    => self::$fixture->dateTime,
                                                     'periods' => 'day',
                                                     'keepLiveIds' => true,
                                                     'keepLiveDates' => true,
                                                     'otherRequestParameters' => array(
                                                         'showColumns' => 'idVisit,visitorId,userId,lastActionDateTime,actions,actionDetails',
                                                         'filter_sort_column' => 'idVisit',
                                                         'filter_sort_order' => 'asc',
                                                     )
            )),

            // Testing userId segment matches both log_visits and log_conversion
            array(array('VisitsSummary.get', 'Goals.get'),
                                        array('idSite'     => self::$fixture->idSite,
                                             'date'       => self::$fixture->dateTime,
                                             'periods'    => 'day',
                                             'segment'    => 'userId==' . urlencode('new-email@example.com'),
                                             'testSuffix' => '_segmentUserId',
            )),

            array('Goals.getItemsName',
                                        array('idSite'     => self::$fixture->idSite,
                                               'date'       => self::$fixture->dateTime,
                                               'periods'    => 'day',
                                               'segment'    => 'visitEcommerceStatus==abandonedCart;userId==' . urlencode('new-email@example.com'),
                                               'testSuffix' => '_segmentUserIdAndCartAbandoned_getAbandonedCartItems',
                                               'otherRequestParameters' => array(
                                                       'abandonedCarts' => 1
                                               ),
            )),
        );
    }
}

TrackingAPISetVisitorIdTest::$fixture = new FewVisitsWithSetVisitorId();