<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\API\Proxy;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\FewVisitsWithSetVisitorId;

/**
 * This test tests that when using &cid=, the visitor ID is enforced
 *
 * @group UserIdAndVisitorIdTest
 * @group Plugins
 */
class UserIdAndVisitorIdTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        Proxy::getInstance()->setHideIgnoredFunctions(false);
    }

    public function tearDown(): void
    {
        Proxy::getInstance()->setHideIgnoredFunctions(true);
    }

    public static function getOutputPrefix()
    {
        return "UserId_VisitorId";
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
            array(array('VisitsSummary.get', 'VisitsSummary.getUsers'),
                  array('idSite'     => self::$fixture->idSite,
                        'date'       => self::$fixture->dateTime,
                        'periods'    => array( 'day', 'month', 'week', 'year' ),
                        'testSuffix' => '',
                  )),

            array('Live.getLastVisitsDetails',
                                        array('idSite'  => self::$fixture->idSite,
                                                     'date'    => self::$fixture->dateTime,
                                                     'periods' => 'month',
                                                     'keepLiveIds' => true,
                                                     'keepLiveDates' => true,
                                                     'otherRequestParameters' => array(
                                                         'showColumns' => 'idVisit,visitorId,userId,lastActionDateTime,actions,actionDetails',
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

            // Testing userId segment matches both log_visits and log_conversion
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

UserIdAndVisitorIdTest::$fixture = new FewVisitsWithSetVisitorId();
