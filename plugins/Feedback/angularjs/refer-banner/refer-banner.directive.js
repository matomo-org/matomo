/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-refer-banner>
 */
(function () {
    angular.module('piwikApp').directive('piwikReferBanner', piwikReferBanner);

    piwikReferBanner.$inject = ['piwik'];

    function piwikReferBanner(piwik){
        return {
            restrict: 'A',
            scope: {
                showReferBanner: '<'
            },
            templateUrl: 'plugins/Feedback/angularjs/refer-banner/refer-banner.directive.html?cb=' + piwik.cacheBuster,
            controller: 'ReferBannerController',
            controllerAs: 'referBanner',
        };
    }
})();