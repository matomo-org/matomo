<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserSettings\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;

/**
 * TODO
 */
class PluginsVisitsPercent extends ProcessedMetric
{
    private $cachedTotalVisits = null;

    public function getName()
    {
        return 'nb_visits_percentage';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnPercentageVisits');
    }

    public function compute(Row $row)
    {
        // TODO: Implement compute() method.
    }

    public function format($value)
    {
        return ($value * 100) . '%';
    }

    public function getDependenctMetrics()
    {
        return array('nb_visits');
    }

    public function beforeCompute(Report $report, DataTable $table)
    {
        // TODO
        return true;
    }
}