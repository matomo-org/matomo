<h1>{'Installation_DatabaseSetup'|translate}</h1>

{if isset($errorMessage)}
	<div class="error">
		<img src="themes/default/images/error_medium.png" />
		{'Installation_DatabaseErrorConnect'|translate}:
		<br />{$errorMessage}
		
	</div>
{/if}

{if isset($form_data)}
	{include file=default/genericForm.tpl}
{/if}
