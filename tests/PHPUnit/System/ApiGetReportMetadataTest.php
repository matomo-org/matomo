<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\API\Proxy;
use Piwik\Tests\Fixtures\ThreeGoalsOnePageview;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * This tests the output of the API plugin API
 * It will return metadata about all API reports from all plugins
 * as well as the data itself, pre-processed and ready to be displayed
 *
 * @group Plugins
 * @group ApiGetReportMetadataTest
 */
class ApiGetReportMetadataTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        parent::setUp();

        // From Piwik 1.5, we hide Goals.getConversions and other get* methods via @ignore, but we
        // ensure that they still work. This hack allows the API proxy to let us generate example
        // URLs for the ignored functions
        Proxy::getInstance()->setHideIgnoredFunctions(false);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // reset that value after the test
        Proxy::getInstance()->setHideIgnoredFunctions(true);
    }

    public static function getOutputPrefix()
    {
        return 'apiGetReportMetadata';
    }

    public function getApiForTesting()
    {
        $idSite   = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        return [
            ['API', ['idSite' => $idSite, 'date' => $dateTime]],

            // test w/ hideMetricsDocs=true
            [
                'API.getMetadata',
                [
                    'idSite'                 => $idSite,
                    'date'                   => $dateTime,
                    'apiModule'              => 'Actions',
                    'apiAction'              => 'get',
                    'testSuffix'             => '_hideMetricsDoc',
                    'otherRequestParameters' => ['hideMetricsDoc' => 1],
                ],
            ],
            [
                'API.getProcessedReport',
                [
                    'idSite'                 => $idSite,
                    'date'                   => $dateTime,
                    'apiModule'              => 'Actions',
                    'apiAction'              => 'get',
                    'testSuffix'             => '_hideMetricsDoc',
                    'otherRequestParameters' => ['hideMetricsDoc' => 1],
                ],
            ],

            [
                'API.getProcessedReport',
                [
                    'idSite'                 => $idSite,
                    'date'                   => $dateTime,
                    'apiModule'              => 'UserCountry',
                    'apiAction'              => 'getCountry',
                    'testSuffix'             => '_withExtraProcessedMetrics',
                    'otherRequestParameters' => [
                        'filter_update_columns_when_show_all_goals' => '1',
                        'idGoal' => '0',
                    ],
                ],
            ],

            // Test w/ showRawMetrics=true
            [
                'API.getProcessedReport',
                [
                    'idSite'                 => $idSite,
                    'date'                   => $dateTime,
                    'apiModule'              => 'UserCountry',
                    'apiAction'              => 'getCountry',
                    'testSuffix'             => '_showRawMetrics',
                    'otherRequestParameters' => ['showRawMetrics' => 1],
                ],
            ],

            // Test w/ showRawMetrics=true
            [
                'Actions.getPageTitles',
                [
                    'idSite'     => $idSite,
                    'date'       => $dateTime,
                    'testSuffix' => '_pageTitleZeroString',
                ],
            ],

            // Test w/ no format, should default to format=json
            [
                'Actions.getPageTitles',
                [
                    'idSite'     => $idSite,
                    'date'       => $dateTime,
                    'testSuffix' => '_defaultFormatValue',
                    'format'     => 'asldjkf',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}

ApiGetReportMetadataTest::$fixture = new ThreeGoalsOnePageview();
