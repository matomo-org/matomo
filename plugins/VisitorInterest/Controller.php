<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\View;

/**
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
        return $view->render();
    }

    public function getNumberOfVisitsPerVisitDuration()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getNumberOfVisitsPerPage()
    {
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Returns a report that lists the count of visits for different ranges of
     * a visitor's visit number.
     *
     * @return string The rendered report or nothing if $fetch is set to false.
     */
    public function getNumberOfVisitsByVisitCount()
    {
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Returns a rendered report that lists the count of visits for different ranges
     * of days since a visitor's last visit.
     *
     * @return string The rendered report or nothing if $fetch is set to false.
     */
    public function getNumberOfVisitsByDaysSinceLast()
    {
        return $this->renderReport(__FUNCTION__);
    }
}
