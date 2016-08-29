/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('DeleteOldLogsController', DeleteOldLogsController);

    DeleteOldLogsController.$inject = ['reportDeletionModel', 'piwikApi', '$timeout'];

    function DeleteOldLogsController(reportDeletionModel, piwikApi, $timeout) {

        var self = this;
        
        this.isLoading = false;

        function saveSettings()
        {
            var method = 'PrivacyManager.setDeleteLogsSettings';
            reportDeletionModel.savePurageDataSettings(self, method, self.getSettings());
        }

        this.getSettings = function () {
            return {
                enableDeleteLogs: this.enabled ? '1' : '0',
                deleteLogsOlderThan: this.deleteOlderThan
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
                var confirmId = 'deleteLogsConfirm';
                if (reportDeletionModel.settings && '1' === reportDeletionModel.settings.enableDeleteReports) {
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