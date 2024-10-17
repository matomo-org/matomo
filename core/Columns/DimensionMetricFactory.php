<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Columns;

use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;

/**
 * A factory to create metrics from a dimension.
 *
 * @api since Piwik 3.2.0
 */
class DimensionMetricFactory
{
    /**
     * @var Dimension
     */
    private $dimension = null;

    /**
     * Generates a new dimension metric factory.
     * @param Dimension $dimension A dimension instance the created metrics should be based on.
     */
    public function __construct(Dimension $dimension)
    {
        $this->dimension = $dimension;
    }

    /**
     * @return ArchivedMetric
     */
    public function createCustomMetric($metricName, $readableName, $aggregation, $documentation = '', ?string $semanticType = null)
    {
        if (!$this->dimension->getDbTableName() || !$this->dimension->getColumnName()) {
            throw new \Exception(sprintf('Cannot make metric from dimension %s because DB table or column missing', $this->dimension->getId()));
        }

        $metric = new ArchivedMetric($this->dimension, $aggregation);
        $metric->setType($semanticType ?: $this->dimension->getType());
        $metric->setName($metricName);
        $metric->setTranslatedName($readableName);
        $metric->setDocumentation($documentation);
        $metric->setCategory($this->dimension->getCategoryId());

        return $metric;
    }

    /**
     * @return \Piwik\Plugin\ComputedMetric
     */
    public function createComputedMetric($metricName1, $metricName2, $aggregation)
    {
        // We cannot use reuse ComputedMetricFactory here as it would result in an endless loop since ComputedMetricFactory
        // requires a MetricsList which is just being built here...
        $metric = new ComputedMetric($metricName1, $metricName2, $aggregation);
        $metric->setCategory($this->dimension->getCategoryId());
        return $metric;
    }

    /**
     * @return ArchivedMetric
     */
    public function createMetric($aggregation)
    {
        $dimension = $this->dimension;

        if (!$dimension->getNamePlural()) {
            throw new \Exception(sprintf('No metric can be created for this dimension %s automatically because no $namePlural is set.', $dimension->getId()));
        }

        $prefix = '';
        $translatedName = $dimension->getNamePlural();

        $documentation = '';
        $semanticType = null; // if null, will default to dimension type

        switch ($aggregation) {
            case ArchivedMetric::AGGREGATION_COUNT:
                $prefix = ArchivedMetric::AGGREGATION_COUNT_PREFIX;
                $translatedName = $dimension->getNamePlural();
                $documentation = Piwik::translate('General_ComputedMetricCountDocumentation', $dimension->getNamePlural());
                $semanticType = Dimension::TYPE_NUMBER;
                break;
            case ArchivedMetric::AGGREGATION_SUM:
                $prefix = ArchivedMetric::AGGREGATION_SUM_PREFIX;
                $translatedName = Piwik::translate('General_ComputedMetricSum', $dimension->getNamePlural());
                $documentation  = Piwik::translate('General_ComputedMetricSumDocumentation', $dimension->getNamePlural());
                if ($dimension->getType() == Dimension::TYPE_BOOL) {
                    $semanticType = Dimension::TYPE_NUMBER;
                }
                break;
            case ArchivedMetric::AGGREGATION_MAX:
                $prefix = ArchivedMetric::AGGREGATION_MAX_PREFIX;
                $translatedName = Piwik::translate('General_ComputedMetricMax', $dimension->getNamePlural());
                $documentation  = Piwik::translate('General_ComputedMetricMaxDocumentation', $dimension->getNamePlural());
                break;
            case ArchivedMetric::AGGREGATION_MIN:
                $prefix = ArchivedMetric::AGGREGATION_MIN_PREFIX;
                $translatedName = Piwik::translate('General_ComputedMetricMin', $dimension->getNamePlural());
                $documentation  = Piwik::translate('General_ComputedMetricMinDocumentation', $dimension->getNamePlural());
                break;
            case ArchivedMetric::AGGREGATION_UNIQUE:
                $prefix = ArchivedMetric::AGGREGATION_UNIQUE_PREFIX;
                $translatedName = Piwik::translate('General_ComputedMetricUniqueCount', $dimension->getNamePlural());
                $documentation  = Piwik::translate('General_ComputedMetricUniqueCountDocumentation', $dimension->getNamePlural());
                $semanticType = Dimension::TYPE_NUMBER;
                break;
            case ArchivedMetric::AGGREGATION_COUNT_WITH_NUMERIC_VALUE:
                $prefix = ArchivedMetric::AGGREGATION_COUNT_WITH_NUMERIC_VALUE_PREFIX;
                $translatedName = Piwik::translate('General_ComputedMetricCountWithValue', $dimension->getName());
                $documentation  = Piwik::translate('General_ComputedMetricCountWithValueDocumentation', $dimension->getName());
                $semanticType = Dimension::TYPE_NUMBER;
                break;
        }

        $metricId = strtolower($dimension->getMetricId());

        return $this->createCustomMetric($prefix . $metricId, $translatedName, $aggregation, $documentation, $semanticType);
    }
}
