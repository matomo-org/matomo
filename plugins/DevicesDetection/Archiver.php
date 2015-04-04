<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Metrics;

class Archiver extends \Piwik\Plugin\Archiver
{
    const BROWSER_SEPARATOR = ';';
    const DEVICE_TYPE_RECORD_NAME = 'DevicesDetection_types';
    const DEVICE_BRAND_RECORD_NAME = 'DevicesDetection_brands';
    const DEVICE_MODEL_RECORD_NAME = 'DevicesDetection_models';
    const OS_RECORD_NAME = 'DevicesDetection_os';
    const OS_VERSION_RECORD_NAME = 'DevicesDetection_osVersions';
    const BROWSER_RECORD_NAME = 'DevicesDetection_browsers';
    const BROWSER_ENGINE_RECORD_NAME = 'DevicesDetection_browserEngines';
    const BROWSER_VERSION_RECORD_NAME = 'DevicesDetection_browserVersions';

    const DEVICE_TYPE_FIELD = "config_device_type";
    const DEVICE_BRAND_FIELD = "config_device_brand";
    const DEVICE_MODEL_FIELD = "CONCAT(log_visit.config_device_brand, ';', log_visit.config_device_model)";
    const OS_FIELD = "config_os";
    const OS_VERSION_FIELD = "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))";
    const BROWSER_FIELD = "config_browser_name";
    const BROWSER_ENGINE_FIELD = "config_browser_engine";
    const BROWSER_VERSION_DIMENSION = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";

    public function aggregateDayReport()
    {
        $this->aggregateByLabel(self::DEVICE_TYPE_FIELD, self::DEVICE_TYPE_RECORD_NAME);
        $this->aggregateByLabel(self::DEVICE_BRAND_FIELD, self::DEVICE_BRAND_RECORD_NAME);
        $this->aggregateByLabel(self::DEVICE_MODEL_FIELD, self::DEVICE_MODEL_RECORD_NAME);
        $this->aggregateByLabel(self::OS_FIELD, self::OS_RECORD_NAME);
        $this->aggregateByLabel(self::OS_VERSION_FIELD, self::OS_VERSION_RECORD_NAME);
        $this->aggregateByLabel(self::BROWSER_FIELD, self::BROWSER_RECORD_NAME);
        $this->aggregateByLabel(self::BROWSER_ENGINE_FIELD, self::BROWSER_ENGINE_RECORD_NAME);
        $this->aggregateByLabel(self::BROWSER_VERSION_DIMENSION, self::BROWSER_VERSION_RECORD_NAME);
    }

    public function aggregateMultipleReports()
    {
        $dataTablesToSum = array(
            self::DEVICE_TYPE_RECORD_NAME,
            self::DEVICE_BRAND_RECORD_NAME,
            self::DEVICE_MODEL_RECORD_NAME,
            self::OS_RECORD_NAME,
            self::OS_VERSION_RECORD_NAME,
            self::BROWSER_RECORD_NAME,
            self::BROWSER_ENGINE_RECORD_NAME,
            self::BROWSER_VERSION_RECORD_NAME
        );

        $columnsAggregationOperation = null;

        foreach ($dataTablesToSum as $dt) {
            $this->getProcessor()->aggregateDataTableRecords(
                $dt,
                $this->maximumRows,
                $this->maximumRows,
                $columnToSort = 'nb_visits',
                $columnsAggregationOperation,
                $columnsToRenameAfterAggregation = null,
                $countRowsRecursive = array());
        }
    }

    private function aggregateByLabel($labelSQL, $recordName)
    {
        $metrics = $this->getLogAggregator()->getMetricsFromVisitByDimension($labelSQL)->asDataTable();
        $report = $metrics->getSerialized($this->maximumRows, null, Metrics::INDEX_NB_VISITS);
        $this->getProcessor()->insertBlobRecord($recordName, $report);
    }

}
