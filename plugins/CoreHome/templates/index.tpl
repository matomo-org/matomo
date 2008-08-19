<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Piwik &rsaquo; Web Analytics Reports</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{loadJavascriptTranslations plugins='CoreHome'}
{include file="CoreHome/templates/js_css_includes.tpl"}
</head>

<body>
{assign var=showSitesSelection value=true}
{include file="CoreHome/templates/top_bar.tpl"}
{include file="CoreHome/templates/header.tpl"}
{include file="CoreHome/templates/menu.tpl"}
<div style='clear:both'></div>
{include file="CoreHome/templates/loading.tpl"}

<div id='content'>
{if $content}{$content}{/if}
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}
</body>
</html>
