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
        $view->dataTableVisitInformationPerLocalTime = $this->getVisitInformationPerLocalTime(true);
        $view->dataTableVisitInformationPerServerTime = $this->getVisitInformationPerServerTime(true);
        return $view->render();
    }

    public function getVisitInformationPerServerTime()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getVisitInformationPerLocalTime()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getByDayOfWeek()
    {
        return $this->renderReport(__FUNCTION__);
    }
}
