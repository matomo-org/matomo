<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\System;

use Piwik\Plugins\DebugView\tests\Fixtures\FewDebugRequests;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group DebugView
 * @group DebugViewSystemApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var FewDebugRequests
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        // fields that legitimately differ between runs: the fixture tracks
        // relative to "now", and the tracker adds random/clock-based params
        $volatileFields = [
            'timestamp', 'timePretty', 'serverTime', 'serverTimeReceived',
            'cdt', 'r', '_idts', '_viewts', '_ects', 'h', 'm', 's', 'pv_id',
            'userAgent', 'uadata', 'send_image',
        ];

        $apiToTest = [];

        $apiToTest[] = [['DebugView.getRecentHits'],
            [
                'idSite'                 => 1,
                'date'                   => 'today',
                'periods'                => ['day'],
                'otherRequestParameters' => ['lastMinutes' => 60],
                'xmlFieldsToRemove'      => $volatileFields,
                'testSuffix'             => '',
            ],
        ];

        // a different site returns only its own hits
        $apiToTest[] = [['DebugView.getRecentHits'],
            [
                'idSite'                 => 2,
                'date'                   => 'today',
                'periods'                => ['day'],
                'otherRequestParameters' => ['lastMinutes' => 60],
                'xmlFieldsToRemove'      => $volatileFields,
                'testSuffix'             => '_site2',
            ],
        ];

        // a site without any captured requests returns an empty envelope
        $apiToTest[] = [['DebugView.getRecentHits'],
            [
                'idSite'                 => 3,
                'date'                   => 'today',
                'periods'                => ['day'],
                'otherRequestParameters' => ['lastMinutes' => 60],
                'xmlFieldsToRemove'      => $volatileFields,
                'testSuffix'             => '_siteWithNoData',
            ],
        ];

        // an up-to-date cursor returns an empty, stable envelope
        $apiToTest[] = [['DebugView.getRecentHits'],
            [
                'idSite'                 => 1,
                'date'                   => 'today',
                'periods'                => ['day'],
                'otherRequestParameters' => ['lastMinutes' => 60, 'minId' => 999999],
                'xmlFieldsToRemove'      => $volatileFields,
                'testSuffix'             => '_futureMinId',
            ],
        ];

        return $apiToTest;
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

ApiTest::$fixture = new FewDebugRequests();
