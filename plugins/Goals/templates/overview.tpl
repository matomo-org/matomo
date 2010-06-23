
{include file="Goals/templates/title_and_evolution_graph.tpl"}

{foreach from=$goalMetrics item=goal}
{assign var=nb_conversions value=$goal.nb_conversions}
{assign var=conversion_rate value=$goal.conversion_rate}
<h2 style="padding-top: 30px;">{$goal.name} (goal)</h2>
<div id='leftcolumn'>
	<div class="sparkline">{sparkline src=$goal.urlSparklineConversions}
	{'%s conversions'|translate:"<strong>$nb_conversions</strong>"}</div>
</div>
<div id='rightcolumn'>
	<div class="sparkline">{sparkline src=$goal.urlSparklineConversionRate}
	{'%s conversion rate'|translate:"<strong>$conversion_rate%</strong>"}</div>
	{* (<a href=''>more</a>) *}
</div>
{/foreach}

{if $userCanEditGoals}
	{include file=Goals/templates/add_edit_goal.tpl}
{/if}

{include file="Goals/templates/release_notes.tpl}
