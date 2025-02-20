/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createApp } from 'vue';
import { translate, translateOrDefault } from './translate';
import { externalLink, externalRawLink } from './externalLink';
import {
  formatNumber,
  formatPercent,
  formatCurrency,
  formatEvolution,
  calculateAndFormatEvolution,
} from './NumberFormatter';

export default function createVueApp(
  ...args: Parameters<typeof createApp>
): ReturnType<typeof createApp> {
  const app = createApp(...args);
  app.config.globalProperties.$sanitize = window.vueSanitize;
  app.config.globalProperties.translate = translate;
  app.config.globalProperties.translateOrDefault = translateOrDefault;
  app.config.globalProperties.externalLink = externalLink;
  app.config.globalProperties.externalRawLink = externalRawLink;
  app.config.globalProperties.formatNumber = formatNumber;
  app.config.globalProperties.formatPercent = formatPercent;
  app.config.globalProperties.formatCurrency = formatCurrency;
  app.config.globalProperties.formatEvolution = formatEvolution;
  app.config.globalProperties.calculateAndFormatEvolution = calculateAndFormatEvolution;
  return app;
}
