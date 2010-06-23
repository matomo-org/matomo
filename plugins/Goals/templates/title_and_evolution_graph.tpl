<script type="text/javascript" src="plugins/CoreHome/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="{$nameGraphEvolution}"></a>
<h2>{$title}</h2>
{$graphEvolution}

<div id='leftcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineConversions}
	{'%s conversions'|translate:"<strong>$nb_conversions</strong>"}</div>
	{if $revenue != 0 }
		<div class="sparkline">{sparkline src=$urlSparklineRevenue}
		{'%s overall revenue'|translate:"<strong>$currency$revenue</strong>"}</div>
	{/if}
</div>
<div id='rightcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineConversionRate}
	{'%s overall conversion rate (visits with a completed goal)'|translate:"<strong>$conversion_rate%</strong>"}</div>
</div>
