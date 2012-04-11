<div class="rowevolution">
	<div class="popover-title">{$popoverTitle}</div>
	<div class="graph">
		{$graph}
	</div>
	<div class="metrics-container">
		<h2>{$availableMetricsText}</h2>
		<div class="rowevolution-documentation">
			{'RowEvolution_Documentation'|translate}
		</div>
		<table class="metrics" border="0" cellpadding="0" cellspacing="0">
			{foreach from=$metrics item=metric}
				<tr>
					<td class="sparkline">
						{$metric.sparkline}
					</td>
					<td class="text">
						<span style="color:{$metric.color}">{$metric.label|escape:'html'}</span>{if $metric.details}: 
						<span class="details">{$metric.details}</span>{/if}
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
	<div class="compare-container">
		<h2>{'RowEvolution_CompareRows'|translate}</h2>
		<div class="rowevolution-documentation">
			{'RowEvolution_CompareDocumentation'|translate}
		</div>
		<a href="#" class="rowevolution-startmulti">&raquo; {'RowEvolution_PickARow'|translate}</a>
	</div>
</div>