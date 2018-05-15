/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Controller to save archiving settings
 */
(function () {
    angular.module('piwikApp').controller('ArchivingController', ArchivingController);

    ArchivingController.$inject = ['$scope', 'piwikApi'];

    function ArchivingController($scope, piwikApi) {

        var self = this;
        this.isLoading = false;

        this.save = function () {

            this.isLoading = true;

            var enableBrowserTriggerArchiving = $('input[name=enableBrowserTriggerArchiving]:checked').val();
            var todayArchiveTimeToLive = $('#todayArchiveTimeToLive').val();

            piwikApi.post({module: 'API', method: 'CoreAdminHome.setArchiveSettings'}, {
                enableBrowserTriggerArchiving: enableBrowserTriggerArchiving,
                todayArchiveTimeToLive: todayArchiveTimeToLive
            }).then(function (success) {
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