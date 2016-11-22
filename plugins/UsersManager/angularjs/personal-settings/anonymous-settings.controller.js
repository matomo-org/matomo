/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('AnonymousSettingsController', AnonymousSettingsController);

    AnonymousSettingsController.$inject = ['piwikApi'];

    function AnonymousSettingsController(piwikApi) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;

        function updateSettings(postParams)
        {
            self.loading = true;

            piwikApi.withTokenInUrl();
            piwikApi.post({
                module: 'UsersManager', action: 'recordAnonymousUserSettings', format: 'json'
            }, postParams).then(function (success) {
                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {
                    id: 'anonymousUserSettings', context: 'success'});
                notification.scrollToNotification();

                self.loading = false;
            }, function (errorMessage) {
                self.loading = false;
            });
        }

        this.save = function () {

            var postParams = {
                anonymousDefaultReport: this.defaultReport == '1' ? this.defaultReportWebsite : this.defaultReport,
                anonymousDefaultDate: this.defaultDate
            };

            updateSettings(postParams);
        };
    }
})();