<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Container\StaticContainer;
use Piwik\DeviceDetector\DeviceDetectorFactory;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreHome\Columns\Consent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

abstract class Base extends VisitDimension
{
    protected function getUAParser($userAgent, $clientHints)
    {
        return StaticContainer::get(DeviceDetectorFactory::class)->makeInstance($userAgent, $clientHints);
    }

    /**
     * Whether this request is the one that brings the visitor's consent to a visit measured
     * without it, so that what was withheld for the visit can be detected from it.
     *
     * This is the request on which the visit's consent is recorded. Later requests leave the
     * values it detected in place.
     *
     * Consent::appliesToVisit() only keeps a request away from a visit measured before consent
     * while ConsentAwareAnalytics::hasRelaxableRestriction() reports a restriction for the site.
     * The device settings are required by the same policy as campaign masking, which that check
     * looks at, so a site withholding device details is always one it reports.
     *
     * @param Action|null $action
     */
    protected function isFirstConsentedRequest(Request $request, Visitor $visitor, $action): bool
    {
        return !Consent::hasVisitConsented($visitor) && Consent::appliesToVisit($request, $visitor, $action);
    }
}
