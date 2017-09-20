/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReportingPageController', ReportingPageController);

    ReportingPageController.$inject = ['$scope', 'piwik', '$rootScope', '$location', 'reportingPageModel', 'reportingPagesModel'];

    function ReportingPageController($scope, piwik, $rootScope, $location, pageModel, pagesModel) {
        pageModel.resetPage();
        $scope.pageModel = pageModel;

        var currentCategory = null;
        var currentSubcategory = null;
        var currentPeriod = null;
        var currentDate = null;
        var currentSegment = null;

        function renderInitialPage()
        {
            var $search = $location.search();
            currentPeriod = $search.period;
            currentDate = $search.date;
            currentSegment = $search.segment;
            $scope.renderPage($search.category, $search.subcategory);
        }

        $scope.renderPage = function (category, subcategory) {
            if (!category || !subcategory) {
                pageModel.resetPage();
                $scope.loading = false;
                return;
            }

            $rootScope.$emit('piwikPageChange', {});

            currentCategory = category;
            currentSubcategory = subcategory;

            if (category === 'Dashboard_Dashboard' && $.isNumeric(subcategory) && $('[piwik-dashboard]').length) {
                // hack to make loading of dashboards faster since all the information is already there in the
                // piwik-dashboard widget, we can let the piwik-dashboard widget render the page. We need to find
                // a proper solution for this. A workaround for now could be an event or something to let other
                // components render a specific page.
                $scope.loading = false;
                var element = $('[piwik-dashboard]');
                var scope = angular.element(element).scope();
                scope.fetchDashboard(parseInt(subcategory, 10));
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
        }

        $scope.loading = true; // we only set loading on initial load
        
        renderInitialPage();

        $rootScope.$on('$locationChangeSuccess', function () {
            var $search = $location.search();

            // should be handled by $route
            var category = $search.category;
            var subcategory = $search.subcategory;
            var period = $search.period;
            var date = $search.date;
            var segment = $search.segment;

            if (category === currentCategory
                && subcategory === currentSubcategory
                && period === currentPeriod
                && date === currentDate
                && segment === currentSegment) {
                // this page is already loaded
                return;
            }

            currentPeriod = period;
            currentDate = date;
            currentSegment = segment;

            $scope.renderPage(category, subcategory);
        });

        $rootScope.$on('loadPage', function (event, category, subcategory) {
            $scope.renderPage(category, subcategory);
        });
    }
})();
