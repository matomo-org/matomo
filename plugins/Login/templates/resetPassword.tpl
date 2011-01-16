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

<form {$form_data.attributes}>
	<p>
		<label>{'Login_LoginOrEmail'|translate}:<br />
		<input type="text" name="form_login" id="form_login" class="input" value="" size="20" tabindex="10" /></label>
		<input type="hidden" name="form_nonce" id="form_nonce" value="{$nonce}" /></label>
	</p>

	<p>
		<label>{'Login_Password'|translate}:<br />
		<input type="password" name="form_password" id="form_password" class="input" value="" size="20" tabindex="20" /></label>
	</p>

	<p>
		<label>{'Login_PasswordRepeat'|translate}:<br />
		<input type="password" name="form_password_bis" id="form_password_bis" class="input" value="" size="20" tabindex="30" /></label>
	</p>

	<p>
		<label>{'Login_PasswordResetToken'|translate}:<br />
		<input type="text" name="form_token" id="form_token" class="input" value="{$form_data.form_token.value}" size="20" tabindex="40" /></label>
	</p>

	<p class="submit">
		<input type="submit" value="{'Login_ChangePassword'|translate}" tabindex="100" />
	</p>
</form>

<p id="nav">
<a href="index.php?module=Login&amp;action=lostPassword" title="{'Login_LostYourPassword'|translate}">{'Login_LostYourPassword'|translate}</a>
</p>

</div>

</body>
</html>
