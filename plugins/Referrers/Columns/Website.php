<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Common;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\TrackerConfig;
use Piwik\Tracker\Visitor;

class Website extends Base
{
    protected $type = self::TYPE_TEXT;
    protected $nameSingular = 'General_Website';

    public function shouldForceNewVisit(Request $request, Visitor $visitor, ?Action $action = null)
    {
        if (TrackerConfig::getConfigValue('create_new_visit_when_website_referrer_changes', $request->getIdSiteIfExists()) != 1) {
            return false;
        }

        $information = $this->getReferrerInformationFromRequest($request, $visitor);

        if (
            $information['referer_type'] == Common::REFERRER_TYPE_WEBSITE
            && $this->isReferrerInformationNew($visitor, $information)
        ) {
            Common::printDebug("Existing visit detected, but creating new visit because website referrer information is different than last action.");

            return true;
        }

        return false;
    }
}
