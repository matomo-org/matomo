<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Tracker;

/**
 * This class represents a download or an outlink.
 * This is a particular type of Action: it has no 'name'
 *
 * @package Piwik\Tracker
 */
class ActionClickUrl extends Action
{
    function __construct($type, $url, Request $request)
    {
        parent::__construct($type, $request);
        $this->setActionUrl($url);
    }

    protected function getActionsToLookup()
    {
        return array(
            // Note: we do not normalize download/oulink URL
            'idaction_url' => array($this->getActionUrl(), $this->getActionType())
        );
    }

    function writeDebugInfo()
    {
        parent::writeDebugInfo();

        if (self::detectActionIsOutlinkOnAliasHost($this, $this->request->getIdSite())) {
            Common::printDebug("INFO: The outlink URL host is one of the known host for this website. ");
        }
    }

    /**
     * Detect whether action is an outlink given host aliases
     *
     * @param Action $action
     * @return bool true if the outlink the visitor clicked on points to one of the known hosts for this website
     */
    public static function detectActionIsOutlinkOnAliasHost(Action $action, $idSite)
    {
        if ($action->getActionType() != Action::TYPE_OUTLINK) {
            return false;
        }
        $decodedActionUrl = $action->getActionUrl();
        $actionUrlParsed = @parse_url($decodedActionUrl);
        if (!isset($actionUrlParsed['host'])) {
            return false;
        }
        return Visit::isHostKnownAliasHost($actionUrlParsed['host'], $idSite);
    }
}
