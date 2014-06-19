<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\Plugins\VisitorInterest\Reports\GetNumberOfVisitsByDaysSinceLast;
use Piwik\Plugins\VisitorInterest\Reports\GetNumberOfVisitsByVisitCount;
use Piwik\Plugins\VisitorInterest\Reports\GetNumberOfVisitsPerPage;
use Piwik\Plugins\VisitorInterest\Reports\GetNumberOfVisitsPerVisitDuration;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@VisitorInterest/index');
        $view->dataTableNumberOfVisitsPerVisitDuration = $this->renderReport(new GetNumberOfVisitsPerVisitDuration());
        $view->dataTableNumberOfVisitsPerPage = $this->renderReport(new GetNumberOfVisitsPerPage());
        $view->dataTableNumberOfVisitsByVisitNum = $this->renderReport(new GetNumberOfVisitsByVisitCount());
        $view->dataTableNumberOfVisitsByDaysSinceLast = $this->renderReport(new GetNumberOfVisitsByDaysSinceLast());
        return $view->render();
    }
}
