/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-rate-feature title="My Feature Name">
 */
(function () {
    angular.module('piwikApp').directive('piwikRateFeature', piwikRateFeature);

    piwikRateFeature.$inject = ['piwik'];

    function piwikRateFeature(piwik){

        return {
            restrict: 'A',
            scope: {
                title: '@'
            },
            templateUrl: 'plugins/Feedback/angularjs/ratefeature/ratefeature.directive.html?cb=' + piwik.cacheBuster,
            controller: 'RateFeatureController',
            controllerAs: 'rateFeature',
        };
    }
})();