<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoSitesTwoVisitorsDifferentDays;

/**
 * tests Tracker several websites, different days.
 * tests API for period=day/week/month/year, requesting data for both websites,
 * and requesting data for last N periods.
 * Also tests a visit that spans over 2 days.
 * And testing empty URL and empty Page name request
 * Also testing a click on a mailto counted as outlink
 * Also testing metadata API for multiple periods
 *
 * @group TwoVisitorsTwoWebsitesDifferentDaysTest
 * @group TwoSitesTwoVisitorsDifferentDays
 * @group Plugins
 */
class TwoVisitorsTwoWebsitesDifferentDaysTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function testImagesIncludedInTests()
    {
        $this->alertWhenImagesExcludedFromTests();
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        if(self::isTravisCI() && self::isPhpVersion53()) {
            $this->markTestSkipped('This test fails on travis eg. https://travis-ci.org/piwik/piwik/jobs/46944264');
        }
        $this->runApiTests($api, $params);
    }

    protected function getApiToCall()
    {
        return array('VisitFrequency.get',
                     'VisitsSummary.get',
                     'Referrers.getWebsites',
                     'Actions.getPageUrls',
                     'Actions.getPageTitles',
                     'Actions.getOutlinks',
                     'Actions.getPageTitle',
                     'Actions.getPageUrl',
                     'VisitorInterest.getNumberOfVisitsByDaysSinceLast');
    }

    public function getApiForTesting()
    {
        $idSite1 = self::$fixture->idSite1;
        $dateTime = self::$fixture->dateTime;

        $apiToCall = $this->getApiToCall();
        $singlePeriodApi = array('VisitsSummary.get', 'Goals.get');

        $periods = array('day', 'week', 'month', 'year');

        $result = array();

        // testing metadata API for multiple periods
        $apiToCallProcessedReport = array_diff($apiToCall, array('Actions.getPageTitle', 'Actions.getPageUrl'));
        foreach ($apiToCallProcessedReport as $api) {
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

        // Request data for the last 6 periods and idSite=all
        $result[] = array($apiToCall, array('idSite'       => 'all',
                                    'date'         => $dateTime,
                                    'periods'      => $periods,
                                    'setDateLastN' => true)
        );

        // Request data for the last 6 periods and idSite=1
        $result[] = array($apiToCall, array('idSite'       => $idSite1,
                                    'date'         => $dateTime,
                                    'periods'      => $periods,
                                    'setDateLastN' => true,
                                    'testSuffix'   => '_idSiteOne_')
        );

        // We also test a single period to check that this use case (Reports per idSite in the response) works
        $result[] = array($singlePeriodApi, array('idSite'       => 'all',
                                      'date'         => $dateTime,
                                      'periods'      => array('day', 'month'),
                                      'setDateLastN' => false,
                                      'testSuffix'   => '_NotLastNPeriods')
        );

        return array_merge($result, self::getApiForTestingScheduledReports($dateTime, 'month'));
    }

    public static function getOutputPrefix()
    {
        return 'TwoVisitors_twoWebsites_differentDays';
    }
}

TwoVisitorsTwoWebsitesDifferentDaysTest::$fixture = new TwoSitesTwoVisitorsDifferentDays();