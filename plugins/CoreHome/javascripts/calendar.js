/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function ($) {

    Date.prototype.getWeek = function () {
        var onejan = new Date(this.getFullYear(), 0, 1), // needed for getDay()

        // use UTC times since getTime() can differ based on user's timezone
            onejan_utc = Date.UTC(this.getFullYear(), 0, 1),
            this_utc = Date.UTC(this.getFullYear(), this.getMonth(), this.getDate()),

            daysSinceYearStart = (this_utc - onejan_utc) / 86400000; // constant is millisecs in one day

        return Math.ceil((daysSinceYearStart + onejan.getDay()) / 7);
    };

    var currentYear, currentMonth, currentDay, currentDate, currentWeek;

    function setCurrentDate(dateStr) {
        var splitDate = dateStr.split("-");
        currentYear = splitDate[0];
        currentMonth = splitDate[1] - 1;
        currentDay = splitDate[2];
        currentDate = new Date(currentYear, currentMonth, currentDay);
        currentWeek = currentDate.getWeek();
    }

    if(!piwik.currentDateString) {
        // eg. Login form
        return;
    }
    setCurrentDate(piwik.currentDateString);

    var todayDate = new Date;
    var todayMonth = todayDate.getMonth();
    var todayYear = todayDate.getFullYear();
    var todayDay = todayDate.getDate();

// min/max date for picker
    var piwikMinDate = new Date(piwik.minDateYear, piwik.minDateMonth - 1, piwik.minDateDay),
        piwikMaxDate = new Date(piwik.maxDateYear, piwik.maxDateMonth - 1, piwik.maxDateDay);

// we start w/ the current period
    var selectedPeriod = piwik.period;

    function isDateInCurrentPeriod(date) {
        // if the selected period isn't the current period, don't highlight any dates
        if (selectedPeriod != piwik.period) {
            return [true, ''];
        }

        var valid = false;

        var dateMonth = date.getMonth();
        var dateYear = date.getFullYear();
        var dateDay = date.getDate();

        // we don't color dates in the future
        if (dateMonth == todayMonth
            && dateYear == todayYear
            && dateDay > todayDay
            ) {
            return [true, ''];
        }

        // we don't color dates before the minimum date
        if (dateYear < piwik.minDateYear
            || ( dateYear == piwik.minDateYear
            &&
            (
                (dateMonth == piwik.minDateMonth - 1
                    && dateDay < piwik.minDateDay)
                    || (dateMonth < piwik.minDateMonth - 1)
                )
            )
            ) {
            return [true, ''];
        }

        // we color all day of the month for the same year for the month period
        if (piwik.period == "month"
            && dateMonth == currentMonth
            && dateYear == currentYear
            ) {
            valid = true;
        }
        // we color all day of the year for the year period
        else if (piwik.period == "year"
            && dateYear == currentYear
            ) {
            valid = true;
        }
        else if (piwik.period == "week"
            && date.getWeek() == currentWeek
            && dateYear == currentYear
            ) {
            valid = true;
        }
        else if (piwik.period == "day"
            && dateDay == currentDay
            && dateMonth == currentMonth
            && dateYear == currentYear
            ) {
            valid = true;
        }

        if (valid) {
            return [true, 'ui-datepicker-current-period'];
        }

        return [true, ''];
    }

    piwik.getBaseDatePickerOptions = function (defaultDate) {
        return {
            showOtherMonths: false,
            dateFormat: 'yy-mm-dd',
            firstDay: 1,
            minDate: piwikMinDate,
            maxDate: piwikMaxDate,
            prevText: "",
            nextText: "",
            currentText: "",
            defaultDate: defaultDate,
            changeMonth: true,
            changeYear: true,
            stepMonths: 1,
            // jquery-ui-i18n 1.7.2 lacks some translations, so we use our own
            dayNamesMin: [
                _pk_translate('General_DaySu'),
                _pk_translate('General_DayMo'),
                _pk_translate('General_DayTu'),
                _pk_translate('General_DayWe'),
                _pk_translate('General_DayTh'),
                _pk_translate('General_DayFr'),
                _pk_translate('General_DaySa')],
            dayNamesShort: [
                _pk_translate('General_ShortDay_7'), // start with sunday
                _pk_translate('General_ShortDay_1'),
                _pk_translate('General_ShortDay_2'),
                _pk_translate('General_ShortDay_3'),
                _pk_translate('General_ShortDay_4'),
                _pk_translate('General_ShortDay_5'),
                _pk_translate('General_ShortDay_6')],
            dayNames: [
                _pk_translate('General_LongDay_7'), // start with sunday
                _pk_translate('General_LongDay_1'),
                _pk_translate('General_LongDay_2'),
                _pk_translate('General_LongDay_3'),
                _pk_translate('General_LongDay_4'),
                _pk_translate('General_LongDay_5'),
                _pk_translate('General_LongDay_6')],
            monthNamesShort: [
                _pk_translate('General_ShortMonth_1'),
                _pk_translate('General_ShortMonth_2'),
                _pk_translate('General_ShortMonth_3'),
                _pk_translate('General_ShortMonth_4'),
                _pk_translate('General_ShortMonth_5'),
                _pk_translate('General_ShortMonth_6'),
                _pk_translate('General_ShortMonth_7'),
                _pk_translate('General_ShortMonth_8'),
                _pk_translate('General_ShortMonth_9'),
                _pk_translate('General_ShortMonth_10'),
                _pk_translate('General_ShortMonth_11'),
                _pk_translate('General_ShortMonth_12')],
            monthNames: [
                _pk_translate('General_LongMonth_1'),
                _pk_translate('General_LongMonth_2'),
                _pk_translate('General_LongMonth_3'),
                _pk_translate('General_LongMonth_4'),
                _pk_translate('General_LongMonth_5'),
                _pk_translate('General_LongMonth_6'),
                _pk_translate('General_LongMonth_7'),
                _pk_translate('General_LongMonth_8'),
                _pk_translate('General_LongMonth_9'),
                _pk_translate('General_LongMonth_10'),
                _pk_translate('General_LongMonth_11'),
                _pk_translate('General_LongMonth_12')]
        };
    };

    var updateDate;

    function getDatePickerOptions() {
        var result = piwik.getBaseDatePickerOptions(currentDate);
        result.beforeShowDay = isDateInCurrentPeriod;
        result.stepMonths = selectedPeriod == 'year' ? 12 : 1;
        result.onSelect = function () { updateDate.apply(this, arguments); };
        return result;
    }

    $(function () {

        var datepickerElem = $('#datepicker').datepicker(getDatePickerOptions()),
            periodLabels = $('#periodString').find('.period-type label'),
            periodTooltip = $('#periodString').find('.period-click-tooltip').html();

        var toggleWhitespaceHighlighting = function (klass, toggleTop, toggleBottom) {
            var viewedYear = $('.ui-datepicker-year', datepickerElem).val(),
                viewedMonth = +$('.ui-datepicker-month', datepickerElem).val(), // convert to int w/ '+'
                firstOfViewedMonth = new Date(viewedYear, viewedMonth, 1),
                lastOfViewedMonth = new Date(viewedYear, viewedMonth + 1, 0);

            // only highlight dates between piwik.minDate... & piwik.maxDate...
            // we select the cells to highlight by checking whether the first & last of the
            // currently viewed month are within the min/max dates.
            if (firstOfViewedMonth >= piwikMinDate) {
                $('tbody>tr:first-child td.ui-datepicker-other-month', datepickerElem).toggleClass(klass, toggleTop);
            }
            if (lastOfViewedMonth < piwikMaxDate) {
                $('tbody>tr:last-child td.ui-datepicker-other-month', datepickerElem).toggleClass(klass, toggleBottom);
            }
        };

        // 'this' is the table cell
        var highlightCurrentPeriod = function () {
            switch (selectedPeriod) {
                case 'day':
                    // highlight this link
                    $('a', $(this)).addClass('ui-state-hover');
                    break;
                case 'week':
                    var row = $(this).parent();

                    // highlight parent row (the week)
                    $('a', row).addClass('ui-state-hover');

                    // toggle whitespace if week goes into previous or next month. we check if week is on
                    // top or bottom row.
                    var toggleTop = row.is(':first-child'),
                        toggleBottom = row.is(':last-child');
                    toggleWhitespaceHighlighting('ui-state-hover', toggleTop, toggleBottom);
                    break;
                case 'month':
                    // highlight all parent rows (the month)
                    $('a', $(this).parent().parent()).addClass('ui-state-hover');
                    break;
                case 'year':
                    // highlight table (month + whitespace)
                    $('a', $(this).parent().parent()).addClass('ui-state-hover');
                    toggleWhitespaceHighlighting('ui-state-hover', true, true);
                    break;
            }
        };

        var unhighlightAllDates = function () {
            // make sure nothing is highlighted
            $('.ui-state-active,.ui-state-hover', datepickerElem).removeClass('ui-state-active ui-state-hover');

            // color whitespace
            if (piwik.period == 'year') {
                var viewedYear = $('.ui-datepicker-year', datepickerElem).val(),
                    toggle = selectedPeriod == 'year' && currentYear == viewedYear;
                toggleWhitespaceHighlighting('ui-datepicker-current-period', toggle, toggle);
            }
            else if (piwik.period == 'week') {
                var toggleTop = $('tr:first-child a', datepickerElem).parent().hasClass('ui-datepicker-current-period'),
                    toggleBottom = $('tr:last-child a', datepickerElem).parent().hasClass('ui-datepicker-current-period');
                toggleWhitespaceHighlighting('ui-datepicker-current-period', toggleTop, toggleBottom);
            }
        };

        updateDate = function (dateText) {
            piwikHelper.showAjaxLoading('ajaxLoadingCalendar');

            // select new dates in calendar
            setCurrentDate(dateText);
            piwik.period = selectedPeriod;

            // make sure it's called after jquery-ui is done, otherwise everything we do will
            // be undone.
            setTimeout(unhighlightAllDates, 1);

            datepickerElem.datepicker('refresh');

            // Let broadcast do its job:
            // It will replace date value to both search query and hash and load the new page.
            broadcast.propagateNewPage('date=' + dateText + '&period=' + selectedPeriod);
        };

        var toggleMonthDropdown = function (disable) {
            if (typeof disable === 'undefined') {
                disable = selectedPeriod == 'year';
            }

            // enable/disable month dropdown based on period == year
            $('.ui-datepicker-month', datepickerElem).attr('disabled', disable);
        };

        var togglePeriodPickers = function (showSingle) {
            $('#periodString').find('.period-date').toggle(showSingle);
            $('#periodString').find('.period-range').toggle(!showSingle);
            $('#calendarRangeApply').toggle(!showSingle);
        };

        //
        // setup datepicker
        //

        unhighlightAllDates();

        //
        // hook up event slots
        //

        // highlight current period when mouse enters date
        datepickerElem.on('mouseenter', 'tbody td', function () {
            if ($(this).hasClass('ui-state-hover')) // if already highlighted, do nothing
            {
                return;
            }

            // unhighlight if cell is disabled/blank, unless the period is year
            if ($(this).hasClass('ui-state-disabled') && selectedPeriod != 'year') {
                unhighlightAllDates();

                // if period is week, then highlight the current week
                if (selectedPeriod == 'week') {
                    highlightCurrentPeriod.call(this);
                }
            }
            else {
                highlightCurrentPeriod.call(this);
            }
        });

        // make sure cell stays highlighted when mouse leaves cell (overrides jquery-ui behavior)
        datepickerElem.on('mouseleave', 'tbody td', function () {
            $('a', this).addClass('ui-state-hover');
        });

        // unhighlight everything when mouse leaves table body (can't do event on tbody, for some reason
        // that fails, so we do two events, one on the table & one on thead)
        datepickerElem.on('mouseleave', 'table', unhighlightAllDates)
            .on('mouseenter', 'thead', unhighlightAllDates);

        // make sure whitespace is clickable when the period makes it appropriate
        datepickerElem.on('click', 'tbody td.ui-datepicker-other-month', function () {
            if ($(this).hasClass('ui-state-hover')) {
                var row = $(this).parent(), tbody = row.parent();

                if (row.is(':first-child')) {
                    // click on first of the month
                    $('a', tbody).first().click();
                }
                else {
                    // click on last of month
                    $('a', tbody).last().click();
                }
            }
        });

        var reloading = false;
        var changePeriodOnClick = function (periodInput) {
            if (reloading) // if a click event resulted in reloading, don't reload again
            {
                return;
            }

            var url = periodInput.val(),
                period = broadcast.getValueFromUrl('period', url);

            // if clicking on the selected period, change the period but not the date
            if (selectedPeriod == period && selectedPeriod != 'range') {
                // only reload if current period is different from selected
                if (piwik.period != selectedPeriod && !reloading) {
                    reloading = true;
                    selectedPeriod = period;
                    updateDate(piwik.currentDateString);
                }
                return true;
            }

            return false;
        };

        $("#otherPeriods").find("label,input").on('dblclick', function (e) {
            var id = $(e.target).attr('for');
            changePeriodOnClick($('#' + id));
        });

        // when non-range period is clicked, change the period & refresh the date picker
        $("#otherPeriods").find("input").on('click', function (e) {
            var request_URL = $(e.target).val(),
                period = broadcast.getValueFromUrl('period', request_URL),
                lastPeriod = selectedPeriod;

            if (changePeriodOnClick($(e.target))) {
                return true;
            }

            // switch the selected period
            selectedPeriod = period;

            // remove tooltips from the period inputs
            periodLabels.each(function () { $(this).attr('title', '').removeClass('selected-period-label'); });

            // range periods are handled in an event handler below
            if (period == 'range') {
                return true;
            }

            // set the tooltip of the current period
            if (period != piwik.period) // don't add tooltip for current period
            {
                $(this).parent().find('label[for=period_id_' + period + ']')
                    .attr('title', periodTooltip).addClass('selected-period-label');
            }

            // toggle the right selector controls (show period selector datepicker & hide 'apply range' button)
            togglePeriodPickers(true);

            // set months step to 12 for year period (or set back to 1 if leaving year period)
            if (selectedPeriod == 'year' || lastPeriod == 'year') {
                // setting stepMonths will change the month in view back to the selected date. to avoid
                // we set the selected date to the month in view.
                var currentMonth = $('.ui-datepicker-month', datepickerElem).val(),
                    currentYear = $('.ui-datepicker-year', datepickerElem).val();

                datepickerElem
                    .datepicker('option', 'stepMonths', selectedPeriod == 'year' ? 12 : 1)
                    .datepicker('setDate', new Date(currentYear, currentMonth));
            }

            datepickerElem.datepicker('refresh'); // must be last datepicker call, otherwise cells get highlighted

            unhighlightAllDates();
            toggleMonthDropdown();

            return true;
        });

        // clicking left/right re-enables the month dropdown, so we disable it again
        $(datepickerElem).on('click', '.ui-datepicker-next,.ui-datepicker-prev', function () {
            unhighlightAllDates(); // make sure today's date isn't highlighted & toggle extra year highlighting
            toggleMonthDropdown(selectedPeriod == 'year');
        });

        // reset date/period when opening calendar
        $("#periodString").on('click', "#date,.calendar-icon", function () {
            var periodMore = $("#periodMore").toggle();
            if (periodMore.is(":visible")) {
                periodMore.find(".ui-state-highlight").removeClass('ui-state-highlight');
            }
        });

        $('body').on('click', function(e) {
            var target = $(e.target);
            if (target.closest('html').length && !target.closest('#periodString').length && !target.is('option') && $("#periodMore").is(":visible")) {
                $("#periodMore").hide();
            }
        });

        function onDateRangeSelect(dateText, inst) {
            var toOrFrom = inst.id == 'calendarFrom' ? 'From' : 'To';
            $('#inputCalendar' + toOrFrom).val(dateText);
        }

        // this will trigger to change only the period value on search query and hash string.
        $("#period_id_range").on('click', function (e) {
            togglePeriodPickers(false);

            var options = getDatePickerOptions();

            // Custom Date range callback
            options.onSelect = onDateRangeSelect;
            // Do not highlight the period
            options.beforeShowDay = '';
            // Create both calendars
            options.defaultDate = piwik.startDateString;
            $('#calendarFrom').datepicker(options).datepicker("setDate", $.datepicker.parseDate('yy-mm-dd', piwik.startDateString));

            // Technically we should trigger the onSelect event on the calendar, but I couldn't find how to do that
            // So calling the onSelect bind function manually...
            //$('#calendarFrom').trigger('dateSelected'); // or onSelect
            onDateRangeSelect(piwik.startDateString, { "id": "calendarFrom" });

            // Same code for the other calendar
            options.defaultDate = piwik.endDateString;
            $('#calendarTo').datepicker(options).datepicker("setDate", $.datepicker.parseDate('yy-mm-dd', piwik.endDateString));
            onDateRangeSelect(piwik.endDateString, { "id": "calendarTo" });

            // If not called, the first date appears light brown instead of dark brown
            $('.ui-state-hover').removeClass('ui-state-hover');

            // Apply date range button will reload the page with the selected range
            $('#calendarRangeApply')
                .on('click', function () {
                    var request_URL = $(e.target).val();
                    var dateFrom = $('#inputCalendarFrom').val(),
                        dateTo = $('#inputCalendarTo').val(),
                        oDateFrom = $.datepicker.parseDate('yy-mm-dd', dateFrom),
                        oDateTo = $.datepicker.parseDate('yy-mm-dd', dateTo);

                    if (!isValidDate(oDateFrom)
                        || !isValidDate(oDateTo)
                        || oDateFrom > oDateTo) {
                        $('#alert').find('h2').text(_pk_translate('General_InvalidDateRange'));
                        piwikHelper.modalConfirm('#alert', {});
                        return false;
                    }
                    piwikHelper.showAjaxLoading('ajaxLoadingCalendar');
                    broadcast.propagateNewPage('period=range&date=' + dateFrom + ',' + dateTo);
                })
                .show();

            // Bind the input fields to update the calendar's date when date is manually changed
            $('#inputCalendarFrom, #inputCalendarTo')
                .keyup(function (e) {
                    var fromOrTo = this.id == 'inputCalendarFrom' ? 'From' : 'To';
                    var dateInput = $(this).val();
                    try {
                        var newDate = $.datepicker.parseDate('yy-mm-dd', dateInput);
                    } catch (e) {
                        return;
                    }
                    $("#calendar" + fromOrTo).datepicker("setDate", newDate);
                    if (e.keyCode == 13) {
                        $('#calendarRangeApply').click();
                    }
                });
            return true;
        });
        function isValidDate(d) {
            if (Object.prototype.toString.call(d) !== "[object Date]")
                return false;
            return !isNaN(d.getTime());
        }

        if (piwik.period == 'range') {
            $("#period_id_range").click();
        }
    });

}(jQuery));
