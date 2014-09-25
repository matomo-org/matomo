/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SitesManagerController', SitesManagerController);

    SitesManagerController.$inject = ['$scope', '$filter', 'coreAPI', 'coreAdminAPI', 'sitesManagerAPI', 'piwik', 'sitesManagerApiHelper'];

    function SitesManagerController($scope, $filter, coreAPI, coreAdminAPI, sitesManagerAPI, piwik, sitesManagerApiHelper) {

        var translate = $filter('translate');

        var init = function () {

            initModel();
            initActions();
        };

        var initModel = function() {

            $scope.sites = [];
            $scope.hasSuperUserAccess = piwik.hasSuperUserAccess;
            $scope.redirectParams = {showaddsite: false};

            initSelectLists();
            initUtcTime();
            initUserIP();
            initCustomVariablesActivated();
            initIsTimezoneSupportEnabled();
            initGlobalParams();
        };

        var initActions = function () {

            $scope.cancelEditSite = cancelEditSite;
            $scope.addSite = addSite;
            $scope.saveGlobalSettings = saveGlobalSettings;

            $scope.informSiteIsBeingEdited = informSiteIsBeingEdited;
            $scope.lookupCurrentEditSite = lookupCurrentEditSite;
        };

        var informSiteIsBeingEdited = function() {

            $scope.siteIsBeingEdited = true;
        };

        var initSelectLists = function() {

            initSiteSearchSelectOptions();
            initEcommerceSelectOptions();
            initCurrencyList();
            initTimezones();
        };

        var initGlobalParams = function() {

            showLoading();

            sitesManagerAPI.getGlobalSettings(function(globalSettings) {

                $scope.globalSettings = globalSettings;

                $scope.globalSettings.searchKeywordParametersGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.searchKeywordParametersGlobal);
                $scope.globalSettings.searchCategoryParametersGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.searchCategoryParametersGlobal);
                $scope.globalSettings.excludedIpsGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.excludedIpsGlobal);
                $scope.globalSettings.excludedQueryParametersGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.excludedQueryParametersGlobal);
                $scope.globalSettings.excludedUserAgentsGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.excludedUserAgentsGlobal);

                initKeepURLFragmentsList();

                initSiteList();

                triggerAddSiteIfRequested();
            });
        };

        var triggerAddSiteIfRequested = function() {
            var search = String(window.location.search);

            if(piwik.helper.getArrayFromQueryString(search).showaddsite == 1)
                addSite();
        };

        var initEcommerceSelectOptions = function() {

            $scope.eCommerceptions = [
                {key: '0', value: translate('SitesManager_NotAnEcommerceSite')},
                {key: '1', value: translate('SitesManager_EnableEcommerce')}
            ];
        };

        var initUtcTime = function() {

            var currentDate = new Date();

            $scope.utcTime =  new Date(
                currentDate.getUTCFullYear(),
                currentDate.getUTCMonth(),
                currentDate.getUTCDate(),
                currentDate.getUTCHours(),
                currentDate.getUTCMinutes(),
                currentDate.getUTCSeconds()
            );
        };

        var initIsTimezoneSupportEnabled = function() {

            sitesManagerAPI.isTimezoneSupportEnabled(function (timezoneSupportEnabled) {
                $scope.timezoneSupportEnabled = timezoneSupportEnabled;
            });
        };

        var initTimezones = function() {

            sitesManagerAPI.getTimezonesList(

                function (timezones) {

                    $scope.timezones = [];

                    angular.forEach(timezones, function(groupTimezones, timezoneGroup) {

                        angular.forEach(groupTimezones, function(label, code) {

                            $scope.timezones.push({
                                group: timezoneGroup,
                                code: code,
                                label: label
                            });
                        });
                    });
                }
            );
        };

        var initCustomVariablesActivated = function() {

            coreAdminAPI.isPluginActivated(

                function (customVariablesActivated) {
                    $scope.customVariablesActivated = customVariablesActivated;
                },

                {pluginName: 'CustomVariables'}
            );
        };

        var initUserIP = function() {

            coreAPI.getIpFromHeader(function(ip) {
                $scope.currentIpAddress = ip;
            });
        };

        var initSiteSearchSelectOptions = function() {

            $scope.siteSearchOptions = [
                {key: '1', value: translate('SitesManager_EnableSiteSearch')},
                {key: '0', value: translate('SitesManager_DisableSiteSearch')}
            ];
        };

        var initKeepURLFragmentsList = function() {

            $scope.keepURLFragmentsOptions = {
                0: ($scope.globalSettings.keepURLFragmentsGlobal ? translate('General_Yes') : translate('General_No')) + ' (' + translate('General_Default') + ')',
                1: translate('General_Yes'),
                2: translate('General_No')
            };
        };

        var addSite = function() {
            $scope.sites.push({});
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
                enableSiteUserAgentExclude: $scope.globalSettings.siteSpecificUserAgentExcludeEnabled ? 1 : 0,
                searchKeywordParameters: $scope.globalSettings.searchKeywordParametersGlobal.join(','),
                searchCategoryParameters: $scope.globalSettings.searchCategoryParametersGlobal.join(',')
            }, 'POST');

            ajaxHandler.redirectOnSuccess($scope.redirectParams);
            ajaxHandler.setLoadingElement();
            ajaxHandler.send(true);
        };

        var cancelEditSite = function ($event) {
            $event.stopPropagation();
            piwik.helper.redirect($scope.redirectParams);
        };

        var lookupCurrentEditSite = function () {

            var sitesInEditMode = $scope.sites.filter(function(site) {
                return site.editMode;
            });

            return sitesInEditMode[0];
        };

        var initSiteList = function () {

            sitesManagerAPI.getSitesWithAdminAccess(function (sites) {

                angular.forEach(sites, function(site) {
                    $scope.sites.push(site);
                });

                hideLoading();
            });
        };

        var initCurrencyList = function () {

            sitesManagerAPI.getCurrencyList(function (currencies) {
                $scope.currencies = currencies;
            });
        };

        var showLoading = function() {
            $scope.loading = true;
        };

        var hideLoading = function() {
            $scope.loading = false;
        };

        init();
    }
})();
