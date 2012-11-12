<div> <!-- Wrapper is needed that the html can be jqueryfied -->

	<!-- This div is removed by JS and the content is put in the location div -->
	<div class="Overlay_Location">
		<b>{'Overlay_Page'|translate|escape:'html'}:</b> 
		<span>{$location|escape:'html'}</span>
	</div>
	
	{if count($data)}
		<h2 class="Overlay_MainMetrics">{'Overlay_MainMetrics'|translate|escape:'html'}</h2>
		{foreach from=$data item=metric}
			<div class="Overlay_Metric">
				<span class="Overlay_MetricValue">{$metric.value}</span> {$metric.name|escape:'html'}
			</div>
		{/foreach}
	{else}
		<div class="Overlay_NoData">{'Overlay_NoData'|translate|escape:'html'}</div>
	{/if}
	
	<a class="Overlay_FullScreen" href="#">
		<span class="Overlay_OpenFullScreen">{'Overlay_OpenFullScreen'|translate|escape:'html'}</span>
		<span class="Overlay_CloseFullScreen">{'Overlay_CloseFullScreen'|translate|escape:'html'}</span>
	</a>
	<a class="Overlay_NewTab" href="index.php?module=Overlay&action=startOverlaySession&idsite={$idSite}&period={$period}&date={$date}#{$currentUrl|escape:'html'}" target="_blank">
		{'Overlay_OpenNewTab'|translate|escape:'html'}
	</a>
	
</div>