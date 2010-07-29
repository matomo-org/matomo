{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='UsersManager'}
<h2>{'UsersManager_MenuUserSettings'|translate}</h2>

<br />
<table id='userSettingsTable' class="adminTable adminTableNoBorder" style='width:1000px'>
<tr>
	<td><label for="username">{'General_Username'|translate} </label></td>
	<td>
		<input size="25" value="{$userLogin}" id="username" disabled="disabled" />
		<span class='form-description'>{'UsersManager_YourUsernameCannotBeChanged'|translate}</span>
	</td>
</tr>

<tr>
	<td><label for="alias">{'UsersManager_Alias'|translate} </label></td>
	<td><input size="25" value="{$userAlias}" id="alias"{if $isSuperUser} disabled="disabled"{/if} />
		{if $isSuperUser}
			<span class='form-description'>
				{'UsersManager_TheSuperUserAliasCannotBeChanged'|translate}
			</span>
		{/if}
	</td>
</td>
</tr>
<tr>
	<td><label for="email">{'UsersManager_Email'|translate} </label></td>
	<td><input size="25" value="{$userEmail}" id="email" /></td>
</tr>
<tr>
	<td>{'UsersManager_ReportToLoadByDefault'|translate}</td>
	<td>
	<fieldset>
		<label><input type="radio" value="MultiSites" name="defaultReport"{if $defaultReport=='MultiSites'} checked="checked"{/if} /> {'General_AllWebsitesDashboard'|translate}</label><br />
		<label><input type="radio" value="1" name="defaultReport"{if $defaultReport!='MultiSites'} checked="checked"{/if} /> {'General_DashboardForASpecificWebsite'|translate}</label> 
		<select id="defaultReportWebsite">
		   {if isset($sites)}{foreach from=$sites item=info}
		   		<option value="{$info.idsite}" {if $defaultReport==$info.idsite} selected="selected"{/if}>{$info.name}</option>
		   {/foreach}{/if}
		</select>
	</fieldset>
	</td>
</tr>
<tr>
	<td>{'UsersManager_ReportDateToLoadByDefault'|translate}</td>
	<td>
	<fieldset>
		{foreach from=$availableDefaultDates key=value item=description}
			<label><input type="radio"{if $defaultDate==$value} checked="checked"{/if} value="{$value}" name="defaultDate" /> {$description}</label><br />
		{/foreach}
	</fieldset>
	</td>
</tr>

<tr>
	<td><label for="email">{'UsersManager_ChangePassword'|translate} </label></td>
	<td><input size="25" value="" autocomplete="off" id="password" type="password" />
	 <span class='form-description'>{'UsersManager_IfYouWouldLikeToChangeThePasswordTypeANewOne'|translate}</span>
	<br /><br /><input size="25" value="" autocomplete="off" id="passwordBis" type="password" />
	 <span class='form-description'> {'UsersManager_TypeYourPasswordAgain'|translate}</span>
	 </td>
</tr>
</table>

{ajaxErrorDiv id=ajaxErrorUserSettings}
{ajaxLoadingDiv id=ajaxLoadingUserSettings}
<input type="submit" value="{'General_Save'|translate}" id="userSettingsSubmit" class="submit" />

<br/><br/>
<a name='excludeCookie'></a><h2>{'UsersManager_ExcludeVisitsViaCookie'|translate}</h2>
<p>{if $ignoreCookieSet}{'UsersManager_YourVisitsAreIgnoredOnDomain'|translate:"<strong>":$piwikHost:"</strong>"}
{else}{'UsersManager_YourVisitsAreNotIgnored'|translate:"<strong>":"</strong>"}{/if}</p>
<span style='margin-left:20px'>
<a href='{url token_auth=$token_auth action=setIgnoreCookie}#excludeCookie'>&rsaquo; {if $ignoreCookieSet}{'UsersManager_ClickHereToDeleteTheCookie'|translate}
{else}{'UsersManager_ClickHereToSetTheCookieOnDomain'|translate:$piwikHost}{/if} 
<br />
</a></span>

<br/><br/>
{if $isSuperUser}
	<h2>{'UsersManager_MenuAnonymousUserSettings'|translate}</h2>
	{if count($anonymousSites) == 0}
		<br /><span class='form-description'><b>{'UsersManager_NoteNoAnonymousUserAccessSettingsWontBeUsed'|translate}</b></span><br />
	{/if}
	<br />
	
	{ajaxErrorDiv id=ajaxErrorAnonymousUserSettings}
	{ajaxLoadingDiv id=ajaxLoadingAnonymousUserSettings}

	<table id='anonymousUserSettingsTable' class="adminTable adminTableNoBorder" style='width:850px'>
	<tr>
		<td style='width:400px'>{'UsersManager_WhenUsersAreNotLoggedInAndVisitPiwikTheyShouldAccess'|translate}</td>
		<td>
		<fieldset>
			<label><input type="radio" value="Login" name="anonymousDefaultReport"{if $anonymousDefaultReport==$loginModule} checked="checked"{/if} /> {'UsersManager_TheLoginScreen'|translate}</label><br />
			<label><input {if empty($anonymousSites)}disabled="disabled" {/if}type="radio" value="MultiSites" name="anonymousDefaultReport"{if $anonymousDefaultReport=='MultiSites'} checked="checked"{/if} /> {'General_AllWebsitesDashboard'|translate}</label><br />
			
				<label><input {if empty($anonymousSites)}disabled="disabled" {/if}type="radio" value="1" name="anonymousDefaultReport"{if $anonymousDefaultReport>0} checked="checked"{/if} /> {'General_DashboardForASpecificWebsite'|translate}</label>
				{if !empty($anonymousSites)}
				<select id="anonymousDefaultReportWebsite">
				   {foreach from=$anonymousSites item=info}
				   		<option value="{$info.idsite}" {if $anonymousDefaultReport==$info.idsite} selected="selected"{/if}>{$info.name}</option>
				   {/foreach}
				</select>
				{/if}
		</fieldset>
		</td>
	</tr>
	<tr>
		<td>{'UsersManager_ForAnonymousUsersReportDateToLoadByDefault'|translate}</td>
		<td>
		<fieldset>
			{foreach from=$availableDefaultDates key=value item=description}
				<label><input type="radio" {if $anonymousDefaultDate==$value}checked="checked" {/if}value="{$value}" name="anonymousDefaultDate" /> {$description}</label><br />
			{/foreach}
		</fieldset>
		</td>
	</tr>
	
	</table>
	
<input type="submit" value="{'General_Save'|translate}" id="anonymousUserSettingsSubmit" class="submit" />

{/if}


{include file="CoreAdminHome/templates/footer.tpl"}
