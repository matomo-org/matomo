/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * If the given expression evaluates to true the element will be focussed
 *
 * Example:
 * <input type="text" piwik-focus-if="view.editName">
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikFocusIf', piwikFocusIf);

    piwikFocusIf.$inject = ['$timeout'];

    function piwikFocusIf($timeout) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                scope.$watch(attrs.piwikFocusIf, function(newValue, oldValue) {
                    if (newValue) {
                        $timeout(function () {
                            element[0].focus();
                        }, 5);
                    }
                });
            }
        };
    }
})();