<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\System;

use Piwik\Config;
use Piwik\Plugins\API\API;
use Piwik\Plugins\Live\SystemSettings;
use Piwik\Plugins\Live\tests\Fixtures\ManyVisitsOfSameVisitor;
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
        $api = [
            'Live.getVisitorProfile',
        ];

        $apiToTest   = [];
        $apiToTest[] = [
            $api,
            [
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => ['day'],
                'testSuffix' => '',
            ],
        ];
        $apiToTest[] = [
            $api,
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
                        urlencode("idSite=1&date=".self::$fixture->dateTime."&period=day&method=Live.getLastVisitsDetails"),
                        urlencode("idSite=1&date=".self::$fixture->dateTime."&period=day&method=API.getSuggestedValuesForSegment&segmentName=pageTitle"),
                        urlencode("idSite=1&date=".self::$fixture->dateTime."&period=day&method=Live.getVisitorProfile"),
                    ]
                ],
            ]
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

ApiTest::$fixture = new ManyVisitsOfSameVisitor();
