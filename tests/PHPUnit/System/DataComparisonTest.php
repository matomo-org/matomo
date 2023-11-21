<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
/**
 * Testing Data comparison
 *
 * @group DataComparisonTest
 */
class DataComparisonTest extends SystemTestCase
{
    /**
     * @var ManySitesImportedLogs
     */
    public static $fixture;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Configures a custom dimensions and adds a segment for it. This segment will only be available for
        // the specific site and will be invalid in global context
        // Added to avoid further regressions like: https://github.com/matomo-org/matomo/issues/21573
        $idDimension = \Piwik\Plugins\CustomDimensions\API::getInstance()->configureNewCustomDimension(
            self::$fixture->idSite, 'test', CustomDimensions::SCOPE_VISIT, true
        );
        Fixture::clearInMemoryCaches(false);
        \Piwik\Plugins\SegmentEditor\API::getInstance()->add('custom dimension', "dimension$idDimension==test", self::$fixture->idSite);
    }

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

        foreach ($apiToTest as [$superApiMethod, $subtableApiMethod]) {
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
            foreach (array_values($rowWithSubtable->getComparisons()->getRows()) as $seriesIndex => $compareRow) {
                $comparisonIdSubtables[$seriesIndex] = $compareRow->getMetadata('idsubdatatable');
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
        $segment4 = urlencode('operatingSystemCode==WIN');

        $date1 = '2012-08-10';
        $period1 = 'day';

        $date2 = '2012-08-16';
        $period2 = 'week';

        $date3 = '2012-08-16';
        $period3 = 'month';

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
            ['API.getProcessedReport', [
                'idSite' => self::$fixture->idSite,
                'date' => $date1,
                'period' => $period1,
                'testSuffix' => '_processedReportSingle',
                'apiModule' => 'UserCountry',
                'apiAction' => 'getContinent',
                'otherRequestParameters' => [
                    'compareDates' => [$date2],
                    'comparePeriods' => [$period2],
                    'compareSegments' => [$segment2],
                    'compare' => '1',
                ],
            ]],

            // label filter tests
            ['Actions.getPageUrls', [
                'idSite' => self::$fixture->idSite,
                'date' => $date3,
                'period' => $period3,
                'testSuffix' => '_labelFilter',
                'otherRequestParameters' => [
                    'compareDates' => [$date2],
                    'comparePeriods' => [$period2],
                    'compare' => '1',
                    'label' => ['blog', 'faq'],
                ],
            ]],
            ['Actions.getPageUrls', [
                'idSite' => self::$fixture->idSite,
                'date' => $date3,
                'period' => $period3,
                'testSuffix' => '_labelFilterWithSeries',
                'otherRequestParameters' => [
                    'compareDates' => [$date2],
                    'comparePeriods' => [$period2],
                    'compareSegments' => [$segment4],
                    'compare' => '1',
                    'label' => ['blog', 'faq'],
                    'labelSeries' => '1,2',
                ],
            ]],

            // flat tests
            [['Actions.getPageUrls', 'Referrers.getWebsites'], [
                'idSite' => self::$fixture->idSite,
                'date' => $date3,
                'period' => $period3,
                'testSuffix' => '_flat',
                'otherRequestParameters' => [
                    'compareDates' => [$date2],
                    'comparePeriods' => [$period2],
                    'compareSegments' => [$segment4],
                    'compare' => '1',
                    'flat' => '1',
                    'expanded' => '0',
                ],
            ]],

            // single row evolution
            ['API.getRowEvolution', [
                'idSite' => self::$fixture->idSite,
                'date' => $date3,
                'testSuffix' => '_singleRowEvolution',
                'otherRequestParameters' => [
                    'date' => '2012-08-01,2012-08-31',
                    'period' => 'day',
                    'apiModule' => 'Actions',
                    'apiAction' => 'getPageUrls',
                    'label' => 'blog',
                    'expanded' => '0',
                ],
            ]],

            // multi row evolution
            ['API.getRowEvolution', [
                'idSite' => self::$fixture->idSite,
                'date' => $date3,
                'testSuffix' => '_multiRowEvolution',
                'otherRequestParameters' => [
                    'date' => '2012-08-01,2012-08-31',
                    'period' => 'day',
                    'apiModule' => 'Actions',
                    'apiAction' => 'getPageUrls',
                    'label' => ['blog', 'faq'],
                    'expanded' => '0',
                ],
            ]],

            // invert comparison change test
            [$apiToTest, [
                'idSite' => self::$fixture->idSite,
                'date' => '2012-08-09',
                'period' => 'month',
                'testSuffix' => '_invertCompare',
                'otherRequestParameters' => [
                    'compareSegments' => [$segment1, $segment2],
                    'compareDates' => [$date1],
                    'comparePeriods' => [$period1],
                    'compare' => '1',
                    'invert_compare_change_compute' => '1',
                ],
            ]],
        ];
    }
}

DataComparisonTest::$fixture = new ManySitesImportedLogs();
DataComparisonTest::$fixture->includeCloudfront = true;
