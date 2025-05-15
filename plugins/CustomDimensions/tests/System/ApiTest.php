<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\System;

use Piwik\Cache;
use Piwik\Context;
use Piwik\Plugins\CustomDimensions\tests\Fixtures\TrackVisitsWithCustomDimensionsFixture;
use Piwik\ReportRenderer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Config;
use Piwik\Plugins\CustomDimensions\FeatureFlags\CustomDimensionReportWithRollUp;

/**
 * @group CustomDimensions
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var TrackVisitsWithCustomDimensionsFixture
     */
    public static $fixture = null; // initialized below class definition

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::setAllowedModulesToFilterApiResponse('API.getReportMetadata', array('CustomDimensions'));
        self::setAllowedCategoriesToFilterApiResponse('API.getSegmentsMetadata', array('Visitors', 'Behaviour'));
        self::setAllowedModulesToFilterApiResponse('API.getWidgetMetadata', array('CustomDimensions'));
        self::setAllowedCategoriesToFilterApiResponse('API.getReportPagesMetadata', array('Visitors', 'Behaviour'));
    }

    private static function triggerWithRollupFeatureFlag(bool $enableFlag)
    {
        $config = Config::getInstance();
        $featureFlag = new CustomDimensionReportWithRollUp();
        $featureFlagConfig = $featureFlag->getName() . '_feature';

        if ($enableFlag) {
            $config->FeatureFlags = [$featureFlagConfig => 'enabled'];
        } else {
            $config->FeatureFlags = [$featureFlagConfig => 'disabled'];
        }
    }

    /**
     * @dataProvider getApiForTesting
     * @dataProvider getMetadataApiForTesting
     */
    public function testApi($api, $params)
    {
        self::triggerWithRollupFeatureFlag($enableFlag = false);
        self::deleteArchiveTables();
        $this->runApiTests($api, $params);
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApiWithRollup($api, $params)
    {
        if (!empty($params["testSuffix"])) {
            $params["testSuffix"] .= "_with_rollup";
        }
        self::triggerWithRollupFeatureFlag($enableFlag = true);
        self::deleteArchiveTables();
        $this->runApiTests($api, $params);
    }

    /**
     * @dataProvider getRankingLimitTestData
     * @dataProvider getRankingLimitTestDataExpanded
     */
    public function testRankingLimit(
        int $rowsRankingQuery,
        int $rowsTableTopLevel,
        int $rowsTableSubTable,
        string $testSuffix,
        array $additionalRequestParameters = []
    ): void {
        self::triggerWithRollupFeatureFlag($enableFlag = false);
        self::deleteArchiveTables();
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['archiving_ranking_query_row_limit'] = $rowsRankingQuery;
        $generalConfig['datatable_archiving_maximum_rows_custom_dimensions'] = $rowsTableTopLevel;
        $generalConfig['datatable_archiving_maximum_rows_subtable_custom_dimensions'] = $rowsTableSubTable;

        // flush caches to ensure the RecordBuilder picks up the changed configuration
        Cache::flushAll();
        $this->runApiTests(['CustomDimensions.getCustomDimension'], [
            'idSite' => 3,
            'date' => self::$fixture->dateTime,
            'periods' => ['day'],
            'otherRequestParameters' => array_merge(['idDimension' => 1], $additionalRequestParameters),
            'testSuffix' => 'ranking_limit_' . $testSuffix,
        ]);
    }

    /**
     * @dataProvider getRankingLimitTestDataWithRollup
     * @dataProvider getRankingLimitTestDataExpandedWithRollup
     */
    public function testRankingLimitWithRollup(
        int $rowsRankingQuery,
        int $rowsTableTopLevel,
        int $rowsTableSubTable,
        string $testSuffix,
        array $additionalRequestParameters = []
    ): void {
        self::triggerWithRollupFeatureFlag($enableFlag = true);
        self::deleteArchiveTables();
        $generalConfig = &Config::getInstance()->General;
        $generalConfig['archiving_ranking_query_row_limit'] = $rowsRankingQuery;
        $generalConfig['datatable_archiving_maximum_rows_custom_dimensions'] = $rowsTableTopLevel;
        $generalConfig['datatable_archiving_maximum_rows_subtable_custom_dimensions'] = $rowsTableSubTable;

        // flush caches to ensure the RecordBuilder picks up the changed configuration
        Cache::flushAll();
        $this->runApiTests(['CustomDimensions.getCustomDimension'], [
            'idSite' => 3,
            'date' => self::$fixture->dateTime,
            'periods' => ['day'],
            'otherRequestParameters' => array_merge(['idDimension' => 1], $additionalRequestParameters),
            'testSuffix' => 'ranking_limit_with_rollup_' . $testSuffix,
        ]);
    }

    public function getApiForTesting()
    {
        $api = array(
            'CustomDimensions.getCustomDimension',
        );

        $tests = array(
            array('idSite' => 1, 'idDimension' => 1),
            array('idSite' => 1, 'idDimension' => 2),
            array('idSite' => 1, 'idDimension' => 3),
            array('idSite' => 1, 'idDimension' => 4),
            array('idSite' => 1, 'idDimension' => 5),
            array('idSite' => 1, 'idDimension' => 6),
            array('idSite' => 2, 'idDimension' => 1),
            array('idSite' => 1, 'idDimension' => 999), // dimension does not exist
        );

        $removeColumns = [
            'sum_time_generation',
            'sum_bandwidth',
            'nb_hits_with_bandwidth',
            'min_bandwidth',
            'max_bandwidth',
            'avg_bandwidth',
            'nb_total_overall_bandwidth',
            'nb_total_pageview_bandwidth',
            'nb_total_download_bandwidth',
            'nb_visits_converted'
        ];

        $apiToTest = array();

        foreach ($tests as $test) {
            $idSite = $test['idSite'];
            $idDimension = $test['idDimension'];

            foreach (array('day', 'year') as $period) {
                $apiToTest[] = array($api,
                    array(
                        'idSite'     => $idSite,
                        'date'       => self::$fixture->dateTime,
                        'periods'    => array($period),
                        'otherRequestParameters' => array(
                            'idDimension' => $idDimension,
                            'expanded' => '0',
                            'flat' => '0',
                        ),
                        'testSuffix' => "{$period}_site_{$idSite}_dimension_{$idDimension}",
                        'xmlFieldsToRemove' => $removeColumns
                    )
                );
            }
        }

        $apiToTest[] = array($api, array(
            'idSite'     => 1,
            'date'       => self::$fixture->dateTime,
            'periods'    => array('day'),
            'otherRequestParameters' => array(
                'idDimension' => 3,
                'expanded' => '1',
                'flat' => '0',
            ),
            'testSuffix' => "day_site_1_dimension_3_expanded",
            'xmlFieldsToRemove' => $removeColumns
        ));

        $apiToTest[] = array($api, array(
            'idSite'     => 1,
            'date'       => self::$fixture->dateTime,
            'periods'    => array('day'),
            'otherRequestParameters' => array(
                'idDimension' => 3,
                'expanded' => '0',
                'flat' => '1',
            ),
            'testSuffix' => "day_site_1_dimension_3_flat",
            'xmlFieldsToRemove' => $removeColumns
        ));

        $apiToTest[] = array($api,
            array(
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('year'),
                'segment'    => 'dimension1=@value5',
                'otherRequestParameters' => array(
                    'idDimension' => 1,
                ),
                'testSuffix' => "year_site_1_dimension_1_withsegment",
                'xmlFieldsToRemove' => $removeColumns
            )
        );

        $apiToTest[] = array(array('API.getProcessedReport'),
                             array(
                                 'idSite'  => 1,
                                 'date'    => self::$fixture->dateTime,
                                 'periods' => array('year'),
                                 'otherRequestParameters' => array(
                                     'apiModule' => 'CustomDimensions',
                                     'apiAction' => 'getCustomDimension',
                                     'idDimension' => '3'
                                 ),
                                 'testSuffix' => '_actionDimension',
                                 'xmlFieldsToRemove' => ['idsubdatatable']
                             )
        );

        $apiToTest[] = array(array('API.getProcessedReport'),
            array(
                'idSite'  => 1,
                'date'    => self::$fixture->dateTime,
                'periods' => array('year'),
                'otherRequestParameters' => array(
                    'apiModule' => 'CustomDimensions',
                    'apiAction' => 'getCustomDimension',
                    'idDimension' => '1'
                ),
                'testSuffix' => '_visitDimension',
                'xmlFieldsToRemove' => ['nb_visits_converted']
           )
        );

        $removeColumns = [
            'generationTimeMilliseconds',
            'totalEcommerceRevenue',
            'totalEcommerceConversions',
            'totalEcommerceItems',
            'totalAbandonedCarts',
            'totalAbandonedCartsRevenue',
            'totalAbandonedCartsItems'
        ];

        $apiToTest[] = array(
            array('Live.getLastVisitsDetails'),
            array(
                'idSite'                 => 1,
                'date'                   => self::$fixture->dateTime,
                'periods'                => array('year'),
                'xmlFieldsToRemove'      => $removeColumns
            )
        );

        return $apiToTest;
    }

    public function getMetadataApiForTesting()
    {
        $apiToTest = array();

        $apiToTest[] = array(
            array('API.getReportMetadata'),
            array(
                'idSite'  => 1,
                'date'    => self::$fixture->dateTime,
                'periods' => array('day')
            )
        );

        $apiToTest[] = array(array('API.getSegmentsMetadata'),
            array(
                'idSite' => 1,
                'date' => self::$fixture->dateTime,
                'periods' => array('year'),
                'otherRequestParameters' => [
                    'hideColumns' => 'acceptedValues' // hide accepted values as they might change
                ]
            )
        );

        $apiToTest[] = array(
            array('API.getReportPagesMetadata'),
            array(
                'idSite'  => 1,
                'date'    => self::$fixture->dateTime,
                'periods' => array('day')
            )
        );

        $apiToTest[] = array(array('CustomDimensions.getAvailableExtractionDimensions'),
            array(
                'idSite'  => 1,
                'date'    => self::$fixture->dateTime,
                'periods' => array('day')
            )
        );

        foreach (array(1, 2, 99) as $idSite) {
            $api = array('CustomDimensions.getConfiguredCustomDimensions',
                         'CustomDimensions.getAvailableScopes');
            $apiToTest[] = array($api,
                array(
                    'idSite'     => $idSite,
                    'date'       => self::$fixture->dateTime,
                    'periods'    => array('day'),
                    'testSuffix' => '_' . $idSite
                )
            );

            $apiToTest[] = array('CustomDimensions.getConfiguredCustomDimensionsHavingScope',
                array(
                    'idSite'     => $idSite,
                    'date'       => self::$fixture->dateTime,
                    'periods'    => array('day'),
                    'testSuffix' => '_' . $idSite,
                    'otherRequestParameters' => [
                        'scope' => 'visit',
                    ],
                ),
            );
        }

        $apiToTest[] = array(
            array('API.getWidgetMetadata'),
            array(
                'idSite'  => 1,
                'date'    => self::$fixture->dateTime,
                'periods' => array('day')
            )
        );

        return $apiToTest;
    }

    public function testScheduledReport()
    {
        if (!Fixture::canImagesBeIncludedInScheduledReports()) {
            $this->markTestSkipped("Skipping test for scheduled reports, as system settings don't match.");
        }
        // Context change is needed, as otherwise the customdimension reports are not available
        Context::changeIdSite(1, function () {
            $this->runApiTests(['ScheduledReports.generateReport'], [
                'idSite'                 => 1,
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['year'],
                'format'                 => 'original',
                'fileExtension'          => 'pdf',
                'otherRequestParameters' => [
                    'idReport'     => 1,
                    'reportFormat' => ReportRenderer::PDF_FORMAT,
                    'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN,
                    'serialize'    => 0,
                ],
            ]);
        });
    }

    public function getRankingLimitTestData(): iterable
    {
        yield [50000, 500, 500, 'unlimited'];
        yield [50000, 500, 3, 'by_datatable_subtable'];
        yield [50000, 3, 500, 'by_datatable_toplevel'];
        yield [50000, 3, 3, 'by_datatable_subtable_and_toplevel'];
        yield [50000, 1, 1, 'by_datatable_minimum'];
    }

    public function getRankingLimitTestDataWithRollup(): iterable
    {
        foreach ($this->getRankingLimitTestData() as $testData) {
            yield $testData;
        }

        /*
         * set zero for custom dimension rows to prevent
         * "10 * datatable_archiving_maximum_rows_custom_dimensions"
         * being used as the actual limit in the ranking query
         */
        yield [3, 0, 0, 'by_archiving_query'];
    }

    public function getRankingLimitTestDataExpanded(): iterable
    {
        foreach ($this->getRankingLimitTestData() as $testData) {
            [$rowsRankingQuery, $rowsTableTopLevel, $rowsTableSubTable, $testSuffix] = $testData;

            yield [
                $rowsRankingQuery,
                $rowsTableTopLevel,
                $rowsTableSubTable,
                $testSuffix . '_expanded',
                ['expanded' => 1]
            ];
        }
    }

    public function getRankingLimitTestDataExpandedWithRollup(): iterable
    {
        foreach ($this->getRankingLimitTestDataWithRollup() as $testData) {
            [$rowsRankingQuery, $rowsTableTopLevel, $rowsTableSubTable, $testSuffix] = $testData;

            yield [
                $rowsRankingQuery,
                $rowsTableTopLevel,
                $rowsTableSubTable,
                $testSuffix . '_expanded',
                ['expanded' => 1]
            ];
        }
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    public static function tearDownAfterClass(): void
    {
        self::triggerWithRollupFeatureFlag($enableFlag = false);
        parent::tearDownAfterClass();
    }
}

ApiTest::$fixture = new TrackVisitsWithCustomDimensionsFixture();
