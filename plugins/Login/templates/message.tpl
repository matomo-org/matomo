{if isset($infoMessage)}
<p class="message">{$infoMessage}</p>
{/if}
{if isset($formErrors)}
<p id="login_error">	
	{foreach from=$formErrors item=data}
		<strong>{'General_Error'|translate}</strong>: {$data}<br />
	{/foreach}
</p>
{/if}

