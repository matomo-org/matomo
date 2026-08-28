/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

export interface AutoClearArgs {
  delay?: number,
}

export interface HTMLInputElementWithAutoClear extends HTMLInputElement {
  onUmounted?: {
    cleanup: () => void;
  };
}

function collectPasswordInputs(el: HTMLElement): Array<HTMLInputElementWithAutoClear> {
  const targets: Array<HTMLInputElementWithAutoClear> = [];

  if (el.tagName === 'INPUT' && (el as HTMLInputElement).type === 'password') {
    targets.push(el as HTMLInputElementWithAutoClear);
  } else {
    const nested = el.querySelectorAll('input[type="password"]');
    nested.forEach((nestedEl: Element) => targets.push(nestedEl as HTMLInputElementWithAutoClear));
  }

  return targets;
}

function setupAutoClear(el: HTMLInputElementWithAutoClear, delay: number) {
  // Never arm the same input twice.
  if (el.dataset.autoClearEnabled === 'true') {
    return;
  }

  let timeoutId: ReturnType<typeof setTimeout> | undefined;
  let intervalId: ReturnType<typeof setInterval> | undefined;
  let lastValue = el.value;

  const clearValue = (): void => {
    if (el.value === '') {
      return;
    }

    el.value = '';
    el.dispatchEvent(new Event('input'));

    // The event hits our own listener and re-arms the timer, so drop it again.
    lastValue = '';
    if (timeoutId !== undefined) {
      clearTimeout(timeoutId);
      timeoutId = undefined;
    }
  };

  const resetTimer = (): void => {
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(clearValue, delay * 1000);
  };

  const inputListener = () => resetTimer();
  const changeListener = () => resetTimer();

  // Forward declared so `teardown` can remove the listener registered below.
  let pageHideListener: ((event: PageTransitionEvent) => void) | undefined;

  // Tears everything down and drops the retained value copy. `unmounted` does
  // not run on full-page navigation, so `pagehide` covers that case too.
  const teardown = (clearField: boolean): void => {
    if (timeoutId !== undefined) {
      clearTimeout(timeoutId);
    }
    if (intervalId !== undefined) {
      clearInterval(intervalId);
    }
    el.removeEventListener('input', inputListener);
    el.removeEventListener('change', changeListener);
    if (pageHideListener) {
      window.removeEventListener('pagehide', pageHideListener);
    }
    delete el.dataset.autoClearEnabled;
    delete el.onUmounted;
    lastValue = '';
    if (clearField) {
      // No `input` event dispatched on purpose: the page is unloading, so
      // re-triggering the timer or v-model updates would be pointless.
      el.value = '';
    }
  };

  pageHideListener = (event: PageTransitionEvent): void => {
    if (event.persisted) {
      // Kept for the back/forward cache and may be restored: clear value and
      // model (a restore render would otherwise repopulate it) but stay armed.
      clearValue();
      lastValue = '';
      return;
    }
    teardown(true);
  };

  el.addEventListener('input', inputListener);
  el.addEventListener('change', changeListener);
  el.dataset.autoClearEnabled = 'true';
  window.addEventListener('pagehide', pageHideListener);

  intervalId = setInterval(() => {
    if (el.value !== lastValue) {
      lastValue = el.value;
      resetTimer();
    }
  }, 300);

  el.onUmounted = {
    cleanup() {
      teardown(false);
    },
  };
}

export default {
  mounted(el: HTMLInputElementWithAutoClear, binding: DirectiveBinding<AutoClearArgs>): void {
    const delay = (binding.value && binding.value.delay) || 600;

    const targets = collectPasswordInputs(el);
    targets.forEach((input: HTMLInputElementWithAutoClear) => setupAutoClear(input, delay));
  },

  unmounted(el: HTMLInputElementWithAutoClear): void {
    const targets = collectPasswordInputs(el);
    targets.forEach((e: HTMLInputElementWithAutoClear) => {
      if (e.onUmounted && typeof e.onUmounted.cleanup === 'function') {
        e.onUmounted.cleanup();
      }
    });
  },
};
