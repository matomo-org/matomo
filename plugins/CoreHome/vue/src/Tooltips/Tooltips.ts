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

const { $ } = window;

function defaultContentTransform(this: HTMLElement) {
  const title = $(this).attr('title') || '';
  return window.vueSanitize(title.replace(/\n/g, '<br />'));
}

function setupTooltips(el: HTMLElement, binding: DirectiveBinding<TooltipsArgs>) {
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
