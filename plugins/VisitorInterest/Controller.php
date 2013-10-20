<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package VisitorInterest
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\View;
use Piwik\ViewDataTable\Factory;

/**
 * @package VisitorInterest
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@VisitorInterest/index');
        $view->dataTableNumberOfVisitsPerVisitDuration = $this->getNumberOfVisitsPerVisitDuration(true);
        $view->dataTableNumberOfVisitsPerPage = $this->getNumberOfVisitsPerPage(true);
        $view->dataTableNumberOfVisitsByVisitNum = $this->getNumberOfVisitsByVisitCount(true);
        $view->dataTableNumberOfVisitsByDaysSinceLast = $this->getNumberOfVisitsByDaysSinceLast(true);
        echo $view->render();
    }

    public function getNumberOfVisitsPerVisitDuration($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getNumberOfVisitsPerPage($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
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
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
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
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }
}
