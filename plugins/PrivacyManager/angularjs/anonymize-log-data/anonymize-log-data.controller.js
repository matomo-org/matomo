/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('AnonymizeLogDataController', AnonymizeLogDataController);

    AnonymizeLogDataController.$inject = ["$scope", "piwikApi", "piwik", "$timeout"];

    function AnonymizeLogDataController($scope, piwikApi, piwik, $timeout) {
        function sub(value)
        {
            if (value < 10) {
                return '0' + value;
            }
            return value;
        }

        var self = this;
        var now = new Date();
        this.isLoading = false;
        this.isDeleting = false;
        this.anonymizeIp = false;
        this.anonymizeLocation = false;
        this.anonymizeUserId = false;
        this.site = {id: 'all', name: 'All Websites'};
        this.availableVisitColumns = [];
        this.availableActionColumns = [];
        this.selectedVisitColumns = [{column: ''}];
        this.selectedActionColumns = [{column: ''}];
        this.start_date = now.getFullYear() + '-' +  sub(now.getMonth() + 1) +  '-' + sub(now.getDay() + 1);
        this.end_date = this.start_date;

        piwikApi.fetch({method: 'PrivacyManager.getAvailableVisitColumnsToAnonymize'}).then(function (columns) {
            self.availableVisitColumns = [];
            angular.forEach(columns, function (column) {
                self.availableVisitColumns.push({key: column.column_name, value: column.column_name});
            });
        });

        piwikApi.fetch({method: 'PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize'}).then(function (columns) {
            self.availableActionColumns = [];

            angular.forEach(columns, function (column) {
                self.availableActionColumns.push({key: column.column_name, value: column.column_name});
            });
        });

        this.onVisitColumnChange = function () {
            var hasAll = true;
            angular.forEach(this.selectedVisitColumns, function (visitColumn) {
                if (!visitColumn || !visitColumn.column) {
                    hasAll = false;
                }
            });
            if (hasAll) {
                this.addVisitColumn();
            }
        };

        this.addVisitColumn = function () {
            this.selectedVisitColumns.push({column: ''});
        };

        this.removeVisitColumn = function (index) {
            if (index > -1) {
                var lastIndex = this.selectedVisitColumns.length - 1;
                if (lastIndex === index) {
                    this.selectedVisitColumns[index] = {column: ''};
                } else {
                    this.selectedVisitColumns.splice(index, 1);
                }
            }
        };

        this.onActionColumnChange = function () {
            var hasAll = true;
            angular.forEach(this.selectedActionColumns, function (actionColumn) {
                if (!actionColumn || !actionColumn.column) {
                    hasAll = false;
                }
            });
            if (hasAll) {
                this.addActionColumn();
            }
        };

        this.addActionColumn = function () {
            this.selectedActionColumns.push({column: ''});
        };

        this.removeActionColumn = function (index) {
            if (index > -1) {
                var lastIndex = this.selectedActionColumns.length - 1;
                if (lastIndex === index) {
                    this.selectedActionColumns[index] = {column: ''};
                } else {
                    this.selectedActionColumns.splice(index, 1);
                }
            }
        };

        this.scheduleAnonymization = function () {
            var date = this.start_date + ',' + this.end_date;
            if (this.start_date === this.end_date) {
                date = this.start_date;
            }

            var params = {date: date};
            params.idSites = this.site.id;
            params.anonymizeIp = this.anonymizeIp ? '1' : '0';
            params.anonymizeLocation = this.anonymizeLocation ? '1' : '0';
            params.anonymizeUserId = this.anonymizeUserId ? '1' : '0';
            params.unsetVisitColumns = [];
            params.unsetLinkVisitActionColumns = [];
            angular.forEach(this.selectedVisitColumns, function (column) {
                if (column.column) {
                    params.unsetVisitColumns.push(column.column);
                }
            });
            angular.forEach(this.selectedActionColumns, function (column) {
                if (column.column) {
                    params.unsetLinkVisitActionColumns.push(column.column);
                }
            });

            piwik.helper.modalConfirm('#confirmAnonymizeLogData', {yes: function () {
                piwikApi.post({method: 'PrivacyManager.anonymizeSomeRawData'}, params).then(function () {
                    location.reload(true);
                });
            }});
        };

        $timeout(function () {
            var options1 = piwik.getBaseDatePickerOptions(null);
            var options2 = piwik.getBaseDatePickerOptions(null);

            $(".anonymizeStartDate").datepicker(options1);
            $(".anonymizeEndDate").datepicker(options2);
        });

    }
})();
