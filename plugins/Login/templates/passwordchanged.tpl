{include file="Login/templates/header.tpl"}

<div id="login">

{if isset($ErrorString)}
	<div id="login_error"><strong>{'General_Error'|translate}</strong>: {$ErrorString}<br />
	{'Login_ContactAdmin'|translate}
	</div>
{else}
	<p class="message">
	{'Login_PasswordSuccessfullyChanged'|translate}
	</p>
{/if}

<p id="nav">
<a href="index.php?module=Login&amp;form_url={$urlToRedirect|escape:url}" title="{'Login_LogIn'|translate}">{'Login_LogIn'|translate}</a>
</p>

</div>

</body>
</html>
