<span id="header_message">
{if $piwikUrl == 'http://piwik.org/demo/'}
	{literal}
	<style>
	#header_message {border: 1px solid #FF7F00 ; padding:5px;right:35px;} 
	#header_message .demolink { font-weight:bold; }
	#header_message .demolink, #header_message .demolink a { font-size:12pt;color:#FF7F00; }
	</style>
	{/literal}
	<span class="demolink">
	You are currently viewing the demo of <a target='_blank' href='http://piwik.org'>Piwik</a>; 
	<a href='http://piwik.org/'><u>download</u></a> the full version! Check out <a href='http://piwik.org'><u>piwik.org</u></a>
	</span>
{else}
	{'General_PiwikIsACollaborativeProject'|translate:"<a href='http://piwik.org'>":"</a>":"<br />":"<u><a href='mailto:hello@piwik.org?subject=I would like to help Piwik! Here is my idea and how I could contribute'>":"</a></u>"} 
{/if}
</span>
