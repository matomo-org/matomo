<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin;

/**
 * Class Archiver
 * @package Piwik\Plugins\ExamplePlugin
 *
 * Archiver is class processing raw data into ready ro read reports.
 * It must implement two methods for aggregating daily reports
 * aggregateDayReport() and other for summing daily reports into periods
 * like week, month, year or custom range aggregateMultipleReports().
 *
 * For more detailed information about Archiver please visit Piwik developer guide
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Archiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    /**
     * It is a good practice to store your archive names (reports stored in database)
     * in Archiver class constants. You can define as many record names as you want
     * for your plugin.
     *
     * Also important thing is that record name must be prefixed with plugin name.
     *
     * This is only an example record name, so feel free to change it to suit your needs.
     */
    const EXAMPLEPLUGIN_ARCHIVE_RECORD = "ExamplePlugin_archive_record";

    public function aggregateDayReport()
    {
        /**
         * inside this method you can implement your LogAggreagator usage
         * to process daily reports, this one uses idvisitor to group results.
         *
         * $visitorMetrics = $this
         * ->getLogAggregator()
         * ->getMetricsFromVisitByDimension('idvisitor')
         * ->asDataTable();
         * $visitorReport = $visitorMetrics->getSerialized();
         * $this->getProcessor()->insertBlobRecord(self::EXAMPLEPLUGIN_ARCHIVE_RECORD, $visitorReport);
         */
    }

    public function aggregateMultipleReports()
    {
        /**
         * Inside this method you can simply point daily records
         * to be summed. This work for most cases.
         * However if needed, also custom queries can be implemented
         * for periods to achieve more acurrate results.
         *
         * $this->getProcessor()->aggregateDataTableRecords(self::EXAMPLEPLUGIN_ARCHIVE_RECORD);
         */
    }
}
