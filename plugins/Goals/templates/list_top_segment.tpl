
{foreach from=$topSegment item=element name=topGoalElements}
{assign var=nb_conversion value=$element.nb_conversions}
{assign var=conversion_rate value=$element.conversion_rate}
<span class='goalTopElement' title='{'Goals_Conversions'|translate:"<b>$nb_conversions</b>"}, 
	{'Goals_ConversionRate'|translate:"<b>$conversion_rate%</b>"}'>
{$element.name}</span>
{logoHtml metadata=$element.metadata alt=$element.name}
{if $smarty.foreach.topGoalElements.iteration == $smarty.foreach.topGoalElements.total-1} and {elseif $smarty.foreach.topGoalElements.iteration < $smarty.foreach.topGoalElements.total-1}, {else}{/if}
{/foreach} {* (<a href=''>more</a>) *}
