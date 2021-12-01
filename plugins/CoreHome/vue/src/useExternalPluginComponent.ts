/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { defineAsyncComponent } from 'vue';

export default function useExternalPluginComponent(
  plugin: string,
  component: string,
): typeof defineAsyncComponent {
  return defineAsyncComponent(() => (new Promise((resolve) => {
    window.$(document).ready(() => {
      if (window[plugin]) {
        resolve(window[plugin][component]);
      } else {
        resolve(null); // plugin not loaded
      }
    });
  })));
}
