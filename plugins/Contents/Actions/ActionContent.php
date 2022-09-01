<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Contents\Actions;

use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

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

}
