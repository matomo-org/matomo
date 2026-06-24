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
                    'BotTracking.getAIChatbotRequests',
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
                    'BotTracking.getAIChatbotRequests',
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
                    'BotTracking.getAIChatbotRequests',
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
            [
                [
                    'BotTracking.getAIChatbotContentPages',
                ], [
                    'idSite'     => 1,
                    'date'       => '2025-02-03',
                    'periods'    => ['day', 'week'],
                    'testSuffix' => '_content_pages',
                ],
            ],
            [
                [
                    'BotTracking.getAIChatbotContentDocuments',
                ], [
                    'idSite'     => 1,
                    'date'       => '2025-02-03',
                    'periods'    => ['day', 'week'],
                    'testSuffix' => '_content_documents',
                ],
            ],
            [
                [
                    'BotTracking.getAIChatbotBrokenContent',
                ], [
                    'idSite'     => 1,
                    'date'       => '2025-02-03',
                    'periods'    => ['day', 'week'],
                    'testSuffix' => '_broken_content',
                ],
            ],
            [
                [
                    'BotTracking.getAIChatbotHumanFavouredPages',
                ], [
                    'idSite'     => 1,
                    'date'       => '2025-02-03',
                    'periods'    => ['day', 'week'],
                    'testSuffix' => '_human_favoured',
                    'otherRequestParameters' => [
                        // Ensure the low-pop filter is disabled so the fixture's low-volume rows
                        // remain visible in the expected output.
                        'enable_filter_excludelowpop' => 0,
                    ],
                ],
            ],
            [
                [
                    'BotTracking.getAIChatbotAIFavouredPages',
                ], [
                    'idSite'     => 1,
                    'date'       => '2025-02-03',
                    'periods'    => ['day', 'week'],
                    'testSuffix' => '_ai_favoured',
                    'otherRequestParameters' => [
                        'enable_filter_excludelowpop' => 0,
                    ],
                ],
            ],
            // Exercise the UI's effective query: exclude rows below a score of 1 and sort by the
            // score. Guards the regression where the score-based low-population filter emptied the
            // report (the score was recomputed after row deletion / absent when the filter ran).
            [
                [
                    'BotTracking.getAIChatbotHumanFavouredPages',
                ], [
                    'idSite'     => 1,
                    'date'       => '2025-02-03',
                    'periods'    => ['day', 'week'],
                    'testSuffix' => '_human_favoured_filtered',
                    'otherRequestParameters' => [
                        'filter_excludelowpop'       => 'discrepancy_score',
                        'filter_excludelowpop_value' => '1',
                        'filter_sort_column'         => 'discrepancy_score',
                    ],
                ],
            ],
            [
                [
                    'BotTracking.getAIChatbotAIFavouredPages',
                ], [
                    'idSite'     => 1,
                    'date'       => '2025-02-03',
                    'periods'    => ['day', 'week'],
                    'testSuffix' => '_ai_favoured_filtered',
                    'otherRequestParameters' => [
                        'filter_excludelowpop'       => 'discrepancy_score',
                        'filter_excludelowpop_value' => '1',
                        'filter_sort_column'         => 'discrepancy_score',
                    ],
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
