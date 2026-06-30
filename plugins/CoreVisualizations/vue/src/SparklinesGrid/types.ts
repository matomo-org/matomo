/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * One metric shown on a sparkline card. Values are already locale-formatted by the
 * backend (Sparklines visualization), so they are rendered as-is.
 */
export interface SparklineMetric {
  value: string | number;
  description: string;
  column?: string;
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
}
