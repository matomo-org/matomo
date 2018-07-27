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
            metric: '<'
        },
        controller: SingleMetricViewController
    });

    SingleMetricViewController.$inject = ['piwik', 'piwikApi', '$element', '$httpParamSerializer', '$compile', '$scope', 'piwikPeriods', '$q'];

    function SingleMetricViewController(piwik, piwikApi, $element, $httpParamSerializer, $compile, $scope, piwikPeriods, $q) {
        var seriesPickerScope;

        var vm = this;
        vm.metricValue = null;
        vm.isLoading = false;
        vm.metricTranslation = null;
        vm.selectableColumns = [];
        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.$onDestroy = $onDestroy;
        vm.getCurrentPeriod = getCurrentPeriod;
        vm.getMetricTranslation = getMetricTranslation;
        vm.getMetricDocumentation = getMetricDocumentation;
        vm.setMetric = setMetric;

        function $onInit() {
            vm.selectedColumns = [vm.metric];
            if (piwik.period !== 'range') {
                vm.pastPeriod = getPastPeriodStr();
            }

            createSeriesPicker();

            $element.closest('.widgetContent')
                .on('widget:destroy', function() { $scope.$parent.$destroy(); })
                .on('widget:reload', function() { $scope.$parent.$destroy(); });
        }

        function $onChanges(changes) {
            if (changes.metric && changes.metric.previousValue !== changes.metric.currentValue) {
                onMetricChanged();
            }
        }

        function $onDestroy() {
            $element.closest('.widgetContent').off('widget:destroy').off('widget:reload');
            destroySeriesPicker();
        }

        function fetchData() {
            vm.isLoading = true;

            var promises = [];

            // first request for formatted data
            promises.push(piwikApi.fetch({
                method: 'API.getProcessedReport',
                apiModule: 'API',
                apiAction: 'get',
                format_metrics: 'all'
            }));

            // third request for past data (unformatted)
            if (piwik.period !== 'range') {
                // second request for unformatted data so we can calculate evolution
                promises.push(piwikApi.fetch({
                    method: 'API.get',
                    format_metrics: '0'
                }));

                promises.push(piwikApi.fetch({
                    method: 'API.get',
                    date: getLastPeriodDate(),
                    format_metrics: '0',
                }));
            }

            $q.all(promises).then(function (responses) {
                vm.metricTranslations = responses[0].metadata.metrics;
                vm.metricDocumentations = responses[0].metadata.metricsDocumentation;

                setSelectableColumns();
                setWidgetTitle();

                var currentData = responses[0].reportData;
                vm.metricValue = currentData[vm.metric] || 0;

                if (responses[1]) {
                    vm.metricValueUnformatted = responses[1][vm.metric];

                    var pastData = responses[2];
                    vm.pastValue = pastData[vm.metric] || 0;

                    var evolution = piwik.helper.calculateEvolution(vm.metricValueUnformatted, vm.pastValue);
                    vm.metricChangePercent = (evolution * 100).toFixed(2) + ' %';
                } else {
                    vm.pastValue = null;
                    vm.metricChangePercent = null;
                }

                vm.isLoading = false;

                // don't change the metric translation until data is fetched to avoid loading state confusion
                vm.metricTranslation = getMetricTranslation();
            });
        }

        function getLastPeriodDate() {
            var RangePeriod = piwikPeriods.get('range');
            var result = RangePeriod.getLastNRange(piwik.period, 2, piwik.currentDateString).startDate;
            return $.datepicker.formatDate('yy-mm-dd', result);
        }

        function setWidgetTitle() {
            $element.closest('div.widget').find('.widgetTop > .widgetName > span').text(vm.getMetricTranslation());
        }

        function getCurrentPeriod() {
            if (piwik.startDateString === piwik.endDateString) {
                return piwik.endDateString;
            }
            return piwik.startDateString + ', ' + piwik.endDateString;
        }

        function createSeriesPicker() {
            var $widgetName = $element.closest('div.widget').find('.widgetTop > .widgetName');

            var $seriesPicker = $('<piwik-series-picker class="single-metric-view-picker" multiselect="false" ' +
                'selectable-columns="$ctrl.selectableColumns" selectable-rows="[]" selected-columns="$ctrl.selectedColumns" ' +
                'selected-rows="[]" on-select="$ctrl.setMetric(columns[0])" />');

            seriesPickerScope = $scope.$new();
            $compile($seriesPicker)(seriesPickerScope);

            $widgetName.append($seriesPicker);
        }

        function destroySeriesPicker() {
            $element.closest('div.widget').find('.single-metric-view-picker').remove();

            seriesPickerScope.$destroy();
            seriesPickerScope = null;
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

            fetchData();

            // notify widget of parameter change so it is replaced
            $element.closest('div.widget').trigger('setParameters', { column: vm.metric });
        }

        function setMetric(newColumn) {
            vm.metric = newColumn;
            onMetricChanged();
        }

        function getPastPeriodStr() {
            var startDate = piwikPeriods.get('range').getLastNRange(piwik.period, 2, piwik.currentDateString).startDate;
            var dateRange = piwikPeriods.get(piwik.period).parse(startDate).getDateRange();
            return $.datepicker.formatDate('yy-mm-dd', dateRange[0]) + ',' + $.datepicker.formatDate('yy-mm-dd', dateRange[1]);
        }
    }
})();
