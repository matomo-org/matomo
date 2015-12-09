<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Events\Actions;

use Piwik\Common;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker;

/**
 * An Event is composed of a URL, a Category name, an Action name, and optionally a Name and Value.
 *
 */
class ActionEvent extends Action
{
    public function __construct(Request $request)
    {
        parent::__construct(Action::TYPE_EVENT, $request);

        $url = $request->getParam('url');

        $this->setActionUrl($url);
        $this->eventValue = trim($request->getParam('e_v'));
    }

    public static function shouldHandle(Request $request)
    {
        $eventCategory = $request->getParam('e_c');
        $eventAction   = $request->getParam('e_a');

        return (strlen($eventCategory) > 0 && strlen($eventAction) > 0);
    }

    public function getEventAction()
    {
        return $this->request->getParam('e_a');
    }

    public function getEventCategory()
    {
        return $this->request->getParam('e_c');
    }

    public function getEventName()
    {
        return $this->request->getParam('e_n');
    }

    public function getCustomFloatValue()
    {
        return $this->eventValue;
    }

    protected function getActionsToLookup()
    {
        $actionUrl = false;

        $url = $this->getActionUrl();

        if (!empty($url)) {
            // normalize urls by stripping protocol and www
            $url = Tracker\PageUrl::normalizeUrl($url);
            $actionUrl = array($url['url'], $this->getActionType(), $url['prefixId']);
        }

        return array('idaction_url' => $actionUrl);
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
        if ($write) {
            Common::printDebug("Event Value = " . $this->getCustomFloatValue());
        }
        return $write;
    }
}
