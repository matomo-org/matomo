<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * This test tests that when using &cid=, the visitor ID is enforced
 *
 */
class Test_Piwik_Integration_TrackingAPI_SetVisitorId extends IntegrationTestCase
{
    protected static $idSite   = 1;
    protected static $dateTime = '2010-03-06 11:22:33';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
            self::setUpWebsitesAndGoals();
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }

    public function setUp()
    {
        Piwik_API_Proxy::getInstance()->setHideIgnoredFunctions(false);
    }

    public function tearDown()
    {
        Piwik_API_Proxy::getInstance()->setHideIgnoredFunctions(true);
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        OneVisitorTwoVisits
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        return array(
            // test hideColumns && showColumns parameters
            array('VisitsSummary.get', array('idSite' => self::$idSite, 'date' => self::$dateTime,
                                             'periods' => 'day',
                                             'testSuffix' => '',
            ))
        );
    }

    protected static function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        self::createWebsite(self::$dateTime);
    }

    protected static function trackVisits()
    {
        $dateTime = self::$dateTime;
        $idSite   = self::$idSite;
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        // First, some basic tests
        self::settingInvalidVisitorIdShouldThrow($t);

        // We create VISITOR A
        $t->setUrl('http://example.org/index.htm');
        $t->setVisitorId(Piwik_Tracker_Visit::generateUniqueVisitorId());
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // VISITOR B: few minutes later, we trigger the same tracker but with a custom visitor ID,
        // => this will create a new visit B
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.05)->getDatetime());
        $t->setUrl('http://example.org/index2.htm');
        $t->setVisitorId(Piwik_Tracker_Visit::generateUniqueVisitorId());
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // This new visit B will have 2 page views
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.org/index3.htm');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // total = 2 visitors, 3 page views

    }

    static protected function settingInvalidVisitorIdShouldThrow(PiwikTracker $t)
    {
        try {
            $t->setVisitorId('test');
            $this->fail('should throw');
        } catch(Exception $e) {
            //OK
        }
        try {
            $t->setVisitorId('61e8');
            $this->fail('should throw');
        } catch(Exception $e) {
            //OK
        }
        try {
            $t->setVisitorId('61e8cc2d51fea26dabcabcabc');
            $this->fail('should throw');
        } catch(Exception $e) {
            //OK
        }
    }
}
