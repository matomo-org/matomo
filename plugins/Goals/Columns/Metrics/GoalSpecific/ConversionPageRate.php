<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific;

use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecificProcessedMetric;
use Piwik\Plugins\Goals\DataTable\Filter\CalculateConversionPageRate;
use Piwik\Plugins\Goals\Goals;

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

    /**
     * @var DataTable
     */
    private $dataTable = null;

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
        $this->dataTable = $table;
        unset($this->goalTotals);
        return true;
    }

    public function afterCompute($report, DataTable $table)
    {
        unset($this->dataTable); // remove the reference to the datatable
    }

    public function compute(Row $row)
    {
        $mappingFromNameToIdGoal = Metrics::getMappingFromNameToIdGoal();
        $goalMetrics = $this->getGoalMetrics($row);

        return $this->getMetric($goalMetrics, 'nb_conversions_page_rate', $mappingFromNameToIdGoal);
    }

    public function computeExtraTemporaryMetrics(Row $row): array
    {
        $goalTotals = $this->getGoalTotals();

        $totalConversionsColumnName = $this->getTotalConversionsColumnName();
        return [
            $totalConversionsColumnName => $goalTotals[$this->idGoal],
        ];
    }

    private function getGoalTotals(): array
    {
        if (isset($this->goalTotals)) {
            return $this->goalTotals;
        }

        $this->goalTotals = CalculateConversionPageRate::getGoalTotalConversions($this->dataTable);

        return $this->goalTotals;
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }

    public function getFormula(): ?string
    {
        $totalConversionsColumnName = $this->getTotalConversionsColumnName();
        return sprintf(
            'min($goals["idgoal=%s"].nb_conversions_page_uniq / %s, 1)',
            $this->idGoal,
            $totalConversionsColumnName
        );
    }

    private function getTotalConversionsColumnName()
    {
        return Goals::makeGoalColumn($this->idGoal, 'total_conversions', false);
    }
}
