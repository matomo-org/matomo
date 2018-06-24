/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-single-metric-view>
 */
(function () {
    angular.module('piwikApp').component('piwikSingleMetricView', {
        templateUrl: 'plugins/CoreVisualizations/angularjs/single-metric-view/single-metric-view.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            metric: '<',
            metricValue: '<',
            pastValue: '<',
            metricChangePercent: '<',
            metricName: '<',
            metricDocumentation: '<',
            sparklineRange: '<',
            pastPeriod: '<',
        },
        controller: SingleMetricViewController
    });

    SingleMetricViewController.$inject = ['piwik', 'piwikApi', '$element', '$httpParamSerializer'];

    function SingleMetricViewController(piwik, piwikApi, $element, $httpParamSerializer) {
        var vm = this;
        vm.metricValue = null;
        vm.metricDocumentation = null;
        vm.$onChanges = $onChanges;
        vm.getSparklineUrl = getSparklineUrl;
        vm.getCurrentPeriod = getCurrentPeriod;

        function $onChanges() {
            setWidgetTitle();

            // done manually due to 'random' query param. since it changes the URL on each digest, depending on angular
            // results in an infinite digest
            updateSparklineSrc();
        }

        function setWidgetTitle() {
            $element.closest('[widgetId').find('.widgetTop > .widgetName > span').text(vm.metricName);
        }

        function updateSparklineSrc() {
            $element.find('img').attr('src', getSparklineUrl());
        }

        function getSparklineUrl() {
            var urlParams = piwikApi.mixinDefaultGetParams({
                forceView: '1',
                viewDataTable: 'sparkline',
                module: 'API',
                action: 'get',
                widget: '1',
                showtitle: '1',
                columns: vm.metric,
                colors: JSON.stringify(piwik.getSparklineColors()),
                date: vm.sparklineRange,
                random: Date.now()
            });

            return '?' + $httpParamSerializer(urlParams);
        }

        function getCurrentPeriod() {
            if (piwik.startDateString === piwik.endDateString) {
                return piwik.endDateString;
            }
            return piwik.startDateString + ', ' + piwik.endDateString;
        }
    }
})();
