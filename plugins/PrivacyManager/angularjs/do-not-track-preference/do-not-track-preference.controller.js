/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('DoNotTrackPreferenceController', DoNotTrackPreferenceController);

    DoNotTrackPreferenceController.$inject = ['piwikApi'];

    function DoNotTrackPreferenceController(piwikApi) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;
        this.isLoading = false;

        this.save = function () {
            this.isLoading = true;

            var action = 'deactivateDoNotTrack';
            if (this.enabled === '1') {
                action = 'activateDoNotTrack';
            }

            piwikApi.post({module: 'API', method: 'PrivacyManager.' + action}).then(function (success) {

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