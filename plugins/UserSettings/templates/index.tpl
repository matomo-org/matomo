
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.dimensions.js"></script>
<script type="text/javascript" src="libs/jquery/tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="libs/jquery/truncate/jquery.truncate.js"></script>
<script type="text/javascript" src="plugins/UserSettings/templates/datatable.js"></script>

<link rel="stylesheet" href="libs/jquery/tooltip/jquery.tooltip.css">
<link rel="stylesheet" href="plugins/UserSettings/templates/datatable.css">
{literal}
<style>
tr.level0 td.label{
	padding-left:+1.5em;
}
tr.level1 td.label{
	padding-left:+2.5em;
}
tr.level2 td.label{
	padding-left:+3.5em;
}
tr.level3 td.label{
	padding-left:+4.5em;
}
tr.level4 td.label{
	padding-left:+5em;
}
tr td.label img.plusMinus {
	margin-left:-1em;
	position:absolute;
}
</style>
{/literal}
<h1>Piwik reports</h1>
<p>- Date = {$date}</p>
<p>- Period = {$period}</p>
<p>- IdSite = {$idSite}</p>

<h2>Actions</h2>
{$dataTableActions} 
<h2>Downloads</h2>
{$dataTableDownloads} 
<h2>Outlinks</h2>
{$dataTableOutlinks}

{*
<h2>Visits summary</h2>
<p>{$nbUniqVisitors} unique visitors</p>
<p>{$nbVisits} visits</p>
<p>{$nbActions} actions (page views)</p>
<p>{$sumVisitLength|sumtime} total time spent by the visitors</p>
<p>{$maxActions} max actions</p>
<p>{$bounceCount} visitors have bounced (left the site directly)</p>

*}
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

{*

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
	