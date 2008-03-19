<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Piwik &rsaquo; Login</title>
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
	<a href="http://piwik.org"><h1 title="Web analytics">Piwik <span class="description"># open source web analytics</span></h1></a>
</div>

<div id="login">

{if $form_data.errors}
<div id="login_error">	
	{foreach from=$form_data.errors item=data}
		<strong>ERROR</strong>: {$data}<br />
	{/foreach}
</div>
{/if}

{if $AccessErrorString}
<div id="login_error"><strong>ERROR</strong>: {$AccessErrorString}<br /></div>
{/if}

<form {$form_data.attributes}>
	<p>
		<label>{'Login_Login'|translate}<br />
		<input type="text" name="form_login" id="form_login" class="input" value="" size="20" tabindex="10" /></label>
	</p>

	<p>
		<label>{'Login_Password'|translate}<br />
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

{*
<p id="nav">
<a href="" title="Password Lost and Found">Lost your password?</a>
</p>
*}
</div>

</body>
</html>



