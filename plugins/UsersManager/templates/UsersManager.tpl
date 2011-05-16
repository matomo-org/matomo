{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='UsersManager'}

{literal}
<style type="text/css">
.dialog {
	display: none;
	padding:20px 10px;
	color:#7A0101;
	cursor:wait;
	font-size:1.2em;
	font-weight:bold;
	text-align:center;
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

<div class="entityContainer" style='width:500px'>
	<table class="entityTable dataTable" id="access">
		<thead>
		<tr>
			<th class='first'>{'UsersManager_User'|translate}</th>
			<th>{'UsersManager_Alias'|translate}</th>
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
			<td>{$usersAliasByLogin[$login]}</td>
			<td id='noaccess'>{if $access=='noaccess' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
			<td id='view'>{if $access=='view' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
			<td id='admin'>{if $access=='admin' and $idSiteSelected!='all'}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
		</tr>
		{/foreach}
		</tbody>
	</table>
</div>

<div class="ui-confirm" id="confirm">
	<h2>{'UsersManager_ChangeAllConfirm'|translate:"<span id='login'></span>"}</h2>
    <input id="yes" type="button" value="{'General_Yes'|translate}" />
    <input id="no" type="button" value="{'General_No'|translate}" />
</div> 

{if $userIsSuperUser}
    <div class="ui-confirm" id="confirmUserRemove">
        <h2></h2>
        <input id="yes" type="button" value="{'General_Yes'|translate}" />
        <input id="no" type="button" value="{'General_No'|translate}" />
    </div> 
    <div class="ui-confirm" id="confirmPasswordChange">
        <h2>{'UsersManager_ChangePasswordConfirm'|translate}</h2>
        <input id="yes" type="button" value="{'General_Yes'|translate}" />
        <input id="no" type="button" value="{'General_No'|translate}" />
    </div> 

	<br />
	<h2>{'UsersManager_UsersManagement'|translate}</h2>
	<p>{'UsersManager_UsersManagementMainDescription'|translate} 
	{'UsersManager_ThereAreCurrentlyNRegisteredUsers'|translate:"<b>$usersCount</b>"}</p>

	{ajaxErrorDiv id=ajaxErrorUsersManagement}
	{ajaxLoadingDiv id=ajaxLoadingUsersManagement}

	<div class="entityContainer" style='margin-bottom:50px'>
	<table class="entityTable dataTable" id="users">
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
					<td><span class="edituser link_but" id="row{$i}"><img title="{'General_Edit'|translate}" src='themes/default/images/ico_edit.png' /> {'General_Edit'|translate} </span></td>
					<td><span class="deleteuser link_but" id="row{$i}"><img title="{'General_Delete'|translate}" src='themes/default/images/ico_delete.png' /> {'General_Delete'|translate} </span></td>
				</tr>
				{/if}
			{/foreach}
		</tbody>
	</table>
	<div class="addrow"><img src='plugins/UsersManager/images/add.png' /> {'UsersManager_AddUser'|translate}</div>
	</div>	
{/if}

{include file="CoreAdminHome/templates/footer.tpl"}
