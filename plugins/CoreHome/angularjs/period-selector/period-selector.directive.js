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

    piwikPeriodSelector.$inject = ['piwik', '$location'];

    function piwikPeriodSelector(piwik, $location) {
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
                scope.periodSelector.periodValue = null;
                scope.periodSelector.dateValue = null;

                scope.periodSelector.selectedPeriod = null;
                scope.periodSelector.selectedDate = null;

                scope.periodSelector.selectedDates = null;
                scope.periodSelector.highlightedDates = null;

                scope.periodSelector.startRangeDate = null;
                scope.periodSelector.endRangeDate = null;

                scope.periodSelector.getPrettyDate = getPrettyDate;
                scope.periodSelector.onHoverNormalCell = onHoverNormalCell;
                scope.periodSelector.onHoverLeaveNormalCells = onHoverLeaveNormalCells;
                scope.periodSelector.getMonthStepCount = getMonthStepCount;
                scope.periodSelector.onSelectPeriod = onSelectPeriod;
                scope.periodSelector.changeViewedPeriod = changeViewedPeriod;
                scope.periodSelector.setPiwikPeriodAndDate = setPiwikPeriodAndDate;
                scope.periodSelector.onApplyClicked = onApplyClicked;

                scope.$on('$locationChangeSuccess', updateSelectedValuesFromHash);

                updateSelectedValuesFromHash();
                initTopControls(); // must be called when a top control changes width

                function updateSelectedValuesFromHash() {
                    var search = $location.search();

                    scope.periodSelector.periodValue = search.period;
                    scope.periodSelector.dateValue = search.date.indexOf(',') === -1 ? parseDate(search.date) : parseDate(search.date.split(',')[0]);

                    scope.periodSelector.selectedPeriod = scope.periodSelector.periodValue;
                    scope.periodSelector.selectedDate = scope.periodSelector.dateValue;

                    scope.periodSelector.startRangeDate = piwik.startDateString;
                    scope.periodSelector.endRangeDate = piwik.endDateString;

                    setSelectedDateRange();
                }

                function getPrettyDate() {
                    var period = scope.periodSelector.periodValue;
                    var date = scope.periodSelector.dateValue;

                    if (period === 'month') {
                        return _pk_translate('Intl_Month_Long_StandAlone_' + (date.getMonth() + 1)) + ' ' + date.getFullYear();
                    } else if (period === 'year') {
                        return date.getFullYear();
                    } else if (period === 'week') {
                        var weekDates = getDateRangeForPeriod(period, date);
                        var startWeek = $.datepicker.formatDate('yy-mm-dd', weekDates[0]);
                        var endWeek = $.datepicker.formatDate('yy-mm-dd', weekDates[1]);

                        return _pk_translate('General_DateRangeFromTo', [startWeek, endWeek]);
                    } else if (period === 'range') {
                        return _pk_translate('General_DateRangeFromTo', [piwik.startDateString, piwik.endDateString]);
                    } else {
                        return $.datepicker.formatDate('yy-mm-dd', date);
                    }
                }

                function onHoverNormalCell(cellDate, $cell) {
                    // don't highlight anything if the period is month, and we're hovering over calendar whitespace.
                    // since there are no dates, it's doesn't make sense what you're selecting.
                    if ($cell.hasClass('ui-datepicker-other-month') && scope.periodSelector.selectedPeriod === 'month') {
                        scope.periodSelector.highlightedDates = null;
                        return;
                    }

                    scope.periodSelector.highlightedDates = getDateRangeForPeriod(scope.periodSelector.selectedPeriod, cellDate);
                }

                function onHoverLeaveNormalCells() {
                    scope.periodSelector.highlightedDates = null;
                }

                function onSelectPeriod(period) {
                    scope.periodSelector.selectedPeriod = period;
                    setSelectedDateRange();
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

                    var dateRange = getDateRangeForPeriod(period, date);

                    piwik.currentDateString = $.datepicker.formatDate('yy-mm-dd', date); // TODO: abstract these parse/formatdate calls
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

                function setSelectedDateRange() {
                    if (scope.periodSelector.periodValue !== scope.periodSelector.selectedPeriod) {
                        scope.periodSelector.selectedDates = null;
                        return;
                    }

                    scope.periodSelector.selectedDates = getDateRangeForPeriod(scope.periodSelector.periodValue,
                        scope.periodSelector.dateValue);
                }

                function getMonthStepCount() {
                    return scope.periodSelector.selectedPeriod === 'year' ? 12 : 1;
                }

                function getDateRangeForPeriod(period, date) {
                    switch (period) {
                        case 'day':
                            return [date, date];
                        case 'week':
                            var daysToMonday = (date.getDay() + 6) % 7;

                            var startWeek = new Date(date.getTime());
                            startWeek.setDate(date.getDate() - daysToMonday);

                            var endWeek = new Date(startWeek.getTime());
                            endWeek.setDate(startWeek.getDate() + 6);

                            return [startWeek, endWeek];
                        case 'month':
                            var startMonth = new Date(date.getTime());
                            startMonth.setDate(1);

                            var endMonth = new Date(date.getTime());
                            endMonth.setMonth(endMonth.getMonth() + 1);
                            endMonth.setDate(0);

                            return [startMonth, endMonth];
                        case 'year':
                            var startYear = new Date(date.getTime());
                            startYear.setMonth(0);
                            startYear.setDate(1);

                            var endYear = new Date(date.getTime());
                            endYear.setMonth(12);
                            endYear.setDate(0);

                            return [startYear, endYear];
                        default:
                            return [];
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