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
 * - **The join direction decides what you may aggregate.** Both custom tables are keyed on the column
 *   they are joined by, so one visit matches at most one row of each and the count below is exact
 *   either way -- the `distinct` is a guard, not load-bearing here. It becomes load-bearing the moment
 *   your table holds one row per *event* rather than one per subject, because then the join multiplies
 *   rows and anything additive is multiplied with them: a `SUM()` over your own column would count each
 *   row once per matching visit, and a fixture giving each subject one visit would still pass. An
 *   additive metric belongs in a query against your own table on its own date column --
 *   `plugins/BotTracking/RecordBuilders/AIChatbotReports.php` is the production example, and note that
 *   it queries `log_visit` separately for its visit-side numbers rather than joining it.
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

        // getWhereStatement() builds the site and date restriction for one table. Its third argument
        // is appended to that, but it is passed through sprintf() with the table name as the
        // substitution, so it is meant for predicates on the table named in the first argument -- and
        // a literal containing a stray `%` would corrupt the query. A predicate on a *different*
        // table is appended by hand instead, which is what core does too.
        $where = $logAggregator->getWhereStatement('log_visit', 'visit_last_action_time');
        $where .= ' AND ' . CustomGroupLog::TABLE_NAME . '.is_admin = 1';

        $query = $logAggregator->generateQuery(
            'count(distinct log_visit.idvisit) AS nb_visits',
            ['log_visit', CustomUserLog::TABLE_NAME, CustomGroupLog::TABLE_NAME],
            $where,
            false,
            false
        );

        $nbVisits = $logAggregator->getDb()->fetchOne($query['sql'], $query['bind']);

        // Non-day periods are summed from the day records automatically, so only the day case is
        // implemented here.
        return [self::NB_VISITS_ADMIN_GROUP_RECORD => (int) $nbVisits];
    }
}
