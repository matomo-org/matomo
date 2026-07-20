<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\DataTable;

use InvalidArgumentException;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Plugins\BotTracking\Columns\Metrics\DiscrepancyScore;
use Piwik\Plugins\BotTracking\Metrics;

/**
 * Materialises the bounded 0–100 Discrepancy Score as a real column on the merged favoured-pages
 * table. Invoked during archiving by {@see \Piwik\Plugins\BotTracking\RecordBuilders\AIChatbotFavouredPages}
 * — for the day record and, re-run, for each higher period's recomputed union.
 *
 * A stored column rather than a ProcessedMetric: the score is table-relative (the `volume` term is
 * anchored to the busiest page), so a ProcessedMetric recomputed at display time would see a shrunken
 * anchor once ExcludeLowPopulation has deleted rows (and be absent while that filter itself runs).
 *
 * @see DiscrepancyScore for the column metadata (label, documentation, formatting).
 */
class FavouredPagesScorer
{
    /** @var DiscrepancyScore::VARIANT_HUMAN_FAVOURED|DiscrepancyScore::VARIANT_AI_FAVOURED */
    private $variant;

    public function __construct(string $variant)
    {
        if (
            $variant !== DiscrepancyScore::VARIANT_HUMAN_FAVOURED
            && $variant !== DiscrepancyScore::VARIANT_AI_FAVOURED
        ) {
            throw new InvalidArgumentException('Unknown DiscrepancyScore variant: ' . $variant);
        }
        $this->variant = $variant;
    }

    /**
     * Adds the `discrepancy_score` column to every row of the table (recursing into
     * `DataTable\Map` children so each period self-calibrates), and marks the column 'skip' so it is
     * never summed into a summary row.
     *
     * @param DataTable|DataTable\Map $table
     */
    public function addScores(DataTableInterface $table): void
    {
        if ($table instanceof DataTable\Map) {
            foreach ($table->getDataTables() as $childTable) {
                $this->addScores($childTable);
            }
            return;
        }

        if (!$table instanceof DataTable) {
            return;
        }

        $strongColumn = $this->variant === DiscrepancyScore::VARIANT_HUMAN_FAVOURED
            ? Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS
            : Metrics::COLUMN_AI_CHATBOT_REQUESTS;

        // Volume anchor = the busiest page's strong-side value. Exclude the "Others" summary row,
        // whose aggregate tail would otherwise hijack the anchor and deflate every real score.
        $maxStrong = 0;
        foreach ($table->getRows() as $row) {
            if ($row->isSummaryRow()) {
                continue;
            }
            $value = (int) $row->getColumn($strongColumn);
            if ($value > $maxStrong) {
                $maxStrong = $value;
            }
        }

        foreach ($table->getRows() as $row) {
            // Leave the "Others" summary row unscored — a score over an aggregate of URLs is meaningless.
            if ($row->isSummaryRow()) {
                continue;
            }

            $human = (int) $row->getColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS);
            $ai    = (int) $row->getColumn(Metrics::COLUMN_AI_CHATBOT_REQUESTS);

            if ($this->variant === DiscrepancyScore::VARIANT_HUMAN_FAVOURED) {
                $strong = $human;
                $weak   = $ai;
            } else {
                $strong = $ai;
                $weak   = $human;
            }

            $row->setColumn(Metrics::COLUMN_DISCREPANCY_SCORE, self::score($strong, $weak, $maxStrong));
        }

        // Mark the score 'skip' so the Truncate filter leaves it out of the "Others" summary row it
        // builds while archiving — a score over an aggregate of URLs is meaningless. (Not serialised
        // with the blob, so readers re-apply 'skip' for the report totals row; see API.)
        $ops = $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        if (!is_array($ops)) {
            $ops = [];
        }
        $ops[Metrics::COLUMN_DISCREPANCY_SCORE] = 'skip';
        $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $ops);
    }

    /**
     * The bounded 0–100 score for one page:
     *   lean   = max(0, (strong − weak) / (strong + weak))    // 0 = balanced or opposite, 1 = entirely one-sided
     *   volume = log10(strong + 1) / log10(maxStrong + 1)      // 0..1, anchored to the busiest page
     *   score  = round(100 × lean × volume, 1)
     */
    public static function score(int $strong, int $weak, int $maxStrong): float
    {
        $total = $strong + $weak;
        if ($total <= 0) {
            return 0.0;
        }

        $lean = max(0, ($strong - $weak) / $total);

        $anchor = log10($maxStrong + 1);
        $volume = $anchor > 0 ? log10($strong + 1) / $anchor : 0.0;

        return round(100 * $lean * $volume, 1);
    }
}
