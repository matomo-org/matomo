
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

function isDateSelected( date )
{
	var valid = false;

	var dateMonth = date.getMonth();
	var dateYear = date.getFullYear();
	var dateDay = date.getDate();
	var style = '';

	if( date.toLocaleDateString() == todayDate.toLocaleDateString())
	{
		style = style + 'dateToday ';
	}

	// we dont color dates in the future
	if( dateMonth == todayMonth
		&& dateYear == todayYear
		&& dateDay >= todayDay
	)
	{
		return [true, style];
	}

	// we dont color dates before the minimum date
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
		return [true, style];
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
		return [true, style+'dateUsedStats'];
	}
	return [true, style];
}


function updateDate()
{
	var date = formatDate(popUpCal.getDateFor($('#calendar')[0]));
        // Let broadcast do it job:
        // It will replace date value to both search query and hash and load the new page.
        broadcast.propagateNewPage('date='+date);
}

function formatDate(date)
{
	var day = date.getDate();
	var month = date.getMonth() + 1;
	return date.getFullYear() + '-'
		+ (month < 10 ? '0' : '') + month + '-'
		+ (day < 10 ? '0' : '') + day ;
}

$(document).ready(function(){

	$("#calendar").calendar({
			onSelect: updateDate,
			showOtherMonths: true,
			dateFormat: 'DMY-',
			firstDay: 1,
			minDate: new Date(piwik.minDateYear, piwik.minDateMonth - 1, piwik.minDateDay),
			maxDate: new Date(piwik.maxDateYear, piwik.maxDateMonth - 1, piwik.maxDateDay),
			changeFirstDay: false,
			prevText: "",
			nextText: "",
			currentText: "",
			customDate: isDateSelected,
			dayNames: [
				_pk_translate('CoreHome_DaySu_js'),
				_pk_translate('CoreHome_DayMo_js'),
				_pk_translate('CoreHome_DayTu_js'),
				_pk_translate('CoreHome_DayWe_js'),
				_pk_translate('CoreHome_DayTh_js'),
				_pk_translate('CoreHome_DayFr_js'),
				_pk_translate('CoreHome_DaySa_js')],
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
			},
			currentDate);

		$("#calendar").hide();
	}
);
