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
            array('VisitsSummary.get', array('idSite'     => self::$fixture->idSite,
                                             'date'       => self::$fixture->dateTime,
                                             'periods'    => 'day',
                                             'testSuffix' => '',
            ))
        );
    }
}

TrackingAPISetVisitorIdTest::$fixture = new FewVisitsWithSetVisitorId();