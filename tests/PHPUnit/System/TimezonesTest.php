<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Date;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\VisitsInDifferentTimezones;

/**
 * Test reports using visits for a site with a non-UTC timezone.
 *
 * @group TimezonesTest
 * @group Core
 */
class TimezonesTest extends SystemTestCase
{
    /**
     * @var VisitsInDifferentTimezones
     */
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        parent::setUp();

        self::$fixture->setMockNow();
    }

    public function getApiForTesting()
    {
        self::$fixture->setMockNow();

        $testcases = [];

        foreach (['all' => '', '1' => '_ast', '2' => '_utc'] as $idSite => $appendix) {
            if ($idSite !== 'all') {
                // Note: Querying idSites=all will always use default timezone (UTC), so this is not relevant for timezone testing
                $testcases[] = [
                    'Live.getLastVisitsDetails',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => 'yesterday',
                        'period'                 => 'day',
                        'otherRequestParameters' => [
                            'filter_limit'       => 100,
                            'doNotFetchActions'  => 1,
                        ],
                        'testSuffix'             => '_yesterday' . $appendix,
                    ],
                ];
                $testcases[] = [
                    'Live.getLastVisitsDetails',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => 'today',
                        'period'                 => 'day',
                        'otherRequestParameters' => [
                            'filter_limit'       => 100,
                            'doNotFetchActions'  => 1,
                        ],
                        'testSuffix'             => '_today' . $appendix,
                    ],
                ];
                $testcases[] = [
                    'Live.getLastVisitsDetails',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => Date::yesterday() . ',' . Date::today(),
                        'period'                 => 'range',
                        'otherRequestParameters' => [
                            'filter_limit'       => 100,
                            'doNotFetchActions'  => 1,
                        ],
                        'testSuffix'             => '_range' . $appendix,
                    ],
                ];
                $testcases[] = [
                    'Live.getLastVisitsDetails',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => Date::yesterday() . ',' . Date::today(),
                        'period'                 => 'range',
                        'otherRequestParameters' => [
                            'filter_limit'       => 100,
                            'doNotFetchActions'  => 1,
                        ],
                        'segment'                => 'pageUrl=@example.org;pageUrl=@index',
                        'testSuffix'             => '_range' . $appendix, // using same suffix as results are the same
                    ],
                ];
                $testcases[] = [
                    'Live.getLastVisitsDetails',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => Date::yesterday() . ',' . Date::today(),
                        'period'                 => 'range',
                        'otherRequestParameters' => [
                            'filter_limit'       => 100,
                            'doNotFetchActions'  => 1,
                        ],
                        'segment'                => 'pageUrl=@example.org;pageUrl!@index',
                        'testSuffix'             => '_range_nomatch' . $appendix, // this segment should match nothing
                    ],
                ];
                // Testing Transitions explicitly, as it builds the sql query itself, and doesn't use archiving
                $testcases[] = [
                    'Transitions.getTransitionsForAction',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => 'today',
                        'period'                 => 'day',
                        'otherRequestParameters' => [
                            'actionName' => 'http://example.org/index.htm',
                            'actionType' => 'url',
                        ],
                        'segment'                => 'pageUrl=@example.org;pageUrl!@index',
                        'testSuffix'             => '_today_nomatch' . $appendix, // this segment should match nothing
                        'xmlFieldsToRemove'      => [
                            'date'
                        ]
                    ]
                ];
                $testcases[] = [
                    'Transitions.getTransitionsForAction',
                    [
                        'idSite'                 => $idSite,
                        'date'                   => 'today',
                        'period'                 => 'day',
                        'otherRequestParameters' => [
                            'actionName' => 'http://example.org/index.htm',
                            'actionType' => 'url',
                        ],
                        'testSuffix'             => '_today' . $appendix, // this segment should match nothing
                        'xmlFieldsToRemove'      => [
                            'date'
                        ]
                    ]
                ];
            }
            $testcases[] = [
                'VisitsSummary.get',
                [
                    'idSite'     => $idSite,
                    'date'       => 'yesterday',
                    'period'     => 'day',
                    'testSuffix' => '_yesterday' . $appendix,
                ],
            ];
            $testcases[] = [
                'VisitsSummary.get',
                [
                    'idSite'     => $idSite,
                    'date'       => 'yesterday',
                    'period'     => 'day',
                    'segment'    => 'pageUrl=@example.org;pageUrl=@index',
                    'testSuffix' => '_yesterday' . $appendix, // using same suffix as results are the same
                ],
            ];
            $testcases[] = [
                'VisitsSummary.get',
                [
                    'idSite'     => $idSite,
                    'date'       => 'yesterday',
                    'period'     => 'day',
                    'segment'    => 'pageUrl=@example.org;pageUrl!@index',
                    'testSuffix' => '_yesterday_nomatch' . $appendix, // this segment should match nothing
                ],
            ];
            $testcases[] = [
                'VisitsSummary.get',
                [
                    'idSite'     => $idSite,
                    'date'       => 'today',
                    'period'     => 'day',
                    'testSuffix' => '_today' . $appendix,
                ],
            ];
            $testcases[] = [
                'VisitsSummary.get',
                [
                    'idSite'     => $idSite,
                    'date'       => 'today',
                    'period'     => 'day',
                    'segment'    => 'pageUrl=@example.org;pageUrl=@index',
                    'testSuffix' => '_today' . $appendix, // using same suffix as results are the same
                ],
            ];
            $testcases[] = [
                'VisitsSummary.get',
                [
                    'idSite'     => $idSite,
                    'date'       => 'today',
                    'period'     => 'day',
                    'segment'    => 'pageUrl=@example.org;pageUrl!@index',
                    'testSuffix' => '_today_nomatch' . $appendix, // this segment should match nothing
                ],
            ];
        }

        return $testcases;
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}

TimezonesTest::$fixture = new VisitsInDifferentTimezones();
