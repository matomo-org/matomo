<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDimensions;


/**
 * The DataArray is a data structure used to aggregate datasets,
 * ie. sum arrays made of rows made of columns,
 * data from the logs is stored in a DataArray before being converted in a DataTable
 *
 */

class DataArray extends \Piwik\DataArray
{
    private static $actionMetrics = array();

    public function setActionMetricsIds($metrics)
    {
        self::$actionMetrics = $metrics;
    }

    protected static function makeEmptyActionRow()
    {
        $metrics = array();

        foreach (self::$actionMetrics as $key) {
            $metrics[$key] = 0;
        }

        return $metrics;
    }

    protected function doSumActionsMetrics($newRowToAdd, &$oldRowToUpdate)
    {
        foreach (self::$actionMetrics as $actionMetric) {
            $oldRowToUpdate[$actionMetric] += $newRowToAdd[$actionMetric];
        }
    }

    public function sumMetricsActionCustomDimensionsPivot($parentLabel, $label, $row)
    {
        if (!isset($this->dataTwoLevels[$parentLabel][$label])) {
            $this->dataTwoLevels[$parentLabel][$label] = $this->makeEmptyActionRow();
        }
        $this->doSumActionsMetrics($row, $this->dataTwoLevels[$parentLabel][$label]);
    }
}
