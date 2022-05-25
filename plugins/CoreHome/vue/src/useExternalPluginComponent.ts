/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/ban-ts-comment */

import { defineAsyncComponent } from 'vue';

export default function useExternalPluginComponent(
  plugin: string,
  component: string,
): typeof defineAsyncComponent {
  return defineAsyncComponent(() => (new Promise((resolve) => {
    window.$(document).ready(() => {
      if ((window as any)[plugin]) {
        resolve((window as any)[plugin][component]);
      } else {
        // @ts-ignore
        resolve(null); // plugin not loaded
      }
    });
  })));
}
