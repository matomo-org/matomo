/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

interface FocusIfArgs {
  // input (provided by user)
  focused?: boolean;
  afterFocus?: () => void;
}

function doFocusIf(el: HTMLElement, binding: DirectiveBinding<FocusIfArgs>): void {
  if (binding.value?.focused && !binding.oldValue?.focused) {
    setTimeout(() => {
      el.focus();

      if (binding.value.afterFocus) {
        binding.value.afterFocus();
      }
    }, 5);
  }
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<FocusIfArgs>): void {
    doFocusIf(el, binding);
  },
  updated(el: HTMLElement, binding: DirectiveBinding<FocusIfArgs>): void {
    doFocusIf(el, binding);
  },
};
