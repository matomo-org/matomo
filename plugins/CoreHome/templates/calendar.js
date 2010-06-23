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
	var date = dateText;
	// Let broadcast do its job:
	// It will replace date value to both search query and hash and load the new page.
	broadcast.propagateNewPage('date=' + date);
}

$(document).ready(function(){
	$('#datepicker').datepicker({
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
	});
});
