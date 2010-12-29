<html>
	<head></head>
	<body>
		<form method="post" action="?module=OptOut&amp;action=changeStatus">
			<input type="hidden" name="nonce" value="{$nonce}"></input>
			<input onchange="this.form.submit();" type="checkbox" name="trackVisits" value="{'OptOut_TrackVisits'|translate}" {if $trackVisits}checked="checked"{/if}>{'OptOut_TrackVisits'|translate}</input>
		</form>
	</body>
</html>
