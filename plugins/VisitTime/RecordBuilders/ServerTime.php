<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitTime\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\DataTable;
use Piwik\DataTable\Filter\Sort;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Plugins\VisitTime\Archiver;

class ServerTime extends Base
{
    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::SERVER_TIME_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $record = new DataTable();

        $query = $logAggregator->queryVisitsByDimension(["label" => "HOUR(log_visit.visit_first_action_time)"]);
        while ($row = $query->fetch()) {
            $row['label'] = $this->convertTimeToLocalTimezone($row['label'], $archiveProcessor);

            $columns = [
                Metrics::INDEX_NB_UNIQ_VISITORS => $row[Metrics::INDEX_NB_UNIQ_VISITORS],
                Metrics::INDEX_NB_VISITS => $row[Metrics::INDEX_NB_VISITS],
                Metrics::INDEX_NB_ACTIONS => $row[Metrics::INDEX_NB_ACTIONS],
                Metrics::INDEX_NB_USERS => $row[Metrics::INDEX_NB_USERS],
                Metrics::INDEX_MAX_ACTIONS => $row[Metrics::INDEX_MAX_ACTIONS],
                Metrics::INDEX_SUM_VISIT_LENGTH => $row[Metrics::INDEX_SUM_VISIT_LENGTH],
                Metrics::INDEX_BOUNCE_COUNT => $row[Metrics::INDEX_BOUNCE_COUNT],
                Metrics::INDEX_NB_VISITS_CONVERTED => $row[Metrics::INDEX_NB_VISITS_CONVERTED],
            ];

            $record->sumRowWithLabel($row['label'], $columns);
        }

        $query = $logAggregator->queryConversionsByDimension(["label" => "HOUR(log_conversion.server_time)"]);
        while ($conversionRow = $query->fetch()) {
            $idGoal = (int) $conversionRow['idgoal'];
            $columns = [
                Metrics::INDEX_GOALS => [
                    $idGoal => Metrics::makeGoalColumnsRow($idGoal, $conversionRow),
                ],
            ];

            $conversionRow['label'] = $this->convertTimeToLocalTimezone($conversionRow['label'], $archiveProcessor);
            $record->sumRowWithLabel($conversionRow['label'], $columns);
        }

        $record->filter(DataTable\Filter\EnrichRecordWithGoalMetricSums::class);

        $this->ensureAllHoursAreSet($record);

        $record->filter(Sort::class, ['label', 'asc']);

        return [
            Archiver::SERVER_TIME_RECORD_NAME => $record,
        ];
    }

    protected function convertTimeToLocalTimezone(int $hour, ArchiveProcessor $archiveProcessor): int
    {
        $date = Date::factory($archiveProcessor->getParams()->getDateStart()->getDateStartUTC())->toString();
        $timezone = $archiveProcessor->getParams()->getSite()->getTimezone();

        $datetime = $date . ' ' . $hour . ':00:00';
        $hourInTz = (int)Date::factory($datetime, $timezone)->toString('H');
        return $hourInTz;
    }
}
