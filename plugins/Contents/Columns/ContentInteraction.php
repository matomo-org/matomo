<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Contents\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Contents\Actions\ActionContent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\TableLogAction;

class ContentInteraction extends ActionDimension
{
    protected $columnName = 'idaction_content_interaction';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $acceptValues = 'The type of interaction with the content. For instance "click" or "submit".';
    protected $segmentName = 'contentInteraction';
    protected $nameSingular = 'Contents_ContentInteraction';
    protected $namePlural = 'Contents_ContentInteractions';
    protected $category = 'General_Actions';
    protected $sqlFilter = [TableLogAction::class, 'getOptimizedIdActionSqlMatch'];

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', $this->getActionId());
    }

    public function getActionId()
    {
        return Action::TYPE_CONTENT_INTERACTION;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionContent)) {
            return false;
        }

        $interaction = $request->getParam('c_i');
        $interaction = trim($interaction);

        if (strlen($interaction) > 0) {
            return $interaction;
        }

        return false;
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_COUNT_WITH_NUMERIC_VALUE);
        $metricsList->addMetric($metric);

        // plugins/Contents/RecordBuilders/ContentRecords.php defines nb_impressions as all link visit actions
        // From what I can tell, the that's what the hits metric does plugins/CoreHome/Columns/LinkVisitActionId.php
        $metric = $dimensionMetricFactory->createComputedMetric($metric->getName(), 'hits', ComputedMetric::AGGREGATION_RATE);
        $metric->setName($this->getMetricId() . '_' . ComputedMetric::AGGREGATION_RATE);
        $metric->setTranslatedName(Piwik::translate('General_ComputedMetricRate', $this->getName()));
        $metricsList->addMetric($metric);

        parent::configureMetrics($metricsList, $dimensionMetricFactory);
    }
}
