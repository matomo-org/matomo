{literal}
<style>
* {
font-family:Trebuchet MS,arial,sans-serif;
}

textarea{
	font-family: Trebuchet MS, Verdana;
	font-size:0.85em;
	
}

#editSites{
	valign:top;
}
</style>
{/literal}
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>

<h2>Sites</h2>
<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none">Loading... <img src="themes/default/loading.gif"></div>

<table id="editSites" border=1 cellpadding="10">
    <tbody>
		<tr>
		<td>Id</td>
		<td>Name</td>
		<td>URLs</td>
		</tr>
		
		{foreach from=$sites key=i item=site}
		<tr id="row{$i}">
			<td id="idSite">{$site.idsite}</td>
			<td id="name" class="editableSite">{$site.name}</td>
			<td id="urls" class="editableSite">{foreach from=$site.alias_urls item=url}{$url}<br>{/foreach}</td>       
			<td><img src='plugins/UsersManager/images/edit.png' class="editSite" id="row{$i}" href='#'></td>
		    <td><img src='plugins/UsersManager/images/remove.png' class="deleteSite" id="row{$i}" value="Delete"></td>
		      
		</tr>
		{/foreach}
		
    </tbody>
</table>
<div id="addRowSite"><img src='plugins/UsersManager/images/add.png'> <a href="#">Add a new Site</a></div>

<script type="text/javascript" src="plugins/UsersManager/templates/UsersManager.js"></script>
<script type="text/javascript" src="plugins/SitesManager/templates/SitesManager.js"></script>
