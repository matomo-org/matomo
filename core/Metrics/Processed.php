<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Metrics;

use Piwik\Metrics;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Tracker\GoalManager;

class Processed extends Base
{

    public function getConversionRate(Row $row)
    {
        $nbVisits = $this->getNumVisits($row);

        $nbVisitsConverted = (int) $this->getColumn($row, Metrics::INDEX_NB_VISITS_CONVERTED);
        if ($nbVisitsConverted > 0) {
            $conversionRate = round(100 * $nbVisitsConverted / $nbVisits, $this->roundPrecision);

            return $conversionRate . '%';
        }
    }

    public function getActionsPerVisit(Row $row)
    {
        $nbVisits  = $this->getNumVisits($row);
        $nbActions = $this->getColumn($row, Metrics::INDEX_NB_ACTIONS);

        if ($nbVisits == 0) {
            return $this->invalidDivision;
        }

        return round($nbActions / $nbVisits, $this->roundPrecision);
    }

    public function getAvgTimeOnSite(Row $row)
    {
        $nbVisits = $this->getNumVisits($row);

        if ($nbVisits == 0) {
            return $this->invalidDivision;
        }

        $visitLength = $this->getColumn($row, Metrics::INDEX_SUM_VISIT_LENGTH);

        return round($visitLength / $nbVisits, $rounding = 0);
    }

    public function getBounceRate(Row $row)
    {
        $nbVisits = $this->getNumVisits($row);

        if ($nbVisits == 0) {
            return $this->invalidDivision;
        }

        $bounceRate = round(100 * $this->getColumn($row, Metrics::INDEX_BOUNCE_COUNT) / $nbVisits, $this->roundPrecision);

        return $bounceRate . "%";
    }

}