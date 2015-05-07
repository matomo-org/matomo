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

class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@VisitorInterest/index');
        $view->dataTableNumberOfVisitsPerVisitDuration = $this->renderReport('getNumberOfVisitsPerVisitDuration');
        $view->dataTableNumberOfVisitsPerPage = $this->renderReport('getNumberOfVisitsPerPage');
        $view->dataTableNumberOfVisitsByVisitNum = $this->renderReport('getNumberOfVisitsByVisitCount');
        $view->dataTableNumberOfVisitsByDaysSinceLast = $this->renderReport('getNumberOfVisitsByDaysSinceLast');
        return $view->render();
    }
}