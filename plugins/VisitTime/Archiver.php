<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitTime;

use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Date;

class Archiver extends \Piwik\Plugin\Archiver
{
    const SERVER_TIME_RECORD_NAME = 'VisitTime_serverTime';
    const LOCAL_TIME_RECORD_NAME = 'VisitTime_localTime';

    public function aggregateDayReport()
    {
        $this->aggregateByLocalTime();
        $this->aggregateByServerTime();
    }

    public function aggregateMultipleReports()
    {
        $dataTableRecords = array(
            self::LOCAL_TIME_RECORD_NAME,
            self::SERVER_TIME_RECORD_NAME,
        );
        $this->getProcessor()->aggregateDataTableRecords($dataTableRecords);
    }

    protected function aggregateByServerTime()
    {
        $dataArray = $this->getLogAggregator()->getMetricsFromVisitByDimension(array("label" => "HOUR(log_visit.visit_last_action_time)"));
        $query = $this->getLogAggregator()->queryConversionsByDimension(array("label" => "HOUR(log_conversion.server_time)"));
        if ($query === false) {
            return;
        }

        while ($conversionRow = $query->fetch()) {
            $dataArray->sumMetricsGoals($conversionRow['label'], $conversionRow);
        }
        $dataArray->enrichMetricsWithConversions();
        $dataArray = $this->convertTimeToLocalTimezone($dataArray);
        $this->ensureAllHoursAreSet($dataArray);
        $report = $dataArray->asDataTable()->getSerialized();
        $this->getProcessor()->insertBlobRecord(self::SERVER_TIME_RECORD_NAME, $report);
    }

    protected function aggregateByLocalTime()
    {
        $array = $this->getLogAggregator()->getMetricsFromVisitByDimension("HOUR(log_visit.visitor_localtime)");
        $this->ensureAllHoursAreSet($array);
        $report = $array->asDataTable()->getSerialized();
        $this->getProcessor()->insertBlobRecord(self::LOCAL_TIME_RECORD_NAME, $report);
    }

    protected function convertTimeToLocalTimezone(DataArray &$array)
    {
        $date = Date::factory($this->getProcessor()->getParams()->getDateStart()->getDateStartUTC())->toString();
        $timezone = $this->getProcessor()->getParams()->getSite()->getTimezone();

        $converted = array();
        foreach ($array->getDataArray() as $hour => $stats) {
            $datetime = $date . ' ' . $hour . ':00:00';
            $hourInTz = (int)Date::factory($datetime, $timezone)->toString('H');
            $converted[$hourInTz] = $stats;
        }
        return new DataArray($converted);
    }

    private function ensureAllHoursAreSet(DataArray &$array)
    {
        $data = $array->getDataArray();
        for ($i = 0; $i <= 23; $i++) {
            if (empty($data[$i])) {
                $array->sumMetricsVisits($i, DataArray::makeEmptyRow());
            }
        }
    }

}
