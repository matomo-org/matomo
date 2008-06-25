<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Piwik &rsaquo; Login</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	{literal}
	<script type="text/javascript">
		function focusit() {
			var formLogin = document.getElementById('form_login');
			if(formLogin)
			{
				formLogin.focus();
			}
		}
		window.onload = focusit;
	</script>
	{/literal}
	<link rel="stylesheet" href="plugins/Login/templates/login.css" type="text/css" media="screen" />
</head>

<body class="login">
<!-- shamelessly taken from wordpress 2.5 - thank you guys!!! -->

<div id="logo">
	<a href="http://piwik.org" title="{$linkTitle}"><span class="h1">Piwik <span class="description"># open source web analytics</span></span></a>
</div>
