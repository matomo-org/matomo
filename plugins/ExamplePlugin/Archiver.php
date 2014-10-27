<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExamplePlugin;

class Archiver extends \Piwik\Plugin\Archiver
{
    const EXAMPLEPLUGIN_USERS_RECORD = "ExamplePlugin_users";

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
         * $this->getProcessor()->insertBlobRecord(self::EXAMPLEPLUGIN_USERS_RECORD, $visitorReport);
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
         * $this->getProcessor()->aggregateDataTableRecords(self::EXAMPLEPLUGIN_USERS_RECORD);
         */
    }

}
