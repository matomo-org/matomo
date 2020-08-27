<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Actions\Actions;

use Piwik\Common;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit;

/**
 * This class represents an outlink.
 * This is a particular type of Action: it has no 'name'
 *
 */
class ActionClickUrl extends Action
{
    public function __construct(Request $request)
    {
        parent::__construct(self::TYPE_OUTLINK, $request);
        $this->setActionUrlWithoutExcludingParameters($request->getParam('link'));
    }

    public static function shouldHandle(Request $request)
    {
        $outlinkUrl = $request->getParam('link');

        return !empty($outlinkUrl);
    }

    protected function getActionsToLookup()
    {
        return array(
            // Note: we do not normalize outlink URL
            'idaction_url' => array($this->getActionUrl(), $this->getActionType())
        );
    }

    public function writeDebugInfo()
    {
        parent::writeDebugInfo();

        if ($this->detectActionIsOutlinkOnAliasHost($this, $this->request->getIdSite())) {
            Common::printDebug("INFO: The outlink URL host is one of the known host for this website. ");
        }
    }

    /**
     * Detect whether action is an outlink given host aliases
     *
     * @param Action $action
     * @return bool true if the outlink the visitor clicked on points to one of the known hosts for this website
     */
    protected function detectActionIsOutlinkOnAliasHost(Action $action, $idSite)
    {
        $decodedActionUrl = $action->getActionUrl();
        $actionUrlParsed  = @parse_url($decodedActionUrl);

        if (!isset($actionUrlParsed['host'])) {
            return false;
        }

        return Visit::isHostKnownAliasHost($actionUrlParsed['host'], $idSite);
    }
}
