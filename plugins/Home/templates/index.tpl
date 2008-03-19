<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">
<html>
<head>
</head>
<body>


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
<script type="text/javascript" src="libs/jquery/jquery.scrollTo.js"></script>
<script type="text/javascript" src="libs/jquery/jquery-calendar.js"></script>
<script type="text/javascript" src="libs/swfobject/swfobject.js"></script>

<script type="text/javascript" src="plugins/Home/templates/datatable.js"></script>
<script type="text/javascript" src="plugins/Home/templates/calendar.js"></script>

<script type="text/javascript" src="plugins/Home/templates/date.js"></script>

<script type="text/javascript" src="libs/jquery/jquery.blockUI.js"></script>
<script type="text/javascript" src="libs/jquery/ui.mouse.js"></script>
<script type="text/javascript" src="libs/jquery/ui.sortable_modif.js"></script>



<link rel="stylesheet" href="plugins/Home/templates/datatable.css">
<link rel="stylesheet" href="plugins/Dashboard/templates/dashboard.css">


<style type="text/css">@import url(libs/jquery/jquery-calendar.css);</style>


<script type="text/javascript" src="libs/jquery/superfish_modified.js"></script>
<script type="text/javascript" src="plugins/Home/templates/menu.js"></script>
<link rel="stylesheet" type="text/css" href="plugins/Home/templates/menu.css" media="screen">

{literal}

<style>
/* reset style */
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, font, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td {
	margin: 0;
	padding: 0;
	border: 0;
	outline: 0;
	font-size: 100%;
	vertical-align: baseline;
	background: transparent;
}
body {
	line-height: 1.33;
}
ol, ul {
	list-style: none;
}
blockquote, q {
	quotes: none;
}

/* remember to define focus styles! */
:focus {
	outline: 0;
}

/* remember to highlight inserts somehow! */
ins {
	text-decoration: none;
}
del {
	text-decoration: line-through;
}


/* start piwik styles */
body {
	font-family: Georgia,"Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	font-size:1em;
}

#h1, #h1 a {
	color: #136F8B;
	font-size: 45px;
	font-weight: lighter;
	text-decoration : none;
	margin:5px;
}

#subh1 {
	color: #879dbd;
	font-size: 25px;
	font-weight: lighter;
}
h1 {
	font-size:2em;
	color:#0F1B2E;
	padding-bottom:1em;
}
h2 {
	font-size:1.6em;
	color:#1D3256;	
	padding-bottom:0.5em;
	
}
h3 {
	font-size:1.3em;
	margin-top:2em;
	color:#1D3256;
}
a {
	color:#0F1B2E;
}
p { 
	padding-bottom:1em;
	margin-right:1em;
}

/* Content */
#content {
	margin-left:10px;
}
/* 2 columns reports */
#leftcolumn {
	float:left;
	width:45%;
	padding-left:10px;
	padding-right:20px;
}
#rightcolumn {
	float: right;
	width:45%;
	padding-left:20px;
}



/* Calendar */
#calendar {
	position: relative;
	margin-left:350px;
}
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

/* style for the date picking */
#periodString {
	margin-left:350px;
}

#periodString #date, #periodString #periods, 
#periodString #date a, #periodString #periods a  {
	color:#520202;
	font-size:15pt;
}
#otherPeriods a{
	 text-decoration:none;
}
#otherPeriods a:hover{
	 text-decoration:underline;
}
#currentPeriod {
	border-bottom:1px dotted #520202;
}
.hoverPeriod {
	cursor: pointer;
	font-weight:bold;
	border-bottom:1px solid #520202;
}


/* OTHER */
#loadingError {
	font-weight:bold;
	font-size: 1.1em;
	color:#F88D22;
	padding:0.5em;
	margin-left:30%;
	display:none;
}
#loadingPiwik {
	font-weight:bold;
	font-size: 1.1em;
	color:#193B6C;
	padding:0.5em;
	margin-left:30%;
}

#miscLinks{
	padding-right:20px;
}

#periodSelection, #periodSelection a {
	color:#8D92AA;
}

#messageToUsers, #messageToUsers a {
	color:red;
	font-size:0.9em;
	text-decoration : none;
	width:100%;
}

.sparkline {
	vertical-align: middle;
	padding-right:10px;
}


#stuff {
	position: absolute;
	margin-left:70%;
	margin-top:10px;
	font-size:0.9em;
	width:20%;
}


/* top right bar */
#loggued {
	float:right;
	text-align:right;
	margin-right: 20px;
	padding-bottom:5px;
	padding-left:5px;
	border-bottom:1px dotted #E2E3FE;
	border-left:1px dotted #E2E3FE;
}
#loggued form {
	display:inline;
}


#javascriptDisable, #javascriptDisable a {
	font-weight:bold;
	color:#F88D22;
}
</style>
{/literal}


<span id="loggued">
<form action="{url idSite=null}" method="GET" id="siteSelection">
<small>
	<strong>{$userLogin}</strong>
	| 
<span id="sitesSelection">
{hiddenurl idSite=null}
Site <select name="idSite" onchange='javascript:this.form.submit()'>
	<optgroup label="Sites">
	   {foreach from=$sites item=info}
	   		<option label="{$info.name}" value="{$info.idsite}" {if $idSite==$info.idsite} selected="selected"{/if}>{$info.name}</option>
	   {/foreach}
	</optgroup>
</select>
</span> | {if $userLogin=='anonymous'}<a href='?module=Login'>Login</a>{else}<a href='?module=Login&action=logout'>Logout</a>{/if}</a>
</small>
</form>
</span>

<span id="h1"><a href='http://piwik.org'>Piwik</a> </span><span id="subh1"> # open source web analytics</span><br>
<br>
<div id="stuff">
	<div>
		<span id="messageToUsers"><a href='http://piwik.org'>Piwik</a> is still alpha. You can <a href="mailto:hello@piwik.org?subject=Feedback piwik"><u>send us</u></a> your feedback.</span> 
		{include file="Home/templates/links_misc_modules.tpl"}
	</div>
</div>


<noscript>
<span id="javascriptDisable">
JavaScript must be enabled in order for you to use Piwik in standard view.<br> 
However, it seems JavaScript is either disabled or not supported by your browser.<br> 
To use standard view, enable JavaScript by changing your browser options, then <a href=''>try again</a>.<br>
</span>
</noscript>
{include file="Home/templates/period_select.tpl"}

<br><br>
{include file="Home/templates/menu.tpl"}

<div style='clear:both'></div>

<div id="loadingPiwik" {if $basicHtmlView}style="display:none"{/if}><img src="themes/default/images/loading-blue.gif"> Loading data...</div>
<div id="loadingError">Oops&hellip; problem during the request, please try again.</div>
<div id='content'>
{if $content}{$content}{/if}
</div>

{if ereg('http://127.0.0.1|http://localhost|http://piwik.org', $url)}
{literal}
<!-- Piwik -->
<a href="http://piwik.org" title="Web analytics" onclick="window.open(this.href);return(false);">
<script language="javascript" src="piwik.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
piwik_action_name = '';
piwik_idsite = 1;
piwik_url = 'piwik.php';
piwik_vars = { 'video_play':1, 'video_finished':0 };
piwik_log(piwik_action_name, piwik_idsite, piwik_url, piwik_vars);
//-->
</script><object>
<noscript><p>Web analytics <img src="piwik.php" style="border:0" alt="piwik"/></p>
</noscript></object></a>
<!-- /Piwik -->
{/literal}
{/if}
