<script type="text/javascript" src="plugins/CoreHome/templates/sparkline.js"></script>


<div id="leftcolumn">
<a name="evolutionGraph" graphId="{$nameGraphEvolution}"></a>
<h2>{$title}</h2>
{$graphEvolution}

</div>

<div id="rightcolumn">
<table>
	<tr><td>
		<p>{sparkline src=$urlSparklineConversions}<span>
		{'%s conversions'|translate:"<strong>$nb_conversions</strong>"}</span></p>
		{if $revenue != 0 }
			<p>{sparkline src=$urlSparklineRevenue}<span>
			{'%s overall revenue'|translate:"<strong>$currency$revenue</strong>"}</span></p>
		{/if}
	</td><td valign="top">
		<p>{sparkline src=$urlSparklineConversionRate}<span>
		{'%s overall conversion rate (visits with a completed goal)'|translate:"<strong>$conversion_rate%</strong>"}</span></p>
	</td></tr>
</table>
</div>

<div style="clear:both">