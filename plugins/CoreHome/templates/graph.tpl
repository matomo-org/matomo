<div id="{$properties.uniqueId}" class="dataTable">
	
	<div class="reportDocumentation">
		{if !empty($reportDocumentation)}<p>{$reportDocumentation}</p>{/if}
		{if isset($properties.metadata.archived_date)}<p>{$properties.metadata.archived_date}</p>{/if}
	</div>
	
	<div class="{if $graphType=='evolution'}dataTableGraphEvolutionWrapper{else}dataTableGraphWrapper{/if}">

	{if $isDataAvailable}
		
		<div class="jqplot-{$graphType}" style="padding-left: 6px;">
			<div id="{$chartDivId}" class="piwik-graph" style="position: relative; width: {$width}{if substr($width, -1) != '%'}px{/if}; height: {$height}{if substr($height, -1) != '%'}px{/if};"></div>
		</div>
		
		<script type="text/javascript">
			{literal}  window.setTimeout(function() {  {/literal}
				var plot = new JQPlot({$data}, '{$properties.uniqueId}');
				{if isset($properties.externalSeriesToggle) && $properties.externalSeriesToggle}
					plot.addExternalSeriesToggle({$properties.externalSeriesToggle}, '{$chartDivId}',
						{if $properties.externalSeriesToggleShowAll}true{else}false{/if});
				{/if}
				plot.render('{$graphType}', '{$chartDivId}', {literal} { {/literal}
					noData: '{'General_NoDataForGraph'|translate|escape:'javascript'}',
					exportTitle: '{'General_ExportAsImage_js'|translate|escape:'javascript'}',
					exportText: '{'General_SaveImageOnYourComputer_js'|translate|escape:'javascript'}',
					metricsToPlot: '{'General_MetricsToPlot'|translate|escape:'javascript'}',
					metricToPlot: '{'General_MetricToPlot'|translate|escape:'javascript'}',
					recordsToPlot: '{'General_RecordsToPlot'|translate|escape:'javascript'}'
				{literal} }); {/literal}
			{literal}  }, 5);  {/literal}
		</script>
		
	{else}
		
		<div><div id="{$chartDivId}" class="pk-emptyGraph">
			{if $showReportDataWasPurgedMessage}
			{'General_DataForThisGraphHasBeenPurged'|translate:$deleteReportsOlderThan}
			{else}
			{'General_NoDataForGraph'|translate}
			{/if}
		</div></div>
		
	{/if}

	{if $properties.show_footer}
		{include file="CoreHome/templates/datatable_footer.tpl"}
		{include file="CoreHome/templates/datatable_js.tpl"}
	{/if}
	
	</div>
</div>
