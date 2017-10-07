/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Wraps a date picker to provide more intuitive date picking for periods.
 *
 * Supports all Piwik periods that have one date (so not range periods). When a user
 * hovers over a date, the entire period for the date is highlighted. The selected
 * period is colored similarly.
 *
 * Properties:
 * - period: The label of the period. 'week', 'day', etc.
 * - date: A date inside the period. Must be a Date object.
 * - select: called when a date is selected in the picker.
 *
 * Usage:
 * <piwik-period-date-picker>
 */
(function () {
    angular.module('piwikApp').component('piwikPeriodDatePicker', {
        templateUrl: 'plugins/CoreHome/angularjs/period-date-picker/period-date-picker.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            period: '<',
            date: '<',
            select: '&'
        },
        controller: PeriodDatePickerController
    });

    PeriodDatePickerController.$inject = ['piwikPeriods', 'piwik'];

    function PeriodDatePickerController(piwikPeriods, piwik) {
        var piwikMinDate = new Date(piwik.minDateYear, piwik.minDateMonth - 1, piwik.minDateDay),
            piwikMaxDate = new Date(piwik.maxDateYear, piwik.maxDateMonth - 1, piwik.maxDateDay);

        var vm = this;
        vm.selectedDates = [null, null];
        vm.highlightedDates = [null, null];
        vm.viewDate = null;
        vm.onHoverNormalCell = onHoverNormalCell;
        vm.onHoverLeaveNormalCells = onHoverLeaveNormalCells;
        vm.onDateSelected = onDateSelected;
        vm.$onChanges = $onChanges;
        vm.$onInit = $onInit;

        function onHoverNormalCell(cellDate, $cell) {
            var isOutOfMinMaxDateRange = cellDate < piwikMinDate || cellDate > piwikMaxDate;

            // don't highlight anything if the period is month or day, and we're hovering over calendar whitespace.
            // since there are no dates, it's doesn't make sense what you're selecting.
            var shouldNotHighlightFromWhitespace = $cell.hasClass('ui-datepicker-other-month') && (vm.period === 'month'
                || vm.period === 'day');

            if (isOutOfMinMaxDateRange
                || shouldNotHighlightFromWhitespace
            ) {
                vm.highlightedDates = [null, null];
                return;
            }

            vm.highlightedDates = getBoundedDateRange(cellDate);
        }

        function onHoverLeaveNormalCells() {
            vm.highlightedDates = [null, null];
        }

        function $onInit() {
            // vm.date is only guaranteed to be set here
            vm.viewDate = vm.date;
        }

        function $onChanges() {
            if (!vm.period || !vm.date) {
                vm.selectedDates = [null, null];
                return;
            }

            vm.selectedDates = getBoundedDateRange(vm.date);
        }

        function onDateSelected(date) {
            if (!vm.select) {
                return;
            }

            vm.select({ date: date });
        }

        function getBoundedDateRange(date) {
            var periodClass = piwikPeriods.get(vm.period);
            var dates = (new periodClass(date)).getDateRange();

            // make sure highlighted date range is within min/max date range
            dates[0] = piwikMinDate < dates[0] ? dates[0] : piwikMinDate;
            dates[1] = piwikMaxDate > dates[1] ? dates[1] : piwikMaxDate;

            return dates;
        }
    }
})();
