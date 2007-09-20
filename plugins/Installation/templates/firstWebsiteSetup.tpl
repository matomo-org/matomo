

{if isset($displayGeneralSetupSuccess)}
<span id="toFade" class="success">
	General Setup configured with success
	<img src="themes/default/images/success_medium.png">
</span>
{/if}

<h1>Setup a website</h1>



{if isset($errorMessage)}
	<div class="error">
		<img src="themes/default/images/error_medium.png">
		There was an error when adding the website:
		<br>- {$errorMessage}
		
	</div>
{/if}


{if isset($form_data)}
	{include file=default/genericForm.tpl}
{/if}