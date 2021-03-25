/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReportingPageController', ReportingPageController);

    ReportingPageController.$inject = ['$scope', 'piwik', '$rootScope', '$location', 'reportingPageModel', 'reportingPagesModel', 'notifications', 'piwikUrl', 'piwikPeriods', 'piwikApi'];

    function ReportingPageController($scope, piwik, $rootScope, $location, pageModel, pagesModel, notifications, piwikUrl, $piwikPeriods, piwikApi) {
        pageModel.resetPage();
        $scope.pageModel = pageModel;

        var currentCategory = null;
        var currentSubcategory = null;
        var currentPeriod = null;
        var currentDate = null;
        var currentSegment = null;

        var currentCompareDates = null;
        var currentComparePeriods = null;
        var currentCompareSegments = null;

        var hasRawData = false;
        var hasNoVisits = false;
        var dateLastChecked = null;

        var UI = require('piwik/UI');
        var notification = new UI.Notification();

        function renderInitialPage()
        {
            var $search = $location.search();
            currentPeriod = piwikUrl.getSearchParam('period');
            currentDate = piwikUrl.getSearchParam('date');
            currentSegment = $search.segment;
            currentCompareSegments = piwikUrl.getSearchParam('compareSegments');
            currentCompareDates = piwikUrl.getSearchParam('compareDates');
            currentComparePeriods = piwikUrl.getSearchParam('comparePeriods');
            $scope.renderPage($search.category, $search.subcategory);
        }

        function showOnlyRawDataNotification() {
            var attributes = {};
            attributes.id = 'onlyRawData';
            attributes.animate = false;
            attributes.context = 'info';
            var url = broadcast.buildReportingUrl('category=General_Visitors&subcategory=Live_VisitorLog')
            var message = _pk_translate('CoreHome_PeriodHasOnlyRawData', ['<a href="' + url + '">', '</a>']);
            notification.show(message, attributes);
        }

        function hideOnlyRawDataNoticifation() {
            notification.remove('onlyRawData');
        }

        function showOnlyRawDataMessageIfRequired() {
            if (hasRawData && hasNoVisits) {
                showOnlyRawDataNotification();
            }

            var $search = $location.search();

            if ($search.segment !== '') {
                hideOnlyRawDataNoticifation();
                return;
            }

            var subcategoryExceptions = [
                'Live_VisitorLog',
                'General_RealTime',
                'UserCountryMap_RealTimeMap',
                'MediaAnalytics_TypeAudienceLog',
                'MediaAnalytics_TypeRealTime',
                'FormAnalytics_TypeRealTime',
                'Goals_AddNewGoal',
            ];

            var categoryExceptions = [
                'HeatmapSessionRecording_Heatmaps',
                'HeatmapSessionRecording_SessionRecordings',
                'Marketplace_Marketplace',
            ];

            if (subcategoryExceptions.indexOf($search.subcategory) !== -1 || categoryExceptions.indexOf($search.category) !== -1 || $search.subcategory.toLowerCase().indexOf('manage') !== -1) {
                hideOnlyRawDataNoticifation();
                return;
            }

            var minuteInMilliseconds = 60000;
            if (dateLastChecked && (new Date().getTime() - dateLastChecked) < minuteInMilliseconds) {
                return;
            }

            piwikApi.fetch({ method: 'VisitsSummary.getVisits' }).then(function (json) {
                dateLastChecked = new Date().getTime();

                if (json.value > 0) {
                    hasNoVisits = false;
                    hideOnlyRawDataNoticifation();
                    return;
                }

                hasNoVisits = true;

                if (hasRawData) {
                    showOnlyRawDataNotification();
                    return;
                }

                piwikApi.fetch({ method: 'Live.getLastVisitsDetails', filter_limit: 1, doNotFetchActions: 1 }).then(function (json)  {
                    if (json.length == 0) {
                        hasRawData = false;
                        hideOnlyRawDataNoticifation();
                        return;
                    }

                    hasRawData = true;
                    showOnlyRawDataNotification();
                });
            });
        }

        $scope.renderPage = function (category, subcategory) {
            if (!category || !subcategory) {
                pageModel.resetPage();
                $scope.loading = false;
                return;
            }

            try {
                $piwikPeriods.parse(currentPeriod, currentDate);
            } catch (e) {
                var attributes = {};
                attributes.id = 'invalidDate';
                attributes.animate = false;
                attributes.context = 'error';
                notification.show(_pk_translate('CoreHome_DateInvalid'), attributes);

                pageModel.resetPage();
                $scope.loading = false;
                return;
            }

            notification.remove('invalidDate');

            $rootScope.$emit('piwikPageChange', {});

            currentCategory = category;
            currentSubcategory = subcategory;

            notifications.clearTransientNotifications();

            var dateRange = $piwikPeriods.parse(currentPeriod, currentDate).getDateRange();
            if ($piwikPeriods.todayIsInRange(dateRange)) {
                showOnlyRawDataMessageIfRequired();
            }

            if (category === 'Dashboard_Dashboard' && $.isNumeric(subcategory) && $('[piwik-dashboard]').length) {
                // hack to make loading of dashboards faster since all the information is already there in the
                // piwik-dashboard widget, we can let the piwik-dashboard widget render the page. We need to find
                // a proper solution for this. A workaround for now could be an event or something to let other
                // components render a specific page.
                $scope.loading = true;
                var element = $('[piwik-dashboard]');
                var scope = angular.element(element).scope();
                scope.fetchDashboard(parseInt(subcategory, 10)).then(function () {
                    $scope.loading = false;
                }, function () {
                    $scope.loading = false;
                });
                return;
            }

            pageModel.fetchPage(category, subcategory).then(function () {
                if (!pageModel.page) {
                    var page = pagesModel.findPageInCategory(category);
                    if (page && page.subcategory) {
                        var $search = $location.search();
                        $search.subcategory = page.subcategory.id;
                        $location.search($search);
                        return;
                    }
                }

                $scope.hasNoPage = !pageModel.page;
                $scope.loading = false;
            });
        };

        $scope.loading = true; // we only set loading on initial load

        renderInitialPage();

        $rootScope.$on('$locationChangeSuccess', function () {
            var $search = $location.search();

            // should be handled by $route
            var category = $search.category;
            var subcategory = $search.subcategory;
            var period = piwikUrl.getSearchParam('period');
            var date = piwikUrl.getSearchParam('date');
            var segment = $search.segment;

            // $location does not handle array parameters properly
            var compareSegments = piwikUrl.getSearchParam('compareSegments');
            var compareDates = piwikUrl.getSearchParam('compareDates');
            var comparePeriods = piwikUrl.getSearchParam('comparePeriods');

            if (category === currentCategory
                && subcategory === currentSubcategory
                && period === currentPeriod
                && date === currentDate
                && segment === currentSegment
                && JSON.stringify(compareDates) === JSON.stringify(currentCompareDates)
                && JSON.stringify(comparePeriods) === JSON.stringify(currentComparePeriods)
                && JSON.stringify(compareSegments) === JSON.stringify(currentCompareSegments)
            ) {
                // this page is already loaded
                return;
            }

            if (date !== currentDate || period !== currentPeriod) {
                hideOnlyRawDataNoticifation();
                dateLastChecked = null;
                hasRawData = false;
                hasNoVisits = false;
            }

            currentPeriod = period;
            currentDate = date;
            currentSegment = segment;
            currentCompareDates = compareDates;
            currentComparePeriods = comparePeriods;
            currentCompareSegments = compareSegments;

            $scope.renderPage(category, subcategory);
        });

        $rootScope.$on('loadPage', function (event, category, subcategory) {
            $scope.renderPage(category, subcategory);
        });
    }
})();
