/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Orderable } from '../Orderable';

export interface Subcategory extends Orderable {
  id: string;
  name: string;
  isGroup: boolean;
  icon?: string;
  tooltip?: string;
  help?: string;

  /**
   * @deprecated exists for BC, should be removed in Matomo 5
   */
  active?: boolean;
}

export interface SubcategoryContainer extends Subcategory {
  subcategories: Subcategory[];
}

export function getSubcategoryChildren(subcategory: Subcategory): Subcategory[] {
  const container = subcategory as SubcategoryContainer;
  if (container.subcategories) {
    return container.subcategories;
  }
  return [];
}
