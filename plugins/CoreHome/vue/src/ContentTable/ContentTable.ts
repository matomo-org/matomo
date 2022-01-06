/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export default {
  mounted(el: HTMLElement): void {
    el.classList.add('card', 'card-table', 'entityTable');
  },
};
