/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-progressbar>
 */
(function () {
    angular.module('piwikApp').directive('piwikProgressbar', piwikProgressbar);

    piwikProgressbar.$inject = ['piwik'];

    function piwikProgressbar(piwik){
        var defaults = {
            label: '',
            progress: 0
        };

        return {
            restrict: 'A',
            scope: {
               progress: '=',
               label: '='
            },
            templateUrl: 'plugins/CoreHome/angularjs/progressbar/progressbar.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                for (var index in defaults) {
                    if (defaults.hasOwnProperty(index) && attrs[index] === undefined) {
                        attrs[index] = defaults[index];
                    }
                }

                return function (scope, element, attrs) {

                    scope.$watch('progress', function (val, oldVal) {
                        if (val !== oldVal) {
                            if (val > 100) {
                                scope.progress = 100;
                            } else if (val < 0) {
                                scope.progress = 0;
                            }
                        }
                    });

                };
            }
        };
    }
})();