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

class Campaign extends Base
{
    protected $nameSingular = 'Referrers_ColumnCampaign';

    /**
     * If we should create a new visit when the campaign changes, check if the campaign info changed and if so
     * force the tracker to create a new visit.i
     *
     * @return bool
     */
    public function shouldForceNewVisit(Request $request, Visitor $visitor, ?Action $action = null)
    {
        if (TrackerConfig::getConfigValue('create_new_visit_when_campaign_changes', $request->getIdSiteIfExists()) != 1) {
            return false;
        }

        $information = $this->getReferrerInformationFromRequest($request, $visitor);

        // Consent reported while the visitor is still on the page they arrived on completes this
        // visit rather than starting another: the campaign it carries is the one they arrived
        // through, so it is not a different campaign to split the visit on. This asks what the
        // request reports rather than what the server honours, so that, where a policy masks
        // campaigns, a tracker reporting consent for a site that is not opted in does not split
        // the visit either.
        if ($this->withheldCampaignDescribesTheArrival($visitor, $information, $request, $action)) {
            return false;
        }

        // we force a new visit if the referrer is a campaign and it's different than the currently recorded referrer.
        // if the current referrer is 'direct entry', however, we assume the referrer information was sent in a later request, and
        // we just update the existing referrer information instead of creating a visit.
        if (
            $information['referer_type'] == Common::REFERRER_TYPE_CAMPAIGN
            && $this->isReferrerInformationNewForRequest($visitor, $information, $action, $request)
            && !$this->isCurrentReferrerDirectEntry($visitor)
        ) {
            Common::printDebug("Existing visit detected, but creating new visit because campaign information is different than last action.");

            return true;
        }

        return false;
    }
}
