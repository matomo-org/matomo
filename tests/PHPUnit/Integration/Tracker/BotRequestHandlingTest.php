<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Container\Container;
use Piwik\Plugin\RequestProcessors;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker;
use Piwik\Tracker\BotRequestProcessor;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Version;

/**
 * @group Core
 * @group Tracker
 * @group BotRequestHandling
 */
class BotRequestHandlingTest extends IntegrationTestCase
{
    public static $eventsTriggered = [];

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2025-05-05 20:00:00');

        BotRequestHandlingTest::$eventsTriggered = [
            'BotRequestProcessor' => [],
            'RequestProcessor' => [],
        ];
    }

    /**
     * @param string|int|array|null $recMode
     * @dataProvider getTestModeData
     */
    public function testVisitTrackingWithDefaultMode(string $ua, $recMode, array $expectedEvents): void
    {
        $tracker = new Tracker();

        $request = new Request([
            'idsite' => 1,
            'url' => 'https://example.com/test',
            'ua' => $ua,
            'rec' => 1,
            'recMode' => $recMode,
        ]);

        $tracker->trackRequest($request);

        self::assertEquals($expectedEvents, self::$eventsTriggered);
    }

    public function getTestModeData(): iterable
    {
        yield 'Visit tracked in default mode should be processed by RequestProcessor only' => [
            'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_7) AppleWebKit/534.24 (KHTML, like Gecko) RockMelt/0.9.58.494 Chrome/11.0.696.71 Safari/534.24',
            'recMode' => null,
            'expectedEvents' => [
                'BotRequestProcessor' => [],
                'RequestProcessor' => [
                    'manipulateRequest',
                    'processRequestParams',
                ],
            ],
        ];

        yield 'Bot tracked in default mode should be also processed by RequestProcessor only' => [
            'ua' => 'Mozilla/5.0 (compatible; ChatGPT-User/1.0; +https://openai.com)',
            'recMode' => '',
            'expectedEvents' => [
                'BotRequestProcessor' => [],
                'RequestProcessor' => [
                    'manipulateRequest',
                    'processRequestParams',
                ],
            ],
        ];

        yield 'Visit tracked in bot mode should not be processed at all' => [
            'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_7) AppleWebKit/534.24 (KHTML, like Gecko) RockMelt/0.9.58.494 Chrome/11.0.696.71 Safari/534.24',
            'recMode' => 1,
            'expectedEvents' => [
                'BotRequestProcessor' => [],
                'RequestProcessor' => [],
            ],
        ];

        yield 'Bot tracked in bot mode should be processed by BotRequestProcessor only' => [
            'ua' => 'Mozilla/5.0 (compatible; ChatGPT-User/1.0; +https://openai.com)',
            'recMode' => '1',
            'expectedEvents' => [
                'BotRequestProcessor' => [
                    'manipulateRequest',
                    'handleRequest',
                ],
                'RequestProcessor' => version_compare(Version::VERSION, '6.0.0-rc1', '<') ? [
                    'manipulateRequest', // currently still triggered for bc reasons
                ] : [],
            ],
        ];


        yield 'Visit tracked in auto mode should be processed by RequestProcessor only' => [
            'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_7) AppleWebKit/534.24 (KHTML, like Gecko) RockMelt/0.9.58.494 Chrome/11.0.696.71 Safari/534.24',
            'recMode' => 2,
            'expectedEvents' => [
                'BotRequestProcessor' => [],
                'RequestProcessor' => [
                    'manipulateRequest',
                    'processRequestParams',
                ],
            ],
        ];

        yield 'Bot tracked in auto mode should be processed by BotRequestProcessor only' => [
            'ua' => 'Mozilla/5.0 (compatible; ChatGPT-User/1.0; +https://openai.com)',
            'recMode' => '2',
            'expectedEvents' => [
                'BotRequestProcessor' => [
                    'manipulateRequest',
                    'handleRequest',
                ],
                'RequestProcessor' => version_compare(Version::VERSION, '6.0.0-rc1', '<') ? [
                    'manipulateRequest', // currently still triggered for bc reasons
                ] : [],
            ],
        ];

        yield 'Visit tracked with invalid integer mode should not be processed' => [
            'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_7) AppleWebKit/534.24 (KHTML, like Gecko) RockMelt/0.9.58.494 Chrome/11.0.696.71 Safari/534.24',
            'recMode' => '5',
            'expectedEvents' => [
                'BotRequestProcessor' => [],
                'RequestProcessor' => [],
            ],
        ];

        yield 'Visit tracked with invalid array mode should not be processed' => [
            'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_7) AppleWebKit/534.24 (KHTML, like Gecko) RockMelt/0.9.58.494 Chrome/11.0.696.71 Safari/534.24',
            'recMode' => ['bla'],
            'expectedEvents' => [
                'BotRequestProcessor' => [],
                'RequestProcessor' => [],
            ],
        ];
    }

    public function provideContainerConfig()
    {
        return [
            RequestProcessors::class => function (Container $c) {
                return new class extends RequestProcessors {
                    public function getBotRequestProcessors(): array
                    {
                        return [
                            new class extends BotRequestProcessor {
                                public function manipulateRequest(Request $request): void
                                {
                                    BotRequestHandlingTest::$eventsTriggered['BotRequestProcessor'][] = 'manipulateRequest';
                                }

                                public function handleRequest(Request $request): bool
                                {
                                    BotRequestHandlingTest::$eventsTriggered['BotRequestProcessor'][] = 'handleRequest';

                                    return false;
                                }
                            },
                        ];
                    }

                    public function getRequestProcessors(): array
                    {
                        return [
                            new class extends RequestProcessor {
                                public function manipulateRequest(Request $request)
                                {
                                    BotRequestHandlingTest::$eventsTriggered['RequestProcessor'][] = 'manipulateRequest';
                                }

                                public function processRequestParams(VisitProperties $visitProperties, Request $request)
                                {
                                    BotRequestHandlingTest::$eventsTriggered['RequestProcessor'][] = 'processRequestParams';

                                    return true; // abort to avoid further processing
                                }
                            },
                        ];
                    }
                };
            },
        ];
    }
}
