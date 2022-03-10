/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import Edit from './Edit.vue';

export default createAngularJsAdapter({
  component: Edit,
  scope: {
    dimensionId: {
      angularJsBind: '=',
    },
    dimensionScope: {
      angularJsBind: '=',
    },
  },
  directiveName: 'piwikCustomDimensionsEdit',
});
