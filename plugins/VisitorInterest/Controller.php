<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\Common;
use Piwik\Plugin\Report;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $idSite = Common::getRequestVar('idSite', '', 'string');
        $numberOfVisitsByDaysSinceLast = Report::factory('VisitorInterest', 'getNumberOfVisitsByDaysSinceLast', $idSite);

        $view = new View('@VisitorInterest/index');
        $view->dataTableNumberOfVisitsPerVisitDuration = $this->renderReport('getNumberOfVisitsPerVisitDuration');
        $view->dataTableNumberOfVisitsPerPage = $this->renderReport('getNumberOfVisitsPerPage');
        $view->dataTableNumberOfVisitsByVisitNum = $this->renderReport('getNumberOfVisitsByVisitCount');
        $view->dataTableNumberOfVisitsByDaysSinceLast = $this->renderReport($numberOfVisitsByDaysSinceLast);
        $view->dataTableNumberOfVisitsByDaysSinceLastTitle = $numberOfVisitsByDaysSinceLast->getName();
        return $view->render();
    }
}