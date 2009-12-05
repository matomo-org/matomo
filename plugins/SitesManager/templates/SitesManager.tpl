{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='SitesManager'}
{include file="CoreAdminHome/templates/menu.tpl"}

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
<p>{'SitesManager_MainDescription'|translate}</p>

<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none"><div id="loadingPiwik"><img src="themes/default/images/loading-blue.gif" alt="" /> {'General_LoadingData'|translate} </div></div>

{if $adminSites|@count == 0}
	{'SitesManager_NoWebsites'|translate}
{else}
	<table class="admin" id="editSites" border=1 cellpadding="10">
		<thead>
			<tr>
			<th>{'SitesManager_Id'|translate}</th>
			<th>{'SitesManager_Name'|translate}</th>
			<th>{'SitesManager_Urls'|translate}</th>
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
				<td><img src='plugins/UsersManager/images/edit.png' class="editSite" id="row{$i}" href='#' alt="" /></td>
				<td><img src='plugins/UsersManager/images/remove.png' class="deleteSite" id="row{$i}" value="{'General_Delete'|translate}" alt="" /></td>
				<td><a href='{url action=displayJavascriptCode idsite=$site.idsite}'>{'SitesManager_ShowTrackingTag'|translate}</a></td>
			</tr>
			{/foreach}
			
		</tbody>
	</table>
	{if $isSuperUser}	
	<div class="addRowSite"><a href="#"><img src='plugins/UsersManager/images/add.png' alt="" /> {'SitesManager_AddSite'|translate}</a></div>
	<div class="ui-widget">
		<div class="ui-state-highlight ui-corner-all" style="margin-top:20px; padding:0 .7em;">
			<p style="font-size:62.5%;"><span class="ui-icon ui-icon-info" style="float:left;margin-right:.3em;"></span>
			{'SitesManager_AliasUrlHelp'|translate}</p>
		</div>
	</div>
	{/if}
{/if}

{include file="CoreAdminHome/templates/footer.tpl"}
