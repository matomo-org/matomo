<span id="header_message">
{if  $piwikUrl == 'http://piwik.org/demo/'}
	{'General_YouAreCurrentlyViewingDemoOfPiwik'|translate:"<a target='_blank' href='http://piwik.org'>Piwik</a>":"<a href='http://piwik.org/'>":"</a>":"<a href='http://piwik.org'>piwik.org</a>"}
{elseif $latest_version_available}
	<img src='themes/default/images/warning_small.png' alt='' style="vertical-align: middle;" /> 
	{'General_PiwikXIsAvailablePleaseUpdateNow'|translate:$latest_version_available:"<br /><a href='index.php?module=CoreUpdater&action=newVersionAvailable'>":"</a>":"<a href='misc/redirectToUrl.php?url=http://piwik.org/changelog/' target='_blank'>":"</a>"}
{else}
	{'General_PiwikIsACollaborativeProjectYouCanContribute'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org'>":"$piwik_version</a>":"<br />":"<a target='_blank' href='misc/redirectToUrl.php?url=http://piwik.org/contribute/'>":"</a>"} 
{/if}
</span>
