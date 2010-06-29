<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Piwik &rsaquo; {'Login_LogIn'|translate}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" />

	<link rel="stylesheet" type="text/css" href="plugins/Login/templates/login.css" media="screen" />
	
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
	<script type="text/javascript" src="libs/jquery/jquery.js"></script>
</head>

<body class="login">
<!-- shamelessly taken from wordpress 2.5 - thank you guys!!! -->

<div id="logo">
	<a href="http://piwik.org" title="{$linkTitle}"><span class="h1"><span style="color: rgb(245, 223, 114);">P</span><span style="color: rgb(241, 175, 108);">i</span><span style="color: rgb(241, 117, 117);">w</span><span style="color: rgb(155, 106, 58);">i</span><span style="color: rgb(107, 50, 11);">k</span> <span class="description"># {'General_OpenSourceWebAnalytics'|translate}</span></span></a>
</div>
