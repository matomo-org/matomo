/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-opt-out-customizer>
 */
(function () {
    angular.module('piwikApp').directive('piwikOptOutCustomizer', piwikOptOutCustomizer);

    piwikOptOutCustomizer.$inject = ['piwik'];

    function piwikOptOutCustomizer(piwik){
        var defaults = {
            // showAllSitesItem: 'true'
        };

        return {
            restrict: 'A',
            scope: {
               language: '@',
               piwikurl: '@'
            },
            templateUrl: 'plugins/PrivacyManager/angularjs/opt-out-customizer/opt-out-customizer.directive.html?cb=' + piwik.cacheBuster,
            controller: 'OptOutCustomizerController',
            controllerAs: 'optOutCustomizer',
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