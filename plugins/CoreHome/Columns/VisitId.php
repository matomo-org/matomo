<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\Dimension\VisitDimension;

/**
 * Dimension for the log_visit.idvisit column. This column is added in the CREATE TABLE
 * statement, so this dimension exists only to configure a segment.
 */
class VisitId extends VisitDimension
{
    protected $columnName = 'idvisit';
    protected $acceptValues = 'Any integer.';
    protected $category = 'General_Visit';
    protected $nameSingular = 'General_VisitId';
    protected $namePlural = 'General_ColumnNbVisits';
    protected $segmentName = 'visitId';
    protected $allowAnonymous = false;
    protected $metricId = 'visits';
    protected $type = self::TYPE_TEXT;

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_UNIQUE);
        $metricsList->addMetric($metric);
    }
}