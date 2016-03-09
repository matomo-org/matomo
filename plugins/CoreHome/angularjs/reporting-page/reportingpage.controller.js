/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReportingPageController', ReportingPageController);

    ReportingPageController.$inject = ['$scope', 'piwik', '$rootScope', '$location', 'reportingPageModel'];

    function ReportingPageController($scope, piwik, $rootScope, $location, pageModel) {
        pageModel.resetPage();
        $scope.pageModel = pageModel;

        var currentCategory = null;
        var currentSubcategory = null;

        $scope.renderPage = function (category, subcategory) {
            if (!category || !subcategory) {
                pageModel.resetPage();
                $scope.loading = false;
                return;
            }

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
                $scope.hasNoPage = !pageModel.page;
                $scope.loading = false;
            });
        }

        $scope.loading = true; // we only set loading on initial load

        $scope.renderPage($location.search().category, $location.search().subcategory);

        $rootScope.$on('$locationChangeSuccess', function () {
            // should be handled by $route
            var category = $location.search().category;
            var subcategory = $location.search().subcategory;

            if (category === currentCategory && subcategory === currentSubcategory) {
                // this page is already loaded
                return;
            }

            $scope.renderPage(category, subcategory);
        });

        $rootScope.$on('loadPage', function (event, category, subcategory) {
            $scope.renderPage(category, subcategory);
        });
    }
})();
