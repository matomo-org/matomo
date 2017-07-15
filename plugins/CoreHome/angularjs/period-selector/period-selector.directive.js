/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-period-selector>
 */
(function () {
    angular.module('piwikApp').directive('piwikPeriodSelector', piwikPeriodSelector);

    piwikPeriodSelector.$inject = ['piwik', '$location', 'piwikPeriods'];

    function piwikPeriodSelector(piwik, $location, piwikPeriods) {
        return {
            restrict: 'A',
            scope: {
                periods: '<',
                periodTranslations: '<'
            },
            templateUrl: 'plugins/CoreHome/angularjs/period-selector/period-selector.directive.html?cb=' + piwik.cacheBuster,
            controller: 'PeriodSelectorController',
            controllerAs: 'periodSelector',
            bindToController: true,
            link: function (scope, element) {
                // the period & date currently being viewed
                scope.periodSelector.periodValue = null;
                scope.periodSelector.dateValue = null;

                scope.periodSelector.selectedPeriod = null;

                scope.periodSelector.startRangeDate = null;
                scope.periodSelector.endRangeDate = null;

                scope.periodSelector.getCurrentlyViewingText = getCurrentlyViewingText;
                scope.periodSelector.changeViewedPeriod = changeViewedPeriod;
                scope.periodSelector.setPiwikPeriodAndDate = setPiwikPeriodAndDate;
                scope.periodSelector.onApplyClicked = onApplyClicked;

                scope.$on('$locationChangeSuccess', updateSelectedValuesFromHash);

                updateSelectedValuesFromHash();
                initTopControls(); // must be called when a top control changes width

                function updateSelectedValuesFromHash() {
                    var search = $location.search();

                    scope.periodSelector.periodValue = search.period;
                    scope.periodSelector.selectedPeriod = search.period;

                    if (search.period === 'range') {
                        var parts = search.date.split(',');
                        scope.periodSelector.startRangeDate = parts[0];
                        scope.periodSelector.endRangeDate = parts[1];

                        scope.periodSelector.dateValue = parseDate(parts[0]);
                    } else {
                        scope.periodSelector.dateValue = parseDate(search.date);

                        var range = piwikPeriods.parse(search.period, search.date).getDateRange();
                        scope.periodSelector.startRangeDate = $.datepicker.formatDate('yy-mm-dd', range[0]);
                        scope.periodSelector.endRangeDate = $.datepicker.formatDate('yy-mm-dd', range[1]);
                    }
                }

                function getCurrentlyViewingText() {
                    var search = $location.search();
                    return piwikPeriods.parse(search.period, search.date).getPrettyString();
                }

                function changeViewedPeriod(period) {
                    // only change period if it's different from what's being shown currently
                    if (period === scope.periodSelector.periodValue) {
                        return;
                    }

                    // can't just change to a range period, w/o setting two new dates
                    if (period === 'range') {
                        return;
                    }

                    setPiwikPeriodAndDate(period, scope.periodSelector.dateValue);
                }

                function onApplyClicked() {
                    if (scope.periodSelector.selectedPeriod === 'range') {
                        var dateFrom = scope.periodSelector.startRangeDate,
                            dateTo = scope.periodSelector.endRangeDate,
                            oDateFrom = $.datepicker.parseDate('yy-mm-dd', dateFrom),
                            oDateTo = $.datepicker.parseDate('yy-mm-dd', dateTo);

                        if (!isValidDate(oDateFrom)
                            || !isValidDate(oDateTo)
                            || oDateFrom > oDateTo
                        ) {
                            // TODO: use a notification instead?
                            $('#alert').find('h2').text(_pk_translate('General_InvalidDateRange'));
                            piwikHelper.modalConfirm('#alert', {});
                            return;
                        }

                        propagateNewUrlParams(dateFrom + ',' + dateTo, 'range');
                        return;
                    }

                    setPiwikPeriodAndDate(scope.periodSelector.selectedPeriod, scope.periodSelector.dateValue);

                }

                function setPiwikPeriodAndDate(period, date) {
                    period = period || scope.periodSelector.periodValue;
                    date = date || scope.periodSelector.dateValue;

                    piwik.period = period;
                    piwik.currentDateString = $.datepicker.formatDate('yy-mm-dd', date);

                    var dateRange = piwikPeriods.parse(period, piwik.currentDateString).getDateRange();
                    piwik.startDateString = $.datepicker.formatDate('yy-mm-dd', dateRange[0]);
                    piwik.endDateString = $.datepicker.formatDate('yy-mm-dd', dateRange[1]);

                    propagateNewUrlParams(piwik.currentDateString, scope.periodSelector.selectedPeriod);
                    initTopControls();
                }

                function propagateNewUrlParams(date, period) {
                    element.find('#periodString').removeClass('expanded');

                    var $search = $location.search();
                    if (date !== $search.date || period !== $search.period) {
                        // eg when using back button the date might be actually already changed in the URL and we do not
                        // want to change the URL again
                        $search.date = date;
                        $search.period = period;
                        $location.search($search);
                    }
                }

                function parseDate(strDate) {
                    if (strDate === 'today') {
                        return getToday();
                    }

                    if (strDate === 'yesterday') {
                        var yesterday = getToday();
                        yesterday.setDate(yesterday.getDate() - 1);
                        return yesterday;
                    }

                    return $.datepicker.parseDate('yy-mm-dd', strDate);
                }

                function getToday() {
                    var date = new Date();
                    date.setHours(0);
                    date.setMinutes(0);
                    date.setSeconds(0);
                    date.setMilliseconds(0);
                    return date;
                }

                function isValidDate(d) {
                    if (Object.prototype.toString.call(d) !== "[object Date]") {
                        return false;
                    }

                    return !isNaN(d.getTime());
                }
            }
        };
    }
})();