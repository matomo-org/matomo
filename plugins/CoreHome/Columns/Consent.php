<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Container\StaticContainer;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\ConsentAwareAnalytics;
use Piwik\Segment\SegmentExpression;
use Piwik\Segment\SegmentsList;

/**
 * Whether the visitor consented (1), refused (0) or never reported a decision (NULL) during the visit.
 *
 * The consentState segment compares the state name, with unknown as an empty string, so that a visit without
 * a stored value can be matched as unknown and an empty segment value (consentState==) matches unknown as it does
 * for any other segment. As the expression is not a plain log_visit column, != on archived reports is resolved
 * through an idvisit subquery, like the other function-wrapped visit segments.
 *
 * The segment is only available while the ConsentAwareAnalytics feature flag is enabled.
 */
class Consent extends VisitDimension
{
    public const COLUMN_TYPE = 'TINYINT(1) UNSIGNED NULL';

    private const SEGMENT_VALUES = [
        'consented' => 'consented',
        'refused' => 'refused',
        'unknown' => '',
    ];

    protected $columnName = 'consent';
    protected $columnType = self::COLUMN_TYPE;
    protected $segmentName = 'consentState';
    protected $nameSingular = 'CoreHome_ConsentState';
    protected $sqlSegment = "CASE log_visit.consent WHEN 1 THEN 'consented' WHEN 0 THEN 'refused' ELSE '' END";
    protected $acceptValues = 'consented, refused, unknown';
    protected $type = self::TYPE_ENUM;

    public function __construct()
    {
        $this->sqlFilter = function ($value, $sqlSegment, $matchType) {
            $supportedMatchTypes = [
                SegmentExpression::MATCH_EQUAL,
                SegmentExpression::MATCH_NOT_EQUAL,
            ];

            if (!in_array($matchType, $supportedMatchTypes, true)) {
                throw new \Exception("This match type $matchType is not available for the consentState segment.");
            }

            // anything but an exact state name maps to no state, so == matches no visit
            return self::SEGMENT_VALUES[$value] ?? null;
        };
    }

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        if (!StaticContainer::get(FeatureFlagManager::class)->isFeatureActive(ConsentAwareAnalytics::class)) {
            return;
        }

        parent::configureSegments($segmentsList, $dimensionSegmentFactory);
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        if ($value === 1 || $value === '1' || $value === 'consented') {
            return Piwik::translate('CoreHome_ConsentStateConsented');
        } elseif ($value === 0 || $value === '0' || $value === 'refused') {
            return Piwik::translate('CoreHome_ConsentStateRefused');
        } elseif ($value === null || $value === '' || $value === 'unknown') {
            return Piwik::translate('General_Unknown');
        }

        return $value;
    }

    public function getEnumColumnValues()
    {
        return array_flip(self::SEGMENT_VALUES);
    }
}
