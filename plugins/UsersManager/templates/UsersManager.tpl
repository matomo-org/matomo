{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='UsersManager'}

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
	<p>{'UsersManager_Sites'|translate}: <select id="selectIdsite" name="idsite" onchange="changeSite()">
	
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

{ajaxErrorDiv}
{ajaxLoadingDiv}
<div id="accessUpdated" class="ajaxSuccess">{'General_Done'|translate}!</div>

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
{assign var=accesValid value="<img src='plugins/UsersManager/images/ok.png' class='accessGranted' />"}
{assign var=accesInvalid value="<img src='plugins/UsersManager/images/no-access.png' class='updateAccess' />"}
{foreach from=$usersAccessByWebsite key=login item=access}
<tr>
	<td id='login'>{$login}</td>
	<td id='noaccess'>{if $access=='noaccess' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
	<td id='view'>{if $access=='view' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
	<td id='admin'>{if $access=='admin' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
</tr>
{/foreach}
</tbody>
</table>

<div class="dialog" id="confirm"> 
	<p>{'UsersManager_ChangeAllConfirm'|translate:"<span id='login'></span>"}</p>
	<input id="yes" type="button" value="{'General_Yes'|translate}" />
	<input id="no" type="button" value="{'General_No'|translate}" />
</div> 

{if $userIsSuperUser}
	<br />
	<h2>{'UsersManager_UsersManagement'|translate}</h2>
	<p>{'UsersManager_UsersManagementMainDescription'|translate}</p>

	{ajaxErrorDiv id=ajaxErrorUsersManagement}
	{ajaxLoadingDiv id=ajaxLoadingUsersManagement}

	<table class="admin" id="users">
		<thead>
			<tr>
				<th>{'General_Username'|translate}</th>
				<th>{'UsersManager_Password'|translate}</th>
				<th>{'UsersManager_Email'|translate}</th>
				<th>{'UsersManager_Alias'|translate}</th>
				<th>token_auth</th>
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
					<td><img src='plugins/UsersManager/images/edit.png' class="edituser" id="row{$i}" href='#' /></td>
					<td><img src='plugins/UsersManager/images/remove.png' class="deleteuser" id="row{$i}" value="Delete" /></td>
				</tr>
				{/if}
			{/foreach}
		</tbody>
	</table>
	
	<div class="addrow"><a href="#"><img src='plugins/UsersManager/images/add.png' /> {'UsersManager_AddUser'|translate}</a></div>
{/if}

{include file="CoreAdminHome/templates/footer.tpl"}
