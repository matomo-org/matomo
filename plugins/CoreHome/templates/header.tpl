<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{if !$isCustomLogo}Piwik &rsaquo; {/if} {'CoreHome_WebAnalyticsReports'|translate} - {$siteName}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" content="Piwik - Open Source Web Analytics" />
<meta name="description" content="Web Analytics report for '{$siteName}' - Piwik" />
<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" /> 
{loadJavascriptTranslations plugins='CoreHome'}
{include file="CoreHome/templates/js_global_variables.tpl"}
<!--[if lt IE 9]>
<script language="javascript" type="text/javascript" src="libs/jqplot/excanvas.min.js"></script>
<![endif]-->
{include file="CoreHome/templates/js_css_includes.tpl"}
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="themes/default/ieonly.css" />
<![endif]-->
{include file="CoreHome/templates/iframe_buster_header.tpl"}
</head>
<body>
{include file="CoreHome/templates/iframe_buster_body.tpl"}
<div id="root">{if !isset($showTopMenu) || $showTopMenu}
{include file="CoreHome/templates/top_bar.tpl"}
{/if}
{include file="CoreHome/templates/top_screen.tpl"}

<div class="ui-confirm" id="alert">
    <h2></h2>
    <input id="yes" type="button" value="{'General_Ok'|translate}" />
</div>