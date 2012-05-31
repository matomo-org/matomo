<div class="rowevolution multirowevolution">
	<div class="popover-title">{'RowEvolution_MultiRowEvolutionTitle'|translate|escape:'html'}</div>
	<div class="graph">
		{$graph}
	</div> 
	<div class="metrics-container">
		<h2>{$availableRecordsText|translate}</h2>
		<table class="metrics" border="0" cellpadding="0" cellspacing="0">
			{foreach from=$metrics item=metric}
				<tr>
					<td class="sparkline">
						{$metric.sparkline}
					</td>
					<td class="text">
						{logoHtml metadata=$metric alt=""} <span style="color:{$metric.color}">{$metric.label|escape:'html'}</span><br />
						<span class="details">{$metric.details}</span>
					</td>
				</tr>
			{/foreach}
		</table>
		<a href="#" class="rowevolution-startmulti">&raquo; {'RowEvolution_PickAnotherRow'|translate}</a>
	</div>
	{if count($availableMetrics) > 1}
	<div class="metric-selectbox">
		<h2>{'RowEvolution_AvailableMetrics'|translate}</h2> 
		<select name="metric" class="multirowevoltion-metric">
			{foreach from=$availableMetrics item=metricName key=metric}
				<option value="{$metric|escape:'html'}"{if $selectedMetric == $metric} selected="selected"{/if}>
					{$metricName|escape:'html'}
				</option>
			{/foreach}
		</select>
	</div>
	{/if}
</div>