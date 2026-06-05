<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Columns\Metrics;

use InvalidArgumentException;
use Piwik\Columns\Dimension;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\BotTracking\Metrics;

/**
 * Highlights pages where human and AI-chatbot traffic diverge.
 *
 * Variant selects which side the score favours:
 *  - VARIANT_HUMAN_FAVOURED: (human_pv / max(ai_req, 1)) * pow(log10(human_pv + 1), 2)
 *  - VARIANT_AI_FAVOURED:    (ai_req / max(human_pv, 1)) * pow(log10(ai_req + 1), 2)
 *
 * The squared log10 term emphasises absolute traffic so popular pages outrank
 * low-traffic outliers with an extreme ratio.
 */
class DiscrepancyScore extends ProcessedMetric
{
    public const VARIANT_HUMAN_FAVOURED = 'human_favoured';
    public const VARIANT_AI_FAVOURED    = 'ai_favoured';

    /** @var self::VARIANT_HUMAN_FAVOURED|self::VARIANT_AI_FAVOURED */
    private $variant;

    public function __construct(string $variant)
    {
        if ($variant !== self::VARIANT_HUMAN_FAVOURED && $variant !== self::VARIANT_AI_FAVOURED) {
            throw new InvalidArgumentException(
                'Unknown DiscrepancyScore variant: ' . $variant
            );
        }
        $this->variant = $variant;
    }

    public function getName()
    {
        return Metrics::COLUMN_DISCREPANCY_SCORE;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('BotTracking_ColumnDiscrepancyScore');
    }

    public function getDocumentation()
    {
        $key = $this->variant === self::VARIANT_HUMAN_FAVOURED
            ? 'BotTracking_ColumnDiscrepancyScoreHumanFavouredDocumentation'
            : 'BotTracking_ColumnDiscrepancyScoreAIFavouredDocumentation';

        return Piwik::translate($key);
    }

    public function getDependentMetrics()
    {
        return [
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS,
            Metrics::COLUMN_AI_CHATBOT_REQUESTS,
        ];
    }

    public function compute(Row $row)
    {
        $rawHuman = $this->getMetric($row, Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS);
        $rawAi    = $this->getMetric($row, Metrics::COLUMN_AI_CHATBOT_REQUESTS);

        $human = is_numeric($rawHuman) ? (int) $rawHuman : 0;
        $ai    = is_numeric($rawAi)    ? (int) $rawAi    : 0;

        if ($this->variant === self::VARIANT_HUMAN_FAVOURED) {
            $numerator   = $human;
            $denominator = max($ai, 1);
            $weightBase  = $human;
        } else {
            $numerator   = $ai;
            $denominator = max($human, 1);
            $weightBase  = $ai;
        }

        $score = ($numerator / $denominator) * pow(log10($weightBase + 1), 2);

        return round($score, 2);
    }

    /**
     * @param float|int $value
     * @return string
     */
    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyNumber((float) $value, 2);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_NUMBER;
    }
}
