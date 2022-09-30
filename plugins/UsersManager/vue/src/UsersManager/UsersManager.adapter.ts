/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import UsersManager from './UsersManager.vue';

export default createAngularJsAdapter({
  component: UsersManager,
  scope: {
    currentUserRole: {
      angularJsBind: '<',
    },
    initialSiteName: {
      angularJsBind: '@',
    },
    initialSiteId: {
      angularJsBind: '@',
    },
    inviteTokenExpiryDays: {
      angularJsBind: '@',
    },
    accessLevels: {
      angularJsBind: '<',
    },
    filterAccessLevels: {
      angularJsBind: '<',
    },
    filterStatusLevels: {
      angularJsBind: '<',
    },
  },
  directiveName: 'piwikUsersManager',
  restrict: 'E',
});
