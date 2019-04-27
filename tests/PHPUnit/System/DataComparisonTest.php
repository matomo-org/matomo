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

                $segmentIndex = array_search($segment, ['', $segment1, $segment2]);
                $periodIndex = array_search($period . '|' . $date, ['|', $period1 . '|' . $date1, $period2 . '|' . $date2]);

                $comparisonIdSubtables[$segmentIndex][$periodIndex] = $compareRow->getMetadata('idsubdatatable_in_db');
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

        $date1 = '2012-08-10';
        $period1 = 'day';

        $date2 = '2012-08-16';
        $period2 = 'week';

        $multiPeriodDate1 = '2012-08-09,2012-08-16';
        $multiPeriodPeriod1 = 'day';

        return [
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

            // API.getProcessedReport test
            // TODO
        ];
    }
}

DataComparisonTest::$fixture = new ManySitesImportedLogs(); // TODO: use better data
