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

    let legacyModule = window.require(jsNamespace);
    if (!legacyModule) {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      legacyModule = (window as any)[jsNamespace];
    }

    legacyModule[jsClass].initElements();
  },
};
