<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicePlugins\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Config as PiwikConfig;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\DevicePlugins\Archiver;

class DevicePlugins extends RecordBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->maxRowsInTable = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::PLUGIN_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $selects = [];
        $columns = \Piwik\Plugins\DevicePlugins\DevicePlugins::getAllPluginColumns();

        foreach ($columns as $column) {
            $selects[] = sprintf(
                "sum(case log_visit.%s when 1 then 1 else 0 end) as %s",
                $column->getColumnName(),
                substr($column->getColumnName(), 7) // remove leading `config_`
            );
        }

        $logAggregator = $archiveProcessor->getLogAggregator();
        $query = $logAggregator->queryVisitsByDimension(array(), false, $selects, $metrics = array());
        $data = $query->fetch();

        $cleanRow = LogAggregator::makeArrayOneColumn($data, Metrics::INDEX_NB_VISITS);
        $table = DataTable::makeFromIndexedArray($cleanRow);

        return [
            Archiver::PLUGIN_RECORD_NAME => $table,
        ];
    }
}
