/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Controller to save mail smtp settings
 */
(function () {
    angular.module('piwikApp').controller('MailSmtpController', MailSmtpController);

    MailSmtpController.$inject = ['$scope', 'piwikApi'];

    function MailSmtpController($scope, piwikApi) {

        var self = this;
        this.isLoading = false;
        this.passwordChanged = false;

        this.save = function () {

            this.isLoading = true;

            var mailSettings = {
                mailUseSmtp: this.enabled ? '1' : '0',
                mailPort: this.mailPort,
                mailHost: this.mailHost,
                mailType: this.mailType,
                mailUsername: this.mailUsername,
                mailEncryption: this.mailEncryption
            };

            if (this.passwordChanged) {
                mailSettings.mailPassword = this.mailPassword;
            }

            piwikApi.withTokenInUrl();
            piwikApi.post({module: 'CoreAdminHome', action: 'setMailSettings'}, mailSettings)
                .then(function (success) {

                self.isLoading = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {
                    id: 'generalSettings', context: 'success'
                });
                notification.scrollToNotification();

            }, function () {
                self.isLoading = false;
            });
        };
    }
})();