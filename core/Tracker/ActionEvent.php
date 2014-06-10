<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Tracker;

/**
 * An Event is composed of a URL, a Category name, an Action name, and optionally a Name and Value.
 *
 */
class ActionEvent extends Action
{
    function __construct($eventCategory, $eventAction, $url, Request $request)
    {
        parent::__construct(Action::TYPE_EVENT, $request);
        $this->setActionUrl($url);
        $this->eventCategory = trim($eventCategory);
        $this->eventAction = trim($eventAction);
        $this->eventName = trim($request->getParam('e_n'));
        $this->eventValue = trim($request->getParam('e_v'));
    }

    function getCustomFloatValue()
    {
        return $this->eventValue;
    }

    protected function getActionsToLookup()
    {
        $actions = array(
            'idaction_url' => $this->getUrlAndType()
        );

        if(strlen($this->eventName) > 0) {
            $actions['idaction_name'] = array($this->eventName, Action::TYPE_EVENT_NAME);
        }
        if(strlen($this->eventCategory) > 0) {
            $actions['idaction_event_category'] = array($this->eventCategory, Action::TYPE_EVENT_CATEGORY);
        }
        if(strlen($this->eventAction) > 0) {
            $actions['idaction_event_action'] = array($this->eventAction, Action::TYPE_EVENT_ACTION);
        }
        return $actions;
    }

    // Do not track this Event URL as Entry/Exit Page URL (leave the existing entry/exit)
    public function getIdActionUrlForEntryAndExitIds()
    {
        return false;
    }

    // Do not track this Event Name as Entry/Exit Page Title (leave the existing entry/exit)
    public function getIdActionNameForEntryAndExitIds()
    {
        return false;
    }

    public function writeDebugInfo()
    {
        $write = parent::writeDebugInfo();
        if($write) {
            Common::printDebug("Event Category = " . $this->eventCategory . ",
                Event Action = " .  $this->eventAction . ",
                Event Name =  " . $this->eventName . ",
                Event Value = " . $this->getCustomFloatValue());
        }
        return $write;
    }

}
