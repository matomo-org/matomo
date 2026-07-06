/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * One metric shown on a sparkline card. Plain metrics arrive as raw numbers and are locale-formatted
 * at render time; processed metrics and revenue arrive as already-formatted strings.
 */
export interface SparklineMetric {
  value: string | number;
  description: string;
  column?: string;
  // Clean column name (eg "Bounce Rate"), used as the card title; falls back to `description`.
  title?: string;
}

/**
 * Evolution shown next to a sparkline. Attached at the entry level by
 * Sparklines\Config::addSparkline() (only when the report supplies evolution data),
 * and maps directly onto the EvolutionBadge props.
 */
export interface SparklineEvolution {
  // pre-formatted percent string emitted by the backend (eg "+5.2%", "-4%")
  percent: string | number;
  isLowerValueBetter: boolean;
  tooltip: string | null;
  // raw value difference (currentValue - pastValue), drives the arrow direction
  trend: number;
}

/**
 * A single sparkline entry as produced by Sparklines\Config::getSortedSparklines().
 * In no-comparison mode the metrics live under the '' group key, and `title` /
 * `seriesIndices` are null.
 */
export interface SparklineEntry {
  url: string;
  tooltip?: string;
  metrics: Record<string, SparklineMetric[]>;
  order: number;
  title: string | null;
  group: string;
  seriesIndices: number[] | null;
  graphParams: Record<string, unknown> | null;
  evolution?: SparklineEvolution;
}
