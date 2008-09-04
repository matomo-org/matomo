<span class="topBarElem" style="padding-right:70px">
	<span id="languageSelection" style="display:none;position:absolute">
		<form action="index.php?module=LanguagesManager&action=saveLanguage" method="get">
		<select name="language">
			<option value="{$currentLanguage}">{$languages.$currentLanguage}</option>
			{foreach from=$languages key=languageCode item=languageName}
			<option value="{$languageCode}">{$languageName}</option>
			{/foreach}
		</select>
		<input type="submit" value="go"/>
		</form>
	</span>
	
	{literal}<script language="javascript">
	$(document).ready(function() {
		$("#languageSelection").fdd2div({CssClassName:"formDiv"});
		$("#languageSelection").show();
	});</script>
	{/literal}
</span>
