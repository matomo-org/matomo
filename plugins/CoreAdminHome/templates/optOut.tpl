<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
		<form method="post" action="?module=CoreAdminHome&amp;action=optOut{if $language}&amp;language={$language}{/if}">
			<input type="hidden" name="nonce" value="{$nonce}" ></input>
			<input type="hidden" name="fuzz" value="{$smarty.now}"></input>
			<input onclick="this.form.submit()" type="checkbox" id="trackVisits" name="trackVisits" {if $trackVisits}checked="checked"{/if}></input>
			<label for="trackVisits"><strong>
			{if $trackVisits}{'CoreAdminHome_YouAreOptedIn'|translate} {'CoreAdminHome_ClickHereToOptOut'|translate}
			{else}{'CoreAdminHome_YouAreOptedOut'|translate} {'CoreAdminHome_ClickHereToOptIn'|translate}{/if}
			</strong></label>
		</form>
	</body>
</html>
