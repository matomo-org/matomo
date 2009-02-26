<span id="header_message">
{if  $piwikUrl == 'http://piwik.org/demo/'}
	{'General_YouAreCurrentlyViewingDemoOfPiwik'|translate:"<a target='_blank' href='http://piwik.org'>Piwik</a>":"<a href='http://piwik.org/'><u>":"</u></a>":"<a href='http://piwik.org'><u>piwik.org</u></a>"}
{elseif $latest_version_available}
	<img src='themes/default/images/warning_small.png' alt='' style="vertical-align: middle;"> 
	{'General_PiwikXIsAvailablePleaseUpdateNow'|translate:$latest_version_available:"<br /><a href='index.php?module=CoreUpdater&action=newVersionAvailable'>":"</a>":"<a href='misc/redirectToUrl.php?url=http://piwik.org/changelog/' target='_blank'>":"</a>"}
{else}
	{'General_PiwikIsACollaborativeProject'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org'>":"$piwik_version</a>":"<br />":"<u><a href='mailto:hello@piwik.org?subject=I would like to help Piwik! Here is my idea and how I could contribute'>":"</a></u>"} 
{/if}
</span>
