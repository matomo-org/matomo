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
    const EVENT_VALUE_PRECISION = 3;

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        if (!empty($action['eventType'])) {
            $action['type'] = 'event';
            $action['icon'] = 'plugins/Morpheus/images/event.png';

            if (strlen($action['pageTitle']) > 0) {
                $action['eventName'] = $action['pageTitle'];
            }

            if (isset($action['custom_float']) && strlen($action['custom_float']) > 0) {
                $action['eventValue'] = round($action['custom_float'], self::EVENT_VALUE_PRECISION);
            }

            unset($action['pageTitle']);
            unset($action['custom_float']);
        } else {
            unset($action['eventCategory']);
            unset($action['eventAction']);
        }
        unset($action['eventType']);
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


    public function initProfile($visits, &$profile)
    {
        $profile['totalEvents'] = 0;
    }

    public function handleProfileAction($action, &$profile)
    {
        if ($action['type'] != 'event') {
            return;
        }
        $profile['totalEvents']++;
    }
}