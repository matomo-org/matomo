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
</style>
{/literal}

<h2>Manage access</h2>

<div id="sites">
<form method="post" action="{url actionToLoad=index}" id="accessSites">
	<p>Sites: <select id="selectIdsite" name="idsite" onchange="this.form.submit()">
	
	<optgroup label="All websites">
		<option label="All websites" value="-1" {if $idSiteSelected==-1} selected="selected"{/if}>Apply to all websites</option>
	</optgroup>
	<optgroup label="Sites">
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
	<th>User</th>
	<th>No access</th>
	<th>View</th>
	<th>Admin</th>
</tr>
</thead>

<tbody>
{foreach from=$usersAccessByWebsite key=login item=access}
{assign var=accesValid value="<img src='plugins/UsersManager/images/ok.png' class='accessGranted'>"}
{assign var=accesInvalid value="<img src='plugins/UsersManager/images/no-access.png' class='updateAccess'>"}
<tr>
	<td id='login'>{$login}</td>
	<td id='noaccess'>{if $access=='noaccess' and $idSiteSelected!=-1}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
	<td id='view'>{if $access=='view' and $idSiteSelected!=-1}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
	<td id='admin'>{if $access=='admin' and $idSiteSelected!=-1}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
</tr>
{/foreach}
</tbody>
</table>

<div id="accessUpdated">Done!</div>

<div class="dialog" id="confirm"> 
	<p>Are you sure you want to change '<span id='login'></span>' permissions on all the websites?</p>
	<input id="yes" type="button" value="Yes"/>
	<input id="no" type="button" value="No"/>
</div> 

<h2>Manage users</h2>

<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none">Loading... <img src="themes/default/loading.gif"></div>
<table id="users">
	<thead>
		<tr>
			<th>Login</th>
			<th>Password</th>
			<th>Email</th>
			<th>Alias</th>
			<th>token_auth</th>
			<th>Edit</th>
			<th>Delete</th>
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

<div id="addrow"><img src='plugins/UsersManager/images/add.png'> <a href="#">Add a new user</a></div>
<script type="text/javascript" src="plugins/UsersManager/templates/UsersManager.js"></script>

<p><a href='?module=Home'>Back to Piwik homepage</a></p>
