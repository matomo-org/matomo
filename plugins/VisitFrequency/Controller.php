<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\Common;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
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

    /**
     * @deprecated used to be a widgetized URL. There to not break widget URLs
     */
    public function getSparklines()
    {
        $_GET['forceView'] = '1';
        $_GET['viewDataTable'] = Sparklines::ID;

        return FrontController::getInstance()->fetchDispatch('VisitFrequency', 'get');
    }

    public function getEvolutionGraph()
    {
        $columns = Common::getRequestVar('columns', false);
        if (false !== $columns) {
            $columns = Piwik::getArrayFromApiParameter($columns);
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

        if (empty($view->config->columns_to_display)) {
            $view->config->columns_to_display = array('nb_visits_returning');
        }

        return $this->renderView($view);
    }
}
