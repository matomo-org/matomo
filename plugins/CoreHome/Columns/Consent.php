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
use Piwik\Segment\SegmentExpression;

/**
 * Whether the visitor had given their consent during the visit (1), or not (NULL).
 *
 * The consentState segment compares the state name, with unknown as an empty string, so that a visit without
 * consent can be matched as unknown and an empty segment value (consentState==) matches unknown as it does for
 * any other segment. A refusal is not recorded, so it is not offered as a state of its own.
 */
class Consent extends VisitDimension
{
    public const COLUMN_TYPE = 'TINYINT(1) UNSIGNED NULL';

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
        if ($value === 1 || $value === '1' || $value === 'consented') {
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
}
