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

// Wait this long after a resize before acting on it, so dragging a window edge costs one request
// per card instead of one per frame.
const RESIZE_DEBOUNCE_MS = 150;

/**
 * Measures a sparkline slot so its image can be requested at exactly the size it is shown at,
 * rather than being drawn at a fixed size and rescaled by CSS. That rescaling is what made the
 * line thickness and the gap below the sparkline change with the card width.
 *
 * Both values are 0 until the slot has been measured, so callers should not render the sparkline
 * until they are positive. A slot in a hidden tab or collapsed widget measures 0 at first and
 * becomes measurable once it is shown.
 */
export default function useSparklineSlotSize(
  slot: Ref<HTMLElement | null>,
): { width: Ref<number>, height: Ref<number> } {
  const width = ref(0);
  const height = ref(0);

  let observer: ResizeObserver | null = null;
  let resizeTimeout: ReturnType<typeof setTimeout> | null = null;

  function applyWidth(measured: number): void {
    // Round down, never up. A wider image than its slot overflows rather than shrinks (Morpheus
    // sets `flex-shrink: 0` on it), and even a fraction of a pixel of overflow can add a
    // horizontal scrollbar, which resizes the slot, which resizes the image again.
    const next = Math.min(Math.floor(measured), MAX_DISPLAY_WIDTH);

    // Keep the last known width when the slot measures 0, which happens while the Dashboard
    // detaches a widget to maximise it. Otherwise the sparkline would vanish and refetch each time.
    if (next > 0) {
      width.value = next;
    }
  }

  // The single place the slot is measured, used both on mount and on every resize.
  function applySize(el: HTMLElement): void {
    const rect = el.getBoundingClientRect();
    applyWidth(rect.width);

    // Height is measured once, then left alone: it is fixed in CSS, and following it would let the
    // image resize the slot it was measured from. A slot that mounts hidden measures 0, so keep
    // looking until there is a real height to use.
    if (height.value === 0) {
      height.value = Math.floor(rect.height);
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
      if (resizeTimeout) {
        clearTimeout(resizeTimeout);
      }

      resizeTimeout = setTimeout(() => applySize(el), RESIZE_DEBOUNCE_MS);
    });

    observer.observe(el);
  });

  onBeforeUnmount(() => {
    if (resizeTimeout) {
      clearTimeout(resizeTimeout);
    }

    observer?.disconnect();
  });

  return { width, height };
}
