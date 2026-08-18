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

// Widest sparkline we will ever display. Mirrors Sparkline::MAX_WIDTH / 2 (core/Visualization/
// Sparkline.php): the image is rendered at twice the displayed size, and a request above the
// server cap is silently clamped there, which would distort the aspect ratio. The widest card a
// grid track can produce is ~681px, so this only binds in layouts that don't exist yet.
export const MAX_DISPLAY_WIDTH = 800;

// Trailing delay before a resize is acted on. Long enough that dragging a window edge produces one
// request per card rather than one per frame, short enough not to be noticed on release. It also
// absorbs the print media query's width round-trip: the two deliveries (to print width and back)
// collapse into a single measurement of the width we ended up at.
const RESIZE_DEBOUNCE_MS = 150;

/**
 * Measures a sparkline slot so the sparkline can be requested at exactly the size it is displayed
 * at, instead of being rendered at a fixed size and rescaled by CSS. Rescaling is what made the
 * stroke weight and the gap under the sparkline vary with the card width.
 *
 * Returns the slot's size, 0 until it has been measured. Callers should hold back the sparkline
 * until both are positive: a slot inside a collapsed widget or an inactive tab measures 0 and
 * becomes measurable later, when the observer reports it.
 */
export default function useSparklineSlotSize(
  slot: Ref<HTMLElement | null>,
): { width: Ref<number>, height: Ref<number> } {
  const width = ref(0);
  const height = ref(0);

  let observer: ResizeObserver | null = null;
  let resizeTimeout: ReturnType<typeof setTimeout> | null = null;

  function applyWidth(measured: number): void {
    // Floor, never round: rounding up makes the image wider than its slot, and Morpheus' legacy
    // `div.sparkline img { flex-shrink: 0 }` turns that into overflow rather than a shrink. An
    // image overflowing by a fraction of a pixel grows the document, which can flip a horizontal
    // scrollbar on, which resizes the grid tracks, which resizes the image again.
    const next = Math.min(Math.floor(measured), MAX_DISPLAY_WIDTH);

    // A slot detached from the document measures 0 — the Dashboard detaches a widget while
    // maximising it. Keeping the last good width stops the sparkline disappearing and refetching
    // on every maximise/restore. (Re-assigning the same width is already a no-op for a ref.)
    if (next > 0) {
      width.value = next;
    }
  }

  onMounted(() => {
    const el = slot.value;

    if (!el) {
      return;
    }

    // Measured synchronously, so the sparkline url is built during mount: waiting for the first
    // observer delivery would push the image request past the point where the UI screenshot runner
    // waits for the network to settle. The slot has no padding or border, so its border box — what
    // getBoundingClientRect reports — is also the width the image should be drawn at.
    const rect = el.getBoundingClientRect();
    applyWidth(rect.width);

    // Read once, deliberately. The slot's height is fixed in CSS, and the card frame's
    // `container-type: inline-size` contains the inline axis only — so tracking the block axis
    // would be a live image -> slot -> image feedback edge the moment the slot's height became
    // content-driven.
    height.value = Math.floor(rect.height);

    // The delivered entry is ignored on purpose: by the time the debounce fires its size can
    // already be stale, so re-measure instead and keep a single measurement path.
    observer = new ResizeObserver(() => {
      if (resizeTimeout) {
        clearTimeout(resizeTimeout);
      }

      resizeTimeout = setTimeout(
        () => applyWidth(el.getBoundingClientRect().width),
        RESIZE_DEBOUNCE_MS,
      );
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
