<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Columns\MetricsList;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;

class ClickedUrl extends ActionDimension
{
    protected $columnName = 'idaction_url';
    protected $segmentName = 'outlinkUrl';
    protected $nameSingular = 'Actions_ColumnClickedURL';
    protected $namePlural = 'Actions_ColumnClickedURLs';
    protected $category = 'General_Actions';
    protected $suggestedValuesApi = 'Actions.getOutlinks';
    protected $type = self::TYPE_URL;
    protected $sqlFilter = [TableLogAction::class, 'getOptimizedIdActionSqlMatch'];

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_OUTLINK);
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_COUNT);
        $metricsList->addMetric($metric);

        parent::configureMetrics($metricsList, $dimensionMetricFactory);
    }
}
