{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file='CoreAdminHome/templates/header.tpl'}
{loadJavascriptTranslations plugins='MobileMessaging'}

{literal}
<style>#accountForm ul {
	list-style: circle;
	margin-left: 17px;
	line-height: 1.5em;
}
.providerDescription {
    border: 2px dashed #C5BDAD;
    border-radius: 16px 16px 16px 16px;
    margin-left: 24px;
    padding: 11px;
    width: 600px;
}
</style>
{/literal}

{if $accountManagedByCurrentUser}
<h2>{'MobileMessaging_Settings_SMSAPIAccount'|translate}</h2>
	{if $credentialSupplied}
		{'MobileMessaging_Settings_CredentialProvided'|translate:$provider}
		{$creditLeft}
		<br/>
		{'MobileMessaging_Settings_UpdateOrDeleteAccount'|translate:"<a id='displayAccountForm'>":"</a>":"<a id='deleteAccount'>":"</a>"}
	{else}
		{'MobileMessaging_Settings_PleaseSignUp'|translate}
	{/if}

	<div id='accountForm' {if $credentialSupplied}style='display: none;'{/if}>
		<br/>
		{'MobileMessaging_Settings_SMSProvider'|translate}
		<select id='smsProviders'>
			{foreach from=$smsProviders key=smsProvider item=description}
				<option value='{$smsProvider}'>
					{$smsProvider}
				</option>
			{/foreach}
		</select>

		{'MobileMessaging_Settings_APIKey'|translate}
		<input size='25' id='apiKey'/>

		<input type='submit' value='{'General_Save'|translate}' id='apiAccountSubmit' class='submit' />

		{foreach from=$smsProviders key=smsProvider item=description}
			<div class='providerDescription' id='{$smsProvider}'>
				{$description}
			</div>
		{/foreach}

	</div>
{/if}

{ajaxErrorDiv id=ajaxErrorMobileMessagingSettings}

<h2>{'MobileMessaging_Settings_PhoneNumbers'|translate}</h2>
{if !$credentialSupplied}
	{if $accountManagedByCurrentUser}
		{'MobileMessaging_Settings_CredentialNotProvided'|translate}
	{else}
		{'MobileMessaging_Settings_CredentialNotProvidedByAdmin'|translate}
	{/if}
{else}

	{'MobileMessaging_Settings_PhoneNumbers_Help'|translate}<br/><br/>
	
	<table style="width:900px;" class="adminTable">
	<tbody><tr>
	<td style="width:480px">
		<strong>{'MobileMessaging_Settings_PhoneNumbers_Add'|translate}</strong><br/><br/>
	
		<span id='suspiciousPhoneNumber' style='display:none;'>
			{'MobileMessaging_Settings_SuspiciousPhoneNumber'|translate:'54184032'}<br/><br/>
		</span>
	
		+ <input id='countryCallingCode' size='4' maxlength='4'/>&nbsp;
		<input id='newPhoneNumber'/>
		<input
				type='submit'
				value='{'MobileMessaging_Settings_AddPhoneNumber'|translate}'
				id='addPhoneNumberSubmit'
				/>
	
		<br/>
		
		<span style=' font-size: 11px;'><span class="form-description">{'MobileMessaging_Settings_CountryCode'|translate}</span><span class="form-description">{'MobileMessaging_Settings_PhoneNumber'|translate}</span></span>
		<br/><br/>
	
		{'MobileMessaging_Settings_PhoneNumbers_CountryCode_Help'|translate}
		
		<select id='countries'>
			<option value=''>&nbsp;</option> {* this is a trick to avoid selecting the first country when no default could be found *}
			{foreach from=$countries key=countryCode item=country}
				<option
						value='{$country.countryCallingCode}'
					{if $defaultCountry==$countryCode} selected='selected' {/if}
						>
					{$country.countryName|truncate:15:'...'}
				</option>
			{/foreach}
		</select>
	
	</td>
	<td style="width:220px">
		{$strHelpAddPhone|inlineHelp}
	
	</td></tr>
	<tr><td colspan="2">
	
	{if $phoneNumbers|@count gt 0}
		<br/>
		<br/>
		<strong>{'MobileMessaging_Settings_ManagePhoneNumbers'|translate}</strong><br/><br/>
	{/if}

	{ajaxErrorDiv id=invalidVerificationCodeAjaxError}

	<div id='phoneNumberActivated' class="ajaxSuccess" style="display:none;">
		{'MobileMessaging_Settings_PhoneActivated'|translate}
	</div>

	<div id='invalidActivationCode' style="display:none;">
		{'MobileMessaging_Settings_InvalidActivationCode'|translate}
	</div>

	<ul>
	{foreach from=$phoneNumbers key=phoneNumber item=validated}
		<li>
			<span class='phoneNumber'>{$phoneNumber}</span>
			{if !$validated}
				<input class='verificationCode'/>
				<input
						type='submit'
						value='{'MobileMessaging_Settings_ValidatePhoneNumber'|translate}'
						class='validatePhoneNumberSubmit'
				/>
			{/if}
			<input
					type='submit'
					value='{'MobileMessaging_Settings_RemovePhoneNumber'|translate}'
					class='removePhoneNumberSubmit'
			/>
			{if !$validated}
				<br/>
				<span class='form-description'>{'MobileMessaging_Settings_VerificationCodeJustSent'|translate}</span>
			{/if}
			<br/>
			<br/>
		</li>
	{/foreach}
	</ul>
	
	</td>
	</tr>
	</tbody></table>
{/if}

{if $isSuperUser}
	<h2>{'MobileMessaging_Settings_SuperAdmin'|translate}</h2>

	<table class='adminTable' style='width:650px;'>
		<tr>
			<td style='width:400px'>{'MobileMessaging_Settings_LetUsersManageAPICredential'|translate}</td>
			<td style='width:250px'>
				<fieldset>
					<label>
						<input
								type='radio'
								value='false'
								name='delegatedManagement' {if !$delegatedManagement} checked='checked'{/if} />
					{'General_No'|translate}
						<br/>
						<span class='form-description'>({'General_Default'|translate}) {'MobileMessaging_Settings_LetUsersManageAPICredential_No_Help'|translate}</span>
					</label>
					<br/>
					<br/>
					<label>
						<input
								type='radio'
								value='true'
								name='delegatedManagement' {if $delegatedManagement} checked='checked'{/if} />
					{'General_Yes'|translate}
						<br/>						
						<span class='form-description'>{'MobileMessaging_Settings_LetUsersManageAPICredential_Yes_Help'|translate}</span>
					</label>

				</fieldset>
		</tr>
	</table>
{/if}

{ajaxLoadingDiv id=ajaxLoadingMobileMessagingSettings}

{include file='CoreAdminHome/templates/footer.tpl'}

<div class='ui-confirm' id='confirmDeleteAccount'>
	<h2>{'MobileMessaging_Settings_DeleteAccountConfirm'|translate}</h2>
	<input role='yes' type='button' value='{'General_Yes'|translate}' />
	<input role='no' type='button' value='{'General_No'|translate}' />
</div>

