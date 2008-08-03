{loadJavascriptTranslations plugins='SitesManager'}
{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{include file="CoreAdminHome/templates/menu.tpl"}

<script type="text/javascript" src="plugins/SitesManager/templates/SitesManager.js"></script>
{literal}
<style>
.addRowSite:hover, .editableSite:hover, .addsite:hover, .cancel:hover, .deleteSite:hover, .editSite:hover, .updateSite:hover{
	cursor: pointer;
}

.addRowSite {
	font-color:#3A477B;
	padding:1em;
	font-weight:bold;
}

#editSites {
	valign: top;
}
</style>
{/literal}
<h2>Websites Management</h2>
<p>Your Web Analytics reports need Websites! Add, update, delete Websites, and show the Javascript to insert in your pages.</p>

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
			<th> {'SitesManager_JsCode'|translate} </th>
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
				<td><a href='{url action=displayJavascriptCode idsite=$site.idsite}'>{'SitesManager_ShowJsCode'|translate}</a></td>
			</tr>
			{/foreach}
			
		</tbody>
	</table>
	<span class="addRowSite"><img src='plugins/UsersManager/images/add.png' alt="" />{'SitesManager_AddSite'|translate}</span>
{/if}

