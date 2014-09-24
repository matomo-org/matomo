/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-zen-mode-switcher>...</div>
 * Will toggle the zen mode on click on this element.
 */
(function () {
    angular.module('piwikApp').directive('piwikZenModeSwitcher', piwikZenModeSwitcher);

    piwikZenModeSwitcher.$inject = ['$rootElement', '$filter'];

    function piwikZenModeSwitcher($rootElement, $filter) {

        function showZenModeIsActivatedNotification() {
            var howToSearch = $filter('translate')('ZenMode_HowToSearch');
            var howToToggle = $filter('translate')('ZenMode_HowToToggleZenMode');
            var activated   = $filter('translate')('ZenMode_Activated');

            var message = '<ul><li>' + howToSearch + '</li><li>' + howToToggle + '</li></ul>';

            var UI = require('piwik/UI');
            var notification = new UI.Notification();
            notification.show(message, {
                title: activated,
                context: 'info',
                id: 'ZenMode_EnabledInfo'
            });
        }

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                element.on('click', function() {
                    $rootElement.trigger('zen-mode-toggle', {});

                    if ($rootElement.hasClass('zenMode')) {
                        showZenModeIsActivatedNotification();
                    }
                });

                return function () {
                };
            }
        };

    }
})();