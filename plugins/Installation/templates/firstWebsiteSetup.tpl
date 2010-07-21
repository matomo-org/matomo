

{if isset($displayGeneralSetupSuccess)}
<span id="toFade" class="success">
	{'Installation_GeneralSetupSuccess'|translate}
	<img src="themes/default/images/success_medium.png" />
</span>
{/if}

<h2>{'Installation_SetupWebsite'|translate}</h2>

{if isset($errorMessage)}
	<div class="error">
		<img src="themes/default/images/error_medium.png" />
		{'Installation_SetupWebsiteError'|translate}:
		<br />- {$errorMessage}
		
	</div>
{/if}

{if isset($form_data)}
	{include file=default/genericForm.tpl}
{/if}
