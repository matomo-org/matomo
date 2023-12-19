<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\tests\Integration\Commands;

use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\Date;

/**
 * @group ArchivingStatusTest
 */
class ArchivingStatusTest extends ConsoleCommandTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Date::$now = strtotime('2010-03-07 01:00:00');

        // make sure archiving is initiated so there is data in the archive tables
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-02-01');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-02-01', 'browserCode==FF');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-02-01', 'daysSinceFirstVisit==2');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01', 'browserCode==FF');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01', 'daysSinceFirstVisit==2');

        // Create a segment
        SegmentEditorAPI::getInstance()->add('test segment', 'browserCode==IE', self::$fixture->idSite);

        Date::$now = strtotime('2010-04-07 06:00:00');
    }

    public function test_CommandOutput_IsAsExpected()
    {
        $expected = <<<OUTPUT

Invalidation Queue
+--------------+------+--------+-------------------------+---------------------+-----------------+---------+------------+--------+
| Invalidation | Site | Period | Date                    | Time Queued         | Waiting         | Started | Processing | Status |
+--------------+------+--------+-------------------------+---------------------+-----------------+---------+------------+--------+
| 1            | 1    | Day    | 2010-03-06              | 2010-03-07 01:00:00 | 31 days 5 hours |         |            | Queued |
| 2            | 1    | Week   | 2010-03-01 - 2010-03-07 | 2010-03-07 01:00:00 | 31 days 5 hours |         |            | Queued |
| 3            | 1    | Month  | 2010-03                 | 2010-03-07 01:00:00 | 31 days 5 hours |         |            | Queued |
| 4            | 1    | Year   | 2010                    | 2010-03-07 01:00:00 | 31 days 5 hours |         |            | Queued |
+--------------+------+--------+-------------------------+---------------------+-----------------+---------+------------+--------+

Archiving Metrics
+--------------------------------------+---------------------+
| Metric                               | Value               |
+--------------------------------------+---------------------+
| Total Invalidation Count             | 4                   |
| In Progress Invalidation Count       | 0                   |
| Scheduled Invalidation Count         | 4                   |
| Earliest invalidation ts_started     |                     |
| Latest invalidation ts_started       |                     |
| Earliest invalidation ts_invalidated | 2010-03-07 01:00:00 |
| Latest invalidation ts_invalidated   | 2010-03-07 01:00:00 |
| Number of segment invalidations      | 0                   |
| Number of plugin invalidations       | 0                   |
| List of plugins being invalidated    |                     |
+--------------------------------------+---------------------+

Archiving Configuration Settings
+----------+-------------------------------------------------------------+-------------------+
| Section  | Setting                                                     | Value             |
+----------+-------------------------------------------------------------+-------------------+
| database | enable_segment_first_table_join_prefix                      |                   |
| database | enable_first_table_join_prefix                              |                   |
| general  | browser_archiving_disabled_enforce                          | 0                 |
| general  | enable_processing_unique_visitors_day                       | 1                 |
| general  | enable_processing_unique_visitors_week                      | 1                 |
| general  | enable_processing_unique_visitors_month                     | 1                 |
| general  | enable_processing_unique_visitors_year                      | 0                 |
| general  | enable_processing_unique_visitors_range                     | 0                 |
| general  | enable_processing_unique_visitors_multiple_sites            | 0                 |
| general  | process_new_segments_from                                   | beginning_of_time |
| general  | time_before_today_archive_considered_outdated               | 900               |
| general  | time_before_week_archive_considered_outdated                | -1                |
| general  | time_before_month_archive_considered_outdated               | -1                |
| general  | time_before_year_archive_considered_outdated                | -1                |
| general  | time_before_range_archive_considered_outdated               | -1                |
| general  | enable_browser_archiving_triggering                         | 1                 |
| general  | archiving_range_force_on_browser_request                    | 1                 |
| general  | archiving_custom_ranges[]                                   |                   |
| general  | archiving_query_max_execution_time                          | 7200              |
| general  | archiving_ranking_query_row_limit                           | 50000             |
| general  | disable_archiving_segment_for_plugins                       |                   |
| general  | disable_archive_actions_goals                               | 0                 |
| general  | datatable_archiving_maximum_rows_referrers                  | 1000              |
| general  | datatable_archiving_maximum_rows_subtable_referrers         | 50                |
| general  | datatable_archiving_maximum_rows_userid_users               | 50000             |
| general  | datatable_archiving_maximum_rows_custom_dimensions          | 1000              |
| general  | datatable_archiving_maximum_rows_subtable_custom_dimensions | 1000              |
| general  | datatable_archiving_maximum_rows_actions                    | 500               |
| general  | datatable_archiving_maximum_rows_subtable_actions           | 100               |
| general  | datatable_archiving_maximum_rows_site_search                | 500               |
| general  | datatable_archiving_maximum_rows_events                     | 500               |
| general  | datatable_archiving_maximum_rows_subtable_events            | 500               |
| general  | datatable_archiving_maximum_rows_products                   | 10000             |
| general  | datatable_archiving_maximum_rows_standard                   | 500               |
+----------+-------------------------------------------------------------+-------------------+
OUTPUT;

        $this->applicationTester->run([
            'command' => 'diagnostics:archiving-status'
        ]);
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }


    public function test_CommandOutput_withStatsOption_IsAsExpected()
    {
        $expected = <<<OUTPUT

Invalidation Queue
+--------------+------+--------+-------------------------+---------------------+-----------------+---------+------------+--------+
| Invalidation | Site | Period | Date                    | Time Queued         | Waiting         | Started | Processing | Status |
+--------------+------+--------+-------------------------+---------------------+-----------------+---------+------------+--------+
| 1            | 1    | Day    | 2010-03-06              | 2010-03-07 01:00:00 | 31 days 5 hours |         |            | Queued |
| 2            | 1    | Week   | 2010-03-01 - 2010-03-07 | 2010-03-07 01:00:00 | 31 days 5 hours |         |            | Queued |
| 3            | 1    | Month  | 2010-03                 | 2010-03-07 01:00:00 | 31 days 5 hours |         |            | Queued |
| 4            | 1    | Year   | 2010                    | 2010-03-07 01:00:00 | 31 days 5 hours |         |            | Queued |
+--------------+------+--------+-------------------------+---------------------+-----------------+---------+------------+--------+

Archiving Metrics
+--------------------------------------+---------------------+
| Metric                               | Value               |
+--------------------------------------+---------------------+
| Total Invalidation Count             | 4                   |
| In Progress Invalidation Count       | 0                   |
| Scheduled Invalidation Count         | 4                   |
| Earliest invalidation ts_started     |                     |
| Latest invalidation ts_started       |                     |
| Earliest invalidation ts_invalidated | 2010-03-07 01:00:00 |
| Latest invalidation ts_invalidated   | 2010-03-07 01:00:00 |
| Number of segment invalidations      | 0                   |
| Number of plugin invalidations       | 0                   |
| List of plugins being invalidated    |                     |
+--------------------------------------+---------------------+

Instance Statistics
+----------------------+------------------------+
| Statistic Name       | Value                  |
+----------------------+------------------------+
| Site Count           | 3                      |
| Segment Count        | 1                      |
| Database Version     | mysql-version-redacted |
| Last full Month Hits | 8                      |
| Last 12 Month Hits   | 8                      |
+----------------------+------------------------+

Archiving Configuration Settings
+----------+-------------------------------------------------------------+-------------------+
| Section  | Setting                                                     | Value             |
+----------+-------------------------------------------------------------+-------------------+
| database | enable_segment_first_table_join_prefix                      |                   |
| database | enable_first_table_join_prefix                              |                   |
| general  | browser_archiving_disabled_enforce                          | 0                 |
| general  | enable_processing_unique_visitors_day                       | 1                 |
| general  | enable_processing_unique_visitors_week                      | 1                 |
| general  | enable_processing_unique_visitors_month                     | 1                 |
| general  | enable_processing_unique_visitors_year                      | 0                 |
| general  | enable_processing_unique_visitors_range                     | 0                 |
| general  | enable_processing_unique_visitors_multiple_sites            | 0                 |
| general  | process_new_segments_from                                   | beginning_of_time |
| general  | time_before_today_archive_considered_outdated               | 900               |
| general  | time_before_week_archive_considered_outdated                | -1                |
| general  | time_before_month_archive_considered_outdated               | -1                |
| general  | time_before_year_archive_considered_outdated                | -1                |
| general  | time_before_range_archive_considered_outdated               | -1                |
| general  | enable_browser_archiving_triggering                         | 1                 |
| general  | archiving_range_force_on_browser_request                    | 1                 |
| general  | archiving_custom_ranges[]                                   |                   |
| general  | archiving_query_max_execution_time                          | 7200              |
| general  | archiving_ranking_query_row_limit                           | 50000             |
| general  | disable_archiving_segment_for_plugins                       |                   |
| general  | disable_archive_actions_goals                               | 0                 |
| general  | datatable_archiving_maximum_rows_referrers                  | 1000              |
| general  | datatable_archiving_maximum_rows_subtable_referrers         | 50                |
| general  | datatable_archiving_maximum_rows_userid_users               | 50000             |
| general  | datatable_archiving_maximum_rows_custom_dimensions          | 1000              |
| general  | datatable_archiving_maximum_rows_subtable_custom_dimensions | 1000              |
| general  | datatable_archiving_maximum_rows_actions                    | 500               |
| general  | datatable_archiving_maximum_rows_subtable_actions           | 100               |
| general  | datatable_archiving_maximum_rows_site_search                | 500               |
| general  | datatable_archiving_maximum_rows_events                     | 500               |
| general  | datatable_archiving_maximum_rows_subtable_events            | 500               |
| general  | datatable_archiving_maximum_rows_products                   | 10000             |
| general  | datatable_archiving_maximum_rows_standard                   | 500               |
+----------+-------------------------------------------------------------+-------------------+
OUTPUT;

        $this->applicationTester->run(array(
            'command' => 'diagnostics:archiving-status',
            '--with-stats' => true
        ));
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }

}

ArchivingStatusTest::$fixture = new OneVisitorTwoVisits();
