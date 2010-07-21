{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='SitesManager'}

<h2>Tracking Code</h2>
{include file="SitesManager/templates/DisplayJavascriptCode.tpl"}