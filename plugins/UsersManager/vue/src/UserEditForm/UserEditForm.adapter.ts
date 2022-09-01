/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import UserEditForm from './UserEditForm.vue';

export default createAngularJsAdapter({
  component: UserEditForm,
  scope: {
    user: {
      angularJsBind: '<',
    },
    onDoneEditing: {
      angularJsBind: '&',
      vue: 'done',
    },
    currentUserRole: {
      angularJsBind: '<',
    },
    accessLevels: {
      angularJsBind: '<',
    },
    filterAccessLevels: {
      angularJsBind: '<',
    },
    initialSiteId: {
      angularJsBind: '<',
    },
    initialSiteName: {
      angularJsBind: '<',
    },
    onUpdated: {
      angularJsBind: '&',
      vue: 'updated',
    },
  },
  directiveName: 'piwikUserEditForm',
  restrict: 'E',
});
