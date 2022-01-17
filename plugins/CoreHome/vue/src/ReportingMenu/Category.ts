/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Orderable } from '../Orderable';
import { Subcategory } from './Subcategory';

export interface Category extends Orderable {
  id: string;
  name: string;
  icon?: string;
  tooltip?: string;

  /**
   * @deprecated exists for BC, should be removed in Matomo 5
   */
  active?: boolean;
}

export interface CategoryContainer extends Category {
  subcategories: Subcategory[];
}

export function getCategoryChildren(category: Category): Subcategory[] {
  const container = category as CategoryContainer;
  if (container.subcategories) {
    return container.subcategories;
  }
  return [];
}
