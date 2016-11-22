/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('DeleteOldReportsController', DeleteOldReportsController);

    DeleteOldReportsController.$inject = ['reportDeletionModel', 'piwikApi', '$timeout'];

    function DeleteOldReportsController(reportDeletionModel, piwikApi, $timeout) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;
        this.isLoading = false;

        function getInt(value)
        {
            return value ? '1' : '0';
        }

        function saveSettings()
        {
            var method = 'PrivacyManager.setDeleteReportsSettings';
            reportDeletionModel.savePurageDataSettings(self, method, self.getSettings());
        }

        this.getSettings = function () {
            return {
                enableDeleteReports: this.enabled ? '1' : '0',
                deleteReportsOlderThan: this.deleteOlderThan,
                keepBasic: getInt(this.keepBasic),
                keepDay: getInt(this.keepDataForDay),
                keepWeek: getInt(this.keepDataForWeek),
                keepMonth: getInt(this.keepDataForMonth),
                keepYear: getInt(this.keepDataForYear),
                keepRange: getInt(this.keepDataForRange),
                keepSegments: getInt(this.keepDataForSegments),
            };
        }

        this.reloadDbStats = function () {
            reportDeletionModel.updateSettings(this.getSettings());
        }

        $timeout(function () {
            reportDeletionModel.initSettings(self.getSettings());
        });

        this.save = function () {

            if (this.enabled) {
                var confirmId = 'deleteReportsConfirm';
                if (reportDeletionModel.settings && '1' === reportDeletionModel.settings.enableDeleteLogs) {
                    confirmId = 'deleteBothConfirm';
                }
                $('#confirmDeleteSettings').find('>h2').hide();
                $("#" + confirmId).show();
                piwikHelper.modalConfirm('#confirmDeleteSettings', {yes: saveSettings});
            } else {
                saveSettings();
            }
        };
    }
})();