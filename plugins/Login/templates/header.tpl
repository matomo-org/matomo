<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>{if !$isCustomLogo}Piwik &rsaquo; {/if}{'Login_LogIn'|translate}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="plugins/Login/templates/login.css" />
	<meta name="description" content="{'General_OpenSourceWebAnalytics'|translate|escape}" />
	


{if isset($forceSslLogin) && $forceSslLogin}
{literal}
	<script type="text/javascript">
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
{if 'General_LayoutDirection'|translate =='rtl'}
<link rel="stylesheet" type="text/css" href="themes/default/rtl.css" />
{/if}
{include file="CoreHome/templates/iframe_buster_header.tpl"}
</head>
<body class="login">
{include file="CoreHome/templates/iframe_buster_body.tpl"}
{include file="default/ie6.tpl"}
	<div id="logo">
	{if !$isCustomLogo}<a href="http://piwik.org" alt="Piwik" title="{$linkTitle}">{/if}
		<img src='{$logoLarge}' title="{$linkTitle}" width='200' style='margin-right:20px' />
		{if !$isCustomLogo}<div class="description"># {$linkTitle}</div>
		{else}{capture name='poweredByPiwik'}
				<i><a href="http://piwik.org/" target="_blank">{$linkTitle}</a></i>
				{/capture}
		{/if}
	{if !$isCustomLogo}</a>{/if}
	</div>
