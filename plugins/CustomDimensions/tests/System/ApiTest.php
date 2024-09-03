<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\System;

use Piwik\Context;
use Piwik\Plugins\CustomDimensions\tests\Fixtures\TrackVisitsWithCustomDimensionsFixture;
use Piwik\ReportRenderer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

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

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
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

        $apiToTest[] = array(array('CustomDimensions.getAvailableExtractionDimensions'),
            array(
                'idSite'  => 1,
                'date'    => self::$fixture->dateTime,
                'periods' => array('day')
            )
        );

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

        $apiToTest[] = array(
            array('API.getWidgetMetadata'),
            array(
                'idSite'  => 1,
                'date'    => self::$fixture->dateTime,
                'periods' => array('day')
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

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

ApiTest::$fixture = new TrackVisitsWithCustomDimensionsFixture();
