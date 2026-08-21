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

export interface MeasuredRect {
  top: number;
  height: number;
  width: number;
}

/** Injected so specs can supply rects; jsdom reports every element as 0x0. */
export type MeasureRect = (element: Element) => MeasuredRect;

/**
 * The band stops short of the card by this much at each end, so the card reads as taller than the
 * ribbons meeting it and they never run into its rounded corners.
 */
export const CENTER_INSET = 50;

/**
 * How far the row end of a band reaches back into the row it leaves. The rows paint over the
 * ribbon layer, so the overlap is hidden and the band appears to come out from under the row
 * instead of butting against its rounded edge.
 */
export const ROW_OVERLAP = 10;

/**
 * Length of the band's straight run at the row end. It covers the whole overlap plus a few pixels
 * beyond it, so the band leaves the row at exactly the row's height with parallel edges before it
 * starts curving.
 */
export const ROW_STRAIGHT = ROW_OVERLAP + 6;

/**
 * The card inset may not eat its whole edge, so it gives way once it would take more than this
 * share of the card at each end. Without it a short card would leave no room to draw in.
 */
const MAX_INSET_SHARE = 0.25;

/** Keys are generated ids, but escape anyway so an unusual group name cannot break the selector. */
function escapeKey(key: string): string {
  return typeof CSS !== 'undefined' && typeof CSS.escape === 'function'
    ? CSS.escape(key)
    : key.replace(/["\\]/g, '\\$&');
}

export const measureBoundingRect: MeasureRect = (element) => {
  const rect = element.getBoundingClientRect();
  return { top: rect.top, height: rect.height, width: rect.width };
};

/** A row the ribbon layer should connect, before it has been measured. */
export interface RibbonSource {
  key: string;
  share: number;
}

/**
 * Resolves an element the layout depends on. These are looked up per measurement rather than held
 * reactively, because a reactive proxy around a DOM node breaks native calls, and because the
 * elements belong to sibling components that mount in an order the layer should not depend on.
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
  measure?: MeasureRect;
}

/**
 * Measures the rows of one column and lays out the ribbons that connect them to the center card.
 *
 * Re-measures when the observed elements resize, coalescing bursts of resize notifications into a
 * single animation frame. The observer and any pending frame are released on unmount.
 */
export function useRibbonGeometry(options: UseRibbonGeometryOptions) {
  const measure = options.measure ?? measureBoundingRect;
  const paths = ref<RibbonPath[]>([]);
  const size = ref({ width: 0, height: 0 });

  let frame: number|null = null;
  let scheduled = false;
  let observer: ResizeObserver|null = null;
  let observed: Element[] = [];

  function layout(layer: SVGSVGElement, column: HTMLElement, center: HTMLElement) {
    const layerRect = measure(layer);
    size.value = { width: layerRect.width, height: layerRect.height };

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

    // A frame callback can run before requestAnimationFrame() returns; only remember the handle
    // while it is genuinely still pending, so the scheduler cannot wedge itself.
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

  // Post-flush: the rows to measure only exist in the DOM after the update has been applied.
  watch(options.rows, schedule, { deep: true, flush: 'post' });

  return {
    paths,
    size,
    recompute,
    schedule,
    teardown,
  };
}
