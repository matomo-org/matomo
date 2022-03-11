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
      transform(v: unknown): unknown {
        if (typeof v === 'string') {
          return parseInt(v, 10);
        }
        return v;
      },
    },
    dimensionScope: {
      angularJsBind: '=',
    },
  },
  directiveName: 'piwikCustomDimensionsEdit',
});
