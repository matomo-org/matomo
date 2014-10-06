/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('NotificationController', NotificationController);

    NotificationController.$inject = ['piwikApi'];

    function NotificationController(piwikApi) {
        /**
         * Marks a persistent notification as read so it will not reappear on the next page
         * load.
         */
        this.markNotificationAsRead = function () {
            var notificationId = this.notificationId;
            if (!notificationId) {
                return;
            }

            piwikApi.post(
                { // GET params
                    module: 'CoreHome',
                    action: 'markNotificationAsRead'
                },
                { // POST params
                    notificationId: notificationId
                }
            );
        };
    }
})();