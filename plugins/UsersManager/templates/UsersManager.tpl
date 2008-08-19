{loadJavascriptTranslations plugins='UsersManager'}
{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{include file="CoreAdminHome/templates/menu.tpl"}

{literal}
<style>
.dialog {
	display: none;
	padding:20px 10px;
	color:#7A0101;
	cursor:wait;
	font-size:1.2em;
	font-weight:bold;
	text-align:center;
}

#accessUpdated {
	color: red;
	text-align: center;
	font-weight: bold;
	width: 350px;
	margin: 10px;
	padding: 10px;
	display: none;
	border: 3px solid green;
	color: green;
}
#access td, #users td {
	spacing: 0px;
	padding: 2px 5px 5px 4px;
	border: 1px solid #660000;
	width: 100px;
}
.editable:hover, .addrow:hover, .updateAccess:hover, .accessGranted:hover, .adduser:hover, .edituser:hover, .deleteuser:hover, .updateuser:hover, .cancel:hover{
	cursor: pointer;
}

.addrow {
	font-color:#3A477B;
	padding:1em;
	font-weight:bold;
}
.addrow a {
	text-decoration: none;
}
.addrow img {
	vertical-align: middle;
}
</style>
{/literal}

<h2>{'UsersManager_ManageAccess'|translate}</h2>
<p>{'UsersManager_MainDescription'|translate}</p>
<div id="sites">
<form method="post" action="{url action=index}" id="accessSites">
	<p>{'UsersManager_Sites'|translate}: <select id="selectIdsite" name="idsite" onchange="this.form.submit()">
	
	<optgroup label="{'UsersManager_AllWebsites'|translate}">
		<option label="{'UsersManager_AllWebsites'|translate}" value="all" {if $idSiteSelected=='all'} selected="selected"{/if}>{'UsersManager_ApplyToAllWebsites'|translate}</option>
	</optgroup>
	
	<optgroup label="{'UsersManager_Sites'|translate}">
		{foreach from=$websites item=info}
			<option value="{$info.idsite}" {if $idSiteSelected==$info.idsite} selected="selected"{/if}>{$info.name}</option>
		{/foreach}
	</optgroup>
	
	</select></p>
</form>
</div>

<table class="admin" id="access">
<thead>
<tr>
	<th>{'UsersManager_User'|translate}</th>
	<th>{'UsersManager_PrivNone'|translate}</th>
	<th>{'UsersManager_PrivView'|translate}</th>
	<th>{'UsersManager_PrivAdmin'|translate}</th>
</tr>
</thead>

<tbody>
{foreach from=$usersAccessByWebsite key=login item=access}
{assign var=accesValid value="<img src='plugins/UsersManager/images/ok.png' class='accessGranted'>"}
{assign var=accesInvalid value="<img src='plugins/UsersManager/images/no-access.png' class='updateAccess'>"}
<tr>
	<td id='login'>{$login}</td>
	<td id='noaccess'>{if $access=='noaccess' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
	<td id='view'>{if $access=='view' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
	<td id='admin'>{if $access=='admin' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
</tr>
{/foreach}
</tbody>
</table>

<div id="accessUpdated">{'General_Done'|translate}!</div>

<div class="dialog" id="confirm"> 
	<p>{'UsersManager_ChangeAllConfirm'|translate:"<span id='login'></span>"}</p>
	<input id="yes" type="button" value="{'General_Yes'|translate}"/>
	<input id="no" type="button" value="{'General_No'|translate}"/>
</div> 

<br/>
<h2>{'UsersManager_UsersManagement'|translate}</h2>
<p>{'UsersManager_UsersManagementMainDescription'|translate}</p>
<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none"><div id="loadingPiwik"><img src="themes/default/images/loading-blue.gif" alt="" /> {'General_LoadingData'|translate}</div></div>
<table class="admin" id="users">
	<thead>
		<tr>
			<th>{'UsersManager_Login'|translate}</th>
			<th>{'UsersManager_Password'|translate}</th>
			<th>{'UsersManager_Email'|translate}</th>
			<th>{'UsersManager_Alias'|translate}</th>
			<th>{'UsersManager_Token'|translate}</th>
			<th>{'General_Edit'|translate}</th>
			<th>{'General_Delete'|translate}</th>
		</tr>
	</thead>
	
	<tbody>
		{foreach from=$users item=user key=i}
			{if $user.login != 'anonymous'}
			<tr class="editable" id="row{$i}">
				<td id="userLogin" class="editable">{$user.login}</td>
				<td id="password" class="editable">-</td>
				<td id="email" class="editable">{$user.email}</td>
				<td id="alias" class="editable">{$user.alias}</td>
				<td id="alias">{$user.token_auth}</td>
				<td><img src='plugins/UsersManager/images/edit.png' class="edituser" id="row{$i}" href='#'></td>
				<td><img src='plugins/UsersManager/images/remove.png' class="deleteuser" id="row{$i}" value="Delete"></td>
			</tr>
			{/if}
		{/foreach}
	</tbody>
</table>

<div class="addrow"><a href="#"><img src='plugins/UsersManager/images/add.png'> {'UsersManager_AddUser'|translate}</a></div>
<script type="text/javascript" src="plugins/UsersManager/templates/UsersManager.js"></script>
