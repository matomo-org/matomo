/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-dropdown-submenu>
 */
(function () {
    angular.module('piwikApp').directive('piwikDropdownSubmenu', piwikDropdownSubmenu);

    piwikDropdownSubmenu.$inject = ['$timeout'];

    function piwikDropdownSubmenu($timeout){
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                $timeout(function () {
                    angular.element('#' + attrs.activates).addClass('submenu-dropdown-content');

                    element.dropdown({
                        alignment: 'left',
                        hover: true
                    });
                });
            }
        };
    }
})();