<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

class DataComparisonTest extends SystemTestCase
{
    /**
     * @var ManySitesImportedLogs
     */
    public static $fixture;

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function testSubtableComparisons()
    {
        $segment1 = urlencode('browserCode==ff');
        $segment2 = urlencode('browserCode==ie');

        $date1 = '2012-08-10';
        $period1 = 'day';

        $date2 = '2012-08-16';
        $period2 = 'week';

        $apiToTest = [
            ['Actions.getPageUrls', 'Actions.getPageUrls'],
            ['Referrers.getWebsites', 'Referrers.getUrlsFromWebsiteId'],
        ];

        $allSegments = ['', $segment1, $segment2];
        $allPeriods = ['|', $period1 . '|' . $date1, $period2 . '|' . '2012-08-13']; // '2012-08-13' is the start of the week

        foreach ($apiToTest as list($superApiMethod, $subtableApiMethod)) {
            /** @var DataTable $topLevelComparisons */
            $topLevelComparisons = Request::processRequest($superApiMethod, [
                'idSite' => self::$fixture->idSite,
                'date' => '2012-08-09',
                'period' => 'month',
                'compareSegments' => [$segment1, $segment2],
                'compareDates' => [$date1, $date2],
                'comparePeriods' => [$period1, $period2],
                'compare' => '1',
            ]);

            $rowWithSubtable = null;
            foreach ($topLevelComparisons->getRows() as $row) {
                if ($row->getIdSubDataTable()) {
                    $rowWithSubtable = $row;
                    break;
                }
            }

            $comparisonIdSubtables = [];
            foreach ($rowWithSubtable->getComparisons()->getRows() as $compareRow) {
                $segment = $compareRow->getMetadata('compareSegment');
                $date = $compareRow->getMetadata('compareDate');
                $period = $compareRow->getMetadata('comparePeriod');

                $segmentIndex = array_search($segment, $allSegments);
                $periodIndex = array_search($period . '|' . $date, $allPeriods);
                $seriesIndex = $periodIndex * count($allSegments) + $segmentIndex;

                $comparisonIdSubtables[$seriesIndex] = $compareRow->getMetadata('idsubdatatable_in_db');
            }

            $this->runApiTests($subtableApiMethod, [
                'idSite' => self::$fixture->idSite,
                'date' => '2012-08-09',
                'period' => 'month',
                'testSuffix' => '_subtableActions',
                'otherRequestParameters' => [
                    'idSubtable' => $rowWithSubtable->getIdSubDataTable(),
                    'compareSegments' => [$segment1, $segment2],
                    'compareDates' => [$date1, $date2],
                    'comparePeriods' => [$period1, $period2],
                    'compare' => '1',
                    'comparisonIdSubtables' => json_encode($comparisonIdSubtables),
                ],
            ]);
        }
    }

    public function getApiForTesting()
    {
        $apiToTest = [
            'VisitsSummary.get', // simple datatable
            'Actions.getPageUrls', // actions datatable
            'VisitFrequency.get', // api method that modifies the segment
            'Referrers.getAll', // report w/ subtables
        ];

        $segment1 = urlencode('browserCode==ff');
        $segment2 = urlencode('browserCode==ie');
        $segment3 = urlencode('browserName==icedragon');

        $date1 = '2012-08-10';
        $period1 = 'day';

        $date2 = '2012-08-16';
        $period2 = 'week';

        $multiPeriodDate1 = '2012-08-09,2012-08-16';
        $multiPeriodPeriod1 = 'day';

        $multiPeriodDate2 = '2012-08-12,2012-08-15';
        $multiPeriodPeriod2 = 'day';

        $rangePeriodDate = '2012-08-09,2012-08-16';
        $rangePeriodPeriod = 'range';

        $noDataDate1 = '2013-05-06';
        $noDataPeriod1 = 'day';

        $noDataDate2 = '2013-05-16';
        $noDataPeriod2 = 'day';

        return [
            // no data, multiple compare
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $noDataDate1,
                'period' => $noDataPeriod1,
                'testSuffix' => '_noData_againstWithData',
                'otherRequestParameters' => [
                    'compareDates' => [$date1],
                    'comparePeriods' => [$period1],
                    'compareSegments' => [$segment1],
                    'compare' => '1',
                ],
            ]],
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $noDataDate1,
                'period' => $noDataPeriod1,
                'testSuffix' => '_noData_againsNoData',
                'otherRequestParameters' => [
                    'compareDates' => [$noDataDate2],
                    'comparePeriods' => [$noDataPeriod2],
                    'compareSegments' => [$segment3],
                    'compare' => '1',
                ],
            ]],

            // compare multiple segments
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => '2012-08-09',
                'period' => 'month',
                'testSuffix' => '_segments',
                'otherRequestParameters' => [
                    'compareSegments' => [$segment1, $segment2],
                    'compare' => '1',
                ],
            ]],

            // compare multiple periods
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => '2012-08-09',
                'period' => 'month',
                'testSuffix' => '_periods',
                'otherRequestParameters' => [
                    'compareDates' => [$date1, $date2],
                    'comparePeriods' => [$period1, $period2],
                    'compare' => '1',
                ],
            ]],

            // compare multiple segments/periods
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => '2012-08-09',
                'period' => 'month',
                'testSuffix' => '_segmentsAndPeriods',
                'otherRequestParameters' => [
                    'compareSegments' => [$segment1, $segment2],
                    'compareDates' => [$date1, $date2],
                    'comparePeriods' => [$period1, $period2],
                    'compare' => '1',
                ],
            ]],

            // multiple sites + compare multiple segments/periods
            [$apiToTest, [
                'idSite' => 'all',
                'date' => '2012-08-09',
                'period' => 'month',
                'testSuffix' => '_multipleSites_multipleCompare',
                'otherRequestParameters' => [
                    'compareSegments' => [$segment1, $segment2],
                    'compareDates' => [$date1, $date2],
                    'comparePeriods' => [$period1, $period2],
                    'compare' => '1',
                ],
            ]],

            // multiple periods + compare multiple segments/periods
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $multiPeriodDate1,
                'period' => $multiPeriodPeriod1,
                'testSuffix' => '_multiplePeriods_multipleCompare',
                'otherRequestParameters' => [
                    'compareSegments' => [$segment1, $segment2],
                    'compareDates' => [$date1, $date2],
                    'comparePeriods' => [$period1, $period2],
                    'compare' => '1',
                ],
            ]],

            // multiple sites + multiple periods + compare multiple segments/periods
            [$apiToTest, [
                'idSite' => 'all',
                'date' => $multiPeriodDate1,
                'period' => $multiPeriodPeriod1,
                'testSuffix' => '_multipleSitesPeriods_multipleCompare',
                'otherRequestParameters' => [
                    'compareSegments' => [$segment1, $segment2],
                    'compareDates' => [$date1, $date2],
                    'comparePeriods' => [$period1, $period2],
                    'compare' => '1',
                ],
            ]],
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $multiPeriodDate1,
                'period' => $multiPeriodPeriod1,
                'testSuffix' => '_multipleMultiPeriods',
                'otherRequestParameters' => [
                    'compareSegments' => [$segment1, $segment2],
                    'compareDates' => [$multiPeriodDate1, $multiPeriodDate2],
                    'comparePeriods' => [$multiPeriodPeriod1, $multiPeriodPeriod2],
                    'compare' => '1',
                ],
            ]],

            // comparing with and against ranges
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $rangePeriodDate,
                'period' => $rangePeriodPeriod,
                'testSuffix' => '_rangePeriodAgainstSingle',
                'otherRequestParameters' => [
                    'compareSegments' => [$segment1, $segment2],
                    'compareDates' => [$date1, $date2],
                    'comparePeriods' => [$period1, $period2],
                    'compare' => '1',
                ],
            ]],
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $rangePeriodDate,
                'period' => $rangePeriodPeriod,
                'testSuffix' => '_rangePeriodAgainstMultiple',
                'otherRequestParameters' => [
                    'compareDates' => [$date1, $multiPeriodDate1],
                    'comparePeriods' => [$period1, $multiPeriodPeriod1],
                    'compare' => '1',
                ],
            ]],
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $date1,
                'period' => $period1,
                'testSuffix' => '_singleAgainstRange',
                'otherRequestParameters' => [
                    'compareDates' => [$rangePeriodDate],
                    'comparePeriods' => [$rangePeriodPeriod],
                    'compare' => '1',
                ],
            ]],
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $multiPeriodDate1,
                'period' => $multiPeriodPeriod1,
                'testSuffix' => '_multipleAgainstRange',
                'otherRequestParameters' => [
                    'compareDates' => [$rangePeriodDate],
                    'comparePeriods' => [$rangePeriodPeriod],
                    'compare' => '1',
                ],
            ]],
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => $multiPeriodDate2,
                'period' => $multiPeriodPeriod2,
                'testSuffix' => '_multipleAgainstLongMultiple',
                'otherRequestParameters' => [
                    'compareDates' => [$rangePeriodDate, '2012-08-13,2012-08-14'],
                    'comparePeriods' => ['day', 'day'],
                    'compare' => '1',
                ],
            ]],

            // API.getProcessedReport tests
            ['API.getProcessedReport', [
                'idSite' => self::$fixture->idSite,
                'date' => $multiPeriodDate1,
                'period' => $multiPeriodPeriod2,
                'testSuffix' => '_processedReport',
                'apiModule' => 'VisitsSummary',
                'apiAction' => 'get',
                'otherRequestParameters' => [
                    'compareDates' => [$multiPeriodDate2],
                    'comparePeriods' => [$multiPeriodPeriod2],
                    'compare' => '1',
                ],
            ]],
        ];
    }
}

DataComparisonTest::$fixture = new ManySitesImportedLogs();
