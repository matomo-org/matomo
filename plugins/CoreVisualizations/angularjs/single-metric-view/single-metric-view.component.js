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
            metricDocumentations: '<'
        },
        controller: SingleMetricViewController
    });

    SingleMetricViewController.$inject = ['piwik', 'piwikApi', '$element', '$httpParamSerializer', '$compile', '$scope', 'piwikPeriods'];

    function SingleMetricViewController(piwik, piwikApi, $element, $httpParamSerializer, $compile, $scope, piwikPeriods) {
        var seriesPickerScope;

        var vm = this;
        vm.metricValue = null;
        vm.isLoading = false;
        vm.metricTranslation = null;
        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.$onDestroy = $onDestroy;
        vm.getCurrentPeriod = getCurrentPeriod;
        vm.getMetricTranslation = getMetricTranslation;
        vm.getMetricDocumentation = getMetricDocumentation;
        vm.setMetric = setMetric;

        function $onInit() {
            setSelectableColumns();

            vm.selectedColumns = [vm.metric];
            vm.pastPeriod = getPastPeriodStr();

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
            piwikApi.fetch({
                method: 'API.get',
                filter_last_period_evolution: '1',
                format_metrics: '1'
            }).then(function (response) {
                vm.metricValue = response[vm.metric];
                vm.pastValue = response[vm.metric + '_past'];
                vm.metricChangePercent = response[vm.metric + '_evolution'];

                vm.isLoading = false;

                // don't change the metric translation until data is fetched to avoid loading state confusion
                vm.metricTranslation = getMetricTranslation();
            });
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

            setWidgetTitle();

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
