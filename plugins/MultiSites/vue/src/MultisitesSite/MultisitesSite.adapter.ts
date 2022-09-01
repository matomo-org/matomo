/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import MultisitesSite from './MultisitesSite.vue';

export default createAngularJsAdapter({
  component: MultisitesSite,
  scope: {
    website: {
      angularJsBind: '=',
    },
    evolutionMetric: {
      angularJsBind: '=',
    },
    showSparklines: {
      angularJsBind: '=',
    },
    dateSparkline: {
      angularJsBind: '=',
    },
    displayRevenueColumn: {
      angularJsBind: '=',
    },
    metric: {
      angularJsBind: '=',
    },
  },
  directiveName: 'piwikMultisitesSite',
});
