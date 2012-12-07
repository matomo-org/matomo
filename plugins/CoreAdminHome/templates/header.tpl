<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{if !$isCustomLogo}Piwik &rsaquo; {/if}{'CoreAdminHome_Administration'|translate}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" content="Piwik - Open Source Web Analytics" />
<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" />

{loadJavascriptTranslations plugins='CoreAdminHome'}

{include file="CoreHome/templates/js_global_variables.tpl"}
{include file="CoreHome/templates/js_css_includes.tpl"}
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="themes/default/ieonly.css" />
<![endif]-->
{include file="CoreHome/templates/iframe_buster_header.tpl"}
</head>
<body>

{include file="CoreHome/templates/iframe_buster_body.tpl"}
<div id="root">
{if !isset($showTopMenu) || $showTopMenu}
{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreHome/templates/top_bar.tpl"}
{/if}

<div id="header">
{include file="CoreHome/templates/logo.tpl"}
{if $showPeriodSelection}{include file="CoreHome/templates/period_select.tpl"}{/if}
{include file="CoreHome/templates/js_disabled_notice.tpl"}
</div>

{if !isset($showMenu) || $showMenu}
	{include file="CoreAdminHome/templates/menu.tpl"}
{/if}

{ajaxRequestErrorDiv}

<div id="content" class="admin">

{include file="CoreHome/templates/header_message.tpl"}

{if !empty($configFileNotWritable)}
<div class="ajaxSuccess" style="display:inline-block">
	{'General_ConfigFileIsNotWritable'|translate:"(config/config.ini.php)":"<br/>"}
</div>
{elseif preg_match('/updated=[1-9]/', $url)}
<div class="ajaxSuccess" style="display:inline-block">
	{'General_YourChangesHaveBeenSaved'|translate}
</div>
{/if}

<div class="ui-confirm" id="alert">
    <h2></h2>
    <input role="no" type="button" value="{'General_Ok'|translate}" />
</div>

{* untrusted host warning *}
{if isset($isValidHost) && isset($invalidHostMessage) && !$isValidHost}
<div class="ajaxSuccess">
	<a style="float:right" href="http://piwik.org/faq/troubleshooting/#faq_171" target="_blank"><img src="themes/default/images/help_grey.png" /></a>
	<strong>{'General_Warning'|translate}:&nbsp;</strong>{$invalidHostMessage}
</div>
{/if}

{* missing plugins warning *}
{if $isSuperUser && !empty($missingPluginsWarning)}
<div class="ajaxSuccess">
	<strong>{'General_Warning'|translate}:&nbsp;</strong>{$missingPluginsWarning}
</div>
{/if}

{* old GeoIP plugin warning *}
{if $isSuperUser && !empty($usingOldGeoIPPlugin)}
<div class="ajaxSuccess">
	<strong>{'General_Warning'|translate}:&nbsp;</strong>{'UserCountry_OldGeoIPWarning'|translate:'<a href="index.php?module=CorePluginsAdmin&action=index&idSite=1&period=day&date=yesterday">':'</a>':'<a href="index.php?module=UserCountry&action=adminIndex&idSite=1&period=day&date=yesterday#location-providers">':'</a>':'<a href="http://piwik.org/faq/how-to/#faq_167">':'</a>':'<a href="http://piwik.org/faq/how-to/#faq_59">':'</a>'}
</div>
{/if}
