/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

Date.prototype.getWeek = function() {
	var onejan = new Date(this.getFullYear(),0,1);
	return Math.ceil((((this - onejan) / 86400000) + onejan.getDay())/7);
}

var splitDate = piwik.currentDateString.split("-");
var currentYear = splitDate[0];
var currentMonth = splitDate[1] - 1;
var currentDay = splitDate[2];
var currentDate = new Date(currentYear, currentMonth, currentDay);

var todayDate = new Date;
var todayMonth = todayDate.getMonth();
var todayYear = todayDate.getFullYear();
var todayDay = todayDate.getDate();

function highlightCurrentPeriod( date )
{
	var valid = false;

	var dateMonth = date.getMonth();
	var dateYear = date.getFullYear();
	var dateDay = date.getDate();
	var style = '';

	// we don't color dates in the future
	if( dateMonth == todayMonth
		&& dateYear == todayYear
		&& dateDay >= todayDay
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
			&& date.getWeek() == currentDate.getWeek()
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

function updateDate(dateText, inst)
{
    piwikHelper.showAjaxLoading('ajaxLoadingCalendar');
	var date = dateText;
	// Let broadcast do its job:
	// It will replace date value to both search query and hash and load the new page.
	broadcast.propagateNewPage('date=' + date);
}


function getDatePickerOptions()
{
	return {
		onSelect: updateDate,
		showOtherMonths: false,
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		minDate: new Date(piwik.minDateYear, piwik.minDateMonth - 1, piwik.minDateDay),
		maxDate: new Date(piwik.maxDateYear, piwik.maxDateMonth - 1, piwik.maxDateDay),
		prevText: "",
		nextText: "",
		currentText: "",
		beforeShowDay: highlightCurrentPeriod,
		defaultDate: currentDate,
		changeMonth: true,
		changeYear: true,
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
}
function displayCalendar()
{
	if(typeof piwik.currentDateString != "undefined")
	{
		$(document).ready(function(){
			$('#datepicker').datepicker(getDatePickerOptions());
		});
	}
}
$(document).ready(function() {
	
	displayCalendar();
	
	
	// this will trigger to change only the period value on search query and hash string.
	$("#otherPeriods input").bind('click',function(e) {
	    var request_URL = $(e.target).attr("value");
	    var period = broadcast.getValueFromUrl('period',request_URL);
	    if(period == 'range') return true;
	    broadcast.propagateNewPage('period='+period+'&date='+piwik.currentDateString);
	    piwikHelper.showAjaxLoading('ajaxLoadingCalendar');
		return true;
	});
	
	function onDateRangeSelect(dateText, inst)
	{
		var toOrFrom = inst.id == 'calendarFrom' ? 'From' : 'To';
		//alert(dateText + toOrFrom);
		$('#inputCalendar'+toOrFrom).val(dateText);
	}
	 
	// this will trigger to change only the period value on search query and hash string.
	$("#period_id_range").bind('click', function(e) {
		$('.period-date').html('<div id="calendarRangeFrom"><h6>'+_pk_translate('General_DateRangeFrom_js')+'<input tabindex="1" type="text" id="inputCalendarFrom" name="inputCalendarFrom"/></h6><div id="calendarFrom"></div></div>'+
		 			 				'<div id="calendarRangeTo"><h6>'+_pk_translate('General_DateRangeTo_js')+'<input tabindex="2" type="text" id="inputCalendarTo" name="inputCalendarTo"/></h6><div id="calendarTo"></div></div>');
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
		 		.bind('click', function() {
		 	        var request_URL = $(e.target).attr("value");
		 	        var dateFrom = $('#inputCalendarFrom').val(), 
		 	        	dateTo = $('#inputCalendarTo').val(),
		 	        	oDateFrom = $.datepicker.parseDate('yy-mm-dd', dateFrom),
		 	        	oDateTo = $.datepicker.parseDate('yy-mm-dd', dateTo);
		 	        
		 	        if( !isValidDate(oDateFrom )
		 	        	|| !isValidDate(oDateTo )
		 	        	|| oDateFrom > oDateTo )
		 	        {
		 	        	$('#alert h2').text(_pk_translate('General_InvalidDateRange_js'));
		 	        	piwikHelper.windowModal('#alert', function(){});
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