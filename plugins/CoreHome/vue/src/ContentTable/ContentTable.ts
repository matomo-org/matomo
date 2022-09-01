/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { nextTick } from 'vue';

export default {
  mounted(el: HTMLElement): void {
    el.classList.add('card', 'card-table', 'entityTable');
  },
  updated(el: HTMLElement): void {
    // classes can be overwritten when elements bind to :class, nextTick + using
    // updated avoids this problem (and doing in both mounted and updated avoids a temporary
    // state where the classes aren't added)
    nextTick(() => {
      el.classList.add('card', 'card-table', 'entityTable');
    });
  },
};
