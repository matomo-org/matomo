<link rel="stylesheet" type="text/css" href="plugins/Goals/templates/goals.css" />

{include file="Goals/templates/title_and_evolution_graph.tpl"}

{foreach from=$goalMetrics item=goal}
	{assign var=nb_conversions value=$goal.nb_conversions}
	{assign var=conversion_rate value=$goal.conversion_rate}
	{assign var=name value=$goal.name}
	
    <div class="goalEntry">
	<h2>
		<a href="javascript:broadcast.propagateAjax('module=Goals&action=goalReport&idGoal={$goal.id}')">
		{'Goals_GoalX'|translate:"'$name'"}
		</a>
	</h2>
	<div id='leftcolumn'>
		<div class="sparkline">{sparkline src=$goal.urlSparklineConversions}
		{'Goals_Conversions'|translate:"<strong>$nb_conversions</strong>"}</div>
	</div>
	<div id='rightcolumn'>
		<div class="sparkline">{sparkline src=$goal.urlSparklineConversionRate}
		{'Goals_ConversionRate'|translate:"<strong>$conversion_rate</strong>"}</div>
	</div>
    <br class="clear" />
    </div>
{/foreach}

{if $displayFullReport}

	{include file="Goals/templates/table_by_segment.tpl"}
	
	{if $userCanEditGoals}
		{include file=Goals/templates/add_edit_goal.tpl}
	{/if}
{/if}