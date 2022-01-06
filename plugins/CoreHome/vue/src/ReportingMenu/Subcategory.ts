/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Orderable } from '../Orderable';

export default  interface Subcategory extends Orderable {
  id: string;
  name: string;
  isGroup: boolean;
  icon?: string;
  tooltip?: string;
  help?: string;
  subcategories: Subcategory[];
}
