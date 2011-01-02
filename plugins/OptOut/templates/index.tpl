<html>
	<head>
		{if $css}<style type="text/css">{$css|escape:'html'}</style>{/if}
	</head>
	<body>
{if $trackVisits}
	{assign var=text value='OptOut_DontTrackVisits'|translate}
{else}
	{assign var=text value='OptOut_TrackVisits'|translate}
{/if}
{if $control eq 'button'}
		<form method="post" action="?module=OptOut">
			<input type="hidden" name="control" value="button"></input>
			<input type="hidden" name="css" value="{$css|escape:'html'}"></input>
			<input type="hidden" name="nonce" value="{$nonce}"></input>
			<button type="submit" name="trackVisits" value="{$text}">{$text}</button>
		</form>
{elseif $control eq 'text'}
		<a href="?module=OptOut&amp;nonce={$nonce}&amp;control=text&amp;css={$css|escape:'url'}">{$text}</a>
{else}
		<form method="post" action="?module=OptOut">
			<input type="hidden" name="css" value="{$css|escape:'html'}"></input>
			<input type="hidden" name="nonce" value="{$nonce}"></input>
			<input onchange="this.form.submit();" type="checkbox" name="trackVisits" value="{'OptOut_TrackVisits'|translate}" {if $trackVisits}checked="checked"{/if}>{'OptOut_TrackVisits'|translate}</input>
		</form>
{/if}
	</body>
</html>
