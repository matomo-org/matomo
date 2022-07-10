/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

import { DirectiveBinding } from 'vue';

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<string>): void {
    const [jsNamespace, jsClass] = binding.value.split('.');
    window.require(jsNamespace)[jsClass].initElements();
  },
};
