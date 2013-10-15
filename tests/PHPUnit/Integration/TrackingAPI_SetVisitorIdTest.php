<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\API\Proxy;

/**
 * This test tests that when using &cid=, the visitor ID is enforced
 *
 */
class Test_Piwik_Integration_TrackingAPI_SetVisitorId extends IntegrationTestCase
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

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
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

Test_Piwik_Integration_TrackingAPI_SetVisitorId::$fixture = new Test_Piwik_Fixture_FewVisitsWithSetVisitorId();

