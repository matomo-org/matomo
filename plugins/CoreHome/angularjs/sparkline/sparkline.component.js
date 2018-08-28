/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Displays a sparkline. 'module', 'action' and 'date' are required elements of the
 * params attribute.
 *
 * Usage:
 * <piwik-sparkline params="{'module': 'API', 'action': 'get', 'date': '...'}"></piwik-sparkline>
 */
(function () {
    angular.module('piwikApp').component('piwikSparkline', {
        template: '<img />',
        bindings: {
            params: '<'
        },
        controller: SparklineController
    });

    SparklineController.$inject = ['$element', '$httpParamSerializer', 'piwikApi', 'piwik', 'piwikPeriods'];

    function SparklineController($element, $httpParamSerializer, piwikApi, piwik, piwikPeriods) {
        var vm = this;
        vm.$onChanges = $onChanges;

        function $onChanges() {
            // done manually due to 'random' query param. since it changes the URL on each digest, depending on angular
            // results in an infinite digest
            $element.find('img').attr('src', getSparklineUrl());
        }

        function getSparklineUrl() {
            var defaultParams = {
                forceView: '1',
                viewDataTable: 'sparkline',
                widget: $element.closest('[widgetId]').length ? '1' : '0',
                showtitle: '1',
                colors: JSON.stringify(piwik.getSparklineColors()),
                random: Date.now(),
                date: getDefaultDate()
            };

            var urlParams = piwikApi.mixinDefaultGetParams($element.extend(defaultParams, vm.params));
            return '?' + $httpParamSerializer(urlParams);
        }

        function getDefaultDate() {
            if (piwik.period === 'range') {
                return piwik.startDateString + ',' + piwik.endDateString;
            }

            var dateRange = piwikPeriods.get('range').getLastNRange(piwik.period, 30, piwik.currentDateString).getDateRange();

            var piwikMinDate = new Date(piwik.minDateYear, piwik.minDateMonth - 1, piwik.minDateDay);
            if (dateRange[0] < piwikMinDate) {
                dateRange[0] = piwikMinDate;
            }

            var startDateStr = $.datepicker.formatDate('yy-mm-dd', dateRange[0]);
            var endDateStr = $.datepicker.formatDate('yy-mm-dd', dateRange[1]);
            return startDateStr + ',' + endDateStr;
        }
    }
})();
