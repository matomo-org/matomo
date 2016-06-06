/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SitesManagerController', SitesManagerController);

    SitesManagerController.$inject = ['$scope', '$filter', 'coreAPI', 'sitesManagerAPI', 'piwikApi', 'sitesManagerAdminSitesModel', 'piwik', 'sitesManagerApiHelper', 'sitesManagerTypeModel'];

    function SitesManagerController($scope, $filter, coreAPI, sitesManagerAPI, piwikApi, adminSites, piwik, sitesManagerApiHelper, sitesManagerTypeModel) {

        var translate = $filter('translate');

        var init = function () {

            $scope.period = piwik.broadcast.getValueFromUrl('period');
            $scope.date = piwik.broadcast.getValueFromUrl('date');
            $scope.adminSites = adminSites;
            $scope.hasSuperUserAccess = piwik.hasSuperUserAccess;
            $scope.redirectParams = {showaddsite: false};
            $scope.siteIsBeingEdited = false;
            $scope.cacheBuster = piwik.cacheBuster;
            $scope.totalNumberOfSites = '?';

            initSelectLists();
            initUtcTime();
            initUserIP();
            initCustomVariablesActivated();
            initIsTimezoneSupportEnabled();
            initGlobalParams();

            initActions();
        };

        var initActions = function () {

            $scope.cancelEditSite = cancelEditSite;
            $scope.addSite = addSite;
            $scope.addNewEntity = addNewEntity;
            $scope.saveGlobalSettings = saveGlobalSettings;

            $scope.informSiteIsBeingEdited = informSiteIsBeingEdited;
            $scope.lookupCurrentEditSite = lookupCurrentEditSite;

            $scope.closeAddMeasurableDialog = function () {
                // I couldn't figure out another way to close that jquery dialog
                var element  = angular.element('[piwik-dialog="$parent.showAddSiteDialog"]');
                if (element.parents('ui-dialog') && element.dialog('isOpen')) {
                    element.dialog('close');
                }
            }
        };

        var initAvailableTypes = function () {
            return sitesManagerTypeModel.fetchAvailableTypes().then(function (types) {
                $scope.availableTypes = types;
                $scope.typeForNewEntity = 'website';

                return types;
            });
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

            var availableTypesPromise = initAvailableTypes();

            sitesManagerAPI.getGlobalSettings(function(globalSettings) {

                $scope.globalSettings = globalSettings;

                $scope.globalSettings.searchKeywordParametersGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.searchKeywordParametersGlobal);
                $scope.globalSettings.searchCategoryParametersGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.searchCategoryParametersGlobal);
                $scope.globalSettings.excludedIpsGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.excludedIpsGlobal);
                $scope.globalSettings.excludedQueryParametersGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.excludedQueryParametersGlobal);
                $scope.globalSettings.excludedUserAgentsGlobal = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.globalSettings.excludedUserAgentsGlobal);

                hideLoading();

                initKeepURLFragmentsList();

                adminSites.fetchLimitedSitesWithAdminAccess(function () {
                    availableTypesPromise.then(function () {
                        triggerAddSiteIfRequested();
                    });
                });
                sitesManagerAPI.getSitesIdWithAdminAccess(function (siteIds) {
                    if (siteIds && siteIds.length) {
                        $scope.totalNumberOfSites = siteIds.length;
                    }
                });
            });
        };

        var triggerAddSiteIfRequested = function() {
            var search = String(window.location.search);

            if(piwik.helper.getArrayFromQueryString(search).showaddsite == 1)
                addNewEntity();
        };

        var initEcommerceSelectOptions = function() {

            $scope.eCommerceptions = [
                {key: 0, value: translate('SitesManager_NotAnEcommerceSite')},
                {key: 1, value: translate('SitesManager_EnableEcommerce')}
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
                                label: label
                            });
                        });
                    });
                }
            );
        };

        var initCustomVariablesActivated = function() {

            coreAPI.isPluginActivated(

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
                {key: 1, value: translate('SitesManager_EnableSiteSearch')},
                {key: 0, value: translate('SitesManager_DisableSiteSearch')}
            ];
        };

        var initKeepURLFragmentsList = function() {
            $scope.keepURLFragmentsOptions = [
                {key: 0, value: ($scope.globalSettings.keepURLFragmentsGlobal ? translate('General_Yes') : translate('General_No')) + ' (' + translate('General_Default') + ')'},
                {key: 1, value: translate('General_Yes')},
                {key: 2, value: translate('General_No')}
            ];
        };

        var addNewEntity = function () {
            sitesManagerTypeModel.hasMultipleTypes().then(function (hasMultipleTypes) {
                if (hasMultipleTypes) {
                    $scope.showAddSiteDialog = true;
                } else if ($scope.availableTypes.length === 1) {
                    var type = $scope.availableTypes[0].id;
                    addSite(type);
                }
            });
        };

        var addSite = function(type) {
            if (!type) {
                type = 'website'; // todo shall we really hard code this or trigger an exception or so?
            }

            $scope.adminSites.sites.unshift({type: type});
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

            var sitesInEditMode = $scope.adminSites.sites.filter(function(site) {
                return site.editMode;
            });

            return sitesInEditMode[0];
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
