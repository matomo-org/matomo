
{if $userCanEditGoals}
	{include file=Goals/templates/add_edit_goal.tpl}
{else}
Only an Administrator or the Super User can add Goals for a given website. 
Please ask your Piwik administrator to set up a Goal for your website.
<br>Tracking Goals are a great tool to help you maximize your website performance!
{/if}
