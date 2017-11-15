/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Combines two jquery UI datepickers to provide a date range picker (that picks inclusive
 * ranges).
 *
 * Properties:
 * - startDate: The start of the date range. Should be a string in the YYYY-MM-DD format.
 * - endDate: The end of the date range. Should be a string in the YYYY-MM-DD format. Note:
 *            date ranges are inclusive.
 * - rangeChange: Called when one or both dates bounding the range change. If the dates are
 *                in an invalid state, the date will be null in this event.
 * - submit: Called if the 'enter' key is pressed in either of the inputs.
 *
 * Usage:
 * <piwik-date-range-picker>
 */
(function () {
    angular.module('piwikApp').component('piwikDateRangePicker', {
        templateUrl: 'plugins/CoreHome/angularjs/date-range-picker/date-range-picker.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            startDate: '<',
            endDate: '<',
            rangeChange: '&',
            submit: '&'
        },
        controller: DateRangePickerController
    });

    DateRangePickerController.$inject = [];

    function DateRangePickerController() {
        var vm = this;

        vm.fromPickerSelectedDates = null;
        vm.toPickerSelectedDates = null;
        vm.fromPickerHighlightedDates = null;
        vm.toPickerHighlightedDates = null;
        vm.startDateInvalid = false;
        vm.endDateInvalid = false;

        vm.$onChanges = $onChanges;
        vm.setStartRangeDate = setStartRangeDate;
        vm.setEndRangeDate = setEndRangeDate;
        vm.onRangeInputChanged = onRangeInputChanged;
        vm.getNewHighlightedDates = getNewHighlightedDates;
        vm.handleEnterPress = handleEnterPress;

        function $onChanges(changes) {
            if (changes.startDate) {
                setStartRangeDateFromStr(vm.startDate);
            }

            if (changes.endDate) {
                setEndRangeDateFromStr(vm.endDate);
            }
        }

        function onRangeInputChanged(source) {
            if (source === 'from') {
                setStartRangeDateFromStr(vm.startDate);
            } else {
                setEndRangeDateFromStr(vm.endDate);
            }
        }

        function setStartRangeDateFromStr(dateStr) {
            vm.startDateInvalid = true;

            var startDateParsed;
            try {
                startDateParsed = $.datepicker.parseDate('yy-mm-dd', dateStr);
            } catch (e) {
                // ignore
            }

            if (startDateParsed) {
                vm.fromPickerSelectedDates = [startDateParsed, startDateParsed];
                vm.startDateInvalid = false;
            }

            rangeChanged();
        }

        function setEndRangeDateFromStr(dateStr) {
            vm.endDateInvalid = true;

            var endDateParsed;
            try {
                endDateParsed = $.datepicker.parseDate('yy-mm-dd', dateStr);
            } catch (e) {
                // ignore
            }

            if (endDateParsed) {
                vm.toPickerSelectedDates = [endDateParsed, endDateParsed];
                vm.endDateInvalid = false;
            }

            rangeChanged();
        }

        function handleEnterPress($event) {
            if ($event.keyCode !== 13 || !vm.submit) {
                return;
            }

            vm.submit({
                start: vm.startDate,
                end: vm.endDate
            });
        }

        function setStartRangeDate(date) {
            vm.startDateInvalid = false;
            vm.startDate = $.datepicker.formatDate('yy-mm-dd', date);

            vm.fromPickerSelectedDates = [date, date];

            rangeChanged();
        }

        function setEndRangeDate(date) {
            vm.endDateInvalid = false;
            vm.endDate = $.datepicker.formatDate('yy-mm-dd', date);

            vm.toPickerSelectedDates = [date, date];

            rangeChanged();
        }

        function rangeChanged() {
            if (!vm.rangeChange) {
                return;
            }

            vm.rangeChange({
                start: vm.startDateInvalid ? null : vm.startDate,
                end: vm.endDateInvalid ? null : vm.endDate
            });
        }

        function getNewHighlightedDates(date, $cell) {
            if ($cell.hasClass('ui-datepicker-unselectable')) {
                return null;
            }

            return [date, date];
        }
    }
})();
