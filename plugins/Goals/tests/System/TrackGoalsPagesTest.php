<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\SomePageGoalVisitsWithConversions;

/**
 * Tests API methods with goals that do and don't allow multiple
 * conversions per visit.
 *
 * @group TrackGoalsPagesTest
 * @group TrackGoalsPages
 * @group Plugins
 */
class TrackGoalsPagesTest extends SystemTestCase
{
    /**
     * @var SomePageGoalVisitsWithConversions
     */
    public static $fixture = null;

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
            ['Actions.getPageUrls',        ['idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime,
                                            'idGoal' => 1, 'period' => 'week']],
            ['Actions.getPageTitles',      ['idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime,
                                            'idGoal' => 1, 'period' => 'week']],
            ['Actions.getEntryPageUrls',   ['idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime,
                                            'idGoal' => 1, 'period' => 'day', 'otherRequestParameters' =>
                                                              ['filter_update_columns_when_show_all_goals' => 1]]],
            ['Actions.getEntryPageTitles', ['idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime,
                                            'idGoal' => 1, 'period' => 'day', 'otherRequestParameters' =>
                                                              ['filter_update_columns_when_show_all_goals' => 1]]],

            ['API.getProcessedReport', [
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'period' => 'day',
                'testSuffix' => 'showGoalsMetricsSingleGoal',
                'otherRequestParameters' => [
                    'filter_update_columns_when_show_all_goals' => '1',
                    'filter_show_goal_columns_process_goals' => '1',
                    'apiModule' => 'Actions',
                    'apiAction' => 'getEntryPageTitles',
                ],
            ]],
            ['API.getProcessedReport', [
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'period' => 'day',
                'testSuffix' => 'showGoalsMetricsAllGoals',
                'otherRequestParameters' => [
                    'filter_update_columns_when_show_all_goals' => '1',
                    'apiModule' => 'Actions',
                    'apiAction' => 'getEntryPageTitles',
                ],
            ]],

            ['API.getProcessedReport', [
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'period' => 'day',
                'testSuffix' => 'showGoalsMetricsPageReport',
                'otherRequestParameters' => [
                    'filter_update_columns_when_show_all_goals' => '1',
                    'filter_show_goal_columns_process_goals' => '1',
                    'apiModule' => 'Actions',
                    'apiAction' => 'getPageTitles',
                ],
            ]],
        ];
    }

    /**
     * Check that the log_conversion.pageviews_before column was correctly calculated
     *
     * @dataProvider getConversionPagesBeforeExpected
     */
    public function test_conversionPagesBeforeValues($id, $expected)
    {
        $actual = Db::get()->fetchOne('SELECT pageviews_before FROM ' . Common::prefixTable('log_conversion') .
                                      ' WHERE idlink_va = ?', [$id]);

        $this->assertEquals($expected, $actual);
    }

    public static function getConversionPagesBeforeExpected()
    {
        return [
            ['id' => 5, 'expected' => 4],
            ['id' => 9, 'expected' => 3],
            ['id' => 14, 'expected' => 2],
            ['id' => 18, 'expected' => 5],
            ['id' => 23, 'expected' => 4],
            ['id' => 27, 'expected' => 7],
            ['id' => 29, 'expected' => 1],
            ['id' => 33, 'expected' => 3]
        ];
    }

    public static function getOutputPrefix()
    {
        return 'trackGoals_pages';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

TrackGoalsPagesTest::$fixture = new SomePageGoalVisitsWithConversions();
