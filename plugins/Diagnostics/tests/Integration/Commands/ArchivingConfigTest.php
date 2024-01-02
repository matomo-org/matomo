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

/**
 * @group ArchivingConfigTest
 */
class ArchivingConfigTest extends ConsoleCommandTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    public function test_CommandOutput_IsAsExpected()
    {
        $expected = <<<OUTPUT
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
            'command' => 'diagnostics:archiving-config'
        ]);
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }

    public function test_CommandOutput_withJsonOption_IsAsExpected()
    {
        $expected = '[["Total Invalidation Count","0"],["In Progress Invalidation Count","0"],["Scheduled Invalidation Count","0"],["Earliest invalidation ts_started",""],["Latest invalidation ts_started",""],["Earliest invalidation ts_invalidated",""],["Latest invalidation ts_invalidated",""],["Number of segment invalidations","0"],["Number of plugin invalidations","0"],["List of plugins being invalidated",""]]';
        $this->applicationTester->run([
            'command' => 'diagnostics:archiving-metrics',
            '--json' => true
        ]);
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }
}

ArchivingConfigTest::$fixture = new OneVisitorTwoVisits();
