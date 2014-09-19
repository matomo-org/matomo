<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Contents\Actions;

use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker;

/**
 * A content is composed of a name, an actual piece of content, and optionally a target.
 */
class ActionContent extends Action
{
    public function __construct(Request $request)
    {
        parent::__construct(Action::TYPE_CONTENT, $request);

        $url = $request->getParam('url');
        $this->setActionUrl($url);
    }

    public static function shouldHandle(Request $request)
    {
        $name = $request->getParam('c_n');

        return !empty($name);
    }

    protected function getActionsToLookup()
    {
        return array(
            'idaction_url' => array($this->getActionUrl(), $this->getActionType())
        );
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
}
