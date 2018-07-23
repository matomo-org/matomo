<?php
/**
 * Created by PhpStorm.
 * User: benakamoorthi
 * Date: 7/23/18
 * Time: 1:30 AM
 */

namespace Piwik\Plugins\CoreHome\Columns\Metrics;


use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Metric;
use Piwik\Plugins\AbTesting\Columns\Metrics\ProcessedMetric;

class PastDataMetric extends ProcessedMetric
{
    /**
     * @var Metric|string
     */
    private $wrapped;

    /**
     * @var string
     */
    private $pastDataMetricName;

    /**
     * @var DataTable
     */
    private $pastData;

    /**
     * Constructor.
     *
     * @param string|Metric $wrapped The metric in the past data table to add.
     * @param DataTable|null $pastData The data in the past to use .
     * @param string|false $pastDataMetricName The name of the newly added metric. Defaults to
     *                                         $wrapped's name with `'_past'` appended.
     */
    public function __construct($wrapped, DataTable $pastData = null, $pastDataMetricName = false)
    {
        $this->wrapped = $wrapped;
        $this->pastData = $pastData;

        if (empty($evolutionMetricName)) {
            $wrappedName = $this->getWrappedName();
            $pastDataMetricName = $wrappedName . '_past';
        }

        $this->pastDataMetricName = $pastDataMetricName;
    }

    public function getName()
    {
        return $this->pastDataMetricName;
    }

    public function getTranslatedName()
    {
        return $this->wrapped instanceof Metric ? $this->wrapped->getTranslatedName() : $this->getName();
    }

    public function compute(Row $row)
    {
        $columnName = $this->getWrappedName();
        $pastRow = $this->getPastRowFromCurrent($row);

        $pastValue = $pastRow ? $this->getMetric($pastRow, $columnName) : 0;
        return $pastValue;
    }

    public function format($value, Formatter $formatter)
    {
        if ($this->wrapped instanceof Metric) {
            return $this->wrapped->format($value, $formatter);
        }
        return $value;
    }

    public function getDependentMetrics()
    {
        return array($this->getWrappedName());
    }
    protected function getWrappedName()
    {
        return $this->wrapped instanceof Metric ? $this->wrapped->getName() : $this->wrapped;
    }

    private function getPastRowFromCurrent(Row $row)
    {
        if (empty($this->pastData)) {
            return null;
        }

        $label = $row->getColumn('label');
        return $label ? $this->pastData->getRowFromLabel($label) : $this->pastData->getFirstRow();
    }
}