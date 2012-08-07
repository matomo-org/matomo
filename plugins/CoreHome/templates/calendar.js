/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function($) {

Date.prototype.getWeek = function() {
	var onejan = new Date(this.getFullYear(),0,1), // needed for getDay()
		
		// use UTC times since getTime() can differ based on user's timezone
		onejan_utc = Date.UTC(this.getFullYear(),0,1),
		this_utc = Date.UTC(this.getFullYear(),this.getMonth(),this.getDate()),
		
		daysSinceYearStart = (this_utc - onejan_utc) / 86400000; // constant is millisecs in one day
	
	return Math.ceil((daysSinceYearStart + onejan.getDay()) / 7);
}

var currentYear, currentMonth, currentDay, currentDate, currentWeek;
function setCurrentDate( dateStr )
{
	var splitDate = dateStr.split("-");
	currentYear = splitDate[0];
	currentMonth = splitDate[1] - 1;
	currentDay = splitDate[2];
	currentDate = new Date(currentYear, currentMonth, currentDay);
	currentWeek = currentDate.getWeek();
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

function isDateInCurrentPeriod( date )
{
	// if the selected period isn't the current period, don't highlight any dates
	if (selectedPeriod != piwik.period)
	{
		return [true, ''];
	}
	
	var valid = false;

	var dateMonth = date.getMonth();
	var dateYear = date.getFullYear();
	var dateDay = date.getDate();
	var style = '';

	// we don't color dates in the future
	if( dateMonth == todayMonth
		&& dateYear == todayYear
		&& dateDay > todayDay
	)
	{
		return [true, ''];
	}

	// we don't color dates before the minimum date
	if( dateYear < piwik.minDateYear
		|| ( dateYear == piwik.minDateYear
				&&
					(
						(dateMonth == piwik.minDateMonth - 1
						&& dateDay < piwik.minDateDay)
					||  (dateMonth < piwik.minDateMonth - 1)
				)
			)
	)
	{
		return [true, ''];
	}

	// we color all day of the month for the same year for the month period
	if(piwik.period == "month"
		&& dateMonth == currentMonth
		&& dateYear == currentYear
	)
	{
		valid = true;
	}
	// we color all day of the year for the year period
	else if(piwik.period == "year"
			&& dateYear == currentYear
	)
	{
		valid = true;
	}
	else if(piwik.period == "week"
			&& date.getWeek() == currentWeek
			&& dateYear == currentYear
	)
	{
		valid = true;
	}
	else if( piwik.period == "day"
				&& dateDay == currentDay
				&& dateMonth == currentMonth
				&& dateYear == currentYear
		)
	{
		valid = true;
	}

	if(valid)
	{
		return [true, 'ui-datepicker-current-period'];
	}

	return [true, ''];
}

var updateDate;
function getDatePickerOptions()
{
	return {
		onSelect: function () { updateDate.apply(this, arguments); },
		showOtherMonths: false,
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		minDate: piwikMinDate,
		maxDate: piwikMaxDate,
		prevText: "",
		nextText: "",
		currentText: "",
		beforeShowDay: isDateInCurrentPeriod,
		defaultDate: currentDate,
		changeMonth: true,
		changeYear: true,
		stepMonths: selectedPeriod == 'year' ? 12 : 1,
		// jquery-ui-i18n 1.7.2 lacks some translations, so we use our own
		dayNamesMin: [
			_pk_translate('CoreHome_DaySu_js'),
			_pk_translate('CoreHome_DayMo_js'),
			_pk_translate('CoreHome_DayTu_js'),
			_pk_translate('CoreHome_DayWe_js'),
			_pk_translate('CoreHome_DayTh_js'),
			_pk_translate('CoreHome_DayFr_js'),
			_pk_translate('CoreHome_DaySa_js')],
		dayNamesShort: [
			_pk_translate('CoreHome_ShortDay_1_js'),
			_pk_translate('CoreHome_ShortDay_2_js'),
			_pk_translate('CoreHome_ShortDay_3_js'),
			_pk_translate('CoreHome_ShortDay_4_js'),
			_pk_translate('CoreHome_ShortDay_5_js'),
			_pk_translate('CoreHome_ShortDay_6_js'),
			_pk_translate('CoreHome_ShortDay_7_js')],
		dayNames: [
			_pk_translate('CoreHome_LongDay_1_js'),
			_pk_translate('CoreHome_LongDay_2_js'),
			_pk_translate('CoreHome_LongDay_3_js'),
			_pk_translate('CoreHome_LongDay_4_js'),
			_pk_translate('CoreHome_LongDay_5_js'),
			_pk_translate('CoreHome_LongDay_6_js'),
			_pk_translate('CoreHome_LongDay_7_js')],
		monthNamesShort: [
			_pk_translate('CoreHome_ShortMonth_1_js'),
			_pk_translate('CoreHome_ShortMonth_2_js'),
			_pk_translate('CoreHome_ShortMonth_3_js'),
			_pk_translate('CoreHome_ShortMonth_4_js'),
			_pk_translate('CoreHome_ShortMonth_5_js'),
			_pk_translate('CoreHome_ShortMonth_6_js'),
			_pk_translate('CoreHome_ShortMonth_7_js'),
			_pk_translate('CoreHome_ShortMonth_8_js'),
			_pk_translate('CoreHome_ShortMonth_9_js'),
			_pk_translate('CoreHome_ShortMonth_10_js'),
			_pk_translate('CoreHome_ShortMonth_11_js'),
			_pk_translate('CoreHome_ShortMonth_12_js')],
		monthNames: [
			_pk_translate('CoreHome_MonthJanuary_js'),
			_pk_translate('CoreHome_MonthFebruary_js'),
			_pk_translate('CoreHome_MonthMarch_js'),
			_pk_translate('CoreHome_MonthApril_js'),
			_pk_translate('CoreHome_MonthMay_js'),
			_pk_translate('CoreHome_MonthJune_js'),
			_pk_translate('CoreHome_MonthJuly_js'),
			_pk_translate('CoreHome_MonthAugust_js'),
			_pk_translate('CoreHome_MonthSeptember_js'),
			_pk_translate('CoreHome_MonthOctober_js'),
			_pk_translate('CoreHome_MonthNovember_js'),
			_pk_translate('CoreHome_MonthDecember_js')]
	}
};

$(document).ready(function() {
	
	var datepickerElem = $('#datepicker').datepicker(getDatePickerOptions());
	
	var toggleWhitespaceHighlighting = function (klass, toggleTop, toggleBottom)
	{
		var viewedYear = $('.ui-datepicker-year', datepickerElem).val(),
			viewedMonth = +$('.ui-datepicker-month', datepickerElem).val(), // convert to int w/ '+'
			firstOfViewedMonth = new Date(viewedYear, viewedMonth, 1),
			lastOfViewedMonth = new Date(viewedYear, viewedMonth + 1, 0);
		
		// only highlight dates between piwik.minDate... & piwik.maxDate...
		// we select the cells to highlight by checking whether the first & last of the
		// currently viewed month are within the min/max dates.
		if (firstOfViewedMonth >= piwikMinDate)
		{
			$('tbody>tr:first-child td.ui-datepicker-other-month', datepickerElem).toggleClass(klass, toggleTop);
		}
		if (lastOfViewedMonth < piwikMaxDate)
		{
			$('tbody>tr:last-child td.ui-datepicker-other-month', datepickerElem).toggleClass(klass, toggleBottom);
		}
	};
	
	// 'this' is the table cell
	var highlightCurrentPeriod = function ()
	{
		switch (selectedPeriod)
		{
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
	
	var unhighlightAllDates = function ()
	{
		// make sure nothing is highlighted 
		$('.ui-state-active,.ui-state-hover', datepickerElem).removeClass('ui-state-active ui-state-hover');
		
		// color whitespace
		if (piwik.period == 'year')
		{
			var viewedYear = $('.ui-datepicker-year', datepickerElem).val(),
				toggle = selectedPeriod == 'year' && currentYear == viewedYear;
			toggleWhitespaceHighlighting('ui-datepicker-current-period', toggle, toggle);
		}
		else if (piwik.period == 'week')
		{
			var toggleTop = $('tr:first-child a', datepickerElem).parent().hasClass('ui-datepicker-current-period'),
				toggleBottom = $('tr:last-child a', datepickerElem).parent().hasClass('ui-datepicker-current-period');
			toggleWhitespaceHighlighting('ui-datepicker-current-period', toggleTop, toggleBottom);
		}
	};
	
	updateDate = function (dateText, inst)
	{
		piwikHelper.showAjaxLoading('ajaxLoadingCalendar');
		var date = dateText;
	
		// select new dates in calendar
		setCurrentDate(dateText);
		piwik.period = selectedPeriod;
		
		// make sure it's called after jquery-ui is done, otherwise everything we do will
		// be undone.
		setTimeout(unhighlightAllDates, 1);
		
		datepickerElem.datepicker('refresh');
		
		// Let broadcast do its job:
		// It will replace date value to both search query and hash and load the new page.
		broadcast.propagateNewPage('date=' + date + '&period=' + selectedPeriod);
	};

	var toggleMonthDropdown = function (disable)
	{
		if (typeof disable === 'undefined')
		{
			disable = selectedPeriod == 'year';
		}
		
	    // enable/disable month dropdown based on period == year
	    $('.ui-datepicker-month', datepickerElem).attr('disabled', disable);
	};
	
	var togglePeriodPickers = function (showSingle)
	{
		$('#periodString .period-date').toggle(showSingle);
		$('#periodString .period-range').toggle(!showSingle);
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
	datepickerElem.on('mouseenter', 'tbody td', function() {
		if ($(this).hasClass('ui-state-hover')) // if already highlighted, do nothing
		{
			return;
		}
		
		// unhighlight if cell is disabled/blank, unless the period is year
		if ($(this).hasClass('ui-state-disabled') && selectedPeriod != 'year')
		{
			unhighlightAllDates();
			
			// if period is week, then highlight the current week
			if (selectedPeriod == 'week')
			{
				highlightCurrentPeriod.call(this);
			}
		}
		else
		{
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
		if ($(this).hasClass('ui-state-hover'))
		{
			var row = $(this).parent(), tbody = row.parent();
			
			if (row.is(':first-child'))
			{
				// click on first of the month
				$('a', tbody).first().click();
			}
			else
			{
				// click on last of month
				$('a', tbody).last().click();
			}
		}
	});
	
	// when non-range period is clicked, change the period & refresh the date picker
	$("#otherPeriods input").on('click', function(e) {
	    var request_URL = $(e.target).val(),
	    	period = broadcast.getValueFromUrl('period', request_URL),
	    	lastPeriod = selectedPeriod;
	    
	    // switch the selected period
		selectedPeriod = period;
		
		// range periods are handled in an event handler below
	    if (period == 'range')
	    {
	    	return true;
	    }
	    
	    // toggle the right selector controls (show period selector datepicker & hide 'apply range' button)
	    togglePeriodPickers(true);
		
	    // set months step to 12 for year period (or set back to 1 if leaving year period)
	    if (selectedPeriod == 'year' || lastPeriod == 'year')
	    {
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
	$(datepickerElem).on('click', '.ui-datepicker-next,.ui-datepicker-prev', function() {
		unhighlightAllDates(); // make sure today's date isn't highlighted & toggle extra year highlighting
		toggleMonthDropdown(selectedPeriod == 'year');
	});
	
	// reset date/period when opening calendar
	var firstClick = true;
	$('#periodString #date').click(function() {
		if (!firstClick)
		{
			datepickerElem.datepicker('setDate', currentDate);
			$('#period_id_' + piwik.period).click();
		}
		
		firstClick = false;
	});
	
	function onDateRangeSelect(dateText, inst)
	{
		var toOrFrom = inst.id == 'calendarFrom' ? 'From' : 'To';
		$('#inputCalendar'+toOrFrom).val(dateText);
	}
	
	// this will trigger to change only the period value on search query and hash string.
	$("#period_id_range").on('click', function(e) {
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
		onDateRangeSelect(piwik.startDateString, { "id": "calendarFrom" } );
		
		// Same code for the other calendar
		options.defaultDate = piwik.endDateString;
		$('#calendarTo').datepicker(options).datepicker("setDate", $.datepicker.parseDate('yy-mm-dd', piwik.endDateString));
		onDateRangeSelect(piwik.endDateString, { "id": "calendarTo" });
		
	
		// If not called, the first date appears light brown instead of dark brown
		$('.ui-state-hover').removeClass('ui-state-hover');
		
		// Apply date range button will reload the page with the selected range
		 	$('#calendarRangeApply')
		 		.on('click', function() {
		 	        var request_URL = $(e.target).val();
		 	        var dateFrom = $('#inputCalendarFrom').val(), 
		 	        	dateTo = $('#inputCalendarTo').val(),
		 	        	oDateFrom = $.datepicker.parseDate('yy-mm-dd', dateFrom),
		 	        	oDateTo = $.datepicker.parseDate('yy-mm-dd', dateTo);
		 	        
		 	        if( !isValidDate(oDateFrom )
		 	        	|| !isValidDate(oDateTo )
		 	        	|| oDateFrom > oDateTo )
		 	        {
		 	        	$('#alert h2').text(_pk_translate('General_InvalidDateRange_js'));
		 	        	piwikHelper.modalConfirm('#alert', {});
		 	        	return false;
		 	        }
		         	piwikHelper.showAjaxLoading('ajaxLoadingCalendar');
		 	        broadcast.propagateNewPage('period=range&date='+dateFrom+','+dateTo);
		 		})
		 		.show();
		
	
		// Bind the input fields to update the calendar's date when date is manually changed
		$('#inputCalendarFrom, #inputCalendarTo')
			.keyup( function (e) {
				var fromOrTo = this.id == 'inputCalendarFrom' ? 'From' : 'To';
				var dateInput = $(this).val();
				try {
				    var newDate = $.datepicker.parseDate('yy-mm-dd', dateInput);
				} catch (e) {
				    return;
				}
				$("#calendar"+fromOrTo).datepicker("setDate", newDate);
				if(e.keyCode == 13) {
					$('#calendarRangeApply').click();
				}
		});
		return true;
	});
	 function isValidDate(d) {
		  if ( Object.prototype.toString.call(d) !== "[object Date]" )
		    return false;
		  return !isNaN(d.getTime());
		}
	
	 var period = broadcast.getValueFromUrl('period');
	 if(period == 'range') { 
		 $("#period_id_range").click();
	 }
});

}(jQuery));
