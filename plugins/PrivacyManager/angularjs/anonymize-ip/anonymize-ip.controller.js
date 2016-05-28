/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('AnonymizeIpController', AnonymizeIpController);

    AnonymizeIpController.$inject = ['piwikApi'];

    function AnonymizeIpController(piwikApi) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;
        this.isLoading = false;

        this.save = function () {
            this.isLoading = true;

            piwikApi.post({module: 'API', method: 'PrivacyManager.setAnonymizeIpSettings'}, {
                anonymizeIPEnable: this.enabled ? '1' : '0',
                maskLength: this.maskLength,
                useAnonymizedIpForVisitEnrichment: parseInt(this.useAnonymizedIpForVisitEnrichment, 10) ? '1' : '0'
            }).then(function (success) {
                self.isLoading = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {context: 'success', id:'privacyManagerSettings'});
                notification.scrollToNotification();

            }, function () {
                self.isLoading = false;
            });
        };
    }
})();