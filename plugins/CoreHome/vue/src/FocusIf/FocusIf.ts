/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

interface FocusIfArgs {
  focusIf: boolean;
  afterFocus?: () => void;
}

export default {
  updated(el: HTMLElement, binding: DirectiveBinding<FocusIfArgs>): void {
    if (binding.value.focusIf) {
      setTimeout(() => {
        el.focus();
      }, 5);
    }
  },
};
