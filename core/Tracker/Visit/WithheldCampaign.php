<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tracker\Visit;

use Piwik\Common;
use Piwik\Plugins\CoreHome\Columns\Consent;
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

/**
 * Whether a campaign the tracker withheld until consent describes the arrival of a visit.
 *
 * For use while tracking a request, by core and by plugins that record their own campaign
 * dimensions and need the same answer core decides on.
 */
class WithheldCampaign
{
    /**
     * Whether a campaign this request carries is the one the visit arrived through, revealed by
     * consent, rather than a different campaign reached later.
     *
     * Where the tracker withholds campaign parameters until consent, the request that opened the
     * visit carried none, so the visit was attributed to whatever was left - the referring site,
     * or a direct entry. Consent reported while the visitor is still on the page they arrived on
     * brings the campaign with it, and that campaign describes the same arrival, so it must not
     * split the visit. Callers decide for themselves whether the request carries a campaign.
     *
     * This reads the consent the request reports rather than asking whether the server honours
     * it, because a site admin can change the tracking code where only a superuser can opt the
     * site in, and splitting the visit over that disagreement would count one visitor as two.
     * Nothing may be relaxed on the strength of this alone. It is limited to the sites where the
     * campaign can have been withheld: those whose policy masks campaigns, and those that measure
     * consent-aware analytics. Elsewhere a site using requireConsent() reports consent on every
     * request, and a campaign it carries is an ordinary campaign change.
     *
     * @param Action|null $action the action this request records, if it records one
     */
    public static function revealsArrival(Request $request, Visitor $visitor, $action = null): bool
    {
        if ('1' !== $request->getParam('consent') || Consent::hasVisitConsented($visitor)) {
            return false;
        }

        if (
            !CampaignParameterValuesMasked::isEnabled((int) $request->getIdSiteIfExists())
            && !$request->measuresConsentAwareAnalytics()
        ) {
            return false;
        }

        // a visit already attributed to a campaign is completed through the placeholder rules
        // instead, and a second campaign over the first is a change rather than a completion
        if ($visitor->getImmutableVisitorColumn('referer_type') == Common::REFERRER_TYPE_CAMPAIGN) {
            return false;
        }

        return true === EntryPage::isRequestOnEntryPage($visitor, $action, $request);
    }
}
