/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

interface TooltipsArgs {
  content: () => void;
  delay?: number;
  duration?: number;
}

function setupTooltips(el: HTMLElement, binding: DirectiveBinding<TooltipsArgs>) {
  window.$(el).tooltip({
    track: true,
    content: binding.value.content,
    show: { delay: binding.value.delay || 700, duration: binding.value.duration || 200 },
    hide: false,
  });
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<TooltipsArgs>): void {
    setTimeout(() => setupTooltips(el, binding));
  },
  updated(el: HTMLElement, binding: DirectiveBinding<TooltipsArgs>): void {
    setTimeout(() => setupTooltips(el, binding));
  },
  beforeUnmount(el: HTMLElement): void {
    try {
      window.$(el).tooltip('destroy');
    } catch (e) {
      // ignore
    }
  },
};
