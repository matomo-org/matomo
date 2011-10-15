<a name="evolutionGraph" graphId="{$nameGraphEvolution}"></a>

{if $displayFullReport}
	<h2>{if isset($goalName)}{'Goals_GoalX'|translate:$goalName}{else}{'Goals_GoalsOverview'|translate}{/if}</h2>
{/if}
{$graphEvolution}

<div id='leftcolumn' {if !$isWidget}style='width:33%'{/if}>
	<div class="sparkline">{sparkline src=$urlSparklineConversions}
	{if isset($ecommerce)} <strong>{$nb_conversions}</strong> {'General_EcommerceOrders'|translate} <img src='themes/default/images/ecommerceOrder.gif'> 
	{else}{'Goals_Conversions'|translate:"<strong>$nb_conversions</strong>"}
	{/if}
		 {if isset($goalAllowMultipleConversionsPerVisit) && $goalAllowMultipleConversionsPerVisit}
		 	({'VisitsSummary_NbVisits'|translate:"<strong>$nb_visits_converted</strong>"})
		 {/if}
	</div>
	{if $revenue != 0 || isset($ecommerce)}
		<div class="sparkline">{sparkline src=$urlSparklineRevenue}
		{assign var=revenue value=$revenue|money:$idSite}
		{if isset($ecommerce)}<strong>{$revenue}</strong> {'General_TotalRevenue'|translate}
		{else}{'Goals_OverallRevenue'|translate:"<strong>$revenue</strong>"}
		{/if}
		</div>
	{/if}
	{if isset($ecommerce)}
		<div class="sparkline">{sparkline src=$urlSparklineAverageOrderValue}
		<strong>{$avg_order_revenue|money:$idSite}</strong> {'General_AverageOrderValue'|translate}</div>
	{/if}
	
</div>
<div id='leftcolumn' {if !$isWidget}style='width:33%'{/if}>
	<div class="sparkline">{sparkline src=$urlSparklineConversionRate}
	{if isset($ecommerce)}{capture assign='ecommerceOrdersText'}{'General_EcommerceOrders'|translate}{/capture}
		{'Goals_ConversionRate'|translate:"<strong>$conversion_rate</strong> $ecommerceOrdersText"}
	{else}
		{'Goals_OverallConversionRate'|translate:"<strong>$conversion_rate</strong>"}
	{/if}
	</div>
	{if isset($ecommerce)}
		<div class="sparkline">{sparkline src=$urlSparklinePurchasedProducts}
		<strong>{$items}</strong> {'General_PurchasedProducts'|translate}</div>
	{/if}
</div>
{if isset($ecommerce)}
<div id='rightcolumn'  {if !$isWidget}style='width:30%'{/if}>
	<div>
		<img src='themes/default/images/ecommerceAbandonedCart.gif'> <i>{'General_AbandonedCarts'|translate}</i>
	</div>
	
	<div class="sparkline">{sparkline src=$cart_urlSparklineConversions}
	{capture assign='ecommerceAbandonedCartsText'}{'Goals_AbandonedCart'|translate}{/capture}
	<strong>{$cart_nb_conversions}</strong> {'General_VisitsWith'|translate:$ecommerceAbandonedCartsText}
	</div>
	
	<div class="sparkline">{sparkline src=$cart_urlSparklineRevenue}
	{capture assign=revenue}{$cart_revenue|money:$idSite}{/capture}
	{capture assign=revenueText}{'Live_GoalRevenue'|translate}{/capture}
	<strong>{$revenue}</strong> {'Goals_LeftInCart'|translate:$revenueText}
	</div>
	
	<div class="sparkline">{sparkline src=$cart_urlSparklineConversionRate}
	<strong>{$cart_conversion_rate}</strong> {'General_VisitsWith'|translate:$ecommerceAbandonedCartsText}
	</div>
</div>
{/if}
{include file="CoreHome/templates/sparkline_footer.tpl"}

