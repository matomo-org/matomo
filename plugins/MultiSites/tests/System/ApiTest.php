<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\tests\System;

use Piwik\Plugins\MultiSites\tests\Fixtures\ManySitesWithVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group MultiSites
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var ManySitesWithVisits
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
        return [
            [
                'MultiSites.getAllWithGroups',
                [
                    'period' => 'day',
                    'date' => '2013-01-23',
                    'otherRequestParameters' => [
                        'filter_limit' => 20,
                    ],
                    'testSuffix' => '',
                ],
            ],
            [
                'MultiSites.getAllWithGroups',
                [
                    'period' => 'day',
                    'date' => '2013-01-23',
                    'otherRequestParameters' => [
                        'filter_limit' => 5,
                    ],
                    'testSuffix' => 'limited',
                ],
            ],
            [
                'MultiSites.getAllWithGroups',
                [
                    'period' => 'day',
                    'date' => '2013-01-23',
                    'otherRequestParameters' => [
                        'filter_limit' => 5,
                        'filter_offset' => 4,
                    ],
                    'testSuffix' => 'limitedWithOffset',
                ],
            ],
            [
                'MultiSites.getAllWithGroups',
                [
                    'period' => 'day',
                    'date' => '2013-01-23',
                    'otherRequestParameters' => [
                        'filter_limit' => 5,
                        'pattern' => 'Site 1',
                    ],
                    'testSuffix' => 'limitedWithPattern',
                ],
            ],
            [
                'MultiSites.getAllWithGroups',
                [
                    'period' => 'day',
                    'date' => '2013-01-23,2013-01-25',
                    'otherRequestParameters' => [
                        'filter_limit' => 5,
                    ],
                    'testSuffix' => 'multiplePeriods',
                ],
            ]
        ];
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

ApiTest::$fixture = new ManySitesWithVisits();
