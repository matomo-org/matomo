/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * TODO: can probably merge this + submenu directive
 * Usage:
 * <div piwik-dropdown-menu>
 */
(function () {
    angular.module('piwikApp').directive('piwikDropdownMenu', piwikDropdownMenu);

    piwikDropdownMenu.$inject = ['$timeout'];

    function piwikDropdownMenu($timeout){
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                $timeout(function () {
                    element.dropdown();
                });
            }
        };
    }
})();