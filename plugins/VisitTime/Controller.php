<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitTime
 */

/**
 *
 * @package Piwik_VisitTime
 */
class Piwik_VisitTime_Controller extends Piwik_Controller
{
    public function index()
    {
        $view = Piwik_View::factory('index');
        $view->dataTableVisitInformationPerLocalTime = $this->getVisitInformationPerLocalTime(true);
        $view->dataTableVisitInformationPerServerTime = $this->getVisitInformationPerServerTime(true);
        echo $view->render();
    }

    public function getVisitInformationPerServerTime($fetch = false)
    {
        $view = $this->getGraph(__FUNCTION__, 'VisitTime.getVisitInformationPerServerTime',
            'VisitTime_ColumnServerTime');

        $view->setCustomParameter('hideFutureHoursWhenToday', 1);
        $view->enableShowGoals();

        return $this->renderView($view, $fetch);
    }

    public function getVisitInformationPerLocalTime($fetch = false)
    {
        $view = $this->getGraph(__FUNCTION__, 'VisitTime.getVisitInformationPerLocalTime',
            'VisitTime_ColumnLocalTime');

        // add the visits by day of week as a related report, if the current period is not 'day'
        if (Piwik_Common::getRequestVar('period', 'day') != 'day') {
            $view->addRelatedReports(Piwik_Translate('VisitTime_LocalTime'), array(
                                                                                  'VisitTime.getByDayOfWeek' => Piwik_Translate('VisitTime_VisitsByDayOfWeek')
                                                                             ));
        }

        return $this->renderView($view, $fetch);
    }

    public function getByDayOfWeek($fetch = false)
    {
        $view = $this->getGraph(
            __FUNCTION__, 'VisitTime.getByDayOfWeek', 'VisitTime_DayOfWeek', $limit = 7, $sort = false);
        $view->disableSort();

        if ($view instanceof Piwik_ViewDataTable_GenerateGraphHTML) {
            $view->showAllTicks();
        }

        // get query params
        $idsite = Piwik_Common::getRequestVar('idSite');
        $date = Piwik_Common::getRequestVar('date');
        $period = Piwik_Common::getRequestVar('period');

        // create a period instance
        $oSite = new Piwik_Site($idsite);
        $oPeriod = Piwik_Archive::makePeriodFromQueryParams($oSite, $period, $date);

        // set the footer message using the period start & end date
        $start = $oPeriod->getDateStart()->toString();
        $end = $oPeriod->getDateEnd()->toString();
        if ($start == $end) {
            $dateRange = $start;
        } else {
            $dateRange = $start . " &ndash; " . $end;
        }

        $view->setFooterMessage(Piwik_Translate('General_ReportGeneratedFrom', $dateRange));

        return $this->renderView($view, $fetch);
    }

    private function getGraph($controllerMethod, $apiMethod, $labelTranslation, $limit = 24)
    {
        $view = Piwik_ViewDataTable::factory('graphVerticalBar');
        $view->init($this->pluginName, $controllerMethod, $apiMethod);


        $view->setColumnTranslation('label', Piwik_Translate($labelTranslation));
        $view->setSortedColumn('label', 'asc');

        $view->setLimit($limit);
        $view->setGraphLimit($limit);
        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $view->disableOffsetInformationAndPaginationControls();
        $this->setMetricsVariablesView($view);

        return $view;
    }
}
