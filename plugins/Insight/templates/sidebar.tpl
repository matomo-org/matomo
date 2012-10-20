<div> <!-- Wrapper is needed that the html can be jqueryfied -->

	<!-- This div is removed by JS and the content is put in the location div -->
	<div class="Insight_Location">
		<b>{'Insight_Page'|translate|escape:'html'}:</b> 
		<span>{$location|escape:'html'}</span>
	</div>
	
	{if count($data)}
		<h2 class="Insight_MainMetrics">{'Insight_MainMetrics'|translate|escape:'html'}</h2>
		{foreach from=$data item=metric}
			<div class="Insight_Metric">
				<span class="Insight_MetricValue">{$metric.value}</span> {$metric.name|escape:'html'}
			</div>
		{/foreach}
	{else}
		<div class="Insight_NoData">{'Insight_NoData'|translate|escape:'html'}</div>
	{/if}
	
	<a class="Insight_FullScreen" href="#">
		<span class="Insight_OpenFullScreen">{'Insight_OpenFullScreen'|translate|escape:'html'}</span>
		<span class="Insight_CloseFullScreen">{'Insight_CloseFullScreen'|translate|escape:'html'}</span>
	</a>
	
</div>