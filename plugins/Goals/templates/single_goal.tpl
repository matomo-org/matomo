<link rel="stylesheet" type="text/css" href="plugins/Goals/templates/goals.css" />
{include file="Goals/templates/title_and_evolution_graph.tpl"}

	<div class="clear"></div>
	{if $nb_conversions > 0}
	    <h2>{'Goals_ConversionsOverview'|translate}</h2>
		<ul class="ulGoalTopElements">
{if !isset($ecommerce)}
	    {if isset($topDimensions.country)}<li>{'Goals_BestCountries'|translate} {include file='Goals/templates/list_top_dimension.tpl' topDimension=$topDimensions.country}</li>{/if}
	    {if isset($topDimensions.keyword) && count($topDimensions.keyword)>0}<li>{'Goals_BestKeywords'|translate} {include file='Goals/templates/list_top_dimension.tpl' topDimension=$topDimensions.keyword}</li>{/if}
	    {if isset($topDimensions.website) && count($topDimensions.website)>0}<li>{'Goals_BestReferers'|translate} {include file='Goals/templates/list_top_dimension.tpl' topDimension=$topDimensions.website}</li>{/if}
	    <li>{'Goals_ReturningVisitorsConversionRateIs'|translate:"<b>$conversion_rate_returning</b>"}, {'Goals_NewVisitorsConversionRateIs'|translate:"<b>$conversion_rate_new</b>"}</li>
{else}
		<li>{'Live_GoalRevenue'|translate}: {$revenue|money:$idSite}{if !empty($revenue_subtotal)}, 
			{'General_Subtotal'|translate}: {$revenue_subtotal|money:$idSite}{/if}{if !empty($revenue_tax)},
			{'General_Tax'|translate}: {$revenue_tax|money:$idSite}{/if}{if !empty($revenue_shipping)}, 
			{'General_Shipping'|translate}: {$revenue_shipping|money:$idSite}{/if}{if !empty($revenue_discount)}, 
			{'General_Discount'|translate}: {$revenue_discount|money:$idSite}{/if} 
		</li>
{/if}
		</ul>
	{/if}

{literal}
<script type="text/javascript">
$(document).ready( function() {
	$('.goalTopElement').tooltip();
});
</script>
{/literal}

{if $displayFullReport}
	{if $nb_conversions > 0}
		{include file="Goals/templates/table_by_dimension.tpl"}
	{/if}
{/if}
