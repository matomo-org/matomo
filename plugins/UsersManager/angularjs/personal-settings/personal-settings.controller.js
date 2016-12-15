/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('PersonalSettingsController', PersonalSettingsController);

    PersonalSettingsController.$inject = ['piwikApi', '$window'];

    function PersonalSettingsController(piwikApi, $window) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;

        function updateSettings(postParams)
        {
            self.loading = true;

            piwikApi.withTokenInUrl();
            piwikApi.post({
                module: 'UsersManager', action: 'recordUserSettings', format: 'json'
            }, postParams).then(function (success) {
                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {
                    id: 'PersonalSettingsSuccess', context: 'success'});
                notification.scrollToNotification();

                self.loading = false;
            }, function (errorMessage) {
                self.loading = false;
            });
        }

        this.regenerateTokenAuth = function () {
            var parameters = { userLogin: piwik.userLogin };

            self.loading = true;

            piwikHelper.modalConfirm('#confirmTokenRegenerate', {yes: function () {
                piwikApi.withTokenInUrl();
                piwikApi.post({
                    module: 'API',
                    method: 'UsersManager.regenerateTokenAuth'
                }, parameters).then(function (success) {
                    $window.location.reload();

                    self.loading = false;
                }, function (errorMessage) {
                    self.loading = false;
                });
            }});
        };

        this.save = function () {

            var postParams = {
                alias: this.alias,
                email: this.email,
                defaultReport: this.defaultReport == 'MultiSites' ? this.defaultReport : this.site.id,
                defaultDate: this.defaultDate,
                language: this.language,
                timeformat: this.timeformat,
            };

            if (this.password) {
                postParams.password = this.password;
            }

            if (this.passwordBis) {
                postParams.passwordBis = this.passwordBis;
            }

            updateSettings(postParams);
        };
    }
})();
