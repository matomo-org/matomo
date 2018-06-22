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
            metricTranslations: '<',
            sparklineRange: '<'
        },
        controller: SingleMetricViewController
    });

    SingleMetricViewController.$inject = ['piwik', 'piwikApi', '$element', '$httpParamSerializer'];

    function SingleMetricViewController(piwik, piwikApi, $element, $httpParamSerializer) {
        var vm = this;
        vm.metricValue = null;
        vm.$onChanges = $onChanges;
        vm.getSparklineUrl = getSparklineUrl;

        function $onChanges(changes) {
            setWidgetTitle();

            if (vm.metric === changes.metric) {
                return;
            }

            // done manually due to 'random' query param. since it changes the URL on each digest, depending on angular
            // results in an infinite digest
            updateSparklineSrc();

            piwikApi.fetch({
                method: 'API.get',
            }).then(function (response) {
                vm.metricValue = response[vm.metric];
            });
        }

        function setWidgetTitle() {
            var translation = vm.metricTranslations[vm.metric];
            $element.closest('[widgetId').find('.widgetTop > .widgetName > span').text(translation);
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
    }
})();
