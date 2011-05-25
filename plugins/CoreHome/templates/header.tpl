<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Piwik &rsaquo; {'CoreHome_WebAnalyticsReports'|translate}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" content="Piwik - Open Source Web Analytics" />
<meta name="description" content="Web Analytics report for '{$siteName|escape}' - Piwik" />
<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" /> 
{loadJavascriptTranslations plugins='CoreHome'}
{include file="CoreHome/templates/js_global_variables.tpl"}
{include file="CoreHome/templates/js_css_includes.tpl"}
<!--[if lt IE 9]>
<link rel="stylesheet" type="text/css" href="themes/default/ieonly.css" />
<script language="javascript" type="text/javascript" src="libs/jqplot/excanvas.min.js"></script>
<![endif]-->
</head>
<body>
<div id="root">{if !isset($showTopMenu) || $showTopMenu}
{include file="CoreHome/templates/top_bar.tpl"}
{/if}
{include file="CoreHome/templates/top_screen.tpl"}

<div class="ui-confirm" id="alert">
    <h2></h2>
    <input id="yes" type="button" value="{'General_Ok'|translate}" />
</div>