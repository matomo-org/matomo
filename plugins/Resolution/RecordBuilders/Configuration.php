<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Resolution\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Config as PiwikConfig;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\Resolution\Archiver;

class Configuration extends RecordBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->maxRowsInTable = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor)
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::CONFIGURATION_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor)
    {
        $record = new DataTable();

        $cursor = $archiveProcessor->getLogAggregator()->queryVisitsByDimension(['label' => Archiver::CONFIGURATION_DIMENSION]);
        while ($row = $cursor->fetch()) {
            $record->sumRowWithLabel($row['label'], $row);
        }
        return [Archiver::CONFIGURATION_RECORD_NAME => $record];
    }
}
