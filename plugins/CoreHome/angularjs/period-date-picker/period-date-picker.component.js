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

    PeriodDatePickerController.$inject = ['piwikPeriods'];

    function PeriodDatePickerController(piwikPeriods) {
        var vm = this;

        vm.selectedDates = null;
        vm.highlightedDates = null;
        vm.onHoverNormalCell = onHoverNormalCell;
        vm.onHoverLeaveNormalCells = onHoverLeaveNormalCells;
        vm.onDateSelected = onDateSelected;
        vm.$onChanges = $onChanges;

        function onHoverNormalCell(cellDate, $cell) {
            // don't highlight anything if the period is month, and we're hovering over calendar whitespace.
            // since there are no dates, it's doesn't make sense what you're selecting.
            if ($cell.hasClass('ui-datepicker-other-month') && vm.period === 'month') {
                vm.highlightedDates = null;
                return;
            }

            var periodClass = piwikPeriods.get(vm.period);
            vm.highlightedDates = (new periodClass(cellDate)).getDateRange();
        }

        function onHoverLeaveNormalCells() {
            vm.highlightedDates = null;
        }

        function $onChanges() {
            if (!vm.period || !vm.date) {
                vm.selectedDates = null;
                return;
            }

            var periodClass = piwikPeriods.get(vm.period);
            vm.selectedDates = (new periodClass(vm.date)).getDateRange();
        }

        function onDateSelected(date) {
            if (!vm.select) {
                return;
            }

            vm.select({ date: date });
        }
    }
})();
