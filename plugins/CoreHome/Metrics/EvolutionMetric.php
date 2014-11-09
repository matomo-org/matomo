<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;

/**
 * TODO
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
     * TODO
     */
    public function __construct($wrapped, $pastData, $evolutionMetricName = false, $quotientPrecision = 0)
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
        return $this->wrapped instanceof Metric ? $this->wrapped->getTranslatedName() : $this->getName();
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

    public function format($value)
    {
        return ($value * 100) . '%';
    }

    public function getDependenctMetrics()
    {
        return array($this->getWrappedName());
    }

    protected function getWrappedName()
    {
        return $this->wrapped instanceof Metric ? $this->wrapped->getName() : $this->wrapped;
    }

    protected function getPastRowFromCurrent(Row $row)
    {
        return $this->pastData->getRowFromLabel($row->getColumn('label'));
    }
}