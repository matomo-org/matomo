/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-component>
 */
(function () {
    angular.module('piwikApp').directive('piwikComponent', piwikComponent);

    piwikComponent.$inject = ['piwik'];

    function piwikComponent(piwik){
        var defaults = {
            // showAllSitesItem: 'true'
        };

        return {
            restrict: 'A',
            scope: {
               // showAllSitesItem: '='
            },
            templateUrl: 'plugins/ExamplePlugin/angularjs/directive-component/component.directive.html?cb=' + piwik.cacheBuster,
            controller: 'ComponentController',
            controllerAs: 'componentAs',
            compile: function (element, attrs) {

                for (var index in defaults) {
                    if (defaults.hasOwnProperty(index) && attrs[index] === undefined) {
                        attrs[index] = defaults[index];
                    }
                }

                return function (scope, element, attrs) {

                };
            }
        };
    }
})();