
{foreach from=$topDimension item=element name=topGoalElements}
	{assign var=goal_nb_conversion value=$element.nb_conversions}
	{assign var=goal_conversion_rate value=$element.conversion_rate}
	<span class='goalTopElement' title='{'Goals_Conversions'|translate:"<b>$goal_nb_conversion</b>"}, 
		{'Goals_ConversionRate'|translate:"<b>$goal_conversion_rate</b>"}'>
	{$element.name}</span>
	{logoHtml metadata=$element.metadata alt=$element.name}
	{if $smarty.foreach.topGoalElements.iteration == $smarty.foreach.topGoalElements.total-1} and {elseif $smarty.foreach.topGoalElements.iteration < $smarty.foreach.topGoalElements.total-1}, {else}{/if}
{/foreach} 