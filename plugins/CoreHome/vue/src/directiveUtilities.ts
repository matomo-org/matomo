/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

function getRef<T>(expander: string | HTMLElement, binding: DirectiveBinding<T>): HTMLElement|null {
  if (expander instanceof HTMLElement) {
    return expander;
  }

  const refTarget = binding.instance?.$refs[expander] as {
    $refs?: { title?: HTMLElement };
  } | HTMLElement | undefined;

  if (refTarget instanceof HTMLElement) {
    return refTarget;
  }

  return refTarget?.$refs?.title || null;
}

export default {
  getRef,
};
