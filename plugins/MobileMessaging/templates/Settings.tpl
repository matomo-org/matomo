{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file='CoreAdminHome/templates/header.tpl'}
{loadJavascriptTranslations plugins='MobileMessaging'}

{* TODO this UI probably needs some embellishment by working out a better HTML structure (table ? list ?), specifying better locations for errors and so on *}

{if $isSuperUser}
<h2>{'MobileMessaging_Settings_LetUsersManageAPICredential'|translate}</h2>

<input
	type='radio'
	value='true'
	name='delegatedManagement'{if $delegatedManagement} checked='checked'{/if} />
{'General_Yes'|translate}

<input
	type='radio'
	value='false'
	name='delegatedManagement'{if !$delegatedManagement} checked='checked'{/if} />
{'General_No'|translate}

{/if}

{if $accountManagedByCurrentUser}
<h2>{'MobileMessaging_Settings_SMSAPIAccount'|translate}</h2>
	{if $credentialSupplied}
		{'MobileMessaging_Settings_CredentialProvided'|translate:$provider:$APIUsername}
		{$creditLeft}
		<a id='deleteAccount'>{'MobileMessaging_Settings_DeleteAccount'|translate}</a>
		<br/>
	{/if}

	{'MobileMessaging_Settings_UpdateAccount'|translate} : <br/>

	{'MobileMessaging_Settings_SMSProvider'|translate}
	<select id="smsProviders">
		{foreach from=$smsProviders item=smsProvider}
			<option value="{$smsProvider}">
				{$smsProvider}
			</option>
		{/foreach}
	</select>

	{'MobileMessaging_Settings_Username'|translate}
	<input size='25' id='username'/>

	{'MobileMessaging_Settings_Password'|translate}
	<input size='25' id='password' type='password' autocomplete='off'/>

	<input type='submit' value='{'General_Save'|translate}' id='apiAccountSubmit' class='submit' />
{/if}

<h2>{'MobileMessaging_Settings_PhoneNumbers'|translate}</h2>
{if !$credentialSupplied}
	{if $accountManagedByCurrentUser}
		{'MobileMessaging_Settings_CredentialNotProvided'|translate}
	{else}
		{'MobileMessaging_Settings_CredentialNotProvidedByAdmin'|translate}
	{/if}
{else}
	<span id='suspiciousPhoneNumber' style='display:none;'>
		{'MobileMessaging_Settings_SuspiciousPhoneNumber'|translate}
		<br/>
	</span>
	<select id='countries'>
		<option>&nbsp;</option> {* this is a trick to avoid selecting the first country when no default could be found *}
		{foreach from=$countries key=countryCode item=country}
			<option
				value='{$countryCode}'
				calling-code='{$country.countryCallingCode}'
				{if $defaultCountry==$countryCode} selected='selected' {/if}
			>
				 {$country.countryName}
			 </option>
		{/foreach}
	</select>
	+<input id='countryCallingCode'/>
	<input id='newPhoneNumber'/>
	<input
			type='submit'
			value='{'MobileMessaging_Settings_AddPhoneNumber'|translate}'
			id='addPhoneNumberSubmit'
			class='submit'
	/>

	<ul>
	{foreach from=$phoneNumbers key=phoneNumber item=validated}
		<li>
			<span class='phoneNumber'>{$phoneNumber}</span>
			{if !$validated}
				<input class='verificationCode'/>
				<input
						type='submit'
						value='{'MobileMessaging_Settings_ValidatePhoneNumber'|translate}'
						class='submit validatePhoneNumberSubmit'
				/>
			{/if}
			<input
					type='submit'
					value='{'MobileMessaging_Settings_RemovePhoneNumber'|translate}'
					class='submit removePhoneNumberSubmit'
			/>
		</li>
	{/foreach}
	</ul>
{/if}

{ajaxErrorDiv id=ajaxErrorMobileMessagingSettings}
{ajaxLoadingDiv id=ajaxLoadingMobileMessagingSettings}

{include file='CoreAdminHome/templates/footer.tpl'}
