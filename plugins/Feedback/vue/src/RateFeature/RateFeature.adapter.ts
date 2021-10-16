/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import RateFeature from './RateFeature.vue';

export default createAngularJsAdapter({
  component: RateFeature,
  scope: {
    title: {
      vue: 'title', // TODO: Default to same name as title
      angularJsBind: '@',
    },
  },
  directiveName: 'piwikRateFeature', // TODO: default to piwik + component name
});
