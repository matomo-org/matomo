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
        $byDimension = new View\ReportsByDimension('VisitorInterest');

        $reportsToAdd = array(
            new GetNumberOfVisitsPerVisitDuration(),
            new GetNumberOfVisitsPerPage(),
            new GetNumberOfVisitsByVisitCount(),
            new GetNumberOfVisitsByDaysSinceLast()
        );

        foreach ($reportsToAdd as $report) {
            /** @var \Piwik\Plugin\Report $report */
            $byDimension->addReport(
                $report->getCategory(),
                $report->getWidgetTitle(),
                $report->getModule() . '.' . $report->getAction(),
                array());
        }

        $view = new View('@VisitorInterest/index');
        $view->reports = $byDimension->render();
        return $view->render();
    }
}
