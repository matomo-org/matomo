<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Tracker\Action;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        if ($action['type'] == Action::TYPE_EVENT) {
            $action['type'] = 'event';
            $action['icon'] = 'plugins/Morpheus/images/event.png';
        }
    }

    public function extendVisitorDetails(&$visitor)
    {
        $visitor['events'] = $this->details['visit_total_events'];
    }

    public function renderAction($action, $previousAction, $visitorDetails)
    {
        if ($action['type'] != 'event') {
            return;
        }

        $view                 = new View('@Events/_actionEvent.twig');
        $view->action         = $action;
        $view->previousAction = $previousAction;
        $view->visitInfo      = $visitorDetails;
        return $view->render();
    }
}