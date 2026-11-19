<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\Columns;

use Piwik\Plugins\CoreHome\Columns\Consent;
use Piwik\Plugins\Resolution\Resolution as ResolutionPlugin;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class Resolution extends VisitDimension
{
    protected $columnName = 'config_resolution';
    protected $columnType = 'VARCHAR(18) NULL';
    protected $acceptValues = '1280x1024, 800x600, etc.';
    protected $segmentName = 'resolution';
    protected $nameSingular = 'Resolution_ColumnResolution';
    protected $namePlural = 'Resolution_Resolutions';
    protected $type = self::TYPE_TEXT;

    /**
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        // in privacy compliance mode, we can't detect screen resolution unless the visit holds the
        // visitor's consent
        if (
            ResolutionPlugin::isScreenResolutionDetectionDisabledByCompliancePolicy(
                $request->getIdSiteIfExists(),
                Consent::visitHasConsent($request, $visitor)
            )
        ) {
            return Request::UNKNOWN_RESOLUTION;
        }

        $resolution = $request->getParam('res');

        if (!empty($resolution)) {
            return substr($resolution, 0, 9);
        }

        return $resolution;
    }

    /**
     * @param Action|null $action
     * @return mixed
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        // In case the value was initially unknown, update it from a subsequent action
        if ($visitor->getVisitorColumn($this->columnName) !== Request::UNKNOWN_RESOLUTION) {
            return false;
        }

        // where the policy withholds the resolution, only a request that consent applies to may
        // fill it in, so that a ping or later page reporting consent cannot fill in a visit
        // measured without it
        if (
            ResolutionPlugin::isScreenResolutionDetectionDisabledByCompliancePolicy($request->getIdSiteIfExists())
            && !$this->doesConsentApplyToVisit($request, $visitor, $action)
        ) {
            return false;
        }

        return $this->onNewVisit($request, $visitor, $action);
    }

    /**
     * Whether the visit holds the visitor's consent, or this request is the one that brings it to
     * the visit.
     *
     * The resolution of a consented visit stays open to be filled in from any later request while
     * it is unknown, as it is where no policy is enforced, so this does not stop at the request
     * that records the consent.
     *
     * Consent::appliesToVisit() only keeps a request away from a visit measured before consent
     * while ConsentAwareAnalytics::hasRelaxableRestriction() reports a restriction for the site,
     * which it does wherever the screen resolution is withheld.
     *
     * @param Action|null $action
     */
    private function doesConsentApplyToVisit(Request $request, Visitor $visitor, $action): bool
    {
        return Consent::hasVisitConsented($visitor) || Consent::appliesToVisit($request, $visitor, $action);
    }
}
