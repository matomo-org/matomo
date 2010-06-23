<span class="topBarElem" style="padding-right:70px">
	<span id="languageSelection" style="display:none;position:absolute">
		<form action="index.php?{if $currentModule != ''}module=LanguagesManager&{/if}action=saveLanguage" method="get">
		<select name="language">
			<option value="{$currentLanguageCode}">{$currentLanguageName}</option>
			<option href='misc/redirectToUrl.php?url=http://piwik.org/translations/'>{'LanguagesManager_AboutPiwikTranslations'|translate}</option>
			{foreach from=$languages item=language}
			<option value="{$language.code}">{$language.name}</option>
			{/foreach}
		</select>
		<input type="submit" value="go" />
		</form>
	</span>
	
	{literal}<script type="text/javascript">
	$(document).ready(function() {
		$("#languageSelection").fdd2div({CssClassName:"formDiv"});
		$("#languageSelection").show();
		$("#languageSelection ul").hide();
	});</script>
	{/literal}
</span>
