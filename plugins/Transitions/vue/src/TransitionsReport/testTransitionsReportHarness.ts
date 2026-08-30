/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import TransitionsReport from './TransitionsReport.vue';

/** Mounts the report with the sanitize helpers Matomo installs globally. */
export function mountTransitionsReport(
  props = {},
  options: Record<string, unknown> = {},
): VueWrapper {
  return mount(TransitionsReport as any, { // eslint-disable-line @typescript-eslint/no-explicit-any
    ...options,
    props: {
      actionType: 'url',
      actionName: 'http://example.org/page',
      ...props,
    },
    global: {
      config: {
        globalProperties: {
          $sanitize: (value: string) => value,
          // stands in for DOMPurify.isValidAttribute: rejects anything not http(s)
          $sanitizeUrl: (url: string) => (/^https?:\/\//i.test(url) ? url : ''),
        },
      },
    },
  });
}

/** The grid, the resolved element refs and the ribbon paths each take a render. */
export async function flushRibbons(): Promise<void> {
  for (let i = 0; i < 5; i += 1) {
    // eslint-disable-next-line no-await-in-loop
    await nextTick();
  }
}
