/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * <div piwik-dashboard dashboard-id="5"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikDashboard', piwikDashboard);

    piwikDashboard.$inject = ['dashboardsModel', '$rootScope', '$q'];

    function piwikDashboard(dashboardsModel, $rootScope, $q) {

        function renderDashboard(dashboardId, dashboard, layout)
        {
            $('.dashboardSettings').show();
            initTopControls();

            // Embed dashboard / exported as widget
            if (!$('#topBars').length) {
                $('.dashboardSettings').after($('#Dashboard'));
                $('#Dashboard ul li').removeClass('active');
                $('#Dashboard_embeddedIndex_' + dashboardId).addClass('active');
            }

            widgetsHelper.getAvailableWidgets();

            $('#dashboardWidgetsArea').off('dashboardempty', showEmptyDashboardNotification);
            $('#dashboardWidgetsArea')
                .on('dashboardempty', showEmptyDashboardNotification)
                .dashboard({
                    idDashboard: dashboardId,
                    layout: layout,
                    name: dashboard ? dashboard.name : ''
                });

            var divElements = $('#columnPreview').find('>div');

            divElements.each(function () {
                var width = [];
                $('div', this).each(function () {
                    width.push(this.className.replace(/width-/, ''));
                });
                $(this).attr('layout', width.join('-'));
            });

            divElements.off('click.renderDashboard');
            divElements.on('click.renderDashboard', function () {
                divElements.removeClass('choosen');
                $(this).addClass('choosen');
            });
        }

        function fetchDashboard(dashboardId) {
            var dashboardElement = $('#dashboardWidgetsArea');
            dashboardElement.dashboard('destroyWidgets');
            dashboardElement.empty();
            globalAjaxQueue.abort();

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

                function onLocationChange(event, newUrl, oldUrl)
                {
                   if (broadcast.getValueFromUrl('module') != 'Widgetize' && newUrl !== oldUrl &&
                       newUrl.indexOf('category=Dashboard_Dashboard') === -1) {
                       // we remove the dashboard only if we no longer show a dashboard.
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