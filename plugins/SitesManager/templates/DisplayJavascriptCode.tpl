{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='SitesManager'}
{include file="CoreAdminHome/templates/menu.tpl"}

{literal}
<style>
code {
	background-color:#F0F7FF;
	border-color:#00008B;
	border-style:dashed dashed dashed solid;
	border-width:1px 1px 1px 5px;
	direction:ltr;
	display:block;
	font-size:80%;
	margin:2px 2px 20px;
	padding:4px;
	text-align:left;
	font-family: "Courier New" Courier monospace;
}
</style>
{/literal}

<p>{'SitesManager_JsTrackingTagHelp'|translate}:</p>

<code>{$jsTag}</code>


<ul style="list-style-type:disc; padding-left:20px">
{include file=SitesManager/templates/JavascriptTagHelp.tpl}
</ul>
