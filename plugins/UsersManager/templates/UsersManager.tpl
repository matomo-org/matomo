{loadJavascriptTranslations modules='UsersManager'}
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>
<link rel="stylesheet" href="themes/default/common-admin.css">

<script type="text/javascript" src="libs/jquery/jquery.blockUI.js"></script>

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
.editable:hover, .addrow:hover, .updateAccess:hover, .accessGranted:hover, .adduser:hover, .edituser:hover, .deleteuser:hover, .updateuser:hover, .cancel:hover{
	cursor: pointer;
}

.addrow {
	font-color:#3A477B;
	padding:1em;
	font-weight:bold;
}
</style>
{/literal}

<h2>{'UsersManager_ManageAccess'|translate}</h2>

<div id="sites">
<form method="post" action="{url actionToLoad=index}" id="accessSites">
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

<table id="access">
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

<h2>Manage users</h2>

<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none">{'General_LoadingData'|translate} <img src="themes/default/loading.gif"></div>
<table id="users">
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

<div class="addrow"><img src='plugins/UsersManager/images/add.png'> {'UsersManager_AddUser'|translate}</div>
<script type="text/javascript" src="plugins/UsersManager/templates/UsersManager.js"></script>
