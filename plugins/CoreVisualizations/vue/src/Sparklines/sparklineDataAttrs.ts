/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { MatomoUrl } from 'CoreHome';
import { SparklineEntry } from './types';

/**
 * The `data-graph-params` value for a sparkline's `.sparkline` wrapper, or null when none can be
 * derived. The legacy click-to-evolution wiring (window.initializeSparklines) reads it to open the
 * metric's evolution graph, so it must be set whenever the sparkline is linkable.
 *
 * Prefers the explicit backend `graphParams`; otherwise derives the reload params (columns/rows/
 * idGoal) from the url — the reused Sparkline renders the image with `src` (no `data-src`), so
 * sparkline.js can't read the columns off the img and we supply them here.
 *
 * Shared by SparklineCard and SegmentComparisonCard, where the whole card is one linkable
 * sparkline, so these attributes ride on the card root.
 */
export function sparklineGraphParamsAttr(entry: SparklineEntry): string | null {
  const { graphParams, url } = entry;

  if (graphParams && Object.keys(graphParams).length) {
    return JSON.stringify(graphParams);
  }

  if (url) {
    const parsed = MatomoUrl.parse(url.substring(url.indexOf('?') + 1));
    const derived: Record<string, unknown> = {};
    (['columns', 'rows', 'idGoal'] as const).forEach((key) => {
      if (parsed[key]) {
        derived[key] = parsed[key];
      }
    });

    if (Object.keys(derived).length) {
      return JSON.stringify(derived);
    }
  }

  return null;
}

/**
 * The `data-series-indices` value for a sparkline's `.sparkline` wrapper, or null when the entry
 * carries no series indices (no-comparison sparklines). Comparison entries set one index per series
 * so the evolution graph highlights the matching coloured line(s).
 */
export function sparklineSeriesIndicesAttr(entry: SparklineEntry): string | null {
  const { seriesIndices } = entry;
  return seriesIndices && seriesIndices.length ? JSON.stringify(seriesIndices) : null;
}
