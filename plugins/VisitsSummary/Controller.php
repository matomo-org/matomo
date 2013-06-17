<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitsSummary
 */

/**
 *
 * @package Piwik_VisitsSummary
 */
class Piwik_VisitsSummary_Controller extends Piwik_Controller
{
    public function index()
    {
        $view = Piwik_View::factory('index');
        $this->setPeriodVariablesView($view);
        $view->graphEvolutionVisitsSummary = $this->getEvolutionGraph(true, array('nb_visits'));
        $this->setSparklinesAndNumbers($view);
        echo $view->render();
    }

    public function getSparklines()
    {
        $view = Piwik_View::factory('sparklines');
        $this->setPeriodVariablesView($view);
        $this->setSparklinesAndNumbers($view);
        echo $view->render();
    }

    public function getEvolutionGraph($fetch = false, array $columns = array())
    {
        if (empty($columns)) {
            $columns = Piwik_Common::getRequestVar('columns');
            $columns = Piwik::getArrayFromApiParameter($columns);
        }

        $documentation = Piwik_Translate('VisitsSummary_VisitsSummaryDocumentation') . '<br />'
            . Piwik_Translate('General_BrokenDownReportDocumentation') . '<br /><br />'

            . '<b>' . Piwik_Translate('General_ColumnNbVisits') . ':</b> '
            . Piwik_Translate('General_ColumnNbVisitsDocumentation') . '<br />'

            . '<b>' . Piwik_Translate('General_ColumnNbUniqVisitors') . ':</b> '
            . Piwik_Translate('General_ColumnNbUniqVisitorsDocumentation') . '<br />'

            . '<b>' . Piwik_Translate('General_ColumnNbActions') . ':</b> '
            . Piwik_Translate('General_ColumnNbActionsDocumentation') . '<br />'

            . '<b>' . Piwik_Translate('General_ColumnActionsPerVisit') . ':</b> '
            . Piwik_Translate('General_ColumnActionsPerVisitDocumentation');

        $selectableColumns = array(
            // columns from VisitsSummary.get
            'nb_visits',
            'nb_uniq_visitors',
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

        $idSite = Piwik_Common::getRequestVar('idSite');
        $displaySiteSearch = Piwik_Site::isSiteSearchEnabledFor($idSite);

        if ($displaySiteSearch) {
            $selectableColumns[] = 'nb_searches';
            $selectableColumns[] = 'nb_keywords';
        }
        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns,
            $selectableColumns, $documentation);

        return $this->renderView($view, $fetch);
    }

    static public function getVisitsSummary()
    {
        $requestString = "method=VisitsSummary.get" .
            "&format=original" .
            // we disable filters for example "search for pattern", in the case this method is called
            // by a method that already calls the API with some generic filters applied
            "&disable_generic_filters=1";
        $request = new Piwik_API_Request($requestString);
        $result = $request->process();
        return empty($result) ? new Piwik_DataTable() : $result;
    }

    static public function getVisits()
    {
        $requestString = "method=VisitsSummary.getVisits" .
            "&format=original" .
            "&disable_generic_filters=1";
        $request = new Piwik_API_Request($requestString);
        return $request->process();
    }

    protected function setSparklinesAndNumbers($view)
    {
        $view->urlSparklineNbVisits = $this->getUrlSparkline('getEvolutionGraph', array('columns' => $view->displayUniqueVisitors ? array('nb_visits', 'nb_uniq_visitors') : array('nb_visits')));
        $view->urlSparklineNbPageviews = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_pageviews', 'nb_uniq_pageviews')));
        $view->urlSparklineNbDownloads = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_downloads', 'nb_uniq_downloads')));
        $view->urlSparklineNbOutlinks = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_outlinks', 'nb_uniq_outlinks')));
        $view->urlSparklineAvgVisitDuration = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('avg_time_on_site')));
        $view->urlSparklineMaxActions = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('max_actions')));
        $view->urlSparklineActionsPerVisit = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_actions_per_visit')));
        $view->urlSparklineBounceRate = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('bounce_rate')));
		$view->urlSparklineAvgGenerationTime = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('avg_time_generation')));

        $idSite = Piwik_Common::getRequestVar('idSite');
        $displaySiteSearch = Piwik_Site::isSiteSearchEnabledFor($idSite);
        if ($displaySiteSearch) {
            $view->urlSparklineNbSearches = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_searches', 'nb_keywords')));
        }
        $view->displaySiteSearch = $displaySiteSearch;

        $dataTableVisit = self::getVisitsSummary();
        $dataRow = $dataTableVisit->getRowsCount() == 0 ? new Piwik_DataTable_Row() : $dataTableVisit->getFirstRow();

        $dataTableActions = Piwik_Actions_API::getInstance()->get($idSite, Piwik_Common::getRequestVar('period'), Piwik_Common::getRequestVar('date'), Piwik_ViewDataTable::getRawSegmentFromRequest());
        $dataActionsRow =
            $dataTableActions->getRowsCount() == 0 ? new Piwik_DataTable_Row() : $dataTableActions->getFirstRow();

        $view->nbUniqVisitors = (int)$dataRow->getColumn('nb_uniq_visitors');
        $nbVisits = (int)$dataRow->getColumn('nb_visits');
        $view->nbVisits = $nbVisits;
        $view->nbPageviews = (int)$dataActionsRow->getColumn('nb_pageviews');
        $view->nbUniquePageviews = (int)$dataActionsRow->getColumn('nb_uniq_pageviews');
        $view->nbDownloads = (int)$dataActionsRow->getColumn('nb_downloads');
        $view->nbUniqueDownloads = (int)$dataActionsRow->getColumn('nb_uniq_downloads');
        $view->nbOutlinks = (int)$dataActionsRow->getColumn('nb_outlinks');
        $view->nbUniqueOutlinks = (int)$dataActionsRow->getColumn('nb_uniq_outlinks');
        $view->averageVisitDuration = $dataRow->getColumn('avg_time_on_site');
        $nbBouncedVisits = $dataRow->getColumn('bounce_count');
        $view->bounceRate = Piwik::getPercentageSafe($nbBouncedVisits, $nbVisits);
        $view->maxActions = (int)$dataRow->getColumn('max_actions');
        $view->nbActionsPerVisit = $dataRow->getColumn('nb_actions_per_visit');
		$view->averageGenerationTime = $dataActionsRow->getColumn('avg_time_generation');

        if ($displaySiteSearch) {
            $view->nbSearches = (int)$dataActionsRow->getColumn('nb_searches');
            $view->nbKeywords = (int)$dataActionsRow->getColumn('nb_keywords');
        }

        // backward compatibility:
        // show actions if the finer metrics are not archived
        $view->showOnlyActions = false;
        if ($dataActionsRow->getColumn('nb_pageviews')
            + $dataActionsRow->getColumn('nb_downloads')
            + $dataActionsRow->getColumn('nb_outlinks') == 0
            && $dataRow->getColumn('nb_actions') > 0
        ) {
            $view->showOnlyActions = true;
            $view->nbActions = $dataRow->getColumn('nb_actions');
            $view->urlSparklineNbActions = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_actions')));
        }
    }
}
