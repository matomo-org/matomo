/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div matomo-campaign-builder>
 */
(function () {
    angular.module('piwikApp').directive('matomoCampaignBuilder', matomoCampaignBuilder);

    matomoCampaignBuilder.$inject = ['piwik'];

    function matomoCampaignBuilder(piwik){
        var defaults = {
            hasExtraPlugin: true
        };

        return {
            restrict: 'A',
            scope: {
               hasExtraPlugin: '<'
            },
            templateUrl: 'plugins/Referrers/angularjs/campaign-builder/campaign-builder.directive.html?cb=' + piwik.cacheBuster,
            controller: 'CampaignBuilderController',
            controllerAs: 'campaignBuilder',
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