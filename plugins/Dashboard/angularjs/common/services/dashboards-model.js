/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').factory('dashboardsModel', dashboardsModel);

    dashboardsModel.$inject = ['piwikApi'];

    function dashboardsModel (piwikApi) {

        var dashboardsPromise = null;

        var model = {
            dashboards: [],
            getAllDashboards: getAllDashboards,
            reloadAllDashboards: reloadAllDashboards,
            getDashboard: getDashboard,
            getDashboardLayout: getDashboardLayout
        };

        return model;

        function getDashboard(dashboardId)
        {
            return getAllDashboards().then(function (dashboards) {
                var dashboard = null;
                angular.forEach(dashboards, function (board) {
                    if (parseInt(board.id, 10) === parseInt(dashboardId, 10)) {
                        dashboard = board;
                    }
                });
                return dashboard;
            });
        }

        function getDashboardLayout(dashboardId)
        {
            piwikApi.withTokenInUrl();

            return piwikApi.fetch({module: 'Dashboard', action: 'getDashboardLayout', idDashboard: dashboardId});
        }

        function reloadAllDashboards()
        {
            if (dashboardsPromise) {
                dashboardsPromise = null;
            }

            return getAllDashboards();
        }

        function getAllDashboards()
        {
            if (!dashboardsPromise) {
                dashboardsPromise = piwikApi.fetch({method: 'Dashboard.getDashboards', filter_limit: '-1'}).then(function (response) {
                    if (response) {
                        model.dashboards = response;
                    }

                    return response;
                });
            }

            return dashboardsPromise;
        }
    }
})();