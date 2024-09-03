/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/ban-ts-comment */

import { defineAsyncComponent } from 'vue';
import importPluginUmd from './importPluginUmd';

export default function useExternalPluginComponent(
  plugin: string,
  component: string,
): typeof defineAsyncComponent {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return defineAsyncComponent(() => importPluginUmd(plugin).then((module: any) => {
    if (!module) {
      // @ts-ignore
      resolve(null); // plugin not loaded
    }

    return module[component];
  }));
}
