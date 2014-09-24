/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SitesManagerSiteController', SitesManagerSiteController);

    SitesManagerSiteController.$inject = ['$scope', '$filter', 'sitesManagerApiHelper'];

    function SitesManagerSiteController($scope, $filter, sitesManagerApiHelper) {

        var translate = $filter('translate');

        var init = function () {

            initModel();
            initActions();
        };

        var initActions = function () {

            $scope.editSite = editSite;
            $scope.saveSite = saveSite;
            $scope.openDeleteDialog = openDeleteDialog;
            $scope.site.delete = deleteSite;
        };

        var initModel = function() {

            if(siteIsNew())
                initNewSite();
            else
                initExistingSite();

            $scope.site.editDialog = {};
            $scope.site.removeDialog = {};
        };

        var editSite = function () {

            if ($scope.siteIsBeingEdited) {

                $scope.site.editDialog.show = true;
                $scope.site.editDialog.title = translate('SitesManager_OnlyOneSiteAtTime', '"' + $scope.lookupCurrentEditSite().name + '"');

            } else {

                $scope.site.editMode = true;
                $scope.informSiteIsBeingEdited();
            }
        };

        var saveSite = function() {

            var sendSiteSearchKeywordParams = $scope.site.sitesearch == '1' && !$scope.site.useDefaultSiteSearchParams;
            var sendSearchCategoryParameters = sendSiteSearchKeywordParams && $scope.customVariablesActivated;

            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({
                module: 'API',
                format: 'json'
            }, 'GET');

            if(siteIsNew()) {

                ajaxHandler.addParams({
                    method: 'SitesManager.addSite'
                }, 'GET');

            } else {

                ajaxHandler.addParams({
                    idSite: $scope.site.idsite,
                    method: 'SitesManager.updateSite'
                }, 'GET');
            }

            ajaxHandler.addParams({
                siteName: $scope.site.name,
                timezone: $scope.site.timezone,
                currency: $scope.site.currency,
                ecommerce: $scope.site.ecommerce,
                excludedIps: $scope.site.excluded_ips.join(','),
                excludedQueryParameters: $scope.site.excluded_parameters.join(','),
                excludedUserAgents: $scope.site.excluded_user_agents.join(','),
                keepURLFragments: $scope.site.keep_url_fragment,
                siteSearch: $scope.site.sitesearch,
                searchKeywordParameters: sendSiteSearchKeywordParams ? $scope.site.sitesearch_keyword_parameters.join(',') : null,
                searchCategoryParameters: sendSearchCategoryParameters ? $scope.site.sitesearch_category_parameters.join(',') : null,
                urls: $scope.site.alias_urls
            }, 'POST');

            ajaxHandler.redirectOnSuccess($scope.redirectParams);
            ajaxHandler.setLoadingElement();
            ajaxHandler.send(true);
        };

        var siteIsNew = function() {
            return angular.isUndefined($scope.site.idsite);
        };

        var initNewSite = function() {

            $scope.informSiteIsBeingEdited();

            $scope.site.editMode = true;
            $scope.site.name = "Name";
            $scope.site.alias_urls = [
                "http://siteUrl.com/",
                "http://siteUrl2.com/"
            ];
            $scope.site.keep_url_fragment = "0";
            $scope.site.excluded_ips = [];
            $scope.site.excluded_parameters = [];
            $scope.site.excluded_user_agents = [];
            $scope.site.sitesearch_keyword_parameters = [];
            $scope.site.sitesearch_category_parameters = [];
            $scope.site.sitesearch = $scope.globalSettings.searchKeywordParametersGlobal.length ? "1" : "0";
            $scope.site.timezone = $scope.globalSettings.defaultTimezone;
            $scope.site.currency = $scope.globalSettings.defaultCurrency;
            $scope.site.ecommerce = "0";

            updateSiteWithSiteSearchConfig();
        };

        var initExistingSite = function() {

            $scope.site.excluded_ips = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_ips);
            $scope.site.excluded_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_parameters);
            $scope.site.excluded_user_agents = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_user_agents);
            $scope.site.sitesearch_keyword_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.sitesearch_keyword_parameters);
            $scope.site.sitesearch_category_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.sitesearch_category_parameters);

            updateSiteWithSiteSearchConfig();
        };

        var updateSiteWithSiteSearchConfig = function() {

            $scope.site.useDefaultSiteSearchParams =
                $scope.globalSettings.searchKeywordParametersGlobal.length && !$scope.site.sitesearch_keyword_parameters.length;
        };

        var openDeleteDialog = function() {

            $scope.site.removeDialog.title = translate('SitesManager_DeleteConfirm', '"' + $scope.site.name + '" (idSite = ' + $scope.site.idsite + ')');
            $scope.site.removeDialog.show = true;
        };

        var deleteSite = function() {

            var ajaxHandler = new ajaxHelper();

            ajaxHandler.addParams({
                idSite: $scope.site.idsite,
                module: 'API',
                format: 'json',
                method: 'SitesManager.deleteSite'
            }, 'GET');

            ajaxHandler.redirectOnSuccess($scope.redirectParams);
            ajaxHandler.setLoadingElement();
            ajaxHandler.send(true);
        };

        init();
    }
})();