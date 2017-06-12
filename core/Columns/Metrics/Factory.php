<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns\Metrics;

use Piwik\Columns\Column;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Columns\ColumnsProvider;

class Factory
{
    /**
     * @var ColumnsProvider
     */
    private $dimensionFactory;

    public function __construct(ColumnsProvider $dimensionFactory)
    {
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * @param $metricName
     * @return ProcessedMetric|void
     */
    public function makeProcessedMetric($metricName)
    {
        /** @var Column $dimension */
        $dimension = $this->dimensionFactory->factory($metricName);

        switch ($dimension->getType()) {
            case Column::TYPE_NUMBER:
                return new Number($dimension->getColumnName(), $dimension->getName());
            case Column::TYPE_FLOAT:
                return new Float($dimension->getColumnName(), $dimension->getName());
            case Column::TYPE_DURATION_MS:
                return new DurationMilliseconds($dimension->getColumnName(), $dimension->getName());
            case Column::TYPE_DURATION_S:
                return new DurationSeconds($dimension->getColumnName(), $dimension->getName());
            case Column::TYPE_MONEY:
                return new Money($dimension->getColumnName(), $dimension->getName());
        }
    }

}