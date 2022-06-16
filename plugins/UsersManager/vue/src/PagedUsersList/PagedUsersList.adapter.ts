/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import PagedUsersList from './PagedUsersList.vue';

export default createAngularJsAdapter({
  component: PagedUsersList,
  scope: {
    onEditUser: {
      angularJsBind: '&',
      vue: 'editUser',
    },
    onChangeUserRole: {
      angularJsBind: '&',
      vue: 'changeUserRole',
    },
    onDeleteUser: {
      angularJsBind: '&',
      vue: 'deleteUser',
    },
    onSearchChange: {
      angularJsBind: '&',
      vue: 'searchChange',
    },
    onResendInvite: {
      angularJsBind: '&',
      vue: 'resendInvite',
    },
    initialSiteId: {
      angularJsBind: '<',
    },
    initialSiteName: {
      angularJsBind: '<',
    },
    currentUserRole: {
      angularJsBind: '<',
    },
    isLoadingUsers: {
      angularJsBind: '<',
    },
    accessLevels: {
      angularJsBind: '<',
    },
    filterAccessLevels: {
      angularJsBind: '<',
    },
    totalEntries: {
      angularJsBind: '<',
    },
    users: {
      angularJsBind: '<',
    },
    searchParams: {
      angularJsBind: '<',
    },
  },
  directiveName: 'piwikPagedUsersList',
  restrict: 'E',
});
