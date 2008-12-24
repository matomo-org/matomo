
{include file="Goals/templates/title_and_evolution_graph.tpl"}



{foreach from=$goalMetrics item=goal}
{assign var=nb_conversions value=$goal.nb_conversions}
{assign var=conversion_rate value=$goal.conversion_rate}
<h3 style="text-decoration:underline;padding-top:20px">{$goal.name} (goal)</h3>
<table width=700px>
	<tr><td>
		<p>{sparkline src=$goal.urlSparklineConversions}<span>
		{'%s conversions'|translate:"<strong>$nb_conversions</strong>"}</span></p>
	</td><td>
		<p>{sparkline src=$goal.urlSparklineConversionRate}<span>
		{'%s conversion rate'|translate:"<strong>$conversion_rate%</strong>"}</span></p>
	</td><td>
	{* (<a href=''>more</a>) *}
	</td></tr>
</table>

{/foreach}

{if $userCanEditGoals}
	{include file=Goals/templates/add_edit_goal.tpl}
{/if}
