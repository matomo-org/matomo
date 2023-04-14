<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\System;

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
