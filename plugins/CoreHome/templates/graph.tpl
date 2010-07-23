<div id="{$properties.uniqueId}">
	<div class="{if $graphType=='evolution'}dataTableGraphEvolutionWrapper{else}dataTableGraphWrapper{/if}">

	{if $flashParameters.isDataAvailable || !$flashParameters.includeData}
		<div><div id="{$chartDivId}">
			{'General_RequiresFlash'|translate} >= {$flashParameters.requiredFlashVersion}. <a target="_blank" href="misc/redirectToUrl.php?url={'http://piwik.org/faq/troubleshooting/#faq_53'|escape:"url"}">{'General_GraphHelp'|translate}</a>
		</div></div>
		<script type="text/javascript">
<!--
			{if $flashParameters.includeData}
			piwikHelper.OFC.set("{$chartDivId}", '{$flashParameters.data}');
			{/if}
			swfobject.embedSWF(
				"{$flashParameters.ofcLibraryPath}open-flash-chart.swf?{$tag}",
				"{$chartDivId}",
				"{$flashParameters.width}", "{$flashParameters.height}",
				"{$flashParameters.requiredFlashVersion}",
				"{$flashParameters.swfLibraryPath}expressInstall.swf",
				{literal}{{/literal}
					"{if $flashParameters.includeData}x-{/if}data-file":"{$urlGraphData|escape:"url"}",
				{if $flashParameters.includeData}
					"id":"{$chartDivId}",
				{/if}
					"loading":"{'General_Loading_js'|translate|escape:"html"}"
				{literal}},
				{{/literal}
					"allowScriptAccess":"always",
					"wmode":"transparent"
				{literal}},
				{{/literal}
					"bgcolor":"#FFFFFF"
				{literal}}{/literal}
			);
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
