<script type="text/javascript">
	var piwik = {literal}{}{/literal};
	piwik.token_auth = "{$token_auth}";
	piwik.piwik_url = "{$piwikUrl}";
	{if isset($userLogin)}piwik.userLogin = "{$userLogin|escape:'javascript'}";{/if}
	{if isset($idSite)}piwik.idSite = "{$idSite}";{/if}
	{if isset($siteName)}piwik.siteName = "{$siteName|escape:'javascript'}";{/if}
	{if isset($siteMainUrl)}piwik.siteMainUrl = "{$siteMainUrl|escape:'javascript'}";{/if}
	{if isset($period)}piwik.period = "{$period}";{/if}
	{* piwik.currentDateString should not be used other than by the calendar Javascript 
			(it is not set to the expected value when period=range) 
		Use broadcast.getValueFromUrl('date') instead
	*}
	piwik.currentDateString = "{if isset($date)}{$date}{elseif isset($endDate)}{$endDate}{/if}";
	{if isset($startDate)}piwik.startDateString = "{$startDate}";{/if}
	{if isset($endDate)}piwik.endDateString = "{$endDate}";{/if}
	{if isset($minDateYear)}piwik.minDateYear = {$minDateYear};{/if}
	{if isset($minDateMonth)}piwik.minDateMonth = parseInt("{$minDateMonth}", 10);{/if}
	{if isset($minDateDay)}piwik.minDateDay = parseInt("{$minDateDay}", 10);{/if}
	{if isset($maxDateYear)}piwik.maxDateYear = {$maxDateYear};{/if}
	{if isset($maxDateMonth)}piwik.maxDateMonth = parseInt("{$maxDateMonth}", 10);{/if}
	{if isset($maxDateDay)}piwik.maxDateDay = parseInt("{$maxDateDay}", 10);{/if}
	{if isset($language)}piwik.language = "{$language}";{/if}
</script>
