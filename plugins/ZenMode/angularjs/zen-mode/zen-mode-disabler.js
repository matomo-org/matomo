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
    angular.module('piwikApp').directive('piwikReportingMenu', piwikZenModeSwitcher);

    piwikZenModeSwitcher.$inject = ['$rootElement', '$filter'];

    function piwikZenModeSwitcher($rootElement, $filter) {

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                element.find('.Menu--dashboard').prepend(
                    '<span piwik-zen-mode-switcher class="deactivateZenMode">'
                    + '<img src="plugins/CoreHome/images/navigation_collapse.png" >'
                    + '</span>');

                return function () {
                };
            }
        };

    }
})();