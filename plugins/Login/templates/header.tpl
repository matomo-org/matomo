<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Piwik &rsaquo; {'Login_LogIn'|translate}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="plugins/Login/templates/login.css" />
{if !$enableFramedLogins}
{literal}
	<style>body { display : none; }</style>
{/literal}
{/if}
{if $forceSslLogin}
{literal}
	<script>
		if(window.location.protocol !== 'https:') {
			var url = window.location.toString();
			url = url.replace(/^http:/, 'https:');
			window.location.replace(url);
		}
	</script>
{/literal}
{/if}
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
{if !$enableFramedLogins}
{literal}
	<script type="text/javascript">
		if(self == top) {
			var theBody = document.getElementsByTagName('body')[0];
			theBody.style.display = 'block';
		} else {
			top.location = self.location;
		}
	</script>
{/literal}
{/if}
	<div id="logo">
		<a href="http://piwik.org" title="{$linkTitle}"><span class="h1"><span style="color: rgb(245, 223, 114);">P</span><span style="color: rgb(241, 175, 108);">i</span><span style="color: rgb(241, 117, 117);">w</span><span style="color: rgb(155, 106, 58);">i</span><span style="color: rgb(107, 50, 11);">k</span> <span class="description"># {'General_OpenSourceWebAnalytics'|translate}</span></span></a>
	</div>
