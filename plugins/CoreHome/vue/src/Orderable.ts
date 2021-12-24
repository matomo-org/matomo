/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DeepReadonly } from "vue";

export interface Orderable {
  order: number;
}

export function sortOrderables<T extends Orderable>(menu?: DeepReadonly<T[]>): DeepReadonly<T>[] {
  const result = [...(menu || [])];
  result.sort((lhs, rhs) => {
    if (lhs.order < rhs.order) {
      return -1;
    }

    if (lhs.order > rhs.order) {
      return 1;
    }

    return 0;
  });
  return result;
}
