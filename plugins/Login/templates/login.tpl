{include file="Login/templates/header.tpl"}

<div id="login">

{* untrusted host warning *}
{if isset($isValidHost) && isset($invalidHostMessage) && !$isValidHost}
<div id="login_error" style='width:400px'>
	<strong>{'General_Warning'|translate}:&nbsp;</strong>{$invalidHostMessage}

	<br><br>{$invalidHostMessageHowToFix}
	<br/><br/><a style="float:right" href="http://piwik.org/faq/troubleshooting/#faq_171" target="_blank">{'General_Help'|translate} <img style='vertical-align: bottom' src="themes/default/images/help_grey.png" /></a><br/>


</div>
{else}
<div id="message_container">
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

	{if $infoMessage}
	<p class="message">{$infoMessage}</p>
	{/if}
</div>

<form id="login_form" {$form_data.attributes}>
	<h1>{'Login_LogIn'|translate}</h1>
	<fieldset class="inputs">
		<input type="text" name="form_login" id="login_form_login" class="input" value="" size="20" tabindex="10" placeholder="{'General_Username'|translate}" />
		<input type="password" name="form_password" id="login_form_password" class="input" value="" size="20" tabindex="20" placeholder="{'Login_Password'|translate}" />
		<input type="hidden" name="form_nonce" id="login_form_nonce" value="{$nonce}" />
	</fieldset>

	<fieldset class="actions">
		<label><input name="form_rememberme" type="checkbox" id="login_form_rememberme" value="1" tabindex="90" {if $form_data.form_rememberme.value}checked="checked" {/if}/> {'Login_RememberMe'|translate}</label>
		<input class="submit" id='login_form_submit' type="submit" value="{'Login_LogIn'|translate}" tabindex="100" />
	</fieldset>
</form>

<form id="reset_form" style="display:none;">
	<fieldset class="inputs">
		<input type="text" name="form_login" id="reset_form_login" class="input" value="" size="20" tabindex="10" placeholder="{'Login_LoginOrEmail'|translate}" />
		<input type="hidden" name="form_nonce" id="reset_form_nonce" value="{$nonce}" /></label>

		<input type="password" name="form_password" id="reset_form_password" class="input" value="" size="20" tabindex="20" placeholder="{'Login_Password'|translate}" /></label>

		<input type="password" name="form_password_bis" id="reset_form_password_bis" class="input" value="" size="20" tabindex="30" placeholder="{'Login_PasswordRepeat'|translate}" /></label>
	</fieldset>
	
	<fieldset class="actions">
		<span class="loadingPiwik" style="display:none;"><img src="themes/default/images/loading-blue.gif" /></span>
		<input class="submit" id='reset_form_submit' type="submit" value="{'Login_ChangePassword'|translate}" tabindex="100"/>
	</fieldset>
	
	<input type="hidden" name="module" value="Login"/>
	<input type="hidden" name="action" value="resetPassword"/>
</form>

<p id="nav">
<a id="login_form_nav" href="#" title="{'Login_LostYourPassword'|translate}">{'Login_LostYourPassword'|translate}</a>
<a id="alternate_reset_nav" href="#" style="display:none;" title="{'Login_LogIn'|translate}">{'Login_LogIn'|translate}</a>
<a id="reset_form_nav" href="#" style="display:none;" title="{'Mobile_NavigationBack'|translate}">{'General_Cancel'|translate}</a>
</p>
{if isset($smarty.capture.poweredByPiwik)}
	<p id="piwik">
	{$smarty.capture.poweredByPiwik}
	</p>
{/if}

<div id="lost_password_instructions" style="display:none;">
	<p class="message">{'Login_ResetPasswordInstructions'|translate}</p>
</div>
{/if}
</div>
</body>
</html>
