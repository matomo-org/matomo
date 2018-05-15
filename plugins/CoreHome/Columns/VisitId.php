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
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;

/**
 * Dimension for the log_visit.idvisit column. This column is added in the CREATE TABLE
 * statement, so this dimension exists only to configure a segment.
 */
class VisitId extends VisitDimension
{
    protected $columnName = 'idvisit';
    protected $acceptValues = 'Any integer.';
    protected $nameSingular = 'General_Visit';
    protected $namePlural = 'General_ColumnNbVisits';
    protected $segmentName = 'visitId';
    protected $allowAnonymous = false;
    protected $metricId = 'visits';
    protected $type = self::TYPE_TEXT;

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setName('General_VisitId');
        $this->addSegment($segment);
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_UNIQUE);
        $metric->setTranslatedName(Piwik::translate('General_ColumnNbVisits'));
        $metric->setDocumentation(Piwik::translate('General_ColumnNbVisitsDocumentation'));
        $metric->setName('nb_visits');
        $metricsList->addMetric($metric);
    }
}