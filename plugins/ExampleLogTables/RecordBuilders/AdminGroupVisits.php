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
 * Record builders are discovered only inside a `RecordBuilders/` directory. Moving this class
 * elsewhere silently returns the plugin to the legacy archiving path instead of raising an error.
 *
 * The point of the query below is the FROM list: naming both custom tables alongside
 * `log_visit` is enough, because each table declares in `Tracker/LogTable/` how it joins to the
 * next. The same two declarations that make the segments work make this aggregation work, and they
 * are also what makes the tables reachable for GDPR deletion and export.
 */
class AdminGroupVisits extends RecordBuilder
{
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
