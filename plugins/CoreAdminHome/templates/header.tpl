<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Piwik &rsaquo; {'CoreAdminHome_Administration'|translate}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="generator" content="Piwik {$piwik_version}" />
<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" />

{loadJavascriptTranslations plugins='CoreAdminHome'}

{include file="CoreHome/templates/js_global_variables.tpl"}

{includeAssets type="css"}
{includeAssets type="js"}

</head>
<body>
<div id="root">
{if !isset($showTopMenu) || $showTopMenu}
{include file="CoreHome/templates/top_bar.tpl"}
{/if}

<div id="header">
{include file="CoreHome/templates/logo.tpl"}
{if $showPeriodSelection}{include file="CoreHome/templates/period_select.tpl"}{/if}
{include file="CoreHome/templates/js_disabled_notice.tpl"}
</div>

{ajaxRequestErrorDiv}
{if !isset($showMenu) || $showMenu}
	{include file="CoreAdminHome/templates/menu.tpl"}
{/if}


<div id="content" class="admin">

{include file="CoreHome/templates/header_message.tpl"}

{if !empty($configFileNotWritable)}
<div class="ajaxSuccess" style="display:normal">
	{'General_ConfigFileIsNotWritable'|translate:"(config/config.ini.php)":"<br/>"}
</div>
{elseif strpos($url, 'updated=1')}	
<div class="ajaxSuccess" style="display:normal">
	{'General_YourChangesHaveBeenSaved'|translate}
</div>
{/if}

