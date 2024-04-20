<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Fixtures\TwoSitesTwoVisitorsDifferentDays;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

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

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    protected function getApiToCall()
    {
        return [
            'VisitFrequency.get',
            'VisitsSummary.get',
            'Referrers.getWebsites',
            'Actions.getPageUrls',
            'Actions.getPageTitles',
            'Actions.getOutlinks',
            'Actions.getPageTitle',
            'Actions.getPageUrl',
            'VisitorInterest.getNumberOfVisitsByDaysSinceLast',
        ];
    }

    public function getApiForTesting()
    {
        $idSite1  = self::$fixture->idSite1;
        $idSite2  = self::$fixture->idSite2;
        $dateTime = self::$fixture->dateTime;

        $apiToCall       = $this->getApiToCall();
        $singlePeriodApi = ['VisitFrequency.get', 'VisitsSummary.get', 'Goals.get'];

        $periods = ['day', 'week', 'month', 'year'];

        $result = [];

        // testing metadata API for multiple periods
        $apiToCallProcessedReport = array_diff($apiToCall, ['Actions.getPageTitle', 'Actions.getPageUrl']);
        foreach ($apiToCallProcessedReport as $api) {
            [$apiModule, $apiAction] = explode(".", $api);

            $result[] = [
                'API.getProcessedReport',
                [
                    'idSite'       => $idSite1,
                    'date'         => $dateTime,
                    'periods'      => ['day'],
                    'setDateLastN' => true,
                    'apiModule'    => $apiModule,
                    'apiAction'    => $apiAction,
                    'testSuffix'   => '_' . $api . '_firstSite_lastN',
                ],
            ];
        }

        // Request data for the last 6 periods and idSite=all
        $result[] = [
            $apiToCall,
            [
                'idSite'       => 'all',
                'date'         => $dateTime,
                'periods'      => $periods,
                'setDateLastN' => true,
            ],
        ];

        // Request data for the last 6 periods and idSite=1
        $result[] = [
            $apiToCall,
            [
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $periods,
                'setDateLastN' => true,
                'testSuffix'   => '_idSiteOne_',
            ],
        ];

        // We also test a single period to check that this use case (Reports per idSite in the response) works
        $result[] = [
            $singlePeriodApi,
            [
                'idSite'       => 'all',
                'date'         => $dateTime,
                'periods'      => ['day', 'month'],
                'setDateLastN' => false,
                'testSuffix'   => '_NotLastNPeriods',
            ],
        ];
        $result[] = [
            $singlePeriodApi,
            [
                'idSite'       => "$idSite1,$idSite2",
                'date'         => $dateTime,
                'periods'      => 'month',
                'setDateLastN' => false,
                'testSuffix'   => '_idsites',
            ],
        ];

        return array_merge($result, self::getApiForTestingScheduledReports($dateTime, 'month'));
    }

    public static function getOutputPrefix()
    {
        return 'TwoVisitors_twoWebsites_differentDays';
    }
}

TwoVisitorsTwoWebsitesDifferentDaysTest::$fixture = new TwoSitesTwoVisitorsDifferentDays();
