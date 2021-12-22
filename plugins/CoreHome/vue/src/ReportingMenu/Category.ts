/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Orderable } from '../Orderable';
import Subcategory from './Subcategory';

export default interface Category extends Orderable {
  id: string;
  name: string;
  icon?: string;
  tooltip?: string;
  subcategories: Subcategory[];
}
