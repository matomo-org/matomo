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
        var defaults = {
        };

        return {
            restrict: 'A',
            scope: {
                promptForRefer: '<'
            },
            templateUrl: 'plugins/Feedback/angularjs/refer-banner/refer-banner.directive.html?cb=' + piwik.cacheBuster,
            controller: 'ReferBannerController',
            controllerAs: 'referBanner',
            compile: function (element, attrs) {
                for (var index in defaults) {
                    if (defaults.hasOwnProperty(index) && attrs[index] === undefined) {
                        attrs[index] = defaults[index];
                    }
                }
            }
        };
    }
})();