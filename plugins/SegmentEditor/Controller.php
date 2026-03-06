<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor;

use Piwik\DataTable\Filter\CalculateEvolutionFilter;
use Piwik\DataTable\Renderer\Json;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\SegmentEditor;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Plugins\VisitsSummary;
use Piwik\Request;
use Piwik\Url;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    private const POSITIVE = 'positive';
    private const NEGATIVE = 'negative';
    private const STABLE = 'stable';

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

    public function getSegmentData(): string
    {
        $segmentDefinition = Request::fromRequest()->getStringParameter('segmentDefinition', '');
        Json::sendHeaderJSON();

        try {
            $data = VisitsSummary\API::getInstance()
                ->get($this->idSite, $this->period, $this->strDate, $segmentDefinition)
                ->getFirstRow()->getArrayCopy();
            [$previousDate] = Range::getLastDate($this->strDate, $this->period);
            $pastNbVisits = VisitsSummary\API::getInstance()
                ->getVisits($this->idSite, $this->period, $previousDate, $segmentDefinition)
                ->getFirstRow()->getColumn('nb_visits');

            $nbVisits = (int)($data['nb_visits'] ?? 0);
            $nbActions = (int)($data['nb_actions'] ?? 0);
            $pastNbVisits = (int)$pastNbVisits;
            $evolutionDirection = $this->getEvolutionDirection($nbVisits, $pastNbVisits);

            return json_encode([
                'nb_visits' => $nbVisits,
                'nb_actions' => $nbActions,
                'evolution_visits_direction' => $evolutionDirection,
                'evolution_visits_icon' => $this->getEvolutionIcon($evolutionDirection),
                'evolution_visits' => CalculateEvolutionFilter::calculate($nbVisits, $pastNbVisits, 0, true, false),
            ]);
        } catch (\Throwable $e) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'SegmentEditor.getSegmentData failed (idSite: {idSite}, period: {period}, date: {date}, segmentDefinition: {segmentDefinition}): {exception}',
                [
                    'idSite' => $this->idSite,
                    'period' => $this->period,
                    'date' => $this->strDate,
                    'segmentDefinition' => $segmentDefinition,
                    'exception' => $e,
                ]
            );

            return json_encode([
                'result' => 'error',
                'message' => Piwik::translate('General_ErrorRequest'),
            ]);
        }
    }

    private function isRealtimeSegment(array $segment): bool
    {
        return !empty($segment['definition']) && empty((int)$segment['auto_archive']);
    }

    protected function getEvolutionDirection(int $currentValue, int $pastValue): string
    {
        if ($currentValue > $pastValue) {
            return self::POSITIVE;
        }

        if ($currentValue < $pastValue) {
            return self::NEGATIVE;
        }

        return self::STABLE;
    }

    /*
     * @param self::POSITIVE|self::NEGATIVE|self::STABLE $direction
     */
    protected function getEvolutionIcon(string $direction): string
    {
        if ($direction === self::POSITIVE) {
            return 'plugins/MultiSites/images/arrow_up.png';
        }

        if ($direction === self::NEGATIVE) {
            return 'plugins/MultiSites/images/arrow_down.png';
        }

        return 'plugins/MultiSites/images/stop.png';
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
