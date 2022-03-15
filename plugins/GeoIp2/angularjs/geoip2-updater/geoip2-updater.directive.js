/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-geoip2-updater>
 */
(function () {
    angular.module('piwikApp').directive('piwikGeoip2Updater', piwikGeoip2Updater);

    piwikGeoip2Updater.$inject = ['piwik'];

    function piwikGeoip2Updater(piwik){

        return {
            restrict: 'A',
            transclude: true,
            controller: 'Geoip2UpdaterController',
            controllerAs: 'locationUpdater',
            template: '<div ng-transclude></div>',
            compile: function (element, attrs) {

                return function (scope, element, attrs, controller) {
                    scope.geoipDatabaseInstalled = '0' !== attrs.geoipDatabaseInstalled;
                  scope.showFreeDownload = false;
                  scope.showPiwikNotManagingInfo = true;
                  scope.progressFreeDownload = 0;
                  scope.progressUpdateDownload = 0;
                };
            }
        };
    }
})();
