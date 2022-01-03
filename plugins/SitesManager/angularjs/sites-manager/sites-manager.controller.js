/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SitesManagerController', SitesManagerController);

    SitesManagerController.$inject = ['$scope', '$filter', 'coreAPI', 'sitesManagerAPI', 'piwikApi', 'sitesManagerAdminSitesModel', 'piwik', 'sitesManagerApiHelper', 'sitesManagerTypeModel', '$rootScope', '$window'];

    function SitesManagerController($scope, $filter, coreAPI, sitesManagerAPI, piwikApi, adminSites, piwik, sitesManagerApiHelper, sitesManagerTypeModel, $rootScope, $window) {

        $scope.globalSettings = {};


        var init = function () {
            $scope.period = piwik.broadcast.getValueFromUrl('period');
            $scope.date = piwik.broadcast.getValueFromUrl('date');
            $scope.adminSites = adminSites;
            $scope.hasSuperUserAccess = piwik.hasSuperUserAccess;
            $scope.cacheBuster = piwik.cacheBuster;
            $scope.totalNumberOfSites = '?';

            initUserIP();
        };

        var initUserIP = function() { // TODO: just for global settings
            coreAPI.getIpFromHeader(function(ip) {
                $scope.currentIpAddress = ip;
            });
        };

        var saveGlobalSettings = function() {

            var ajaxHandler = new ajaxHelper();

            ajaxHandler.addParams({
                module: 'SitesManager',
                format: 'json',
                action: 'setGlobalSettings'
            }, 'GET');

            ajaxHandler.addParams({
                timezone: $scope.globalSettings.defaultTimezone,
                currency: $scope.globalSettings.defaultCurrency,
                excludedIps: $scope.globalSettings.excludedIpsGlobal.join(','),
                excludedQueryParameters: $scope.globalSettings.excludedQueryParametersGlobal.join(','),
                excludedUserAgents: $scope.globalSettings.excludedUserAgentsGlobal.join(','),
                keepURLFragments: $scope.globalSettings.keepURLFragmentsGlobal ? 1 : 0,
                searchKeywordParameters: $scope.globalSettings.searchKeywordParametersGlobal.join(','),
                searchCategoryParameters: $scope.globalSettings.searchCategoryParametersGlobal.join(',')
            }, 'POST');
            ajaxHandler.withTokenInUrl();
            ajaxHandler.redirectOnSuccess($scope.redirectParams);
            ajaxHandler.setLoadingElement();
            ajaxHandler.send();
        };

        var lookupCurrentEditSite = function () {

            var sitesInEditMode = $scope.adminSites.sites.filter(function(site) {
                return site.editMode;
            });

            return sitesInEditMode[0];
        };

        init();
    }
})();
