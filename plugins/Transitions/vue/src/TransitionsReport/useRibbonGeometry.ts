/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
  Ref,
} from 'vue';
import { computeRibbonPaths, RibbonPath, RibbonRow } from './ribbonGeometry';
import { TransitionsSide } from './types';

/** Keeps the band clear of the card's rounded corners at each end. */
export const CENTER_INSET = 50;

/** Rows paint over the layer, so this much of the band hides under the row it leaves. */
export const ROW_OVERLAP = 10;

/** The overlap plus a few pixels, so the band leaves the row at the row's exact height. */
export const ROW_STRAIGHT = ROW_OVERLAP + 6;

/** Caps CENTER_INSET on a short card, which would otherwise leave no edge to draw into. */
const MAX_INSET_SHARE = 0.25;

/** Escaped for a double-quoted attribute value, not CSS.escape()'s identifier position. */
function escapeKey(key: string): string {
  return key.replace(/["\\]/g, '\\$&');
}

/** Specs stub getBoundingClientRect, which jsdom otherwise reports as 0x0. */
function measure(element: Element) {
  const rect = element.getBoundingClientRect();
  return { top: rect.top, height: rect.height, width: rect.width };
}

/** A row the ribbon layer should connect, before it has been measured. */
export interface RibbonSource {
  key: string;
  share: number;
}

/**
 * Looked up per measurement rather than held reactively: a reactive proxy around a DOM node breaks
 * native calls, and these elements belong to siblings that mount in an order we should not assume.
 */
export type ElementSource = () => HTMLElement|null|undefined;

export interface UseRibbonGeometryOptions {
  side: TransitionsSide;
  /** The `<svg>` the ribbons are drawn into; also the coordinate origin. */
  layer: Ref<SVGSVGElement|null>;
  /** The column holding the rows to connect. Rows are marked with `data-ribbon-key`. */
  column: ElementSource;
  /** The center card the ribbons converge on. */
  center: ElementSource;
  rows: Ref<RibbonSource[]>;
}

/**
 * Measures one column's rows and lays out the ribbons connecting them to the center card.
 * Re-measures on resize, coalescing bursts into a single animation frame.
 */
export function useRibbonGeometry(options: UseRibbonGeometryOptions) {
  const paths = ref<RibbonPath[]>([]);

  let frame: number|null = null;
  let scheduled = false;
  let observer: ResizeObserver|null = null;
  let observed: Element[] = [];

  function layout(layer: SVGSVGElement, column: HTMLElement, center: HTMLElement) {
    const layerRect = measure(layer);
    const centerRect = measure(center);

    const measuredRows: RibbonRow[] = [];
    options.rows.value.forEach((source) => {
      const element = column.querySelector(`[data-ribbon-key="${escapeKey(source.key)}"]`);
      if (!element) {
        return;
      }

      const rect = measure(element);
      measuredRows.push({
        key: source.key,
        share: source.share,
        top: rect.top - layerRect.top,
        height: rect.height,
      });
    });

    const inset = Math.min(CENTER_INSET, centerRect.height * MAX_INSET_SHARE);

    paths.value = computeRibbonPaths(measuredRows, {
      side: options.side,
      width: layerRect.width,
      centerTop: centerRect.top - layerRect.top + inset,
      centerHeight: centerRect.height - inset * 2,
      rowOverlap: ROW_OVERLAP,
      rowStraight: ROW_STRAIGHT,
    });
  }

  /** Rebinds the observer only when the element set actually changed. */
  function observe(elements: Element[]) {
    if (typeof ResizeObserver !== 'function') {
      return;
    }

    const unchanged = elements.length === observed.length
      && elements.every((element, index) => element === observed[index]);
    if (unchanged) {
      return;
    }

    if (!observer) {
      observer = new ResizeObserver(schedule); // eslint-disable-line no-use-before-define
    }

    observer.disconnect();
    elements.forEach((element) => observer!.observe(element));
    observed = elements;
  }

  function recompute() {
    const layer = options.layer.value;
    const column = options.column();
    const center = options.center();

    if (!layer || !column || !center) {
      paths.value = [];
      return;
    }

    observe([layer, column, center]);
    layout(layer, column, center);
  }

  function schedule() {
    if (scheduled) {
      return;
    }

    scheduled = true;

    if (typeof requestAnimationFrame !== 'function') {
      scheduled = false;
      recompute();
      return;
    }

    const handle = requestAnimationFrame(() => {
      scheduled = false;
      frame = null;
      recompute();
    });

    // A frame callback can run before rAF() returns, so only keep a handle that is still pending.
    if (scheduled) {
      frame = handle;
    }
  }

  function teardown() {
    if (observer) {
      observer.disconnect();
      observer = null;
      observed = [];
    }

    if (frame !== null) {
      if (typeof cancelAnimationFrame === 'function') {
        cancelAnimationFrame(frame);
      }
      frame = null;
    }

    scheduled = false;
  }

  onMounted(schedule);
  onBeforeUnmount(teardown);

  // Post-flush, because the rows only exist in the DOM after the update is applied. Not deep: the
  // producer returns a fresh array each time, so identity alone fires this.
  watch(options.rows, schedule, { flush: 'post' });

  return { paths };
}
