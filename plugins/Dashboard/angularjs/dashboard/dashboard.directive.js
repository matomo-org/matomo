/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * <div piwik-dashboard dashboardId="5" layout="10"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikDashboard', piwikDashboard);

    piwikDashboard.$inject = ['dashboardsModel', '$rootScope', '$q'];

    function piwikDashboard(dashboardsModel, $rootScope, $q) {

        function renderDashboard(dashboardId, dashboard, layout)
        {
            $('.dashboardSettings').show();
            initTopControls();

            // Embed dashboard
            if (!$('#topBars').length) {
                $('.dashboardSettings').after($('#Dashboard'));
                $('#Dashboard_embeddedIndex_' + dashboardId).addClass('sfHover');
            }

            widgetsHelper.getAvailableWidgets();

            $('#dashboardWidgetsArea')
                .on('dashboardempty', showEmptyDashboardNotification)
                .dashboard({
                    idDashboard: dashboardId,
                    layout: layout,
                    name: dashboard ? dashboard.name : ''
                });

            $('#columnPreview').find('>div').each(function () {
                var width = [];
                $('div', this).each(function () {
                    width.push(this.className.replace(/width-/, ''));
                });
                $(this).attr('layout', width.join('-'));
            });

            $('#columnPreview').find('>div').on('click', function () {
                $('#columnPreview').find('>div').removeClass('choosen');
                $(this).addClass('choosen');
            });
        }

        function fetchDashboard(dashboardId) {
            $('#dashboardWidgetsArea').innerHTML ='';

            var getDashboard = dashboardsModel.getDashboard(dashboardId);
            var getLayout = dashboardsModel.getDashboardLayout(dashboardId);

            $q.all([getDashboard, getLayout]).then(function (response) {
                var dashboard = response[0];
                var layout = response[1];

                $(function() {
                    renderDashboard(dashboardId, dashboard, layout);
                });
            });
        }

        function clearDashboard () {
            $('.top_controls .dashboard-manager').hide();
            $('#dashboardWidgetsArea').dashboard('destroy');
        }

        return {
            restrict: 'A',
            scope: {
                dashboardid: '=',
                layout: '='
            },
            link: function (scope, element, attrs) {

                scope.$parent.fetchDashboard = function (dashboardId) {
                    scope.dashboardId = dashboardId;
                    fetchDashboard(dashboardId)
                };

                fetchDashboard(scope.dashboardid);

                function onLocationChange(event, url1, url2)
                {
                   if (url1 !== url2) {
                       clearDashboard();
                   }
                }

                // should be rather handled in route or so.
                var unbind = $rootScope.$on('$locationChangeSuccess', onLocationChange);
                scope.$on('$destroy', onLocationChange);
                scope.$on('$destroy', unbind);
            }
        };
    }
})();