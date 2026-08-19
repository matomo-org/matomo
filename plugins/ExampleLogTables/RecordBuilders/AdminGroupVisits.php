<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Plugins\ExampleLogTables\Dao\CustomGroupLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;

/**
 * Archives one metric aggregated across both custom log tables.
 *
 * Record builders are discovered only inside a `RecordBuilders/` directory. Move this class elsewhere
 * and nothing archives the record: core falls back to an `Archiver` class if the plugin has one, and
 * this plugin has none, so the metric simply stops existing -- with no error. (`Archiver.addRecordBuilders`
 * is the sanctioned way to register a builder from outside the directory. A builder whose constructor
 * requires arguments is also dropped silently, so keep it constructible.)
 *
 * The point of the query below is the FROM list: naming both custom tables alongside `log_visit` is
 * enough, because each table declares in `Tracker/LogTable/` how it joins to the next. The same two
 * declarations that make the segments work make this aggregation work, and they are also what makes
 * the tables reachable for GDPR deletion and export.
 *
 * Two things about that FROM list do not generalise, and both bite quietly:
 *
 * - **The join fans out.** One row per user in the user table becomes one row per *visit* of that user
 *   once joined to `log_visit`. That is harmless for `count(distinct log_visit.idvisit)` below and
 *   wrong for anything additive: `SUM()` over a column of your own table would be multiplied by the
 *   visits per user, and a fixture giving each user one visit would still pass. For an additive metric,
 *   query your own table directly with `getWhereStatement()` on its own date column instead --
 *   `plugins/BotTracking/RecordBuilders/AIChatbotReports.php` is the production example.
 * - **The group flag is mutable reference data.** The tracker rewrites it, while invalidation on
 *   tracking only covers the site and date of the request that arrived. Flipping a group's flag today
 *   therefore leaves last month's archives standing, and a later re-archive of that month produces a
 *   different number. Aggregating against a table that is corrected over time is a decision, not a
 *   detail.
 */
class AdminGroupVisits extends RecordBuilder
{
    /**
     * The prefix before the first underscore is not decoration: `Archive::getPluginForReport()` reads
     * it to decide which plugin to launch archiving for, and throws if it is not an activated plugin.
     * Rename the plugin without renaming this value and reads fail, long after archiving succeeded.
     */
    public const NB_VISITS_ADMIN_GROUP_RECORD = 'ExampleLogTables_nb_visits_admin_group';

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_NUMERIC, self::NB_VISITS_ADMIN_GROUP_RECORD),
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $query = $logAggregator->generateQuery(
            'count(distinct log_visit.idvisit) AS nb_visits',
            ['log_visit', CustomUserLog::TABLE_NAME, CustomGroupLog::TABLE_NAME],
            $logAggregator->getWhereStatement(
                'log_visit',
                'visit_last_action_time',
                CustomGroupLog::TABLE_NAME . '.is_admin = 1'
            ),
            '',
            ''
        );

        $nbVisits = $logAggregator->getDb()->fetchOne($query['sql'], $query['bind']);

        // Non-day periods are summed from the day records automatically, so only the day case is
        // implemented here.
        return [self::NB_VISITS_ADMIN_GROUP_RECORD => (int) $nbVisits];
    }
}
