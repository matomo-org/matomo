/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
            idGoal: '<',
            metricTranslations: '<',
            metricDocumentations: '<',
            goals: '<',
            goalMetrics: '<'
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
        vm.metricDocumentation = null;
        vm.selectableColumns = [];
        vm.responses = null;
        vm.sparklineParams = {};
        vm.$onInit = $onInit;
        vm.$onChanges = $onChanges;
        vm.$onDestroy = $onDestroy;
        vm.getCurrentPeriod = getCurrentPeriod;
        vm.getMetricTranslation = getMetricTranslation;
        vm.setMetric = setMetric;

        function setSparklineParams() {
            var params = { module: 'API', action: 'get', columns: vm.metric };
            if (isIdGoalSet()) {
                params.idGoal = vm.idGoal;
                params.module = 'Goals';
            }
            vm.sparklineParams = params;
        }

        function $onInit() {
            vm.selectedColumns = [vm.metric];
            if (piwik.period !== 'range') {
                vm.pastPeriod = getPastPeriodStr();
            }

            setSelectableColumns();

            createSeriesPicker();

            $element.closest('.widgetContent')
                .on('widget:destroy', function() { $scope.$parent.$destroy(); })
                .on('widget:reload', function() { $scope.$parent.$destroy(); });

            setSparklineParams();
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

            var apiModule = 'API';
            var apiAction = 'get';

            var extraParams = {};
            if (isIdGoalSet()) {
                extraParams.idGoal = vm.idGoal;
                // the conversion rate added by the AddColumnsProcessedMetrics filter conflicts w/ the goals one, so don't run it
                extraParams.filter_add_columns_when_show_all_columns = 0;

                apiModule = 'Goals';
                apiAction = 'get';
            }

            // first request for formatted data
            promises.push(piwikApi.fetch($.extend({
                method: apiModule + '.' + apiAction,
                format_metrics: 'all'
            }, extraParams)));

            if (piwik.period !== 'range') {
                // second request for unformatted data so we can calculate evolution
                promises.push(piwikApi.fetch($.extend({
                    method: apiModule + '.' + apiAction,
                    format_metrics: '0'
                }, extraParams)));

                // third request for past data (unformatted)
                promises.push(piwikApi.fetch($.extend({
                    method: apiModule + '.' + apiAction,
                    date: getLastPeriodDate(),
                    format_metrics: '0',
                }, extraParams)));

                // fourth request for past data (formatted for tooltip display)
                promises.push(piwikApi.fetch($.extend({
                    method: apiModule + '.' + apiAction,
                    date: getLastPeriodDate(),
                    format_metrics: 'all',
                }, extraParams)));
            }

            return $q.all(promises).then(function (responses) {
                vm.responses = responses;
                vm.isLoading = false;
            });
        }

        function recalculateValues() {
            // update display based on processed report metadata
            setWidgetTitle();
            vm.metricDocumentation = getMetricDocumentation();

            // update data
            var currentData = vm.responses[0];
            vm.metricValue = currentData[vm.metric] || 0;

            if (vm.responses[1]) {
                vm.metricValueUnformatted = vm.responses[1][vm.metric];

                var pastData = vm.responses[2];
                vm.pastValueUnformatted = pastData[vm.metric] || 0;

                var evolution = piwik.helper.calculateEvolution(vm.metricValueUnformatted, vm.pastValueUnformatted);
                vm.metricChangePercent = (evolution * 100).toFixed(2) + ' %';

                var pastDataFormatted = vm.responses[3];
                vm.pastValue = pastDataFormatted[vm.metric] || 0;
            } else {
                vm.pastValue = null;
                vm.metricChangePercent = null;
            }

            // don't change the metric translation until data is fetched to avoid loading state confusion
            vm.metricTranslation = getMetricTranslation();
        }

        function getLastPeriodDate() {
            var RangePeriod = piwikPeriods.get('range');
            var result = RangePeriod.getLastNRange(piwik.period, 2, piwik.currentDateString).startDate;
            return piwikPeriods.format(result);
        }

        function setWidgetTitle() {
            var title = vm.getMetricTranslation();
            if (isIdGoalSet()) {
                var goalName = vm.goals[vm.idGoal].name;
                title = goalName + ' - ' + title;
            }

            $element.closest('div.widget').find('.widgetTop > .widgetName > span').text(title);
        }

        function getCurrentPeriod() {
            if (piwik.startDateString === piwik.endDateString) {
                return piwik.endDateString;
            }
            return piwik.startDateString + ', ' + piwik.endDateString;
        }

        function createSeriesPicker() {
            vm.selectedColumns = [vm.idGoal ? ('goal' + vm.idGoal + '_' + vm.metric) : vm.metric];

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

            Object.keys(vm.goals).forEach(function (idgoal) {
                var goal = vm.goals[idgoal];
                vm.goalMetrics.forEach(function (column) {
                    result.push({
                        column: 'goal' + goal.idgoal + '_' + column,
                        translation: goal.name + ' - ' + vm.metricTranslations[column]
                    });
                });
            });

            vm.selectableColumns = result;
        }

        function onMetricChanged() {
            setSparklineParams();

            fetchData().then(recalculateValues);

            // notify widget of parameter change so it is replaced
            $element.closest('[widgetId]').trigger('setParameters', { column: vm.metric, idGoal: vm.idGoal });
        }

        function setMetric(newColumn) {
            var idGoal;

            var m = newColumn.match(/^goal([0-9]+)_(.*)/);
            if (m) {
                idGoal = +m[1];
                newColumn = m[2];
            }

            if (vm.metric !== newColumn || idGoal !== vm.idGoal) {
                vm.metric = newColumn;
                vm.idGoal = idGoal;
                onMetricChanged();
            }
        }

        function getPastPeriodStr() {
            var startDate = piwikPeriods.get('range').getLastNRange(piwik.period, 2, piwik.currentDateString).startDate;
            var dateRange = piwikPeriods.get(piwik.period).parse(startDate).getDateRange();
            return piwikPeriods.format(dateRange[0]) + ',' + piwikPeriods.format(dateRange[1]);
        }

        function isIdGoalSet() {
            return vm.idGoal || vm.idGoal === 0;
        }
    }
})();
