<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitorInterest
 */

/**
 * @package Piwik_VisitorInterest
 */
class Piwik_VisitorInterest_Controller extends Piwik_Controller
{
    function index()
    {
        $view = Piwik_View::factory('index');
        $view->dataTableNumberOfVisitsPerVisitDuration = $this->getNumberOfVisitsPerVisitDuration(true);
        $view->dataTableNumberOfVisitsPerPage = $this->getNumberOfVisitsPerPage(true);
        $view->dataTableNumberOfVisitsByVisitNum = $this->getNumberOfVisitsByVisitCount(true);
        $view->dataTableNumberOfVisitsByDaysSinceLast = $this->getNumberOfVisitsByDaysSinceLast(true);
        echo $view->render();
    }

    function getNumberOfVisitsPerVisitDuration($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory('cloud');
        $view->init($this->pluginName, __FUNCTION__, "VisitorInterest.getNumberOfVisitsPerVisitDuration");

        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setSortedColumn('label', 'asc');
        $view->setColumnTranslation('label', Piwik_Translate('VisitorInterest_ColumnVisitDuration'));
        $view->setGraphLimit(10);
        $view->disableSort();
        $view->disableExcludeLowPopulation();
        $view->disableOffsetInformationAndPaginationControls();
        $view->disableSearchBox();
        $view->disableShowAllColumns();

        return $this->renderView($view, $fetch);
    }

    function getNumberOfVisitsPerPage($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory('cloud');
        $view->init($this->pluginName, __FUNCTION__, "VisitorInterest.getNumberOfVisitsPerPage");
        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setSortedColumn('label', 'asc');
        $view->setColumnTranslation('label', Piwik_Translate('VisitorInterest_ColumnPagesPerVisit'));
        $view->setGraphLimit(10);
        $view->disableExcludeLowPopulation();
        $view->disableOffsetInformationAndPaginationControls();
        $view->disableSearchBox();
        $view->disableSort();
        $view->disableShowAllColumns();

        return $this->renderView($view, $fetch);
    }

    /**
     * Returns a report that lists the count of visits for different ranges of
     * a visitor's visit number.
     *
     * @param bool $fetch Whether to return the rendered view as a string or echo it.
     * @return string The rendered report or nothing if $fetch is set to false.
     */
    public function getNumberOfVisitsByVisitCount($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, "VisitorInterest.getNumberOfVisitsByVisitCount");
        $view->setColumnsToDisplay(array('label', 'nb_visits', 'nb_visits_percentage'));
        $view->setSortedColumn('label', 'asc');
        $view->setColumnTranslation('label', Piwik_Translate('VisitorInterest_VisitNum'));
        $view->setColumnTranslation('nb_visits_percentage', str_replace(' ', '&nbsp;', Piwik_Translate('General_ColumnPercentageVisits')));
        $view->disableExcludeLowPopulation();
        $view->disableOffsetInformationAndPaginationControls();
        $view->disableShowAllViewsIcons();
        $view->setLimit(15);
        $view->disableSearchBox();
        $view->disableSort();
        $view->disableShowAllColumns();

        return $this->renderView($view, $fetch);
    }

    /**
     * Returns a rendered report that lists the count of visits for different ranges
     * of days since a visitor's last visit.
     *
     * @param bool $fetch Whether to return the rendered view as a string or echo it.
     * @return string The rendered report or nothing if $fetch is set to false.
     */
    public function getNumberOfVisitsByDaysSinceLast($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'VisitorInterest.getNumberOfVisitsByDaysSinceLast');
        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setSortedColumn('label', 'asc');
        $view->setColumnTranslation('label', Piwik_Translate('General_DaysSinceLastVisit'));
        $view->disableExcludeLowPopulation();
        $view->disableOffsetInformationAndPaginationControls();
        $view->disableShowAllViewsIcons();
        $view->setLimit(15);
        $view->disableSearchBox();
        $view->disableSort();
        $view->disableShowAllColumns();

        return $this->renderView($view, $fetch);
    }
}
