<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Piwik - Your Web Analytics Reports</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{loadJavascriptTranslations modules='CoreHome'}

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

<script type="text/javascript" src="plugins/CoreHome/templates/datatable.js"></script>
<script type="text/javascript" src="plugins/CoreHome/templates/calendar.js"></script>
<script type="text/javascript" src="plugins/CoreHome/templates/date.js"></script>

<script type="text/javascript" src="libs/jquery/jquery.blockUI.js"></script>
<script type="text/javascript" src="libs/jquery/ui.mouse.js"></script>
<script type="text/javascript" src="libs/jquery/ui.sortable_modif.js"></script>

<link rel="stylesheet" href="plugins/CoreHome/templates/datatable.css" />
<link rel="stylesheet" href="plugins/Dashboard/templates/dashboard.css" />
<style type="text/css">@import url(libs/jquery/jquery-calendar.css);</style>

<script type="text/javascript" src="libs/jquery/superfish_modified.js"></script>
<script type="text/javascript" src="plugins/CoreHome/templates/menu.js"></script>
<link rel="stylesheet" type="text/css" href="plugins/CoreHome/templates/menu.css" media="screen" />
<link rel="stylesheet" type="text/css" href="plugins/CoreHome/templates/style.css" media="screen" />

<script type="text/javascript" src="libs/jquery/thickbox.js"></script>
<link rel="stylesheet" href="libs/jquery/thickbox.css" />
</head>
<body>

{include file="CoreHome/templates/top_bar.tpl"}

<br clear="all" />

<div id="header">
{include file="CoreHome/templates/header.tpl"}
</div>

<noscript>
<span id="javascriptDisable">
{'CoreHome_JavascriptDisabled'|translate:'<a href="">':'</a>'}
</span>
</noscript>

<br />
{include file="CoreHome/templates/menu.tpl"}

<div style='clear:both'></div>

<div id="loadingPiwik" {if $basicHtmlView}style="display:none"{/if}><img src="themes/default/images/loading-blue.gif" alt="" /> {'General_LoadingData'|translate}</div>
<div id="loadingError">{'General_ErrorRequest'|translate}</div>

<div id='content'>
{if $content}{$content}{/if}
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}
</body>
</html>
