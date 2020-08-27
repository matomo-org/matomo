<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicePlugins;

use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\DevicePlugins\Columns\DevicePluginColumn;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicePlugins/functions.php';

/**
 * Archiver for DevicePlugins Plugin
 *
 * @see PluginsArchiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const PLUGIN_RECORD_NAME = 'DevicePlugins_plugin';

    /**
     * Daily archive of DevicePlugins report. Processes reports for Visits by plugins.
     */
    public function aggregateDayReport()
    {
        $this->aggregateByPlugin();
    }

    /**
     * Period archiving: simply sums up daily archives
     */
    public function aggregateMultipleReports()
    {
        $dataTableRecords = array(
            self::PLUGIN_RECORD_NAME,
        );
        $columnsAggregationOperation = null;
        $this->getProcessor()->aggregateDataTableRecords(
            $dataTableRecords,
            $this->maximumRows,
            $maximumRowsInSubDataTable = null,
            $columnToSortByBeforeTruncation = null,
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array()
        );
    }

    /**
     * Archives reports for all available plugin columns
     * @see DevicePluginColumn
     */
    protected function aggregateByPlugin()
    {
        $selects = array();
        $columns = DevicePlugins::getAllPluginColumns();

        foreach ($columns as $column) {
            $selects[] = sprintf(
                "sum(case log_visit.%s when 1 then 1 else 0 end) as %s",
                $column->getColumnName(),
                substr($column->getColumnName(), 7) // remove leading `config_`
            );
        }

        $query = $this->getLogAggregator()->queryVisitsByDimension(array(), false, $selects, $metrics = array());
        $data = $query->fetch();
        $cleanRow = LogAggregator::makeArrayOneColumn($data, Metrics::INDEX_NB_VISITS);
        $table = DataTable::makeFromIndexedArray($cleanRow);
        $this->insertTable(self::PLUGIN_RECORD_NAME, $table);
    }

    protected function insertTable($recordName, DataTable $table)
    {
        $report = $table->getSerialized($this->maximumRows, null, Metrics::INDEX_NB_VISITS);
        $this->getProcessor()->insertBlobRecord($recordName, $report);
    }

}

