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
use Piwik\Plugins\ExampleLogTables\Dao\CustomAccountLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;

/**
 * Archives one metric aggregated across both custom log tables.
 *
 * Record builders are discovered only inside a `RecordBuilders/` directory. Move this class elsewhere
 * and nothing archives the record, with no error: core only instantiates an archiver for a plugin at
 * all if that plugin has a discoverable builder or an `Archiver` subclass, and this plugin has no
 * `Archiver`. A builder whose constructor requires arguments is dropped just as silently, so keep it
 * constructible.
 *
 * `Archiver.addRecordBuilders` is the sanctioned way to register a builder from outside the directory,
 * and it is not an escape hatch from the paragraph above: the event is posted from the same
 * `getRecordBuilders()` that only runs once a plugin already qualifies, so a plugin with neither a
 * discoverable builder nor an `Archiver` never sees it fire. It adds builders to a plugin that
 * archives; it does not make one archive.
 *
 * The point of the query below is the FROM list: naming both custom tables alongside `log_visit` is
 * enough, because each table declares in `Tracker/LogTable/` how it joins to the next. The same two
 * declarations that make the segments work make this aggregation work, and they are also what makes
 * the tables reachable for GDPR deletion and export. Name them as `Tracker/LogTable/` declared them --
 * `LogAggregator` prefixes the FROM list itself, so this is the one place in the plugin where
 * `Common::prefixTable()` would be wrong and would double the prefix.
 *
 * Four things about that FROM list do not generalise, and all of them bite quietly:
 *
 * - **The joins are `LEFT`.** Core upgrades them to `INNER` only behind a segment's temporary table,
 *   so rows with no account are excluded here by the `is_paying = 1` predicate rejecting NULL, not by
 *   the join. Any predicate tolerant of NULL -- `!= 1`, `IS NULL`, a `COALESCE()` -- would silently
 *   start counting visits that have no account row at all.
 *
 * - **The join direction decides what you may aggregate.** Both custom tables are keyed on the column
 *   they are joined by, so one visit matches at most one row of each and the count below is exact
 *   either way -- the `distinct` is a guard, not load-bearing here. It becomes load-bearing the moment
 *   your table holds one row per *event* rather than one per subject, because then the join multiplies
 *   rows and anything additive is multiplied with them: a `SUM()` over your own column would count each
 *   row once per matching visit, and a fixture giving each subject one visit would still pass. An
 *   additive metric belongs in a query against your own table on its own date column --
 *   `plugins/BotTracking/RecordBuilders/AIChatbotReports.php` is the production example, and note that
 *   it queries `log_visit` separately for its visit-side numbers rather than joining it -- and that it
 *   builds its SQL by hand rather than through `generateQuery()`, which is why its metrics are
 *   segment-blind. That is a trade-off to make on purpose, not to inherit.
 * - **A hand-appended predicate must not carry a placeholder.** When segment caching applies,
 *   `LogAggregator` rewrites the where statement against its temporary table and empties the bind
 *   array while doing it. The literal `1` below survives that; `is_paying = ?` with a bound value
 *   would fail with an invalid parameter number -- and only under a segment, so it passes every
 *   unsegmented test first.
 * - **The paying flag is mutable reference data.** The tracker rewrites it, while invalidation on
 *   tracking only covers the site and date of the request that arrived. Flipping an account's flag today
 *   therefore leaves last month's archives standing, and a later re-archive of that month produces a
 *   different number. Aggregating against a table that is corrected over time is a decision, not a
 *   detail.
 */
class PayingAccountVisits extends RecordBuilder
{
    /**
     * The prefix before the first underscore is not decoration: `Archive::getPluginForReport()` reads
     * it to decide which plugin to launch archiving for, and throws if it is not an activated plugin.
     * Rename the plugin without renaming this value and reads fail, long after archiving succeeded.
     */
    public const NB_VISITS_PAYING_ACCOUNT_RECORD = 'ExampleLogTables_nb_visits_paying_account';

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_NUMERIC, self::NB_VISITS_PAYING_ACCOUNT_RECORD),
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
        $where .= ' AND ' . CustomAccountLog::TABLE_NAME . '.is_paying = 1';

        $query = $logAggregator->generateQuery(
            'count(distinct log_visit.idvisit) AS nb_visits',
            ['log_visit', CustomUserLog::TABLE_NAME, CustomAccountLog::TABLE_NAME],
            $where,
            false,
            false
        );

        $nbVisits = $logAggregator->getDb()->fetchOne($query['sql'], $query['bind']);

        // Non-day periods are summed from the day records automatically, so only the day case is
        // implemented here. The operation is guessed from the record *name*: anything not starting
        // with `max_` or `min_` is summed, so a numeric record you did not want summed needs a
        // different name or an explicit aggregation operation.
        return [self::NB_VISITS_PAYING_ACCOUNT_RECORD => (int) $nbVisits];
    }
}
