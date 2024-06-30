<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific;

use Piwik\Archive;
use Piwik\Cache;
use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Archiver as GoalsArchiver;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecificProcessedMetric;
use Piwik\Plugins\Goals\Goals;
use Piwik\Site;

/**
 * The page conversion rate for a specific goal. Calculated as:
 *
 *     goal's nb_conversions / sum_daily_nb_uniq_visitors
 *
 * The goal's nb_conversions is calculated by the Goal archiver and nb_visits
 * by the core archiving process.
 */
class ConversionPageRate extends GoalSpecificProcessedMetric
{
    /**
     * @var array|null
     */
    private $goalTotals = null;

    public function getName()
    {
        return Goals::makeGoalColumn($this->idGoal, 'nb_conversions_page_rate', false);
    }

    public function getTranslatedName()
    {
        return Piwik::translate('Goals_ConversionRatePageViewedBefore', $this->getGoalName());
    }

    public function getDocumentation()
    {
        return Piwik::translate('Goals_ColumnConversionRatePageViewedBeforeDocumentation', $this->getGoalNameForDocs());
    }

    public function getDependentMetrics()
    {
        return ['goals'];
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function beforeCompute($report, DataTable $table)
    {
        // Find all goal ids used in the table and store in an array
        $goals = [];
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            if (isset($row[Metrics::INDEX_GOALS])) {
                foreach ($row[Metrics::INDEX_GOALS] as $goalIdString => $metrics) {
                    $goals[$goalIdString] = $goalIdString;
                }
            }
        }

        // Get the total top-level conversions for the goals in the table
        $goalTotals = $this->getGoalTotalConversions($table, $goals);
        if (count($goalTotals) === 0) {
            return false; // do not compute the metric
        }

        $this->goalTotals = $goalTotals;
    }

    public function compute(Row $row)
    {
        $mappingFromNameToIdGoal = Metrics::getMappingFromNameToIdGoal();
        $goalMetrics = $this->getGoalMetrics($row);

        $nbConversions = $this->getMetric($goalMetrics, Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ, $mappingFromNameToIdGoal);
        $goalTotal = $this->goalTotals[$this->idGoal];

        $rate = Piwik::getQuotientSafe($nbConversions, $goalTotal, 3);

        // Prevent page rates over 100% which can happen when there are subpages
        return min($rate, 1);
    }

    public function computeExtraTemporaryMetrics(Row $row): array
    {
        return [
            'total_conversions' => $this->goalTotals[$this->idGoal],
        ];
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }

    public function getFormula(): ?string
    {
        return 'min($goals["idgoal=%s"].nb_conversions_page_uniq / $total_conversions, 1)';
    }

    /**
     * Get the conversions total for each goal in the top level datatable
     *
     * @param DataTable $table
     * @param array $goalIds
     * @return array
     */
    private function getGoalTotalConversions(DataTable $table, array $goalIds): array
    {
        $goalTotals = [];

        if (empty($goalIds)) {
            return $goalTotals;
        }

        /** @var Site $site */
        $site = $table->getMetadata('site');
        if (empty($site)) {
            return $goalTotals;
        }
        $idSite = $site->getId();

        $period = $table->getMetadata('period');
        $periodName = $period->getLabel();
        $date = $period->getDateStart()->toString();
        $date = ($periodName === 'range' ? $date . ',' . $period->getDateEnd()->toString() : $date);
        $segment = $table->getMetadata('segment');

        $cache = Cache::getTransientCache();
        $cacheId = sprintf('ConversionPageRate.goalTotals.%s.%s.%s.%s', $idSite, $periodName, $date, $segment);

        $goalTotals = $cache->fetch($cacheId);
        if (is_array($goalTotals)) {
            return $goalTotals;
        }

        $archive = Archive::build($idSite, $periodName, $date, $segment);

        $names = [];
        foreach ($goalIds as $idGoal => $g) {
            $names[$idGoal] = GoalsArchiver::getRecordName('nb_conversions', $idGoal);
        }

        $sum = $archive->getNumeric($names);
        foreach ($names as $idGoal => $name) {
            if (is_array($sum) && array_key_exists($name, $sum) && is_numeric($sum[$name])) {
                $goalTotals[$idGoal] = $sum[$name];
            } elseif (is_numeric($sum)) {
                $goalTotals[$idGoal] = $sum;
            }
        }

        $cache->save($cacheId, $goalTotals);

        return $goalTotals;
    }
}
