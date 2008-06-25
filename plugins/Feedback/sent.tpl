{if isset($ErrorString)}
	<strong>{'General_Error'|translate}</strong>: {$ErrorString}<br />
	Please send your message at <a href='mailto:hello@piwik.org'>hello@piwik.org</a>:
	<br />{$message}
{else}
	<p>Your message was sent to Piwik developers</p>
	<p><strong>Thank you for your feedback!</strong><br /> Piwik Team</p>	
{/if}
