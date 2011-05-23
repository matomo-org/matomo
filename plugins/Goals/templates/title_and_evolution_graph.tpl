<a name="evolutionGraph" graphId="{$nameGraphEvolution}"></a>

{if $displayFullReport}
	<h2>{if isset($goalName)}{'Goals_GoalX'|translate:$goalName}{else}{'Goals_GoalsOverview'|translate}{/if}</h2>
{/if}
{$graphEvolution}

<div id='leftcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineConversions}
	{if $ecommerce} <strong>{$nb_conversions}</strong> {'General_EcommerceOrders'|translate}
	{else}{'Goals_Conversions'|translate:"<strong>$nb_conversions</strong>"}
	{/if}
		 {if isset($goalAllowMultipleConversionsPerVisit) && $goalAllowMultipleConversionsPerVisit}
		 	({'VisitsSummary_NbVisits'|translate:"<strong>$nb_visits_converted</strong>"})
		 {/if}
	</div>
	{if $revenue != 0 }
		<div class="sparkline">{sparkline src=$urlSparklineRevenue}
		{assign var=revenue value=$revenue|money:$idSite}
		{if $ecommerce}<strong>{$revenue}</strong> {'General_TotalRevenue'|translate}
		{else}{'Goals_OverallRevenue'|translate:"<strong>$revenue</strong>"}
		{/if}
		</div>
	{/if}
	{if isset($ecommerce)}
		<div class="sparkline">{sparkline src=$urlSparklineAverageOrderValue}
		<strong>{$avg_order_revenue|money:$idSite}</strong> {'General_AverageOrderValue'|translate}</div>
	{/if}
	
</div>
<div id='rightcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineConversionRate}
	{if $ecommerce}{capture assign='ecommerceOrdersText'}{'General_EcommerceOrders'|translate}{/capture}
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


{include file="CoreHome/templates/sparkline_footer.tpl"}

