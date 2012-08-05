<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * Tests API methods with goals that do and don't allow multiple
 * conversions per visit.
 */
class Test_Piwik_Integration_TrackGoals_AllowMultipleConversionsPerVisit extends IntegrationTestCase
{
    protected static $dateTime = '2009-01-04 00:11:42';
    protected static $idSite = 1;
    protected static $idGoal_OneConversionPerVisit = 1;
    protected static $idGoal_MultipleConversionPerVisit = 2;

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

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        TrackGoals_AllowMultipleConversionsPerVisit
     */
    public function testApi($api, $params)
    {
            $this->runApiTests($api, $params);
    }

    /**
     * @depends      testApi
     * @group        Integration
     * @group        TrackGoals_AllowMultipleConversionsPerVisit
     */
    public function testCheck()
    {
        $idSite = self::$idSite;

        // test delete is working as expected
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $this->assertTrue(2 == count($goals));
        Piwik_Goals_API::getInstance()->deleteGoal($idSite, self::$idGoal_OneConversionPerVisit);
        Piwik_Goals_API::getInstance()->deleteGoal($idSite, self::$idGoal_MultipleConversionPerVisit);
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $this->assertTrue(empty($goals));
    }

    public function getApiForTesting()
    {
        $apiToCall = array('VisitTime.getVisitInformationPerServerTime', 'VisitsSummary.get');

        return array(
            array($apiToCall, array('idSite' => self::$idSite, 'date' => self::$dateTime))
        );
    }

    public function getOutputPrefix()
    {
        return 'trackGoals_allowMultipleConversionsPerVisit';
    }

    public static function setUpWebsitesAndGoals()
    {
        self::createWebsite(self::$dateTime);

        // First, a goal that is only recorded once per visit
        Piwik_Goals_API::getInstance()->addGoal(self::$idSite, 'triggered js ONCE', 'title', 'Thank you', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = false);

        // Second, a goal allowing multiple conversions
        Piwik_Goals_API::getInstance()->addGoal(self::$idSite, 'triggered js MULTIPLE ALLOWED', 'manually', '', '', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = true);
    }

    protected static function trackVisits()
    {
        $dateTime                          = self::$dateTime;
        $idSite                            = self::$idSite;
        $idGoal_OneConversionPerVisit      = self::$idGoal_OneConversionPerVisit;
        $idGoal_MultipleConversionPerVisit = self::$idGoal_MultipleConversionPerVisit;

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        // Record 1st goal, should only have 1 conversion
        $t->setUrl('http://example.org/index.htm');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackPageView('Thank you mate'));
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 10000000));

        // Record 2nd goal, should record both conversions
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.5)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_MultipleConversionPerVisit, $revenue = 300));
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.6)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_MultipleConversionPerVisit, $revenue = 366));

        // Update & set to not allow multiple
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $goal  = $goals[$idGoal_OneConversionPerVisit];
        self::assertTrue($goal['allow_multiple'] == 0);
        Piwik_Goals_API::getInstance()->updateGoal($idSite, $idGoal_OneConversionPerVisit, $goal['name'], @$goal['match_attribute'], @$goal['pattern'], @$goal['pattern_type'], @$goal['case_sensitive'], $goal['revenue'], $goal['allow_multiple'] = 1);
        self::assertTrue($goal['allow_multiple'] == 1);

        // 1st goal should Now be tracked
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.61)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 656));
    }
}
