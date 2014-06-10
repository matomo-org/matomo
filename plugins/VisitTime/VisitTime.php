<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar;
use Piwik\Site;

/**
 *
 */
class VisitTime extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Goals.getReportsWithGoalMetrics' => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'ViewDataTable.configure'         => 'configureViewDataTable',
            'ViewDataTable.getDefaultType'    => 'getDefaultTypeViewDataTable'
        );
        return $hooks;
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'          => Piwik::translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik::translate('VisitTime_WidgetLocalTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerLocalTime',
            'dimension'         => Piwik::translate('VisitTime_ColumnLocalTime'),
            'documentation'     => Piwik::translate('VisitTime_WidgetLocalTimeDocumentation', array('<strong>', '</strong>')),
            'constantRowsCount' => true,
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik::translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik::translate('VisitTime_WidgetServerTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerServerTime',
            'dimension'         => Piwik::translate('VisitTime_ColumnServerTime'),
            'documentation'     => Piwik::translate('VisitTime_WidgetServerTimeDocumentation', array('<strong>', '</strong>')),
            'constantRowsCount' => true,
            'order'             => 15,
        );

        $reports[] = array(
            'category'          => Piwik::translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik::translate('VisitTime_VisitsByDayOfWeek'),
            'module'            => 'VisitTime',
            'action'            => 'getByDayOfWeek',
            'dimension'         => Piwik::translate('VisitTime_DayOfWeek'),
            'documentation'     => Piwik::translate('VisitTime_WidgetByDayOfWeekDocumentation'),
            'constantRowsCount' => true,
            'order'             => 25,
        );
    }

    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions[] = array('category' => Piwik::translate('VisitTime_ColumnServerTime'),
                              'name'     => Piwik::translate('VisitTime_ColumnServerTime'),
                              'module'   => 'VisitTime',
                              'action'   => 'getVisitInformationPerServerTime',
        );
    }

    public function getSegmentsMetadata(&$segments)
    {
        $acceptedValues = "0, 1, 2, 3, ..., 20, 21, 22, 23";
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => Piwik::translate('VisitTime_ColumnServerTime'),
            'segment'        => 'visitServerHour',
            'sqlSegment'     => 'HOUR(log_visit.visit_last_action_time)',
            'acceptedValues' => $acceptedValues
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => Piwik::translate('VisitTime_ColumnLocalTime'),
            'segment'        => 'visitLocalHour',
            'sqlSegment'     => 'HOUR(log_visit.visitor_localtime)',
            'acceptedValues' => $acceptedValues
        );
    }

    public function getDefaultTypeViewDataTable(&$defaultViewTypes)
    {
        $defaultViewTypes['VisitTime.getVisitInformationPerServerTime'] = Bar::ID;
        $defaultViewTypes['VisitTime.getVisitInformationPerLocalTime']  = Bar::ID;
        $defaultViewTypes['VisitTime.getByDayOfWeek']                   = Bar::ID;
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'VisitTime.getVisitInformationPerServerTime':
                $this->setBasicConfigViewProperties($view);
                $this->configureViewForVisitInformationPerServerTime($view);
                break;
            case 'VisitTime.getVisitInformationPerLocalTime':
                $this->setBasicConfigViewProperties($view);
                $this->configureViewForVisitInformationPerLocalTime($view);
                break;
            case 'VisitTime.getByDayOfWeek':
                $this->setBasicConfigViewProperties($view);
                $this->configureViewForByDayOfWeek($view);
                break;
        }
    }

    protected function configureViewForVisitInformationPerServerTime(ViewDataTable $view)
    {
        $view->requestConfig->filter_limit = 24;
        $view->requestConfig->request_parameters_to_modify['hideFutureHoursWhenToday'] = 1;

        $view->config->show_goals = true;
        $view->config->addTranslation('label', Piwik::translate('VisitTime_ColumnServerTime'));

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = false;
        }
    }

    protected function configureViewForVisitInformationPerLocalTime(ViewDataTable $view)
    {
        $view->requestConfig->filter_limit = 24;

        $view->config->title = Piwik::translate('VisitTime_ColumnLocalTime');
        $view->config->addTranslation('label', Piwik::translate('VisitTime_LocalTime'));

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = false;
        }

        // add the visits by day of week as a related report, if the current period is not 'day'
        if (Common::getRequestVar('period', 'day') != 'day') {
            $view->config->addRelatedReport('VisitTime.getByDayOfWeek', Piwik::translate('VisitTime_VisitsByDayOfWeek'));
        }

    }

    protected function configureViewForByDayOfWeek(ViewDataTable $view)
    {
        $view->requestConfig->filter_limit = 7;

        $view->config->enable_sort = false;
        $view->config->show_footer_message = Piwik::translate('General_ReportGeneratedFrom', self::getDateRangeForFooterMessage());
        $view->config->addTranslation('label', Piwik::translate('VisitTime_DayOfWeek'));

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = false;
            $view->config->show_all_ticks     = true;
        }
    }

    private static function getDateRangeForFooterMessage()
    {
        // get query params
        $idSite = Common::getRequestVar('idSite', false);
        $date = Common::getRequestVar('date', false);
        $period = Common::getRequestVar('period', false);

        // create a period instance
        try {
            $oPeriod = Period\Factory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date);
        } catch (Exception $ex) {
            return ''; // if query params are incorrect, forget about the footer message
        }

        // set the footer message using the period start & end date
        $start = $oPeriod->getDateStart()->toString();
        $end = $oPeriod->getDateEnd()->toString();
        if ($start == $end) {
            $dateRange = $start;
        } else {
            $dateRange = $start . " &ndash; " . $end;
        }
        return $dateRange;
    }

    /**
     * @param ViewDataTable $view
     */
    private function setBasicConfigViewProperties(ViewDataTable $view)
    {
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order = 'asc';
        $view->requestConfig->addPropertiesThatShouldBeAvailableClientSide(array('filter_sort_column'));
        $view->config->show_search = false;
        $view->config->show_limit_control = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
    }
}
