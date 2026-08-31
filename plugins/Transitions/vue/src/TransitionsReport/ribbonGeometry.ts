/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { TransitionsSide } from './types';

/** Smallest ribbon a row may get, so a row with a tiny share still reads as connected. */
export const MIN_RIBBON_THICKNESS = 3;

/** Absolute floor applied after the overflow rescale, so no ribbon collapses to nothing. */
const HAIRLINE_THICKNESS = 1;

export interface RibbonRow {
  key: string;
  /** 0..1. Negative and non-finite count as zero, so the row still gets a minimum band. */
  share: number;
  /** Both in the ribbon layer's coordinate space. */
  top: number;
  height: number;
}

export interface RibbonLayout {
  side: TransitionsSide;
  /** The gap between a side column and the center card. */
  width: number;
  /** Where the whole band meets the center card. */
  centerTop: number;
  centerHeight: number;
  /** How far the row end reaches back into the row, hiding the join. */
  rowOverlap?: number;
  /** How long the band holds the row's exact height before it starts curving. */
  rowStraight?: number;
}

export interface RibbonPath {
  key: string;
  d: string;
  thickness: number;
}

function sanitiseShare(share: number): number {
  return Number.isFinite(share) && share > 0 ? share : 0;
}

/**
 * Lays out one side's ribbons, left to right for incoming and right to left for outgoing.
 *
 * Each row takes a proportional slice of the center band, clamped up to MIN_RIBBON_THICKNESS.
 * Clamped rows keep that minimum and the rest share what is left, so the minimum is not scaled
 * away again; only when the minimums alone overflow does everything scale down.
 *
 * @returns one path per row, in input order. Empty when there is nothing to draw.
 */
export function computeRibbonPaths(rows: RibbonRow[], layout: RibbonLayout): RibbonPath[] {
  const {
    side,
    width,
    centerTop,
    centerHeight,
  } = layout;

  if (!rows.length || width <= 0 || centerHeight <= 0) {
    return [];
  }

  const total = rows.reduce((sum, row) => sum + sanitiseShare(row.share), 0);
  if (total <= 0) {
    return [];
  }

  const raw = rows.map((row) => (sanitiseShare(row.share) / total) * centerHeight);
  const isClamped = raw.map((thickness) => thickness < MIN_RIBBON_THICKNESS);

  const clampedTotal = isClamped.filter(Boolean).length * MIN_RIBBON_THICKNESS;
  const unclampedTotal = raw.reduce(
    (sum, thickness, index) => (isClamped[index] ? sum : sum + thickness),
    0,
  );
  const budget = centerHeight - clampedTotal;

  let thicknesses: number[];
  if (budget <= 0 || unclampedTotal <= 0) {
    // Too many rows to give each one the minimum; give up on it and scale the stack to fit.
    const scale = centerHeight / (clampedTotal + unclampedTotal);
    thicknesses = raw.map((thickness, index) => Math.max(
      HAIRLINE_THICKNESS,
      (isClamped[index] ? MIN_RIBBON_THICKNESS : thickness) * scale,
    ));
  } else {
    // Clamped rows keep their minimum; the rest share what is left, in proportion.
    const scale = budget / unclampedTotal;
    thicknesses = raw.map(
      (thickness, index) => (isClamped[index] ? MIN_RIBBON_THICKNESS : thickness * scale),
    );
  }

  const rowOverlap = layout.rowOverlap ?? 0;
  const rowStraight = layout.rowStraight ?? 0;

  const rowX = side === 'incoming' ? -rowOverlap : width + rowOverlap;
  const centerX = side === 'incoming' ? width : 0;

  // Where the band stops running straight and starts curving towards the center.
  const curveX = side === 'incoming'
    ? Math.min(rowX + rowStraight, centerX)
    : Math.max(rowX - rowStraight, centerX);
  const controlX = curveX + (centerX - curveX) / 2;

  let bandTop = centerTop;

  return rows.map((row, index) => {
    const thickness = thicknesses[index];
    const rowTop = row.top;
    const rowBottom = row.top + row.height;
    const bandBottom = bandTop + thickness;

    const d = `M${rowX},${rowTop}`
      + ` L${curveX},${rowTop}`
      + ` C${controlX},${rowTop} ${controlX},${bandTop} ${centerX},${bandTop}`
      + ` L${centerX},${bandBottom}`
      + ` C${controlX},${bandBottom} ${controlX},${rowBottom} ${curveX},${rowBottom}`
      + ` L${rowX},${rowBottom}`
      + ' Z';

    bandTop = bandBottom;

    return { key: row.key, d, thickness };
  });
}
