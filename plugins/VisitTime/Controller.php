<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime;

use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@VisitTime/index');
        $view->dataTableVisitInformationPerLocalTime = $this->renderReport('getVisitInformationPerLocalTime');
        $view->dataTableVisitInformationPerServerTime = $this->renderReport('getVisitInformationPerServerTime');
        return $view->render();
    }
}
