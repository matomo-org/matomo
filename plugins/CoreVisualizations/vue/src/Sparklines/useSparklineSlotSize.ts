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
  Ref,
} from 'vue';

// Widest sparkline we will ever display. Must stay at Sparkline::MAX_WIDTH / 2
// (core/Visualization/Sparkline.php), since the image is requested at twice this. Asking for more
// than the server allows gets clamped there, which would squash the image.
export const MAX_DISPLAY_WIDTH = 800;

// Tallest sparkline we will ever display, mirroring MAX_DISPLAY_WIDTH: half of
// Sparkline::MAX_HEIGHT, since the image is requested at twice this.
export const MAX_DISPLAY_HEIGHT = 64;

// Wait this long after a resize before acting on it, so dragging a window edge costs one request
// per card instead of one per frame.
const RESIZE_DEBOUNCE_MS = 150;

// Ignore width changes smaller than this. A scrollbar appearing, a font settling or a one-pixel
// reflow would otherwise cost a fresh server-rendered PNG per card, and blank each one behind its
// placeholder while it arrives. Ignoring one leaves the image at its old width: if the slot grew,
// it simply sits that many pixels narrow; if the slot shrank, `max-width: 100%` squeezes it
// horizontally, since Sparkline pins the height inline. Either way it is under a percent of a card.
const MIN_WIDTH_CHANGE_PX = 8;

/**
 * Measures a sparkline slot so its image can be requested at exactly the size it is shown at,
 * rather than being drawn at a fixed size and rescaled by CSS. That rescaling is what made the
 * line thickness and the gap below the sparkline change with the card width.
 *
 * Both values are 0 until the slot has been measured, so callers should not render the sparkline
 * until they are positive. A slot in a hidden tab or collapsed widget measures 0 at first and
 * becomes measurable once it is shown.
 *
 * `isResizePending` is true from the moment a resize is observed until it has been acted on, so a
 * card is never in a state where it looks settled but is about to swap its image. It is set before
 * the debounce rather than after, which also keeps the refetch inside the window
 * `PageRenderer.waitForNetworkIdle()` samples: that sleeps 750ms before its first check, so a
 * request started `RESIZE_DEBOUNCE_MS` after a resize is always counted. Keep the debounce well
 * under that 750ms or a spec that waits for the network can capture a half-loaded card.
 */
export default function useSparklineSlotSize(
  slot: Ref<HTMLElement | null>,
): { width: Ref<number>, height: Ref<number>, isResizePending: Ref<boolean> } {
  const width = ref(0);
  const height = ref(0);
  const isResizePending = ref(false);

  let observer: ResizeObserver | null = null;
  let resizeTimeout: ReturnType<typeof setTimeout> | null = null;

  /**
   * The width this slot should ask for, or null when the current one still stands. Called both to
   * apply a measurement and, from the observer, to decide whether a reflow is worth reacting to at
   * all - a layout pass that leaves the slot the same width must not put the card back into its
   * loading state, or anything that forces a layout (the screenshot runner included) makes every
   * card blink.
   */
  function resolveWidth(measured: number): number | null {
    // Round down, never up. A wider image than its slot overflows rather than shrinks (Morpheus
    // sets `flex-shrink: 0` on it), and even a fraction of a pixel of overflow can add a
    // horizontal scrollbar, which resizes the slot, which resizes the image again.
    const next = Math.min(Math.floor(measured), MAX_DISPLAY_WIDTH);

    // Keep the last known width when the slot measures 0, which happens while the Dashboard
    // detaches a widget to maximise it. Otherwise the sparkline would vanish and refetch each time.
    if (next <= 0) {
      return null;
    }

    // The first measurement always counts; after that a change has to be worth a new request.
    if (width.value !== 0 && Math.abs(next - width.value) < MIN_WIDTH_CHANGE_PX) {
      return null;
    }

    return next;
  }

  // The single place the slot is measured, used both on mount and on every resize.
  function applySize(el: HTMLElement): void {
    const rect = el.getBoundingClientRect();
    const nextWidth = resolveWidth(rect.width);

    if (nextWidth !== null) {
      width.value = nextWidth;
    }

    // Height is measured once, then left alone: it is fixed in CSS, and following it would let the
    // image resize the slot it was measured from. A slot that mounts hidden measures 0, so keep
    // looking until there is a real height to use.
    if (height.value === 0) {
      height.value = Math.min(Math.floor(rect.height), MAX_DISPLAY_HEIGHT);
    }
  }

  onMounted(() => {
    const el = slot.value;

    if (!el) {
      return;
    }

    // Measure during mount rather than waiting for the observer's first callback, so the image
    // request starts early enough for the UI screenshot runner to wait for it.
    applySize(el);

    // The size the observer hands us is ignored: it can be stale by the time the debounce fires,
    // so measure again instead.
    observer = new ResizeObserver(() => {
      // Observer callbacks run after layout, so measuring here is free. Ignore a reflow that leaves
      // the slot the same size: reacting to it would blank the card behind its placeholder for the
      // length of the debounce, for no change at all.
      if (resolveWidth(el.getBoundingClientRect().width) === null && height.value !== 0) {
        return;
      }

      // Flagged here rather than after the debounce, so nothing can report itself settled while a
      // refetch is already scheduled. See the note on the return value above.
      isResizePending.value = true;

      if (resizeTimeout) {
        clearTimeout(resizeTimeout);
      }

      resizeTimeout = setTimeout(() => {
        applySize(el);
        isResizePending.value = false;
      }, RESIZE_DEBOUNCE_MS);
    });

    observer.observe(el);
  });

  onBeforeUnmount(() => {
    if (resizeTimeout) {
      clearTimeout(resizeTimeout);
    }

    observer?.disconnect();
  });

  return { width, height, isResizePending };
}
