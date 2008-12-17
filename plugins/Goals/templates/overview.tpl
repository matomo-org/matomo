{include file="Goals/templates/title_and_evolution_graph.tpl"}


{foreach from=$goalMetrics item=goal}
{assign var=nb_conversions value=$goal.nb_conversions}
{assign var=conversion_rate value=$goal.conversion_rate}
<h2>{$goal.name} (goal)</h2>
<table width=700px>
	<tr><td>
		<p>{sparkline src=$goal.urlSparklineConversions}<span>
		{'%s conversions'|translate:"<strong>$nb_conversions</strong>"}</span></p>
	</td><td>
		<p>{sparkline src=$goal.urlSparklineConversionRate}<span>
		{'%s conversion rate'|translate:"<strong>$conversion_rate%</strong>"}</span></p>
	</td><td>
	(<a href=''>more</a>)
	</td></tr>
</table>

{/foreach}

<h2><a href=''>+ Add a new Goal</a></h2>

