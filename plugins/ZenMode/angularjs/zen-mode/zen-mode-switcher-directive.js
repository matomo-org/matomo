/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-zen-mode-switcher>...</div>
 * Will toggle the zen mode on click on this element.
 */
angular.module('piwikApp').directive('piwikZenModeSwitcher', function($rootElement) {

    function showZenModeIsActivatedNotification() {
        var UI = require('piwik/UI');
        var notification = new UI.Notification();
        var message = '<ul><li>To search for menu items, reports or websites use the search box on the top right or press alt+s.</li><li>To show the footer icons in the tables press alt+f.</li><li>To leave the ZenMode press the arrow on the top right or press alt+z</li></ul>';
        notification.show(message, {
            title: 'ZenMode activated',
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

});