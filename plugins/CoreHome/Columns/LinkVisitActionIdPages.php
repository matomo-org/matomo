<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Columns\Discriminator;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Action;

class LinkVisitActionIdPages extends ActionDimension
{
    protected $columnName = 'idlink_va';
    protected $category = 'General_Actions';
    protected $nameSingular = 'General_Actions';
    protected $type = self::TYPE_NUMBER;

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        // empty so we don't auto-generate a segment
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_PAGE_URL);
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_UNIQUE);
        $metric->setTranslatedName(Piwik::translate('General_ColumnPageviews'));
        $metric->setDocumentation(Piwik::translate('General_ColumnPageviewsDocumentation'));
        $metric->setName('pageviews');
        $metricsList->addMetric($metric);
    }
}
