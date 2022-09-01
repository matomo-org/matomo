<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\Discriminator;
use Piwik\Columns\Join;
use Piwik\Columns\MetricsList;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class ExitPageUrl extends VisitDimension
{
    protected $columnName = 'visit_exit_idaction_url';
    protected $columnType = 'INTEGER(10) UNSIGNED NULL DEFAULT 0';
    protected $type = self::TYPE_URL;
    protected $segmentName = 'exitPageUrl';
    protected $nameSingular = 'Actions_ColumnExitPageURL';
    protected $namePlural = 'Actions_ColumnExitPageURLs';
    protected $category = 'General_Actions';
    protected $suggestedValuesApi = 'Actions.getExitPageUrls';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        parent::configureMetrics($metricsList, $dimensionMetricFactory);
    }

    public function getDbColumnJoin()
    {
        return new Join\ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_PAGE_URL);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int|bool
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $idActionUrl = false;

        if (!empty($action)) {
            $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();
        }

        return (int) $idActionUrl;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if (empty($action)) {
            return false;
        }

        $id = $action->getIdActionUrlForEntryAndExitIds();

        if (!empty($id)) {
            $id = (int) $id;
        }

        return $id;
    }
}
