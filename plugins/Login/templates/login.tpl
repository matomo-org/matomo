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
		<label>{'General_Username'|translate}:<br />
		<input type="text" name="form_login" id="form_login" class="input" value="" size="20" tabindex="10" />
		<input type="hidden" name="form_nonce" id="form_nonce" value="{$nonce}" /></label>
	</p>

	<p>
		<label>{'Login_Password'|translate}:<br />
		<input type="password" name="form_password" id="form_password" class="input" value="" size="20" tabindex="20" /></label>
	</p>
	<p class="forgetmenot">
		<label><input name="form_rememberme" type="checkbox" id="form_rememberme" value="1" tabindex="90" {if $form_data.form_rememberme.value}checked="checked" {/if}/> {'Login_RememberMe'|translate} </label>
	</p>
	<p class="submit">
		<input type="submit" value="{'Login_LogIn'|translate}" tabindex="100" />
	</p>
</form>

<p id="nav">
<a href="index.php?module=Login&amp;action=lostPassword" title="{'Login_LostYourPassword'|translate}">{'Login_LostYourPassword'|translate}</a>
</p>
{if isset($smarty.capture.poweredByPiwik)}
	<p id="piwik">
	{$smarty.capture.poweredByPiwik}
	</p>
{/if}


</div>


</body>
</html>
