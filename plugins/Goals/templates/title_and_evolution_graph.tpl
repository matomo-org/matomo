<script type="text/javascript" src="plugins/CoreHome/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="{$nameGraphEvolution}"></a>
<h2>{$title}</h2>
{$graphEvolution}

<div id='leftcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineConversions}
	{'Goals_Conversions'|translate:"<strong>$nb_conversions</strong>"}</div>
	{if $revenue != 0 }
		<div class="sparkline">{sparkline src=$urlSparklineRevenue}
		{'Goals_OverallRevenue'|translate:"<strong>$currency$revenue</strong>"}</div>
	{/if}
</div>
<div id='rightcolumn'>
	<div class="sparkline">{sparkline src=$urlSparklineConversionRate}
	{'Goals_OverallConversionRate'|translate:"<strong>$conversion_rate%</strong>"}</div>
</div>
