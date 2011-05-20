<div id="{$properties.uniqueId}">
	
	{if !empty($reportDocumentation)}
		<div class="reportDocumentation"><p>{$reportDocumentation}</p></div>
	{/if}
	
	<div class="{if $graphType=='evolution'}dataTableGraphEvolutionWrapper{else}dataTableGraphWrapper{/if}">

	{if $flashParameters.isDataAvailable || !$flashParameters.includeData}
		
		<div class="jqplot-{$jqPlotType}" style="padding-left: 6px;">
			<div id="{$chartDivId}" class="piwik-graph" style="position: relative; width: {$flashParameters.width}{if substr($flashParameters.width, -1) != '%'}px{/if}; height: {$flashParameters.height}{if substr($flashParameters.height, -1) != '%'}px{/if};"></div>
		</div>
		
		<script type="text/javascript">
			{literal}  window.setTimeout(function() {  {/literal}
				(new JQPlot({$flashParameters.data})).render('{$jqPlotType}', '{$chartDivId}', false, {literal} { {/literal}
					noData: '{'General_NoDataForGraph'|translate}',
					exportTitle: '{'General_ExportAsImage_js'|translate}',
					exportText: '{'General_SaveImageOnYourComputer_js'|translate}'	
				{literal} }); {/literal}
			{literal}  }, 5);  {/literal}
		</script>
		
	{else}
		
		<div><div id="{$chartDivId}" class="pk-emptyGraph">
			{'General_NoDataForGraph'|translate}
		</div></div>
		
	{/if}

	{if $properties.show_footer}
		{include file="CoreHome/templates/datatable_footer.tpl"}
		{include file="CoreHome/templates/datatable_js.tpl"}
	{/if}
	
	</div>
</div>
