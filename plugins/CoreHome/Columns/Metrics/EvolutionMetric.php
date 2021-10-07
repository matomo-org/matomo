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
use Piwik\Date;
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
     * @var DataTable
     */
    private $currentData;

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
    public function __construct($wrapped, DataTable $currentData = null, DataTable $pastData = null, $evolutionMetricName = false, $quotientPrecision = 0)
    {
        $this->wrapped = $wrapped;
        $this->pastData = $pastData;
        $this->currentData = $currentData;

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

        // Reduce past value proportionally to match the percent of the current period which is complete, if applicable
        $ratio = $this->getRatio($this->currentData, $this->pastData);
        $period = $this->pastData->getMetadata('period');
        $row->setMetadata('ratio', $ratio);
        $row->setMetadata('previous_'.$columnName, $pastValue);
        $row->setMetadata('periodName', $period->getLabel());
        $row->setMetadata('previousRange', $period->getDateStart()->setTime('00:00:00').' - '.$period->getDateEnd()->setTime('00:00:00'));
        $pastValue = ($pastValue * $ratio);

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

    /**
     * Calculate the ratio of time between a past period and current incomplete period
     *
     * eg. if today is Thursday at 12:00pm and the past period is a week then the ratio is 0.5, exactly half of the
     * current incomplete period has passed
     *
     * If the current period end is in the past then the ratio will always be 1, since the current period is complete.
     *
     * @param DataTable $currentData
     * @param DataTable $pastData
     * @return float|int
     * @throws \Exception
     */
    public static function getRatio(DataTable $currentData, DataTable $pastData)
    {
        $ratio = 1;

        $p = $pastData->getMetadata('period');

        $pStart = $p->getDateStart()->setTime('00:00:00');
        $pEnd = $p->getDateEnd()->setTime('00:00:00');

        $c = $currentData->getMetadata('period');
        $cStart = $c->getDateStart()->setTime('00:00:00');
        $cEnd = $c->getDateEnd()->setTime('00:00:00');

        $nowTS = Date::getNowTimestamp();

        $metadata = $currentData->getAllTableMetadata();

        // If we know the date the the datatable data was generated then use that instead of now
        if (isset($metadata[DataTable::ARCHIVED_DATE_METADATA_NAME])) {
            $nowTS = $metadata[DataTable::ARCHIVED_DATE_METADATA_NAME];
        }

        if ($cStart->getTimestamp() <= $nowTS && $cEnd->getTimestamp() >= $nowTS) {
            $secsInPastPeriod = $pEnd->getTimestamp() - $pStart->getTimestamp();
            $secsInCurrentPeriod = $nowTS - $cStart->getTimestamp();
            $ratio = $secsInCurrentPeriod / $secsInPastPeriod;
        }

        return $ratio;

    }
}