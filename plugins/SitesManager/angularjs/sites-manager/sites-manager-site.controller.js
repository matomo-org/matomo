/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SitesManagerSiteController', SitesManagerSiteController);

    SitesManagerSiteController.$inject = ['$scope', '$filter', 'sitesManagerApiHelper', 'sitesManagerTypeModel', 'piwikApi', '$timeout'];

    function SitesManagerSiteController($scope, $filter, sitesManagerApiHelper, sitesManagerTypeModel, piwikApi, $timeout) {

        var translate = $filter('translate');

        var updateView = function () {
            $timeout(function () {
                $('.editingSite').find('select').material_select();
                Materialize.updateTextFields();
            });
        }

        var init = function () {

            initModel();
            initActions();

            $scope.site.isLoading = true;
            sitesManagerTypeModel.fetchTypeById($scope.site.type).then(function (type) {
                $scope.site.isLoading = false;

                if (type) {
                    $scope.currentType = type;
                    $scope.howToSetupUrl = type.howToSetupUrl;
                    $scope.isInternalSetupUrl = '?' === ('' + type.howToSetupUrl).substr(0, 1);
                    $scope.typeSettings = type.settings;

                    if (isSiteNew()) {
                        $scope.measurableSettings = angular.copy(type.settings);
                    }
                } else {
                    $scope.currentType = {name: $scope.site.type};
                }
            });
        };

        var initActions = function () {

            $scope.editSite = editSite;
            $scope.saveSite = saveSite;
            $scope.openDeleteDialog = openDeleteDialog;
            $scope.site['delete'] = deleteSite;
        };

        var initModel = function() {

            if (isSiteNew()) {
                initNewSite();
            } else {
                $scope.site.excluded_ips = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_ips);
                $scope.site.excluded_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_parameters);
                $scope.site.excluded_user_agents = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_user_agents);
                $scope.site.sitesearch_keyword_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.sitesearch_keyword_parameters);
                $scope.site.sitesearch_category_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.sitesearch_category_parameters);
            }

            $scope.site.removeDialog = {};

            updateView();
        };

        var editSite = function () {
            $scope.site.editMode = true;

            $scope.measurableSettings = [];
            $scope.site.isLoading = true;
            piwikApi.fetch({method: 'SitesManager.getSiteSettings', idSite: $scope.site.idsite}).then(function (settings) {
                $scope.measurableSettings = settings;
                $scope.site.isLoading = false;
            }, function () {
                $scope.site.isLoading = false;
            });

            updateView();
        };

        var saveSite = function() {

            var sendSiteSearchKeywordParams = $scope.site.sitesearch == '1' && !$scope.site.useDefaultSiteSearchParams;
            var sendSearchCategoryParameters = sendSiteSearchKeywordParams && $scope.customVariablesActivated;

            var values = {
                siteName: $scope.site.name,
                timezone: $scope.site.timezone,
                currency: $scope.site.currency,
                type: $scope.site.type,
                settingValues: {}
            };

            var isNewSite = isSiteNew();

            var apiMethod = 'SitesManager.addSite';
            if (!isNewSite) {
                apiMethod = 'SitesManager.updateSite';
                values.idSite = $scope.site.idsite;
            }

            angular.forEach($scope.measurableSettings, function (settings) {
                if (!values['settingValues'][settings.pluginName]) {
                    values['settingValues'][settings.pluginName] = [];
                }

                angular.forEach(settings.settings, function (setting) {
                    var value = setting.value;
                    if (value === false) {
                        value = '0';
                    } else if (value === true) {
                        value = '1';
                    }
                    if (angular.isArray(value) && setting.uiControl == 'textarea') {
                        var newValue = [];
                        angular.forEach(value, function (val) {
                            // as they are line separated we cannot trim them in the view
                            if (val) {
                                newValue.push(val);
                            }
                        });
                        value = newValue;
                    }

                    values['settingValues'][settings.pluginName].push({
                        name: setting.name,
                        value: value
                    });
                });
            });

            piwikApi.post({method: apiMethod}, values).then(function (response) {
                $scope.site.editMode = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();

                var message = 'Website updated';
                if (isNewSite) {
                    message = 'Website created';
                }

                notification.show(message, {context: 'success', id: 'websitecreated'});
                notification.scrollToNotification();

                if (!$scope.site.idsite && response && response.value) {
                    $scope.site.idsite = response.value;
                }

                angular.forEach(values.settingValues, function (settings, pluginName) {
                    angular.forEach(settings, function (setting) {
                        if (setting.name === 'urls') {
                            $scope.site.alias_urls = setting.value;
                        } else {
                            $scope.site[setting.name] = setting.value;
                        }
                    });
                });
            });
        };

        var isSiteNew = function() {
            return angular.isUndefined($scope.site.idsite);
        };

        var initNewSite = function() {
            $scope.site.editMode = true;
            $scope.site.timezone = $scope.globalSettings.defaultTimezone;
            $scope.site.currency = $scope.globalSettings.defaultCurrency;

            if ($scope.typeSettings) {
                // we do not want to manipulate initial type settings
                $scope.measurableSettings = angular.copy($scope.typeSettings);
            }
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
            ajaxHandler.send();
        };

        init();
    }
})();