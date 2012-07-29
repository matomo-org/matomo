<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

require_once dirname(__FILE__) . '/OneVisitorTwoVisitsTest.php';

/**
 * Same as OneVisitorTwoVisits.test.php, but with cookie support, which incurs some slight changes
 * in the reporting data (more accurate unique visitor count, better referer tracking for goals, etc.)
 */
class Test_Piwik_Integration_OneVisitorTwoVisits_WithCookieSupport extends Test_Piwik_Integration_OneVisitorTwoVisits
{
    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        OneVisitorTwoVisits_WithCookieSupport
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array(
            'VisitTime', 'VisitsSummary', 'VisitorInterest', 'VisitFrequency', 'UserSettings',
            'UserCountry', 'Referers', 'Provider', 'Goals', 'CustomVariables', 'CoreAdminHome',
            'Actions', 'Live.getLastVisitsDetails');

        return array(
            array($apiToCall, array('idSite' => self::$idSite, 'date' => self::$dateTime))
        );
    }

    public function getOutputPrefix()
    {
        return 'OneVisitorTwoVisits_withCookieSupport';
    }

    protected function trackVisits()
    {
        $t                   = $this->getTracker(self::$idSite, self::$dateTime, $defaultInit = true, $useThirdPartyCookie = 1);
        $t->DEBUG_APPEND_URL = '&forceUseThirdPartyCookie=1';
        $this->trackVisitsImpl($t);
    }
}
