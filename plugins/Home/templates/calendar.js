
Date.prototype.getWeek = function() {
var onejan = new Date(this.getFullYear(),0,1);
return Math.ceil((((this - onejan) / 86400000) + onejan.getDay())/7);
}

var splitDate = currentDateStr.split("-");

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
	if( dateYear < minDateYear
		|| ( dateYear == minDateYear
				&& 
					(
						(dateMonth == minDateMonth - 1
						&& dateDay < minDateDay)
					||  (dateMonth < minDateMonth - 1)
				)
			)
	)
	{
		return [true, style];		
	}
	
	// we color all day of the month for the same year for the month period
	if(period == "month"
		&& dateMonth == currentMonth
		&& dateYear == currentYear
	)
	{
		valid = true;
	}
	// we color all day of the year for the year period
	else if(period == "year"
			&& dateYear == currentYear
	)
	{
		valid = true;
	}
	else if(period == "week"
			&& date.getWeek() == currentDate.getWeek()
			&& dateYear == currentYear
	)
	{
		valid = true;
	}
	else if( period == "day"  
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

	// available in global scope
	var currentUrl = window.location.href;
	if((startStrDate = currentUrl.indexOf("date")) >= 0)
	{
		// look for the & after the date
		var endStrDate = currentUrl.indexOf("&", startStrDate);
		if(endStrDate == -1)
		{
			endStrDate = currentUrl.length;
		}

		var dateToReplace = currentUrl.substring( 
							startStrDate + 4+1, 
							endStrDate
						);
		regDateToReplace = new RegExp(dateToReplace, 'ig');
		currentUrl = currentUrl.replace( regDateToReplace, date );		
	}
	else
	{
		currentUrl = currentUrl + '&date=' + date;
	}
	
	window.location.href = currentUrl;
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
			minDate: new Date(minDateYear, minDateMonth - 1, minDateDay),
			maxDate: new Date(),
			changeFirstDay: false,
			prevText: "",
			nextText: "",
			currentText: "",
			customDate: isDateSelected,
			dayNames: [
				_pk_translate('Home_DaySu'),
				_pk_translate('Home_DayMo'),
				_pk_translate('Home_DayTu'),
				_pk_translate('Home_DayWe'),
				_pk_translate('Home_DayTh'),
				_pk_translate('Home_DayFr'),
				_pk_translate('Home_DaySa')],
			monthNames: [
				_pk_translate('Home_MonthJanuary'),
				_pk_translate('Home_MonthFebruary'),
				_pk_translate('Home_MonthMarch'),
				_pk_translate('Home_MonthApril'),
				_pk_translate('Home_MonthMay'),
				_pk_translate('Home_MonthJune'),
				_pk_translate('Home_MonthJuly'),
				_pk_translate('Home_MonthAugust'),
				_pk_translate('Home_MonthSeptember'),
				_pk_translate('Home_MonthOctober'),
				_pk_translate('Home_MonthNovember'),
				_pk_translate('Home_MonthDecemeber')]
			},
			currentDate);
	}
);
