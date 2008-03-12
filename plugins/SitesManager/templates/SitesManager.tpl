


<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>

<script type="text/javascript" src="plugins/UsersManager/templates/UsersManager.js"></script>
<script type="text/javascript" src="plugins/SitesManager/templates/SitesManager.js"></script>

<link rel="stylesheet" href="themes/default/common-admin.css">

<h2>Sites</h2>
<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none">Loading... <img src="themes/default/loading.gif"></div>

{if $sites|@count == 0}
	You don't have any website to administrate.
{else}
	<table id="editSites" border=1 cellpadding="10">
	    <thead>
			<tr>
			<th>Id</th>
			<th>Name</th>
			<th>URLs</th>
			<th> </th>
			<th> </th>
			<th> Javascript code </th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$sites key=i item=site}
			<tr id="row{$i}">
				<td id="idSite">{$site.idsite}</td>
				<td id="siteName" class="editableSite">{$site.name}</td>
				<td id="urls" class="editableSite">{foreach from=$site.alias_urls item=url}{$url}<br>{/foreach}</td>       
				<td><img src='plugins/UsersManager/images/edit.png' class="editSite" id="row{$i}" href='#'></td>
			    <td><img src='plugins/UsersManager/images/remove.png' class="deleteSite" id="row{$i}" value="Delete"></td>
		        <td><a href='{url action=displayJavascriptCode idsite=$site.idsite}'>Show Code</a></td>
			</tr>
			{/foreach}
			
	    </tbody>
	</table>
	<div id="addRowSite"><img src='plugins/UsersManager/images/add.png'> <a href="#">Add a new Site</a></div>
{/if}


<p><a href='?module=Home'>Back to Piwik homepage</a></p>


