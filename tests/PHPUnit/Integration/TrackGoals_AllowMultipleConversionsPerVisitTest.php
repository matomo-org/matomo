<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Plugins\Goals\API;

/**
 * Tests API methods with goals that do and don't allow multiple
 * conversions per visit.
 */
class Test_Piwik_Integration_TrackGoals_AllowMultipleConversionsPerVisit extends IntegrationTestCase
{
    public static $fixture = null;

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    /**
     * @depends      testApi
     * @group        Integration
     */
    public function testCheck()
    {
        $idSite = self::$fixture->idSite;

        // test delete is working as expected
        $goals = API::getInstance()->getGoals($idSite);
        $this->assertTrue(2 == count($goals));
        API::getInstance()->deleteGoal($idSite, self::$fixture->idGoal_OneConversionPerVisit);
        API::getInstance()->deleteGoal($idSite, self::$fixture->idGoal_MultipleConversionPerVisit);
        $goals = API::getInstance()->getGoals($idSite);
        $this->assertTrue(empty($goals));
    }

    public function getApiForTesting()
    {
        $apiToCall = array('VisitTime.getVisitInformationPerServerTime', 'VisitsSummary.get');

        return array(
            array($apiToCall, array('idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime))
        );
    }

    public static function getOutputPrefix()
    {
        return 'trackGoals_allowMultipleConversionsPerVisit';
    }
}

Test_Piwik_Integration_TrackGoals_AllowMultipleConversionsPerVisit::$fixture
    = new Piwik_Test_Fixture_SomeVisitsAllConversions();
