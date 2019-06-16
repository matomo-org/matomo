/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function() {
    angular.module('piwikApp').directive('piwikNotificationsRegistration', piwikNotificationsRegistration);

    function piwikNotificationsRegistration() {
        return {
            restrict: 'A',
            replace: true,
            scope: {
                token: '@?'
            },
            compile: function() {
                return function (scope) {
                    if (scope.token && 'serviceWorker' in navigator) {
                        navigator.serviceWorker.getRegistration('notifications-sw.js')
                            .then(function(registration) {
                                if (typeof registration === "undefined") {
                                    Push.Permission.request(function() {
                                        navigator.serviceWorker.register('/notifications-sw.js?token=' + scope.token);
                                    });
                                }
                            });
                    }
                };
            }
        };
    }
})();
