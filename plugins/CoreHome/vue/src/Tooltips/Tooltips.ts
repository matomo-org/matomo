/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

interface TooltipsArgs {
  content?: () => void;
  delay?: number;
  duration?: number;
  show?: unknown;
  tooltipClass?: string;
}

// Minimal shape of the jQuery UI tooltip widget instance we rely on to find the
// targets of currently open tooltips. `tooltips` is keyed by the tooltip id and
// `element` is the jQuery wrapper around the element the tooltip is shown for.
interface TooltipInstance {
  tooltips: Record<string, { element: JQuery }>;
}

const { $ } = window;

// Tracks the MutationObserver attached to each tooltip host so it can be
// disconnected again when the host element is unmounted.
const observers = new WeakMap<HTMLElement, MutationObserver>();

function defaultContentTransform(this: HTMLElement) {
  const title = $(this).attr('title') || '';
  return window.vueSanitize(title.replace(/\n/g, '<br />'));
}

/**
 * jQuery UI shows a single, delegated tooltip for every descendant of the host
 * element that has a `title`. It only auto-closes a tooltip when its target
 * receives mouseleave/focusout, or when the target is removed through jQuery's
 * own `remove` event. Vue removes elements natively (eg. a v-if swap on an
 * inline action button), which triggers neither - so a tooltip that was open
 * over a now-removed element is left orphaned on screen.
 *
 * After any descendant is removed, close every open tooltip whose target is no
 * longer attached to the document. Triggering mouseleave/focusout routes to the
 * close handlers jQuery UI bound on the target, and the `ui-tooltip-id` data it
 * stored survives native removal, so the orphaned tooltip is cleaned up.
 */
function closeOrphanedTooltips(el: HTMLElement): void {
  let instance: TooltipInstance | undefined;

  try {
    instance = $(el).tooltip('instance') as unknown as TooltipInstance | undefined;
  } catch (e) {
    return; // tooltip not initialised (or already destroyed)
  }

  if (!instance || !instance.tooltips) {
    return;
  }

  // Snapshot the ids first - closing a tooltip mutates instance.tooltips.
  Object.keys(instance.tooltips).forEach((id) => {
    const target = instance?.tooltips[id]?.element?.[0];
    if (target && !target.isConnected) {
      $(target).trigger('mouseleave').trigger('focusout');
    }
  });
}

function setupTooltips(el: HTMLElement, binding: DirectiveBinding<TooltipsArgs>) {
  if (!el.isConnected) {
    return;
  }

  $(el).tooltip({
    track: true,
    content: binding.value?.content || defaultContentTransform,
    show: typeof binding.value?.show !== 'undefined'
      ? binding.value?.show
      : {
        delay: binding.value?.delay || 700,
        duration: binding.value?.duration || 200,
      },
    hide: false,
    tooltipClass: binding.value?.tooltipClass,
  });

  if (!observers.has(el)) {
    const observer = new MutationObserver((mutations) => {
      if (mutations.some((mutation) => mutation.removedNodes.length > 0)) {
        closeOrphanedTooltips(el);
      }
    });
    observer.observe(el, { childList: true, subtree: true });
    observers.set(el, observer);
  }
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<TooltipsArgs>): void {
    setTimeout(() => setupTooltips(el, binding));
  },
  updated(el: HTMLElement, binding: DirectiveBinding<TooltipsArgs>): void {
    setTimeout(() => setupTooltips(el, binding));
  },
  beforeUnmount(el: HTMLElement): void {
    const observer = observers.get(el);
    if (observer) {
      observer.disconnect();
      observers.delete(el);
    }

    try {
      window.$(el).tooltip('destroy');
    } catch (e) {
      // ignore
    }
  },
};
