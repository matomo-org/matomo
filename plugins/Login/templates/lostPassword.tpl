{include file="Login/templates/header.tpl"}

<div id="login">

{if $form_data.errors}
<div id="login_error">	
	{foreach from=$form_data.errors item=data}
		<strong>{'General_Error'|translate}</strong>: {$data}<br />
	{/foreach}
</div>
{/if}

{if $AccessErrorString}
<div id="login_error"><strong>{'General_Error'|translate}</strong>: {$AccessErrorString}<br /></div>
{/if}

<p class="message">
{'Login_PasswordReminder'|translate}
</p>

<form {$form_data.attributes}>
	<p>
		<label>{'Login_LoginOrEmail'|translate}:<br />
		<input type="text" name="form_login" id="form_login" class="input" value="" size="20" tabindex="10" /></label>
		<input type="hidden" name="form_nonce" id="form_nonce" value="{$nonce}" /></label>
	</p>
	<p class="submit">
		<input type="submit" value="{'Login_RemindPassword'|translate}" tabindex="100" />
	</p>
</form>

<p id="nav">
<a href="index.php?module=Login" title="{'Login_LogIn'|translate}">{'Login_LogIn'|translate}</a>
</p>

</div>

</body>
</html>
