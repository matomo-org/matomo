<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Events;

use Piwik\Piwik;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    const EVENT_VALUE_PRECISION = 3;

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        if (!empty($action['eventType'])) {
            $action['type'] = 'event';
            $action['icon'] = 'plugins/Morpheus/images/event.png';
            $action['iconSVG'] = 'plugins/Morpheus/images/event.svg';
            $action['title'] = Piwik::translate('Events_Event');
            $action['subtitle'] = Piwik::translate('Events_Category') . ': "' . $action['eventCategory'] . "'";

            if (!empty($action['eventName'])) {
                $action['subtitle'] .= ', ' . Piwik::translate('General_Name') . ': "' . $action['eventName'] . '"';
            }
            if (!empty($action['eventAction'])) {
                $action['subtitle'] .= ', ' . Piwik::translate('General_Action') . ': "' . $action['eventAction'] . '"';
            }
            if (!empty($action['eventValue'])) {
                $action['subtitle'] .= ', ' . Piwik::translate('General_Value') . ': "' . $action['eventValue'] . '"';
            }

            if (strlen(strval($action['pageTitle'])) > 0) {
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
        $view->sendHeadersWhenRendering = false;
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