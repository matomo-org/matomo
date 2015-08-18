/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Directive to show a notification.
 *
 * Note: using this directive is preferred over the Notification class (which uses jquery
 * exclusively).
 *
 * Supports the following attributes:
 *
 * * **context**: Either 'success', 'error', 'info', 'warning'
 * * **type**: Either 'toast', 'persistent', 'transient'
 * * **noclear**: If truthy, no clear button is displayed. For persistent notifications, has no effect.
 *
 * Usage:
 *
 *     <div piwik-notification context="success" type="persistent" noclear="true">
 *         <strong>Info: </strong>My notification message.
 *     </div>
 */
(function () {
    angular.module('piwikApp').directive('piwikNotification', piwikNotification);

    piwikNotification.$inject = ['piwik', '$timeout'];

    function piwikNotification(piwik, $timeout) {
        return {
            restrict: 'A',
            scope: {
                notificationId: '@?',
                title: '@?notificationTitle', // TODO: shouldn't need this since the title can be specified within
                                              //       HTML of the node that uses the directive.
                context: '@?',
                type: '@?',
                noclear: '@?'
            },
            transclude: true,
            templateUrl: 'plugins/CoreHome/angularjs/notification/notification.directive.html?cb=' + piwik.cacheBuster,
            controller: 'NotificationController',
            controllerAs: 'notification',
            link: function (scope, element) {
                if (scope.notificationId) {
                    closeExistingNotificationHavingSameIdIfNeeded(scope.notificationId, element);
                }

                if (scope.context) {
                    element.children('.notification').addClass('notification-' + scope.context);
                }

                if (scope.type == 'persistent') {
                    // otherwise it is never possible to dismiss the notification
                    scope.noclear = false;
                }

                if ('toast' == scope.type) {
                    addToastEvent();
                }

                if (!scope.noclear) {
                    addCloseEvent();
                }

                function addToastEvent() {
                    $timeout(function () {
                        element.fadeOut('slow', function() {
                            element.remove();
                        });
                    }, 12 * 1000);
                }

                function addCloseEvent() {
                    element.on('click', '.close', function (event) {
                        if (event && event.delegateTarget) {
                            angular.element(event.delegateTarget).remove();
                        }
                    });
                }

                function closeExistingNotificationHavingSameIdIfNeeded(id, notificationElement) {
                    // TODO: instead of doing a global query for notification, there should be a notification-container
                    //       directive that manages notifications.
                    var existingNode = angular.element('[notification-id=' + id + ']').not(notificationElement);
                    if (existingNode && existingNode.length) {
                        existingNode.remove();
                    }
                }
            }
        };
    }
})();