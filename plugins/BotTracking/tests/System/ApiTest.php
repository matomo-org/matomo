<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\System;

use Piwik\Plugins\BotTracking\tests\Fixtures\BotTraffic;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group BotTracking
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var BotTraffic
     */
    public static $fixture;

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
                [
                    'BotTracking.get',
                ], [
                    'idSite'                 => 1,
                    'date'                   => '2025-02-03',
                    'periods'                => ['day', 'week'],
                ],
            ],
            [
                [
                    'BotTracking.getAIAssistantRequests',
                ],
                [
                    'idSite'                 => 1,
                    'date'                   => '2025-02-03',
                    'periods'                => ['day', 'week'],
                    'otherRequestParameters' => [
                        'expanded'           => 1,
                        'secondaryDimension' => 'pages',
                    ],
                    'testSuffix'             => '_pages',
                ],
            ],
            [
                [
                    'BotTracking.getAIAssistantRequests',
                ],
                [
                    'idSite'                 => 1,
                    'date'                   => '2025-02-03',
                    'periods'                => ['day', 'week'],
                    'otherRequestParameters' => [
                        'flat' => 1,
                    ],
                    'testSuffix'             => '_flat',
                ],
            ],
            [
                [
                    'BotTracking.getAIAssistantRequests',
                ], [
                    'idSite'                 => 1,
                    'date'                   => '2025-02-03',
                    'periods'                => ['day', 'week'],
                    'otherRequestParameters' => [
                        'expanded'           => 1,
                        'secondaryDimension' => 'documents',
                    ],
                    'testSuffix'             => '_documents',
                ],
            ],
        ];
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return __DIR__;
    }
}

ApiTest::$fixture = new BotTraffic();
