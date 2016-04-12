<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Translation\Translator;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    public function index()
    {
        $view = new View('@VisitFrequency/index');
        $this->setGeneralVariablesView($view);

        $view->graphEvolutionVisitFrequency = $this->getEvolutionGraph(array(), array('nb_visits_returning'));
        $this->setSparklinesAndNumbers($view);

        return $view->render();
    }

    public function getSparklines()
    {
        $view = new View('@VisitFrequency/getSparklines');
        $this->setSparklinesAndNumbers($view);
        return $view->render();
    }

    public function getEvolutionGraph(array $columns = array(), array $defaultColumns = array())
    {
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }

        $documentation = $this->translator->translate('VisitFrequency_ReturningVisitsDocumentation') . '<br />'
            . $this->translator->translate('General_BrokenDownReportDocumentation') . '<br />'
            . $this->translator->translate('VisitFrequency_ReturningVisitDocumentation');

        // Note: if you edit this array, maybe edit the code below as well
        $selectableColumns = array(
            // columns from VisitFrequency.get
            'nb_visits_returning',
            'nb_actions_returning',
            'nb_actions_per_visit_returning',
            'bounce_rate_returning',
            'avg_time_on_site_returning',
            // columns from VisitsSummary.get
            'nb_visits',
            'nb_actions',
            'nb_actions_per_visit',
            'bounce_rate',
            'avg_time_on_site'
        );

        $period = Common::getRequestVar('period', false);

        if ($period == 'day') {
            // add number of unique (returning) visitors for period=day
            $selectableColumns = array_merge(
                array($selectableColumns[0]),
                array('nb_uniq_visitors_returning'),
                array_slice($selectableColumns, 1, -4),
                array('nb_uniq_visitors'),
                array_slice($selectableColumns, -4));
        }

        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns,
            $selectableColumns, $documentation);

        if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
            $view->config->columns_to_display = $defaultColumns;
        }

        return $this->renderView($view);
    }

    protected function setSparklinesAndNumbers($view)
    {
        $view->urlSparklineNbVisitsReturning = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_visits_returning')));
        $view->urlSparklineNbActionsReturning = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_actions_returning')));
        $view->urlSparklineActionsPerVisitReturning = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_actions_per_visit_returning')));
        $view->urlSparklineAvgVisitDurationReturning = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('avg_time_on_site_returning')));
        $view->urlSparklineBounceRateReturning = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('bounce_rate_returning')));

        $dataTableFrequency = $this->getSummary();
        $dataRow = $dataTableFrequency->getFirstRow();
        $nbVisitsReturning = $dataRow->getColumn('nb_visits_returning');
        $view->nbVisitsReturning = $nbVisitsReturning;
        $view->nbActionsReturning = $dataRow->getColumn('nb_actions_returning');
        $view->nbActionsPerVisitReturning = $dataRow->getColumn('nb_actions_per_visit_returning');
        $view->avgVisitDurationReturning = $dataRow->getColumn('avg_time_on_site_returning');
        $view->bounceRateReturning = $dataRow->getColumn('bounce_rate_returning');
    }

    protected function getSummary()
    {
        $requestString = "method=VisitFrequency.get&format=original";
        $request = new Request($requestString);
        return $request->process();
    }
}
