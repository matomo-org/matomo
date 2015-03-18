<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicePlugins;

use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;

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

    protected function aggregateByPlugin()
    {
        $selects = array(
            "sum(case log_visit.config_pdf when 1 then 1 else 0 end) as pdf",
            "sum(case log_visit.config_flash when 1 then 1 else 0 end) as flash",
            "sum(case log_visit.config_java when 1 then 1 else 0 end) as java",
            "sum(case log_visit.config_director when 1 then 1 else 0 end) as director",
            "sum(case log_visit.config_quicktime when 1 then 1 else 0 end) as quicktime",
            "sum(case log_visit.config_realplayer when 1 then 1 else 0 end) as realplayer",
            "sum(case log_visit.config_windowsmedia when 1 then 1 else 0 end) as windowsmedia",
            "sum(case log_visit.config_gears when 1 then 1 else 0 end) as gears",
            "sum(case log_visit.config_silverlight when 1 then 1 else 0 end) as silverlight",
            "sum(case log_visit.config_cookie when 1 then 1 else 0 end) as cookie"
        );

        $query = $this->getLogAggregator()->queryVisitsByDimension(array(), false, $selects, $metrics = array());
        $data = $query->fetch();
        $cleanRow = LogAggregator::makeArrayOneColumn($data, Metrics::INDEX_NB_VISITS);
        $table = DataTable::makeFromIndexedArray($cleanRow);
        $this->insertTable(self::PLUGIN_RECORD_NAME, $table);
    }

    protected function insertTable($recordName, DataTable $table)
    {
        $report = $table->getSerialized($this->maximumRows, null, Metrics::INDEX_NB_VISITS);
        return $this->getProcessor()->insertBlobRecord($recordName, $report);
    }

}

