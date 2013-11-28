<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Plugins\Goals\Archiver;

require_once 'Goals/Goals.php';

/**
 * Same as TwoVisitors_twoWebsites_differentDays but with goals that convert
 * on every url.
 */
class Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays_Conversions extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     *
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiToCall()
    {
        return array('Goals.getDaysToConversion',
                     'MultiSites.getAll'
        );
    }

    public function getApiForTesting()
    {
        $dateTime = self::$fixture->dateTime;
        $idSite1 = self::$fixture->idSite1;

        // NOTE: copied from TwoVisitors_TwoWebsites_DifferentDays (including the test or inheriting means
        // the test will get run by phpunit, even when we only want to run this one. should be put into
        // non-test class later.)
        $apiToCall = $this->getApiToCall();
        $singlePeriodApi = array('VisitsSummary.get', 'Goals.get');

        $periods = array(
                'day',
                'week',
                'month',
                'year'
        );

        $result = array(
            // Request data for the last 6 periods and idSite=all
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => $dateTime,
                                    'periods'      => $periods,
                                    'setDateLastN' => true)),

            // Request data for the last 6 periods and idSite=1
            array($apiToCall, array('idSite'       => $idSite1,
                                    'date'         => $dateTime,
                                    'periods'      => $periods,
                                    'setDateLastN' => true,
                                    'testSuffix'   => '_idSiteOne_')),

            // We also test a single period to check that this use case (Reports per idSite in the response) works
            array($singlePeriodApi, array('idSite'       => 'all',
                                          'date'         => $dateTime,
                                          'periods'      => array('day', 'month'),
                                          'setDateLastN' => false,
                                          'testSuffix'   => '_NotLastNPeriods')),
        );

        // testing metadata API for multiple periods
        $apiToCall = array_diff($apiToCall, array('Actions.getPageTitle', 'Actions.getPageUrl'));
        foreach ($apiToCall as $api) {
            list($apiModule, $apiAction) = explode(".", $api);

            $result[] = array(
                'API.getProcessedReport', array('idSite'       => $idSite1,
                                                'date'         => $dateTime,
                                                'periods'      => array('day'),
                                                'setDateLastN' => true,
                                                'apiModule'    => $apiModule,
                                                'apiAction'    => $apiAction,
                                                'testSuffix'   => '_' . $api . '_firstSite_lastN')
            );
        }

        // Tests that getting a visits summary metric (nb_visits) & a Goal's metric (Goal_revenue)
        // at the same time works.
        $dateTime = '2010-01-03,2010-01-06';
        $columns = 'nb_visits,' . Archiver::getRecordName('conversion_rate');

        $result[] = array(
            'VisitsSummary.get', array('idSite'                 => 'all', 'date' => $dateTime, 'periods' => 'range',
                                       'otherRequestParameters' => array('columns' => $columns),
                                       'testSuffix'             => '_getMetricsFromDifferentReports')
        );

        return $result;
    }

    public static function getOutputPrefix()
    {
        return 'TwoVisitors_twoWebsites_differentDays_Conversions';
    }
}

Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays_Conversions::$fixture =
    new Test_Piwik_Fixture_TwoSitesTwoVisitorsDifferentDays();
Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays_Conversions::$fixture->allowConversions = true;

