/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

function getRef<T>(expander: string | HTMLElement, binding: DirectiveBinding<T>): HTMLElement|null {
  return expander instanceof HTMLElement
    ? expander
    : binding.instance?.$refs[expander] as HTMLElement;
}

export default {
  getRef,
};
