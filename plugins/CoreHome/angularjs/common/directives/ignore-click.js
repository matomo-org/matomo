/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Prevents the default behavior of the click. For instance useful if a link should only work in case the user
 * does a "right click open in new window".
 *
 * Example
 * <a piwik-ignore-click ng-click="doSomething()" href="/">my link</a>
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikIgnoreClick', piwikIgnoreClick);

    function piwikIgnoreClick() {
        return function(scope, element, attrs) {
            $(element).click(function(event) {
                event.preventDefault();
            });
        };
    }
})();