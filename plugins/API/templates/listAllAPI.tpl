{assign var=showSitesSelection value=true}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

{literal}
<style>
#token_auth { 
	background-color:#E8FFE9; 
	border-color:#00CC3A; 
	border-style: solid;
	border-width: 1px;
	margin: 0pt 0pt 16px 8px;
	padding: 12px;			
	line-height:4em;
}
.example, .example A {
	color:#9E9E9E;
}
</style>
{/literal}

{'API_QuickDocumentation'|translate:$token_auth}
<span id='token_auth'>token_auth = <b>{$token_auth}</b></span>
<p><i>{'API_LoadedAPIs'|translate:$countLoadedAPI}</i></p>
{$list_api_methods_with_links}