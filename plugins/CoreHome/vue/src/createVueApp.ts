/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createApp } from 'vue';
import { translate, translateOrDefault } from './translate';

export default function createVueApp(
  ...args: Parameters<typeof createApp>
): ReturnType<typeof createApp> {
  const app = createApp(...args);
  app.config.globalProperties.$sanitize = window.vueSanitize;
  app.config.globalProperties.translate = translate;
  app.config.globalProperties.translateOrDefault = translateOrDefault;
  return app;
}
