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
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\BotTracking\Metrics;

/**
 * Bounded 0–100 index highlighting pages where human and AI-chatbot traffic diverge.
 *
 * Per row, with strong/weak being this report's own side (Human-Favoured: strong = human
 * pageviews, weak = AI requests; AI-Favoured: swapped):
 *
 *   lean   = max(0, (strong − weak) / (strong + weak))   // 0 = balanced or opposite, 1 = entirely one-sided
 *   volume = log10(strong + 1) / log10(maxStrong + 1)     // 0..1, anchored to the table's top page
 *   score  = round(100 * lean * volume, 1)                // 0..100
 *
 * maxStrong (the largest strong-side value in the table) is resolved once per table in
 * beforeCompute(), so the volume anchor auto-scales per report and per period without any
 * configuration constant. A page must be both strongly one-sided AND carry meaningful traffic
 * (relative to the busiest page) to score high; balanced or opposite-leaning pages score 0.
 */
class DiscrepancyScore extends ProcessedMetric
{
    public const VARIANT_HUMAN_FAVOURED = 'human_favoured';
    public const VARIANT_AI_FAVOURED    = 'ai_favoured';

    /** @var self::VARIANT_HUMAN_FAVOURED|self::VARIANT_AI_FAVOURED */
    private $variant;

    /**
     * Largest strong-side value in the table being processed; the volume anchor. Resolved in
     * beforeCompute(); 0 means "no usable anchor" (empty/all-zero table) → every score is 0.
     *
     * @var int
     */
    private $maxStrong = 0;

    public function __construct(string $variant)
    {
        if ($variant !== self::VARIANT_HUMAN_FAVOURED && $variant !== self::VARIANT_AI_FAVOURED) {
            throw new InvalidArgumentException(
                'Unknown DiscrepancyScore variant: ' . $variant
            );
        }
        $this->variant = $variant;
    }

    /**
     * The metric column whose magnitude this variant favours (human pageviews or AI requests).
     */
    private function getStrongColumn(): string
    {
        return $this->variant === self::VARIANT_HUMAN_FAVOURED
            ? Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS
            : Metrics::COLUMN_AI_CHATBOT_REQUESTS;
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

    /**
     * Resolves the per-table volume anchor (the largest strong-side value) once, before the
     * per-row compute() calls. Called per table, including per DataTable\Map child, so the score
     * self-calibrates to each report and period.
     */
    public function beforeCompute($report, DataTable $table)
    {
        $strongColumn    = $this->getStrongColumn();
        $this->maxStrong = 0;

        foreach ($table->getRows() as $row) {
            $value = $this->getMetric($row, $strongColumn);
            $value = is_numeric($value) ? (int) $value : 0;
            if ($value > $this->maxStrong) {
                $this->maxStrong = $value;
            }
        }

        return true;
    }

    public function compute(Row $row)
    {
        $rawHuman = $this->getMetric($row, Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS);
        $rawAi    = $this->getMetric($row, Metrics::COLUMN_AI_CHATBOT_REQUESTS);

        $human = is_numeric($rawHuman) ? (int) $rawHuman : 0;
        $ai    = is_numeric($rawAi)    ? (int) $rawAi    : 0;

        if ($this->variant === self::VARIANT_HUMAN_FAVOURED) {
            $strong = $human;
            $weak   = $ai;
        } else {
            $strong = $ai;
            $weak   = $human;
        }

        $total = $strong + $weak;
        if ($total <= 0) {
            return 0.0;
        }

        // How one-sided the page is (0 = balanced or leaning the other way, 1 = entirely one side).
        $lean = max(0, ($strong - $weak) / $total);

        // How significant the page's traffic is relative to the busiest page in this table.
        $anchor = log10($this->maxStrong + 1);
        $volume = $anchor > 0 ? log10($strong + 1) / $anchor : 0.0;

        return round(100 * $lean * $volume, 1);
    }

    /**
     * @param float|int $value
     * @return string
     */
    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyNumber((float) $value, 1);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_NUMBER;
    }
}
