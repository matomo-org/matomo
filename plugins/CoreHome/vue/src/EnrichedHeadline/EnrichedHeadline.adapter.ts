/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import EnrichedHeadline from './EnrichedHeadline.vue';

export default createAngularJsAdapter({
  component: EnrichedHeadline,
  scope: {
    helpUrl: {
      angularJsBind: '@',
    },
    editUrl: {
      angularJsBind: '@',
    },
    reportGenerated: {
      angularJsBind: '@?',
    },
    featureName: {
      angularJsBind: '@',
    },
    inlineHelp: {
      angularJsBind: '@?',
    },
  },
  directiveName: 'piwikEnrichedHeadline',
  transclude: true,
});
