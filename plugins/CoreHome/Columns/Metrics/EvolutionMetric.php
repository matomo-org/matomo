<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;

/**
 * Calculates evolution values for any other metric. An evolution is the percent change from a
 * point in the past to the present. They are computed as:
 *
 *     (current value - value in past) / value in past
 *
 * @api
 */
class EvolutionMetric extends ProcessedMetric
{
    /**
     * @var Metric|string
     */
    private $wrapped;

    /**
     * @var string
     */
    private $evolutionMetricName;

    /**
     * @var int
     */
    private $quotientPrecision;

    /**
     * @var DataTable
     */
    private $pastData;

    /**
     * The list of labels leading to the current subtable being processed. Used to get the proper subtable in
     * $pastData.
     *
     * @var string[]
     */
    private $labelPath = [];

    /**
     * Constructor.
     *
     * @param string|Metric $wrapped The metric used to calculate the evolution.
     * @param DataTable|null $pastData The data in the past to use when calculating evolutions.
     * @param string|false $evolutionMetricName The name of the evolution processed metric. Defaults to
     *                                          $wrapped's name with `'_evolution'` appended.
     * @param int $quotientPrecision The percent's quotient precision.
     */
    public function __construct($wrapped, DataTable $pastData = null, $evolutionMetricName = false, $quotientPrecision = 0)
    {
        $this->wrapped = $wrapped;
        $this->pastData = $pastData;

        if (empty($evolutionMetricName)) {
            $wrappedName = $this->getWrappedName();
            $evolutionMetricName = $wrappedName . '_evolution';
        }

        $this->evolutionMetricName = $evolutionMetricName;
        $this->quotientPrecision = $quotientPrecision;
    }

    public function getName()
    {
        return $this->evolutionMetricName;
    }

    public function getTranslatedName()
    {
        if ($this->wrapped instanceof Metric) {
            $metricName = $this->wrapped->getTranslatedName();
        } else {
            $defaultMetricTranslations = Metrics::getDefaultMetricTranslations();
            $metricName = isset($defaultMetricTranslations[$this->wrapped]) ? $defaultMetricTranslations[$this->wrapped] : $this->wrapped;
        }
        return Piwik::translate('CoreHome_EvolutionMetricName', [$metricName]);
    }

    public function compute(Row $row)
    {
        $columnName = $this->getWrappedName();
        $pastRow = $this->getPastRowFromCurrent($row);

        $currentValue = $this->getMetric($row, $columnName);
        $pastValue = $pastRow ? $this->getMetric($pastRow, $columnName) : 0;

        $dividend = $currentValue - $pastValue;
        $divisor = $pastValue;

        if ($dividend == 0) {
            return 0;
        } else if ($divisor == 0) {
            return 1;
        } else {
            return Piwik::getQuotientSafe($dividend, $divisor, $this->quotientPrecision + 2);
        }
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function getDependentMetrics()
    {
        return array($this->getWrappedName());
    }

    public function beforeComputeSubtable(Row $row)
    {
        $this->labelPath[] = $row->getColumn('label');
    }

    public function afterComputeSubtable(Row $row)
    {
        array_pop($this->labelPath);
    }

    protected function getWrappedName()
    {
        return $this->wrapped instanceof Metric ? $this->wrapped->getName() : $this->wrapped;
    }

    /**
     * public for Insights use.
     */
    public function getPastRowFromCurrent(Row $row)
    {
        $pastData = $this->getPastDataTable();
        if (empty($pastData)) {
            return null;
        }

        $label = $row->getColumn('label');
        return $label ? $pastData->getRowFromLabel($label) : $pastData->getFirstRow();
    }

    private function getPastDataTable()
    {
        $result = $this->pastData;
        foreach ($this->labelPath as $label) {
            $row = $result->getRowFromLabel($label);
            if (empty($row)) {
                return null;
            }

            $subtable = $row->getSubtable();
            if (empty($subtable)) {
                return null;
            }

            $result = $subtable;
        }
        return $result;
    }
}