/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('PluginSettingsController', PluginSettingsController);

    PluginSettingsController.$inject = ['$scope', 'piwikApi'];

    function PluginSettingsController($scope, piwikApi) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;

        this.isLoading = true;

        var apiMethod = 'CorePluginsAdmin.getUserSettings';

        if ($scope.mode === 'admin') {
            apiMethod = 'CorePluginsAdmin.getSystemSettings';
        }

        piwikApi.fetch({method: apiMethod}).then(function (settings) {
            self.isLoading = false;
            self.settingsPerPlugin = settings;
        }, function () {
            self.isLoading = false;
        });

        this.save = function (settings) {
            var apiMethod = 'CorePluginsAdmin.setUserSettings';
            if ($scope.mode === 'admin') {
                apiMethod = 'CorePluginsAdmin.setSystemSettings';
            }

            this.isLoading = true;

            var values = {};
            if (!values[settings.pluginName]) {
                values[settings.pluginName] = [];
            }

            angular.forEach(settings.settings, function (setting) {
                var value = setting.value;
                if (value === false) {
                    value = '0';
                } else if (value === true) {
                    value = '1';
                }
                values[settings.pluginName].push({
                    name: setting.name,
                    value: value
                });
            });

            piwikApi.post({method: apiMethod}, {settingValues: values}).then(function (success) {
                self.isLoading = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_PluginSettingsSaveSuccess'), {
                    id: 'generalSettings', context: 'success'
                });
                notification.scrollToNotification();

            }, function () {
                self.isLoading = false;
            });
        };
    }
})();