/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import SeriesPicker from './SeriesPicker.vue';

export default createAngularJsAdapter({
  component: SeriesPicker,
  scope: {
    multiselect: {
      angularJsBind: '<',
    },
    selectableColumns: {
      angularJsBind: '<',
    },
    selectableRows: {
      angularJsBind: '<',
    },
    selectedColumns: {
      angularJsBind: '<',
    },
    selectedRows: {
      angularJsBind: '<',
    },
    onSelect: {
      angularJsBind: '&',
      vue: 'select',
    },
  },
  directiveName: 'piwikSeriesPicker',
  restrict: 'E',
});
