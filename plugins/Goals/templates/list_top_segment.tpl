
{foreach from=$topSegment item=element name=topGoalElements}
<span class='goalTopElement' title='<b>{$element.nb_conversions}</b> conversions, <b>{$element.conversion_rate}%</b> conversion rate'>
{$element.name}</span>{logoHtml metadata=$element.metadata alt=$element.name}{if $smarty.foreach.topGoalElements.iteration == $smarty.foreach.topGoalElements.total-1} and {elseif $smarty.foreach.topGoalElements.iteration < $smarty.foreach.topGoalElements.total-1}, {else}{/if}
{/foreach} (<a href=''>more</a>)
