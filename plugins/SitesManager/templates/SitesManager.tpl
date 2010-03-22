{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='SitesManager'}
{include file="CoreAdminHome/templates/menu.tpl"}

<script type="text/javascript">
{capture assign=excludedIpHelpPlain}{'SitesManager_HelpExcludedIps'|translate:"1.2.3.*":"1.2.*.*"}<br/><br/> {'SitesManager_YourCurrentIpAddressIs'|translate:"<i>$currentIpAddress</i>"}{/capture}
{assign var=excludedIpHelp value=$excludedIpHelpPlain|inlineHelp}
var excludedIpHelp = '{$excludedIpHelp|escape:javascript}';
var aliasUrlsHelp = '{'SitesManager_AliasUrlHelp'|translate|inlineHelp|escape:javascript}';
</script>
<script type="text/javascript" src="plugins/SitesManager/templates/SitesManager.js"></script>

{literal}
<style>
.addRowSite:hover, .editableSite:hover, .addsite:hover, .cancel:hover, .deleteSite:hover, .editSite:hover, .updateSite:hover{
	cursor: pointer;
}
.addRowSite a {
	text-decoration: none;
}
.addRowSite {
	padding:1em;
	font-color:#3A477B;
	padding:1em;
	font-weight:bold;
}
#editSites {
	valign: top;
}
</style>
{/literal}

<h2>{'SitesManager_WebsitesManagement'|translate}</h2>
<p>{'SitesManager_MainDescription'|translate}
{if $isSuperUser}
<br/>{'SitesManager_SuperUserCanExcludeIpsOnAllWebsites'|translate:"<a href='#globalIpExclusion'>":"</a>"}.
{/if}
</p>
{ajaxErrorDiv}
{ajaxLoadingDiv}

{if $adminSites|@count == 0}
	{'SitesManager_NoWebsites'|translate}
{else}
	<table class="admin" id="editSites" border=1 cellpadding="10">
		<thead>
			<tr>
			<th>{'SitesManager_Id'|translate}</th>
			<th>{'SitesManager_Name'|translate}</th>
			<th>{'SitesManager_Urls'|translate}</th>
			<th>{'SitesManager_ExcludedIps'|translate}</th>
			<th> </th>
			<th> </th>
			<th> {'SitesManager_JsTrackingTag'|translate} </th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$adminSites key=i item=site}
			<tr id="row{$i}">
				<td id="idSite">{$site.idsite}</td>
				<td id="siteName" class="editableSite">{$site.name}</td>
				<td id="urls" class="editableSite">{foreach from=$site.alias_urls item=url}{$url}<br />{/foreach}</td>       
				<td id="excludedIps" class="editableSite">{foreach from=$site.excluded_ips item=ip}{$ip}<br />{/foreach}</td>       
				<td><img src='plugins/UsersManager/images/edit.png' class="editSite" id="row{$i}" href='#' title="{'General_Edit'|translate}" /></td>
				<td><img src='plugins/UsersManager/images/remove.png' class="deleteSite" id="row{$i}" title="{'General_Delete'|translate}" value="{'General_Delete'|translate}" /></td>
				<td><a href='{url action=displayJavascriptCode idsite=$site.idsite}'>{'SitesManager_ShowTrackingTag'|translate}</a></td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	{if $isSuperUser}	
	<div class="addRowSite"><a href=""><img src='plugins/UsersManager/images/add.png' alt="" /> {'SitesManager_AddSite'|translate}</a></div>
	{/if}
{/if}

{if $isSuperUser}	
	<a name='globalIpExclusion'></a><h2>{'SitesManager_GlobalListExcludedIps'|translate}</h2>
	<p>{'SitesManager_ListOfIpsToBeExcludedOnAllWebsites'|translate} </p>
	{ajaxErrorDiv id=ajaxErrorExcludedIps}
	{ajaxLoadingDiv id=ajaxLoadingExcludedIps}
	<textarea cols="30" rows="3" id="globalExcludedIps">{$globalExcludedIps}
	</textarea><br/>
	{$excludedIpHelp}
	<input type="hidden" name="token_auth" value="{$token_auth}" />
	<p><input type="submit" class="submit" id='globalExcludedIpsSubmit' value="{'General_Save'|translate}" /></p>
{/if}

<br /><br /><br /><br />
{include file="CoreAdminHome/templates/footer.tpl"}
