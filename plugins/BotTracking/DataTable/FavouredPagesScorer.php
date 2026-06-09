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
 * table.
 *
 * The score is deliberately computed here (once, over the full table) rather than as a
 * ProcessedMetric: it is table-relative (the `volume` term is anchored to the busiest page's
 * strong-side value), and a ProcessedMetric is recomputed at display time after row-deleting
 * filters such as ExcludeLowPopulation have run — which would shrink the anchor and corrupt the
 * scores, and leaves the column absent when the exclusion filter itself runs (emptying the report).
 * Writing a stable column up front lets the standard sort and ExcludeLowPopulation filters operate
 * on it deterministically.
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
     * `DataTable\Map` children so each period self-calibrates).
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

        // Volume anchor: the busiest page's strong-side value across the whole table.
        $maxStrong = 0;
        foreach ($table->getRows() as $row) {
            $value = (int) $row->getColumn($strongColumn);
            if ($value > $maxStrong) {
                $maxStrong = $value;
            }
        }

        foreach ($table->getRows() as $row) {
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
