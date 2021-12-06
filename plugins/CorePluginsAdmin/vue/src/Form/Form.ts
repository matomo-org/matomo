/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const { $ } = window;

export default {
  mounted(el: HTMLElement): void {
    setTimeout(() => {
      $(el).find('input[type=text]').keypress((e) => {
        const key = e.keyCode || e.which;
        if (key === 13) {
          $(el).find('.matomo-save-button input').triggerHandler('click');
        }
      });
    });
  },
};
