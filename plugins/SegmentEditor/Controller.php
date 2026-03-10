<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor;

use Piwik\Piwik;
use Piwik\Plugins\SegmentEditor;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Request;
use Piwik\Url;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    /** The requested period */
    protected $period;

    /** The requested segment */
    protected $currentSegmentDefinition;

    public function __construct()
    {
        parent::__construct();

        $this->period = Request::fromRequest()->getStringParameter('period', 'day');
        $this->strDate = Request::fromRequest()->getStringParameter('date', 'yesterday');
        $this->currentSegmentDefinition = Request::fromRequest()->getStringParameter('segment', '');
        $this->checkSitePermission();
        Piwik::checkUserHasViewAccess($this->idSite);
    }

    public function manageSegments(): string
    {
        $view = new View('@SegmentEditor/manageSegments');
        $this->setGeneralVariablesView($view);
        $view->authorizedToCreateSegments = SegmentEditorAPI::getInstance()->isUserCanAddNewSegment($this->idSite);
        $view->segmentTranslations = $this->getTranslations();
        $view->showMenu = true;
        $view->sparklineTooltipKey = $this->getSparklineTooltipKey();

        $allVisitsSegment = [
            'definition' => '',
            'name' => Piwik::translate('SegmentEditor_DefaultAllVisits'),
            'fixed' => true,
            'starred' => false,
        ];
        $view->segmentList = SegmentEditor\API::getInstance()->getAll($this->idSite);
        array_unshift($view->segmentList, $allVisitsSegment);

        $view->hasRealtimeSegments = false;
        foreach ($view->segmentList as &$segment) {
            $segment['fixed'] = $segment['fixed'] ?? false;
            $segment['selected'] = $segment['definition'] === $this->currentSegmentDefinition;

            if ($this->isRealtimeSegment($segment)) {
                $segment['isRealtime'] = true;
                $view->hasRealtimeSegments = true;
                continue;
            }

            $segment['isRealtime'] = false;
            $segment['sparklineUrl'] = $this->getSegmentSparklineUrl($segment);
        }

        return $view->render();
    }

    private function isRealtimeSegment(array $segment): bool
    {
        return !empty($segment['definition']) && empty((int)$segment['auto_archive']);
    }

    protected function getSegmentSparklineUrl(array $segment): string
    {
        $params = $this->getGraphParamsModified([
            'viewDataTable' => 'sparkline',
            'action'        => 'getEvolutionGraph',
            'module'        => 'VisitsSummary',
            'columns'       => ['nb_visits'],
            'segment'       => $segment['definition'],
        ]);
        return Url::getCurrentQueryStringWithParametersModified($params);
    }

    private function getSparklineTooltipKey(): string
    {
        switch ($this->period) {
            case 'day':
                return 'SegmentEditor_SparklineTooltipDays';
            case 'week':
                return 'SegmentEditor_SparklineTooltipWeeks';
            case 'month':
                return 'SegmentEditor_SparklineTooltipMonths';
            case 'year':
                return 'SegmentEditor_SparklineTooltipYears';
        }
        return '';
    }

    /**
     * @return array<string, string>
     */
    private function getTranslations(): array
    {
        $translationKeys = array(
            'SegmentEditor_AddNewSegment',
            'SegmentEditor_ManageSegments',
            'SegmentEditor_SegmentPageTitle',
            'SegmentEditor_SegmentPageDescription',
            'SegmentEditor_SeeDashboardForThisSegment',
            'SegmentEditor_SparklineTooltipDays',
            'SegmentEditor_SparklineTooltipWeeks',
            'SegmentEditor_SparklineTooltipMonths',
            'SegmentEditor_SparklineTooltipYears',
            'SegmentEditor_ManageSegmentsRealtimeNotice',
            'SegmentEditor_ManageSegmentsRealtimeNoDataTooltip',
            'General_Segment',
            'General_SegmentDocumentation',
            'General_ColumnEvolutionVisits',
            'General_ColumnEvolutionVisitsDocumentation',
            'General_ColumnNbVisits',
            'General_ColumnNbVisitsDocumentation',
            'General_ColumnNbActions',
            'General_ColumnNbActionsDocumentation',
            'General_Search',
            'General_SearchNoResults',
        );
        $translations = array();
        foreach ($translationKeys as $key) {
            $translations[$key] = Piwik::translate($key);
        }
        return $translations;
    }
}
