
{if $userCanEditGoals}
	{include file=Goals/templates/add_edit_goal.tpl}
{else}
<h2>{'Goals_CreateNewGOal'|translate}</h2>
<p>
{'Goals_NoGoalsNeedAccess'|translate}
</p>
<p>{'Goals_LearnMoreAboutGoalTrackingDocumentation'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/tracking-goals-web-analytics/' target='_blank'>":"</a>"}
</p>
{/if}
