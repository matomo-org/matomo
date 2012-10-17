<div> <!-- Wrapper is needed that the html can be jqueryfied -->

	<!-- This div is removed by JS and the content is put in the location div -->
	<div class="Insight_Location"><b>{'Insight_Page'|translate}:</b> {$location|escape}</div>
	
	{if count($data)}
		<h2 class="Insight_MainMetrics">{'Insight_MainMetrics'|translate|escape}</h2>
		{foreach from=$data item=metric}
			<div class="Insight_Metric">
				<span class="Insight_MetricValue">{$metric.value}</span> {$metric.name|escape}
			</div>
		{/foreach}
	{else}
		<div class="Insight_NoData">{'Insight_NoData'|translate|escape}</div>
	{/if}
	
</div>