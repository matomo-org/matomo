/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-location-provider-selection>
 */
(function () {
    angular.module('piwikApp').directive('piwikLocationProviderUpdater', piwikLocationProviderUpdater);

    piwikLocationProviderUpdater.$inject = ['piwik'];

    function piwikLocationProviderUpdater(piwik){

        return {
            restrict: 'A',
            transclude: true,
            controller: 'LocationProviderUpdaterController',
            controllerAs: 'locationUpdater',
            template: '<div ng-transclude></div>',
            compile: function (element, attrs) {

                return function (scope, element, attrs, controller) {
                    controller.geoipDatabaseInstalled = '0' !== attrs.geoipDatabaseInstalled;
                    controller.showFreeDownload = false;
                    controller.showPiwikNotManagingInfo = true;
                    controller.progressFreeDownload = 0;
                    controller.progressUpdateDownload = 0;
                };
            }
        };
    }
})();