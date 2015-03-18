<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Resolution;

use Piwik\DataTable;
use Piwik\Metrics;

/**
 * Archiver for Resolution Plugin
 *
 * @see PluginsArchiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const RESOLUTION_RECORD_NAME = 'Resolution_resolution';
    const CONFIGURATION_RECORD_NAME = 'Resolution_configuration';
    const RESOLUTION_DIMENSION = "log_visit.config_resolution";
    const CONFIGURATION_DIMENSION = "CONCAT(log_visit.config_os, ';', log_visit.config_browser_name, ';', log_visit.config_resolution)";

    public function aggregateDayReport()
    {
        $this->aggregateByResolution();
        $this->aggregateByConfiguration();
    }

    /**
     * Period archiving: simply sums up daily archives
     */
    public function aggregateMultipleReports()
    {
        $dataTableRecords = array(
            self::RESOLUTION_RECORD_NAME,
            self::CONFIGURATION_RECORD_NAME,
        );
        $columnsAggregationOperation = null;
        $this->getProcessor()->aggregateDataTableRecords(
            $dataTableRecords,
            $this->maximumRows,
            $maximumRowsInSubDataTable = null,
            $columnToSortByBeforeTruncation = null,
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array());
    }

    protected function aggregateByConfiguration()
    {
        $metrics = $this->getLogAggregator()->getMetricsFromVisitByDimension(self::CONFIGURATION_DIMENSION)->asDataTable();
        $this->insertTable(self::CONFIGURATION_RECORD_NAME, $metrics);
    }

    protected function aggregateByResolution()
    {
        $table = $this->getLogAggregator()->getMetricsFromVisitByDimension(self::RESOLUTION_DIMENSION)->asDataTable();
        $table->filter('ColumnCallbackDeleteRow', array('label', function ($value) {
            return strlen($value) <= 5;
        }));
        $this->insertTable(self::RESOLUTION_RECORD_NAME, $table);
        return $table;
    }

    protected function insertTable($recordName, DataTable $table)
    {
        $report = $table->getSerialized($this->maximumRows, null, Metrics::INDEX_NB_VISITS);
        return $this->getProcessor()->insertBlobRecord($recordName, $report);
    }

}

