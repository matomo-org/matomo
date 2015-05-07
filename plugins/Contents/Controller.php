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
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{

    public function index()
    {
        $reportsView = new View\ReportsByDimension('Contents');

        /** @var \Piwik\Plugin\Report[] $reports */
        $contentNames  = Report::factory($this->pluginName, 'getContentNames');
        $contentPieces = Report::factory($this->pluginName, 'getContentPieces');
        $reports = array($contentNames, $contentPieces);

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
        $report = Report::factory($this->pluginName, 'getContentNames');

        return View::singleReport($report->getName(), $report->render());
    }

    public function menuGetContentPieces()
    {
        $report = Report::factory($this->pluginName, 'getContentPieces');

        return View::singleReport($report->getName(), $report->render());
    }

}
