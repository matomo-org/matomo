<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime;

use Piwik\Plugins\VisitTime\Reports\GetVisitInformationPerLocalTime;
use Piwik\Plugins\VisitTime\Reports\GetVisitInformationPerServerTime;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@VisitTime/index');
        $view->dataTableVisitInformationPerLocalTime = $this->renderReport(new GetVisitInformationPerLocalTime());
        $view->dataTableVisitInformationPerServerTime = $this->renderReport(new GetVisitInformationPerServerTime());
        return $view->render();
    }
}
