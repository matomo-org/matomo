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
                selectedDates: '<',
                highlightedDates: '<',
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
                var customOptions = scope.options || {};
                var datePickerOptions = $.extend({}, piwik.getBaseDatePickerOptions(), customOptions, {
                    onSelect: onDateSelected
                });
                element.datepicker(datePickerOptions);

                // redraw when selected/highlighted dates change
                scope.$watchGroup(
                    [
                        dateForWatchGetter('selectedDates', 0),
                        dateForWatchGetter('selectedDates', 1),
                        dateForWatchGetter('highlightedDates', 0),
                        dateForWatchGetter('highlightedDates', 1)
                    ],
                    setDatePickerCellColors
                );

                scope.$watch('viewDate', function () {
                    var date = scope.viewDate;
                    if (!(date instanceof Date)) {
                        try {
                            date = $.datepicker.parseDate('yy-mm-dd', date);
                        } catch (e) {
                            return;
                        }
                    }

                    element.datepicker('setDate', date);

                    setDatePickerCellColors();
                });

                scope.$watch('stepMonths', function () {
                    // setting stepMonths will change the month in view back to the selected date. to avoid
                    // we set the selected date to the month in view.
                    var currentMonth = $('.ui-datepicker-month', element).val(),
                        currentYear = $('.ui-datepicker-year', element).val();
                    scope.viewDate = new Date(currentYear, currentMonth);

                    element.datepicker('option', 'stepMonths', scope.stepMonths);
                });

                scope.$watch('disableMonthDropdown', enableDisableMonthDropdown);

                element.on('mouseover', 'tbody td a', setDatePickerCellColors);

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

                function onDateSelected(dateStr) {
                    if (!scope.dateSelect) {
                        return;
                    }

                    scope.dateSelect({
                        date: $.datepicker.parseDate('yy-mm-dd', dateStr)
                    });
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

                    $timeout(); // trigger new digest
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

                    var $firstDateCell = $calendarTable.find('td').first();
                    var currentDate = getCellDate($firstDateCell, monthYear[0], monthYear[1]);

                    $calendarTable.find('td').each(function () {
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

                    if (scope.selectedDates
                        && dateValue >= scope.selectedDates[0]
                        && dateValue <= scope.selectedDates[1]
                    ) {
                        $dateCell.addClass('ui-datepicker-current-period');
                    } else {
                        $dateCell.removeClass('ui-datepicker-current-period');
                    }

                    if (scope.highlightedDates
                        && dateValue >= scope.highlightedDates[0]
                        && dateValue <= scope.highlightedDates[1]
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

                // watch on date's actual time, so the watch only executes when the actual time values change
                function dateForWatchGetter(propertyName, index) {
                    return function () {
                        return (scope[propertyName] && scope[propertyName][index]) ? scope[propertyName][index].getTime() : null;
                    };
                }

                function enableDisableMonthDropdown() {
                    element.find('.ui-datepicker-month').attr('disabled', scope.disableMonthDropdown);
                }
            }
        };
    }
})();