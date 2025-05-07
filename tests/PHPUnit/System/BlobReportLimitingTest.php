<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\ManyVisitsWithMockLocationProvider;

/**
 * Test Piwik's report limiting code. Make sure the datatable_archiving_maximum_rows_...
 * config options limit the size of certain reports when archiving.
 *
 * @group Core
 * @group BlobReportLimitingTest
 */
class BlobReportLimitingTest extends SystemTestCase
{
    /**
     * @var ManyVisitsWithMockLocationProvider
     */
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        Cache::getTransientCache()->flushAll();
        parent::setUp();
    }

    public function getApiForTesting()
    {
        $apiToCall = [
            'Actions.getPageUrls',
            'Actions.getPageTitles',
            'Actions.getDownloads',
            'Actions.getOutlinks',
            'Actions.getSiteSearchKeywords',
            'CustomVariables.getCustomVariables',
            'Referrers.getReferrerType',
            'Referrers.getKeywords',
            'Referrers.getSearchEngines',
            'Referrers.getWebsites',
            'Referrers.getAll',
            /* TODO 'Referrers.getCampaigns', */
            'Resolution.getResolution',
            'Resolution.getConfiguration',
            'DevicesDetection.getOsVersions',
            'DevicesDetection.getBrowserVersions',
            'UserCountry.getRegion',
            'UserCountry.getCity',
            'UserId.getUsers',
            'Events',
            'Contents',
        ];

        $ecommerceApi = ['Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory'];
        return [
            [
                $apiToCall,
                [
                    'idSite'  => self::$fixture->idSite,
                    'date'    => self::$fixture->dateTime,
                    'periods' => 'day',
                ],
            ],

            [
                $ecommerceApi,
                [
                    'idSite'  => self::$fixture->idSite,
                    'date'    => self::$fixture->nextDay,
                    'periods' => 'day',
                ],
            ],

            [
                'CustomDimensions.getCustomDimension',
                [
                    'idSite'                 => 1,
                    'date'                   => self::$fixture->dateTime,
                    'periods'                => ['day'],
                    'otherRequestParameters' => [
                        'idDimension' => self::$fixture->customDimensionId,
                    ],
                    'testSuffix'             => "dimension_" . self::$fixture->customDimensionId,
                ],
            ],
            [
                'CustomDimensions.getCustomDimension',
                [
                    'idSite'                 => 1,
                    'date'                   => self::$fixture->dateTime,
                    'periods'                => ['day'],
                    'otherRequestParameters' => [
                        'idDimension' => self::$fixture->actionCustomDimensionId,
                    ],
                    'testSuffix'             => "dimension_" . self::$fixture->actionCustomDimensionId,
                    // ranking query doesn't guarantee order if the main metric values are the same so the label/segment can randomly change.
                    // in this test, we only care to check that the result is being limited/aggregated correctly, so we can remove these
                    // when comparing.
                    'xmlFieldsToRemove'      => [
                        'label',
                        'segment',
                        'url',
                        'exit_nb_visits',
                        'exit_rate',
                        'bounce_count',
                        'bounce_rate',
                    ],
                ],
            ],

            [
                'Events',
                [
                    'idSite'     => 1,
                    'date'       => '2015-02-03',
                    'period'     => ['day'],
                    'testSuffix' => 'withNegOneLabel_',
                ],
            ],
        ];
    }

    public function getRankingQueryDisabledApiForTesting()
    {
        $idSite   = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        return [
            [
                'Actions.getPageUrls',
                [
                    'idSite'  => $idSite,
                    'date'    => $dateTime,
                    'periods' => ['day'],
                ],
            ],

            // TODO these system tests need to be moved to Provider plugin
            /*
            array('Provider.getProvider', array('idSite'  => $idSite,
                                                'date'    => $dateTime,
                                                'periods' => array('month'))),

            array('Provider.getProvider', array('idSite'     => $idSite,
                                                'date'       => $dateTime,
                                                'periods'    => array('month'),
                                                'segment'    => 'provider==comcast.net',
                                                'testSuffix' => '_segment_provider')),
            */

            // test getDownloads w/ period=range & flat=1
            [
                'Actions.getDownloads',
                [
                    'idSite'                 => $idSite,
                    'date'                   => '2010-01-03,2010-01-05',
                    'periods'                => 'range',
                    'testSuffix'             => '_rangeFlat',
                    'otherRequestParameters' => [
                        'flat'     => 1,
                        'expanded' => 0,
                    ],
                ],
            ],

            [
                'Insights.getInsightsOverview',
                [
                    'idSite' => 1,
                    'date'   => '2015-03-04',
                    'period' => ['day'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        self::setUpConfigOptions();

        // also perform some tests for month period, to ensure limiting of aggregated archives is correct
        if (empty($params['testSuffix']) && $params['periods'] === 'day' && $params['date'] === self::$fixture->dateTime) {
            $params['periods'] = ['day', 'month'];
        }

        $this->runApiTests($api, $params);
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApiWithFlattening($apiToCall, $params)
    {
        self::setUpConfigOptions();

        if (empty($params['testSuffix'])) {
            $params['testSuffix'] = '';
        }
        $params['testSuffix'] .= '_flattened';
        if (empty($params['otherRequestParameters'])) {
            $params['otherRequestParameters'] = [];
        }
        $params['otherRequestParameters']['flat'] = '1';

        $this->runApiTests($apiToCall, $params);
    }

    public function testApiWithRankingQuery()
    {
        self::setUpConfigOptions();

        // custom setup
        self::deleteArchiveTables();
        Config::getInstance()->General['archiving_ranking_query_row_limit'] = 3;
        ArchivingHelper::reloadConfig();

        foreach ($this->getApiForTesting() as $pair) {
            [$apiToCall, $params] = $pair;

            if (empty($params['testSuffix'])) {
                $params['testSuffix'] = '';
            }
            $params['testSuffix'] .= '_rankingQuery';

            $this->runApiTests($apiToCall, $params);
        }
    }

    public function testApiWithRankingQueryDisabled()
    {
        self::deleteArchiveTables();
        $generalConfig                                                                =& Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_referrers']                  = 500;
        $generalConfig['datatable_archiving_maximum_rows_subtable_referrers']         = 500;
        $generalConfig['datatable_archiving_maximum_rows_actions']                    = 500;
        $generalConfig['datatable_archiving_maximum_rows_subtable_actions']           = 500;
        $generalConfig['datatable_archiving_maximum_rows_standard']                   = 500;
        $generalConfig['datatable_archiving_maximum_rows_custom_dimensions']          = 500;
        $generalConfig['datatable_archiving_maximum_rows_subtable_custom_dimensions'] = 500;
        $generalConfig['archiving_ranking_query_row_limit']                           = 0;
        $generalConfig['datatable_archiving_maximum_rows_site_search']                = 500;
        $generalConfig['datatable_archiving_maximum_rows_userid_users']               = 500;

        foreach ($this->getRankingQueryDisabledApiForTesting() as $pair) {
            [$apiToCall, $params] = $pair;

            if (empty($params['testSuffix'])) {
                $params['testSuffix'] = '';
            }
            $params['testSuffix'] .= '_rankingQueryDisabled';

            $this->runApiTests($apiToCall, $params);
        }
    }

    public static function getOutputPrefix()
    {
        return 'reportLimiting';
    }

    protected static function setUpConfigOptions()
    {
        $generalConfig                                                                =& Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_referers']                   = 3;
        $generalConfig['datatable_archiving_maximum_rows_subtable_referers']          = 2;
        $generalConfig['datatable_archiving_maximum_rows_actions']                    = 4;
        $generalConfig['datatable_archiving_maximum_rows_custom_dimensions']          = 3;
        $generalConfig['datatable_archiving_maximum_rows_subtable_custom_dimensions'] = 2;
        $generalConfig['datatable_archiving_maximum_rows_subtable_actions']           = 2;
        $generalConfig['datatable_archiving_maximum_rows_standard']                   = 3;
        $generalConfig['datatable_archiving_maximum_rows_userid_users']               = 3;
        $generalConfig['datatable_archiving_maximum_rows_events']                     = 3;
        $generalConfig['datatable_archiving_maximum_rows_subtable_events']            = 2;
        $generalConfig['archiving_ranking_query_row_limit']                           = 50000;
        // Should be more than the datatable_archiving_maximum_rows_actions as code will take the max of these two
        $generalConfig['datatable_archiving_maximum_rows_site_search'] = 5;
    }
}

BlobReportLimitingTest::$fixture = new ManyVisitsWithMockLocationProvider();
BlobReportLimitingTest::$fixture->trackVisitsForDaysInPast = 2;
