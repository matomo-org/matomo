/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageGdprController', ManageGdprController);

    ManageGdprController.$inject = ["$scope", "piwikApi", "piwik", "$timeout"];

    function ManageGdprController($scope, piwikApi, piwik, $timeout) {

        var self = this;
        this.isLoading = false;
        this.isDeleting = false;
        this.site = {id: 'all', name: 'All Websitees'};
        this.segment_filter = 'userId==';
        this.dataSubjects = [];
        this.toggleAll = true;
        this.hasSearched = false;
        this.emailAddress = '';
        this.emailSubject = $scope.defaultEmailSubject;
        this.emailMessage = $scope.defaultEmailMessage;

        function showSuccessNotification(message)
        {
            var UI = require('piwik/UI');
            var notification = new UI.Notification();
            notification.show(message, {context: 'success', id: 'manageGdpr'});
        }

        this.toggleActivateAll = function () {
            var toggleAll = this.toggleAll;
            angular.forEach(this.dataSubjects, function (dataSubject) {
                dataSubject.dataSubjectActive = toggleAll;
            });
        };

        this.hasActiveDataSubjects = function()
        {
            return !!this.getActivatedDataSubjects().length;
        };

        this.emailDataSubject = function () {
            var visitsToDelete = this.getActivatedDataSubjects();
            if (!visitsToDelete || !visitsToDelete.length) {
                return;
            }

            piwik.helper.modalConfirm('#emailDataSubject', {yes: function () {
                if (!self.emailAddress) {
                    return;
                }
                piwikApi.post({
                    module: 'API',
                    method: 'PrivacyManager.emailDataSubjectExport',
                    filter_limit: -1,
                }, {visits: visitsToDelete,
                    emailAddress: self.emailAddress,
                    subject: self.emailSubject,
                    message: self.emailMessage}).then(function () {

                    showSuccessNotification('Email was successfully sent');
                    $timeout(function () {
                        notification.scrollToNotification();
                    }, 200);
                });
                self.emailAddress = '';
                // we keep subject and message the same, only clear email
            }});
        };

        this.getActivatedDataSubjects = function () {
            var visitsToDelete = [];

            angular.forEach(this.dataSubjects, function (visit) {
                if (visit.dataSubjectActive) {
                    visitsToDelete.push({idSite: visit.idSite, idVisit: visit.idVisit});
                }
            });
            return visitsToDelete;
        }

        this.showProfile = function (visitorId, idSite) {
            require('piwik/UI').VisitorProfileControl.showPopover(visitorId, idSite);
        };

        this.exportDataSubject = function () {
            function sendContentAsDownload(filename, content) {
                var mimeType = 'text/plain';
                function downloadFile(content)
                {
                    var node = document.createElement('a');
                    node.style.display = 'none';
                    if ('string' === typeof content) {
                        node.setAttribute('href', 'data:' + mimeType + ';charset=utf-8,' + encodeURIComponent(content));
                    } else {
                        node.href = window.URL.createObjectURL(blob);
                    }
                    node.setAttribute('download', filename);
                    document.body.appendChild(node);
                    node.click();
                    document.body.removeChild(node);
                }

                var node;
                if ('function' === typeof Blob) {
                    // browser supports blob
                    try {
                        var blob = new Blob([content], {type: mimeType});
                        if (window.navigator.msSaveOrOpenBlob) {
                            window.navigator.msSaveBlob(blob, filename);
                            return;
                        } else {
                            downloadFile(blob);
                            return;
                        }
                    } catch (e) {
                        downloadFile(content);
                    }
                }
                downloadFile(content);
            }

            var visitsToDelete = this.getActivatedDataSubjects();
            piwikApi.post({
                module: 'API',
                method: 'PrivacyManager.exportDataSubjects',
                format: 'json',
                filter_limit: -1,
            }, {visits: visitsToDelete}).then(function (visits) {
                showSuccessNotification('Visits were successfully deleted');
                sendContentAsDownload('exported_data_subjects.json', JSON.stringify(visits));
            });
        };

        this.deleteDataSubject = function () {
            piwik.helper.modalConfirm('#confirmDeleteDataSubject', {yes: function () {
                self.isDeleting = true;
                var visitsToDelete = self.getActivatedDataSubjects();

                piwikApi.post({
                    module: 'API',
                    method: 'PrivacyManager.deleteDataSubjects',
                    filter_limit: -1,
                }, {visits: visitsToDelete}).then(function (visits) {
                    self.dataSubjects = [];
                    self.isDeleting = false;
                    showSuccessNotification('Visits were successfully deleted');
                }, function () {
                    self.isDeleting = false;
                });
            }});
        };

        this.addFilter = function (segment, value) {
            this.segment_filter += ',' + segment + '==' + value;
            this.findDataSubjects();
        };
        
        this.findDataSubjects = function () {
            this.dataSubjects = [];
            this.isLoading = true;
            this.toggleAll = true;

            piwikApi.fetch({
                idSite: this.site.id,
                period: 'range',
                date: '1998-01-01,today',
                module: 'API',
                method: 'Live.getLastVisitsDetails',
                segment: this.segment_filter,
                filter_limit: -1,
                doNotFetchActions: 1
            }).then(function (visits) {
                self.hasSearched = true;
                angular.forEach(visits, function (visit) {
                    visit.dataSubjectActive = true;
                });
                self.dataSubjects = visits;
                self.isLoading = false;
            }, function () {
                self.isLoading = false;
            });
        };
    }
})();
