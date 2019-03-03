/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ScheduleReportDeletionController', ScheduleReportDeletionController);

    ScheduleReportDeletionController.$inject = ['reportDeletionModel', 'piwikApi', '$timeout'];

    function ScheduleReportDeletionController(reportDeletionModel, piwikApi, $timeout) {

        var self = this;
        this.isLoading = false;
        this.dataWasPurged = false;
        this.showPurgeNowLink = true;
        this.model = reportDeletionModel;

        this.save = function () {
            var method = 'PrivacyManager.setScheduleReportDeletionSettings';
            self.model.savePurageDataSettings(this, method, {
                deleteLowestInterval: this.deleteLowestInterval
            });
        };

        this.executeDataPurgeNow = function () {

            if (reportDeletionModel.isModified) {
                piwikHelper.modalConfirm('#saveSettingsBeforePurge', {yes: function () {}});
                return;
            }

            // ask user if they really want to delete their old data
            piwikHelper.modalConfirm('#confirmPurgeNow', {
                yes: function () {
                    self.loadingDataPurge = true;
                    self.showPurgeNowLink = false;

                    // execute a data purge
                    piwikApi.withTokenInUrl();
                    var ajaxRequest = piwikApi.fetch({
                        module: 'PrivacyManager',
                        action: 'executeDataPurge',
                        format: 'html'
                    }).then(function () {
                        self.loadingDataPurge = false;
                        // force reload
                        reportDeletionModel.reloadDbStats();

                        self.dataWasPurged = true;

                        $timeout(function () {
                            self.dataWasPurged = false;
                            self.showPurgeNowLink = true;
                        }, 2000);
                    }, function () {
                        self.loadingDataPurge = false;
                    });
                }
            });
        };

    }
})();
