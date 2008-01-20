{literal}
<style>

</style>
{/literal}

<script type="text/javascript">
var period = "{$period}";
var currentDateStr = "{$date}";
var minDateYear = {$minDateYear};
var minDateMonth = {$minDateMonth};
var minDateDay = {$minDateDay};
</script>

<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.dimensions.js"></script>
<script type="text/javascript" src="libs/jquery/tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="libs/jquery/truncate/jquery.truncate.js"></script>
<script type="text/javascript" src="libs/jquery/jquery-calendar.js"></script>
<script type="text/javascript" src="libs/swfobject/swfobject.js"></script>

<script type="text/javascript" src="plugins/Home/templates/datatable.js"></script>
<script type="text/javascript" src="plugins/Home/templates/calendar.js"></script>
<script type="text/javascript" src="plugins/Home/templates/mainmenu.js"></script>

<link rel="stylesheet" href="libs/jquery/tooltip/jquery.tooltip.css">
<link rel="stylesheet" href="plugins/Home/templates/datatable.css">


<style type="text/css">@import url(libs/jquery/jquery-calendar.css);</style>
{literal}

<style>
* {
	font-family: Georgia,"Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	font-size:1em;
}
h1 {
	font-size:2em;
	color:#0F1B2E;
}
h2 {
	font-size:1.6em;
	margin-top:2em;
	color:#1D3256;
}
h3 {
	font-size:1.3em;
	margin-top:2em;
	color:#1D3256;
}
/* Actions table */
/* levels higher than 4 have a default padding left */
tr.subActionsDataTable td.label{
	padding-left:7em;
}
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

/* Calendar */
.calendar td.dateToday, .calendar td.dateToday a{
	font-weight:bold;

}
.calendar td.dateUsedStats, .calendar td.dateUsedStats a{
	color:#2E85FF;
	border-color:#2E85FF ;
}

.calendar td.calendar_unselectable {
	color:#F2F7FF;
}

#calendar{
	float:left;
	margin:10px;
}

#miscLinks{
	font-size:small;
	padding-right:20px;
}

#sitesSelection {
	 
}
#periodSelection,#periodSelection a {
	color:#8D92AA;
}

#generatedMenu span {
	text-decoration:underline;
	color:blue;
	cursor:pointer;
}

#generatedMenu span:hover {
	background:#DDEAF4 none repeat scroll 0%;
	color:#333333;
}

#generatedMenu span {
	border-bottom:medium none;
	color:#000000;
	font-size:14px;
	font-weight:normal;
	margin:0pt;
	padding:3px 5px;
	line-height:1.8em;
}

#generatedMenu span:hover{
	color:#006699;
}

#generatedMenu span	 {
	border-bottom:1px solid #6699CC;
	color:#00019B;
	text-decoration:none;
}

.section {
	display:none;
}

#stuff {
	position:relative;
	float:right;
	margin-right:10%;
	margin-top:10px;
	font-size:0.9em;
}
#h1, #h1 a {
	color: #006;
	font-size: 45px;
	font-weight: lighter;
	text-decoration : none;
}

#subh1 {
	color: #879DBD;
	font-size: 25px;
	font-weight: lighter;
}

#messageToUsers, #messageToUsers a {
	color:red;
	font-size:0.9em;
	text-decoration : none;
	width:100%;
}

.formEmbedCode, .formEmbedCode input, .formEmbedCode a {
	font-size: 11px;
	text-decoration : none;
}
.formEmbedCode input {
	background-color: #FBFDFF;
	border: 1px solid #ECECEC; 
}
.sparkline {
	vertical-align: middle;
	padding-right:10px;
}
</style>
{/literal}

{literal}
<script type="text/javascript">
	
function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}
function reload()
{
  tmp = findSWF("getLastVisitsGraphChart_swf");
  x = tmp.reload();
}

</script>
{/literal}


<span id="h1"><a href='http://piwik.org'>Piwik</a> </span><span id="subh1"> # open source web analytics</span><br>
<br>
<div id="stuff">
	<div id="calendar"></div>
	<div>
		<p> Date = {$date}</p>
		<p>User logged = {$userLogin}</p>
		{include file="Home/templates/period_select.tpl"}<br><br>
		{include file="Home/templates/sites_select.tpl"}<br>
		
	<div id="messageToUsers"><a href='http://piwik.org'>Piwik</a> is still alpha. 
				<br>We are currently working hard on a new shiny User Interface.
				<br>Please <a href="mailto:hello@piwik.org?subject=Feedback piwik"><u>send us</u></a> your feedback.
				<br>
				</div> 
		{include file="Home/templates/links_misc_modules.tpl"}<br>
	</div>
</div>

<span id="loadingPiwik"><img src="themes/default/loading-blue.gif"> Loading data...</span>

<span id="generatedMenu"></span>

<div class="section" id="Visits_summary">
	<h3>Visits</h3>
	{$graphLastVisits}
	
	<h3>Report</h3>
	
	<p><a href="javascript:reload();">test</a>
	<p><img class="sparkline" src="{$urlSparklineNbVisits}" /> <strong>{$nbVisits} </strong>visits</p>
	<p><img class="sparkline" src="{$urlSparklineNbUniqVisitors}" /> <strong>{$nbUniqVisitors}</strong> unique visitors</p>
	<p><img class="sparkline" src="{$urlSparklineNbActions}" /> <strong>{$nbActions}</strong> actions (page views)</p>
	<p><img class="sparkline" src="{$urlSparklineSumVisitLength}" /> <strong>{$sumVisitLength|sumtime}</strong> total time spent by the visitors</p>
	<p><img class="sparkline" src="{$urlSparklineMaxActions}" /> <strong>{$maxActions}</strong> max actions</p>
	<p><img class="sparkline" src="{$urlSparklineBounceCount}" /> <strong>{$bounceCount} </strong>visitors have bounced (left the site directly)</p>
	
	
	<br><br><br><hr width="300px" align="left">
	<p><small>{$totalTimeGeneration} seconds {if $totalNumberOfQueries != 0}/ {$totalNumberOfQueries}  queries{/if} to generate the page</p>
</div>

<div class="section" id="User_Country">
	<h3>Country</h3>
	{$dataTableCountry}
	<h3>Continent</h3>
	{$dataTableContinent}
</div>

<div class="section" id="Provider">
	{$dataTableProvider}
</div>

<div class="section" id="Referers">

	<h3>Number of distinct keywords</h3>
	{$graphLastDistinctKeywords}
	
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
</div>

<div class="section" id="Actions">
	<h3>Actions</h3>
	{$dataTableActions} 
	<h3>Downloads</h3>
	{$dataTableDownloads} 
	<h3>Outlinks</h3>
	{$dataTableOutlinks}
</div>

<div class="section" id="User_Settings">
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
</div>


<div class="section" id="Frequency">
	<p>{$nbVisitsReturning} returning visits</p>
	<p>{$nbActionsReturning} actions by the returning visits</p>
	<p>{$maxActionsReturning} maximum actions by a returning visit</p>
	<p>{$sumVisitLengthReturning|sumtime} total time spent by returning visits</p>
	<p>{$bounceCountReturning} times that a returning visit has bounced</p>
</div>

<div class="section" id="Visit_Time">
	<h3>Visit per local time</h3>
	{$dataTableVisitInformationPerLocalTime}
	<h3>Visit per server time</h3>
	{$dataTableVisitInformationPerServerTime}
</div>

<div class="section" id="Visitor_Interest">
	<h3>Visits per visit duration</h3>
	{$dataTableNumberOfVisitsPerVisitDuration}
	<h3>Visits per number of pages</h3>
	{$dataTableNumberOfVisitsPerPage}
</div>

