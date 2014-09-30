<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Plugin\Report;
use Piwik\Plugins\Contents\Reports\GetContentNames;
use Piwik\Plugins\Contents\Reports\GetContentPieces;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{

    public function index()
    {
        $reportsView = new View\ReportsByDimension('Contents');

        /** @var \Piwik\Plugin\Report[] $reports */
        $reports = array(new GetContentNames(), new GetContentPieces());

        foreach($reports as $report) {
            $reportsView->addReport(
                $report->getCategory(),
                $report->getName(),
                'Contents.' . Report::PREFIX_ACTION_IN_MENU . ucfirst($report->getAction())
            );
        }

        return $reportsView->render();
    }

    public function menuGetContentNames()
    {
        $report = new GetContentNames();

        return View::singleReport($report->getName(), $report->render());
    }

    public function menuGetContentPieces()
    {
        $report = new GetContentPieces();

        return View::singleReport($report->getName(), $report->render());
    }

}
