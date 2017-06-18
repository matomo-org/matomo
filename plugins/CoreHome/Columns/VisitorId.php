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
 * Dimension for the log_visit.idvisitor column. This column is added in the CREATE TABLE
 * statement, so this dimension exists only to configure a segment.
 */
class VisitorId extends VisitDimension
{
    protected $columnName = 'idvisitor';
    protected $metricId = 'visitors';
    protected $category = 'General_Visit';
    protected $nameSingular = 'General_VisitorID';
    protected $namePlural = 'General_Visitors';
    protected $allowAnonymous = false;
    protected $type = self::TYPE_TEXT;

    protected function configureSegments()
    {
        parent::configureSegments();

        $segment = new Segment();
        $segment->setType(Segment::TYPE_DIMENSION);
        $segment->setSegment('visitorId');
        $segment->setAcceptedValues('34c31e04394bdc63 - any 16 Hexadecimal chars ID, which can be fetched using the Tracking API function getVisitorId()');
        $segment->setSqlFilterValue(array('Piwik\Common', 'convertVisitorIdToBin'));
        $this->addSegment($segment);
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_UNIQUE);
        $metricsList->addMetric($metric);


    }
}
