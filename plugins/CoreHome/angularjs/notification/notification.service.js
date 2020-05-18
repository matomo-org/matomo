/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('notifications', NotificationFactory);

    NotificationFactory.$inject = [];

    function NotificationFactory() {

        return {
            parseNotificationDivs: parseNotificationDivs,
            clearTransientNotifications: clearTransientNotifications,
        };

        function parseNotificationDivs() {
            var UI = require('piwik/UI');

            var $notificationNodes = $('[data-role="notification"]');

            $notificationNodes.each(function (index, notificationNode) {
                $notificationNode = $(notificationNode);
                var attributes = $notificationNode.data();
                var message    = $notificationNode.html();

                if (message) {
                    var notification   = new UI.Notification();
                    attributes.animate = false;
                    notification.show(message, attributes);
                }

                $notificationNodes.remove();
            });
        }

        function clearTransientNotifications() {
            $('[piwik-notification][type=transient]').each(function () {
                var $element = angular.element(this);
                $element.scope().$destroy();
                $element.remove();
            });
        }
    }

    angular.module('piwikApp').run(['notifications', function (notifications) {
        $(function () {
            notifications.parseNotificationDivs();
        });
    }]);
})();
