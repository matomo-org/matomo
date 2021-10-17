/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import ContentBlock from './ContentBlock.vue';

export default createAngularJsAdapter({
  component: ContentBlock,
  scope: {
    contentTitle: {
      angularJsBind: '@',
    },
    feature: {
      angularJsBind: '@',
    },
    helpUrl: {
      angularJsBind: '@',
    },
    helpText: {
      angularJsBind: '@',
    },
    anchor: {
      angularJsBind: '@?',
    },
  },
  directiveName: 'piwikContentBlock',
  transclude: true,
});
