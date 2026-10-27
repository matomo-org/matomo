/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

function getRef<T>(expander: string | HTMLElement, binding: DirectiveBinding<T>): HTMLElement|null {
  return expander instanceof HTMLElement
    ? expander
    : binding.instance?.$refs[expander] as HTMLElement;
}

// The classes ExpandOnClick and ExpandOnHover carry while open: `expanded` when none or an empty
// list is given.
function getExpandedClasses(expandedClass?: string | string[]): string[] {
  const expandedClasses = Array.isArray(expandedClass) ? expandedClass : [expandedClass];
  const validClasses = expandedClasses.filter((name): name is string => !!name);
  return validClasses.length ? validClasses : ['expanded'];
}

export default {
  getRef,
  getExpandedClasses,
};
