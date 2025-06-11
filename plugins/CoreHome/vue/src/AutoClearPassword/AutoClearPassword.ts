/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

interface AutoClearArgs {
  delay?: number,
}

interface HTMLInputElementWithAutoClear extends HTMLInputElement {
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
  let timeoutId: number | undefined;
  let lastValue = el.value;

  const clearValue = (): void => {
    el.value = '';
    el.dispatchEvent(new Event('input'));
  };

  const resetTimer = (): void => {
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(clearValue, delay * 1000);
  };

  const inputListener = () => resetTimer();
  const changeListener = () => resetTimer();

  el.addEventListener('input', inputListener);
  el.addEventListener('change', changeListener);
  el.dataset.autoClearEnabled = 'true';

  const intervalId = setInterval(() => {
    if (el.value !== lastValue) {
      lastValue = el.value;
      resetTimer();
    }
  }, 300);

  el.onUmounted = {
    cleanup() {
      clearTimeout(timeoutId);
      clearInterval(intervalId);
      el.removeEventListener('input', inputListener);
      el.removeEventListener('change', changeListener);
      delete el.dataset.autoClearEnabled;
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
        delete e.onUmounted;
      }
    });
  },
};
