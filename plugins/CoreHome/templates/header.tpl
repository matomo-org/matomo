<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$siteName} - {if !$isCustomLogo}Piwik &rsaquo; {/if} {'CoreHome_WebAnalyticsReports'|translate}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" content="Piwik - Open Source Web Analytics" />
<meta name="description" content="Web Analytics report for '{$siteName}' - Piwik" />
<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" /> 
{loadJavascriptTranslations plugins='CoreHome Annotations'}
{include file="CoreHome/templates/js_global_variables.tpl"}
<!--[if lt IE 9]>
<script language="javascript" type="text/javascript" src="libs/jqplot/excanvas.min.js"></script>
<![endif]-->
{include file="CoreHome/templates/js_css_includes.tpl"}
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="themes/default/ieonly.css" />
<![endif]-->
{include file="CoreHome/templates/iframe_buster_header.tpl"}
{if isset($addToHead)}{$addToHead}{/if}
</head>
<body>
{include file="CoreHome/templates/iframe_buster_body.tpl"}
<div id="root">{if !isset($showTopMenu) || $showTopMenu}
{include file="CoreHome/templates/top_bar.tpl"}
{/if}
{include file="CoreHome/templates/top_screen.tpl"}

<div class="ui-confirm" id="alert">
    <h2></h2>
    <input role="yes" type="button" value="{'General_Ok'|translate}" />
</div>

{* untrusted host warning *}
{if isset($isValidHost) && isset($invalidHostMessage) && !$isValidHost}
	<div class="ajaxSuccess" style='clear:both;width:800px'>
		<a style="float:right" href="http://piwik.org/faq/troubleshooting/#faq_171" target="_blank"><img src="themes/default/images/help_grey.png" /></a>
		<strong>{'General_Warning'|translate}:&nbsp;</strong>{$invalidHostMessage}
	</div>
{/if}

