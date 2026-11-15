<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Http\BadRequestException;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Segment\SegmentExpression;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\EntryPage;
use Piwik\Tracker\Visitor;

/**
 * Whether the visitor had given their consent during this visit.
 *
 * Consent is reported per request, but it describes the visitor rather than the request, so it is
 * recorded once for the visit and kept. A visit with no value never reported consent, which is not
 * the same as refusing it: a site that does not signal consent at all looks exactly the same.
 *
 * The consentState segment compares the state name, with unknown as an empty string, so that a visit without
 * consent can be matched as unknown and an empty segment value (consentState==) matches unknown as it does for
 * any other segment. A refusal is not recorded, so it is not offered as a state of its own.
 */
class Consent extends VisitDimension
{
    public const COLUMN_TYPE = 'TINYINT(1) UNSIGNED NULL';
    public const CONSENT_GIVEN = 1;

    private const SEGMENT_VALUES = [
        'consented' => 'consented',
        'unknown' => '',
    ];

    protected $columnName = 'consent';
    protected $columnType = self::COLUMN_TYPE;
    protected $segmentName = 'consentState';
    protected $nameSingular = 'CoreHome_ConsentState';
    protected $sqlSegment = "CASE log_visit.consent WHEN 1 THEN 'consented' ELSE '' END";
    protected $acceptValues = 'consented, unknown';
    protected $type = self::TYPE_ENUM;

    public function __construct()
    {
        $this->sqlFilter = function ($value, $sqlSegment, $matchType) {
            $supportedMatchTypes = [
                SegmentExpression::MATCH_EQUAL,
                SegmentExpression::MATCH_NOT_EQUAL,
            ];

            if (!in_array($matchType, $supportedMatchTypes, true)) {
                throw new BadRequestException("This match type $matchType is not available for the consentState segment.");
            }

            // anything but an exact state name maps to no state, so == matches no visit
            return self::SEGMENT_VALUES[$value] ?? null;
        };
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        // do not generate any metric for this
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        if ($value === self::CONSENT_GIVEN || $value === '1' || $value === 'consented') {
            return Piwik::translate('CoreHome_ConsentStateConsented');
        } elseif ($value === null || $value === '' || $value === 0 || $value === '0' || $value === 'unknown') {
            return Piwik::translate('General_Unknown');
        }

        return $value;
    }

    public function getEnumColumnValues()
    {
        return array_flip(self::SEGMENT_VALUES);
    }

    /**
     * @param Action|null $action
     * @return int|false
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $request->hasConsent() ? self::CONSENT_GIVEN : false;
    }

    /**
     * @param Action|null $action
     * @return int|false
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if (self::hasVisitConsented($visitor) || !self::appliesToVisit($request, $visitor, $action)) {
            return false;
        }

        // consent given part way through a visit applies to the visit, so later requests do not
        // have to repeat the flag for it to be known
        return self::CONSENT_GIVEN;
    }

    /**
     * Whether the consent this request reports applies to the visit it is being recorded against.
     *
     * A request can report consent and still land on a visit that was measured before any was
     * given: a ping, for one, records no page and so cannot start a visit of its own. Nothing it
     * carries may be measured as a consented visitor's, because the visit it would be written to
     * is not that visitor's consented visit - the next request that can start one is. Marking it
     * otherwise would hand the earlier browsing the treatment consent buys, and leave the visit
     * reading as consented so that nothing split it afterwards.
     *
     * @param Action|null $action
     */
    public static function appliesToVisit(Request $request, Visitor $visitor, $action = null): bool
    {
        if (!$request->hasConsent()) {
            return false;
        }

        // Nothing is being withheld for consent to lift, so there is no earlier treatment to
        // keep this request away from. Ordinary tracking - a referrer detected part way through
        // a visit, say - must not be held back by a question that makes no difference here.
        if (!ConsentAwareAnalytics::hasRelaxableRestriction((int) $request->getIdSiteIfExists())) {
            return true;
        }

        if (self::hasVisitConsented($visitor)) {
            return true;
        }

        // null means it could not be told, and the visit is left as it was
        return true === EntryPage::isRequestOnEntryPage($visitor, $action, $request);
    }

    /**
     * Consent reaching a visit that did not have it starts a new visit, unless the visitor is
     * still on the page they arrived on.
     *
     * A visit measured before consent was measured under a compliance policy's restrictions. Where
     * the visitor is still on their arrival page the same visit is continued and filled in, since
     * it is the same landing and the data for it is still in the request. Once they have moved on
     * the two are kept apart, and consent begins a visit of its own.
     */
    public function shouldForceNewVisit(Request $request, Visitor $visitor, ?Action $action = null)
    {
        if (!$request->hasConsent() || self::hasVisitConsented($visitor)) {
            return false;
        }

        // Splitting the visit is what keeps browsing measured under a restriction apart from
        // browsing measured without it. Where no such restriction is in force there is nothing to
        // keep apart, and splitting would only count one visitor as two.
        if (!ConsentAwareAnalytics::hasRelaxableRestriction((int) $request->getIdSiteIfExists())) {
            return false;
        }

        // null means it could not be told, and a visit is not split on a guess
        return false === EntryPage::isRequestOnEntryPage($visitor, $action, $request);
    }

    /**
     * Whether consent was already recorded for this visit, as stored before the current request.
     */
    public static function hasVisitConsented(Visitor $visitor): bool
    {
        return self::CONSENT_GIVEN === (int) $visitor->getImmutableVisitorColumn('consent');
    }
}
