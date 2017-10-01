/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * A wrapper around the jquery UI date picker that allows you to show multiple dates selected
 * or highlighted.
 *
 * Properties:
 * - selectedDates: null or a two element array of Date instances. The first element is the start
 *                  of the range of selected dates. The second element is the end of the range
 *                  of selected dates. The range is inclusive.
 * - highlightedDates: null or a two element array of Date instances. The first element is the
 *                     start of the range of highlighted dates. The second element is the end of
 *                     the range of highlighted dates. The range is inclusive.
 * - viewDate: The date that should be displayed initially. The month & year of this date is what will
 *             be displayed to the user.
 * - stepMonths: The number of months to move when the left/right arrows are clicked.
 * - disableMonthDropdown: true if the month dropdown should be disabled, false if enabled.
 * - options: extra options to pass the jquery ui's datepicker. They will not be re-applied if
 *            the value for this property changes.
 * - cellHover: called when the user hovers over a calendar cell. Called w/ 'date' argument set
 *              to the Date of the cell clicked & '$cell' set to the <td> element from the datepicker.
 * - cellHoverLeave: called when the user leaves all of the calendar cells.
 * - dateSelect: called when the user selects a date.
 *
 * Usage:
 * <div piwik-date-picker .../>
 */
(function () {
    angular.module('piwikApp').directive('piwikDatePicker', piwikDatePicker);

    piwikDatePicker.$inject = ['piwik', '$timeout'];

    function piwikDatePicker(piwik, $timeout) {
        return {
            restrict: 'A',
            scope: {
                selectedDateStart: '<',
                selectedDateEnd: '<',
                highlightedDateStart: '<',
                highlightedDateEnd: '<',
                viewDate: '<',
                stepMonths: '<',
                disableMonthDropdown: '<',
                options: '<',
                cellHover: '&',
                cellHoverLeave: '&',
                dateSelect: '&'
            },
            template: '',
            link: function (scope, element) {
                scope.$onChanges = $onChanges;

                var customOptions = scope.options || {};
                var datePickerOptions = $.extend({}, piwik.getBaseDatePickerOptions(), customOptions, {
                    onSelect: onDateSelected
                });
                element.datepicker(datePickerOptions);

                element.on('mouseover', 'tbody td a', function (event) {
                    // this event is triggered when a user clicks a date as well. in that case,
                    // the originalEvent is null. we don't need to redraw again for that, so
                    // we ignore events like that.
                    if (event.originalEvent) {
                        setDatePickerCellColors();
                    }
                });

                // on hover cell, execute scope.cellHover()
                element.on('mouseenter', 'tbody td', function () {
                    if (!scope.cellHover) {
                        return;
                    }

                    var monthYear = getMonthYearDisplayed();

                    var $dateCell = $(this);
                    var dateValue = getCellDate($dateCell, monthYear[0], monthYear[1]);
                    scope.cellHover({ date: dateValue, $cell: $dateCell });

                    $timeout(); // trigger new digest
                });

                // call scope.cellHover() when mouse leaves table body (can't do event on tbody, for some reason
                // that fails, so we do two events, one on the table & one on thead)
                element
                    .on('mouseleave', 'table', onCalendarHoverLeave)
                    .on('mouseenter', 'thead', onCalendarHoverLeave);

                // make sure whitespace is clickable when the period makes it appropriate
                element.on('click', 'tbody td.ui-datepicker-other-month', handleOtherMonthClick);

                // NOTE: using a selector w/ .on() doesn't seem to work for some reason...
                element.on('click', function (e) {
                    e.preventDefault();

                    var $target = $(e.target).closest('a');
                    if (!$target.is('.ui-datepicker-next')
                        && !$target.is('.ui-datepicker-prev')
                    ) {
                        return;
                    }

                    onCalendarViewChange();
                });

                init();

                function init() {
                    viewDateChanged();
                    stepMonthsChanged();
                    enableDisableMonthDropdown();
                    setDatePickerCellColors();
                }

                function $onChanges(changesObj) {
                    // redraw when selected/highlighted dates change
                    var redraw = changesObj.selectedDateStart
                        || changesObj.selectedDateEnd
                        || changesObj.highlightedDateStart
                        || changesObj.highlightedDateEnd;

                    if (changesObj.viewDate) {
                        // I can't figure out why, but $onChanges is triggered with viewDate
                        // three times when selecting a date (even though period-date-picker
                        // only changes once). for two of those changes,
                        // selectedDateStart/highlightedDateStart is undefined. not sure what's
                        // causing it, but it seems internal to angular.
                        //
                        // note: the change values for viewDate for those two changes are the
                        // start/end of the new selected date range.
                        if (scope.selectedDateStart
                            || scope.highlightedDateStart
                        ) {
                            redraw = true;
                        }

                        viewDateChanged();
                    }

                    if (changesObj.stepMonths) {
                        stepMonthsChanged();
                    }

                    if (changesObj.enableDisableMonthDropdown) {
                        enableDisableMonthDropdown();
                    }

                    if (redraw) {
                        // timeout ensures the style change happens after jquery datepicker
                        // does its own rendering, so we're overriding jquery.
                        setTimeout(function () {
                            setDatePickerCellColors();
                        }, 0);
                    }
                }

                function viewDateChanged() {
                    var date = scope.viewDate;
                    if (!(date instanceof Date)) {
                        try {
                            date = $.datepicker.parseDate('yy-mm-dd', date);
                        } catch (e) {
                            return;
                        }
                    }

                    element.datepicker('setDate', date);
                }

                function stepMonthsChanged() {
                    // setting stepMonths will change the month in view back to the selected date. to avoid
                    // we set the selected date to the month in view.
                    var currentMonth = $('.ui-datepicker-month', element).val(),
                        currentYear = $('.ui-datepicker-year', element).val();
                    scope.viewDate = new Date(currentYear, currentMonth);

                    element.datepicker('option', 'stepMonths', scope.stepMonths);
                }

                function onDateSelected(dateStr) {
                    if (!scope.dateSelect) {
                        return;
                    }

                    scope.dateSelect({
                        date: $.datepicker.parseDate('yy-mm-dd', dateStr)
                    });

                    // this event comes from jquery, so we must apply manually
                    scope.$apply();
                }

                function handleOtherMonthClick() {
                    if (!$(this).hasClass('ui-state-hover')) {
                        return;
                    }

                    var $row = $(this).parent(),
                        $tbody = $row.parent();

                    if ($row.is(':first-child')) {
                        // click on first of the month
                        $tbody.find('a').first().click();
                    } else {
                        // click on last of month
                        $tbody.find('a').last().click();
                    }
                }

                function onCalendarHoverLeave() {
                    if (!scope.cellHoverLeave) {
                        return;
                    }

                    scope.cellHoverLeave();

                    $timeout();
                }

                function onCalendarViewChange() {
                    // clicking left/right re-enables the month dropdown, so we disable it again
                    enableDisableMonthDropdown();

                    setDatePickerCellColors();
                }

                function setDatePickerCellColors() {
                    var $calendarTable = element.find('.ui-datepicker-calendar');

                    // unhighlight datepicker's "current day"
                    $calendarTable.find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day');

                    var monthYear = getMonthYearDisplayed();

                    // highlight the rest of the cells by first getting the date for the first cell
                    // in the calendar, then just incrementing by one for the rest of the cells.
                    var $cells = $calendarTable.find('td');
                    var $firstDateCell = $cells.first();
                    var currentDate = getCellDate($firstDateCell, monthYear[0], monthYear[1]);

                    $cells.each(function () {
                        setDateCellColor($(this), currentDate);

                        currentDate.setDate(currentDate.getDate() + 1);
                    });
                }

                function getMonthYearDisplayed() {
                    var $firstCellWithMonth = element.find('td[data-month]');
                    var month = parseInt($firstCellWithMonth.attr('data-month'));
                    var year = parseInt($firstCellWithMonth.attr('data-year'));
                    return [month, year];
                }

                function setDateCellColor($dateCell, dateValue) {
                    var $dateCellLink = $dateCell.children('a');
                    $dateCellLink.removeClass('ui-state-active');

                    if (scope.selectedDateStart
                        && scope.selectedDateEnd
                        && dateValue >= scope.selectedDateStart
                        && dateValue <= scope.selectedDateEnd
                    ) {
                        $dateCell.addClass('ui-datepicker-current-period');
                    } else {
                        $dateCell.removeClass('ui-datepicker-current-period');
                    }

                    if (scope.highlightedDateStart
                        && scope.highlightedDateEnd
                        && dateValue >= scope.highlightedDateStart
                        && dateValue <= scope.highlightedDateEnd
                    ) {
                        // other-month cells don't have links, so the <td> must have the ui-state-hover class
                        var elementToAddClassTo = $dateCellLink.length ? $dateCellLink : $dateCell;
                        elementToAddClassTo.addClass('ui-state-hover');
                    } else {
                        $dateCell.removeClass('ui-state-hover');
                        $dateCellLink.removeClass('ui-state-hover');
                    }
                }

                function getCellDate($dateCell, month, year) {
                    if ($dateCell.hasClass('ui-datepicker-other-month')) {
                        return getOtherMonthDate($dateCell, month, year);
                    }

                    var day = parseInt($dateCell.children('a,span').text());

                    return new Date(year, month, day);
                }

                function getOtherMonthDate($dateCell, month, year) {
                    var date;

                    var $row = $dateCell.parent();
                    var $rowCells = $row.children('td');

                    // if in the first row, the date cell is before the current month
                    if ($row.is(':first-child')) {
                        var $firstDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').first();

                        date = getCellDate($firstDateInMonth, month, year);
                        date.setDate($rowCells.index($dateCell) - $rowCells.index($firstDateInMonth) + 1);
                        return date;
                    }

                    // the date cell is after the current month
                    var $lastDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').last();

                    date = getCellDate($lastDateInMonth, month, year);
                    date.setDate(date.getDate() + $rowCells.index($dateCell) - $rowCells.index($lastDateInMonth));
                    return date;
                }

                function enableDisableMonthDropdown() {
                    element.find('.ui-datepicker-month').attr('disabled', scope.disableMonthDropdown);
                }
            }
        };
    }
})();