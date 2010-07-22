{* testing *}
{assign var=test_latest_version_available value="1.0"}
{assign var=test_piwikUrl value='http://demo.piwik.org/'}
<span id="header_message" class="{if $piwikUrl == 'http://demo.piwik.org/' || !$latest_version_available}header_info{else}header_alert{/if}">
<span class="header_short">
	{if $piwikUrl == 'http://demo.piwik.org/'}
		{'General_YouAreViewingDemoShortMessage'|translate}
	{elseif $latest_version_available}
		{'General_NewUpdatePiwikX'|translate:$latest_version_available}
	{else}
		{'General_AboutPiwikX'|translate:$piwik_version}
	{/if}
</span>

<span class="header_full">
	{if $piwikUrl == 'http://demo.piwik.org/'}
		{'General_YouAreViewingDemoShortMessage'|translate}<br/>
		{'General_DownloadFullVersion'|translate:"<a href='http://piwik.org/'>":"</a>":"<a href='http://piwik.org'>piwik.org</a>"}
	{elseif $latest_version_available}
		{if $isSuperUser}
			{'General_PiwikXIsAvailablePleaseUpdateNow'|translate:$latest_version_available:"<br /><a href='index.php?module=CoreUpdater&action=newVersionAvailable'>":"</a>":"<a href='misc/redirectToUrl.php?url=http://piwik.org/changelog/' target='_blank'>":"</a>"}
		{else}
			{'General_PiwikXIsAvailablePleaseNotifyPiwikAdmin'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org/' target='_blank'>Piwik</a> <a href='misc/redirectToUrl.php?url=http://piwik.org/changelog/' target='_blank'>$latest_version_available</a>"}
		{/if}
	{else}
		{'General_PiwikIsACollaborativeProjectYouCanContribute'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org' target='_blank'>":"$piwik_version</a>":"<br />":"<a target='_blank' href='misc/redirectToUrl.php?url=http://piwik.org/contribute/'>":"</a>"}
	{/if}
</span>
</span>
