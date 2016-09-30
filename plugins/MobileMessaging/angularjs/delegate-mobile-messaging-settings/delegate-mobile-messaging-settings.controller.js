/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('DelegateMobileMessagingSettingsController', DelegateMobileMessagingSettingsController);

    DelegateMobileMessagingSettingsController.$inject = ['piwikApi', 'piwik'];

    function DelegateMobileMessagingSettingsController(piwikApi, piwik) {

        var self = this;
        this.isLoading = false;

        this.save = function () {
            this.isLoading = true;

            piwikApi.post(
                {method: 'MobileMessaging.setDelegatedManagement'},
                {delegatedManagement: (this.enabled == '1') ? 'true' : 'false'}
            ).then(function () {

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {
                    id: 'mobileMessagingSettings', context: 'success'
                });
                notification.scrollToNotification();
                
                piwik.helper.redirect();
                self.isLoading = false;
            }, function () {
                self.isLoading = false;
            });
        };
    }
})();