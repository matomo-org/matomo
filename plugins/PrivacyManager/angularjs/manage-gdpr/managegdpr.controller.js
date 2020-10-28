/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageGdprController', ManageGdprController);

    ManageGdprController.$inject = ["$scope", "piwikApi", "piwik", "$timeout"];

    function ManageGdprController($scope, piwikApi, piwik, $timeout) {

        var self = this;
        this.isLoading = false;
        this.isDeleting = false;
        this.site = {id: 'all', name: 'All Websites'};
        this.segment_filter = 'userId==';
        this.dataSubjects = [];
        this.toggleAll = true;
        this.hasSearched = false;
        this.profileEnabled = piwik.visitorProfileEnabled;

        var sitesPromise = piwikApi.fetch({method: 'SitesManager.getSitesIdWithAdminAccess', filter_limit: '-1'});

        this.linkTo = function (action){
            var currentUrl = window.location.pathname + window.location.search;
            var newUrl = piwik.broadcast.updateParamValue('module=PrivacyManager', currentUrl);
            newUrl = piwik.broadcast.updateParamValue('action=' + action, newUrl);
            return newUrl;
        }

        function showSuccessNotification(message)
        {
            var UI = require('piwik/UI');
            var notification = new UI.Notification();
            notification.show(message, {context: 'success', id: 'manageGdpr'});

            $timeout(function () {
                notification.scrollToNotification();
            }, 200);
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

        this.getActivatedDataSubjects = function () {
            var visitsToDelete = [];

            angular.forEach(this.dataSubjects, function (visit) {
                if (visit.dataSubjectActive) {
                    visitsToDelete.push({idsite: visit.idSite, idvisit: visit.idVisit});
                }
            });
            return visitsToDelete;
        }

        this.showProfile = function (visitorId, idSite) {
            require('piwik/UI').VisitorProfileControl.showPopover(visitorId, idSite);
        };

        this.exportDataSubject = function () {
            var visitsToDelete = this.getActivatedDataSubjects();
            piwikApi.post({
                module: 'API',
                method: 'PrivacyManager.exportDataSubjects',
                format: 'json',
                filter_limit: -1,
            }, {visits: visitsToDelete}).then(function (visits) {
                showSuccessNotification('Visits were successfully exported');
                piwik.helper.sendContentAsDownload('exported_data_subjects.json', JSON.stringify(visits));
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
                    self.findDataSubjects();
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

            function addDatePadding(number)
            {
                if (number < 10) {
                    return '0' + number;
                }
                return number;
            }

            var now = new Date();
            var dateString = (now.getFullYear() + 2) + '-' + addDatePadding(now.getMonth() + 1) + '-' + addDatePadding(now.getDay());
            // we are adding two years to make sure to also capture some requests in the future as we fetch data across
            // different sites and different timezone and want to avoid missing any possible requests

            sitesPromise.then(function (idsites) {

                var siteIds = self.site.id;
                if (siteIds === 'all' && !piwik.hasSuperUserAccess) {
                    // when superuser, we speed the request up a little and simply use 'all'
                    siteIds = idsites;
                    if (angular.isArray(idsites)) {
                        siteIds = idsites.join(',');
                    }
                }

                piwikApi.fetch({
                    idSite: siteIds,
                    module: 'API',
                    method: 'PrivacyManager.findDataSubjects',
                    segment: self.segment_filter,
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
            });
        };
    }
})();
