<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Translation\Translator;

/**
 *
 */
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
    public function index()
    {
        $_GET['containerId'] = 'VisitOverviewWithGraph';

        return FrontController::getInstance()->fetchDispatch('CoreHome', 'renderWidgetContainer');
    }

    /**
     * @deprecated used to be a widgetized URL. There to not break widget URLs
     */
    public function getSparklines()
    {
        $_GET['forceView'] = '1';
        $_GET['viewDataTable'] = Sparklines::ID;

        return FrontController::getInstance()->fetchDispatch('VisitsSummary', 'get');
    }

    public function getEvolutionGraph()
    {
        $this->checkSitePermission();
        $columns = Common::getRequestVar('columns', false);
        if (false !== $columns) {
            $columns = Piwik::getArrayFromApiParameter($columns);
        }

        $documentation = $this->translator->translate('VisitsSummary_VisitsSummaryDocumentation') . '<br />'
            . $this->translator->translate('General_BrokenDownReportDocumentation') . '<br /><br />'

            . '<b>' . $this->translator->translate('General_ColumnNbVisits') . ':</b> '
            . $this->translator->translate('General_ColumnNbVisitsDocumentation') . '<br />'

            . '<b>' . $this->translator->translate('General_ColumnNbUniqVisitors') . ':</b> '
            . $this->translator->translate('General_ColumnNbUniqVisitorsDocumentation') . '<br />'

            . '<b>' . $this->translator->translate('General_ColumnNbActions') . ':</b> '
            . $this->translator->translate('General_ColumnNbActionsDocumentation') . '<br />'

            . '<b>' . $this->translator->translate('General_ColumnNbUsers') . ':</b> '
            . $this->translator->translate('General_ColumnNbUsersDocumentation') . ' (<a rel="noreferrer noopener" target="_blank" href="https://matomo.org/docs/user-id/">User ID</a>)<br />'

            . '<b>' . $this->translator->translate('General_ColumnActionsPerVisit') . ':</b> '
            . $this->translator->translate('General_ColumnActionsPerVisitDocumentation');

        $selectableColumns = array(
            // columns from VisitsSummary.get
            'nb_visits',
            'nb_uniq_visitors',
            'nb_users',
            'avg_time_on_site',
            'bounce_rate',
            'nb_actions_per_visit',
            'max_actions',
            'nb_visits_converted',
            // columns from Actions.get
            'nb_pageviews',
            'nb_uniq_pageviews',
            'nb_downloads',
            'nb_uniq_downloads',
            'nb_outlinks',
            'nb_uniq_outlinks',
            'avg_time_generation'
        );

        $currentPeriod = Common::getRequestVar('period', false);

        if (!SettingsPiwik::isUniqueVisitorsEnabled($currentPeriod)) {
            $selectableColumns = array_diff($selectableColumns, ['nb_uniq_visitors', 'nb_users']);
        }

        $displaySiteSearch = Site::isSiteSearchEnabledFor($this->idSite);

        if ($displaySiteSearch) {
            $selectableColumns[] = 'nb_searches';
            $selectableColumns[] = 'nb_keywords';
        }
        // $callingAction may be specified to distinguish between
        // "VisitsSummary_WidgetLastVisits" and "VisitsSummary_WidgetOverviewGraph"
        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns,
            $selectableColumns, $documentation);

        if (empty($view->config->columns_to_display)) {
            $view->config->columns_to_display = array('nb_visits');
        }

        return $this->renderView($view);
    }

    public static function getVisitsSummary()
    {
        $result = Request::processRequest("VisitsSummary.get", array(
            // we disable filters for example "search for pattern", in the case this method is called
            // by a method that already calls the API with some generic filters applied
            'disable_generic_filters' => 1,
            'columns' => false
        ));

        return empty($result) ? new DataTable() : $result;
    }
}
