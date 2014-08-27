<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Metrics;

/**
 * The DataArray is a data structure used to aggregate datasets,
 * ie. sum arrays made of rows made of columns,
 * data from the logs is stored in a DataArray before being converted in a DataTable
 *
 */

class DataArray extends \Piwik\DataArray
{
    public function sumMetricsImpressions($label, $row)
    {
        if (!isset($this->data[$label])) {
            $this->data[$label] = self::makeEmptyContentsRow();
        }
        $this->doSumContentsImpressionMetrics($row, $this->data[$label]);
    }

    public function sumMetricsInteractions($label, $row)
    {
        if (!isset($this->data[$label])) {
            return; // do igonre interactions that do not have an impression
        }
        $this->doSumContentsInteractionMetrics($row, $this->data[$label]);
    }

    protected static function makeEmptyContentsRow()
    {
        return array(
            Metrics::INDEX_NB_UNIQ_VISITORS         => 0,
            Metrics::INDEX_NB_VISITS                => 0,
            Metrics::INDEX_CONTENT_NB_IMPRESSIONS   => 0,
            Metrics::INDEX_CONTENT_NB_INTERACTIONS  => 0
        );
    }

    protected function doSumContentsImpressionMetrics($newRowToAdd, &$oldRowToUpdate)
    {
        $oldRowToUpdate[Metrics::INDEX_NB_VISITS] += $newRowToAdd[Metrics::INDEX_NB_VISITS];
        $oldRowToUpdate[Metrics::INDEX_NB_UNIQ_VISITORS] += $newRowToAdd[Metrics::INDEX_NB_UNIQ_VISITORS];
        $oldRowToUpdate[Metrics::INDEX_CONTENT_NB_IMPRESSIONS] += $newRowToAdd[Metrics::INDEX_CONTENT_NB_IMPRESSIONS];
    }

    protected function doSumContentsInteractionMetrics($newRowToAdd, &$oldRowToUpdate)
    {
        $oldRowToUpdate[Metrics::INDEX_CONTENT_NB_INTERACTIONS] += $newRowToAdd[Metrics::INDEX_CONTENT_NB_INTERACTIONS];
    }

    public function sumMetricsContentsImpressionPivot($parentLabel, $label, $row)
    {
        if (!isset($this->dataTwoLevels[$parentLabel][$label])) {
            $this->dataTwoLevels[$parentLabel][$label] = $this->makeEmptyContentsRow();
        }
        $this->doSumContentsImpressionMetrics($row, $this->dataTwoLevels[$parentLabel][$label]);
    }

    public function sumMetricsContentsInteractionPivot($parentLabel, $label, $row)
    {
        if (!isset($this->dataTwoLevels[$parentLabel][$label])) {
            return; // do igonre interactions that do not have an impression
        }
        $this->doSumContentsInteractionMetrics($row, $this->dataTwoLevels[$parentLabel][$label]);
    }

}
