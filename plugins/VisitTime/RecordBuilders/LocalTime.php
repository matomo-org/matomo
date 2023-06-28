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
use Piwik\Plugins\VisitTime\Archiver;

class LocalTime extends Base
{
    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::LOCAL_TIME_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $record = new DataTable();

        $query = $logAggregator->queryVisitsByDimension(["label" => "HOUR(log_visit.visitor_localtime)"]);
        while ($row = $query->fetch()) {
            $record->sumRowWithLabel($row['label'], $row);
        }

        $this->ensureAllHoursAreSet($record);

        $record->filter(Sort::class, ['label', 'asc']);

        return [
            Archiver::LOCAL_TIME_RECORD_NAME => $record,
        ];
    }
}
