<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Tracker\Action;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function renderAction($action, $previousAction, $visitorDetails)
    {
        if ($action['type'] != Action::TYPE_CONTENT) {
            return;
        }

        $view                 = new View('@Contents/_actionContent.twig');
        $view->action         = $action;
        $view->previousAction = $previousAction;
        $view->visitInfo      = $visitorDetails;
        return $view->render();
    }

    public function renderActionTooltip($action, $visitInfo)
    {
        if ($action['type'] != Action::TYPE_CONTENT) {
            return;
        }

        $view         = new View('@Contents/_actionTooltip');
        $view->action = $action;
        return $view->render();
    }
}