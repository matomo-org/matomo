/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import NotificationsStore from './Notifications.store';

window.angular.module('piwikApp').factory('notifications', () => NotificationsStore);
