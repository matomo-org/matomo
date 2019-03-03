/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div matomo-tracking-failures>
 */
(function () {
    angular.module('piwikApp').directive('matomoTrackingFailures', matomoTrackingFailures);

    matomoTrackingFailures.$inject = ['piwik'];

    function matomoTrackingFailures(piwik){
        return {
            restrict: 'A',
            templateUrl: 'plugins/CoreAdminHome/angularjs/trackingfailures/trackingfailures.directive.html?cb=' + piwik.cacheBuster,
            controller: 'TrackingFailuresController',
            controllerAs: 'trackingFailures'
        };
    }
})();