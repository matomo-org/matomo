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
		<label>{'Login_Login'|translate}:<br />
		<input type="text" name="form_login" id="form_login" class="input" value="" size="20" tabindex="10" /></label>
	</p>

	<p>
		<label>{'Login_Password'|translate}:<br />
		<input type="password" name="form_password" id="form_password" class="input" value="" size="20" tabindex="20" /></label>
	</p>
	{*
		<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> Remember Me</label></p>
	*}
	{$form_data.form_url.html}
	<p class="submit">
		<input type="submit" value="{'Login_LogIn'|translate}" tabindex="100" />
	</p>
</form>


<p id="nav">
<a href="?module=Login&amp;action=lostpassword&amp;form_url={$urlToRedirect}" title="{'Login_LostYourPassword'|translate}">{'Login_LostYourPassword'|translate}</a>
</p>

</div>

</body>
</html>



