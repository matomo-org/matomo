/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp.directive').directive('piwikFocus', function($timeout) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            scope.$watch(attrs.piwikFocus, function(newValue, oldValue) {
                if (newValue) {
                    $timeout(function () {
                        element[0].focus();
                    }, 5);
                }
            });
        }
    }
});