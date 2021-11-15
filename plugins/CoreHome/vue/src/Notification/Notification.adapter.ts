/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import Notification from './Notification.vue';

export default createAngularJsAdapter({
  component: Notification,
  scope: {
    notificationId: {
      angularJsBind: '@?',
    },
    title: {
      angularJsBind: '@?notificationTitle',
    },
    context: {
      angularJsBind: '@?',
    },
    type: {
      angularJsBind: '@?',
    },
    noclear: {
      angularJsBind: '@?',
      transform: (v) => !!v,
    },
    toastLength: {
      angularJsBind: '@?',
    },
  },
  directiveName: 'piwikNotification',
  transclude: true,
});
