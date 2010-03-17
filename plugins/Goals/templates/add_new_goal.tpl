
{if $userCanEditGoals}
	{include file=Goals/templates/add_edit_goal.tpl}
{else}
{'Goals_NoGoalsNeedAccess'|translate}
{/if}
