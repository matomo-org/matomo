
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.dimensions.js"></script>
<script type="text/javascript" src="libs/jquery/tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="libs/jquery/truncate/jquery.truncate.js"></script>
<script type="text/javascript" src="plugins/UserSettings/templates/datatable.js"></script>

<link rel="stylesheet" href="libs/jquery/tooltip/jquery.tooltip.css">
<link rel="stylesheet" href="plugins/UserSettings/templates/datatable.css">

<script>
var period = "{$period}";
var currentDateStr = "{$date}";
</script>

{literal}
<style>
tr.level0 td.label{
	padding-left:+1.5em;
}
tr.level1 td.label{
	padding-left:+3.5em;
}
tr.level2 td.label{
	padding-left:+5.5em;
}
tr.level3 td.label{
	padding-left:+6.5em;
}
tr.level4 td.label{
	padding-left:+7em;
}
tr td.label img.plusMinus {
	margin-left:-1em;
	position:absolute;
}





.calendar td.testSelect, .calendar td.testSelect a{
	color:red;
	border-color:red;
}
</style>


<style type="text/css">@import url(libs/jquery/jquery-calendar.css);</style>
<script type="text/javascript" src="libs/jquery/jquery-calendar.js"></script>
<script>

Date.prototype.getWeek = function (dowOffset) {
	/*getWeek() was developed by Nick Baicoianu at MeanFreePath: http://www.meanfreepath.com */
	
	dowOffset = typeof(dowOffset) == 'int' ? dowOffset : 0; //default dowOffset to zero
	var newYear = new Date(this.getFullYear(),0,1);
	var day = newYear.getDay() - dowOffset; //the day of week the year begins on
	day = (day >= 0 ? day : day + 7);
	var daynum = Math.floor((this.getTime() - newYear.getTime() -
	(this.getTimezoneOffset()-newYear.getTimezoneOffset())*60000)/86400000) + 1;
	var weeknum;
	//if the year starts before the middle of a week
	if(day < 4) {
	weeknum = Math.floor((daynum+day-1)/7) + 1;
	if(weeknum > 52) {
	nYear = new Date(this.getFullYear() + 1,0,1);
	nday = nYear.getDay() - dowOffset;
	nday = nday >= 0 ? nday : nday + 7;
	/*if the next year starts before the middle of
	the week, it is week #1 of that year*/
	weeknum = nday < 4 ? 1 : 53;
	}
	}
	else {
	weeknum = Math.floor((daynum+day-1)/7);
	}
	return weeknum;
};

var splitDate = currentDateStr.split("-");
var currentYear = splitDate[0];
var currentMonth = splitDate[1];
var currentDay = splitDate[2];

var currentDate = new Date(currentYear, currentMonth, currentDay);



function isDateSelected( date )
{
	var valid = false;
	if(period == "month" || period == "year")
	{
		valid = true;
	}
	else if(period == "week" && date.getWeek(1) == currentDate.getWeek(1) )
	{
		valid = true;
	}
	else if( period == "day"  && date.getDate() == currentDate.getDate() )
	{
		valid = true;
	}
	
	if(valid)
	{
		return [true, 'testSelect'];
	}
	return [true, ''];
}


function updateDate()
{
	var date = formatDate(popUpCal.getDateFor($('#chooseDate')[0]));

	var currentUrl = window.location.href;
	if((startStrDate = currentUrl.indexOf("date")) >= 0)
	{
		var dateToReplace = currentUrl.substring( 
							startStrDate + 4+1, 
							startStrDate +4+1 +4+1+2+1+2 
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
	$("#chooseDate").calendar({
			onSelect: updateDate,
			dateFormat: 'DMY-',
			firstDay: 1,
			minDate: new Date(2007, 1 - 1, 1),
			maxDate: new Date(2007, 12 - 1, 31),
			changeFirstDay: false,
			prevText: "",
			nextText: "",
			currentText: "",
			customDate: isDateSelected
			});
	}
);
</script>


{/literal}


<div class="calendarInline" id="inlineFrom"></div>

<br>
					
<h1>Piwik reports</h1>
<span id="chooseDate"></span>
<p>- Date = {$date}</p>
<p>Url = {url}</p>
<p>User logged = {$userLogin}</p>
{include file="UserSettings/templates/period_select.tpl"}
{include file="UserSettings/templates/sites_select.tpl"}


<h2>Actions</h2>
{$dataTableActions} 
{*
<h2>Downloads</h2>
{$dataTableDownloads} 
<h2>Outlinks</h2>
{$dataTableOutlinks}

<h2>Visits summary</h2>
<p>{$nbUniqVisitors} unique visitors</p>
<p>{$nbVisits} visits</p>
<p>{$nbActions} actions (page views)</p>
<p>{$sumVisitLength|sumtime} total time spent by the visitors</p>
<p>{$maxActions} max actions</p>
<p>{$bounceCount} visitors have bounced (left the site directly)</p>

<h2>User Country</h2>

<h3>Country</h3>
{$dataTableCountry}

<h3>Continent</h3>
{$dataTableContinent}

<h2>Provider</h2>
{$dataTableProvider}

<h2>Referers</h2>

<h3>Referer Type</h3>
{$dataTableRefererType}

<h3>Search Engines</h3>
<p>{$numberDistinctSearchEngines} distinct search engines</p>
{$dataTableSearchEngines}

<h3>Keywords</h3>
<p>{$numberDistinctKeywords} distinct keywords</p>
{$dataTableKeywords}


<h3>Websites</h3>
<p>{$numberDistinctWebsites} distinct websites</p>
<p>{$numberDistinctWebsitesUrls} distinct websites URLs</p>
{$dataTableWebsites}

<h3>Partners</h3>
<p>{$numberDistinctPartners} distinct partners</p>
<p>{$numberDistinctPartnersUrls} distinct partners URLs</p>
{$dataTablePartners}

<h3>Campaigns</h3>
<p>{$numberDistinctCampaigns} distinct campaigns</p>
{$dataTableCampaigns}

<h2>User Settings</h2>
<h3>Configurations</h3>
{$dataTableConfiguration}

<h3>Resolutions</h3>
{$dataTableResolution}

<h3>Operating systems</h3>
{$dataTableOS}

<h3>Browsers</h3>
{$dataTableBrowser}

<h3>Browser families</h3>
{$dataTableBrowserType}

<h3>Wide Screen</h3>
{$dataTableWideScreen}

<h3>Plugins</h3>
{$dataTablePlugin}


<h2>Frequency</h2>
<p>{$nbVisitsReturning} returning visits</p>
<p>{$nbActionsReturning} actions by the returning visits</p>
<p>{$maxActionsReturning} maximum actions by a returning visit</p>
<p>{$sumVisitLengthReturning|sumtime} total time spent by returning visits</p>
<p>{$bounceCountReturning} times that a returning visit has bounced</p>

<h2>Visit Time</h2>
<h3>Visit per local time</h3>
{$dataTableVisitInformationPerLocalTime}
<h3>Visit per server time</h3>
{$dataTableVisitInformationPerServerTime}
		
<h2>Visitor Interest</h2>
<h3>Visits per visit duration</h3>
{$dataTableNumberOfVisitsPerVisitDuration}
<h3>Visits per number of pages</h3>
{$dataTableNumberOfVisitsPerPage}
*}

{$totalTimeGeneration} seconds to generate the page