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

            // TODO: could also use processed report instead of setting these?
            metricTranslations: '<',
            metricDocumentations: '<',

            sparklineRange: '<', // TODO: should not be bound
            pastPeriod: '<', // TODO: should not be bound
        },
        controller: SingleMetricViewController
    });

    SingleMetricViewController.$inject = ['piwik', 'piwikApi', '$element', '$httpParamSerializer', '$compile', '$scope'];

    function SingleMetricViewController(piwik, piwikApi, $element, $httpParamSerializer, $compile, $scope) {
        var seriesPickerScope;
        var $seriesPicker;

        var vm = this;
        vm.metricValue = null;
        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.$onDestroy = $onDestroy;
        vm.getSparklineUrl = getSparklineUrl;
        vm.getCurrentPeriod = getCurrentPeriod;
        vm.getMetricTranslation = getMetricTranslation;
        vm.getMetricDocumentation = getMetricDocumentation;
        vm.setMetric = setMetric;

        function $onInit() {
            setSelectableColumns();
            vm.selectedColumns = [vm.metric];

            createSeriesPicker();

            $element.closest('.widgetContent')
                .on('widget:destroy', destroyComponent)
                .on('widget:reload', destroyComponent);
        }

        function destroyComponent() {
            $scope.$parent.$destroy();
        }

        function $onChanges(changes) {
            if (changes.metric && changes.metric.previousValue !== changes.metric.currentValue) {
                onMetricChanged();
            }
        }

        function fetchData() {
            piwikApi.fetch({
                method: 'API.get',
                filter_last_period_evolution: '1'
            }).then(function (response) {
                vm.metricValue = response[vm.metric];
                vm.pastValue = response[vm.metric + '_past'];
                vm.metricChangePercent = response[vm.metric + '_evolution'];
            });
        }

        function $onDestroy() {
            $element.closest('.widgetContent').off('widget:destroy').off('widget:reload');
            destroySeriesPicker();
        }

        function setWidgetTitle() {
            $element.closest('[widgetId').find('.widgetTop > .widgetName > span').text(vm.getMetricTranslation());
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

        function createSeriesPicker() {
            $seriesPicker = $('<piwik-series-picker class="single-metric-view-picker" multiselect="false" ' +
                'selectable-columns="$ctrl.selectableColumns" selectable-rows="[]" selected-columns="$ctrl.selectedColumns" ' +
                'selected-rows="[]" on-select="$ctrl.setMetric(columns[0])" />');

            seriesPickerScope = $scope.$new();
            $compile($seriesPicker)(seriesPickerScope);

            $element.closest('[widgetId').find('.widgetTop > .widgetName').append($seriesPicker);
        }

        function destroySeriesPicker() {
            $seriesPicker.remove();
            seriesPickerScope.$destroy();
        }

        function getMetricDocumentation() {
            if (!vm.metricDocumentations || !vm.metricDocumentations[vm.metric]) {
                return '';
            }

            return vm.metricDocumentations[vm.metric];
        }

        function getMetricTranslation() {
            if (!vm.metricTranslations || !vm.metricTranslations[vm.metric]) {
                return '';
            }

            return vm.metricTranslations[vm.metric];
        }

        function setSelectableColumns() {
            var result = [];
            Object.keys(vm.metricTranslations).forEach(function (column) {
                result.push({ column: column, translation: vm.metricTranslations[column] });
            });
            vm.selectableColumns = result;
        }

        function onMetricChanged() {
            vm.selectedColumns = [vm.metric];

            setWidgetTitle();

            fetchData();

            // done manually due to 'random' query param. since it changes the URL on each digest, depending on angular
            // results in an infinite digest
            updateSparklineSrc();
        }

        function setMetric(newColumn) {
            vm.metric = newColumn;
            onMetricChanged();
        }
    }
})();
