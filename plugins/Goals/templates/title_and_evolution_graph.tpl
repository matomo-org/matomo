<a name="evolutionGraph" graphId="{$nameGraphEvolution}"></a>

{if $displayFullReport}
	<h2>{if isset($goalName)}{'Goals_GoalX'|translate:$goalName}{else}{'Goals_GoalsOverview'|translate}{/if}</h2>
{/if}
{$graphEvolution}

<div id='leftcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineConversions}
	{'Goals_Conversions'|translate:"<strong>$nb_conversions</strong>"}</div>
	{if $revenue != 0 }
		<div class="sparkline">{sparkline src=$urlSparklineRevenue}
		{assign var=revenue value=$revenue|money:$idSite}
		{'Goals_OverallRevenue'|translate:"<strong>$revenue</strong>"}</div>
	{/if}
</div>
<div id='rightcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineConversionRate}
	{'Goals_OverallConversionRate'|translate:"<strong>$conversion_rate</strong>"}</div>
</div>


{include file=CoreHome/templates/sparkline_footer.tpl}

