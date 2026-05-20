<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\System;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
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
    public function testConversionPagesBeforeValues($id, $expected)
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
            ['id' => 33, 'expected' => 3],
        ];
    }

    /**
     * Regression test for DEV-13925.
     *
     * Subtable page rows in Actions.getPageUrls report goal metrics, but the
     * nb_conversions_page_rate column stays at 0 because the post-processing
     * filter never recurses into subtables. flat=1 surfaces those subtable
     * rows at the top level, which makes the bug user-visible.
     */
    public function testPageRateIsCalculatedForFlattenedSubtableRows()
    {
        /** @var DataTable $urls */
        $urls = Request::processRequest('Actions.getPageUrls', [
            'idSite'        => self::$fixture->idSite,
            'period'        => 'week',
            'date'          => self::$fixture->dateTime,
            'idGoal'        => 1,
            'flat'          => '1',
            'filter_limit'  => -1,
        ]);

        $rowsByLabel = [];
        foreach ($urls->getRows() as $row) {
            $rowsByLabel[(string) $row->getColumn('label')] = $row;
        }

        // Goal 1 has 5 total conversions across the week (visits 1, 2, 4, 5, 6).
        // /page_A/index.html: 5 unique => 5 / 5 = 1
        // /page_A/Z:          2 unique => 2 / 5 = 0.4
        // /page_A/X:          1 unique => 1 / 5 = 0.2
        // Without the fix all three subtable rates are 0.
        $expected = [
            '/page_A/index.html' => ['uniq' => 5, 'rate' => 1.0],
            '/page_A/Z'          => ['uniq' => 2, 'rate' => 0.4],
            '/page_A/X'          => ['uniq' => 1, 'rate' => 0.2],
        ];

        foreach ($expected as $label => $expectedRow) {
            $row = $rowsByLabel[$label] ?? null;
            $this->assertNotNull(
                $row,
                "Row '$label' missing from flattened report. Got labels: " . implode(', ', array_keys($rowsByLabel))
            );

            $goals = $row->getColumn('goals');
            $this->assertIsArray($goals, "'goals' column not an array on $label");
            $this->assertArrayHasKey('idgoal=1', $goals, "Goal 1 data missing on $label");

            $this->assertSame(
                $expectedRow['uniq'],
                (int) ($goals['idgoal=1']['nb_conversions_page_uniq'] ?? 0),
                "Unexpected nb_conversions_page_uniq for $label"
            );

            $actualRate = round((float) ($goals['idgoal=1']['nb_conversions_page_rate'] ?? 0), 3);
            $this->assertSame(
                $expectedRow['rate'],
                $actualRate,
                "Expected nb_conversions_page_rate $expectedRow[rate] for $label but got $actualRate"
            );
        }
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
