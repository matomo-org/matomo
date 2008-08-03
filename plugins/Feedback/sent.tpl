{literal}
<style>
body {
	font-family: Georgia,"Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	font-size:0.9em;
	padding:0.2em;
}
#error {
	color: red;
	text-align: center;
	border: 2px solid red;
	background-color:#FFFBFB;
	margin: 10px;
	padding: 10px;
}
#success {
	color: #38D73B;
	text-align: center;
	border: 2px solid red;
	margin: 10px;
	padding: 10px;
}
</style>
{/literal}

{if isset($ErrorString)}
	<div id="error"><strong>{'General_Error'|translate}:</strong> {$ErrorString}</div>
	<p>Please manually send your message at <a href='mailto:hello@piwik.org'>hello@piwik.org</a></p>
	<p>{$message}</p>
{else}
	<div id="success">Your message was sent to Piwik.</div>
	<p><strong>Thank you for your helping us making Piwik better!</strong><br /> The Piwik Team</p>	
{/if}
