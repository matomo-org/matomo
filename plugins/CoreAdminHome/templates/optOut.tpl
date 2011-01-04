<html>
	<head>
	</head>
	<body>
		{if !$trackVisits}{'CoreAdminHome_OptOutComplete'|translate}
		<br/>
		{'CoreAdminHome_OptOutCompleteBis'|translate}
		{else}
		{'CoreAdminHome_YouMayOptOut'|translate} 
		<br/>
		{'CoreAdminHome_YouMayOptOutBis'|translate} 
		{/if}
		<br/><br/> 
		<form method="post" action="?module=CoreAdminHome&amp;action=optOut">
			<input type="hidden" name="nonce" value="{$nonce}"></input>
			<input onclick="this.form.submit()" type="checkbox" id="trackVisits" name="trackVisits" {if $trackVisits}checked="checked"{/if}><label for="trackVisits"><strong>{if $trackVisits}{'CoreAdminHome_YouAreOptedIn'|translate}{else}{'CoreAdminHome_YouAreOptedOut'|translate} {/if}</strong></a></input>
		</form>
	</body>
</html>
