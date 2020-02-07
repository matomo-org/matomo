/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('TrackingFailuresController', TrackingFailuresController);

    TrackingFailuresController.$inject = ['piwikApi', 'piwik'];

    function TrackingFailuresController(piwikApi, piwik){
        var self = this;
        this.failures = [];
        this.sortColumn = 'idsite';
        this.sortReverse = false;
        this.isLoading = false;

        this.changeSortOrder = function (columnToSort) {
            if (this.sortColumn === columnToSort) {
                this.sortReverse = !this.sortReverse;
            } else {
                this.sortColumn = columnToSort;
            }
        };

        this.fetchAll = function () {
            this.failures = [];
            this.isLoading = true;
            piwikApi.fetch({method: 'CoreAdminHome.getTrackingFailures', filter_limit: '-1'}).then(function (failures) {
                self.failures = failures;
                self.isLoading = false;
            }, function () {
                self.isLoading = false;
            });
        };

        this.deleteAll = function () {
            piwik.helper.modalConfirm('#confirmDeleteAllTrackingFailures', {yes: function () {
                self.failures = [];
                piwikApi.fetch({method: 'CoreAdminHome.deleteAllTrackingFailures'}).then(function () {
                    self.fetchAll();
                });
            }});
        };

        this.deleteFailure = function (idSite, idFailure) {
            piwik.helper.modalConfirm('#confirmDeleteThisTrackingFailure', {yes: function () {
                self.failures = [];
                piwikApi.fetch({method: 'CoreAdminHome.deleteTrackingFailure', idSite: idSite, idFailure: idFailure}).then(function () {
                    self.fetchAll();
                });
            }});
        };

        this.fetchAll();
    }

})();
