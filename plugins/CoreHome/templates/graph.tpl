<div id="{$properties.uniqueId}">
	<div class="{if $graphType=='evolution'}dataTableGraphEvolutionWrapper{else}dataTableGraphWrapper{/if}">

	{if $flashParameters.isDataAvailable}	
		<div><div id="{$chartDivId}">
			{'General_RequiresFlash'|translate} >= {$flashParameters.requiredFlashVersion}. <a target="_blank" href="misc/redirectToUrl.php?url={'http://piwik.org/faq/troubleshooting/#faq_53'|escape:"url"}">{'General_GraphHelp'|translate}</a>
		</div></div>
		<script type="text/javascript">
<!--
			swfobject.embedSWF(
				"{$flashParameters.ofcLibraryPath}open-flash-chart.swf?{$tag}",
				"{$chartDivId}",
				"{$flashParameters.width}", "{$flashParameters.height}",
				"{$flashParameters.requiredFlashVersion}",
				"{$flashParameters.swfLibraryPath}expressInstall.swf",
				{literal}{{/literal}
					"x-data-file":"{$urlGraphData|escape:"url"}",
					"loading":"{'General_Loading'|translate|escape:"html"}",
					"id":"{$chartDivId}"
				{literal}},
				{{/literal}
					"allowScriptAccess":"always",
					"wmode":"transparent"
				{literal}},
				{{/literal}
					"bgcolor":"#FFFFFF"
				{literal}}{/literal}
			);
			piwikHelper.OFC.set("{$chartDivId}", '{$flashParameters.data}');
//-->
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
