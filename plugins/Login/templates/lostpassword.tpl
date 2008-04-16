<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Piwik &rsaquo; Lost password</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	{literal}
	<script type="text/javascript">
		function focusit() {
			document.getElementById('form_login').focus();
		}
		window.onload = focusit;
	</script>
	{/literal}
<link rel="stylesheet" href="plugins/Login/templates/login.css">
</head>

<body class="login">
<!-- shamelessly taken from wordpress 2.5 - thank you guys!!! -->

<div id="logo">
	<a href="http://piwik.org" title="{$linkTitle}"><span class="h1">Piwik <span class="description"># open source web analytics</span></span></a>
</div>

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
	</p>
	{$form_data.form_url.html}
	<p class="submit">
		<input type="submit" value="{'Login_RemindPassword'|translate}" tabindex="100" />
	</p>
</form>


<p id="nav">
<a href="?module=Login&form_url={$urlToRedirect}" title="{'Login_LogIn'|translate}">{'Login_LogIn'|translate}</a>
</p>

</div>

</body>
</html>



