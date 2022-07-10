/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding, nextTick } from 'vue';

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<string|number>): void {
    nextTick(() => {
      window.require('piwik/UI/DataTable').initNewDataTables(binding.value);
    });
  },
};
