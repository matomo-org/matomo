<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Same as OneVisitorTwoVisits.test.php, but with cookie support, which incurs some slight changes
 * in the reporting data (more accurate unique visitor count, better referrer tracking for goals, etc.)
 */
class Test_Piwik_Integration_OneVisitorTwoVisits_WithCookieSupport extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     *
     */
    public function testApi($api, $params)
    {
        //var_dump(\Piwik\Db::get()->fetchAll('select * from piwiktests_log_action' ));
        $this->runApiTests($api, $params);

    }

    public function getApiForTesting()
    {
        $apiToCall = array(
            'VisitTime', 'VisitsSummary', 'VisitorInterest', 'VisitFrequency', 'UserSettings',
            'UserCountry', 'Referrers', 'Provider', 'Goals', 'CustomVariables', 'CoreAdminHome',
            'Actions', 'Live.getLastVisitsDetails');

        return array(
            array($apiToCall, array('idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime))
        );
    }

    public static function getOutputPrefix()
    {
        return 'OneVisitorTwoVisits_withCookieSupport';
    }
}

Test_Piwik_Integration_OneVisitorTwoVisits_WithCookieSupport::$fixture = new Test_Piwik_Fixture_OneVisitorTwoVisits();
Test_Piwik_Integration_OneVisitorTwoVisits_WithCookieSupport::$fixture->useThirdPartyCookies = true;
Test_Piwik_Integration_OneVisitorTwoVisits_WithCookieSupport::$fixture->useSiteSearch = true;

