<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Date;
use Piwik\Tests\Fixtures\VisitsOverSeveralDays;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Test RSS export
 *
 * @group RssExportTest
 * @group Core
 */
class RssExportTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $idSite   = self::$fixture->idSite;

        $apiToCall = ['VisitsSummary.get', 'Actions.getPageUrls'];

        return [
            [
                $apiToCall,
                [
                    'idSite' => $idSite,
                    'period' => 'day',
                    'date'   => '2010-12-14,2010-12-16',
                    'format' => 'rss',
                ],
            ],
            [
                'Actions.getPageUrl',
                [
                    'idSite'                 => $idSite,
                    'period'                 => ['day', 'week'],
                    'date'                   => '2010-12-14,2010-12-21',
                    'format'                 => 'rss',
                    'otherRequestParameters' => [
                        'pageUrl' => '/homepage',
                    ],
                ],
            ],
            [
                'Actions.getPageUrl',
                [
                    'idSite'                 => $idSite,
                    'period'                 => 'day',
                    'format'                 => 'rss',
                    'date'                   => '2010-12-14,2010-12-21',
                    'otherRequestParameters' => [
                        'pageUrl' => '/sub1/sub2/sub3/index',
                    ],
                    'testSuffix'             => '_subdir'
                ],
            ],
            [
                'Actions.getPageUrl',
                [
                    'idSite'                 => $idSite,
                    'period'                 => 'day',
                    'format'                 => 'rss',
                    'otherRequestParameters' => [
                        'pageUrl' => '/page',
                        'date'                   => 'last7',
                    ],
                    'testSuffix'             => '_empty'
                ],
            ],
            [
                'Actions.getPageUrl',
                [
                    'idSite'                 => $idSite,
                    'period'                 => 'week',
                    'format'                 => 'rss',
                    'otherRequestParameters' => [
                        'pageUrl' => '/page',
                        'date'                   => 'last3',
                    ],
                    'testSuffix'             => '_empty'
                ],
            ],
        ];
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        Date::$now = Date::factory('2020-10-20 20:10:20')->getTimestamp();
        $this->runApiTests($api, $params);
    }

    public static function getOutputPrefix()
    {
        return 'rssExport';
    }
}

RssExportTest::$fixture = new VisitsOverSeveralDays();
