/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createVueApp, translate } from 'CoreHome';
import TransitionExporterPopover from './TransitionExporterPopover';
import { actionName } from './transitionParams';

const { Piwik_Popover } = window;

export default {
  mounted(element: HTMLElement): void {
    element.addEventListener('click', (e) => {
      e.preventDefault();

      const props = {
        exportFormat: 'JSON',
        exportFormatOptions: [
          { key: 'JSON', value: 'JSON' },
          { key: 'XML', value: 'XML' },
        ],
      };

      const app = createVueApp({
        template: `
          <popover v-bind="bind"/>`,
        data() {
          return {
            bind: props,
          };
        },
      });
      app.component('popover', TransitionExporterPopover);

      const mountPoint = document.createElement('div');
      app.mount(mountPoint);

      Piwik_Popover.showLoading('');
      Piwik_Popover.setTitle(`${actionName.value} ${translate('Transitions_Transitions')}`);
      Piwik_Popover.setContent(mountPoint);

      Piwik_Popover.onClose(() => {
        app.unmount();
      });
    });
  },
};
