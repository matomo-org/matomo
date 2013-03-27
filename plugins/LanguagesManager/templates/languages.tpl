<span class="topBarElem" style="padding-right:70px">
	<span id="languageSelection" style="position:absolute">
		<form action="index.php?module=LanguagesManager&amp;action=saveLanguage" method="post">
            <select name="language" id="language">
                <option title="" value=""
                        href="?module=Proxy&amp;action=redirect&amp;url=http://piwik.org/translations/">{'LanguagesManager_AboutPiwikTranslations'|translate}</option>
                {foreach from=$languages item=language}
                    <option value="{$language.code}" {if $language.code == $currentLanguageCode}selected="selected"{/if}
                            title="{$language.name} ({$language.english_name})">{$language.name}</option>
                {/foreach}
            </select>
            {* During installation token_auth is not set *}
            {if !empty($token_auth)}<input type="hidden" name="token_auth" value="{$token_auth}"/>{/if}
            <input type="submit" value="go"/>
        </form>
	</span>
	
	<script type="text/javascript">
        piwik.languageName = "{$currentLanguageName}";
    </script>
</span>
