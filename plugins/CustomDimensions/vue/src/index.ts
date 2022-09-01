/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import './Edit/Edit.adapter';
import './List/List.adapter';
import './Manage/Manage.adapter';

export { default as CustomDimensionsStore } from './CustomDimensions.store';
export { default as Edit } from './Edit/Edit.vue';
export { default as List } from './List/List.vue';
export { default as Manage } from './Manage/Manage.vue';
