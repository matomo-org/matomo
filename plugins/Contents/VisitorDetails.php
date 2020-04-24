<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Tracker\Action;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        if ($action['type'] != Action::TYPE_CONTENT) {
            unset($action['contentName']);
            unset($action['contentPiece']);
            unset($action['contentTarget']);
            unset($action['contentInteraction']);
        }
    }

    public function renderAction($action, $previousAction, $visitorDetails)
    {
        if ($action['type'] != Action::TYPE_CONTENT) {
            return;
        }

        $view                 = new View('@Contents/_actionContent.twig');
        $view->sendHeadersWhenRendering = false;
        $view->action         = $action;
        $view->previousAction = $previousAction;
        $view->visitInfo      = $visitorDetails;
        return $view->render();
    }

    public function renderActionTooltip($action, $visitInfo)
    {
        if ($action['type'] != Action::TYPE_CONTENT) {
            return [];
        }

        $view         = new View('@Contents/_actionTooltip');
        $view->sendHeadersWhenRendering = false;
        $view->action = $action;
        return [[ 10, $view->render() ]];
    }
}