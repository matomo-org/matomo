<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\System;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Plugins\API\API;
use Piwik\Plugins\Live\MeasurableSettings;
use Piwik\Plugins\Live\SystemSettings;
use Piwik\Plugins\Live\tests\Fixtures\ManyVisitsOfSameVisitor;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Live
 * @group ApiTest
 * @group Api
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var ManyVisitsOfSameVisitor
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
        $apiToTest   = [];
        $apiToTest[] = [
            ['Live.getVisitorProfile'],
            [
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => ['day'],
                'testSuffix' => '',
            ],
        ];
        $apiToTest[] = [
            ['Live.getVisitorProfile'],
            [
                'idSite'                 => 1,
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => ['limitVisits' => 20],
                'testSuffix'             => 'higherLimit',
            ],
        ];

        $apiToTest[] = [
            ['Live.getLastVisitsDetails'],
            [
                'idSite'                 => 'all',
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => ['limitVisits' => 20],
                'testSuffix'             => 'allSites',
            ],
        ];

        $apiToTest[] = [
            ['Live.getLastVisitsDetails'],
            [
                'idSite'                 => '1,2',
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => ['limitVisits' => 40],
                'testSuffix'             => 'multiSites',
            ],
        ];

        $apiToTest[] = [
            ['Live.getLastVisitsDetails'],
            [
                'idSite'                 => '1',
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => [
                    'segment'      => 'pageTitle=@title',
                    'filter_limit' => 2,
                ],
                'testSuffix'             => 'actionSegment',
            ],
        ];

        $apiToTest[] = [
            ['Live.getLastVisitsDetails'],
            [
                'idSite'                 => '1',
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => [
                    'filter_limit' => -1,
                ],
                'testSuffix'             => 'filterLimitDashOne',
            ],
        ];

        $apiToTest[] = [
            ['Live.getLastVisitsDetails'],
            [
                'idSite'                 => '1',
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => [
                    'filter_limit' => 1,
                ],
                'testSuffix'             => 'filterLimitOne',
            ],
        ];

        $apiToTest[] = [
            ['Live.getLastVisitsDetails'],
            [
                'idSite'                 => 1,
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => ['hideColumns' => 'pageTitle,referrerName,pluginIcon'],
                'testSuffix'             => 'hideColumns',
            ],
        ];
        $apiToTest[] = [
            ['Live.getLastVisitsDetails'],
            [
                'idSite'                 => 1,
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => [
                    'hideColumns'            => 'pageTitle,referrerName,pluginIcon',
                    'hideColumnsRecursively' => 0,
                ],
                'testSuffix'             => 'hideColumnsNonRecursive',
            ],
        ];
        $apiToTest[] = [
            ['Live.getMostRecentVisitsDateTime'],
            [
                'idSite'                 => 1,
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day', 'week'],
            ],
        ];

        return $apiToTest;
    }

    public function testApiWithLowerMaxVisitsLimit()
    {
        Config::getInstance()->General['live_visitor_profile_max_visits_to_aggregate'] = 20;

        $this->runApiTests('Live.getVisitorProfile', [
            'idSite'     => 1,
            'date'       => self::$fixture->dateTime,
            'periods'    => ['day'],
            'testSuffix' => 'maxVisitLimit',
        ]);
    }

    public function testApiWithHttpsHost()
    {
        \Piwik\Plugins\SitesManager\API::getInstance()->updateSite(1, 'Piwik test', ['http://piwik.net', 'https://example.org', 'http://example.org']);
        Cache::getTransientCache()->flushAll();

        $this->runApiTests('Live.getLastVisitsDetails', [
            'idSite'     => 1,
            'date'       => self::$fixture->dateTime,
            'periods'    => ['day'],
            'testSuffix' => 'httpshost',
        ]);
    }


    /**
     * @dataProvider getApiForTestingDisabledFeatures
     */
    public function testSuggestSegmentAPIWithDisabledFeatures($api, $params)
    {
        $settings = new SystemSettings();
        $settings->disableVisitorLog->setValue(true);
        $settings->disableVisitorProfile->setValue(true);
        $settings->save();

        $date                      = mktime(0, 0, 0, 1, 1, 2010);
        $lookBack                  = ceil((time() - $date) / 86400);
        API::$_autoSuggestLookBack = $lookBack;

        $params['testSuffix'] = 'disabledFeatures';

        $this->runApiTests($api, $params);
    }

    public function getApiForTestingDisabledFeatures()
    {
        $apiToTest   = [];
        $apiToTest[] = [
            ['API.getSuggestedValuesForSegment'],
            [
                'idSite'                 => 1,
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
                'otherRequestParameters' => [
                    'segmentName' => 'pageTitle',
                ],
            ]
        ];
        $apiToTest[] = [
            ['Live.getLastVisitsDetails', 'Live.getVisitorProfile'],
            [
                'idSite'                 => 1,
                'date'                   => self::$fixture->dateTime,
                'periods'                => ['day'],
            ]
        ];
        $apiToTest[] = [
            ['API.getBulkRequest'],
            [
                'format' => 'xml',
                'otherRequestParameters' => [
                    'urls' => [
                        urlencode("idSite=1&date=" . self::$fixture->dateTime . "&period=day&method=Live.getLastVisitsDetails"),
                        urlencode("idSite=1&date=" . self::$fixture->dateTime . "&period=day&method=API.getSuggestedValuesForSegment&segmentName=pageTitle"),
                        urlencode("idSite=1&date=" . self::$fixture->dateTime . "&period=day&method=Live.getVisitorProfile"),
                    ]
                ],
            ]
        ];

        return $apiToTest;
    }

    public function testVisitorIdSegmentWithDisabledProfileForSite()
    {
        $settings = new SystemSettings();
        $settings->disableVisitorLog->setValue(false);
        $settings->disableVisitorProfile->setValue(false);
        $settings->save();

        $settings = new MeasurableSettings(1);
        $settings->disableVisitorLog->setValue(false);
        $settings->disableVisitorProfile->setValue(true);
        $settings->save();

        $date                      = mktime(0, 0, 0, 1, 1, 2010);
        $lookBack                  = ceil((time() - $date) / 86400);
        API::$_autoSuggestLookBack = $lookBack;

        Fixture::clearInMemoryCaches();

        $this->runApiTests('API.getSuggestedValuesForSegment', [
            'idSite'                 => 1,
            'date'                   => self::$fixture->dateTime,
            'periods'                => ['day'],
            'otherRequestParameters' => [
                'segmentName' => 'visitorId',
            ],
            'testSuffix' => 'disabledProfile'
        ]);

        $this->runApiTests('API.getSuggestedValuesForSegment', [
            'idSite'                 => 2,
            'date'                   => self::$fixture->dateTime,
            'periods'                => ['day'],
            'otherRequestParameters' => [
                'segmentName' => 'visitorId',
            ],
            'testSuffix' => 'disabledProfile2'
        ]);

        // user id segment should be disabled if visitor profile isn't available
        $this->runApiTests('VisitsSummary.get', [
            'idSite'     => 1,
            'date'       => self::$fixture->dateTime,
            'periods'    => ['day'],
            'segment'    => 'userId==' . urlencode('new-email@example.com'),
            'testSuffix' => 'disabledProfile',
        ]);
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

ApiTest::$fixture = new ManyVisitsOfSameVisitor();
